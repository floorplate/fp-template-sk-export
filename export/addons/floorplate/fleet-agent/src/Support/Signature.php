<?php

namespace Floorplate\FleetAgent\Support;

/**
 * Verifies the HMAC signatures Fleet puts on its login and check URLs
 * (Floorplate\Fleet\Support\FleetSsoLink on the Fleet side — keep in step),
 * and signs this site's analytics requests to Fleet the other way round
 * (Floorplate\Fleet\Support\AnalyticsSignature — keep in step too).
 *
 * Login signs `uid.exp.nonce`; the check signs `check.exp.nonce`, so a check
 * URL can never be replayed as a login.
 */
class Signature
{
    /** Fleet's TTL is 60s; anything valid for longer wasn't minted by Fleet. */
    public const MAX_TTL_SECONDS = 300;

    public static function loginPayload(string $uid, int $exp, string $nonce): string
    {
        return "{$uid}.{$exp}.{$nonce}";
    }

    public static function checkPayload(int $exp, string $nonce): string
    {
        return "check.{$exp}.{$nonce}";
    }

    /**
     * Names this site to Fleet without naming the secret: an HMAC of a fixed
     * label. Fleet finds the site by it (Floorplate\Fleet\Support\
     * AnalyticsSignature::keyId).
     */
    public static function analyticsKey(string $secret): string
    {
        return substr(hash_hmac('sha256', 'fleet-analytics-key', $secret), 0, 32);
    }

    /**
     * Signs an analytics request to Fleet: the view and its query (keys
     * sorted, empty values dropped, RFC 3986), under this site's key.
     *
     * @param  array<string, scalar|null>  $query
     */
    public static function analytics(string $secret, int $expires, string $view, array $query): string
    {
        $query = array_filter($query, fn ($value) => $value !== null && $value !== '');
        ksort($query);

        $payload = 'analytics.'.self::analyticsKey($secret).".{$expires}.{$view}.".http_build_query($query, '', '&', PHP_QUERY_RFC3986);

        return hash_hmac('sha256', $payload, $secret);
    }

    public static function valid(string $payload, string $signature, int $exp): bool
    {
        $secret = (string) config('fleet-agent.sso_secret');

        if ($secret === '' || $signature === '') {
            return false;
        }

        $now = now()->timestamp;

        if ($exp < $now || $exp > $now + self::MAX_TTL_SECONDS) {
            return false;
        }

        return hash_equals(hash_hmac('sha256', $payload, $secret), $signature);
    }
}
