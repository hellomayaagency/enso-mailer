<?php

namespace Hellomayaagency\Enso\Mailer\Http\Controllers\Admin;

use Exception;
use Hellomayaagency\Enso\Mailer\Contracts\AudienceController as AudienceControllerContract;
use Hellomayaagency\Enso\Mailer\Contracts\Condition;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Yadda\Enso\Crud\Controller;
use Yadda\Enso\Facades\EnsoCrud;

class AudienceController extends Controller implements AudienceControllerContract
{
    protected $crud_name = 'mailer_audience';

    /**
     * Sets the JS Data for an edit page.
     *
     * @return void
     */
    protected function setJSCreateData()
    {
        // We don't need any for this controller
    }

    /**
     * Permforms that actual create functionality using provided data.
     *
     * @param array $data Data to apply
     *
     * @return void
     */
    protected function performCreate($data)
    {
        $item = $this->getConfig()
            ->getCreateForm()
            ->getModelInstance();

        $item->name = Arr::get($data, 'name', '');
        $item->save();

        try {
            DB::beginTransaction();

            // Clear all conditions and rebuild. It's both simple and more reliable than
            // matching up existing items to other items that could or could not have been
            // deleted.
            $item->conditions->each(function ($condition) {
                $condition->delete();
            });

            // There should only be one, top level, query-group condition.
            $conditions_data = collect($data['conditions'] ?? [])->each(function ($condition_data) use ($item) {
                $condition = $this->createConditionFromData($condition_data, $item);
            });
        } catch (Exception $e) {
            Log::error($e->getMessage());
            DB::rollback();
        }

        DB::commit();
    }

    /**
     * Creates a condition from condition data. All Condition data sets should meet the
     * minimium requirements to be a valid condition, although it could still be empty.
     *
     * If it is empty, then return null.
     *
     * @param array $condition_data
     *
     * @return Condition|null
     */
    protected function createConditionFromData($condition_data, $parent_audience)
    {
        $condition_class = EnsoCrud::modelClass('mailer_condition');
        $condition = new $condition_class;
        $condition->audience()->associate($parent_audience);

        $condition->type = $condition_data['type'];
        $condition->component = $condition_data['component'];
        $condition->save();

        $condition = call_user_func_array(
            [
                $this,
                'apply' . Str::studly($condition->component) . 'Data',
            ],
            [
                $condition,
                $parent_audience,
                $condition_data,
            ]
        );

        return $condition;
    }

    /**
     * Applied data to a Condition object, recursively creating children as needed.
     *
     * @param Condition $condition
     * @param Audience $parent_audience
     * @param array $data
     *
     * @return Condition|null
     */
    protected function applyQueryGroupData($condition, $parent_audience, $data)
    {
        $conditions = collect($data['conditions'] ?? []);

        $conditions = $conditions->transform(function ($condition_data) use ($condition, $parent_audience) {
            $child_condition = $this->createConditionFromData($condition_data, $parent_audience);

            if ($child_condition) {
                $child_condition->parent()->associate($condition)->save();
            }

            return $child_condition;
        })->filter();

        /**
         * If there are no valid query conditions for this group, then this group
         * is also invalid. As such, delete it.
         */
        if ($conditions->count() === 0) {
            $condition->delete();

            return null;
        }

        return $condition;
    }

    /**
     * Applies data to a Condition object. If the data does not fulfil the minimum
     * requirements for a valid condtion, delete it.
     *
     * @param Condition $condition
     * @param Audience $parent_audience
     * @param array $data
     *
     * @return Condition|null
     */
    protected function applyQueryConditionData($condition, $parent_audience, $data)
    {
        $condition->operand = $data['operand'] ?? null;
        $condition->operator = $data['operator'] ?? null;
        $condition->data = $data['data'] ?? [];

        if (! $condition->operand || ! $condition->operator || count($condition->data) === 0) {
            $condition->delete();

            return null;
        }

        return $condition;
    }

    /**
     * Sets the JS Data for an edit page.
     *
     * @param Request $request
     *
     * @return void
     */
    protected function setJSEditData(Request $request)
    {
        // We don't need any for this controller
    }

    protected function performUpdate($data)
    {
        return $this->performCreate($data);
    }
}
