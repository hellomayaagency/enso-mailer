<?php

namespace Hellomayaagency\Enso\Mailer\Handlers;

use Illuminate\Support\Collection;

class ConditionTreeNode
{
    protected $name;
    protected $apply_as;
    protected $group_conditions;
    protected $standalone_conditions;
    protected $child_nodes;

    public function __construct($name = '', $apply_as = 'AND')
    {
        $this->name = $name;
        $this->apply_as = $apply_as;
        $this->group_conditions = new Collection;
        $this->standalone_conditions = new Collection;
        $this->child_nodes = new Collection;
    }

    public function addCondition($condition, $name = '')
    {
        list($head, $tail) = $this->splitName($name);

        if (empty($head) || $head === $this->name) {
            if ($condition->canBeGrouped()) {
                $this->group_conditions->push($condition);
            } else {
                $this->standalone_conditions->push($condition);
            }

            return;
        }

        if ($this->hasChild($head)) {
            $this->getChild($head)->addCondition($condition, $tail);

            return;
        }

        $new_node = new self($head, $this->apply_as);
        $new_node->addCondition($condition, $tail);

        $this->getChildren()->push($new_node);

        return $this;
    }

    public function applyToQuery($query, $apply_as = 'AND')
    {
        if ($this->getAllModifiersCount() === 0) {
            return $query;
        }

        $this->applyChildren($query, $apply_as);
    }

    public function getName()
    {
        return $this->name;
    }

    public function hasGroupConditions()
    {
        return !! $this->getGroupConditions()->count();
    }

    public function getGroupConditions()
    {
        return $this->group_conditions;
    }

    public function hasStandaloneConditions()
    {
        return !! $this->getStandaloneConditions()->count();
    }

    public function getStandaloneConditions()
    {
        return $this->standalone_conditions;
    }

    public function hasChildren()
    {
        return !! $this->getChildren()->count();
    }

    public function getChildren()
    {
        return $this->child_nodes;
    }

    protected function splitName($name)
    {
        $name_parts = explode('.', $name);
        $head = array_shift($name_parts);
        $tail = implode('.', $name_parts);

        return [$head, $tail];
    }

    protected function hasChild($name)
    {
        return (bool) $this->getChild($name);
    }

    protected function getChild($name)
    {
        return $this->getChildren()->first(function ($child) use ($name) {
            return $child->getName() === $name;
        });
    }

    protected function getAllModifiersCount()
    {
        return
            $this->getGroupableModifiersCount() +
            $this->getUngroupableModifiersCount();
    }

    public function getGroupableModifiersCount()
    {
        return
            $this->getGroupConditions()->count() +
            $this->getChildrenWithGroupConditions()->count();
    }

    protected function getChildrenWithGroupConditions()
    {
        return $this->getChildren()->filter(function ($tree_node) {
            return $tree_node->getGroupableModifiersCount();
        });
    }

    public function getUngroupableModifiersCount()
    {
        return
            $this->getStandaloneConditions()->count() +
            $this->getChildrenWithStandaloneConditions()->count();
    }

    protected function getChildrenWithStandaloneConditions()
    {
        return $this->getChildren()->filter(function ($tree_node) {
            return $tree_node->getUngroupableModifiersCount();
        });
    }

    protected function applyChildren($query, $apply_as = 'AND')
    {
        $how_to_modify = $apply_as === 'AND' ? 'where' : 'orWhere';

        if ($this->getName() && $this->getGroupableModifiersCount()) {
            $how_to_modify .= 'Has';

            $query->{$how_to_modify}($this->getName(), function ($sub_query) use ($apply_as) {
                $this->applyGroupConditions($sub_query, $apply_as);
                $this->applyChildNodes($sub_query);
            });
        } else {
            $this->applyGroupConditions($query, $apply_as);
            $this->applyChildNodes($query);
        }

        $this->getStandaloneConditions()->each(function ($condition) use ($query, $apply_as) {
            $operator = $condition->getOperatorObject();
            $operator->modifyQuery($query, $condition, $apply_as);
        });

        return $query;
    }

    protected function applyGroupConditions($query)
    {
        $group_conditions = $this->getGroupConditions();

        /**
         * If this Tree Node has a name, this is a relationship.
         * As such, wrap together all the conditions.
         */
        if ($this->getName()) {
            $query->where(function ($sub_query) use ($group_conditions) {
                $group_conditions->each(function ($condition) use ($sub_query) {
                    $operator = $condition->getOperatorObject();
                    $operator->modifyQuery($sub_query, $condition, $this->apply_as);
                });
            });
        } else {
            $group_conditions->each(function ($condition) use ($query) {
                $operator = $condition->getOperatorObject();
                $operator->modifyQuery($query, $condition, $this->apply_as);
            });
        }
    }

    protected function applyChildNodes($query)
    {
        if ($this->getGroupConditions()->count()) {
            /**
             * Any Node which has direct conditions should apply them all as the parent
             * query-group has specifies.
             */
            $apply_as = $this->apply_as;
        } elseif ($this->getName()) {
            /**
             * If there are no conditions, then this is a sub relationship call, and
             * so it requires it's children also be present.
             */
            $apply_as = 'AND';
        } else {
            $apply_as = $this->apply_as;
        }

        /**
         * If neither of these conditions are met, this a top level Node and
         * we we should combine the Conditions as query-group has specifies.
         */
        $this->getChildren()->each(function ($tree_node) use ($query, $apply_as) {
            return $tree_node->applyToQuery($query, $apply_as);
        });
    }
}
