<?php

/*
 * Written into the site's .env by Fleet (provisioning, or "Enable SSO" for an
 * adopted site). Without them the agent is inert: the login route 403s and
 * fleet:sync-admin does nothing.
 */
return [
    'sso_secret' => env('FLEET_SSO_SECRET'),

    /*
     * Where Fleet runs. The dashboard's analytics widget reads this site's
     * figures from Fleet, signed with sso_secret, so it needs no key of its own.
     */
    'url' => env('FLEET_URL', 'https://floorplate.io'),

    'admin' => [
        'email' => env('FLEET_ADMIN_EMAIL'),
        'password' => env('FLEET_ADMIN_PASSWORD'),
        'name' => env('FLEET_ADMIN_NAME', 'Floorplate'),
    ],
];
