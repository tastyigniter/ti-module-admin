<?php

namespace Admin\Traits;

use Carbon\Carbon;
use Exception;
use Igniter\Flame\Location\WorkingSchedule;
use Illuminate\Support\Collection;
use InvalidArgumentException;

trait HasWorkingHours
{
    /**
     * @var \Illuminate\Database\Eloquent\Collection
     */
    protected $workingHours;

    protected $currentTime;

    /**
     * @return Carbon
     */
    public function getCurrentTime()
    {
        if (!is_null($this->currentTime))
            return $this->currentTime;

        return $this->currentTime = Carbon::now();
    }

    public function availableWorkingTypes()
    {
        return [static::OPENING, static::DELIVERY, static::COLLECTION];
    }

    public function listWorkingHours()
    {
        if (!$this->workingHours)
            $this->workingHours = $this->loadWorkingHours();

        return $this->workingHours;
    }

    /**
     * @param null $hourType
     *
     * @return mixed 24_7, daily or flexible
     */
    public function workingHourType($hourType = null)
    {
        return array_get($this->options, "hours.{$hourType}.type");
    }

    public function getWorkingHoursByType($type)
    {
        if (!$workingHours = $this->listWorkingHours())
            return null;

        return $workingHours->groupBy('type')->get($type);
    }

    public function getWorkingHoursByDay($weekday)
    {
        if (!$workingHours = $this->listWorkingHours())
            return null;

        return $workingHours->groupBy('weekday')->get($weekday);
    }

    public function getWorkingHourByDayAndType($weekday, $type)
    {
        if (!$workingHours = $this->getWorkingHoursByDay($weekday))
            return null;

        return $workingHours->groupBy('type')->get($type)->first();
    }

    public function getWorkingHourByDateAndType($date, $type)
    {
        if (!$date instanceof Carbon)
            $date = make_carbon($date);

        $weekday = $date->format('N') - 1;

        return $this->getWorkingHourByDayAndType($weekday, $type);
    }

    public function loadWorkingHours()
    {
        if (!$this->hasRelation('working_hours'))
            throw new Exception(sprintf("Model '%s' does not contain a definition for 'working_hours'.",
                get_class($this)));

        return $this->working_hours()->get();
    }

    public function newWorkingSchedule($type, $days = null)
    {
        $types = $this->availableWorkingTypes();
        if (is_null($type) OR !in_array($type, $types))
            throw new InvalidArgumentException("Defined parameter '$type' is not a valid working type.");

        if (is_null($days)) {
            $days = $this->hasFutureOrder($type)
                ? (int)$this->futureOrderDays($type)
                : 0;
        }

        $schedule = WorkingSchedule::create($days,
            $this->getWorkingHoursByType($type) ?? new Collection([])
        );

        $schedule->setType($type);
        $schedule->setNow($this->getCurrentTime());

        return $schedule;
    }

    //
    //
    //

    /**
     * Create a new or update existing location working hours
     *
     * @param array $data
     *
     * @return bool
     */
    public function addOpeningHours($data = [])
    {
        $this->working_hours()->delete();

        foreach ($data as $type => $schedules) {
            foreach ($schedules as $day => $hours) {
                foreach ($hours as $hour) {
                    $this->working_hours()->create([
                        'location_id' => $this->getKey(),
                        'weekday' => $hour['day'],
                        'type' => $type,
                        'opening_time' => mdate('%H:%i', strtotime($hour['open'])),
                        'closing_time' => mdate('%H:%i', strtotime($hour['close'])),
                        'status' => $hour['status'],
                    ]);
                }
            }
        }

        return TRUE;
    }

    protected function parseHoursFromOptions(&$value)
    {
        // Rename options array index 'opening_hours' to 'hours'
        if (isset($value['opening_hours'])) {
            $hours = $value['opening_hours'];
            foreach (['opening', 'daily', 'delivery', 'collection'] as $type) {
                foreach (['type', 'days', 'hours'] as $suffix) {
                    if (isset($hours["{$type}_{$suffix}"])) {
                        $valueItem = $hours["{$type}_{$suffix}"];
                        if ($suffix == 'type')
                            $valueItem = $valueItem != '24_7' ? $valueItem : '24_7';

                        $typeIndex = $type == 'daily' ? 'opening' : $type;

                        if ($suffix == 'hours') {
                            $value['hours'][$typeIndex]['open'] = $valueItem['open'] ?? '00:00';
                            $value['hours'][$typeIndex]['close'] = $valueItem['close'] ?? '23:59';
                        }
                        else {
                            $value['hours'][$typeIndex][$suffix] = $valueItem;
                        }
                    }
                }
            }

            if (isset($hours['flexible_hours']) AND is_array($hours['flexible_hours'])) {
                foreach (['opening', 'delivery', 'collection'] as $type) {
                    $value['hours'][$type]['flexible'] = $hours['flexible_hours'];
                }
            }

            unset($value['opening_hours']);
        }

        // Ensures form checkbox is unchecked when value is empty
        foreach (['opening', 'delivery', 'collection'] as $type) {
            if (!isset($value['hours'][$type]['days']))
                $value['hours'][$type]['days'] = [];
        }
    }
}
