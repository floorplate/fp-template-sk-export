# Anchors

Rename-proof in-page anchor links for Statamic 6.

Two fieldtypes:

- **`anchor`** — declares an in-page anchor on the block (or entry) it lives on.
  Auto-slugified on save. Validates that no other field on the same entry
  declares the same anchor. PHP-only; renders with the core text input.
- **`anchor_link`** — a link picker with three modes: **URL** (raw string),
  **Entry** (entry picker plus an optional anchor on that entry), and
  **This page** (an anchor on the entry being edited, read live from the
  publish form — unsaved anchors are pickable immediately).

## Value shapes

| Stored value | Meaning |
|---|---|
| `'#contact'`, `'/work'`, `'https://…'` | Plain string. Legacy content and URL mode both store this. |
| `{mode: entry, entry: <id>, set?: <id>, anchor?: <slug>}` | Link to an entry, optionally to an anchor on it. |
| `{mode: anchor, set: <id>, anchor: <slug>}` | Anchor on the entry the field lives on. |

Anchor references store the **declaring block's set id** plus a slug snapshot.

## Resolution (augmentation)

`anchor_link` always augments to a plain URL string (or `null`), so templates
use `href="{{ link }}"` for every shape. Anchor resolution order:

1. The referenced set's **current** anchor value — renaming an anchor never
   breaks links pointing at it.
2. The stored slug snapshot (the block was deleted).
3. The bare entry URL (entry mode with no anchor). A deleted target entry
   augments to `null`.

Resolution reads the saved entry, so unpublished anchor renames don't affect
the live site until published.

## Notes

- Anchor discovery is blueprint-driven (fields of type `anchor`), never
  handle-driven. Top-level fields and Replicator/Bard sets are traversed;
  grids/groups nested inside sets are not.
- Uniqueness validation includes disabled blocks (a disabled duplicate would
  collide when re-enabled); pickers and augmentation exclude them.
- Field config: `collections` limits the entry picker; `modes` limits which
  modes editors see (e.g. `[url, entry]` for globals/nav, where "This page"
  is meaningless and auto-hidden anyway).

## Development

```bash
npm install
npm run build   # or: npm run dev (hot reload via resources/dist/hot)
```

The host site symlinks `public/vendor/anchors/build` to this addon's
`resources/dist/build`. Built assets are committed.

## Portability

Self-contained: no references to host-app code or content. To extract, move
this directory to its own repo and update the host's composer `repositories`
entry (or publish it).
