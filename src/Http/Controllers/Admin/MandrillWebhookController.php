<?php

namespace Hellomayaagency\Enso\Mailer\Http\Controllers\Admin;

use EnsoMailer;
use Exception;
use Hellomayaagency\Enso\Mailer\Contracts\Campaign;
use Hellomayaagency\Enso\Mailer\Contracts\MandrillWebhookController as MandrillWebhookControllerContract;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Log;
use Response;

class MandrillWebhookController implements MandrillWebhookControllerContract
{
    /**
     * This route exists solely to let Mandrill know that this is a
     * valid webhook target
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        return \Response::make('', 200);
    }

    /**
     * Store the mandrill events that come in from the webhook
     * as individual message events, so that they can be used
     * to aggregate a 'current state' for their respective
     * campaigns.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! $this->authenticateRequest($request)) {
            Log::error('Mandrill webhook request received with bad credentials');

            return Response::make('', 200);
        }

        DB::beginTransaction();

        try {
            $message_content = $request->get('mandrill_events');
            $messages = collect(json_decode($message_content));

            $parser = EnsoMailer::getParser('mandrill');

            $messages = $this->filterMessages($messages)->map(function ($message) use ($parser) {
                return $parser->createMailEvent($message->msg, $message->event, $message->ts);
            })->filter();

            DB::commit();
        } catch (Exception $e) {
            Log::error($e);
            DB::rollback();

            $messages = collect([]);
        }

        /**
         * Keep the recalculation logic separate so that the webhook
         * request data can still be commited to the databse and can
         * be responded to with a 200 response.
         */
        try {
            if ($messages->count()) {
                $this->recalculateCampaignStats($messages);
            }
        } catch (Exception $e) {
            Log::error($e);
        }

        return Response::make('', 200);
    }

    /**
     * Tests that the request has been sent by Mandrill.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     */
    protected function authenticateRequest(Request $request)
    {
        $webhook_key = config('enso.mailer.drivers.mandrill.webhook_key');
        $webhook_url = config('enso.mailer.drivers.mandrill.webhook_url', null) ?? route('admin.webhooks.mailer');

        $generated = $this->generateSignature($webhook_key, $webhook_url, $request->all());
        $sent = $request->header('X-Mandrill-Signature');

        return $generated === $sent;
    }

    /**
     * Filters a Collection of messages to only return those that should be stored in the database.
     *
     * @param \Illuminate\Support\Collection $messages
     *
     * @return \Illuminate\Support\Collection
     */
    protected function filterMessages(Collection $messages)
    {
        return $messages->filter(function ($message) {
            $message_tags = data_get($message, 'msg.tags', []);
            $mailer_tag = config('enso.mailer.drivers.mandrill.identifier_tag', 'EnsoMailer');

            $campaign_id = data_get($message, 'msg.metadata.campaign_id', null);

            return (in_array($mailer_tag, $message_tags) && $campaign_id);
        });
    }

    /**
     * Generates a base64-encoded signature for a Mandrill webhook request.
     *
     * @param string $webhook_key webhook's authentication key
     * @param string $url         webhook url
     * @param array  $params      request's POST parameters
     *
     * @return string
     */
    protected function generateSignature($webhook_key, $url, $params)
    {
        $signed_data = $url;

        ksort($params);

        foreach ($params as $key => $value) {
            $signed_data .= $key;
            $signed_data .= $value;
        }

        return base64_encode(hash_hmac('sha1', $signed_data, $webhook_key, true));
    }

    /**
     * Recalculate the stats for each campaign listed in the webhook events.
     *
     * @param \Illuminate\Support\Collection $messages
     *
     * @return void
     */
    protected function recalculateCampaignStats($messages)
    {
        $campaign_ids = $messages->map(function ($message) {
            return data_get($message, 'payload.metadata.campaign_id', null);
        })->filter();

        if ($campaign_ids->count() === 0) {
            return;
        }

        $campaigns = (App::make(Campaign::class))::find($campaign_ids);

        $campaigns->each(function ($campaign) {
            $campaign->recalculateStats();
        });
    }
}
