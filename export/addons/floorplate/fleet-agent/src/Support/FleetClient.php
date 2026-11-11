<?php

namespace Floorplate\FleetAgent\Support;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Asks Fleet for this site's analytics. Fleet holds the Fathom key and the
 * site's Fathom ID; this site only proves who it is, by signing with the SSO
 * secret Fleet already gave it (see Signature::analytics).
 */
class FleetClient
{
    public static function configured(): bool
    {
        return filled(config('fleet-agent.sso_secret')) && filled(config('fleet-agent.url'));
    }

    /**
     * @param  array<string, scalar|null>  $query
     * @return array{status: int, data: array<string, mixed>}
     */
    public function analytics(string $view, array $query = []): array
    {
        if (! static::configured()) {
            return $this->error(409, 'not_connected', 'This site isn\'t connected to Floorplate Fleet.');
        }

        $secret = (string) config('fleet-agent.sso_secret');
        $query = array_filter($query, fn ($value) => $value !== null && $value !== '');
        $expires = now()->addMinutes(2)->timestamp;

        try {
            // Fleet builds an uncached report in a few seconds; one with many
            // events can take longer.
            $response = Http::baseUrl(rtrim((string) config('fleet-agent.url'), '/'))
                ->acceptJson()
                ->connectTimeout(5)
                ->timeout(60)
                ->withHeaders([
                    'X-Fleet-Key' => Signature::analyticsKey($secret),
                    'X-Fleet-Expires' => (string) $expires,
                    'X-Fleet-Signature' => Signature::analytics($secret, $expires, $view, $query),
                ])
                ->get('/!/fleet/analytics/'.$view, $query);
        } catch (ConnectionException) {
            return $this->error(502, 'unreachable', 'Floorplate couldn\'t be reached. Try again in a moment.');
        }

        $data = $response->json();

        if (! is_array($data)) {
            return $this->error(502, 'unreachable', 'Floorplate couldn\'t be reached. Try again in a moment.');
        }

        if ($response->status() === 401) {
            return $this->error(409, 'not_connected', 'This site isn\'t connected to Floorplate Fleet.');
        }

        return ['status' => $response->successful() ? 200 : $response->status(), 'data' => $data];
    }

    /** @return array{status: int, data: array{error: string, message: string}} */
    protected function error(int $status, string $error, string $message): array
    {
        return ['status' => $status, 'data' => ['error' => $error, 'message' => $message]];
    }
}
