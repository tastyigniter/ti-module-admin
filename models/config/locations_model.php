<?php

$config['list']['filter'] = [
    'search' => [
        'prompt' => 'lang:admin::lang.locations.text_filter_search',
        'mode' => 'all',
    ],
    'scopes' => [
        'status' => [
            'label' => 'lang:admin::lang.text_filter_status',
            'type' => 'switch',
            'conditions' => 'location_status = :filtered',
        ],
    ],
];

$config['list']['toolbar'] = [
    'buttons' => [
        'create' => [
            'label' => 'lang:admin::lang.button_new',
            'class' => 'btn btn-primary',
            'href' => 'locations/create',
        ],
        'delete' => [
            'label' => 'lang:admin::lang.button_delete',
            'class' => 'btn btn-danger',
            'data-attach-loading' => '',
            'data-request' => 'onDelete',
            'data-request-form' => '#list-form',
            'data-request-data' => "_method:'DELETE'",
            'data-request-confirm' => 'lang:admin::lang.alert_warning_confirm',
        ],
    ],
];

$config['list']['columns'] = [
    'edit' => [
        'type' => 'button',
        'iconCssClass' => 'fa fa-pencil',
        'attributes' => [
            'class' => 'btn btn-edit',
            'href' => 'locations/edit/{location_id}',
        ],
    ],
    'default' => [
        'type' => 'button',
        'iconCssClass' => 'fa fa-star-o',
        'attributes' => [
            'class' => 'btn btn-outline-warning bg-transparent',
            'data-request' => 'onSetDefault',
            'data-request-data' => 'default:{location_id}',
        ],
    ],
    'location_name' => [
        'label' => 'lang:admin::lang.label_name',
        'type' => 'text',
        'searchable' => TRUE,
    ],
    'location_city' => [
        'label' => 'lang:admin::lang.locations.column_city',
        'type' => 'text',
        'searchable' => TRUE,
    ],
    'location_state' => [
        'label' => 'lang:admin::lang.locations.column_state',
        'type' => 'text',
        'searchable' => TRUE,
    ],
    'location_postcode' => [
        'label' => 'lang:admin::lang.locations.column_postcode',
        'type' => 'text',
        'searchable' => TRUE,
    ],
    'location_telephone' => [
        'label' => 'lang:admin::lang.locations.column_telephone',
        'type' => 'text',
        'searchable' => TRUE,
    ],
    'location_status' => [
        'label' => 'lang:admin::lang.label_status',
        'type' => 'switch',
    ],
    'location_id' => [
        'label' => 'lang:admin::lang.column_id',
        'invisible' => TRUE,
    ],

];

$config['form']['toolbar'] = [
    'buttons' => [
        'back' => [
            'label' => 'lang:admin::lang.button_icon_back',
            'class' => 'btn btn-default',
            'href' => 'locations',
        ],
        'save' => [
            'label' => 'lang:admin::lang.button_save',
            'context' => ['create', 'edit', 'settings'],
            'partial' => 'form/toolbar_save_button',
            'saveActions' => ['continue', 'close'],
            'class' => 'btn btn-primary',
            'data-request' => 'onSave',
            'data-progress-indicator' => 'admin::lang.text_saving',
        ],
        'delete' => [
            'label' => 'lang:admin::lang.button_icon_delete',
            'class' => 'btn btn-danger',
            'data-request' => 'onDelete',
            'data-request-data' => "_method:'DELETE'",
            'data-request-confirm' => 'lang:admin::lang.alert_warning_confirm',
            'data-progress-indicator' => 'admin::lang.text_deleting',
            'context' => ['edit'],
        ],
    ],
];

$config['form']['tabs'] = [
    'defaultTab' => 'lang:admin::lang.locations.text_tab_general',
    'fields' => [
        'location_name' => [
            'label' => 'lang:admin::lang.label_name',
            'type' => 'text',
            'span' => 'left',
        ],
        'permalink_slug' => [
            'label' => 'lang:admin::lang.locations.label_permalink_slug',
            'type' => 'permalink',
            'span' => 'right',
            'comment' => 'lang:admin::lang.help_permalink',
        ],
        'location_email' => [
            'label' => 'lang:admin::lang.label_email',
            'type' => 'text',
            'span' => 'left',
        ],
        'location_telephone' => [
            'label' => 'lang:admin::lang.locations.label_telephone',
            'type' => 'text',
            'span' => 'right',
        ],
        'thumb' => [
            'label' => 'lang:admin::lang.locations.label_image',
            'type' => 'mediafinder',
            'span' => 'left',
            'mode' => 'inline',
            'useAttachment' => TRUE,
            'comment' => 'lang:admin::lang.locations.help_image',
        ],
        'location_status' => [
            'label' => 'lang:admin::lang.label_status',
            'type' => 'switch',
            'default' => 1,
            'span' => 'right',
        ],
        'location_address_1' => [
            'label' => 'lang:admin::lang.locations.label_address_1',
            'type' => 'text',
            'span' => 'left',
        ],
        'location_address_2' => [
            'label' => 'lang:admin::lang.locations.label_address_2',
            'type' => 'text',
            'span' => 'right',
        ],
        'location_city' => [
            'label' => 'lang:admin::lang.locations.label_city',
            'type' => 'text',
            'span' => 'left',
        ],
        'location_state' => [
            'label' => 'lang:admin::lang.locations.label_state',
            'type' => 'text',
            'span' => 'right',
        ],
        'location_postcode' => [
            'label' => 'lang:admin::lang.locations.label_postcode',
            'type' => 'text',
            'span' => 'left',
        ],
        'location_country_id' => [
            'label' => 'lang:admin::lang.locations.label_country',
            'type' => 'relation',
            'relationFrom' => 'country',
            'nameFrom' => 'country_name',
            'default' => setting('country_id'),
            'span' => 'right',
        ],
        'options[auto_lat_lng]' => [
            'label' => 'lang:admin::lang.locations.label_auto_lat_lng',
            'type' => 'switch',
            'default' => TRUE,
            'onText' => 'lang:admin::lang.text_yes',
            'offText' => 'lang:admin::lang.text_no',
        ],
        'location_lat' => [
            'label' => 'lang:admin::lang.locations.label_latitude',
            'type' => 'text',
            'span' => 'left',
            'trigger' => [
                'action' => 'disable',
                'field' => 'options[auto_lat_lng]',
                'condition' => 'checked',
            ],
        ],
        'location_lng' => [
            'label' => 'lang:admin::lang.locations.label_longitude',
            'type' => 'text',
            'span' => 'right',
            'trigger' => [
                'action' => 'disable',
                'field' => 'options[auto_lat_lng]',
                'condition' => 'checked',
            ],
        ],
        'description' => [
            'label' => 'lang:admin::lang.label_description',
            'type' => 'richeditor',
            'size' => 'small',
        ],

        'options[limit_orders]' => [
            'label' => 'lang:admin::lang.locations.label_limit_orders',
            'tab' => 'lang:admin::lang.locations.text_tab_data',
            'default' => 0,
            'type' => 'switch',
            'comment' => 'lang:admin::lang.locations.help_limit_orders',
            'span' => 'left',
        ],
        'options[limit_orders_count]' => [
            'label' => 'lang:admin::lang.locations.label_limit_orders_count',
            'tab' => 'lang:admin::lang.locations.text_tab_data',
            'default' => 50,
            'type' => 'number',
            'span' => 'right',
            'comment' => 'lang:admin::lang.locations.help_limit_orders_interval',
            'trigger' => [
                'action' => 'enable',
                'field' => 'options[limit_orders]',
                'condition' => 'checked',
            ],
        ],

        'delivery_order' => [
            'label' => 'lang:admin::lang.locations.text_tab_delivery_order',
            'tab' => 'lang:admin::lang.locations.text_tab_data',
            'type' => 'section',
        ],
        'options[offer_delivery]' => [
            'label' => 'lang:admin::lang.locations.label_offer_delivery',
            'tab' => 'lang:admin::lang.locations.text_tab_data',
            'default' => 1,
            'type' => 'switch',
            'span' => 'left',
        ],
        'options[future_orders][enable_delivery]' => [
            'label' => 'lang:admin::lang.locations.label_future_delivery_order',
            'tab' => 'lang:admin::lang.locations.text_tab_data',
            'type' => 'switch',
            'span' => 'right',
            'trigger' => [
                'action' => 'enable',
                'field' => 'options[offer_delivery]',
                'condition' => 'checked',
            ],
        ],
        'options[delivery_time_interval]' => [
            'label' => 'lang:admin::lang.locations.label_delivery_time_interval',
            'tab' => 'lang:admin::lang.locations.text_tab_data',
            'default' => 15,
            'type' => 'number',
            'span' => 'left',
            'comment' => 'lang:admin::lang.locations.help_delivery_time_interval',
            'trigger' => [
                'action' => 'enable',
                'field' => 'options[offer_delivery]',
                'condition' => 'checked',
            ],
        ],
        'options[future_orders][delivery_days]' => [
            'label' => 'lang:admin::lang.locations.label_future_delivery_days',
            'tab' => 'lang:admin::lang.locations.text_tab_data',
            'type' => 'number',
            'default' => 5,
            'span' => 'right',
            'comment' => 'lang:admin::lang.locations.help_future_delivery_days',
            'trigger' => [
                'action' => 'enable',
                'field' => 'options[future_orders][enable_delivery]',
                'condition' => 'checked',
            ],
        ],
        'options[delivery_lead_time]' => [
            'label' => 'lang:admin::lang.locations.label_delivery_lead_time',
            'tab' => 'lang:admin::lang.locations.text_tab_data',
            'default' => 25,
            'type' => 'number',
            'span' => 'left',
            'comment' => 'lang:admin::lang.locations.help_delivery_lead_time',
            'trigger' => [
                'action' => 'enable',
                'field' => 'options[offer_delivery]',
                'condition' => 'checked',
            ],
        ],
        'options[delivery_time_restriction]' => [
            'label' => 'lang:admin::lang.locations.label_delivery_time_restriction',
            'tab' => 'lang:admin::lang.locations.text_tab_data',
            'type' => 'radiotoggle',
            'span' => 'right',
            'comment' => 'lang:admin::lang.locations.help_delivery_time_restriction',
            'options' => [
                'lang:admin::lang.text_none',
                'lang:admin::lang.locations.text_asap_only',
                'lang:admin::lang.locations.text_later_only',
            ],
        ],

        'collection_order' => [
            'label' => 'lang:admin::lang.locations.text_tab_collection_order',
            'tab' => 'lang:admin::lang.locations.text_tab_data',
            'type' => 'section',
        ],
        'options[offer_collection]' => [
            'label' => 'lang:admin::lang.locations.label_offer_collection',
            'tab' => 'lang:admin::lang.locations.text_tab_data',
            'default' => 1,
            'type' => 'switch',
            'span' => 'left',
        ],
        'options[future_orders][enable_collection]' => [
            'label' => 'lang:admin::lang.locations.label_future_collection_order',
            'tab' => 'lang:admin::lang.locations.text_tab_data',
            'type' => 'switch',
            'span' => 'right',
            'trigger' => [
                'action' => 'enable',
                'field' => 'options[offer_collection]',
                'condition' => 'checked',
            ],
        ],
        'options[collection_time_interval]' => [
            'label' => 'lang:admin::lang.locations.label_collection_time_interval',
            'tab' => 'lang:admin::lang.locations.text_tab_data',
            'default' => 15,
            'type' => 'number',
            'span' => 'left',
            'comment' => 'lang:admin::lang.locations.help_collection_time_interval',
            'trigger' => [
                'action' => 'enable',
                'field' => 'options[offer_collection]',
                'condition' => 'checked',
            ],
        ],
        'options[future_orders][collection_days]' => [
            'label' => 'lang:admin::lang.locations.label_future_collection_days',
            'tab' => 'lang:admin::lang.locations.text_tab_data',
            'type' => 'number',
            'default' => 5,
            'span' => 'right',
            'comment' => 'lang:admin::lang.locations.help_future_collection_days',
            'trigger' => [
                'action' => 'enable',
                'field' => 'options[future_orders][enable_collection]',
                'condition' => 'checked',
            ],
        ],
        'options[collection_lead_time]' => [
            'label' => 'lang:admin::lang.locations.label_collection_lead_time',
            'tab' => 'lang:admin::lang.locations.text_tab_data',
            'default' => 25,
            'type' => 'number',
            'span' => 'left',
            'comment' => 'lang:admin::lang.locations.help_collection_lead_time',
            'trigger' => [
                'action' => 'enable',
                'field' => 'options[offer_collection]',
                'condition' => 'checked',
            ],
        ],
        'options[collection_time_restriction]' => [
            'label' => 'lang:admin::lang.locations.label_collection_time_restriction',
            'tab' => 'lang:admin::lang.locations.text_tab_data',
            'type' => 'radiotoggle',
            'span' => 'right',
            'comment' => 'lang:admin::lang.locations.help_collection_time_restriction',
            'options' => [
                'lang:admin::lang.text_none',
                'lang:admin::lang.locations.text_asap_only',
                'lang:admin::lang.locations.text_later_only',
            ],
        ],

        'reservation' => [
            'label' => 'lang:admin::lang.locations.text_tab_reservation',
            'tab' => 'lang:admin::lang.locations.text_tab_data',
            'type' => 'section',
        ],
        'options[offer_reservation]' => [
            'label' => 'lang:admin::lang.locations.label_offer_reservation',
            'tab' => 'lang:admin::lang.locations.text_tab_data',
            'default' => 1,
            'type' => 'switch',
        ],
        'options[reservation_time_interval]' => [
            'label' => 'lang:admin::lang.locations.label_reservation_time_interval',
            'tab' => 'lang:admin::lang.locations.text_tab_data',
            'default' => 15,
            'type' => 'number',
            'span' => 'left',
            'comment' => 'lang:admin::lang.locations.help_reservation_time_interval',
            'trigger' => [
                'action' => 'enable',
                'field' => 'options[offer_reservation]',
                'condition' => 'checked',
            ],
        ],
        'options[reservation_lead_time]' => [
            'label' => 'lang:admin::lang.locations.label_reservation_lead_time',
            'tab' => 'lang:admin::lang.locations.text_tab_data',
            'default' => 45,
            'type' => 'number',
            'span' => 'right',
            'comment' => 'lang:admin::lang.locations.help_reservation_lead_time',
            'trigger' => [
                'action' => 'enable',
                'field' => 'options[offer_reservation]',
                'condition' => 'checked',
            ],
        ],
        'options[reservation_stay_time]' => [
            'label' => 'lang:admin::lang.locations.label_reservation_stay_time',
            'tab' => 'lang:admin::lang.locations.text_tab_data',
            'default' => 45,
            'type' => 'number',
            'span' => 'left',
            'comment' => 'lang:admin::lang.locations.help_reservation_stay_time',
            'trigger' => [
                'action' => 'enable',
                'field' => 'options[offer_reservation]',
                'condition' => 'checked',
            ],
        ],
        'options[auto_allocate_table]' => [
            'label' => 'lang:admin::lang.locations.label_auto_allocate_table',
            'tab' => 'lang:admin::lang.locations.text_tab_data',
            'default' => 1,
            'type' => 'switch',
            'span' => 'right',
            'trigger' => [
                'action' => 'enable',
                'field' => 'options[offer_reservation]',
                'condition' => 'checked',
            ],
        ],

        'options[working_hours]' => [
            'tab' => 'lang:admin::lang.locations.text_tab_schedules',
            'type' => 'scheduleeditor',
            'context' => ['edit'],
            'form' => 'working_hours_model',
        ],

        'options[payments]' => [
            'label' => 'lang:admin::lang.locations.label_payments',
            'tab' => 'lang:admin::lang.locations.label_payments',
            'type' => 'checkboxlist',
            'options' => ['Admin\Models\Payments_model', 'listDropdownOptions'],
            'commentAbove' => 'lang:admin::lang.locations.help_payments',
            'placeholder' => 'lang:admin::lang.locations.help_no_payments',
        ],

        'delivery_areas' => [
            'tab' => 'lang:admin::lang.locations.text_tab_delivery',
            'label' => 'lang:admin::lang.locations.text_delivery_area',
            'type' => 'maparea',
            'context' => ['edit'],
            'form' => 'location_areas_model',
            'commentAbove' => 'lang:admin::lang.locations.help_delivery_areas',
        ],

        'options[gallery][title]' => [
            'label' => 'lang:admin::lang.locations.label_gallery_title',
            'tab' => 'lang:admin::lang.locations.text_tab_gallery',
            'type' => 'text',
        ],
        'options[gallery][description]' => [
            'label' => 'lang:admin::lang.label_description',
            'tab' => 'lang:admin::lang.locations.text_tab_gallery',
            'type' => 'textarea',
        ],
        'gallery' => [
            'label' => 'lang:admin::lang.locations.label_gallery_add_image',
            'tab' => 'lang:admin::lang.locations.text_tab_gallery',
            'type' => 'mediafinder',
            'isMulti' => TRUE,
            'useAttachment' => TRUE,
        ],
    ],
];

return $config;
