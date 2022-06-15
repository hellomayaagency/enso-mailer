<?php

namespace Hellomayaagency\Enso\Mailer\Events;

use Hellomayaagency\Enso\Mailer\Models\MailEvent;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EnsoMailerOpened
{
    use Dispatchable, SerializesModels;

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
