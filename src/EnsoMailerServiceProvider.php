<?php

namespace Hellomayaagency\EnsoMailer;

use Hellomayaagency\EnsoMailer\Commands\EnsoMailerCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class EnsoMailerServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('enso-mailer')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_enso-mailer_table')
            ->hasCommand(EnsoMailerCommand::class);
    }
}
