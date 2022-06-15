<?php

return [

    /**
     * Class of the Crud Config for Mailer Campaigns.
     */
    'config' => Hellomayaagency\Enso\Mailer\Crud\Campaign::class,

    /**
     * Class of the Crud Controller for Mailer Campaigns.
     */
    'controller' => Hellomayaagency\Enso\Mailer\Http\Controllers\Admin\CampaignController::class,

    /**
     * Properties for the Ensō menu item for Mailer Campaigns.
     */
    'menuitem' => [
        'icon' => 'fa fa-file-o',
        'label' => 'Campaigns',
        'route' => ['admin.mailer.campaigns.index'],
    ],

    /**
     * Class of the Crud Model for Mailer Campaigns.
     */
    'model' => Hellomayaagency\Enso\Mailer\Models\Campaign::class,


    'user' => [
        'controller' => Hellomayaagency\Enso\Mailer\Controllers\CampaignUserController::class,
        'json' => Hellomayaagency\Enso\Mailer\Controllers\Json\CampaignUserController::class,
    ],

    'preview' => [
        'controller' => Hellomayaagency\Enso\Mailer\Controllers\CampaignPreviewController::class,
    ],

    'email' => [
        'controller' => Hellomayaagency\Enso\Mailer\Controllers\EmailPreviewController::class,
    ],
];
