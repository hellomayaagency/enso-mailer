<?php

namespace Hellomayaagency\Enso\Mailer\Handlers\Operands;

use Yadda\Enso\Facades\EnsoCrud;

class UserHasRoles extends BaseOperand
{
    /**
     * Allowed operators for this Operand.
     *
     * @var array
     */
    protected $allowed_operators = [
        'relationship_at_least' => 'relationship_at_least',
        'relationship_equals' => 'relationship_equals',
        'relationship_not_equals' => 'relationship_not_equals',
        'relationship_less_than' => 'relationship_less_than',
        'relationship_more_than' => 'relationship_more_than',
        'relationship_no_more_than' => 'relationship_no_more_than',
        'relationship_any' => 'relationship_any',
        'relationship_all' => 'relationship_all',
        'relationship_not_any' => 'relationship_not_any',
        'relationship_not_all' => 'relationship_not_all',
    ];

    protected $label = 'User has Roles';

    protected $relationship_name = 'roles';

    protected $operand = 'roles.slug';

    // protected $overrides_child_query = true;

    // public function overrideChildQueryModifier($query, $condition, $data, $apply_as)
    // {
    //     return $query->where('roles.slug', '!=', 'superuser');
    // }

    protected function getComponentProperties()
    {
        return [
            'operators' => [
                'relationship_any' => 'role_options',
                'relationship_all' => 'role_options',
                'relationship_not_any' => 'role_options',
                'relationship_not_all' => 'role_options',
            ],
            'property_sets' => [
                'role_options' => $this->getOptions(),
            ],
        ];
    }

    protected function getOptions()
    {
        $role_instance = EnsoCrud::modelClass('role');

        return [
            'settings' => [
                'options' => $role_instance::get()->map(function ($role) {
                    return [
                        'id' => $role->slug,
                        'name' => $role->name,
                    ];
                })->toArray(),
                'track_by' => 'id',
                'label' => 'name',
            ],
        ];
    }
}
