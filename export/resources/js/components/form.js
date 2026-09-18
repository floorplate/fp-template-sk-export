/**
 * Statamic form submitted over AJAX.
 *
 * The <form> keeps its real action, method and CSRF token, so with JS disabled
 * the browser posts it normally and Statamic's session-flash success/error path
 * still works. This only intercepts when Alpine is running.
 *
 * Statamic's FormController checks request()->ajax() before wantsJson(), so the
 * X-Requested-With header below — not Accept — is what selects these shapes:
 *   200 { success, submission_created, submission, redirect }
 *   400 { errors: [...], error: { field_handle: "first message" } }
 */

const GENERIC_ERROR =
    "Sorry, something went wrong and your message wasn't sent. Please try again, or email us directly.";

// statamic/ssg is a starter-kit dependency. On a statically generated site the
// CSRF token is baked in at build time, so every POST comes back 419.
const EXPIRED_ERROR =
    "Your session expired while this page was open. Please refresh the page and submit again.";

export default (options = {}) => ({
    errors: {},
    generalError: "",
    succeeded: false,
    submitting: false,

    // Text of the sr-only live region. A permanently-present region whose text
    // changes is announced reliably; toggling a region's visibility is not.
    announcement: "",

    errorMessage: "",

    init() {
        this.errorMessage = options.errorMessage || GENERIC_ERROR;

        // A non-AJAX submit round-trips through the server. Pick up a
        // server-rendered success before Alpine walks the children, so x-show
        // sees the right value on its first evaluation — no flash, no x-cloak.
        if (this.$el.querySelector("[data-form-succeeded]")) {
            this.succeeded = true;
        }

        // Likewise for errors baked into the markup, so `errors` is the only
        // source of truth from here on, whichever path got us here.
        this.$el.querySelectorAll("[data-field-error]").forEach((el) => {
            const message = el.dataset.serverError;
            if (message) {
                this.errors[el.dataset.fieldError] = message;
            }
        });

        // Suppress native constraint bubbles only while JS is driving, so there
        // is one error vocabulary. With JS off the attribute is never added and
        // the browser's own validation still helps.
        this.formEl()?.setAttribute("novalidate", "novalidate");
    },

    formEl() {
        return this.$el.querySelector("form");
    },

    /** The message for one field, or "" — used by x-text, x-show and aria. */
    errorFor(handle) {
        return this.errors[handle] || "";
    },

    /**
     * Applied to each input via x-bind so aria-invalid tracks client-side
     * errors too. "false" rather than removing the attribute: both are valid
     * ARIA, and a constant attribute avoids a DOM mutation on every keystroke.
     */
    fieldBindings(handle) {
        return {
            [":aria-invalid"]: () => (this.errorFor(handle) ? "true" : "false"),
        };
    },

    async submit() {
        if (this.submitting) {
            return;
        }

        const form = this.formEl();
        if (!form) return;

        this.submitting = true;
        this.generalError = "";
        this.announcement = "Sending…";

        let response;
        let payload = null;

        try {
            response = await fetch(
                // getAttribute, not .action: a field named "action" would
                // shadow the property.
                form.getAttribute("action") || window.location.href,
                {
                    method: "POST",
                    body: new FormData(form), // carries _token and the honeypot
                    credentials: "same-origin",
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                        Accept: "application/json",
                    },
                }
            );
            payload = await response.json().catch(() => null);
        } catch (networkError) {
            this.fail(this.errorMessage);
            return;
        } finally {
            this.submitting = false;
        }

        // A honeypot hit also lands here, with submission_created: false. It is
        // meant to look like success, so treat it as one.
        if (response.ok && payload && payload.success) {
            this.errors = {};
            this.succeeded = true;
            // The success copy is server-rendered (editors control it), so read
            // it back rather than duplicating it as a JS string.
            this.announcement = this.successText();
            form.reset();
            this.$nextTick(() => this.$refs.status?.focus());

            return;
        }

        if (response.status === 400 && payload && payload.error) {
            this.errors = payload.error;

            const count = Object.keys(this.errors).length;
            this.announcement = `${count} ${
                count === 1 ? "field needs" : "fields need"
            } your attention.`;

            this.focusFirstError();

            return;
        }

        this.fail(response.status === 419 ? EXPIRED_ERROR : this.errorMessage);
    },

    /** The server-rendered success copy, flattened for the live region. */
    successText() {
        const el = this.$refs.status;

        return el
            ? el.textContent.trim().replace(/\s+/g, " ")
            : "Your message has been sent.";
    },

    fail(message) {
        this.errors = {};
        this.generalError = message;
        this.announcement = message;
        this.$nextTick(() => this.$refs.errorStatus?.focus());
    },

    focusFirstError() {
        this.$nextTick(() => {
            const handle = Object.keys(this.errors)[0];
            if (!handle) return;

            this.formEl()
                ?.querySelector(`[name="${window.CSS.escape(handle)}"]`)
                ?.focus();
        });
    },
});
