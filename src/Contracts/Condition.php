<?php

namespace Hellomayaagency\Enso\Mailer\Contracts;

interface Condition
{
    /**
     * Relationship returning the parent `group` condition of this condition,
     * if it has one.
     *
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function parent();

    /**
     * Relationship returning the root Audience that this condition belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function audience();

    /**
     * Gets the 'AND'/'OR' matching type.
     *
     * @return string
     */
    public function getMatchType();

    /**
     * Gets the name of the Vue component that this operator should
     * render and collect data from.
     *
     * @return string
     */
    public function getComponent();

    /**
     * Checks whether this is a condition group
     *
     * @return bool
     */
    public function isConditionGroup();

    /**
     * Checks whether this is a condition
     *
     * @return bool
     */
    public function isCondition();

    /**
     * Checks to see whether this particular condition can be grouped with other conditions
     * that have matching relatinoship structure. If grouping is enabled, an operator that applied
     * to a relationship like 'users.roles.permissions' could group with a relationship like 'users.roles',
     * resulting in a query where user has roles with (X and/or with permissions with Y). Without grouping,
     * the resulting query would be user has roles with X and/or has roles with permsisions with Y.
     *
     * @return bool
     */
    public function canBeGrouped();

    /**
     * Gets the canonical name of the operand for this condition. Operands  are registered
     * on the EnsoMailer facade.
     *
     * @return void
     */
    public function getOperand();

    /**
     * Gest the object representation of the Operand for this Condition.
     *
     * @return object
     */
    public function getOperandObject();

    /**
     * Query level equates to the actual relationship this query should be based on, by exploding and
     * popping from the relationship_name array. This can be user to build whereHas queries. This happens in
     * scenarios where you want to count relationships for queries like `where has one of any of these items` as opposed
     * to querying them for existence/matching fields directly.
     *
     * @return string
     */
    public function getQueryLevel();

    /**
     * Gets the canonical name for the operator for this condition. Operators are registered
     * on the EnsoMailer facade.
     *
     * @return string
     */
    public function getOperator();

    /**
     * Gets an Object representation of the Operator for this condiiton.
     *
     * @return object
     */
    public function getOperatorObject();

    /**
     * Gets the data that this condition should be based on.
     *
     * @return array
     */
    public function getData();

    /**
     * Returns the current 'state' of this condition. This is used to populate
     * edit pages of the Query builder forms.
     *
     * @return array
     */
    public function getFormState();
}
