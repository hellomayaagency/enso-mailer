<?php

namespace Hellomayaagency\Enso\Mailer;

use Hellomayaagency\Enso\Mailer\Contracts\Audience as AudienceContract;
use Hellomayaagency\Enso\Mailer\Contracts\AudienceController as AudienceControllerContract;
use Hellomayaagency\Enso\Mailer\Contracts\AudienceCrud as AudienceCrudContract;
use Hellomayaagency\Enso\Mailer\Contracts\AudienceListController as AudienceListControllerContract;
use Hellomayaagency\Enso\Mailer\Contracts\AudienceUserController as AudienceUserControllerContract;
use Hellomayaagency\Enso\Mailer\Contracts\Campaign as CampaignContract;
use Hellomayaagency\Enso\Mailer\Contracts\CampaignController as CampaignControllerContract;
use Hellomayaagency\Enso\Mailer\Contracts\CampaignCrud as CampaignCrudContract;
use Hellomayaagency\Enso\Mailer\Contracts\CampaignEmailController;
use Hellomayaagency\Enso\Mailer\Contracts\CampaignPreviewController as CampaignPreviewControllerContract;
use Hellomayaagency\Enso\Mailer\Contracts\CampaignStatusController as CampaignStatusControllerContract;
use Hellomayaagency\Enso\Mailer\Contracts\CampaignUserController as CampaignUserControllerContract;
use Hellomayaagency\Enso\Mailer\Contracts\Condition as ConditionContract;
use Hellomayaagency\Enso\Mailer\Contracts\JsonAudienceUserController as JsonAudienceUserControllerContract;
use Hellomayaagency\Enso\Mailer\Contracts\JsonCampaignUserController as JsonCampaignUserControllerContract;
use Hellomayaagency\Enso\Mailer\Contracts\MailParser;
use Hellomayaagency\Enso\Mailer\Contracts\MailSender;
use Hellomayaagency\Enso\Mailer\Contracts\MandrillWebhookController as MandrillWebhookControllerContract;
use Hellomayaagency\Enso\Mailer\Crud\Audience as AudienceCrud;
use Hellomayaagency\Enso\Mailer\Crud\Campaign as CampaignCrud;
use Hellomayaagency\Enso\Mailer\Facades\EnsoMailer;
use Hellomayaagency\Enso\Mailer\Http\Controllers\Admin\AudienceController;
use Hellomayaagency\Enso\Mailer\Http\Controllers\Admin\AudienceListController;
use Hellomayaagency\Enso\Mailer\Http\Controllers\Admin\AudienceUserController;
use Hellomayaagency\Enso\Mailer\Http\Controllers\Admin\CampaignController;
use Hellomayaagency\Enso\Mailer\Http\Controllers\Admin\CampaignPreviewController;
use Hellomayaagency\Enso\Mailer\Http\Controllers\Admin\CampaignStatusController;
use Hellomayaagency\Enso\Mailer\Http\Controllers\Admin\CampaignUserController;
use Hellomayaagency\Enso\Mailer\Http\Controllers\Admin\EmailPreviewController;
use Hellomayaagency\Enso\Mailer\Http\Controllers\Admin\Json\AudienceUserController as JsonAudienceUserController;
use Hellomayaagency\Enso\Mailer\Http\Controllers\Admin\Json\CampaignUserController as JsonCampaignUserController;
use Hellomayaagency\Enso\Mailer\Http\Controllers\Admin\MandrillWebhookController;
use Hellomayaagency\Enso\Mailer\Models\Audience as AudienceModel;
use Hellomayaagency\Enso\Mailer\Models\Campaign as CampaignModel;
use Hellomayaagency\Enso\Mailer\Models\Condition as ConditionModel;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use TorMorten\Eventy\Facades\Eventy;
use Yadda\Enso\Facades\EnsoCrud;
use Yadda\Enso\Facades\EnsoMenu;

class EnsoMailerServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        // Provides default CRUD implementation for the Mailer.
        $this->mergeConfigFrom(__DIR__ . '/../config/crud/mailer_campaign.php', 'enso.crud.mailer_campaign');
        $this->mergeConfigFrom(__DIR__ . '/../config/crud/mailer_audience.php', 'enso.crud.mailer_audience');
        $this->mergeConfigFrom(__DIR__ . '/../config/crud/mailer_condition.php', 'enso.crud.mailer_condition');

        // Provides default Mailer configurations.
        $this->mergeConfigFrom(
            __DIR__ . '/../config/mailer.php',
            'enso.mailer'
        );

        // Provides default media preset sizes for the mailer.
        $this->mergeConfigFrom(
            __DIR__ . '/../config/media_presets.php',
            'enso.media.presets'
        );

        $this->app->singleton('enso-mailer', function () {
            return $this->app->make(\Hellomayaagency\Enso\Mailer\EnsoMailer::class);
        });

        AliasLoader::getInstance()->alias('EnsoMailer', EnsoMailer::class);

        $this->app->bind('enso-mailer-query', function () {
            return EnsoCrud::modelClass('user')::query();
        });

        $this->app->bind(CampaignContract::class, CampaignModel::class);
        $this->app->bind(AudienceContract::class, AudienceModel::class);
        $this->app->bind(ConditionContract::class, ConditionModel::class);

        $this->app->bind(CampaignCrudContract::class, CampaignCrud::class);
        $this->app->bind(AudienceCrudContract::class, AudienceCrud::class);

        $this->app->bind(CampaignPreviewControllerContract::class, CampaignPreviewController::class);
        $this->app->bind(CampaignStatusControllerContract::class, CampaignStatusController::class);
        $this->app->bind(CampaignUserControllerContract::class, CampaignUserController::class);
        $this->app->bind(CampaignControllerContract::class, CampaignController::class);
        $this->app->bind(CampaignEmailController::class, EmailPreviewController::class);

        $this->app->bind(AudienceListControllerContract::class, AudienceListController::class);
        $this->app->bind(AudienceUserControllerContract::class, AudienceUserController::class);
        $this->app->bind(AudienceControllerContract::class, AudienceController::class);

        $this->app->bind(JsonAudienceUserControllerContract::class, JsonAudienceUserController::class);
        $this->app->bind(JsonCampaignUserControllerContract::class, JsonCampaignUserController::class);

        $this->app->bind(MandrillWebhookControllerContract::class, MandrillWebhookController::class);

        // Gets the correct Driver for sending Mail
        $this->app->singleton(MailSender::class, function () {
            $driver = config('enso.mailer.driver');

            $sender_class = config('enso.mailer.drivers.' . $driver . '.sender');

            return new $sender_class;
        });

        // Gets the correct Driver for parsing Mail content
        $this->app->singleton(MailParser::class, function () {
            $driver = config('enso.mailer.driver');

            $parser_class = config('enso.mailer.drivers.' . $driver . '.parser');

            return new $parser_class;
        });
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if (!$this->app->routesAreCached()) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        }

        $installable_dir = __DIR__ . '/../installable/';

        $this->publishes([
            $installable_dir . 'resources/sass/mailer.scss' => resource_path('/sass/enso-mailer.scss'),
        ], 'enso-first-time-assets');

        $this->publishes([
            $installable_dir . 'resources/sass/enso/enso-mailer.scss' => resource_path('/sass/enso/enso-mailer.scss'),
            $installable_dir . 'resources/js' => resource_path('/js/vendor/enso'),
        ], 'enso-assets-source');

        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'enso-migrations');

        $this->loadViewsFrom(__DIR__ . '/../resources/views/crud', 'enso-crud');

        $this->loadMigrationsFrom([
            __DIR__ . '/../database/migrations/',
        ]);

        Validator::extend('mailer_conditions', function ($attribute, $value, $parameters, $validator) {
            foreach ($value as $condition) {
                // Test that each condition is valid.
                if (!EnsoMailer::conditionIsValid($condition)) {
                    return false;
                }
            }

            return true;
        });

        Validator::replacer('mailer_conditions', function ($message, $attribute, $rule, $parameters) {
            return 'One or more of your Audience Conditions is invalid.';
        });

        EnsoMenu::addItem(
            array_merge(
                Config::get('enso.crud.mailer_campaign.menuitem'),
                [
                    'items' => [
                        Config::get('enso.crud.mailer_audience.menuitem'),
                    ],
                ]
            )
        );
    }
}
