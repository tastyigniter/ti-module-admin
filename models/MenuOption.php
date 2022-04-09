<?php

namespace Admin\Models;

use Admin\Facades\AdminLocation;
use Admin\Traits\Locationable;
use Igniter\Flame\Database\Model;
use Igniter\Flame\Database\Traits\Purgeable;

/**
 * MenuOption Model Class
 */
class MenuOption extends Model
{
    use Locationable;
    use Purgeable;

    const LOCATIONABLE_RELATION = 'locations';

    /**
     * @var string The database table name
     */
    protected $table = 'menu_options';

    /**
     * @var string The database table primary key
     */
    protected $primaryKey = 'option_id';

    protected $fillable = ['option_id', 'option_name', 'display_type', 'update_related_menu_item'];

    protected $casts = [
        'option_id' => 'integer',
        'priority' => 'integer',
        'is_required' => 'boolean',
    ];

    public $relation = [
        'hasMany' => [
            'option_values' => [\Admin\Models\MenuOptionValue::class, 'foreignKey' => 'option_id', 'delete' => TRUE],
            'menu_option_values' => [\Admin\Models\MenuItemOptionValue::class, 'foreignKey' => 'option_id', 'delete' => TRUE],
        ],
        'morphToMany' => [
            'locations' => [\Admin\Models\Location::class, 'name' => 'locationable'],
        ],
    ];

    protected $purgeable = ['option_values'];

    public $timestamps = TRUE;

    public static function getRecordEditorOptions()
    {
        $query = self::selectRaw('option_id, concat(option_name, " (", display_type, ")") AS display_name');

        if (!is_null($ids = AdminLocation::getIdOrAll()))
            $query->whereHasLocation($ids);

        return $query->dropdown('display_name');
    }

    public static function getDisplayTypeOptions()
    {
        return [
            'radio' => 'lang:admin::lang.menu_options.text_radio',
            'checkbox' => 'lang:admin::lang.menu_options.text_checkbox',
            'select' => 'lang:admin::lang.menu_options.text_select',
            'quantity' => 'lang:admin::lang.menu_options.text_quantity',
        ];
    }

    //
    // Events
    //

    protected function afterSave()
    {
        $this->restorePurgedValues();

        if (array_key_exists('option_values', $this->attributes))
            $this->addOptionValues($this->attributes['option_values']);

        if ($this->update_related_menu_item)
            $this->updateRelatedMenuItemsOptionValues();
    }

    protected function beforeDelete()
    {
        $this->locations()->detach();
    }

    //
    // Helpers
    //

    public function isRequired()
    {
        return $this->is_required;
    }

    /**
     * Return all option values by option_id
     *
     * @param int $option_id
     *
     * @return array
     */
    public static function getOptionValues($option_id = null)
    {
        $query = self::orderBy('priority')->from('option_values');

        if ($option_id !== FALSE) {
            $query->where('option_id', $option_id);
        }

        return $query->get();
    }

    /**
     * Create a new or update existing option values
     *
     * @param array $optionValues
     *
     * @return bool
     */
    public function addOptionValues($optionValues = [])
    {
        $optionId = $this->getKey();

        $idsToKeep = [];
        foreach ($optionValues as $value) {
            if (!array_key_exists('allergens', $value))
                $value['allergens'] = [];

            $optionValue = $this->option_values()->firstOrNew([
                'option_value_id' => array_get($value, 'option_value_id'),
                'option_id' => $optionId,
            ])->fill(array_except($value, ['option_value_id', 'option_id']));

            $optionValue->saveOrFail();
            $idsToKeep[] = $optionValue->getKey();
        }

        $this->option_values()->where('option_id', $optionId)
            ->whereNotIn('option_value_id', $idsToKeep)->delete();

        $this->menu_option_values()
            ->whereNotIn('option_value_id', $idsToKeep)->delete();

        return count($idsToKeep);
    }

    /**
     * Overwrite any menu items this option is attached to
     *
     * @return void
     */
    protected function updateRelatedMenuItemsOptionValues()
    {
        $optionValues = $this->option_values()->get()->map(function ($optionValue) {
            return [
                'menu_option_id' => $this->option_id,
                'option_value_id' => $optionValue->option_value_id,
                'new_price' => $optionValue->price,
                'quantity' => 0,
                'priority' => $optionValue->priority,
            ];
        })->all();

        $this->menu_options->each(function ($menuOption) use ($optionValues) {
            $menuOption->addMenuOptionValues($optionValues);
        });
    }
}
