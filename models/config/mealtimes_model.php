<?php
$config['list']['filter'] = [
    'search' => [
        'prompt' => 'lang:admin::mealtimes.text_filter_search',
        'mode'   => 'all' // or any, exact
    ],
    'scopes' => [
        'status' => [
            'label'      => 'lang:admin::mealtimes.text_filter_status',
            'type'       => 'switch',
            'conditions' => 'status = :filtered',
        ],
    ],
];

$config['list']['toolbar'] = [
    'buttons' => [
        'create' => ['label' => 'lang:admin::default.button_new', 'class' => 'btn btn-primary', 'href' => 'mealtimes/create'],
        'delete' => ['label' => 'lang:admin::default.button_delete', 'class' => 'btn btn-danger', 'data-request-form' => '#list-form', 'data-request' => 'onDelete', 'data-request-data' => "_method:'DELETE'", 'data-request-confirm' => 'lang:admin::default.alert_warning_confirm'],
        'filter' => ['label' => 'lang:admin::default.button_icon_filter', 'class' => 'btn btn-default btn-filter', 'data-toggle' => 'list-filter', 'data-target' => '.list-filter'],
    ],
];

$config['list']['columns'] = [
    'edit'            => [
        'type'         => 'button',
        'iconCssClass' => 'fa fa-pencil',
        'attributes'   => [
            'class' => 'btn btn-edit',
            'href'  => 'mealtimes/edit/{mealtime_id}',
        ],
    ],
    'mealtime_name'   => [
        'label'      => 'lang:admin::mealtimes.column_mealtime_name',
        'type'       => 'text',
        'searchable' => TRUE,
    ],
    'start_time'      => [
        'label' => 'lang:admin::mealtimes.column_start_time',
        'type'  => 'time',
    ],
    'end_time'        => [
        'label' => 'lang:admin::mealtimes.column_end_time',
        'type'  => 'time',
    ],
    'mealtime_status' => [
        'label' => 'lang:admin::mealtimes.column_mealtime_status',
        'type'  => 'switch',
    ],
    'mealtime_id'     => [
        'label'     => 'lang:admin::mealtimes.column_mealtime_id',
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
    ],
];

$config['form']['fields'] = [
    'mealtime_name'   => [
        'label' => 'lang:admin::mealtimes.label_mealtime_name',
        'type'  => 'text',
        'span'  => 'left',
    ],
    'mealtime_status' => [
        'label'   => 'lang:admin::mealtimes.label_mealtime_status',
        'type'    => 'switch',
        'default' => TRUE,
        'span'    => 'right',
    ],
    'start_time'      => [
        'label' => 'lang:admin::mealtimes.label_start_time',
        'type'  => 'datepicker',
        'mode'  => 'time',
        'span'  => 'left',
    ],
    'end_time'        => [
        'label' => 'lang:admin::mealtimes.label_end_time',
        'type'  => 'datepicker',
        'mode'  => 'time',
        'span'  => 'right',
    ],
];

return $config;