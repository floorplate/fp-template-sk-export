<?php

namespace Floorplate\FleetAgent\Support;

/**
 * Verifies the HMAC signatures Fleet puts on its login and check URLs
 * (Floorplate\Fleet\Support\FleetSsoLink on the Fleet side — keep in step).
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
