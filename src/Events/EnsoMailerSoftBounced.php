<?php

namespace Hellomayaagency\Enso\Mailer\Events;

use Hellomayaagency\Enso\Mailer\Models\MailEvent;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EnsoMailerSoftBounced
{
    use Dispatchable;
    use SerializesModels;

    public $mail_event;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(MailEvent $mail_event)
    {
        $this->mail_event = $mail_event;
    }
}
