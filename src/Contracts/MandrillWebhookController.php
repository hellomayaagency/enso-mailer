<?php

namespace Hellomayaagency\Enso\Mailer\Contracts;

use Illuminate\Http\Request;

interface MandrillWebhookController
{
    /**
     * This route exists solely to let Mandrill know that this is a
     * valid webhook target
     *
     * @param Request $request
     *
     * @return Response
     */
    public function show(Request $request);

    /**
     * Store the mandrill events that come in from the webhook
     * as individual message events, so that they can be used
     * to aggregate a 'current state' for their respective
     * campaigns.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function store(Request $request);
}
