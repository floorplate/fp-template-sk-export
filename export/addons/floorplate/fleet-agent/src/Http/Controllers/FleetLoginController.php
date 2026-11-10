<?php

namespace Floorplate\FleetAgent\Http\Controllers;

use Floorplate\FleetAgent\Support\Signature;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Statamic\Facades\User;

class FleetLoginController extends Controller
{
    /**
     * Logs the Fleet-managed super-admin into the CP from a Fleet-signed URL.
     *
     * Only ever the managed admin (FLEET_ADMIN_EMAIL): a valid signature for
     * any other user is refused, so a leaked secret can't become a login as a
     * client's own account.
     */
    public function login(Request $request)
    {
        $uid = (string) $request->query('uid');
        $exp = (int) $request->query('exp');
        $nonce = (string) $request->query('nonce');

        abort_unless(
            $nonce !== '' && Signature::valid(Signature::loginPayload($uid, $exp, $nonce), (string) $request->query('sig'), $exp),
            403,
            'This login link is invalid or has expired. Start again from Fleet.'
        );

        $email = (string) config('fleet-agent.admin.email');

        abort_unless($email !== '' && strcasecmp($uid, $email) === 0, 403);

        // Single use. Held past the longest TTL a signature can carry.
        abort_unless(Cache::add('fleet-agent:nonce:'.hash('sha256', $nonce), true, Signature::MAX_TTL_SECONDS + 60), 403, 'This login link has already been used.');

        $user = User::findByEmail($email);

        abort_unless($user && $user->isSuper(), 403, 'The Fleet admin is not set up on this site yet. Redeploy it from Fleet.');

        Auth::guard(config('statamic.users.guards.cp', 'web'))->login($user);

        $request->session()->regenerate();

        // Nobody knows this user's password and its email goes nowhere, so
        // Statamic's password/emailed-code re-confirmation can't be answered.
        // The signed link is that confirmation; logging in again from Fleet
        // renews it.
        $request->session()->elevate();

        return redirect(cp_route('index'));
    }

    /**
     * Lets Fleet confirm, without logging in, that this site runs the agent,
     * shares its secret, and has the managed admin. Fleet only shows one-click
     * login once this passes.
     */
    public function check(Request $request): JsonResponse
    {
        $exp = (int) $request->query('exp');
        $nonce = (string) $request->query('nonce');

        abort_unless(
            $nonce !== '' && Signature::valid(Signature::checkPayload($exp, $nonce), (string) $request->query('sig'), $exp),
            403
        );

        $email = (string) config('fleet-agent.admin.email');
        $user = $email !== '' ? User::findByEmail($email) : null;

        return response()->json([
            'agent' => true,
            'admin' => (bool) ($user && $user->isSuper()),
        ]);
    }
}
