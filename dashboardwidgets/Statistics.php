<?php

namespace Admin\DashboardWidgets;

use Admin\Classes\BaseDashboardWidget;
use Admin\Models\Customers_model;
use Admin\Models\Orders_model;
use Admin\Models\Reservations_model;
use Carbon\Carbon;

/**
 * Statistic dashboard widget.
 */
class Statistics extends BaseDashboardWidget
{
    /**
     * @var string A unique alias to identify this widget.
     */
    protected $defaultAlias = 'statistics';

    /**
     * Renders the widget.
     */
    public function render()
    {
        $this->prepareVars();

        return $this->makePartial('statistics/statistics');
    }

    public function defineProperties()
    {
        return [
            'context' => [
                'label' => 'admin::lang.dashboard.text_context',
                'default' => 'sale',
                'type' => 'select',
                'options' => $this->getContextOptions(),
            ],
            'range' => [
                'label' => 'admin::lang.dashboard.text_range',
                'default' => 'week',
                'type' => 'select',
                'options' => [
                    'day' => 'lang:admin::lang.dashboard.text_today',
                    'week' => 'lang:admin::lang.dashboard.text_week',
                    'month' => 'lang:admin::lang.dashboard.text_month',
                    'year' => 'lang:admin::lang.dashboard.text_year',
                ],
            ],
        ];
    }

    public function listContext()
    {
        return [
            'sale' => [
                'label' => 'lang:admin::lang.dashboard.text_total_sale',
                'icon' => ' bg-success text-white fa fa-line-chart',
            ],
            'lost_sale' => [
                'label' => 'lang:admin::lang.dashboard.text_total_lost_sale',
                'icon' => ' bg-danger text-white fa fa-line-chart fa-rotate-180',
            ],
            'cash_payment' => [
                'label' => 'lang:admin::lang.dashboard.text_total_cash_payment',
                'icon' => ' bg-warning text-white fa fa-money-bill',
            ],
            'customer' => [
                'label' => 'lang:admin::lang.dashboard.text_total_customer',
                'icon' => ' bg-info text-white fa fa-users',
            ],
            'order' => [
                'label' => 'lang:admin::lang.dashboard.text_total_order',
                'icon' => ' bg-success text-white fa fa-shopping-cart',
            ],
            'delivery_order' => [
                'label' => 'lang:admin::lang.dashboard.text_total_delivery_order',
                'icon' => ' bg-primary text-white fa fa-truck',
            ],
            'collection_order' => [
                'label' => 'lang:admin::lang.dashboard.text_total_collection_order',
                'icon' => ' bg-info text-white fa fa-shopping-bag',
            ],
            'completed_order' => [
                'label' => 'lang:admin::lang.dashboard.text_total_completed_order',
                'icon' => ' bg-success text-white fa fa-receipt',
            ],
            'reserved_table' => [
                'label' => 'lang:admin::lang.dashboard.text_total_reserved_table',
                'icon' => ' bg-primary text-white fa fa-table',
            ],
            'reserved_guest' => [
                'label' => 'lang:admin::lang.dashboard.text_total_reserved_guest',
                'icon' => ' bg-primary text-white fa fa-table',
            ],
            'reservation' => [
                'label' => 'lang:admin::lang.dashboard.text_total_reservation',
                'icon' => ' bg-success text-white fa fa-table',
            ],
            'completed_reservation' => [
                'label' => 'lang:admin::lang.dashboard.text_total_completed_reservation',
                'icon' => ' bg-success text-white fa fa-table',
            ],
        ];
    }

    public function getContextOptions()
    {
        return array_map(function ($context) {
            return array_get($context, 'label');
        }, $this->listContext());
    }

    public function getContextLabel($context)
    {
        return array_get(array_get($this->listContext(), $context, []), 'label', '--');
    }

    public function getContextColor($context)
    {
        return array_get(array_get($this->listContext(), $context, []), 'color', 'success');
    }

    public function getContextIcon($context)
    {
        return array_get(array_get($this->listContext(), $context, []), 'icon', 'fa fa-bar-chart-o');
    }

    public function loadAssets()
    {
        $this->addCss('css/statistics.css', 'statistics-css');
    }

    protected function prepareVars()
    {
        $this->vars['statsContext'] = $context = $this->property('context');
        $this->vars['statsLabel'] = $this->getContextLabel($context);
        $this->vars['statsIcon'] = $this->getContextIcon($context);
        $this->vars['statsCount'] = $this->callContextCountMethod($context);
    }

    protected function callContextCountMethod($context)
    {
        $count = 0;
        $contextMethod = 'getTotal'.studly_case($context).'Sum';
        if (method_exists($this, $contextMethod))
            $count = $this->$contextMethod($this->property('range'));

        return $count;
    }

    protected function applyRangeQuery($query, $range)
    {
        if ($range === 'week') {
            $start = Carbon::now()->subWeek();
        }
        elseif ($range === 'month') {
            $start = Carbon::now()->subMonth();
        }
        elseif ($range === 'year') {
            $start = Carbon::now()->startOfYear();
        }
        else {
            $start = Carbon::now()->subDay();
        }

        $query->whereBetween('date_added', [
            $start,
            Carbon::now(),
        ]);
    }

    /**
     * Return the total amount of order sales
     *
     * @param $range
     *
     * @return string
     */
    protected function getTotalSaleSum($range)
    {
        $query = Orders_model::query();
        $query->where('status_id', '>', '0')
              ->where('status_id', '!=', setting('canceled_order_status'));

        $this->applyRangeQuery($query, $range);

        return currency_format($query->sum('order_total') ?? 0);
    }

    /**
     * Return the total amount of lost order sales
     *
     * @param $range
     * @return string
     */
    protected function getTotalLostSaleSum($range)
    {
        $query = Orders_model::query();
        $query->where(function ($query) {
            $query->where('status_id', '<=', '0');
            $query->orWhere('status_id', setting('canceled_order_status'));
        });

        $this->applyRangeQuery($query, $range);

        return currency_format($query->sum('order_total') ?? 0);
    }

    /**
     * Return the total amount of cash payment order sales
     *
     * @param $range
     * @return string
     */
    protected function getTotalCashPaymentSum($range)
    {
        $query = Orders_model::query();
        $query->where(function ($query) {
            $query->where('status_id', '>', '0');
            $query->where('status_id', '!=', setting('canceled_order_status'));
        })->where('payment', 'cod');

        $this->applyRangeQuery($query, $range);

        return currency_format($query->sum('order_total') ?? 0);
    }

    /**
     * Return the total number of customers
     *
     * @param $range
     * @return int
     */
    protected function getTotalCustomerSum($range)
    {
        $query = Customers_model::query();
        $this->applyRangeQuery($query, $range);

        return $query->count();
    }

    /**
     * Return the total number of orders placed
     *
     * @param $range
     * @return int
     */
    protected function getTotalOrderSum($range)
    {
        $query = Orders_model::query();
        $this->applyRangeQuery($query, $range);

        return $query->count();
    }

    /**
     * Return the total number of completed orders
     *
     * @param $range
     * @return int
     */
    protected function getTotalCompletedOrderSum($range)
    {
        $query = Orders_model::query();
        $query->whereIn('status_id', setting('completed_order_status') ?? []);

        $this->applyRangeQuery($query, $range);

        return $query->count();
    }

    /**
     * Return the total number of delivery orders
     *
     * @param string $range
     *
     * @return int
     */
    protected function getTotalDeliveryOrderSum($range)
    {
        $query = Orders_model::query();
        $query->where(function ($query) {
            $query->where('order_type', '1');
            $query->orWhere('order_type', 'delivery');
        });

        $this->applyRangeQuery($query, $range);

        return currency_format($query->sum('order_total') ?? 0);
    }

    /**
     * Return the total number of collection orders
     *
     * @param $range
     * @return int
     */
    protected function getTotalCollectionOrderSum($range)
    {
        $query = Orders_model::query();
        $query->where(function ($query) {
            $query->where('order_type', '2');
            $query->orWhere('order_type', 'collection');
        });

        $this->applyRangeQuery($query, $range);

        return currency_format($query->sum('order_total') ?? 0);
    }

    /**
     * Return the total number of reserved tables
     *
     * @param $range
     * @return int
     */
    protected function getTotalReservedTableSum($range)
    {
        $query = Reservations_model::with('tables');
        $query->where('status_id', setting('confirmed_reservation_status'));
        $this->applyRangeQuery($query, $range);
        $result = $query->get();

        $result->pluck('tables')->flatten();

        return $result->count();
    }

    /**
     * Return the total number of reserved table guests
     *
     * @param $range
     * @return int
     */
    protected function getTotalReservedGuestSum($range)
    {
        $query = Reservations_model::query();
        $query->where('status_id', setting('confirmed_reservation_status'));

        $this->applyRangeQuery($query, $range);

        return $query->sum('guest_num') ?? 0;
    }

    /**
     * Return the total number of reservations
     *
     * @param $range
     * @return int
     */
    protected function getTotalReservationSum($range)
    {
        $query = Reservations_model::query();
        $query->where('status_id', '!=', setting('canceled_reservation_status'));

        $this->applyRangeQuery($query, $range);

        return $query->count();
    }

    /**
     * Return the total number of completed reservations
     *
     * @param $range
     * @return int
     */
    protected function getTotalCompletedReservationSum($range)
    {
        $query = Reservations_model::query();
        $query->where('status_id', setting('confirmed_reservation_status'));

        $this->applyRangeQuery($query, $range);

        return $query->count();
    }
}
