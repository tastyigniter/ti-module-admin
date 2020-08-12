<?php

namespace Admin\Traits;

use AdminLocation;
use Igniter\Flame\Database\Model;

trait Locationable
{
    /*
     * You can change the location relation name:
     *
     * const LOCATIONABLE_RELATION = 'location';
     */

    /**
     * @var bool Flag for arbitrarily enabling location scope.
     */
    public $locationScopeEnabled = FALSE;

    protected $locationableAttributes;

    /**
     * Boot the locationable trait for a model.
     *
     * @return void
     */
    public static function bootLocationable()
    {
        static::saving(function (Model $model) {
            $model->purgeLocationableAttributes();
            $model->setLocationableAttributes();
        });

        static::saved(function (Model $model) {
            $model->syncLocationsOnSave();
        });

        static::deleting(function (Model $model) {
            $model->detachLocationsOnDelete();
        });
    }

    public function locationableScopeEnabled()
    {
        if ($this->locationScopeEnabled)
            return TRUE;

        return AdminLocation::check();
    }

    public function locationableGetUserLocation()
    {
        return AdminLocation::getId();
    }

    //
    //
    //

    public function scopeWhereHasLocation($query, $locationId)
    {
        return $this->applyLocationScope($query, $locationId);
    }

    public function scopeWhereHasOrDoesntHaveLocation($query, $locationId)
    {
        return $query->whereHasLocation($locationId)
                     ->orDoesntHave($this->locationableRelationName());
    }

    /**
     * Apply the Location scope query.
     *
     * @param \Igniter\Flame\Database\Builder $builder
     * @param \Igniter\Flame\Auth\Models\User $userLocation
     */
    protected function applyLocationScope($builder, $userLocation)
    {
        $locationId = !is_numeric($userLocation)
            ? $userLocation->getKey() : $userLocation;

        $relationName = $this->locationableRelationName();
        $relationObject = $this->getLocationableRelationObject();
        $locationModel = $relationObject->getRelated();

        if ($this->locationableIsSingleRelationType()) {
            $builder->where($locationModel->getKeyName(), $locationId);
        }
        else {
            $qualifiedColumnName = $relationObject->getTable().'.'.$locationModel->getKeyName();
            $builder->whereHas($relationName, function ($query) use ($qualifiedColumnName, $locationId) {
                $query->where($qualifiedColumnName, $locationId);
            });
        }
    }

    //
    //
    //

    protected function purgeLocationableAttributes()
    {
        $attributes = $this->getAttributes();
        $relationName = $this->locationableRelationName();
        $cleanAttributes = array_except($attributes, [$relationName]);
        $this->locationableAttributes = array_get($attributes, $relationName) ?? [];

        return $this->attributes = $cleanAttributes;
    }

    protected function setLocationableAttributes()
    {
        if (!$this->locationableScopeEnabled())
            return;

        $locationsToSync = $this->locationableAttributes;
        if (count($locationsToSync))
            return;

        $this->locationableAttributes = null;
        if ($this->locationableRelationExists())
            return;

        if ($this->locationableIsSingleRelationType()) {
            $relationObj = $this->getLocationableRelationObject();
            $attributeName = $relationObj->getForeignKeyName();
            $this->{$attributeName} = $this->locationableGetUserLocation();
        }
        else {
            $this->locationableAttributes = [$this->locationableGetUserLocation()];
        }
    }

    protected function syncLocationsOnSave()
    {
        if ($this->locationableIsSingleRelationType())
            return;

        $locationsToSync = $this->locationableAttributes;
        if (is_null($locationsToSync))
            return;

        $this->getLocationableRelationObject()->sync($locationsToSync);
    }

    protected function detachLocationsOnDelete()
    {
        if ($this->locationableIsSingleRelationType())
            return;

        $this->getLocationableRelationObject()->detach();
    }

    //
    //
    //

    protected function getLocationableRelationObject()
    {
        $relationName = $this->locationableRelationName();

        return $this->{$relationName}();
    }

    protected function locationableIsSingleRelationType()
    {
        $relationType = $this->getRelationType($this->locationableRelationName());

        return in_array($relationType, ['hasOne', 'belongsTo']);
    }

    protected function locationableRelationName()
    {
        return defined('static::LOCATIONABLE_RELATION') ? static::LOCATIONABLE_RELATION : 'location';
    }

    protected function locationableRelationExists()
    {
        $relationName = $this->locationableRelationName();

        if ($this->locationableIsSingleRelationType()) {
            return !is_null($this->{$relationName});
        }

        return count($this->{$relationName});
    }
}
