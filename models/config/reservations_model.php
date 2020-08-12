<?php
$config['list']['filter'] = [
    'search' => [
        'prompt' => 'lang:admin::lang.reservations.text_filter_search',
        'mode' => 'all',
    ],
    'scopes' => [
        'assignee' => [
            'label' => 'lang:admin::lang.reservations.text_filter_assignee',
            'type' => 'select',
            'scope' => 'filterAssignedTo',
            'options' => [
                1 => 'lang:admin::lang.statuses.text_unassigned',
                2 => 'lang:admin::lang.statuses.text_assigned_to_self',
                3 => 'lang:admin::lang.statuses.text_assigned_to_others',
            ],
        ],
        'location' => [
            'label' => 'lang:admin::lang.text_filter_location',
            'type' => 'select',
            'conditions' => 'location_id = :filtered',
            'modelClass' => 'Admin\Models\Locations_model',
            'nameFrom' => 'location_name',
            'locationAware' => 'hide',
        ],
        'status' => [
            'label' => 'lang:admin::lang.text_filter_status',
            'type' => 'select',
            'conditions' => 'status_id = :filtered',
            'modelClass' => 'Admin\Models\Statuses_model',
            'options' => 'getDropdownOptionsForReservation',
        ],
        'date' => [
            'label' => 'lang:admin::lang.text_filter_date',
            'type' => 'daterange',
            'conditions' => 'reserve_date >= CAST(:filtered_start AS DATE) AND reserve_date <= CAST(:filtered_end AS DATE)',
        ],
    ],
];

$config['list']['toolbar'] = [
    'buttons' => [
        'create' => [
            'label' => 'lang:admin::lang.button_new',
            'class' => 'btn btn-primary',
            'href' => 'reservations/create',
        ],
        'delete' => [
            'label' => 'lang:admin::lang.button_delete',
            'class' => 'btn btn-danger',
            'context' => 'index',
            'data-attach-loading' => '',
            'data-request' => 'onDelete',
            'data-request-form' => '#list-form',
            'data-request-data' => "_method:'DELETE'",
            'data-request-confirm' => 'lang:admin::lang.alert_warning_confirm',
        ],
        'calendar' => [
            'label' => 'lang:admin::lang.reservations.text_switch_to_calendar',
            'class' => 'btn btn-default',
            'href' => 'reservations/calendar',
            'context' => 'index',
        ],
    ],
];

$config['list']['columns'] = [
    'edit' => [
        'type' => 'button',
        'iconCssClass' => 'fa fa-pencil',
        'attributes' => [
            'class' => 'btn btn-edit',
            'href' => 'reservations/edit/{reservation_id}',
        ],
    ],
    'reservation_id' => [
        'label' => 'lang:admin::lang.column_id',
    ],
    'location' => [
        'label' => 'lang:admin::lang.reservations.column_location',
        'relation' => 'location',
        'select' => 'location_name',
        'searchable' => TRUE,
        'locationAware' => 'hide',
    ],
    'full_name' => [
        'label' => 'lang:admin::lang.label_name',
        'select' => "concat(first_name, ' ', last_name)",
        'searchable' => TRUE,
    ],
    'guest_num' => [
        'label' => 'lang:admin::lang.reservations.column_guest',
        'type' => 'number',
        'searchable' => TRUE,
    ],
    'table_name' => [
        'label' => 'lang:admin::lang.reservations.column_table',
        'type' => 'text',
        'relation' => 'tables',
        'select' => 'table_name',
        'searchable' => TRUE,
    ],
    'status_name' => [
        'label' => 'lang:admin::lang.label_status',
        'relation' => 'status',
        'select' => 'status_name',
        'type' => 'partial',
        'path' => 'reservations/status_column',
        'searchable' => TRUE,
    ],
    'assignee_name' => [
        'label' => 'lang:admin::lang.reservations.column_staff',
        'type' => 'text',
        'relation' => 'assignee',
        'select' => 'staff_name',
    ],
    'reserve_time' => [
        'label' => 'lang:admin::lang.reservations.column_time',
        'type' => 'time',
    ],
    'reserve_date' => [
        'label' => 'lang:admin::lang.reservations.column_date',
        'type' => 'date',
    ],
];

$config['calendar']['toolbar'] = [
    'buttons' => [
        'create' => [
            'label' => 'lang:admin::lang.button_new',
            'class' => 'btn btn-primary',
            'href' => 'reservations/create',
        ],
        'list' => [
            'label' => 'lang:admin::lang.text_switch_to_list',
            'class' => 'btn btn-default',
            'href' => 'reservations',
            'context' => 'calendar',
        ],
    ],
];

$config['form']['toolbar'] = [
    'buttons' => [
        'save' => [
            'label' => 'lang:admin::lang.button_save',
            'class' => 'btn btn-primary',
            'data-request' => 'onSave',
            'data-progress-indicator' => 'admin::lang.text_saving',
        ],
        'saveClose' => [
            'label' => 'lang:admin::lang.button_save_close',
            'class' => 'btn btn-default',
            'data-request' => 'onSave',
            'data-request-data' => 'close:1',
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

$config['form']['fields'] = [
    '_info' => [
        'type' => 'partial',
        'disabled' => TRUE,
        'path' => 'reservations/form/info',
        'span' => 'left',
        'context' => ['edit', 'preview'],
    ],
    'status_id' => [
        'type' => 'statuseditor',
        'span' => 'right',
        'context' => ['edit', 'preview'],
        'form' => 'reservation_status_model',
    ],
];

$config['form']['tabs'] = [
    'defaultTab' => 'lang:admin::lang.reservations.text_tab_general',
    'fields' => [
        'first_name' => [
            'label' => 'lang:admin::lang.reservations.label_first_name',
            'type' => 'text',
            'span' => 'left',
        ],
        'last_name' => [
            'label' => 'lang:admin::lang.reservations.label_last_name',
            'type' => 'text',
            'span' => 'right',
        ],
        'email' => [
            'label' => 'lang:admin::lang.label_email',
            'type' => 'text',
            'span' => 'left',
        ],
        'telephone' => [
            'label' => 'lang:admin::lang.reservations.label_customer_telephone',
            'type' => 'text',
            'span' => 'right',
        ],
        'reserve_date' => [
            'label' => 'lang:admin::lang.reservations.label_reservation_date',
            'type' => 'datepicker',
            'mode' => 'date',
            'span' => 'left',
            'cssClass' => 'flex-width',
        ],
        'reserve_time' => [
            'label' => 'lang:admin::lang.reservations.label_reservation_time',
            'type' => 'datepicker',
            'mode' => 'time',
            'span' => 'left',
            'cssClass' => 'flex-width',
        ],
        'location_id' => [
            'label' => 'lang:admin::lang.reservations.text_tab_restaurant',
            'type' => 'relation',
            'relationFrom' => 'location',
            'nameFrom' => 'location_name',
            'span' => 'right',
            'placeholder' => 'lang:admin::lang.text_please_select',
            'locationAware' => 'hide',
        ],
        'guest_num' => [
            'label' => 'lang:admin::lang.reservations.label_guest',
            'type' => 'number',
            'span' => 'left',
            'cssClass' => 'flex-width',
        ],
        'tables' => [
            'label' => 'lang:admin::lang.reservations.label_table_name',
            'type' => 'relation',
            'relationFrom' => 'tables',
            'nameFrom' => 'table_name',
            'span' => 'left',
            'cssClass' => 'flex-width',
        ],
        'duration' => [
            'label' => 'lang:admin::lang.reservations.label_reservation_duration',
            'type' => 'number',
            'span' => 'right',
            'comment' => 'lang:admin::lang.reservations.help_reservation_duration',
        ],
        'notify' => [
            'label' => 'lang:admin::lang.reservations.label_send_confirmation',
            'type' => 'switch',
            'span' => 'left',
            'default' => 1,
        ],
        'comment' => [
            'label' => 'lang:admin::lang.statuses.label_comment',
            'type' => 'textarea',
        ],
        'date_added' => [
            'label' => 'lang:admin::lang.reservations.label_date_added',
            'type' => 'datepicker',
            'mode' => 'date',
            'disabled' => TRUE,
            'span' => 'left',
            'context' => ['edit', 'preview'],
        ],
        'ip_address' => [
            'label' => 'lang:admin::lang.reservations.label_ip_address',
            'type' => 'text',
            'span' => 'right',
            'disabled' => TRUE,
            'context' => ['edit', 'preview'],
        ],
        'date_modified' => [
            'label' => 'lang:admin::lang.reservations.label_date_modified',
            'type' => 'datepicker',
            'mode' => 'date',
            'disabled' => TRUE,
            'span' => 'left',
            'context' => ['edit', 'preview'],
        ],
        'user_agent' => [
            'label' => 'lang:admin::lang.reservations.label_user_agent',
            'type' => 'text',
            'span' => 'right',
            'disabled' => TRUE,
            'context' => ['edit', 'preview'],
        ],
        'status_history' => [
            'tab' => 'lang:admin::lang.reservations.text_status_history',
            'type' => 'datatable',
            'context' => ['edit', 'preview'],
            'columns' => [
                'date_added_since' => [
                    'title' => 'lang:admin::lang.reservations.column_date_time',
                ],
                'status_name' => [
                    'title' => 'lang:admin::lang.label_status',
                ],
                'comment' => [
                    'title' => 'lang:admin::lang.reservations.column_comment',
                ],
                'notified' => [
                    'title' => 'lang:admin::lang.reservations.column_notify',
                ],
                'staff_name' => [
                    'title' => 'lang:admin::lang.reservations.column_staff',
                ],
            ],
        ],
    ],
];

return $config;
