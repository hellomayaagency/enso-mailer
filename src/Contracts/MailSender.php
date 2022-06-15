<?php

namespace Hellomayaagency\Enso\Mailer\Contracts;

use Hellomayaagency\Enso\Mailer\Contracts\Campaign;

interface MailSender
{
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
    public function setCampaign(Campaign $campaign);

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
    public function sendToRecipients(array $recipients);

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
    public function getInfoFor($message_id);
}
