<?php
$config['list']['filter'] = [
    'search' => [
        'prompt' => 'lang:admin::coupons.text_filter_search',
        'mode'   => 'all',
    ],
    'scopes' => [
        'type'   => [
            'label'      => 'lang:admin::coupons.text_filter_type',
            'type'       => 'select',
            'conditions' => 'type = :filtered',
            'options'    => [
                'F' => 'lang:admin::coupons.text_fixed_amount',
                'P' => 'lang:admin::coupons.text_percentage',
            ],
        ],
        'status' => [
            'label'      => 'lang:admin::coupons.text_filter_status',
            'type'       => 'switch',
            'conditions' => 'status = :filtered',
        ],
    ],
];

$config['list']['toolbar'] = [
    'buttons' => [
        'create' => ['label' => 'lang:admin::default.button_new', 'class' => 'btn btn-primary', 'href' => 'coupons/create'],
        'delete' => [
            'label'                => 'lang:admin::default.button_delete', 'class' => 'btn btn-danger',
            'data-request-form'    => '#list-form', 'data-request' => 'onDelete', 'data-request-data' => "_method:'DELETE'",
            'data-request-confirm' => 'lang:admin::default.alert_warning_confirm',
        ],
        'filter' => [
            'label'       => 'lang:admin::default.button_icon_filter',
            'class'       => 'btn btn-default btn-filter',
            'data-toggle' => 'list-filter',
            'data-target' => '.list-filter',
        ],
    ],
];

$config['list']['columns'] = [
    'edit'               => [
        'type'         => 'button',
        'iconCssClass' => 'fa fa-pencil',
        'attributes'   => [
            'class' => 'btn btn-edit',
            'href'  => 'coupons/edit/{coupon_id}',
        ],
    ],
    'name'               => [
        'label'      => 'lang:admin::coupons.column_name',
        'type'       => 'text',
        'searchable' => TRUE,
    ],
    'code'               => [
        'label'      => 'lang:admin::coupons.column_code',
        'type'       => 'text',
        'searchable' => TRUE,
    ],
    'type_name'          => [
        'label'    => 'lang:admin::coupons.column_type',
        'type'     => 'text',
        'sortable' => FALSE,
    ],
    'formatted_discount' => [
        'label'    => 'lang:admin::coupons.column_discount',
        'type'     => 'text',
        'sortable' => FALSE,
    ],
    'validity'           => [
        'label'      => 'lang:admin::coupons.column_validity',
        'type'       => 'text',
        'searchable' => TRUE,
        'formatter'  => function ($record, $column, $value) {
            return $value ? ucwords($value) : null;
        },
    ],
    'status'             => [
        'label' => 'lang:admin::coupons.column_status',
        'type'  => 'switch',
    ],
    'coupon_id'          => [
        'label'     => 'lang:admin::default.column_id',
        'invisible' => TRUE,
    ],

];

$config['form']['toolbar'] = [
    'buttons' => [
        'save'      => ['label' => 'lang:admin::default.button_save', 'class' => 'btn btn-primary', 'data-request-form' => '#edit-form', 'data-request' => 'onSave'],
        'saveClose' => [
            'label'             => 'lang:admin::default.button_save_close',
            'class'             => 'btn btn-default',
            'data-request'      => 'onSave',
            'data-request-form' => '#edit-form',
            'data-request-data' => 'close:1',
        ],
        'delete'    => [
            'label'                => 'lang:admin::default.button_icon_delete', 'class' => 'btn btn-danger',
            'data-request-form'    => '#edit-form', 'data-request' => 'onDelete', 'data-request-data' => "_method:'DELETE'",
            'data-request-confirm' => 'lang:admin::default.alert_warning_confirm', 'context' => ['edit'],
        ],
        'back'      => ['label' => 'lang:admin::default.button_icon_back', 'class' => 'btn btn-default', 'href' => 'coupons'],
    ],
];

$config['form']['tabs'] = [
    'defaultTab' => 'lang:admin::coupons.text_tab_general',
    'fields'     => [
        'name'                 => [
            'label' => 'lang:admin::coupons.label_name',
            'type'  => 'text',
            'span'  => 'left',
        ],
        'code'                 => [
            'label' => 'lang:admin::coupons.label_code',
            'type'  => 'text',
            'span'  => 'right',
        ],
        'description'          => [
            'label' => 'lang:admin::coupons.label_description',
            'type'  => 'textarea',
        ],
        'type'                 => [
            'label'   => 'lang:admin::coupons.label_type',
            'type'    => 'radio',
            'span'    => 'left',
            'default' => 'F',
            'options' => [
                'F' => 'lang:admin::coupons.text_fixed_amount',
                'P' => 'lang:admin::coupons.text_percentage',
            ],
        ],
        'discount'             => [
            'label' => 'lang:admin::coupons.label_discount',
            'type'  => 'number',
            'span'  => 'right',
        ],
        'redemptions'          => [
            'label'   => 'lang:admin::coupons.label_redemption',
            'type'    => 'number',
            'span'    => 'left',
            'default' => 0,
            'comment' => 'lang:admin::coupons.help_redemption',
        ],
        'customer_redemptions' => [
            'label'   => 'lang:admin::coupons.label_customer_redemption',
            'type'    => 'number',
            'span'    => 'right',
            'default' => 0,
            'comment' => 'lang:admin::coupons.help_customer_redemption',
        ],
        'min_total'            => [
            'label' => 'lang:admin::coupons.label_min_total',
            'type'  => 'number',
            'span'  => 'left',
        ],
        'order_restriction'    => [
            'label'   => 'lang:admin::coupons.label_order_restriction',
            'type'    => 'radio',
            'comment' => 'lang:admin::coupons.help_order_restriction',
            'options' => [
                'lang:admin::default.text_none',
                'lang:admin::coupons.text_delivery_only',
                'lang:admin::coupons.text_collection_only',
            ],
        ],
        'validity'             => [
            'label'   => 'lang:admin::coupons.label_validity',
            'type'    => 'radio',
            'default' => 'forever',
            'options' => [
                'forever'   => 'lang:admin::coupons.text_forever',
                'fixed'     => 'lang:admin::coupons.text_fixed',
                'period'    => 'lang:admin::coupons.text_period',
                'recurring' => 'lang:admin::coupons.text_recurring',
            ],
        ],
        'fixed_date'           => [
            'label'   => 'lang:admin::coupons.label_fixed_date',
            'type'    => 'datepicker',
            'mode'    => 'date',
            'trigger' => [
                'action'    => 'show',
                'field'     => 'validity',
                'condition' => 'value[fixed]',
            ],
        ],
        'fixed_from_time'      => [
            'label'    => 'lang:admin::coupons.label_fixed_from_time',
            'type'     => 'datepicker',
            'mode'     => 'time',
            'span'     => 'left',
            'cssClass' => 'flex-width',
            'trigger'  => [
                'action'    => 'show',
                'field'     => 'validity',
                'condition' => 'value[fixed]',
            ],
        ],
        'fixed_to_time'        => [
            'label'    => 'lang:admin::coupons.label_fixed_to_time',
            'type'     => 'datepicker',
            'mode'     => 'time',
            'span'     => 'left',
            'cssClass' => 'flex-width',
            'trigger'  => [
                'action'    => 'show',
                'field'     => 'validity',
                'condition' => 'value[fixed]',
            ],
        ],
        'period_start_date'    => [
            'label'    => 'lang:admin::coupons.label_period_start_date',
            'type'     => 'datepicker',
            'mode'     => 'date',
            'span'     => 'left',
            'cssClass' => 'flex-width',
            'trigger'  => [
                'action'    => 'show',
                'field'     => 'validity',
                'condition' => 'value[period]',
            ],
        ],
        'period_end_date'      => [
            'label'    => 'lang:admin::coupons.label_period_end_date',
            'type'     => 'datepicker',
            'mode'     => 'date',
            'span'     => 'left',
            'cssClass' => 'flex-width',
            'trigger'  => [
                'action'    => 'show',
                'field'     => 'validity',
                'condition' => 'value[period]',
            ],
        ],
        'recurring_every'      => [
            'label'   => 'lang:admin::coupons.label_recurring_every',
            'type'    => 'checkbox',
            'trigger' => [
                'action'    => 'show',
                'field'     => 'validity',
                'condition' => 'value[recurring]',
            ],
        ],
        'recurring_from_time'  => [
            'label'    => 'lang:admin::coupons.label_recurring_from_time',
            'type'     => 'datepicker',
            'mode'     => 'time',
            'span'     => 'left',
            'cssClass' => 'flex-width',
            'trigger'  => [
                'action'    => 'show',
                'field'     => 'validity',
                'condition' => 'value[recurring]',
            ],
        ],
        'recurring_to_time'    => [
            'label'    => 'lang:admin::coupons.label_recurring_to_time',
            'type'     => 'datepicker',
            'mode'     => 'time',
            'span'     => 'left',
            'cssClass' => 'flex-width',
            'trigger'  => [
                'action'    => 'show',
                'field'     => 'validity',
                'condition' => 'value[recurring]',
            ],
        ],
        'status'               => [
            'label'   => 'lang:admin::default.label_status',
            'type'    => 'switch',
            'default' => 1,
        ],
        'history'              => [
            'tab'     => 'lang:admin::coupons.text_tab_history',
            'type'    => 'datatable',
            'columns' => [
                'order_id'      => [
                    'title' => 'lang:admin::coupons.column_order_id',
                ],
                'customer_name' => [
                    'title' => 'lang:admin::coupons.column_customer',
                ],
                'min_total'     => [
                    'title' => 'lang:admin::coupons.column_min_total',
                ],
                'amount'        => [
                    'title' => 'lang:admin::coupons.column_amount',
                ],
                'date_used'     => [
                    'title' => 'lang:admin::coupons.column_date_used',
                ],
            ],
        ],
    ],
];

return $config;