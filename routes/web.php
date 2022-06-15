<?php

use Hellomayaagency\Enso\Mailer\Contracts\AudienceController;
use Hellomayaagency\Enso\Mailer\Contracts\AudienceListController;
use Hellomayaagency\Enso\Mailer\Contracts\AudienceUserController;
use Hellomayaagency\Enso\Mailer\Contracts\CampaignController;
use Hellomayaagency\Enso\Mailer\Contracts\CampaignEmailController;
use Hellomayaagency\Enso\Mailer\Contracts\CampaignPreviewController;
use Hellomayaagency\Enso\Mailer\Contracts\CampaignStatusController;
use Hellomayaagency\Enso\Mailer\Contracts\CampaignUserController;
use Hellomayaagency\Enso\Mailer\Contracts\JsonAudienceUserController;
use Hellomayaagency\Enso\Mailer\Contracts\JsonCampaignUserController;
use Hellomayaagency\Enso\Mailer\Contracts\MandrillWebhookController;

Route::group(['prefix' => 'admin', 'middleware' => ['web', 'enso']], function () {
    Route::get('mailer/campaigns/{campaign}/email/show')->uses(CampaignEmailController::class . '@show')
        ->name('admin.mailer.campaigns.email.show');

    Route::get('mailer/campaigns/{campaign}/preview')->uses(CampaignPreviewController::class . '@show')
        ->name('admin.mailer.campaigns.preview.show');

    Route::get('mailer/campaigns/{campaign}/users')->uses(CampaignUserController::class . '@index')
        ->name('admin.mailer.campaigns.users.index');

    Route::get('mailer/campaigns/{campaign}/status')->uses(CampaignStatusController::class . '@show')
        ->name('admin.mailer.campaigns.status.show');

    Route::get('mailer/campaigns/{campaign}/refresh_status')->uses(CampaignStatusController::class . '@refresh')
        ->name('admin.mailer.campaigns.status.refresh');

    Route::post('campaigns/{campaign}/preview-send')->uses(CampaignStatusController::class . '@previewSend')
        ->name('admin.mailer.campaigns.status.preview-send');

    Route::post('campaigns/{campaign}/schedule-send')->uses(CampaignStatusController::class . '@scheduleSend')
        ->name('admin.mailer.campaigns.status.schedule-send');

    Route::get('mailer/campaigns/{campaign}/send')->uses(CampaignStatusController::class . '@send')
        ->name('admin.mailer.campaigns.status.send');

    EnsoCrud::crudRoutes('mailer/campaigns', 'mailer_campaign', 'admin.mailer.campaigns');

    Route::get('mailer/audiences/list')->uses(AudienceListController::class . '@index')
        ->name('admin.mailer.audiences.list');

    Route::get('mailer/audiences/{audience}/users')->uses(AudienceUserController::class . '@index')
        ->name('admin.mailer.audiences.users.index');

    EnsoCrud::crudRoutes('mailer/audiences', 'mailer_audience', 'admin.mailer.audiences');

    Route::group(['prefix' => 'json'], function () {
        Route::get('mailer/audiences/{audience}/users')->uses(JsonAudienceUserController::class . '@index')
            ->name('admin.json.mailer.audiences.users.index');

        Route::get('mailer/campaigns/{campaign}/users')->uses(JsonCampaignUserController::class . '@index')
            ->name('admin.json.mailer.campaigns.users.index');
    });
});

switch (Config::get('enso.mailer.driver', null)) {
    case 'mandrill':
        $webhook_controller = MandrillWebhookController::class;
        break;
    default:
        $webhook_controller = null;
}

if ($webhook_controller) {
    Route::group(['prefix' => 'admin', 'middleware' => [
        \Illuminate\Routing\Middleware\SubstituteBindings::class
    ]], function () use ($webhook_controller) {
        Route::post('webhooks/mailer')->uses($webhook_controller . '@store')
            ->name('admin.webhooks.mailer');

        Route::get('webhooks/mailer')->uses($webhook_controller . '@show');
    });
}
