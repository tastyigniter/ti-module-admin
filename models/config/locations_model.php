<?php

$config['list']['filter'] = [
    'search' => [
        'prompt' => 'lang:admin::locations.text_filter_search',
        'mode'   => 'all',
    ],
    'scopes' => [
        'status' => [
            'label'      => 'lang:admin::locations.text_filter_status',
            'type'       => 'switch',
            'conditions' => 'location_status = :filtered',
        ],
    ],
];

$config['list']['toolbar'] = [
    'buttons' => [
        'create' => ['label' => 'lang:admin::default.button_new', 'class' => 'btn btn-primary', 'href' => 'locations/create'],
        'delete' => ['label' => 'lang:admin::default.button_delete', 'class' => 'btn btn-danger', 'data-request-form' => '#list-form', 'data-request' => 'onDelete', 'data-request-data' => "_method:'DELETE'", 'data-request-confirm' => 'lang:admin::default.alert_warning_confirm'],
        'filter' => ['label' => 'lang:admin::default.button_icon_filter', 'class' => 'btn btn-default btn-filter', 'data-toggle' => 'list-filter', 'data-target' => '.list-filter'],
    ],
];

$config['list']['columns'] = [
    'edit'               => [
        'type'         => 'button',
        'iconCssClass' => 'fa fa-pencil',
        'attributes'   => [
            'class' => 'btn btn-edit',
            'href'  => 'locations/edit/{location_id}',
        ],
    ],
    'default'            => [
        'type'         => 'button',
        'iconCssClass' => 'fa fa-star-o',
        'attributes'   => [
            'class'             => 'btn btn-outline-warning',
            'data-request'      => 'onSetDefault',
            'data-request-data' => 'default:{location_id}',
        ],
    ],
    'location_name'      => [
        'label'      => 'lang:admin::locations.column_name',
        'type'       => "text",
        'searchable' => TRUE,
    ],
    'location_city'      => [
        'label'      => 'lang:admin::locations.column_city',
        'type'       => "text",
        'searchable' => TRUE,
    ],
    'location_state'     => [
        'label'     => 'lang:admin::locations.column_state',
        'type'      => 'text',
        'invisible' => TRUE,
    ],
    'location_postcode'  => [
        'label'      => 'lang:admin::locations.column_postcode',
        'type'       => 'text',
        'searchable' => TRUE,
    ],
    'location_telephone' => [
        'label' => 'lang:admin::locations.column_telephone',
        'type'  => 'text',
    ],
    'location_status'    => [
        'label' => 'lang:admin::locations.column_status',
        'type'  => 'switch',
    ],
    'location_id'        => [
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
        'back'      => ['label' => 'lang:admin::default.button_icon_back', 'class' => 'btn btn-default', 'href' => 'locations'],
    ],
];

$config['form']['tabs'] = [
    'defaultTab' => 'lang:admin::locations.text_tab_general',
    'fields'     => [
        'location_name'      => [
            'label' => 'lang:admin::locations.label_name',
            'type'  => 'text',
            'span'  => 'left',
        ],
        'location_email'     => [
            'label' => 'lang:admin::locations.label_email',
            'type'  => 'text',
            'span'  => 'right',
        ],
        'location_telephone' => [
            'label' => 'lang:admin::locations.label_telephone',
            'type'  => 'text',
            'span'  => 'left',
        ],
        'location_status'    => [
            'label'   => 'lang:admin::default.label_status',
            'type'    => 'switch',
            'default' => 1,
            'span'    => 'right',
        ],

        'address'               => [
            'label' => 'lang:admin::locations.text_tab_title_address',
            'type'  => 'section',
        ],
        'location_address_1'    => [
            'label' => 'lang:admin::locations.label_address_1',
            'type'  => 'text',
            'span'  => 'left',
        ],
        'location_address_2'    => [
            'label' => 'lang:admin::locations.label_address_2',
            'type'  => 'text',
            'span'  => 'right',
        ],
        'location_city'         => [
            'label' => 'lang:admin::locations.label_city',
            'type'  => 'text',
            'span'  => 'left',
        ],
        'location_state'        => [
            'label' => 'lang:admin::locations.label_state',
            'type'  => 'text',
            'span'  => 'right',
        ],
        'location_postcode'     => [
            'label' => 'lang:admin::locations.label_postcode',
            'type'  => 'text',
            'span'  => 'left',
        ],
        'location_country_id'   => [
            'label'        => 'lang:admin::locations.label_country',
            'type'         => 'relation',
            'relationFrom' => 'country',
            'nameFrom'     => 'country_name',
            'default'      => setting('country_id'),
            'span'         => 'right',
        ],
        'options[auto_lat_lng]' => [
            'label'   => 'lang:admin::locations.label_auto_lat_lng',
            'type'    => 'radio',
            'default' => 1,
            'options' => [
                1 => 'lang:admin::default.text_yes',
                0 => 'lang:admin::default.text_no',
            ],
        ],
        'location_lat'          => [
            'label'   => 'lang:admin::locations.label_latitude',
            'type'    => 'text',
            'span'    => 'left',
            'trigger' => [
                'action'    => 'show',
                'field'     => 'options[auto_lat_lng]',
                'condition' => 'value[0]',
            ],
        ],
        'location_lng'          => [
            'label'   => 'lang:admin::locations.label_longitude',
            'type'    => 'text',
            'span'    => 'right',
            'trigger' => [
                'action'    => 'show',
                'field'     => 'options[auto_lat_lng]',
                'condition' => 'value[0]',
            ],
        ],

        'permalink_slug' => [
            'label'   => 'lang:admin::locations.label_permalink_slug',
            'tab'     => 'lang:admin::locations.text_tab_data',
            'type'    => 'permalink',
            'comment' => 'lang:admin::locations.help_permalink',
        ],
        'location_image' => [
            'label'   => 'lang:admin::locations.label_image',
            'tab'     => 'lang:admin::locations.text_tab_data',
            'type'    => 'mediafinder',
            'comment' => 'lang:admin::locations.help_image',
        ],
        'description'    => [
            'label' => 'lang:admin::locations.label_description',
            'tab'   => 'lang:admin::locations.text_tab_data',
            'type'  => 'richeditor',
            'size'  => 'small',
        ],

        'opening_type'                      => [
            'label' => 'lang:admin::locations.label_opening_type',
            'tab'   => 'lang:admin::locations.text_tab_opening_hours',
            'type'  => 'section',
        ],
        'options[hours][opening][type]'     => [
            'label'   => 'lang:admin::locations.label_opening_hour',
            'tab'     => 'lang:admin::locations.text_tab_opening_hours',
            'type'    => 'radio',
            'default' => 'daily',
            'options' => [
                '24_7'     => 'lang:admin::locations.text_24_7',
                'daily'    => 'lang:admin::locations.text_daily',
                'flexible' => 'lang:admin::locations.text_flexible',
            ],
        ],
        'options[hours][opening][days]'     => [
            'label'   => 'lang:admin::locations.label_opening_days',
            'tab'     => 'lang:admin::locations.text_tab_opening_hours',
            'type'    => 'checkbox',
            'options' => 'getWeekDaysOptions',
            'default' => [0, 1, 2, 3, 4, 5, 6],
            'trigger' => [
                'action'    => 'show',
                'field'     => 'options[hours][opening][type]',
                'condition' => 'value[daily]',
            ],
        ],
        'options[hours][opening][open]'     => [
            'label'    => 'lang:admin::locations.label_open_hour',
            'tab'      => 'lang:admin::locations.text_tab_opening_hours',
            'type'     => 'datepicker',
            'default'  => '12:00 AM',
            'mode'     => 'time',
            'span'     => 'left',
            'cssClass' => 'flex-width',
            'trigger'  => [
                'action'    => 'show',
                'field'     => 'options[hours][opening][type]',
                'condition' => 'value[daily]',
            ],
        ],
        'options[hours][opening][close]'    => [
            'label'    => 'lang:admin::locations.label_close_hour',
            'tab'      => 'lang:admin::locations.text_tab_opening_hours',
            'type'     => 'datepicker',
            'default'  => '11:59 PM',
            'mode'     => 'time',
            'span'     => 'left',
            'cssClass' => 'flex-width',
            'trigger'  => [
                'action'    => 'show',
                'field'     => 'options[hours][opening][type]',
                'condition' => 'value[daily]',
            ],
        ],
        'options[hours][opening][flexible]' => [
            'label'   => 'lang:admin::locations.text_flexible',
            'tab'     => 'lang:admin::locations.text_tab_opening_hours',
            'type'    => 'partial',
            'path'    => 'locations/flexible_hours',
            'trigger' => [
                'action'    => 'show',
                'field'     => 'options[hours][opening][type]',
                'condition' => 'value[flexible]',
            ],
        ],

        'delivery_type'                      => [
            'label' => 'lang:admin::locations.label_delivery_type',
            'tab'   => 'lang:admin::locations.text_tab_opening_hours',
            'type'  => 'section',
        ],
        'options[hours][delivery][type]'     => [
            'label'   => 'lang:admin::locations.label_opening_hour',
            'tab'     => 'lang:admin::locations.text_tab_opening_hours',
            'type'    => 'radio',
            'default' => '24_7',
            'options' => [
                '24_7'     => 'lang:admin::locations.text_24_7',
                'daily'    => 'lang:admin::locations.text_daily',
                'flexible' => 'lang:admin::locations.text_flexible',
            ],
        ],
        'options[hours][delivery][days]'     => [
            'label'   => 'lang:admin::locations.label_opening_days',
            'tab'     => 'lang:admin::locations.text_tab_opening_hours',
            'type'    => 'checkbox',
            'options' => 'getWeekDaysOptions',
            'default' => [0, 1, 2, 3, 4, 5, 6],
            'trigger' => [
                'action'    => 'show',
                'field'     => 'options[hours][delivery][type]',
                'condition' => 'value[daily]',
            ],
        ],
        'options[hours][delivery][open]'     => [
            'label'    => 'lang:admin::locations.label_open_hour',
            'tab'      => 'lang:admin::locations.text_tab_opening_hours',
            'type'     => 'datepicker',
            'default'  => '12:00 AM',
            'mode'     => 'time',
            'span'     => 'left',
            'cssClass' => 'flex-width',
            'trigger'  => [
                'action'    => 'show',
                'field'     => 'options[hours][delivery][type]',
                'condition' => 'value[daily]',
            ],
        ],
        'options[hours][delivery][close]'    => [
            'label'    => 'lang:admin::locations.label_close_hour',
            'tab'      => 'lang:admin::locations.text_tab_opening_hours',
            'type'     => 'datepicker',
            'default'  => '11:59 PM',
            'mode'     => 'time',
            'span'     => 'left',
            'cssClass' => 'flex-width',
            'trigger'  => [
                'action'    => 'show',
                'field'     => 'options[hours][delivery][type]',
                'condition' => 'value[daily]',
            ],
        ],
        'options[hours][delivery][flexible]' => [
            'label'   => 'lang:admin::locations.text_flexible',
            'tab'     => 'lang:admin::locations.text_tab_opening_hours',
            'type'    => 'partial',
            'path'    => 'locations/flexible_hours',
            'trigger' => [
                'action'    => 'show',
                'field'     => 'options[hours][delivery][type]',
                'condition' => 'value[flexible]',
            ],
        ],

        'collection_type'                      => [
            'label' => 'lang:admin::locations.label_collection_type',
            'tab'   => 'lang:admin::locations.text_tab_opening_hours',
            'type'  => 'section',
        ],
        'options[hours][collection][type]'     => [
            'label'   => 'lang:admin::locations.label_opening_hour',
            'tab'     => 'lang:admin::locations.text_tab_opening_hours',
            'type'    => 'radio',
            'default' => '24_7',
            'options' => [
                '24_7'     => 'lang:admin::locations.text_24_7',
                'daily'    => 'lang:admin::locations.text_daily',
                'flexible' => 'lang:admin::locations.text_flexible',
            ],
        ],
        'options[hours][collection][days]'     => [
            'label'   => 'lang:admin::locations.label_opening_days',
            'tab'     => 'lang:admin::locations.text_tab_opening_hours',
            'type'    => 'checkbox',
            'options' => 'getWeekDaysOptions',
            'default' => [0, 1, 2, 3, 4, 5, 6],
            'trigger' => [
                'action'    => 'show',
                'field'     => 'options[hours][collection][type]',
                'condition' => 'value[daily]',
            ],
        ],
        'options[hours][collection][open]'     => [
            'label'    => 'lang:admin::locations.label_open_hour',
            'tab'      => 'lang:admin::locations.text_tab_opening_hours',
            'type'     => 'datepicker',
            'default'  => '12:00 AM',
            'mode'     => 'time',
            'span'     => 'left',
            'cssClass' => 'flex-width',
            'trigger'  => [
                'action'    => 'show',
                'field'     => 'options[hours][collection][type]',
                'condition' => 'value[daily]',
            ],
        ],
        'options[hours][collection][close]'    => [
            'label'    => 'lang:admin::locations.label_close_hour',
            'tab'      => 'lang:admin::locations.text_tab_opening_hours',
            'type'     => 'datepicker',
            'default'  => '11:59 PM',
            'mode'     => 'time',
            'span'     => 'left',
            'cssClass' => 'flex-width',
            'trigger'  => [
                'action'    => 'show',
                'field'     => 'options[hours][collection][type]',
                'condition' => 'value[daily]',
            ],
        ],
        'options[hours][collection][flexible]' => [
            'label'   => 'lang:admin::locations.text_flexible',
            'tab'     => 'lang:admin::locations.text_tab_opening_hours',
            'type'    => 'partial',
            'path'    => 'locations/flexible_hours',
            'trigger' => [
                'action'    => 'show',
                'field'     => 'options[hours][collection][type]',
                'condition' => 'value[flexible]',
            ],
        ],

        'offer_delivery'                         => [
            'label'   => 'lang:admin::locations.label_offer_delivery',
            'tab'     => 'lang:admin::locations.text_tab_order',
            'span'    => 'left',
            'default' => 1,
            'type'    => 'switch',
        ],
        'offer_collection'                       => [
            'label'   => 'lang:admin::locations.label_offer_collection',
            'tab'     => 'lang:admin::locations.text_tab_order',
            'span'    => 'right',
            'default' => 1,
            'type'    => 'switch',
        ],
        'delivery_time'                          => [
            'label'   => 'lang:admin::locations.label_delivery_time',
            'tab'     => 'lang:admin::locations.text_tab_order',
            'default' => 0,
            'type'    => 'number',
            'comment' => 'lang:admin::locations.help_delivery_time',
        ],
        'collection_time'                        => [
            'label'   => 'lang:admin::locations.label_collection_time',
            'tab'     => 'lang:admin::locations.text_tab_order',
            'default' => 0,
            'type'    => 'number',
            'comment' => 'lang:admin::locations.help_collection_time',
        ],
        'last_order_time'                        => [
            'label'   => 'lang:admin::locations.label_last_order_time',
            'tab'     => 'lang:admin::locations.text_tab_order',
            'default' => 0,
            'type'    => 'number',
            'comment' => 'lang:admin::locations.help_last_order_time',
        ],
        'options[future_orders]'                 => [
            'label'   => 'lang:admin::locations.label_future_order',
            'tab'     => 'lang:admin::locations.text_tab_order',
            'type'    => 'switch',
            'comment' => 'lang:admin::locations.help_future_order',
        ],
        'options[future_order_days][delivery]'   => [
            'label'   => 'lang:admin::locations.label_future_delivery_days',
            'tab'     => 'lang:admin::locations.text_tab_order',
            'type'    => 'text',
            'default' => 5,
            'span'    => 'left',
            'comment' => 'lang:admin::locations.help_future_delivery_days',
            'trigger' => [
                'action'    => 'show',
                'field'     => 'options[future_orders]',
                'condition' => 'checked',
            ],
        ],
        'options[future_order_days][collection]' => [
            'label'   => 'lang:admin::locations.label_future_collection_days',
            'tab'     => 'lang:admin::locations.text_tab_order',
            'type'    => 'text',
            'default' => 5,
            'span'    => 'right',
            'comment' => 'lang:admin::locations.help_future_collection_days',
            'trigger' => [
                'action'    => 'show',
                'field'     => 'options[future_orders]',
                'condition' => 'checked',
            ],
        ],

        'options[offer_reservation]' => [
            'label'   => 'lang:admin::locations.label_offer_reservation',
            'tab'     => 'lang:admin::locations.text_tab_reservation',
            'default' => 1,
            'type'    => 'switch',
        ],
        'reservation_time_interval'  => [
            'label'   => 'lang:admin::locations.label_reservation_time_interval',
            'tab'     => 'lang:admin::locations.text_tab_reservation',
            'default' => 0,
            'type'    => 'number',
            'comment' => 'lang:admin::locations.help_reservation_time_interval',
        ],
        'reservation_stay_time'      => [
            'label'   => 'lang:admin::locations.label_reservation_stay_time',
            'tab'     => 'lang:admin::locations.text_tab_reservation',
            'default' => 0,
            'type'    => 'number',
            'comment' => 'lang:admin::locations.help_reservation_stay_time',
        ],
        'tables'                     => [
            'label'        => 'lang:admin::locations.label_tables',
            'tab'          => 'lang:admin::locations.text_tab_reservation',
            'type'         => 'relation',
            'relationFrom' => 'tables',
            'nameFrom'     => 'table_name',
        ],

        'options[payments]' => [
            'label'        => 'lang:admin::locations.label_payments',
            'tab'          => 'lang:admin::locations.label_payments',
            'type'         => 'checkboxlist',
            'options'      => ['Admin\Models\Payments_model', 'listDropdownOptions'],
            'commentAbove' => 'lang:admin::locations.help_payments',
            'placeholder'  => 'lang:admin::locations.help_no_payments',
        ],

        'delivery_areas' => [
            'tab'          => 'lang:admin::locations.text_tab_delivery',
            'type'         => 'maparea',
            'context'      => ['edit'],
            'relationFrom' => 'delivery_areas',
            'latFrom'      => 'location_lat',
            'lngFrom'      => 'location_lng',
            'zoom'         => '14',
        ],

        'options[gallery][title]'       => [
            'label' => 'lang:admin::locations.label_gallery_title',
            'tab'   => 'lang:admin::locations.text_tab_gallery',
            'type'  => 'text',
        ],
        'options[gallery][description]' => [
            'label' => 'lang:admin::locations.label_gallery_description',
            'tab'   => 'lang:admin::locations.text_tab_gallery',
            'type'  => 'textarea',
        ],
        'options[gallery][images]'      => [
            'label'   => 'lang:admin::locations.label_gallery_add_image',
            'tab'     => 'lang:admin::locations.text_tab_gallery',
            'type'    => 'mediafinder',
            'isMulti' => TRUE,
        ],
    ],
];

return $config;