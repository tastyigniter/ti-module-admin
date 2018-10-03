<?php

namespace Admin\Traits;

use Admin\Models\Menus_model;
use Admin\Models\Orders_model;
use Admin\Models\Status_history_model;
use Carbon\Carbon;
use DB;
use Event;

trait ManagesOrderItems
{
    public static function bootManagesOrderItems()
    {
        Event::listen('admin.statusHistory.beforeAddStatus', function ($model, $object, $statusId, $previousStatus) {
            if (!$object instanceof Orders_model)
                return;

            $object->processOrderItemsOnAddStatus($statusId);
        });
    }

    protected function processOrderItemsOnAddStatus($statusId)
    {
        if (!in_array($statusId, setting('processing_order_status')))
            return;

        $alreadyExists = Status_history_model::alreadyExists($this, $statusId);
        if ($alreadyExists)
            return FALSE;

        $this->subtractStock();

        $this->redeemCoupon();
    }

    /**
     * Subtract cart item quantity from menu stock quantity
     *
     * @param int $order_id
     *
     * @return bool
     */
    public function subtractStock()
    {
        $this->getOrderMenus()->each(function ($orderMenu) {
            if ($menu = Menus_model::find($orderMenu->menu_id))
                $menu->updateStock($orderMenu->quantity, 'subtract');
        });
    }

    /**
     * Redeem coupon by order_id
     *
     * @return bool TRUE on success, or FALSE on failure
     */
    public function redeemCoupon()
    {
        $couponHistoryModel = $this->coupon_history()->where('status', '!=', '1')->get()->last();

        if ($couponHistoryModel) {
            return $couponHistoryModel->touchStatus();
        }
    }

    /**
     * Return all order menu by order_id
     *
     * @param int $order_id
     *
     * @return array
     */
    public function getOrderMenus()
    {
        return $this->orderMenusQuery()->where('order_id', $this->getKey())->get();
    }

    /**
     * Return all order menu options by order_id
     *
     * @param int $order_id
     *
     * @return array
     */
    public function getOrderMenuOptions()
    {
        return $this->orderMenuOptionsQuery()->where('order_id', $this->getKey())->get()->groupBy('menu_id');
    }

    /**
     * Return all order totals by order_id
     *
     * @param int $order_id
     *
     * @return array
     */
    public function getOrderTotals()
    {
        return $this->orderTotalsQuery()->where('order_id', $this->getKey())->orderBy('priority')->get();
    }

    /**
     * Add cart menu items to order by order_id
     *
     * @param array $cartContent
     *
     * @return bool
     */
    public function addOrderMenus(array $cartContent = [])
    {
        $orderId = $this->getKey();
        if (!is_numeric($orderId))
            return FALSE;

        $this->orderMenusQuery()->where('order_id', $orderId)->delete();
        $this->orderMenuOptionsQuery()->where('order_id', $orderId)->delete();

        foreach ($cartContent as $rowId => $cartItem) {
            if ($rowId != $cartItem['rowId']) continue;

            $orderMenuId = $this->orderMenusQuery()->insertGetId([
                'order_id' => $orderId,
                'menu_id' => $cartItem['id'],
                'name' => $cartItem['name'],
                'quantity' => $cartItem['qty'],
                'price' => $cartItem['price'],
                'subtotal' => $cartItem['subtotal'],
                'comment' => $cartItem['comment'],
                'option_values' => serialize($cartItem['options']),
            ]);

            if ($orderMenuId AND count($cartItem['options'])) {
                $this->addOrderMenuOptions($orderMenuId, $cartItem['id'], $cartItem['options']);
            }
        }
    }

    /**
     * Add cart menu item options to menu and order by,
     * order_id and menu_id
     *
     * @param $orderMenuId
     * @param $menuId
     * @param $options
     *
     * @return bool
     */
    protected function addOrderMenuOptions($orderMenuId, $menuId, $options)
    {
        $orderId = $this->getKey();
        if (!is_numeric($orderId))
            return FALSE;

        foreach ($options as $option) {
            foreach ($option['values'] as $value) {
                $this->orderMenuOptionsQuery()->insert([
                    'order_menu_id' => $orderMenuId,
                    'order_id' => $orderId,
                    'menu_id' => $menuId,
                    'order_menu_option_id' => $option['menu_option_id'],
                    'menu_option_value_id' => $value['menu_option_value_id'],
                    'order_option_name' => $value['name'],
                    'order_option_price' => $value['price'],
                ]);
            }
        }
    }

    /**
     * Add cart totals to order by order_id
     *
     * @param array $totals
     *
     * @return bool
     */
    public function addOrderTotals(array $totals = [])
    {
        $orderId = $this->getKey();
        if (!is_numeric($orderId))
            return FALSE;

        $this->orderTotalsQuery()->where('order_id', $orderId)->delete();

        foreach ($totals as $total) {
            $this->orderTotalsQuery()->insert([
                'order_id' => $orderId,
                'code' => $total['code'],
                'title' => $total['title'],
                'value' => $total['value'],
                'priority' => $total['priority'],
            ]);
        }
    }

    /**
     * Add cart coupon to order by order_id
     *
     * @param \Admin\Models\Coupons_model $coupon
     * @param \Admin\Models\Customers_model $customer
     *
     * @return int|bool
     */
    public function logCouponHistory($coupon, $customer)
    {
        $orderId = $this->getKey();
        if (!is_numeric($orderId))
            return FALSE;

        $this->coupon_history()->delete();

        $couponHistory = $this->coupon_history()->create([
            'customer_id' => $customer ? $customer->getKey() : null,
            'coupon_id' => $coupon->coupon_id,
            'code' => $coupon->code,
            'amount' => '-'.$coupon->amount,
            'min_total' => $coupon->min_total,
            'date_used' => Carbon::now(),
        ]);

        return $couponHistory;
    }

    public function orderMenusQuery()
    {
        return DB::table('order_menus');
    }

    public function orderMenuOptionsQuery()
    {
        return DB::table('order_options');
    }

    public function orderTotalsQuery()
    {
        return DB::table('order_totals');
    }
}