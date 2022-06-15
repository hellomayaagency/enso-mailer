<?php

namespace Hellomayaagency\Enso\Mailer\Models;

use Illuminate\Database\Eloquent\Model;
use Yadda\Enso\Crud\Contracts\IsCrudModel as ContractsIsCrudModel;
use Yadda\Enso\Crud\Traits\IsCrudModel;
use Yadda\Enso\Facades\EnsoCrud;

class MailRecipient extends Model implements ContractsIsCrudModel
{
    use IsCrudModel;

    protected $table = 'mailer_recipients';

    protected $fillable = [
        'campaign_id',
        'message_id',
    ];

    /**
     * Gets the campaign about which the recipient was mailed
     *
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function campaign()
    {
        return $this->belongsTo(EnsoCrud::modelClass('mailer_campaign'));
    }

    /**
     * Gets the messages that have been received about this recipients
     * email.
     *
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function messages()
    {
        return $this->hasMany(MailEvent::class, 'campaign_mail_id', 'message_id');
    }

    /**
     * Gets the id of this message
     *
     * @return string
     */
    public function getMessageId()
    {
        return $this->message_id;
    }

    /**
     * Assigns a recipient to a specified campaign, if not already assigned
     *
     * @param string  $message_id
     * @param int $campaign_id
     *
     * @return void
     */
    public static function attachMessageToCampaign($message_id, $campaign_id)
    {
        if (self::where('campaign_id', $campaign_id)->where('message_id', $message_id)->count() === 0) {
            self::create(compact('campaign_id', 'message_id'));
        }
    }
}
