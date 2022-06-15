<?php

namespace Hellomayaagency\Enso\Mailer\Models;

use App;
use Carbon\Carbon;
use EnsoMailer;
use Exception;
use Hellomayaagency\Enso\Mailer\Contracts\Campaign as CampaignContract;
use Hellomayaagency\Enso\Mailer\Contracts\MailSender;
use Hellomayaagency\Enso\Mailer\Exceptions\CampaignStateException;
use Hellomayaagency\Enso\Mailer\Jobs\ScheduleCampaignSend;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;
use Yadda\Enso\Crud\Contracts\FlexibleFieldHandler;
use Yadda\Enso\Crud\Contracts\IsCrudModel as ContractsIsCrudModel;
use Yadda\Enso\Crud\Traits\HasFlexibleFields;
use Yadda\Enso\Crud\Traits\IsCrudModel;
use Yadda\Enso\Facades\EnsoCrud;

class Campaign extends Model implements CampaignContract, ContractsIsCrudModel
{
    use HasFlexibleFields;
    use IsCrudModel;

    protected $table = 'mailer_campaigns';

    protected $fillable = [
        'name',
        'slug',
        'from',
        'subject',
        'from_name',
        'from_email',
        'mail_title',
        'mail_date',
        'mail_body',
        'sent_at',
        'driver',
    ];

    protected $attributes = [
        'mail_body' => '[]',
    ];

    protected $casts = [
        'mail_body' => 'array',
        'mail_date' => 'date',
        'sent_at' => 'date',
    ];

    protected $dates = [
        'mail_date',
        'sent_at',
    ];

    /**
     * Keep a copy of the Audience count so that if it's required multiple
     * times, it isn't re-queried.
     *
     * @var null|int
     */
    protected $audience_user_count;

    /**
     * The audiences this campaign should be / has been sent to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function audiences()
    {
        return $this->belongsToMany(EnsoCrud::modelClass('mailer_audience'), 'mailer_campaign_audience');
    }

    /**
     * Gets a list of recipients to whom this campaign was actually send.
     *
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function recipients()
    {
        return $this->hasMany(MailRecipient::class);
    }

    /**
     * Gets the current stats from the most recently aggregated
     * recipient's message lists.
     *
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function stats()
    {
        return $this->hasOne(CampaignStats::class);
    }

    /**
     * Creates the base query for Campaign Users
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function baseAudienceQuery()
    {
        return App::make('enso-mailer-query');
    }

    /**
     * Gets the base audience query, and then applies the query modifiers for
     * each of the selected Audiences, to provide a definitive list of users.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function generateAudienceQuery()
    {
        $query = $this->baseAudienceQuery();

        // Campaigns with no audiences should return no users
        if ($this->audiences->count() === 0) {
            return $query->whereRaw('false');
        }

        $query->where(function ($sub_query) {
            $this->audiences->each(function ($audience) use ($sub_query) {
                $sub_query->orWhere(function ($audience_query) use ($audience) {
                    return $audience->generateAudienceQuery($audience_query);
                });
            });
        });

        return $query;
    }

    /**
     * Gets the full list of users for this campaign
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAudienceUsers()
    {
        return $this->generateAudienceQuery()->get();
    }

    /**
     * Returns just a count of the Email Addresses to which this
     * campaign will be sent.
     *
     * @return int
     */
    public function getAudienceUsersCount()
    {
        if ($this->audience_user_count === null) {
            $this->audience_user_count = $this->generateAudienceQuery()->count();
        }

        return $this->audience_user_count;
    }

    /**
     * Gets the number of Sends for this Campaign
     *
     * @return int|null
     */
    public function getSendsAttribute()
    {
        return $this->stats ? $this->stats->send : null;
    }

    /**
     * Gets the number of Opens for this Campaign
     *
     * @return int|null
     */
    public function getUniqueOpensAttribute()
    {
        return $this->stats ? $this->stats->unique_open : null;
    }

    /**
     * Gets the number of Opens for this Campaign
     *
     * @return int|null
     */
    public function getUniqueClicksAttribute()
    {
        return $this->stats ? $this->stats->unique_click : null;
    }

    /**
     * Gets the number of Opens for this Campaign
     *
     * @return int|null
     */
    public function getFailedSendsAttribute()
    {
        if (is_null($this->stats)) {
            return null;
        }

        return (int) $this->stats->soft_bounce + (int) $this->hard_bounce + (int) $this->reject;
    }

    /**
     * Gets the Campaign Name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the driver on this Campaign to either the provided
     * value or to the current value as set in the config.
     *
     * Does not persist this information to the database.
     *
     * @param string $driver
     *
     * @return self
     */
    public function setDriver($driver = null)
    {
        $this->driver = $driver ?? config('enso.mailer.driver');

        return $this;
    }

    /**
     * Sets and saves the driver, either to the provided value or
     * to the current value as set in the config.
     *
     * @param string $driver
     *
     * @return self
     */
    public function updateDriver($driver = null)
    {
        $this->setDriver($driver)->save();

        return $this;
    }

    /**
     * Returns the driver that was used to send this campaign.
     *
     * If it has not yet been sent, it should be null
     *
     * @return string|null
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * Gest the Campaign Subject.
     *
     * @return string
     */
    public function getSubject()
    {
        // @todo - better fallback subject
        return $this->subject ?? 'Hello!';
    }

    /**
     * Gets the fallback email address that this should be sent from
     *
     * @return void
     */
    public static function getSenderNameFallback()
    {
        if (app('ensosettings')) {
            $from_name = app('ensosettings')->get('site-name');
        }

        if (empty($from_name)) {
            $from_name = config('mail.from.name', 'Not set, please contact support');
        }

        return $from_name;
    }

    /**
     * Gets the fallback email address that this should be sent from
     *
     * @return void
     */
    public static function getSenderEmailFallback()
    {
        if (app('ensosettings')) {
            $from_email = app('ensosettings')->get('administrator-email');
        }

        if (empty($from_email)) {
            $from_email = config('mail.from.address', 'Not set, please contact support');
        }

        return $from_email;
    }

    /**
     * Gets the name of the template that should be used to render this campaign's email
     *
     * @return string
     */
    public function getEmailTemplate()
    {
        return 'enso-crud::mailer_email.templates.mailer-template';
    }

    /**
     * Checks wether or not this Campaign has a set date
     *
     * @return bool
     */
    public function hasMailableTitle()
    {
        return ! empty($this->mail_title);
    }

    /**
     * Gets the title of this Campaign's email.
     *
     * @return string
     */
    public function getMailableTitle()
    {
        return $this->mail_title;
    }

    /**
     * Checks wether or not this Campaign has a set date
     *
     * @return bool
     */
    public function hasMailableDate()
    {
        return ! empty($this->mail_date);
    }

    /**
     * Gets the date set on this Campaign, in the specificed format
     *
     * @param string $format
     *
     * @return string
     */
    public function getMailableDate($format = 'jS M Y')
    {
        if (! $this->hasMailableDate()) {
            return '';
        }

        return $this->mail_date->format($format);
    }

    /**
     * Gets the unpacked data for the body of this email.
     *
     * @return \Illuminate\Support\Collection
     */
    public function unpackedEmailBody()
    {
        $row_specs = EnsoCrud::config('mailer_campaign')
            ->getEditForm()
            ->getSection('content')
            ->getField('mail_body')
            ->getRowSpecs();

        $handler = App::make(FlexibleFieldHandler::class);

        $handler->loadData($this->mail_body, '');

        return $handler->getRows()->map(function ($flexible_row) use ($row_specs) {
            return \Yadda\Enso\Utilities\Helpers::getConcreteClass(
                get_class($row_specs->keyBy->getName()->get($flexible_row->getType()))
            )::unpack($flexible_row);
        });

        return EnsoCrud::config('mailer_campaign')::getRowSpecContent('mail_body', $this->mail_body);
    }

    /**
     * Gets the rendered version of this Campaign
     *
     * @param $inline_css
     *
     * @return string
     */
    public function getRenderedEmail($inline_css = true)
    {
        $raw_html = view($this->getEmailTemplate(), [
            'mailable' => $this,
        ])->render();

        if ($inline_css) {
            $html = (new CssToInlineStyles())->convert(
                $raw_html,
                file_get_contents(public_path('/enso/css/enso-mail.css'))
            );
        } else {
            $html = $raw_html;
        }

        return $html;
    }

    /**
     * Sends the campaign, using the provided sender
     *
     * @param MailSender $sender
     *
     * @return bool
     */
    public function send()
    {
        $sender = EnsoMailer::getSender();

        try {
            $this->updateDriver(config('enso.mailer.driver'));

            $sender->setCampaign($this);

            $this->sendToAudience($sender);
        } catch (Exception $e) {
            Log::error($e);

            return false;
        }

        $this->markAsSent();

        return true;
    }

    /**
     * Checks to see whether this Campaign has been sent
     *
     * @return bool
     */
    public function hasBeenSent()
    {
        return ! is_null($this->sent_at);
    }

    /**
     * Marks this Campaign as having been sent.
     *
     * Optionally, this can be set to a specific date
     *
     * @param \Carbon\Carbon $date
     *
     * @return void
     */
    public function markAsSent(Carbon $date = null)
    {
        if ($this->hasBeenSent()) {
            throw new CampaignStateException('This Campaign has already been sent!');
        }

        $this->update([
            'sent_at' => $date ?? Carbon::now(),
        ]);
    }

    /**
     * Gets the appropriate Parser for this MailEvent
     *
     * @return MailParser
     */
    public function getParser()
    {
        if (is_null($this->getDriver())) {
            return;
        }

        $parser_class = config('enso.mailer.drivers.' . $this->getDriver() . '.parser', null);

        if (is_null($parser_class)) {
            return;
        }

        return new $parser_class();
    }

    /**
     * Finds the first message to get response for this campaign, to get the
     * driver from. As Campaigns can only be sent once, they can only be
     * sent via one driver, so this is adequate.
     *
     * Then use the parser to recaluculate the stats for this campaign.
     *
     * @return void
     */
    public function recalculateStats()
    {
        $parser = $this->getParser();

        if ($parser) {
            $parser->calculateStatsFor($this);
        }
    }

    /**
     * Test send this Campaign.
     *
     * This differs from a full send, in that it switches out the Mailer 'Tag'
     * so that we're not recording events against the email, and doesn't mark
     * the email as 'sent'
     *
     * @param array $email_addresses
     *
     * @return bool
     */
    public function scheduleSend(Carbon $send_at)
    {
        try {
            dispatch((new ScheduleCampaignSend($this))->delay($send_at)->onQueue('mailer'));
        } catch (Exception $e) {
            Log::error($e);

            return false;
        }

        $this->markAsSent($send_at);

        return true;
    }

    /**
     * Fetches the current status for each recipient of this campaign
     * from it's sender
     *
     * @return CampaignStats
     */
    public function queryCurrentStatus()
    {
        try {
            $sender = EnsoMailer::getSender();
        } catch (CampaignStateException $e) {
            throw $e;
        } catch (Exception $e) {
            Log::error($e);

            throw new CampaignStateException('Unable to get current Status: Sender Not Available');
        }

        try {
            $this->recipients->each(function ($recipient) use ($sender) {
                $sender->getInfoFor($recipient->getMessageId());
            });

            $this->recalculateStats();
        } catch (Exception $e) {
            Log::error($e);

            throw new CampaignStateException('Unable to get current Status: Fetching data failed');
        }

        return $this->stats;
    }

    /**
     * Sends the Campaign, via the provided Sender, to
     * the recipients list generated by the Audience
     * builder.
     *
     * @param MailSender $sender
     *
     * @return void
     */
    protected function sendToAudience(MailSender $sender)
    {
        if ($this->getAudienceUsersCount() <= 0) { // No recipients = no-op
            return;
        }

        $this->generateAudienceQuery()->chunk(1000, function ($audience_chunk) use ($sender) {
            $sender->sendToRecipients($this->mapUsers($audience_chunk));
        });
    }

    /**
     * Maps the Users into the correct format for sending. The minimum requirement
     * for this is for each user to have at least the 'email' key
     * populated, and optionally you can provide a 'name' key.
     *
     * @param \Illuminate\Support\Collection $users
     *
     * @return array
     */
    protected function mapUsers($users)
    {
        return $users->map(function ($user) {
            return [
                'email' => $user->email,
            ];
        })->toArray();
    }

    /**
     * Test send this Campaign.
     *
     * This differs from a full send, in that it switches out the Mailer 'Tag'
     * so that we're not recording events against the email, and doesn't mark
     * the email as 'sent'
     *
     * @param array $email_addresses
     *
     * @return bool
     */
    public function testSend($email_addresses)
    {
        $sender = EnsoMailer::getSender();

        try {
            $this->updateDriver(config('enso.mailer.driver'));

            $sender->setCampaign($this);
            $sender->setTag('EnsoMailerPreview'); // Anything not the EnsoMailer tag will do

            $sender->sendToRecipients(array_map(function ($email_address) {
                return ['email' => $email_address];
            }, $email_addresses));
        } catch (Exception $e) {
            Log::error($e);

            return false;
        }

        return true;
    }
}
