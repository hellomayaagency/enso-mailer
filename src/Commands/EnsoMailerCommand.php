<?php

namespace Hellomayaagency\Enso\Mailer\Commands;

use Illuminate\Console\Command;

class EnsoMailerCommand extends Command
{
    public $signature = 'enso-mailer';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
