<?php
$config['list']['filter'] = [
    'scopes' => [
        'status' => [
            'label' => 'lang:admin::lang.text_filter_status',
            'type' => 'switch',
            'conditions' => 'mealtime_status = :filtered',
        ],
    ],
];

$config['list']['toolbar'] = [
    'buttons' => [
        'create' => [
            'label' => 'lang:admin::lang.button_new',
            'class' => 'btn btn-primary',
            'href' => 'mealtimes/create',
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
        'filter' => [
            'label' => 'lang:admin::lang.button_icon_filter',
            'class' => 'btn btn-default btn-filter',
            'data-toggle' => 'list-filter',
            'data-target' => '.list-filter',
        ],
    ],
];

$config['list']['columns'] = [
    'edit' => [
        'type' => 'button',
        'iconCssClass' => 'fa fa-pencil',
        'attributes' => [
            'class' => 'btn btn-edit',
            'href' => 'mealtimes/edit/{mealtime_id}',
        ],
    ],
    'mealtime_name' => [
        'label' => 'lang:admin::lang.label_name',
        'type' => 'text',
    ],
    'start_time' => [
        'label' => 'lang:admin::lang.mealtimes.column_start_time',
        'type' => 'time',
    ],
    'end_time' => [
        'label' => 'lang:admin::lang.mealtimes.column_end_time',
        'type' => 'time',
    ],
    'mealtime_status' => [
        'label' => 'lang:admin::lang.label_status',
        'type' => 'switch',
    ],
    'mealtime_id' => [
        'label' => 'lang:admin::lang.column_id',
        'invisible' => TRUE,
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
    'mealtime_name' => [
        'label' => 'lang:admin::lang.mealtimes.label_mealtime_name',
        'type' => 'text',
        'span' => 'left',
    ],
    'mealtime_status' => [
        'label' => 'lang:admin::lang.label_status',
        'type' => 'switch',
        'default' => TRUE,
        'span' => 'right',
    ],
    'start_time' => [
        'label' => 'lang:admin::lang.mealtimes.label_start_time',
        'type' => 'datepicker',
        'mode' => 'time',
        'span' => 'left',
    ],
    'end_time' => [
        'label' => 'lang:admin::lang.mealtimes.label_end_time',
        'type' => 'datepicker',
        'mode' => 'time',
        'span' => 'right',
    ],
];

return $config;