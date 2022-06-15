<?php

namespace Hellomayaagency\Enso\Mailer\Mail;

use Hellomayaagency\Enso\Mailer\Contracts\Campaign;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Mail extends Mailable
{
    use Queueable;
    use SerializesModels;

    protected $campaign;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Campaign $campaign)
    {
        $this->campaign = $campaign;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // Get site-specific information
        $this->from('andrew@maya.agency');
        $this->subject($this->campaign->getSubject());

        return $this->view($this->campaign->getEmailTemplate());
    }
}
