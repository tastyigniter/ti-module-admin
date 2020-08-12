<?php

namespace Admin\Models;

use Carbon\Carbon;
use Model;

/**
 * Menu Specials Model Class
 */
class Menus_specials_model extends Model
{
    /**
     * @var string The database table name
     */
    protected $table = 'menus_specials';

    protected $primaryKey = 'special_id';

    protected $fillable = [
        'menu_id', 'start_date',
        'end_date', 'special_price',
        'special_status', 'type',
        'validity', 'recurring_every',
        'recurring_from', 'recurring_to',
    ];

    public $casts = [
        'menu_id' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'special_price' => 'float',
        'special_status' => 'boolean',
        'recurring_from' => 'time',
        'recurring_to' => 'time',
        'recurring_every' => 'array',
    ];

    public static function getRecurringEveryOptions()
    {
        return ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    }

    public function getPrettyEndDateAttribute()
    {
        if ($this->isRecurring() OR !$this->end_date)
            return null;

        return mdate(setting('date_format'), $this->end_date->getTimestamp());
    }

    public function getTypeAttribute($value)
    {
        return empty($value) ? 'F' : $value;
    }

    public function getValidityAttribute($value)
    {
        return empty($value) ? 'forever' : $value;
    }

    public function active()
    {
        if (!$this->special_status)
            return FALSE;

        return !($this->isExpired() === TRUE);
    }

    public function daysRemaining()
    {
        if ($this->validity != 'period' OR !$this->end_date->greaterThan(Carbon::now()))
            return 0;

        return $this->end_date->diffForHumans();
    }

    public function isRecurring()
    {
        return $this->validity == 'recurring';
    }

    public function isExpired()
    {
        $now = Carbon::now();

        switch ($this->validity) {
            case 'forever':
                return FALSE;
            case 'period':
                return !$now->between($this->start_date, $this->end_date);
            case 'recurring':
                if (!in_array($now->format('w'), $this->recurring_every ?? []))
                    return TRUE;

                $start = $now->copy()->setTimeFromTimeString($this->recurring_from);
                $end = $now->copy()->setTimeFromTimeString($this->recurring_to);

                return !$now->between($start, $end);
        }
    }

    public function isFixed()
    {
        return $this->type !== 'P';
    }

    public function getMenuPrice($price)
    {
        if ($this->isFixed())
            return $this->special_price;

        return $price - (($price / 100) * round($this->special_price));
    }
}
