<?php

return [

    'mailer' => [
        'campaign' => [
            'controller'        => Hellomayaagency\Enso\Mailer\Controllers\CampaignController::class,
            'config'            => Hellomayaagency\Enso\Mailer\Crud\Campaign::class,
            'model'             => Hellomayaagency\Enso\Mailer\Models\Campaign::class,

            'user' => [
                'controller'    => Hellomayaagency\Enso\Mailer\Controllers\CampaignUserController::class,
                'json'          => Hellomayaagency\Enso\Mailer\Controllers\Json\CampaignUserController::class,
            ],

            'preview' => [
                'controller'    => Hellomayaagency\Enso\Mailer\Controllers\CampaignPreviewController::class,
            ],

            'email' => [
                'controller'    => Hellomayaagency\Enso\Mailer\Controllers\EmailPreviewController::class,
            ],
        ],

        'audience' => [
            'controller'        => Hellomayaagency\Enso\Mailer\Controllers\AudienceController::class,
            'config'            => Hellomayaagency\Enso\Mailer\Crud\Audience::class,
            'model'             => Hellomayaagency\Enso\Mailer\Models\Audience::class,

            'list' => [
                'controller'    => Hellomayaagency\Enso\Mailer\Controllers\AudienceListController::class,
            ],

            'user' => [
                'controller'    => Hellomayaagency\Enso\Mailer\Controllers\AudienceUserController::class,
                'json'          => Hellomayaagency\Enso\Mailer\Controllers\Json\AudienceUserController::class,
            ],
        ],

        'condition' => [
            'model'             => Hellomayaagency\Enso\Mailer\Models\Condition::class,
        ],
    ],
];
