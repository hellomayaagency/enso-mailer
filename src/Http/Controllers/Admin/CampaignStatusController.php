<?php

namespace Hellomayaagency\Enso\Mailer\Http\Controllers\Admin;

use Alert;
use Carbon\Carbon;
use Exception;
use Hellomayaagency\Enso\Mailer\Contracts\CampaignStatusController as CampaignStatusControllerContract;
use Hellomayaagency\Enso\Mailer\Exceptions\CampaignSendingException;
use Hellomayaagency\Enso\Mailer\Exceptions\CampaignStateException;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\MessageBag;
use Yadda\Enso\Facades\EnsoCrud;

class CampaignStatusController implements CampaignStatusControllerContract
{
    use ValidatesRequests;

    /**
     * Gets an array of recipients from the request, filtering out invalid
     * email addresses.
     *
     * @return array
     */
    protected function getPreviewRecipients(Request $request): array
    {
        $this->validate($request, ['preview_recipients' => 'required|string']);

        return array_filter(
            array_map(function ($email_address) {
                return trim($email_address);
            }, explode(',', $request->get('preview_recipients'))),
            function ($email_address) {
                $validator = Validator::make(['email' => $email_address], ['email' => 'email']);
                return !$validator->fails();
            }
        );
    }

    /**
     * Gets the Schedule Date from the Request
     *
     * @param Request $request
     *
     * @return Carbon
     */
    private function getScheduleDate(Request $request): Carbon
    {
        $this->validate($request, [
            'send_campaign_at_date' => 'required|date_format:d M Y',
            'send_campaign_at_hours' => 'required|numeric',
            'send_campaign_at_minutes' => 'sometimes|nullable|numeric',
        ]);

        $date = Carbon::createfromFormat('d M Y', $request->get('send_campaign_at_date'));

        $date->setTime(
            $request->get('send_campaign_at_hours', 0),
            $request->get('send_campaign_at_minutes', 0),
            0
        );

        return $date;
    }

    /**
     * Creates a Response with Validation-style errors
     *
     * @return RedirectResponse
     */
    protected function makeGeneralErrorResponse($key, $message): RedirectResponse
    {
        $errors = new MessageBag([$key => [$message]]);

        Session::flash('errors', $errors);

        return Redirect::back()->withInput();
    }

    /**
     * Sends the Campaign email to the provided email addresses, without
     * locking the campaign or sending the MailTag to start triggering
     * responses from Mandrill.
     *
     * @param Request $request
     * @param integer $campaign_id
     *
     * @return RedirectResponse
     */
    public function previewSend(Request $request, $campaign_id): RedirectResponse
    {
        $campaign = EnsoCrud::modelClass('mailer_campaign')::findOrFail($campaign_id);

        $email_addresses = $this->getPreviewRecipients($request);

        if (count($email_addresses) < 1) {
            return $this->makeGeneralErrorResponse(
                'preview_general',
                'No valid email addresses provided for Preview Send. Please provide one or more email addresses, comma separated is providing more than one.'
            );
        }

        try {
            $campaign->testSend($email_addresses);
            Alert::success('A Preview of your Campaign has successfully been sent to ' . implode(', ', $email_addresses));
        } catch (CampaignSendingException $e) {
            Alert::error($e->getMessage());
        } catch (CampaignStateException $e) {
            Alert::error($e->getMessage());
        } catch (Exception $e) {
            Log::error($e);
            Alert::error('There was an error processing this Campaign. It has not been sent!');
        }

        return Redirect::route('admin.mailer.campaigns.status.show', $campaign->getKey());
    }

    /**
     * Gets the current stats for the given campaign from it's sender
     *
     * @param integer $campaign_id
     *
     * @return Redirect
     */
    public function refresh($campaign_id)
    {
        $campaign = EnsoCrud::modelClass('mailer_campaign')::findOrFail($campaign_id);

        try {
            $campaign->queryCurrentStatus();

            Alert::success('Your campaign has pulled it\'s current state');
        } catch (CampaignStateException $e) {
            Alert::error($e->getMessage());
        } catch (Exception $e) {
            Log::error($e);
            Alert::error('There was an error processing this Campaign. Stats have not been updated!');
        }

        return redirect()->route('admin.mailer.campaigns.status.show', $campaign->getKey());
    }

    /**
     * Sends the Campaign email to the provided email addresses, without
     * locking the campaign or sending the MailTag to start triggering
     * responses from Mandrill.
     *
     * @param Request $request
     * @param integer $campaign_id
     *
     * @return RedirectResponse
     */
    public function scheduleSend(Request $request, $campaign_id): RedirectResponse
    {
        $campaign = EnsoCrud::modelClass('mailer_campaign')::findOrFail($campaign_id);

        if ($campaign->hasNoAudience()) {
            Alert::error('This Scheduled Campaign will have no recipients.');

            return redirect()->back()->withInput();
        }

        try {
            $schedule_date = $this->getScheduleDate($request);
            $success = $campaign->scheduleSend($schedule_date);

            if ($success) {
                Alert::success('Your Campaign has been queued up to send at ' . $schedule_date->format('jS Y M, H:i'));

                return redirect()->route('admin.mailer.campaigns.status.show', $campaign->getKey());
            }
        } catch (Exception $e) {
            Log::error($e);
        }

        Alert::error('There was a problem scheduling this Campaign. Please try again, or contact support.');

        return Redirect::back()->withInput();
    }

    /**
     * Sends the given campaign to it's intended recipients
     *
     * @param integer $campaign_id
     *
     * @return RedirectResponse
     */
    public function send($campaign_id): RedirectResponse
    {
        $campaign = EnsoCrud::modelClass('mailer_campaign')::findOrFail($campaign_id);

        try {
            $campaign->send();

            Alert::success('Your Campaign has successfully been sent!');
        } catch (CampaignSendingException $e) {
            Alert::error($e->getMessage());
        } catch (CampaignStateException $e) {
            Alert::error($e->getMessage());
        } catch (Exception $e) {
            Log::error($e);
            Alert::error('There was an error processing this Campaign. It has not been sent!');
        }

        return Redirect::route('admin.mailer.campaigns.status.show', $campaign->getKey());
    }

    /**
     * Shows the statistics page for the given Campaign
     *
     * @param integer $campaign_id
     *
     * @return \Illuminate\View\View
     */
    public function show($campaign_id): \Illuminate\View\View
    {
        $campaign = EnsoCrud::modelClass('mailer_campaign')::findOrFail($campaign_id);
        $campaign_config = EnsoCrud::config('mailer_campaign');

        return View::make($campaign_config->getCrudView('status.show'), [
            'item' => $campaign,
            'crud' => $campaign_config,
        ]);
    }
}
