<?php namespace Admin\Controllers;

use Admin\Models\Locations_model;
use AdminMenu;
use Exception;

class Locations extends \Admin\Classes\AdminController
{
    public $implement = [
        'Admin\Actions\ListController',
        'Admin\Actions\FormController',
    ];

    public $listConfig = [
        'list' => [
            'model'        => 'Admin\Models\Locations_model',
            'title'        => 'lang:admin::locations.text_title',
            'emptyMessage' => 'lang:admin::locations.text_empty',
            'defaultSort'  => ['location_id', 'DESC'],
            'configFile'   => 'locations_model',
        ],
    ];

    public $formConfig = [
        'name'       => 'lang:admin::locations.text_form_name',
        'model'      => 'Admin\Models\Locations_model',
        'create'     => [
            'title'         => 'lang:admin::default.form.create_title',
            'redirect'      => 'locations/edit/{location_id}',
            'redirectClose' => 'locations',
        ],
        'edit'       => [
            'title'         => 'lang:admin::default.form.edit_title',
            'redirect'      => 'locations/edit/{location_id}',
            'redirectClose' => 'locations',
        ],
        'preview'    => [
            'title'    => 'lang:admin::default.form.preview_title',
            'redirect' => 'locations',
        ],
        'delete'     => [
            'redirect' => 'locations',
        ],
        'configFile' => 'locations_model',
    ];

    protected $requiredPermissions = 'Admin.Locations';

    public function __construct()
    {
        parent::__construct();

        AdminMenu::setContext('locations', 'restaurant');
    }

    public function remap($action, $params)
    {
        if ($action != 'settings' AND is_single_location())
            return $this->redirect('locations/settings');

        return parent::remap($action, $params);
    }

    public function settings($context = null)
    {
        $this->asExtension('FormController')->edit('edit', params('default_location_id'));
    }

    public function index_onSetDefault($context = null)
    {
        $defaultId = post('default');

        if (Locations_model::updateDefault(['location_id' => $defaultId])) {
            flash()->success(sprintf(lang('admin::default.alert_success'), lang('admin::locations.alert_set_default')));
        }

        return $this->refreshList('list');
    }

    public function settings_onSave($context = null)
    {
        try {
            return $this->asExtension('FormController')->edit_onSave('edit', params('default_location_id'));
        } catch (Exception $ex) {
            $this->handleError($ex);
        }
    }

    public function listOverrideColumnValue($record, $column, $alias = null)
    {
        if ($column->type != 'button')
            return null;

        if ($column->columnName != 'default')
            return null;

        $attributes = $column->attributes;
        $column->iconCssClass = 'fa fa-star-o';
        if (($record->getKey() == params('default_location_id'))) {
            $column->iconCssClass = 'fa fa-star';
            $attributes['class'] = $attributes['class'].' active';
        }

        return $attributes;
    }

    public function formExtendQuery($query)
    {
        if (is_single_location())
            $query->where('location_id', params('default_location_id'));
    }

    public function formValidate($model, $form)
    {
        $rules = [
            ['location_name', 'lang:admin::locations.label_name', 'required|min:2|max:32'],
            ['location_email', 'lang:admin::locations.label_email', 'required|email'],
            ['location_telephone', 'lang:admin::locations.label_telephone', 'required|min:2|max:15'],
            ['location_address_1', 'lang:admin::locations.label_address_1', 'required|min:2|max:128'],
            ['location_address_2', 'lang:admin::locations.label_address_2', 'max:128'],
            ['location_city', 'lang:admin::locations.label_city', 'required|min:2|max:128'],
            ['location_state', 'lang:admin::locations.label_state', 'max:128'],
            ['location_postcode', 'lang:admin::locations.label_postcode', 'min:2|max:10'],
            ['location_country_id', 'lang:admin::locations.label_country', 'required|integer'],
            ['options.auto_lat_lng', 'lang:admin::locations.label_auto_lat_lng', 'required|integer'],
            ['location_lat', 'lang:admin::locations.label_latitude', 'numeric'],
            ['location_lng', 'lang:admin::locations.label_longitude', 'numeric'],

            ['description', 'lang:admin::locations.label_description', 'min:2|max:3028'],
            ['offer_delivery', 'lang:admin::locations.label_offer_delivery', 'required|integer'],
            ['offer_collection', 'lang:admin::locations.label_offer_collection', 'required|integer'],
            ['options.offer_reservation', 'lang:admin::locations.label_offer_collection', 'required|integer'],
            ['delivery_time', 'lang:admin::locations.label_delivery_time', 'integer'],
            ['collection_time', 'lang:admin::locations.label_collection_time', 'integer'],
            ['last_order_time', 'lang:admin::locations.label_last_order_time', 'integer'],
            ['options.future_orders', 'lang:admin::locations.label_future_orders', 'required|integer'],
            ['options.future_order_days.*', 'lang:admin::locations.label_future_order_days', 'integer'],
            ['options.payments.*', 'lang:admin::locations.label_payments'],

            ['tables.*', 'lang:admin::locations.label_tables', 'integer'],
            ['reservation_time_interval', 'lang:admin::locations.label_interval', 'integer'],
            ['reservation_stay_time', 'lang:admin::locations.label_turn_time', 'integer'],
            ['location_status', 'lang:admin::default.label_status', 'required|integer'],
            ['permalink_slug', 'lang:admin::locations.label_permalink_slug', 'alpha_dash|max:255'],
            ['location_image', 'lang:admin::locations.label_image'],
        ];

        foreach (['opening', 'delivery', 'collection'] as $type) {
            $requiredIf = post('Location.options.hours.'.$type.'.type') == 'daily' ? '|required' : '';
            $rules[] = ['options.hours.'.$type.'.type', 'lang:admin::locations.label_opening_type', 'alpha_dash|max:10'];
            $rules[] = ['options.hours.'.$type.'.days.*', 'lang:admin::locations.label_opening_days', 'integer'];
            $rules[] = ['options.hours.'.$type.'.open', 'lang:admin::locations.label_open_hour', $requiredIf.'|valid_time'];
            $rules[] = ['options.hours.'.$type.'.close', 'lang:admin::locations.label_close_hour', $requiredIf.'|valid_time'];

            foreach (post('Location.options.hours.'.$type.'.flexible') as $key => $value) {
                $requiredIf = post('Location.options.hours.'.$type.'.type') == 'flexible' ? '|required' : '';
                $rules[] = ['options.hours.'.$type.'.flexible.'.$key.'.day', 'lang:admin::locations.label_opening_days', $requiredIf.'|numeric'];
                $rules[] = ['options.hours.'.$type.'.flexible.'.$key.'.open', 'lang:admin::locations.label_open_hour', $requiredIf.'|valid_time'];
                $rules[] = ['options.hours.'.$type.'.flexible.'.$key.'.close', 'lang:admin::locations.label_close_hour', $requiredIf.'|valid_time'];
                $rules[] = ['options.hours.'.$type.'.flexible.'.$key.'.status', 'lang:admin::locations.label_opening_status', $requiredIf.'|integer'];
            }
        }

        if ($areas = post('Location.options.delivery_areas')) {
            foreach ($areas as $key => $value) {
                $rules[] = ['options.delivery_areas.'.$key.'.type', '['.$key.'] '.lang('label_area_type'), 'required'];
                $rules[] = ['options.delivery_areas.'.$key.'.name', '['.$key.'] '.lang('label_area_name'), 'required'];
                $rules[] = ['options.delivery_areas.'.$key.'.area_id', '['.$key.'] '.lang('label_area_id'), 'integer'];

                $rules[] = ['options.delivery_areas.'.$key.'.boundaries.polygon', '['.$key.'] '.lang('label_area_shape'), 'required'];
                $rules[] = ['options.delivery_areas.'.$key.'.boundaries.circle', '['.$key.'] '.lang('label_area_circle'), 'required'];
                $rules[] = ['options.delivery_areas.'.$key.'.boundaries.vertices', '['.$key.'] '.lang('label_area_vertices'), 'required'];

                $rules[] = ['options.delivery_areas.'.$key.'.conditions', '['.$key.'] '.lang('label_delivery_condition'), 'required'];
                if (post('Location.options.delivery_areas.'.$key.'.conditions')) {
                    foreach (post('Location.options.delivery_areas.'.$key.'.conditions') as $k => $v) {
                        $rules[] = ['options.delivery_areas.'.$key.'.conditions.'.$k.'.amount', '['.$key.'] '.lang('label_area_charge'), 'required|numeric'];
                        $rules[] = ['options.delivery_areas.'.$key.'.conditions.'.$k.'.type', '['.$key.'] '.lang('label_charge_condition'), 'required|alpha_dash'];
                        $rules[] = ['options.delivery_areas.'.$key.'.conditions.'.$k.'.total', '['.$key.'] '.lang('label_area_min_amount'), 'required|numeric'];
                    }
                }
            }
        }

        $rules[] = ['gallery.title', 'lang:admin::locations.label_gallery_title', 'max:128'];
        $rules[] = ['gallery.description', 'lang:admin::locations.label_gallery_description', 'max:255'];
//        $rules[] = ['gallery.images.*', 'lang:admin::locations.label_gallery_image_name', 'required'];
        if (post('Location.options.gallery')) {
//            foreach (post('Location.options.gallery') as $key => $value) {
//                if ($key === 'images') foreach ($value as $key => $image) {
//                    $rules[] = ['gallery.images.'.$key.'.name', 'lang:admin::locations.label_gallery_image_name', 'required'];
//                    $rules[] = ['gallery.images.'.$key.'.path', 'lang:admin::locations.label_gallery_image_thumbnail', 'required'];
//                    $rules[] = ['gallery.images.'.$key.'.alt_text', 'lang:admin::locations.label_gallery_image_alt'];
//                    $rules[] = ['gallery.images.'.$key.'.status', 'lang:admin::locations.label_gallery_image_status', 'required|integer'];
//                }
//            }
        }

        return $this->validatePasses(post($form->arrayName), $rules);
    }
}