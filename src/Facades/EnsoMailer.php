<?php

namespace Hellomayaagency\Enso\Mailer\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Hellomayaagency\EnsoMailer\EnsoMailer
 */
class EnsoMailer extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'enso-mailer';
    }
}
