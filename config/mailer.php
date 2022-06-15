<?php

return [

    'driver' => env('ENSO_MAILER_DRIVER', 'mandrill'),

    'drivers' => [
        'mandrill' => [
            'sender' => Hellomayaagency\Enso\Mailer\Drivers\MandrillSender::class,
            'parser' => Hellomayaagency\Enso\Mailer\Drivers\MandrillParser::class,
            'api_key' => env('ENSO_MAILER_SECRET', ''),
            'identifier_tag' => env('ENSO_MAILER_MANDRILL_TAG', 'EnsoMailer'),
            'webhook_key' => env('ENSO_MAILER_MANDRILL_KEY', null),
            'webhook_url' => env('ENSO_MAILER_MANDRILL_URL', null), // Specify this to override for testing only
        ],
    ],
];
