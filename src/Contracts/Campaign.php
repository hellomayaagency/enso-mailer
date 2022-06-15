<?php

namespace Hellomayaagency\Enso\Mailer\Contracts;

use Carbon\Carbon;

interface Campaign
{
    /**
     * The audiences this campaign should be / has been sent to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function audiences();

    /**
     * Gets a list of recipients to whom this campaign was actually send.
     *
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function recipients();

    /**
     * Gets the current stats from the most recently aggregated
     * recipient's message lists.
     *
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function stats();

    /**
     * Gets the base audience query, and then applies the query modifiers for
     * each of the selected Audiences, to provide a definitive list of users.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function generateAudienceQuery();

    /**
     * Gets the full list of users for this campaign
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAudienceUsers();

    /**
     * Returns just a count of the Email Addresses to which this
     * campaign will be sent.
     *
     * @return int
     */
    public function getAudienceUsersCount();

    /**
     * Gets the number of Sends for this Campaign
     *
     * @return int|null
     */
    public function getSendsAttribute();

    /**
     * Gets the number of Opens for this Campaign
     *
     * @return int|null
     */
    public function getUniqueOpensAttribute();

    /**
     * Gets the number of Opens for this Campaign
     *
     * @return int|null
     */
    public function getUniqueClicksAttribute();

    /**
     * Gets the number of Opens for this Campaign
     *
     * @return int|null
     */
    public function getFailedSendsAttribute();

    /**
     * Gets the Campaign Name
     *
     * @return string
     */
    public function getName();

    /**
     * Gest the Campaign Subject.
     *
     * @return string
     */
    public function getSubject();

    /**
     * Gets the name of the template that should be used to render this campaign's email
     *
     * @return string
     */
    public function getEmailTemplate();

    /**
     * Checks wether or not this Campaign has a set date
     *
     * @return bool
     */
    public function hasMailableTitle();

    /**
     * Gets the title of this Campaign's email.
     *
     * @return string
     */
    public function getMailableTitle();

    /**
     * Checks wether or not this Campaign has a set date
     *
     * @return bool
     */
    public function hasMailableDate();

    /**
     * Gets the date set on this Campaign, in the specificed format
     *
     * @param string $format
     *
     * @return string
     */
    public function getMailableDate($format = 'jS M Y');

    /**
     * Gets the unpacked data for the body of this email.
     *
     * @return \Illuminate\Support\Collection
     */
    public function unpackedEmailBody();

    /**
     * Gets the rendered version of this Campaign
     *
     * @param $inline_css
     *
     * @return string
     */
    public function getRenderedEmail($inline_css = true);

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
    public function setDriver($driver = null);

    /**
     * Sets and saves the driver, either to the provided value or
     * to the current value as set in the config.
     *
     * @param string $driver
     *
     * @return self
     */
    public function updateDriver($driver = null);

    /**
     * Returns the driver that was used to send this campaign.
     *
     * If it has not yet been sent, it should be null
     *
     * @return string|null
     */
    public function getDriver();

    /**
     * Sends the campaign, using the provided sender
     *
     * @param MailSender $sender
     *
     * @return bool
     */
    public function send();

    /**
     * Checks to see whether this Campaign has been sent
     *
     * @return bool
     */
    public function hasBeenSent();

    /**
     * Marks this Campaign as having been sent.
     *
     * Optionally, this can be set to a specific date
     *
     * @param \Carbon\Carbon $date
     *
     * @return void
     */
    public function markAsSent(Carbon $date = null);

    /**
     * Gets the appropriate Parser for this MailEvent
     *
     * @return MailParser
     */
    public function getParser();

    /**
     * Finds the first message to get response for this campaign, to get the
     * driver from. As Campaigns can only be sent once, they can only be
     * sent via one driver, so this is adequate.
     *
     * Then use the parser to recaluculate the stats for this campaign.
     *
     * @return void
     */
    public function recalculateStats();

    /**
     * Fetches the current status for each recipient of this campaign
     * from it's sender
     *
     * @return \Hellomayaagency\Enso\Mailer\Models\CampaignStats
     */
    public function queryCurrentStatus();
}
