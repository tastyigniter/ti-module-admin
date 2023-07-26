<?php

namespace Admin\Models;

use Admin\Traits\Locationable;
use Admin\Traits\Stockable;
use Carbon\Carbon;
use Igniter\Flame\Database\Attach\HasMedia;
use Igniter\Flame\Database\Model;
use Igniter\Flame\Database\Traits\Purgeable;

/**
 * Menus Model Class
 */
class Menus_model extends Model
{
    use Purgeable;
    use Locationable;
    use HasMedia;
    use Stockable;

    const LOCATIONABLE_RELATION = 'locations';

    /**
     * @var string The database table name
     */
    protected $table = 'menus';

    /**
     * @var string The database table primary key
     */
    protected $primaryKey = 'menu_id';

    protected $guarded = [];

    protected $casts = [
        'menu_price' => 'float',
        'menu_category_id' => 'integer',
        'minimum_qty' => 'integer',
        'order_restriction' => 'array',
        'menu_status' => 'boolean',
        'menu_priority' => 'integer',
    ];

    public $relation = [
        'hasMany' => [
            'menu_options' => ['Admin\Models\Menu_item_options_model', 'delete' => true],
        ],
        'hasOne' => [
            'special' => ['Admin\Models\Menus_specials_model', 'delete' => true],
        ],
        'belongsToMany' => [
            'categories' => ['Admin\Models\Categories_model', 'table' => 'menu_categories'],
            'mealtimes' => ['Admin\Models\Mealtimes_model', 'table' => 'menu_mealtimes'],
        ],
        'morphToMany' => [
            'allergens' => ['Admin\Models\Allergens_model', 'name' => 'allergenable'],
            'locations' => ['Admin\Models\Locations_model', 'name' => 'locationable'],
        ],
    ];

    protected $purgeable = ['menu_options', 'special'];

    public $mediable = ['thumb'];

    public static $allowedSortingColumns = ['menu_priority asc', 'menu_priority desc'];

    public $timestamps = true;

    public function getMenuPriceFromAttribute()
    {
        if (!$this->menu_options)
            return $this->menu_price;

        return $this->menu_options->mapWithKeys(function ($option) {
            return $option->menu_option_values->keyBy('menu_option_value_id');
        })->min('price') ?: 0;
    }

    public function getMinimumQtyAttribute($value)
    {
        return $value ?: 1;
    }

    //
    // Scopes
    //
    public function scopeWhereHasAllergen($query, $allergenId)
    {
        $query->whereHas('allergens', function ($q) use ($allergenId) {
            $q->where('allergens.allergen_id', $allergenId);
        });
    }

    public function scopeWhereHasCategory($query, $categoryId)
    {
        $query->whereHas('categories', function ($q) use ($categoryId) {
            $q->where('categories.category_id', $categoryId);
        });
    }

    public function scopeWhereHasMealtime($query, $mealtimeId)
    {
        $query->whereHas('mealtimes', function ($q) use ($mealtimeId) {
            $q->where('mealtimes.mealtime_id', $mealtimeId);
        });
    }

    public function scopeListFrontEnd($query, $options = [])
    {
        extract(array_merge([
            'page' => 1,
            'pageLimit' => 20,
            'enabled' => true,
            'sort' => 'menu_priority asc',
            'group' => null,
            'location' => null,
            'category' => null,
            'search' => '',
            'orderType' => null,
        ], $options));

        $searchableFields = ['menu_name', 'menu_description'];

        if (strlen($location) && is_numeric($location)) {
            $query->whereHasOrDoesntHaveLocation($location);
            $query->with(['categories' => function ($q) use ($location) {
                $q->whereHasOrDoesntHaveLocation($location);
                $q->isEnabled();
            }]);
        }

        if (strlen($category)) {
            $query->whereHas('categories', function ($q) use ($category) {
                $q->whereSlug($category);
            });
        }

        if (!is_array($sort)) {
            $sort = [$sort];
        }

        foreach ($sort as $_sort) {
            if (in_array($_sort, self::$allowedSortingColumns)) {
                $parts = explode(' ', $_sort);
                if (count($parts) < 2) {
                    $parts[] = 'desc';
                }
                [$sortField, $sortDirection] = $parts;
                $query->orderBy($sortField, $sortDirection);
            }
        }

        $search = trim($search);
        if (strlen($search)) {
            $query->search($search, $searchableFields);
        }

        if (strlen($group)) {
            $query->whereHas('categories', function ($q) use ($group) {
                $q->groupBy($group);
            });
        }

        if ($enabled) {
            $query->isEnabled();
        }

        if ($orderType) {
            $query->where(function ($query) use ($orderType) {
                $query->whereNull('order_restriction')
                    ->orWhere('order_restriction', 'like', '%"'.$orderType.'"%');
            });
        }

        $this->fireEvent('model.extendListFrontEndQuery', [$query]);

        return $query->paginate($pageLimit, $page);
    }

    public function scopeIsEnabled($query)
    {
        return $query->where('menu_status', 1);
    }

    //
    // Events
    //

    protected function afterSave()
    {
        $this->restorePurgedValues();

        if (array_key_exists('menu_options', $this->attributes))
            $this->addMenuOption((array)$this->attributes['menu_options']);

        if (array_key_exists('special', $this->attributes))
            $this->addMenuSpecial((array)$this->attributes['special']);
    }

    protected function beforeDelete()
    {
        $this->categories()->detach();
        $this->mealtimes()->detach();
        $this->allergens()->detach();
        $this->locations()->detach();
    }

    //
    // Helpers
    //

    public function hasOptions()
    {
        return count($this->menu_options);
    }

    /**
     * Subtract or add to menu stock quantity
     *
     * @param int $quantity
     * @param bool $subtract
     * @return bool TRUE on success, or FALSE on failure
     */
    public function updateStock($quantity = 0, $subtract = true)
    {
        traceLog('Menus_model::updateStock() has been deprecated, use Stocks_model::updateStock() instead.');
    }

    /**
     * Create new or update existing menu allergens
     *
     * @param array $allergenIds if empty all existing records will be deleted
     *
     * @return bool
     */
    public function addMenuAllergens(array $allergenIds = [])
    {
        if (!$this->exists)
            return false;

        $this->allergens()->sync($allergenIds);
    }

    /**
     * Create new or update existing menu categories
     *
     * @param array $categoryIds if empty all existing records will be deleted
     *
     * @return bool
     */
    public function addMenuCategories(array $categoryIds = [])
    {
        if (!$this->exists)
            return false;

        $this->categories()->sync($categoryIds);
    }

    /**
     * Create new or update existing menu mealtimes
     *
     * @param array $mealtimeIds if empty all existing records will be deleted
     *
     * @return bool
     */
    public function addMenuMealtimes(array $mealtimeIds = [])
    {
        if (!$this->exists)
            return false;

        $this->mealtimes()->sync($mealtimeIds);
    }

    /**
     * Create new or update existing menu options
     *
     * @param array $menuOptions if empty all existing records will be deleted
     *
     * @return bool
     */
    public function addMenuOption(array $menuOptions = [])
    {
        $menuId = $this->getKey();
        if (!is_numeric($menuId))
            return false;

        $idsToKeep = [];
        foreach ($menuOptions as $option) {
            $option['menu_id'] = $menuId;
            $menuOption = $this->menu_options()->firstOrNew([
                'menu_option_id' => array_get($option, 'menu_option_id'),
            ])->fill(array_except($option, ['menu_option_id']));

            $menuOption->saveOrFail();
            $idsToKeep[] = $menuOption->getKey();
        }

        $this->menu_options()->whereNotIn('menu_option_id', $idsToKeep)->delete();

        return count($idsToKeep);
    }

    /**
     * Create new or update existing menu special
     *
     * @param bool $id
     * @param array $menuSpecial
     *
     * @return bool
     */
    public function addMenuSpecial(array $menuSpecial = [])
    {
        $menuId = $this->getKey();
        if (!is_numeric($menuId))
            return false;

        $menuSpecial['menu_id'] = $menuId;
        $this->special()->updateOrCreate([
            'special_id' => $menuSpecial['special_id'] ?? null,
        ], array_except($menuSpecial, 'special_id'));
    }

    /**
     * Is menu item available on a given datetime
     *
     * @param string | \Carbon\Carbon $datetime
     *
     * @return bool
     */
    public function isAvailable($datetime = null)
    {
        if (is_null($datetime))
            $datetime = Carbon::now();

        if (!$datetime instanceof Carbon) {
            $datetime = Carbon::parse($datetime);
        }

        $isAvailable = true;

        if (count($this->mealtimes) > 0) {
            $isAvailable = false;
            foreach ($this->mealtimes as $mealtime) {
                if ($mealtime->mealtime_status) {
                    $isAvailable = $isAvailable || $mealtime->isAvailable($datetime);
                }
            }
        }

        if (is_bool($eventResults = $this->fireSystemEvent('admin.menu.isAvailable', [$datetime, $isAvailable], true)))
            $isAvailable = $eventResults;

        return $isAvailable;
    }
}
