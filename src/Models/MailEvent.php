<?php

namespace Hellomayaagency\Enso\Mailer\Models;

use EnsoMailer;
use Illuminate\Database\Eloquent\Model;
use Yadda\Enso\Crud\Contracts\IsCrudModel as ContractsIsCrudModel;
use Yadda\Enso\Crud\Traits\IsCrudModel;

class MailEvent extends Model implements ContractsIsCrudModel
{
    use IsCrudModel;

    protected $table = 'mailer_events';

    protected $fillable = [
        'driver',
        'campaign_mail_id',
        'type',
        'payload',
        'triggered_at',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    protected $dates = [
        'triggered_at',
    ];

    /**
     * Gets the Recipient entry to which this message relates
     *
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function recipient()
    {
        return $this->belongsTo(MailRecipient::class, 'campaign_mail_id', 'message_id');
    }

    /**
     * Gets the Message identifier
     *
     * @return string
     */
    public function getMessageId()
    {
        return $this->campaign_mail_id;
    }

    /**
     * Gets the Driver that this Message was received with
     *
     * @return void
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * Gets the appropriate Parser for this MailEvent
     *
     * @return \Hellomayaagency\Enso\Mailer\Contracts\MailParser
     */
    public function getParser()
    {
        try {
            return EnsoMailer::getParser($this->getDriver());
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Gets the type of Mail event
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Gets the message payload
     *
     * @return array
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * Gets the email address of the recipient
     * from the payload
     *
     * @return string
     */
    public function getRecipientEmail()
    {
        return data_get($this, 'payload.email', null);
    }

    /**
     * Gets the sender for this email.
     *
     * @return null|string
     */
    public function getSender()
    {
        return data_get($this, 'payload.sender', null);
    }

    /**
     * Triggers the associated event for this MailEvent
     *
     * @return void
     */
    public function triggerEvent()
    {
        $parser = $this->getParser();

        if ($parser) {
            $parser->triggerEventFor($this);
        }
    }
}
