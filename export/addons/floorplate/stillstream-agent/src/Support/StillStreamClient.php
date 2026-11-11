<?php

namespace Floorplate\StillStreamAgent\Support;

use Floorplate\FleetAgent\Support\FleetClient;
use Floorplate\FleetAgent\Support\Signature;
use Floorplate\StillStreamAgent\ServiceProvider;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Statamic\Contracts\Auth\User;

/**
 * Talks to StillStream on Floorplate for this site. Every request is signed
 * with the site's SSO secret and says who in this Control Panel is acting
 * and what they may do here (Floorplate\StillStream\Support\TenantSignature
 * on the other side — keep the two in step).
 */
class StillStreamClient
{
    public const PREFIX = '/!/stillstream/api/';

    /**
     * @param  array<string, mixed>  $data
     * @return array{status: int, data: array<string, mixed>}
     */
    public function send(User $user, string $method, string $path, array $data = []): array
    {
        if (! FleetClient::configured()) {
            return $this->error(409, 'This site isn\'t connected to Floorplate.');
        }

        $secret = (string) config('fleet-agent.sso_secret');
        $uri = self::PREFIX.ltrim($path, '/');
        $body = $data ? json_encode($data, JSON_THROW_ON_ERROR) : '';
        $expires = now()->addMinutes(2)->timestamp;
        $actor = base64_encode(json_encode([
            'id' => (string) $user->id(),
            'name' => $user->name() ?: $user->email(),
            'email' => $user->email(),
            'can' => array_values(array_filter(array_keys(ServiceProvider::PERMISSIONS), fn (string $permission) => $user->can($permission))),
        ], JSON_THROW_ON_ERROR));

        $payload = implode('.', ['stillstream', Signature::analyticsKey($secret), $expires, strtoupper($method), $uri, hash('sha256', $body), $actor]);

        try {
            $response = Http::baseUrl(rtrim((string) config('fleet-agent.url'), '/'))
                ->acceptJson()
                ->connectTimeout(5)
                ->timeout(60)
                ->withHeaders([
                    'X-Fleet-Key' => Signature::analyticsKey($secret),
                    'X-Fleet-Expires' => (string) $expires,
                    'X-Fleet-Actor' => $actor,
                    'X-Fleet-Signature' => hash_hmac('sha256', $payload, $secret),
                ])
                // Sent exactly as signed.
                ->withBody($body, 'application/json')
                ->send($method, $uri);
        } catch (ConnectionException) {
            return $this->error(502, 'Floorplate couldn\'t be reached. Try again in a moment.');
        }

        $json = $response->json();

        if (! is_array($json)) {
            return $this->error(502, 'Floorplate couldn\'t be reached. Try again in a moment.');
        }

        return ['status' => $response->status(), 'data' => $json];
    }

    /** @return array{status: int, data: array{message: string}} */
    protected function error(int $status, string $message): array
    {
        return ['status' => $status, 'data' => ['message' => $message]];
    }
}
