<?php
$config['list']['columns'] = [
    'date_added' => [
        'label' => 'lang:admin::lang.statuses.column_time_date',
        'type' => 'timetense',
    ],
    'staff_name' => [
        'label' => 'lang:admin::lang.statuses.column_staff',
        'relation' => 'staff',
        'select' => 'staff_name',
    ],
    'status' => [
        'label' => 'lang:admin::lang.label_status',
        'relation' => 'status',
        'select' => 'status_name',
    ],
    'comment' => [
        'label' => 'lang:admin::lang.statuses.column_comment',
    ],
    'notified' => [
        'label' => 'lang:admin::lang.statuses.column_notify',
    ],
];

return $config;
