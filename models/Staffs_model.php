<?php

namespace Admin\Models;

use Admin\Classes\UserState;
use Admin\Traits\Locationable;
use Igniter\Flame\Database\Traits\Purgeable;
use Model;

/**
 * Staffs Model Class
 */
class Staffs_model extends Model
{
    use Purgeable;
    use Locationable;

    const UPDATED_AT = null;

    const CREATED_AT = 'date_added';

    const LOCATIONABLE_RELATION = 'locations';

    /**
     * @var string The database table name
     */
    protected $table = 'staffs';

    /**
     * @var string The database table primary key
     */
    protected $primaryKey = 'staff_id';

    /**
     * @var array The model table column to convert to dates on insert/update
     */
    public $timestamps = TRUE;

    protected $guarded = [];

    protected $casts = [
        'staff_role_id' => 'integer',
        'staff_location_id' => 'integer',
        'sale_permission' => 'integer',
        'language_id' => 'integer',
        'staff_status' => 'boolean',
    ];

    public $relation = [
        'hasOne' => [
            'user' => ['Admin\Models\Users_model', 'foreignKey' => 'staff_id', 'otherKey' => 'staff_id', 'delete' => TRUE],
        ],
        'hasMany' => [
            'assignable_logs' => ['Admin\Models\Assignable_logs_model', 'foreignKey' => 'assignee_id'],
        ],
        'belongsTo' => [
            'role' => ['Admin\Models\Staff_roles_model', 'foreignKey' => 'staff_role_id'],
            'language' => ['System\Models\Languages_model'],
        ],
        'belongsToMany' => [
            'groups' => ['Admin\Models\Staff_groups_model', 'table' => 'staffs_groups'],
        ],
        'morphToMany' => [
            'locations' => ['Admin\Models\Locations_model', 'name' => 'locationable'],
        ],
    ];

    protected $hidden = ['password'];

    protected $purgeable = ['user'];

    public function getFullNameAttribute($value)
    {
        return $this->staff_name;
    }

    public function getEmailAttribute()
    {
        return $this->staff_email;
    }

    public static function getDropdownOptions()
    {
        return static::isEnabled()->dropdown('staff_name');
    }

    //
    // Scopes
    //

    /**
     * Scope a query to only include enabled staff
     * @return $this
     */
    public function scopeIsEnabled($query)
    {
        return $query->where('staff_status', 1);
    }

    public function scopeWhereNotSuperUser($query)
    {
        $query->whereHas('user', function ($q) {
            $q->where('super_user', '!=', 1);
        });
    }

    public function scopeWhereIsSuperUser($query)
    {
        $query->whereHas('user', function ($q) {
            $q->where('super_user', 1);
        });
    }

    //
    // Events
    //

    protected function afterSave()
    {
        $this->restorePurgedValues();

        if (array_key_exists('user', $this->attributes))
            $this->addStaffUser($this->attributes['user']);
    }

    protected function beforeDelete()
    {
        $this->groups()->detach();
        $this->locations()->detach();
    }

    //
    // Helpers
    //

    /**
     * Return the dates of all staff
     * @return array
     */
    public function getStaffDates()
    {
        return $this->pluckDates('date_added');
    }

    public function addStaffUser($user = [])
    {
        $userModel = $this->user()->firstOrNew(['staff_id' => $this->getKey()]);

        $userModel->super_user = array_get($user, 'super_user', FALSE);
        $userModel->username = array_get($user, 'username', $userModel->username);
        $userModel->password = array_get($user, 'password', $userModel->password);

        if (!$userModel->exists) {
            $userModel->is_activated = TRUE;
            $userModel->date_activated = date('Y-m-d');
        }

        $userModel->save();
    }

    /**
     * Create a new or update existing staff locations
     *
     * @param array $locations
     *
     * @return bool
     */
    public function addStaffLocations($locations = [])
    {
        return $this->locations()->sync($locations);
    }

    /**
     * Create a new or update existing staff groups
     *
     * @param array $groups
     *
     * @return bool
     */
    public function addStaffGroups($groups = [])
    {
        return $this->groups()->sync($groups);
    }

    /**
     * Send email to staff
     *
     * @param string $email
     * @param array $template
     * @param array $data
     *
     * @return bool
     */
    public function sendMail($email, $template, $data = [])
    {
        return Users_model::sendMail($email, $template, $data);
    }

    //
    //
    //

    public function canAssignTo()
    {
        return !UserState::forUser($this->user)->isAway();
    }

    public function hasGlobalAssignableScope()
    {
        return $this->sale_permission === 1;
    }

    public function hasGroupAssignableScope()
    {
        return $this->sale_permission === 2;
    }

    public function hasRestrictedAssignableScope()
    {
        return $this->sale_permission === 3;
    }
}
