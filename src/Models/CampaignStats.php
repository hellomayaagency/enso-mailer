<?php

namespace Hellomayaagency\Enso\Mailer\Models;

use Hellomayaagency\Enso\Mailer\Contracts\Campaign;
use Hellomayaagency\Enso\Mailer\Exceptions\CampaignStatsException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Yadda\Enso\Crud\Contracts\IsCrudModel as ContractsIsCrudModel;
use Yadda\Enso\Crud\Traits\IsCrudModel;

class CampaignStats extends Model implements ContractsIsCrudModel
{
    use IsCrudModel;

    protected $table = 'mailer_campaign_stats';

    public $fillable = [
        'campaign_id',
        'send',
        'open',
        'unique_open',
        'click',
        'unique_click',
        'hard_bounce',
        'soft_bounce',
        'spam',
        'unsub',
        'reject',
    ];

    protected static $potential_properties = [
        'send' => [
            'name' => '# Sent',
            'percent_of_send' => false,
        ],
        'open' => [
            'name' => 'Opens',
            'percent_of_send' => false,
        ],
        'unique_open' => [
            'name' => 'Unique Opens',
            'percent_of_send' => true,
        ],
        'click' => [
            'name' => 'Clicks',
            'percent_of_send' => false,
        ],
        'unique_click' => [
            'name' => 'Unique Clicks',
            'percent_of_send' => true,
        ],
        'hard_bounce' => [
            'name' => 'Hard Bounces',
            'percent_of_send' => true,
        ],
        'soft_bounce' => [
            'name' => 'Soft Bounces',
            'percent_of_send' => true,
        ],
        'spam' => [
            'name' => 'Marked as Spam',
            'percent_of_send' => true,
        ],
        'unsub' => [
            'name' => 'Unsubscribes',
            'percent_of_send' => true,
        ],
        'reject' => [
            'name' => 'Sending Rejected',
            'percent_of_send' => true,
        ],
    ];

    /**
     * Gets the campaign these stats are for
     *
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function campaign()
    {
        return $this->belongsTo(App::make(Campaign::class));
    }

    /**
     * The value to use at this Model's CRUD label.
     *
     * @return string
     */
    public function getCrudLabel(): string
    {
        if ($this->relationLoaded('campaign') && $this->campaign) {
            return $this->campaign->getCrudLabel();
        } else {
            return $this->getKey();
        }
    }

    /**
     * Gets the full list of potential properties
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getPotentialProperties()
    {
        return collect(self::$potential_properties);
    }

    /**
     * Gets properties that have values stored against them
     *
     * @return void
     */
    public function getSupportedProperties()
    {
        return self::getPotentialProperties()->filter(function ($property, $index) {
            return $this->{$index} !== null;
        });
    }

    /**
     * Gets the value of a given property, optionally as a string which
     * may include a percentage of the sent mails, if appropriate.
     *
     * @param string $property
     *
     * @return null|int
     */
    public function getValueFor($property)
    {
        $value = $this->{$property};

        if (!is_null($value)) {
        }

        return $value;
    }

    /**
     * Gets the string value of a given property, adding a percentage of
     * the total sends if configured to do so.
     *
     * @param string $property
     *
     * @return string
     */
    public function getStringValueFor($property)
    {
        $value = $this->{$property};

        if (!is_null($value)
            && data_get(self::$potential_properties, $property . '.percent_of_send', false)) {
            $percent = number_format(($this->{$property} / $this->send) * 100, 0);
            $value = (string)$value . ' (' . $percent . '%)';
        }

        return (string)$value;
    }

    /**
     * Resets all the values on this object.
     *
     * Accepts an array to specific which become 0 and which become null, if not
     * all properties are supported for the implementation that will be
     * calculating the stats
     *
     * @param array $properties
     *
     * @return self
     */
    public function reset($properties = null)
    {
        $supported_properties = $properties ?? array_keys(self::$potential_properties);

        $reset_properties = array_intersect(array_keys(self::$potential_properties), $supported_properties);
        $null_properties = array_diff(array_keys(self::$potential_properties), $supported_properties);

        $fillables = array_merge(
            array_combine($reset_properties, array_pad([], count($reset_properties), 0)),
            array_combine($null_properties, array_pad([], count($null_properties), null))
        );

        $this->fill($fillables);

        return $this;
    }

    /**
     * Increments a stat on this dataset, optionally by a specificed
     * amount
     *
     * @param string $name
     * @param int    $value
     *
     * @return self
     */
    public function incrementStat($name, $value = 1)
    {
        if (!in_array($name, self::$potential_properties)) {
            throw new CampaignStatsException('That property does not exist');
        }

        $this->{$name} = ($this->{$name} ?? 0) + $value;

        return $this;
    }
}
