<?php

namespace Admin\Models;

use Carbon\Carbon;
use Model;

/**
 * Status History Model Class
 */
class Status_history_model extends Model
{
    const UPDATED_AT = null;

    const CREATED_AT = 'date_added';

    /**
     * @var string The database table name
     */
    protected $table = 'status_history';

    protected $primaryKey = 'status_history_id';

    protected $guarded = [];

    protected $appends = ['staff_name', 'status_name', 'notified', 'date_added_since'];

    public $timestamps = TRUE;

    public $casts = [
        'object_id' => 'integer',
        'staff_id' => 'integer',
        'status_id' => 'integer',
        'notify' => 'boolean',
    ];

    public $relation = [
        'belongsTo' => [
            'staff' => 'Admin\Models\Staffs_model',
            'status' => ['Admin\Models\Statuses_model', 'status_id'],
        ],
        'morphTo' => [
            'object' => [],
        ],
    ];

    public static function alreadyExists($model, $statusId)
    {
        return self::where('object_id', $model->getKey())
            ->where('object_type', $model->getMorphClass())
            ->where('status_id', $statusId)->exists();
    }

    public function getStaffNameAttribute($value)
    {
        return ($this->staff AND $this->staff->exists) ? $this->staff->staff_name : $value;
    }

    public function getDateAddedSinceAttribute($value)
    {
        return $this->date_added ? time_elapsed($this->date_added) : null;
    }

    public function getStatusNameAttribute($value)
    {
        return ($this->status AND $this->status->exists) ? $this->status->status_name : $value;
    }

    public function getNotifiedAttribute()
    {
        return $this->notify == 1 ? lang('admin::lang.text_yes') : lang('admin::lang.text_no');
    }

    /**
     * @param \Igniter\Flame\Database\Model|mixed $status
     * @param \Igniter\Flame\Database\Model|mixed $object
     * @param array $options
     * @return static|bool
     */
    public static function createHistory($status, $object, $options = [])
    {
        $statusId = $status->getKey();
        $previousStatus = $object->getOriginal('status_id');

        $model = new static;
        $model->status_id = $statusId;
        $model->object_id = $object->getKey();
        $model->object_type = $object->getMorphClass();
        $model->staff_id = array_get($options, 'staff_id');
        $model->comment = array_get($options, 'comment', $status->status_comment);
        $model->notify = array_get($options, 'notify', $status->notify_customer);

        if ($model->fireSystemEvent('admin.statusHistory.beforeAddStatus', [$object, $statusId, $previousStatus], TRUE) === FALSE)
            return FALSE;

        $model->save();

        // Update using query to prevent model events from firing
        $object->newQuery()->where($object->getKeyName(), $object->getKey())->update([
            'status_id' => $statusId,
            'status_updated_at' => Carbon::now(),
        ]);

        return $model;
    }

    public function isForOrder()
    {
        return $this->object_type === Orders_model::make()->getMorphClass();
    }

    //
    //
    //

    public function scopeApplyRelated($query, $model)
    {
        return $query->where('object_type', $model->getMorphClass())
            ->where('object_id', $model->getKey());
    }

    public function scopeWhereStatusIsLatest($query, $statusId)
    {
        return $query->where('status_id', $statusId)->orderBy('date_added', 'desc');
    }
}
