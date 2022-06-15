<?php

namespace Hellomayaagency\Enso\Mailer\Contracts;

interface Audience
{
    /**
     * Gets a set of Campaigns that have selected this Audience
     *
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function campaigns();

    /**
     * Gets a set of Conditions that define this Audience's Users.
     *
     * @return void
     */
    public function conditions();

    /**
     * Gets the Conditions for this Audience, nested into parent <-> children
     * groups
     *
     * @return \Hellomayaagency\Enso\Mailer\Handlers\ConditionTree
     */
    public function getConditionTree();

    /**
     * Gets the data for this tree in the correct format to pass to
     * the FormBuidler Vue component
     *
     * @return array
     */
    public function getFormState();

    /**
     * Generates the full query to find users that match conditions for this
     * audience, optionally based off of a custom query.
     *
     * @param \Illuminate\Database\Query\Builder|null $query
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function generateAudienceQuery($query = null);

    /**
     * Gets a count of users that this mail should be sent to
     *
     * @param \Illuminate\Database\Query\Builder|null $base_query
     *
     * @return integer
     */
    public function getUserCountAttribute($base_query = null);

    /**
     * @todo documentation
     *
     * @param \Illuminate\Database\Query\Builder|null $base_query
     *
     * @return void
     */
    public function getUsers($base_query = null);

    /**
     * Convert this Audience into a List item array
     *
     * @return array
     */
    public function convertToListItem();
}
