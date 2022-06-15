<?php

namespace Hellomayaagency\Enso\Mailer\Models;

use EnsoMailer;
use Hellomayaagency\Enso\Mailer\Contracts\Audience;
use Hellomayaagency\Enso\Mailer\Contracts\Condition as ConditionContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Yadda\Enso\Crud\Contracts\IsCrudModel as ContractsIsCrudModel;
use Yadda\Enso\Crud\Traits\IsCrudModel;

class Condition extends Model implements ConditionContract, ContractsIsCrudModel
{
    use IsCrudModel;

    protected $table = 'mailer_conditions';

    protected $fillable = [
        'name',
        'audience_id',
    ];

    protected $attributes = [
        'data' => '[]',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Relationship returning the root Audience that this condition belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function audience()
    {
        return $this->belongsTo(App::make(Audience::class));
    }

    /**
     * Relationship returning the parent `group` condition of this condition,
     * if it has one.
     *
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function parent()
    {
        return $this->belongsTo(App::make(ConditionContract::class), 'parent_id');
    }

    /**
     * Gets the 'AND'/'OR' matching type.
     *
     * @return string
     */
    public function getMatchType()
    {
        return $this->type ?? 'AND';
    }

    /**
     * Gets the name of the Vue component that this operator should
     * render and collect data from.
     *
     * @return string
     */
    public function getComponent()
    {
        return $this->component;
    }

    /**
     * Checks whether this is a condition group
     *
     * @return bool
     */
    public function isConditionGroup()
    {
        return $this->getComponent() === 'query-group';
    }

    /**
     * Checks whether this is a condition
     *
     * @return bool
     */
    public function isCondition()
    {
        return $this->getComponent() === 'query-condition';
    }

    /**
     * Checks to see whether this particular condition can be grouped with other conditions
     * that have matching relatinoship structure. If grouping is enabled, an operator that applied
     * to a relationship like 'users.roles.permissions' could group with a relationship like 'users.roles',
     * resulting in a query where user has roles with (X and/or with permissions with Y). Without grouping,
     * the resulting query would be user has roles with X and/or has roles with permsisions with Y.
     *
     * @return bool
     */
    public function canBeGrouped()
    {
        return $this->getOperatorObject()->canBeGrouped();
    }

    /**
     * Gets the canonical name of the operand for this condition. Operands  are registered
     * on the EnsoMailer facade.
     *
     * @return void
     */
    public function getOperand()
    {
        return $this->operand;
    }

    /**
     * Gest the object representation of the Operand for this Condition.
     *
     * @return object
     */
    public function getOperandObject()
    {
        return EnsoMailer::getOperandObject($this->getOperand());
    }

    /**
     * Query level equates to the actual relationship this query should be based on, by exploding and
     * popping from the relationship_name array. This can be user to build whereHas queries. This happens in
     * scenarios where you want to count relationships for queries like `where has one of any of these items` as opposed
     * to querying them for existence/matching fields directly.
     *
     * @return string
     */
    public function getQueryLevel()
    {
        $relationship_name = $this->getOperandObject()->getRelationshipName();
        $relationship_name_parts = explode('.', $relationship_name);
        return array_pop($relationship_name_parts);
    }

    /**
     * Gets the canonical name for the operator for this condition. Operators are registered
     * on the EnsoMailer facade.
     *
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * Gets an Object representation of the Operator for this condiiton.
     *
     * @return object
     */
    public function getOperatorObject()
    {
        return EnsoMailer::getOperatorObject($this->getOperator());
    }

    /**
     * Gets the data that this condition should be based on.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Returns the current 'state' of this condition. This is used to populate
     * edit pages of the Query builder forms.
     *
     * @return array
     */
    public function getFormState()
    {
        return [
            'operand' => $this->getOperand(),
            'operator' => $this->getOperator(),
            'data' =>  $this->getData(),
        ];
    }
}
