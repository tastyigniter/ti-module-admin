<?php
$config['list']['filter'] = [
    'search' => [
        'prompt' => 'lang:admin::lang.tables.text_filter_search',
        'mode' => 'all', // or any, exact
    ],
    'scopes' => [
        'location' => [
            'label' => 'lang:admin::lang.text_filter_location',
            'type' => 'select',
            'scope' => 'whereHasLocation',
            'modelClass' => 'Admin\Models\Locations_model',
            'nameFrom' => 'location_name',
            'locationAware' => TRUE,
        ],
        'status' => [
            'label' => 'lang:admin::lang.text_filter_status',
            'type' => 'switch',
            'conditions' => 'table_status = :filtered',
        ],
    ],
];

$config['list']['toolbar'] = [
    'buttons' => [
        'create' => [
            'label' => 'lang:admin::lang.button_new',
            'class' => 'btn btn-primary',
            'href' => 'tables/create',
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
            'href' => 'tables/edit/{table_id}',
        ],
    ],
    'table_name' => [
        'label' => 'lang:admin::lang.label_name',
        'type' => 'text',
        'searchable' => TRUE,
    ],
    'min_capacity' => [
        'label' => 'lang:admin::lang.tables.column_min_capacity',
        'type' => 'text',
        'searchable' => TRUE,
    ],
    'max_capacity' => [
        'label' => 'lang:admin::lang.tables.column_capacity',
        'type' => 'number',
    ],
    'extra_capacity' => [
        'label' => 'lang:admin::lang.tables.column_extra_capacity',
        'type' => 'number',
        'invisible' => TRUE,
    ],
    'priority' => [
        'label' => 'lang:admin::lang.tables.column_priority',
        'type' => 'number',
        'invisible' => TRUE,
    ],
    'locations' => [
        'label' => 'lang:admin::lang.column_location',
        'type' => 'text',
        'relation' => 'locations',
        'select' => 'location_name',
        'locationAware' => TRUE,
    ],
    'is_joinable' => [
        'label' => 'lang:admin::lang.tables.label_joinable',
        'type' => 'switch',
        'onText' => 'lang:admin::lang.text_yes',
        'offText' => 'lang:admin::lang.text_no',
    ],
    'table_status' => [
        'label' => 'lang:admin::lang.label_status',
        'type' => 'switch',
    ],
    'table_id' => [
        'label' => 'lang:admin::lang.column_id',
        'invisible' => TRUE,
    ],

];

$config['form']['toolbar'] = [
    'buttons' => [
        'back' => [
            'label' => 'lang:admin::lang.button_icon_back',
            'class' => 'btn btn-default',
            'href' => 'tables',
        ],
        'save' => [
            'label' => 'lang:admin::lang.button_save',
            'context' => ['create', 'edit'],
            'partial' => 'form/toolbar_save_button',
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

$config['form']['fields'] = [
    'table_name' => [
        'label' => 'lang:admin::lang.label_name',
        'type' => 'text',
        'span' => 'left',
    ],
    'priority' => [
        'label' => 'lang:admin::lang.tables.label_priority',
        'type' => 'number',
        'span' => 'right',
    ],
    'min_capacity' => [
        'label' => 'lang:admin::lang.tables.label_min_capacity',
        'type' => 'number',
        'span' => 'left',
    ],
    'max_capacity' => [
        'label' => 'lang:admin::lang.tables.label_capacity',
        'type' => 'number',
        'span' => 'right',
    ],
    'table_status' => [
        'label' => 'lang:admin::lang.label_status',
        'type' => 'switch',
        'span' => 'left',
        'default' => 1,
    ],
    'is_joinable' => [
        'label' => 'lang:admin::lang.tables.label_joinable',
        'type' => 'switch',
        'span' => 'right',
        'default' => 1,
        'on' => 'lang:admin::lang.text_yes',
        'off' => 'lang:admin::lang.text_no',
    ],
    'locations' => [
        'label' => 'lang:admin::lang.label_location',
        'type' => 'relation',
        'valueFrom' => 'locations',
        'nameFrom' => 'location_name',
    ],
    'extra_capacity' => [
        'label' => 'lang:admin::lang.tables.label_extra_capacity',
        'type' => 'number',
        'comment' => 'lang:admin::lang.tables.help_extra_capacity',
    ],
];

return $config;
