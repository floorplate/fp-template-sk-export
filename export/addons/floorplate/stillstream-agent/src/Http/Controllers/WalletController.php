<?php

namespace Floorplate\StillStreamAgent\Http\Controllers;

use Floorplate\StillStreamAgent\Support\StillStreamClient;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;

/** This site's credits: the balance, what was spent, and buying more. */
class WalletController extends CpController
{
    public function show(Request $request, StillStreamClient $stillstream)
    {
        $user = User::current();
        $result = $stillstream->send($user, 'GET', 'wallet');

        abort_unless($result['status'] === 200, 503, $result['data']['message'] ?? 'StillStream is unavailable.');

        return Inertia::render('stillstream::Wallet', $result['data'] + [
            // Set when Stripe sends the buyer back; credits arrive shortly after.
            'purchaseReturn' => $request->query('purchase'),
            'purchaseUrl' => cp_route('stillstream-agent.wallet.purchase'),
            'can' => ['purchase' => (bool) $user->can('purchase stillstream credits')],
        ]);
    }

    public function purchase(Request $request, StillStreamClient $stillstream)
    {
        $return = cp_route('stillstream-agent.wallet');

        $result = $stillstream->send(User::current(), 'POST', 'wallet/purchase', [
            'package' => (int) $request->input('package'),
            'success_url' => "{$return}?purchase=success",
            'cancel_url' => "{$return}?purchase=cancelled",
        ]);

        if ($result['status'] !== 200 || empty($result['data']['url'])) {
            return back()->with('error', $result['data']['message'] ?? 'The checkout could not be started.');
        }

        return Inertia::location($result['data']['url']);
    }
}
