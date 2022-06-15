<?php

namespace Hellomayaagency\Enso\Mailer\Handlers;

use Exception;
use Illuminate\Support\Collection;
use Log;

class ConditionTree
{
    protected $root_nodes;

    public function __construct($conditions)
    {
        $this->root_nodes = $this->buildTree($conditions);
    }

    /**
     * Gets the built node tree
     *
     * @return \Illuminate\Support\Collection
     */
    public function getNodeTree()
    {
        return $this->root_nodes;
    }

    /**
     * Gets the Node tree as a data array.
     *
     * @return array
     */
    public function getNodeTreeArray()
    {
        $root_nodes = $this->getNodeTree();

        return $root_nodes->transform(function ($node) {
            return $this->childrenToArray($node);
        })->toArray();
    }

    /**
     * Get the entire tree and applies it to the given query. The tree
     * should build to having a single root as the entry point.
     *
     * @param \Illuminate\Database\Query\Builder $query
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function applyToQuery($query)
    {
        $group_conditions = $this->getNodeTree();

        if ($group_conditions->count() === 0) {
            return $query;
        }

        if ($group_conditions->count() > 1) {
            Log::warning('MAILER: There shouldn\'t be more than one root node for the query builder.');
        }

        return $this->applyGroupCondition($query, $group_conditions->first());
    }

    /**
     * Applies a Group Conditions as either 'AND'ed or 'OR'ed with a query.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param Condtion                           $group_condition
     * @param string                             $apply_as
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function applyGroupCondition($query, $group_condition, $apply_as = 'AND')
    {
        if (! $this->isValidGroupCondition($group_condition)) {
            return $query;
        }

        $conditions = $group_condition['conditions'];

        if ($conditions->count() === 1) {
            return $this->applySingleCondition($query, $conditions->first(), $apply_as);
        }

        $how_to_modify = $apply_as === 'AND' ? 'where' : 'orWhere';

        return $query->{$how_to_modify}(function ($sub_query) use ($group_condition, $conditions) {
            $apply_as = $group_condition->getMatchType();

            list($groups, $conditions) = $this->partitionConditions($conditions);

            $this->namespaceAndApplyConditions($sub_query, $conditions, $apply_as);

            $groups->each(function ($condition_group) use ($sub_query, $apply_as) {
                $this->applyGroupCondition($sub_query, $condition_group, $apply_as);
            });

            return $sub_query;
        });
    }

    protected function applyCondition($query, $condition, $apply_as)
    {
        return $this->namespaceAndApplyConditions($query, collect([$condition]), $apply_as);
    }

    /**
     * Partitions a collection of Conditions into groups dependent on
     * the type of Condition it is (condition-group vs condition)
     *
     * @param \Illuminate\Support\Collection
     *
     * @return \Illuminate\Support\Collection
     */
    protected function partitionConditions($conditions)
    {
        return $conditions->partition(function ($condition) {
            return $condition->isConditionGroup();
        });
    }

    /**
     * Checks that a condition is a valid group condition.
     *
     * @param \Hellomayaagency\Enso\Mailer\Contracts\Condition $group_condition
     *
     * @return bool
     */
    protected function isValidGroupCondition($group_condition)
    {
        if (! $group_condition->isConditionGroup()) {
            throw new Exception('Attempting to apply QueryCondition as a Group');
        }

        /**
         * ConditionGroups that have no conditions shouldn't exist. They should be
         * removed from the tree while saving / updating. This being the case, Log an
         * error and delete the condition-group, then return to continue on.
         */
        if (($group_condition['conditions'] ?? new Collection())->count() === 0) {
            Log::warning('MAILER: Condition group with no conditions found. Deleting');
            $group_condition->delete();

            return false;
        }

        return true;
    }

    /**
     * Applies a single condition. While Single conditions within groups should be at a
     * minimum (a group with on condition is like just having one condition), it's possible
     * to achieve and so needes handling as best as possible.
     *
     * @param \Illuminate\Database\Query\Builder     $query
     * @param \Hellomayaagency\Enso\Mailer\Contracts\Condition $condition
     * @param string                                 $apply_as
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function applySingleCondition($query, $condition, $apply_as)
    {
        if ($condition->isConditionGroup()) {
            return $this->applyGroupCondition($query, $condition, $apply_as);
        } else {
            return $this->applyCondition($query, $condition, $apply_as);
        }
    }

    /**
     * Namespaces a collection of query-condition type Conditions, and then
     * apply them to the query;
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param \Illuminate\Support\Collection     $conditions
     * @param String                             $apply_as
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function namespaceAndApplyConditions($query, $conditions, $apply_as)
    {
        $condition_tree = new ConditionTreeNode('', $apply_as);

        $conditions->each(function ($condition) use ($condition_tree) {
            $relationship_name = $condition->getOperandObject()->getRelationshipName();
            $condition_tree->addCondition($condition, $relationship_name);
        });

        return $condition_tree->applyToQuery($query, $apply_as);
    }

    /**
     * Builds a node tree from a flat collection of Conditions
     *
     * @param \Illuminate\Support\Collection $conditions
     *
     * @return \Illuminate\Support\Collection
     */
    protected function buildTree($conditions)
    {
        list($root_nodes, $children_nodes) = $conditions->partition(function ($condition) {
            return is_null($condition->parent_id);
        });

        $root_nodes->each(function ($node) use ($children_nodes) {
            if ($children_nodes->contains(function ($child_node) use ($node) {
                return $node->getKey() === $child_node->parent_id;
            })) {
                $children_nodes = $this->applyChildrenTo($node, $children_nodes);
            }
        });

        return $root_nodes;
    }

    /**
     * Remove Conditions that are children of the parent Condition from the list of
     * nodes, and applies them as 'conditions' to the parent.
     *
     * @param \Hellomayaagency\Enso\Mailer\Contracts\Condition $parent
     * @param \Illuminate\Support\Collection         $child_nodes
     *
     * @return \Illuminate\Support\Collection new set of child_nodes
     */
    protected function applyChildrenTo($parent, $child_nodes)
    {
        list($direct_children, $not_direct_children) = $child_nodes->partition(function ($condition) use ($parent) {
            return $condition->parent_id === $parent->getKey();
        });

        $parent->conditions = $direct_children ?? new Collection();

        $parent->conditions->each(function ($child_node) use ($not_direct_children) {
            if ($not_direct_children->contains(function ($possible_child) use ($child_node) {
                return $child_node->getKey() === $possible_child->parent_id;
            })) {
                $not_direct_children = $this->applyChildrenTo($child_node, $not_direct_children);
            }
        });

        return $not_direct_children;
    }

    /**
     * Recursively converts the 'conditions' Collection into an array of arrays
     *
     * @param \Hellomayaagency\Enso\Mailer\Contracts\Condition $node
     *
     * @return \Hellomayaagency\Enso\Mailer\Contracts\Condition
     */
    protected function childrenToArray($node)
    {
        if (! $node->conditions) {
            $node->conditions = new Collection();
        }

        $node->conditions = $node->conditions->transform(function ($child_node) {
            return $this->childrenToArray($child_node);
        })->values()->toArray();

        return $node;
    }
}
