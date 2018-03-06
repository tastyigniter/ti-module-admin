<?php

namespace Admin\Models;

use Igniter\Flame\Location\GeoPosition;
use Igniter\Flame\Location\Models\Area as AreaModel;

/**
 * Location areas Model Class
 *
 * @package Admin
 */
class Location_areas_model extends AreaModel
{
    protected $fillable = ['area_id', 'type', 'name', 'boundaries', 'conditions'];

    public $boundary;

    public function getConditionsAttribute($value)
    {
        // backward compatibility v2.0
        if (!is_array($conditions = unserialize($value)))
            $conditions = [];

        foreach ($conditions as $key => &$item) {
            if (isset($item['condition'])) {
                $item['type'] = $item['condition'];
                unset($item['condition']);
            }
        }

        return $conditions;
    }

    //
    // Helpers
    //
}