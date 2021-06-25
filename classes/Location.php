<?php

namespace Admin\Classes;

use Igniter\Flame\Location\Manager;

class Location extends Manager
{
    protected $sessionKey = 'admin_local_info';

    protected $locationModel = 'Admin\Models\Locations_model';

    public function check()
    {
        return (bool)$this->current();
    }

    public function current()
    {
        if (!is_null($this->model))
            return $this->model;

        if (!$this->getAuth()->isLogged())
            return null;

        if ($this->isSingleMode()) {
            $id = params('default_location_id');
        }
        else {
            $id = $this->getSession('id');
            if (!$id AND $this->hasOneLocation() AND !$this->getAuth()->isSuperUser())
                $id = $this->getDefaultLocation();

            if ($id AND !$this->isAttachedToAuth($id))
                $id = $this->getDefaultLocation();
        }

        if ($id AND $model = $this->getById($id))
            $this->setCurrent($model);

        return $this->model;
    }

    public function clearCurrent()
    {
        $this->forgetSession();
    }

    public function hasAccess($location)
    {
        if ($this->getAuth()->isSuperUser())
            return TRUE;

        return $this->getAuth()->user()->hasLocationAccess($location);
    }

    public function hasRestriction()
    {
        if ($this->getAuth()->isSuperUser())
            return FALSE;

        return $this->getAuth()->locations()->isNotEmpty();
    }

    public function isSingleMode()
    {
        return is_single_location();
    }

    public function getId()
    {
        return optional($this->getLocation())->getKey();
    }

    public function getAll()
    {
        if ($this->getAuth()->isSuperUser())
            return null;

        return $this->getLocations()->pluck('location_id')->all();
    }

    public function getIdOrAll()
    {
        return $this->check() ? [$this->getId()] : $this->getAll();
    }

    public function getLocation()
    {
        return $this->current();
    }

    public function listLocations()
    {
        if ($this->getAuth()->isSuperUser())
            return $this->createLocationModel()->getDropdownOptions();

        return $this->getLocations()->pluck('location_name', 'location_id');
    }

    public function getDefaultLocation()
    {
        if (!$staffLocation = $this->getLocations()->first())
            return null;

        return $staffLocation->getKey();
    }

    public function hasOneLocation()
    {
        if ($this->isSingleMode())
            return TRUE;

        return $this->getLocations()->count() === 1;
    }

    public function hasLocations()
    {
        if ($this->isSingleMode())
            return FALSE;

        if ($this->getAuth()->isSuperUser())
            return TRUE;

        return $this->getLocations()->count() > 1;
    }

    protected function getLocations()
    {
        return $this->getAuth()
            ->locations()
            ->where('location_status', TRUE);
    }

    /**
     * @return \Admin\Classes\User
     */
    protected function getAuth()
    {
        return app('admin.auth');
    }

    protected function isAttachedToAuth($id)
    {
        if ($this->getAuth()->isSuperUser())
            return TRUE;

        return $this->getLocations()->contains(function ($model) use ($id) {
            return $model->location_id === $id;
        });
    }
}
