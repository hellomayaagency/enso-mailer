<?php

namespace Hellomayaagency\Enso\Mailer\Models;

use App;
use Hellomayaagency\Enso\Mailer\Contracts\Audience as AudienceContract;
use Hellomayaagency\Enso\Mailer\Handlers\ConditionTree;
use Illuminate\Database\Eloquent\Model;
use Yadda\Enso\Crud\Contracts\IsCrudModel as ContractsIsCrudModel;
use Yadda\Enso\Crud\Traits\IsCrudModel;
use Yadda\Enso\Facades\EnsoCrud;

class Audience extends Model implements AudienceContract, ContractsIsCrudModel
{
    use IsCrudModel;

    protected $table = 'mailer_audiences';

    protected $fillable = [
        'name',
    ];

    protected $condition_tree;

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
     * Gets a count of users that this mail should be sent to
     *
     * @param \Illuminate\Database\Query\Builder|null $base_query
     *
     * @return int
     */
    public function getUserCountAttribute($base_query = null)
    {
        return $this->generateAudienceQuery($base_query ?? $this->baseAudienceQuery())->count();
    }

    /**
     * Undocumented function
     *
     * @param \Illuminate\Database\Query\Builder|null $base_query
     *
     * @return void
     */
    public function getUsers($base_query = null)
    {
        return $this->generateAudienceQuery($base_query ?? $this->baseAudienceQuery())->get();
    }

    /**
     * Gets a set of Campaigns that have selected this Audience
     *
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function campaigns()
    {
        return $this->belongsToMany(EnsoCrud::modelClass('mailer_campaign'), 'mailer_campaign_audience');
    }

    /**
     * Gets a set of Conditions that define this Audience's Users.
     *
     * @return void
     */
    public function conditions()
    {
        return $this->hasMany(EnsoCrud::modelClass('mailer_condition'), 'audience_id');
    }

    /**
     * Gets the Conditions for this Audience, nested into parent <-> children
     * groups
     *
     * @return \Hellomayaagency\Enso\Mailer\Handlers\ConditionTree
     */
    public function getConditionTree()
    {
        if ($this->condition_tree === null) {
            $this->condition_tree = new ConditionTree($this->conditions);
        }

        return $this->condition_tree;
    }

    /**
     * Generates the full query to find users that match conditions for this
     * audience, optionally based off of a custom query.
     *
     * @param \Illuminate\Database\Query\Builder|null $query
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function generateAudienceQuery($query = null)
    {
        $query = $query ?? $this->baseAudienceQuery();

        $this->getConditionTree()->applyToQuery($query);

        return $query;
    }

    /**
     * Convert this Audience into a List item array
     *
     * @return array
     */
    public function convertToListItem()
    {
        return [
            'id' => $this->getKey(),
            'name' => $this->name . ' (' . $this->user_count . ')',
        ];
    }

    /**
     * Gets the data for this tree in the correct format to pass to
     * the FormBuidler Vue component
     *
     * @return array
     */
    public function getFormState()
    {
        return [
            'name' => $this->name ?? '',
            'conditions' => $this->getConditionTree()->getNodeTreeArray(),
        ];
    }
}
