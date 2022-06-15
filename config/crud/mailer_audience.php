<?php

return [

    /**
     * Class of the Crud Config for Mailer Audiences.
     */
    'config' => Hellomayaagency\Enso\Mailer\Crud\Audience::class,

    /**
     * Class of the Crud Controller for Mailer Audiences.
     */
    'controller' => Hellomayaagency\Enso\Mailer\Http\Controllers\Admin\AudienceController::class,

    /**
     * Properties for the Ensō menu item for Mailer Audiences.
     */
    'menuitem' => [
        'icon' => 'fa fa-file-o',
        'label' => 'Audiences',
        'route' => ['admin.mailer.audiences.index'],
    ],

    /**
     * Class of the Crud Model for Mailer Audiences.
     */
    'model' => Hellomayaagency\Enso\Mailer\Models\Audience::class,

    'list' => [
        'controller' => Hellomayaagency\Enso\Mailer\Http\Controllers\Admin\AudienceListController::class,
    ],

    'user' => [
        'controller' => Hellomayaagency\Enso\Mailer\Http\Controllers\Admin\AudienceUserController::class,
        'json' => Hellomayaagency\Enso\Mailer\Http\Controllers\Admin\Json\AudienceUserController::class,
    ],
];
