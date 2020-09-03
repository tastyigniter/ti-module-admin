<?php

namespace Admin\Models;

use Admin\Traits\Assignable;
use Admin\Traits\HasInvoice;
use Admin\Traits\Locationable;
use Admin\Traits\LogsStatusHistory;
use Admin\Traits\ManagesOrderItems;
use Carbon\Carbon;
use Event;
use Igniter\Flame\Auth\Models\User;
use Main\Classes\MainController;
use Model;
use Request;
use System\Traits\SendsMailTemplate;

/**
 * Orders Model Class
 */
class Orders_model extends Model
{
    use HasInvoice;
    use ManagesOrderItems;
    use LogsStatusHistory;
    use SendsMailTemplate;
    use Locationable;
    use Assignable;

    const CREATED_AT = 'date_added';

    const UPDATED_AT = 'date_modified';

    const DELIVERY = 'delivery';

    const COLLECTION = 'collection';

    protected static $orderTypes = [1 => self::DELIVERY, 2 => self::COLLECTION];

    /**
     * @var string The database table name
     */
    protected $table = 'orders';

    /**
     * @var string The database table primary key
     */
    protected $primaryKey = 'order_id';

    protected $timeFormat = 'H:i';

    public $guarded = ['ip_address', 'user_agent', 'hash'];

    protected $hidden = ['cart'];

    /**
     * @var array The model table column to convert to dates on insert/update
     */
    public $timestamps = TRUE;

    public $casts = [
        'customer_id' => 'integer',
        'location_id' => 'integer',
        'address_id' => 'integer',
        'total_items' => 'integer',
        'cart' => 'serialize',
        'order_date' => 'date',
        'order_time' => 'time',
        'order_total' => 'float',
        'notify' => 'boolean',
        'processed' => 'boolean',
    ];

    public $relation = [
        'belongsTo' => [
            'customer' => 'Admin\Models\Customers_model',
            'location' => 'Admin\Models\Locations_model',
            'address' => 'Admin\Models\Addresses_model',
            'payment_method' => ['Admin\Models\Payments_model', 'foreignKey' => 'payment', 'otherKey' => 'code'],
        ],
        'hasMany' => [
            'payment_logs' => 'Admin\Models\Payment_logs_model',
            'coupon_history' => 'Admin\Models\Coupons_history_model',
        ],
        'morphMany' => [
            'review' => ['Admin\Models\Reviews_model'],
        ],
    ];

    public static $allowedSortingColumns = [
        'order_id asc', 'order_id desc',
        'date_added asc', 'date_added desc',
    ];

    public function listCustomerAddresses()
    {
        if (!$this->customer)
            return [];

        return $this->customer->addresses()->get();
    }

    //
    // Events
    //

    protected function beforeCreate()
    {
        $this->generateHash();

        $this->ip_address = Request::getClientIp();
        $this->user_agent = Request::userAgent();
    }

    //
    // Scopes
    //

    public function scopeListFrontEnd($query, $options = [])
    {
        extract(array_merge([
            'page' => 1,
            'pageLimit' => 20,
            'customer' => null,
            'location' => null,
            'sort' => 'address_id desc',
        ], $options));

        $query->where('status_id', '>=', 1);

        if ($location instanceof Locations_model) {
            $query->where('location_id', $location->getKey());
        }
        elseif (strlen($location)) {
            $query->where('location_id', $location);
        }

        if ($customer instanceof User) {
            $query->where('customer_id', $customer->getKey());
        }
        elseif (strlen($customer)) {
            $query->where('customer_id', $customer);
        }

        if (!is_array($sort)) {
            $sort = [$sort];
        }

        foreach ($sort as $_sort) {
            if (in_array($_sort, self::$allowedSortingColumns)) {
                $parts = explode(' ', $_sort);
                if (count($parts) < 2) {
                    array_push($parts, 'desc');
                }
                [$sortField, $sortDirection] = $parts;
                $query->orderBy($sortField, $sortDirection);
            }
        }

        return $query->paginate($pageLimit, $page);
    }

    //
    // Accessors & Mutators
    //

    public function getCustomerNameAttribute($value)
    {
        return $this->first_name.' '.$this->last_name;
    }

    public function getOrderTypeAttribute($value)
    {
        if (isset(self::$orderTypes[$value]))
            return self::$orderTypes[$value];

        return $value;
    }

    public function getOrderTypeNameAttribute()
    {
        return lang('admin::lang.orders.text_'.$this->order_type);
    }

    public function getFormattedAddressAttribute($value)
    {
        return $this->address ? $this->address->formatted_address : null;
    }

    //
    // Helpers
    //

    public function isCompleted()
    {
        if (!$this->isPaymentProcessed())
            return FALSE;

        return $this->status_history()->where(
            'status_id', setting('completed_order_status')
        )->exists();
    }

    /**
     * Check if an order was successfully placed
     *
     * @param int $order_id
     *
     * @return bool TRUE on success, or FALSE on failure
     */
    public function isPaymentProcessed()
    {
        return $this->processed AND !empty($this->status_id);
    }

    public function isDeliveryType()
    {
        return $this->order_type == static::DELIVERY;
    }

    public function isCollectionType()
    {
        return $this->order_type == static::COLLECTION;
    }

    /**
     * Return the dates of all orders
     *
     * @return array
     */
    public function getOrderDates()
    {
        return $this->pluckDates('date_added');
    }

    public function markAsPaymentProcessed()
    {
        Event::fire('admin.order.beforePaymentProcessed', [$this]);

        $this->processed = 1;
        $this->save();

        Event::fire('admin.order.paymentProcessed', [$this]);

        return $this->processed;
    }

    public function logPaymentAttempt($message, $isSuccess, $request = [], $response = [])
    {
        Payment_logs_model::logAttempt($this, $message, $isSuccess, $request, $response);
    }

    public function updateOrderStatus($id, $options = [])
    {
        $id = $id ?? $this->status_id ?? setting('default_order_status');

        return $this->addStatusHistory(
            Statuses_model::find($id), $options
        );
    }

    /**
     * Generate a unique hash for this order.
     * @return string
     */
    protected function generateHash()
    {
        $this->hash = $this->createHash();
        while ($this->newQuery()->where('hash', $this->hash)->count() > 0) {
            $this->hash = $this->createHash();
        }
    }

    /**
     * Create a hash for this order.
     * @return string
     */
    protected function createHash()
    {
        return md5(uniqid('order', microtime()));
    }

    //
    // Mail
    //

    public function mailGetRecipients($type)
    {
        $emailSetting = setting('order_email');
        is_array($emailSetting) OR $emailSetting = [];

        $recipients = [];
        if (in_array($type, $emailSetting)) {
            switch ($type) {
                case 'customer':
                    $recipients[] = [$this->email, $this->customer_name];
                    break;
                case 'location':
                    $recipients[] = [$this->location->location_email, $this->location->location_name];
                    break;
                case 'admin':
                    $recipients[] = [setting('site_email'), setting('site_name')];
                    break;
            }
        }

        return $recipients;
    }

    /**
     * Return the order data to build mail template
     *
     * @return array
     */
    public function mailGetData()
    {
        $data = [];

        $model = $this->fresh();
        $data['order_number'] = $model->order_id;
        $data['order_id'] = $model->order_id;
        $data['first_name'] = $model->first_name;
        $data['last_name'] = $model->last_name;
        $data['customer_name'] = $model->customer_name;
        $data['email'] = $model->email;
        $data['telephone'] = $model->telephone;
        $data['order_comment'] = $model->comment;

        $data['order_type'] = $model->order_type_name;
        $data['order_time'] = Carbon::createFromTimeString($model->order_time)->format(setting('time_format'));
        $data['order_date'] = $model->order_date->format(setting('date_format'));
        $data['order_added'] = $model->date_added->format(setting('date_format'));

        $data['invoice_id'] = $model->invoice_number;
        $data['invoice_number'] = $model->invoice_number;
        $data['invoice_date'] = $model->invoice_date ? $model->invoice_date->format(setting('date_format')) : null;

        $data['order_payment'] = ($model->payment_method)
            ? $model->payment_method->name
            : lang('admin::lang.orders.text_no_payment');

        $data['order_menus'] = [];
        $menus = $model->getOrderMenus();
        $menuOptions = $model->getOrderMenuOptions();
        foreach ($menus as $menu) {
            $optionData = [];
            if ($menuItemOptions = $menuOptions->get($menu->order_menu_id)) {
                foreach ($menuItemOptions as $menuItemOption) {
                    $optionData[] = $menuItemOption->quantity
                        .'&nbsp;'.lang('admin::lang.text_times').'&nbsp;'
                        .$menuItemOption->order_option_name
                        .lang('admin::lang.text_equals')
                        .currency_format($menuItemOption->order_option_price);
                }
            }

            $data['order_menus'][] = [
                'menu_name' => $menu->name,
                'menu_quantity' => $menu->quantity,
                'menu_price' => currency_format($menu->price),
                'menu_subtotal' => currency_format($menu->subtotal),
                'menu_options' => implode('<br /> ', $optionData),
                'menu_comment' => $menu->comment,
            ];
        }

        $data['order_totals'] = [];
        $orderTotals = $model->getOrderTotals();
        foreach ($orderTotals as $total) {
            $data['order_totals'][] = [
                'order_total_title' => htmlspecialchars_decode($total->title),
                'order_total_value' => currency_format($total->value),
                'priority' => $total->priority,
            ];
        }

        $data['order_address'] = lang('admin::lang.orders.text_collection_order_type');
        if ($model->address)
            $data['order_address'] = format_address($model->address->toArray(), FALSE);

        if ($model->location) {
            $data['location_name'] = $model->location->location_name;
            $data['location_email'] = $model->location->location_email;
            $data['location_address'] = format_address($model->location->getAddress());
        }

        $statusHistory = Status_history_model::applyRelated($model)->whereStatusIsLatest($model->status_id)->first();
        $data['status_name'] = $statusHistory ? optional($statusHistory->status)->status_name : null;
        $data['status_comment'] = $statusHistory ? $statusHistory->comment : null;

        $controller = MainController::getController() ?: new MainController;
        $data['order_view_url'] = $controller->pageUrl('account/order', [
            'hash' => $model->hash,
        ]);

        return $data;
    }
}
