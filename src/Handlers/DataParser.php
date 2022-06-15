<?php

namespace Hellomayaagency\Enso\Mailer\Handlers;

use Carbon\Carbon;
use Illuminate\Support\Arr;

class DataParser
{
    /**
     * Parses a generic dataset into useable data.
     *
     * @param \Illuminate\Support\Collection $data
     * @param callable                       $callable
     *
     * @return \Illuminate\Support\Collection parsed data
     */
    public static function parseGeneric($data_set, $callable = null, $apply_as = 'AND')
    {
        if ($callable && is_callable($callable)) {
            $data_set = $callable($data_set);
        }

        return $data_set;
    }

    /**
     * Parses a dataset into useable `date` data.
     *
     * @param \Illuminate\Support\Collection $data
     * @param callable                       $callable
     *
     * @return \Illuminate\Support\Collection parsed data
     */
    public static function parseDates($data_set, $callable = null, $apply_as = 'AND', $format = 'Y-m-d H:i:s.u')
    {
        $data_set = $data_set->map(function ($date_value) use ($format) {
            return Carbon::createFromFormat(
                $format,
                Arr::get($date_value, 'date'),
                Arr::get($date_value, 'timezone')
            );
        });

        if ($callable && is_callable($callable)) {
            $data_set = $callable($data_set);
        }

        return $data_set;
    }

    /**
     * Parse a dataset into useable `number` data.
     *
     * @param \Illuminate\Support\Collection $data
     * @param callable                       $callable
     *
     * @return \Illuminate\Support\Collection parsed data
     */
    public static function parseNumbers($data_set, $callable = null, $apply_as = 'AND')
    {
        $data_set = $data_set->map(function ($datum) {
            return (int) $datum;
        });

        if ($callable && is_callable($callable)) {
            $data_set = call_user_func_array($callable, [$data_set, $apply_as]);
        }

        return $data_set;
    }

    /**
     * Parses a dataset into useable `string` data. If you do not wish
     * to pass all strings through the same stub, simply pass no stub
     * and apply whatever you need in the callable.
     *
     * @param \Illuminate\Support\Collection $data
     * @param callable                       $callable
     *
     * @return \Illuminate\Support\Collection parsed data
     */
    public static function parseStrings($data_set, $callable = null, $apply_as = 'AND', $stub = null)
    {
        if ($stub) {
            $data_set = $data_set->map(function ($datum) use ($stub) {
                return str_replace('#VALUE#', $datum, $stub);
            });
        }

        if ($callable && is_callable($callable)) {
            $data_set = $callable($data_set);
        }

        return $data_set;
    }

    /**
     * Parses a dataset into useable `select` data. If you have objects NOT
     * keyed by id, then you could set run_default to false and pull out
     * identifiers in the callable.
     *
     * @param \Illuminate\Support\Collection $data
     * @param callable                       $callable
     *
     * @return \Illuminate\Support\Collection parsed data
     */
    public static function parseSelectData($data_set, $callable = null, $apply_as = 'AND', $run_default = true)
    {
        if ($run_default) {
            $data_set = $data_set->map(function ($datum) {
                return $datum['id'] ?? null;
            })->filter(function ($item) {
                return $item !== null;
            });
        }

        if ($callable && is_callable($callable)) {
            $data_set = $callable($data_set);
        }

        return $data_set;
    }
}
