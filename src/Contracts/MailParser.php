<?php

namespace Hellomayaagency\Enso\Mailer\Contracts;

use Hellomayaagency\Enso\Mailer\Models\MailEvent;

interface MailParser
{
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
    public function calculateStatsFor($campaign);

    /**
     * Validates the minimum data that a message should have
     * to be considered a 'complete' message
     *
     * @param mixed $message
     *
     * @return boolean
     */
    public function validateMessage($message);

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
    public function createMailEvent($message, $type = null, $timestamp = null);

    /**
     * Triggers the correct event for the given MailEvent, based on it's
     * type.
     *
     * @param MailEvent $message
     *
     * @return void
     */
    public function triggerEventFor(MailEvent $message);

    /**
     * Gets the recipient for this email.
     *
     * @param MailEvent $message
     *
     * @return null|string
     */
    public function getMessageRecipient($message);

    /**
     * Checks whether this Mail event has any opens
     *
     * @param MailEvent $message
     *
     * @return boolean
     */
    public function messageHasOpens($message);

    /**
     * Gets the opens array for this event
     *
     * @param MailEvent $message
     *
     * @return integer
     */
    public function getMessageOpenCount($message);

    /**
     * Checks whether this Mail event has any clicks
     *
     * @param MailEvent $message
     *
     * @return boolean
     */
    public function messageHasClicks($message);

    /**
     * Gets the clicks array for this event
     *
     * @param MailEvent $message
     *
     * @return integer
     */
    public function getMessageClickCount($message);

    /**
     * Gets the state of the Message.
     *
     * @param MailEvent $message
     *
     * @return string|null
     */
    public function getMessageState($message);

    /**
     * Gets the clicks array for this event
     *
     * @return boolean
     */
    public function messageWasHardBounced($message);

    /**
     * Checks whether this message was soft-bounced
     *
     * @param MailEvent $message
     *
     * @return boolean
     */
    public function messageWasSoftBounced($message);

    /**
     * Checks whether the recipient marked this message as spam
     *
     * @param MailEvent $message
     *
     * @return boolean
     */
    public function messageWasMarkedAsSpam($message);

    /**
     * Checks whether this message caused the recipient
     * to unsubscribe
     *
     * @param MailEvent $message
     *
     * @return boolean
     */
    public function messageRecipientUnsubscribed($message);

    /**
     * Checks whether this message was rejected for sending.
     *
     * @param MailEvent $message
     *
     * @return boolean
     */
    public function messageWasRejected($message);
}
