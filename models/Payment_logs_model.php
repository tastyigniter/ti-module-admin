<?php

namespace Admin\Models;

use Model;

/**
 * Payment logs Model Class
 */
class Payment_logs_model extends Model
{
    const UPDATED_AT = 'date_updated';

    const CREATED_AT = 'date_added';

    /**
     * @var string The database table name
     */
    protected $table = 'payment_logs';

    /**
     * @var string The database table primary key
     */
    protected $primaryKey = 'payment_log_id';

    protected $appends = ['date_added_since'];

    public $timestamps = TRUE;

    public $casts = [
        'order_id' => 'integer',
        'request' => 'array',
        'response' => 'array',
        'status' => 'boolean',
    ];

    public static function logAttempt($order, $message, $isSuccess, $request = [], $response = [])
    {
        $record = new static;
        $record->message = $message;
        $record->order_id = $order->order_id;
        $record->payment_name = $order->payment_method->name;
        $record->is_success = $isSuccess;
        $record->request = $request;
        $record->response = $response;

        $record->save();
    }

    public function getDateAddedSinceAttribute($value)
    {
        return $this->date_added ? time_elapsed($this->date_added) : null;
    }
}
