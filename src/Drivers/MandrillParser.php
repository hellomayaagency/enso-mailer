<?php

namespace Hellomayaagency\Enso\Mailer\Drivers;

use Carbon\Carbon;
use Exception;
use Hellomayaagency\Enso\Mailer\Contracts\Campaign;
use Hellomayaagency\Enso\Mailer\Contracts\MailParser;
use Hellomayaagency\Enso\Mailer\Events\EnsoMailerClicked;
use Hellomayaagency\Enso\Mailer\Events\EnsoMailerHardBounced;
use Hellomayaagency\Enso\Mailer\Events\EnsoMailerMarkedAsSpam;
use Hellomayaagency\Enso\Mailer\Events\EnsoMailerOpened;
use Hellomayaagency\Enso\Mailer\Events\EnsoMailerRejected;
use Hellomayaagency\Enso\Mailer\Events\EnsoMailerSent;
use Hellomayaagency\Enso\Mailer\Events\EnsoMailerSoftBounced;
use Hellomayaagency\Enso\Mailer\Events\EnsoMailerUserUnsubscribed;
use Hellomayaagency\Enso\Mailer\Exceptions\CampaignStatsException;
use Hellomayaagency\Enso\Mailer\Models\CampaignStats;
use Hellomayaagency\Enso\Mailer\Models\MailEvent;
use Hellomayaagency\Enso\Mailer\Models\MailRecipient;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MandrillParser implements MailParser
{
    const MANDRILL_API_STATES = [
        'sent',
        'deferred',
        'rejected',
        'spam',
        'unsub',
        'bounced',
        'soft-bounced',
        'manual', // Added for manual calls that only recieve the payload
    ];

    const MANDRILL_API_EVENTS = [
        'send' => EnsoMailerSent::class,
        'deferral' => null,
        'hard_bounce' => EnsoMailerHardBounced::class,
        'soft_bounce' => EnsoMailerSoftBounced::class,
        'open' => EnsoMailerOpened::class,
        'click' => EnsoMailerClicked::class,
        'spam' => EnsoMailerMarkedAsSpam::class,
        'unsub' => EnsoMailerUserUnsubscribed::class,
        'reject' => EnsoMailerRejected::class,
    ];

    protected $supported_properties = [
        'send',
        'open',
        'unique_open',
        'click',
        'unique_click',
        'hard_bounce',
        'soft_bounce',
        'spam',
        'unsub',
        'reject',
    ];

    /**
     * Calculates the Stats for the current campaign, based on the
     * current messages stored for it.
     *
     * Can accept either a Campaign object of the id of one.
     *
     * @param mixed $campaign
     *
     * @return
     */
    public function calculateStatsFor($campaign)
    {
        if (!(is_object($campaign) && $campaign instanceof Campaign)) {
            $campaign = Campaign::find($campaign);
        }

        if (is_null($campaign)) {
            throw new CampaignStatsException('Unable to find campaign to genereate stats for');
        }

        $stats = $campaign->stats ?? CampaignStats::create(['campaign_id' => $campaign->getKey()]);

        DB::beginTransaction();

        try {
            $stats->reset($this->supported_properties);

            MailRecipient::where('campaign_id', $campaign->getKey())->with(['messages' => function ($query) {
                /**
                 * Manually fetching state using 'Refresh' stats provides the same 'triggered_at'
                 * timestamp as the most recent webhook event, so also need to sort by 'created_at'
                 * for reliable outcome.
                 */
                return $query->orderBy('triggered_at', 'DESC')->orderBy('created_at', 'DESC');
            }])->chunk(50, function ($recipients) use ($stats) {
                $recipients->each(function ($recipient) use ($stats) {
                    /**
                     * A message contains all previous state, so only the most recent
                     * Message for this recipient is required.
                     */
                    $most_recent = $recipient->messages->first();

                    if (!is_null($most_recent)) {
                        $this->applyMessage($most_recent, $stats);
                    }

                    $stats->send += 1;
                });
            });

            $stats->save();

            DB::commit();
        } catch (Exception $e) {
            Log::error($e);

            DB::rollback();
        }

        return $stats;
    }

    /**
     * Validates the minimum data that a message should have
     * to be considered a 'complete' message
     *
     * @param mixed $message
     *
     * @return boolean
     */
    public function validateMessage($message)
    {
        return (data_get($message, '_id', null)
            && data_get($message, 'ts', null));
    }

    /**
     * Applies a single message to cumulative stats object.
     *
     * @param MailEvent $message
     * @param CampaignStats $stats
     *
     * @return void
     */
    protected function applyMessage(MailEvent $message, CampaignStats $stats)
    {
        if (!$this->hasValidState($this->getMessageState($message))) {
            Log::error('Mandrill Message with invalid state: ' . $message->getKey());
            return;
        }

        $stats->open += $this->getMessageOpenCount($message);
        $stats->unique_open += $this->messageHasOpens($message) ? 1 : 0;
        $stats->click += $this->getMessageClickCount($message);
        $stats->unique_click += $this->messageHasClicks($message) ? 1 : 0;
        $stats->hard_bounce += (int) $this->messageWasHardBounced($message);
        $stats->soft_bounce += (int) $this->messageWasSoftBounced($message);
        $stats->spam += (int) $this->messageWasMarkedAsSpam($message);
        $stats->unsub += (int) $this->messageRecipientUnsubscribed($message);
        $stats->reject += (int) $this->messageWasRejected($message);
    }

    /**
     * Creates a Mail Event from the given message, potentially attaching it to
     * the Campaign if it isn't already attached.
     *
     * @param mixed $message
     * @param string $type
     * @param string $timestamp
     *
     * @return MailEvent
     */
    public function createMailEvent($message, $type = null, $timestamp = null)
    {
        if (!self::validateMessage($message)) {
            return null;
        }

        $mail_event = MailEvent::create([
            'driver' => 'mandrill',
            'campaign_mail_id' => data_get($message, '_id'),
            'type' => $type ?? 'manual',
            'payload' => $message,
            'triggered_at' => Carbon::createFromTimestampUTC($timestamp ?? data_get($message, 'ts')),
        ]);

        $campaign_id = data_get($message, 'msg.metadata.campaign_id', null);
        $message_id = $mail_event->getMessageId();

        if ($campaign_id && $message_id) {
            MailRecipient::attachMessageToCampaign($message_id, $campaign_id);

            $this->triggerEventFor($mail_event);
        }

        return $mail_event;
    }

    /**
     * Triggers the correct event for the given MailEvent, based on it's
     * type.
     *
     * @param MailEvent $message
     *
     * @return void
     */
    public function triggerEventFor(MailEvent $message)
    {
        if (
            array_key_exists($message->getType(), self::MANDRILL_API_EVENTS)
            && !is_null(self::MANDRILL_API_EVENTS[$message->getType()])
        ) {
            try {
                $event_class = self::MANDRILL_API_EVENTS[$message->getType()];
                event(new $event_class($message));
            } catch (Exception $e) {
                Log::error($e);
            }
        }
    }

    /**
     * Gets the recipient for this email.
     *
     * @param MailEvent $message
     *
     * @return null|string
     */
    public function getMessageRecipient($message)
    {
        return data_get($message->getPayload(), 'email', null);
    }

    /**
     * Checks whether this Mail event has any opens
     *
     * @param MailEvent $message
     *
     * @return boolean
     */
    public function messageHasOpens($message)
    {
        return $this->getMessageOpenCount($message) > 0;
    }

    /**
     * Gets the opens array for this event
     *
     * @param MailEvent $message
     *
     * @return integer
     */
    public function getMessageOpenCount($message)
    {
        if ($message->getType() === 'manual') {
            return Arr::get($message->getPayload(), 'opens', 0);
        } else {
            return count(Arr::get($message->getPayload(), 'opens', []));
        }
    }

    /**
     * Checks whether this Mail event has any clicks
     *
     * @param MailEvent $message
     *
     * @return boolean
     */
    public function messageHasClicks($message)
    {
        return $this->getMessageClickCount($message) > 0;
    }

    /**
     * Gets the clicks array for this event
     *
     * @param MailEvent $message
     *
     * @return integer
     */
    public function getMessageClickCount($message)
    {
        if ($message->getType() === 'manual') {
            return Arr::get($message->getPayload(), 'clicks', 0);
        } else {
            return count(Arr::get($message->getPayload(), 'clicks', []));
        }
    }

    /**
     * Gets the state of the Message.
     *
     * @param MailEvent $message
     *
     * @return string|null
     */
    public function getMessageState($message)
    {
        return Arr::get($message->getPayload(), 'state', null);
    }

    /**
     * Gets the clicks array for this event
     *
     * @return boolean
     */
    public function messageWasHardBounced($message)
    {
        return $this->getMessageState($message) === 'bounced';
    }

    /**
     * Checks whether this message was soft-bounced
     *
     * @param MailEvent $message
     *
     * @return boolean
     */
    public function messageWasSoftBounced($message)
    {
        return $this->getMessageState($message) === 'soft-bounced';
    }

    /**
     * Checks whether the recipient marked this message as spam
     *
     * @param MailEvent $message
     *
     * @return boolean
     */
    public function messageWasMarkedAsSpam($message)
    {
        return $this->getMessageState($message) === 'spam';
    }

    /**
     * Checks whether this message caused the recipient
     * to unsubscribe
     *
     * @param MailEvent $message
     *
     * @return boolean
     */
    public function messageRecipientUnsubscribed($message)
    {
        return $this->getMessageState($message) === 'unsub';
    }

    /**
     * Checks whether this message was rejected for sending.
     *
     * @param MailEvent $message
     *
     * @return boolean
     */
    public function messageWasRejected($message)
    {
        return $this->getMessageState($message) === 'rejected';
    }

    /**
     * Checks to see whether a given state is in the list of states
     * that the Mandrill API could/should return
     *
     * @param string $state
     *
     * @return boolean
     */
    protected function hasValidState($state)
    {
        return in_array($state, static::MANDRILL_API_STATES);
    }
}
