<?php

namespace Floorplate\FleetAgent\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\User;

/**
 * Creates or updates the Fleet-managed super-admin from FLEET_ADMIN_* env.
 * Fleet's deploy script runs it on every deploy, so it only saves when
 * something actually changed — the user file is committed with the site.
 */
class SyncAdmin extends Command
{
    use RunsInPlease;

    protected $signature = 'fleet:sync-admin';

    protected $description = 'Create or update the Fleet-managed super-admin';

    public function handle(): int
    {
        $email = (string) config('fleet-agent.admin.email');
        $password = (string) config('fleet-agent.admin.password');
        $name = (string) config('fleet-agent.admin.name');

        if ($email === '' || $password === '') {
            $this->components->warn('FLEET_ADMIN_EMAIL / FLEET_ADMIN_PASSWORD are not set; skipping.');

            return self::SUCCESS;
        }

        $user = User::findByEmail($email);
        $created = ! $user;
        $user ??= User::make()->email($email);
        $changed = $created;

        if (! $user->password() || ! Hash::check($password, $user->password())) {
            $user->password($password);
            $changed = true;
        }

        // isSuper() needs an id, which a new user only gets on save.
        if ($created || ! $user->isSuper()) {
            $user->makeSuper();
            $changed = true;
        }

        if ($name !== '' && $user->get('name') !== $name) {
            $user->set('name', $name);
            $changed = true;
        }

        if ($changed) {
            $user->save();
        }

        $this->components->info(match (true) {
            $created => "Created Fleet admin [{$email}].",
            $changed => "Updated Fleet admin [{$email}].",
            default => "Fleet admin [{$email}] is up to date.",
        });

        return self::SUCCESS;
    }
}
