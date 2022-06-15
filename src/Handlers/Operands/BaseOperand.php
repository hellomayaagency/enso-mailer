<?php

namespace Hellomayaagency\Enso\Mailer\Handlers\Operands;

abstract class BaseOperand
{
    /**
     * The field to compare the given data to. Ideally, this should be a
     * table_name.field to reduce the likelyhood of collisions between
     * different relations with shared column names.
     *
     * @var string
     */
    protected $operand;

    /**
     * This is the label that will be applied to the select option for this
     * Modifier. Attempts should be made to keep this in line with the
     * concept of fluently reading the results.
     *
     * For example, users.created_at, could be 'Creation Date', so that it could read
     * 'Creation Date On Or After [date]'. Similarly, if users 'author' pages, then a
     * Operand that tests whether users have authored pages might read: 'Has Authored Pages',
     * so that it would read as 'Has Authored Pages Before [date]';
     *
     * @var string
     */
    protected $label = 'Change Me';

    /**
     * Additional props to bind to the Vue component
     * This should be an array with two keys, 'operators' and 'property_sets'.
     * The 'operators' item should be an array of key value pairs where the key is
     * the canonical name of the operator, and the value is the name of the property
     * set to use. 'property_sets' should be an array of key value pairs, where the
     * key is the name of the property set, and the value is an array of the actual
     * properites to set. It should therefore look like:
     *
     * [
     *     'operators' => [
     *         'relationship_any' => 'role_options',
     *         ...
     *     ],
     *     'property_sets' => [
     *         'role_options' => [
     *             'settings' => [
     *                 'options' => [
     *                     ...
     *                 ],
     *             ],
     *         ],
     *         ...
     *     ],
     * ]
     *
     * This has been done so that multiple simliar Operators for the same Operand
     * don't need to potentially pass ALL the same options where they have them
     * in common.
     *
     * @var array
     */
    protected $component_props = [];

    /**
     * Gets the Label for this Modifer
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Returns the name of the field to affect
     *
     * @return void
     */
    public function getFieldName()
    {
        return $this->operand;
    }

    /**
     * Gets the name of the relationship for this Operand. And empty relationship
     * may be used if the field to be queries is directly on the base query table.
     *
     * For nested relationships, use dot notation e.g: 'roles.permissions'
     *
     * @return string
     */
    public function getRelationshipName()
    {
        return $this->relationship_name ?? '';
    }

    /**
     * Gets the full compliment of json data for this Modifier.
     *
     * @return array
     */
    public function getJsonData()
    {
        return [
            'label' => $this->getLabel(),
            'allowed_operators' => $this->getAllowedOperators(),
            'component_props' => $this->getComponentProperties(),
        ];
    }

    /**
     * Checks whether a given operator is valid for this Modifier
     *
     * @param string $operator
     *
     * @return boolean
     */
    public function isValidOperator($operator)
    {
        return in_array($operator, $this->getAllowedOperators());
    }

    public function overridesChildQueryModifier()
    {
        return $this->overrides_child_query ?? false;
    }

    public function overrideChildQueryModifier($query, $condition, $data, $apply_as)
    {
        return null;
    }

    /**
     * Gets a list of allowed operators for this operand
     *
     * @return array
     */
    protected function getAllowedOperators(): array
    {
        return array_keys($this->allowed_operators ?? []);
    }

    /**
     * Gets properties to apply to the component. This resulting properties will be
     * merged with properties set on the relevant operand, with properties set here
     * taking precedence over other properties.
     *
     * @return array
     */
    protected function getComponentProperties()
    {
        return [];
    }
}
