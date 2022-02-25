<?php

namespace Admin\Controllers;

use Admin\Facades\AdminMenu;

/**
 * Admin Controller Class Tables
 */
class Tables extends \Admin\Classes\AdminController
{
    public $implement = [
        'Admin\Actions\ListController',
        'Admin\Actions\FormController',
        'Admin\Actions\LocationAwareController',
    ];

    public $listConfig = [
        'list' => [
            'model' => 'Admin\Models\Table',
            'title' => 'lang:admin::lang.tables.text_title',
            'emptyMessage' => 'lang:admin::lang.tables.text_empty',
            'defaultSort' => ['table_id', 'DESC'],
            'configFile' => 'table',
        ],
    ];

    public $formConfig = [
        'name' => 'lang:admin::lang.tables.text_form_name',
        'model' => 'Admin\Models\Table',
        'request' => 'Admin\Requests\Table',
        'create' => [
            'title' => 'lang:admin::lang.form.create_title',
            'redirect' => 'tables/edit/{table_id}',
            'redirectClose' => 'tables',
            'redirectNew' => 'tables/create',
        ],
        'edit' => [
            'title' => 'lang:admin::lang.form.edit_title',
            'redirect' => 'tables/edit/{table_id}',
            'redirectClose' => 'tables',
            'redirectNew' => 'tables/create',
        ],
        'preview' => [
            'title' => 'lang:admin::lang.form.preview_title',
            'redirect' => 'tables',
        ],
        'delete' => [
            'redirect' => 'tables',
        ],
        'configFile' => 'table',
    ];

    protected $requiredPermissions = 'Admin.Tables';

    public function __construct()
    {
        parent::__construct();

        AdminMenu::setContext('tables', 'restaurant');
    }
}
