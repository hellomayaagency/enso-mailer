<?php

namespace Hellomayaagency\Enso\Mailer\Drivers;

use EnsoMailer;
use Exception;
use Hellomayaagency\Enso\Mailer\Contracts\Campaign;
use Hellomayaagency\Enso\Mailer\Contracts\MailSender;
use Hellomayaagency\Enso\Mailer\Exceptions\CampaignSendingException;
use Hellomayaagency\Enso\Mailer\Exceptions\CampaignStateException;
use Hellomayaagency\Enso\Mailer\Models\MailEvent;
use Hellomayaagency\Enso\Mailer\Models\MailRecipient;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Yadda\Enso\Facades\EnsoCrud;

class MandrillSender implements MailSender
{
    protected $mandrill;

    protected $tag;

    protected $message_body;

    public function __construct()
    {
        $api_key = config('enso.mailer.drivers.mandrill.api_key');

        // $this->mandrill = new Mandrill($api_key);
        $this->mandrill = new \MailchimpTransactional\ApiClient();
        $this->mandrill->setApiKey($api_key);

        $this->tag = config('enso.mailer.drivers.mandrill.identifier_tag', 'EnsoMailer');
    }

    /**
     * Sets the Campaign that this Sender should be sending,
     * and performs any static logic that might be
     * required more than once if it will be required on
     * each send.
     *
     * @param Campaign $campaign
     *
     * @return void
     */
    public function setCampaign(Campaign $campaign)
    {
        $this->campaign = $campaign;

        $this->generateCampaignEmail();
    }

    /**
     * Send Campaign to a set of recipients. This should be
     * an array of arrays with at least the 'email' key,
     * and optionally with the name key.
     *
     * [
     *     [
     *         'email' => 'example@example.com',
     *     ],
     * ]
     *
     * @param array $recipients
     *
     * @return void
     */
    public function sendToRecipients(array $recipients)
    {
        $message = $this->message_body;
        $message['to'] = $recipients;

        $response = $this->mandrill->messages->send([
            'message' => $message,
        ], true);

        try {
            $this->handleResponse($response);
        } catch (CampaignSendingException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new CampaignSendingException(
                'An unexpected error has occured sending this Campaign. Please contact an admin'
            );
        }
    }

    /**
     * Gets the most recent state for a given message id. In typical use, this shouldn't
     * be required as events get stored and the most recent one is used to build stats.
     *
     * However, for cases when the message fails to save, we need a way to pull the 'current'
     * most recent state for any given message.
     *
     * @param string $message_id
     *
     * @return MailEvent|null
     */
    public function getInfoFor($message_id)
    {
        $response = $this->mandrill->messages->info(['id' => $message_id]);

        $parser = EnsoMailer::getParser('mandrill');

        try {
            $mail_event = $parser->createMailEvent($response);
        } catch (Exception $e) {
            Log::error($e);

            throw new CampaignStateException('Unable to collected current information for campaign message');
        }

        return $mail_event ?? null;
    }

    /**
     * Creates the Body for the message that will be sent, based on
     * the stored campaign.
     *
     * @return void
     */
    protected function generateCampaignEmail()
    {
        $from_email = empty($this->campaign->from_email) ?
            EnsoCrud::modelClass('mailer_campaign')::getSenderEmailFallback() : $this->campaign->from_email;

        $from_name = empty($this->campaign->from_name) ?
            EnsoCrud::modelClass('mailer_campaign')::getSenderNameFallback() : $this->campaign->from_name;

        $this->message_body = [
            'html' => $this->campaign->getRenderedEmail(),
            'subject' => $this->campaign->getSubject(),
            'from_email' => $from_email,
            'from_name' => $from_name,
            'track_opens' => true,
            'track_clicks' => true,
            'inline_css' => true,
            'preserve_recipients' => false,
            'tags' => [
                $this->tag,
            ],
            'metadata' => [
                'campaign_id' => $this->campaign->getKey(),
            ],
        ];
    }

    /**
     * Gets the Key of the campaign that this sender has been assigned
     *
     * @return int
     */
    protected function campaignKey()
    {
        return $this->campaign ? $this->campaign->getKey() : null;
    }

    /**
     * Handles the response that it received from Mandrill.
     * This will save the 'queued' records as recipients against
     * a campaign, which will later have events assigned to.
     *
     * @param array $response
     *
     * @return void
     */
    protected function handleResponse($response)
    {
        if ($response instanceof GuzzleHttp\Exception\ServerException) {
            throw new CampaignSendingException(
                'An unexpected error has occured sending this Campaign. Please contact an admin',
            );
        }

        try {
            // Error responses return with a code, whereas success responses do not
            if (isset($response['code'])) {
                throw new CampaignSendingException(
                    'Sending Campaign Failed, the Email provider may be having issues. Please try again later'
                );
            }

            if (! $this->campaignKey()) {
                throw new CampaignSendingException(
                    'The Campaign sender can\'t find the id of the campaign that is should send'
                );
            }

            // Associate each sent-email with the campaign, so that we can
            // get an accurate count of users to whom the campaign attempted to send.
            collect($response)->each(function ($recipient) {
                $message_id = data_get($recipient, '_id', null);

                if (! $message_id) {
                    throw new CampaignSendingException(
                        'Campaign Sending response was not an error, but did not contain a a message identifier'
                    );
                } else {
                    MailRecipient::attachMessageToCampaign($message_id, $this->campaignKey());
                }
            });
        } catch (\Exception $e) {
            Log::error($e);

            throw $e;
        }
    }

    /**
     * Sets the current Tag that this Sender will use to tag messages with.
     *
     * @param string $new_tag
     *
     * @return void
     */
    public function setTag(string $new_tag): void
    {
        $this->tag = $new_tag;

        $this->generateCampaignEmail();
    }
}
