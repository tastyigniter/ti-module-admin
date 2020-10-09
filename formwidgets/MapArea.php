<?php

namespace Admin\FormWidgets;

use Admin\Classes\BaseFormWidget;
use Admin\Classes\FormField;
use Admin\Models\Location_areas_model;
use Admin\Traits\FormModelWidget;
use Html;
use Igniter\Flame\Exception\ApplicationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Map Area
 */
class MapArea extends BaseFormWidget
{
    use FormModelWidget;

    //
    // Configurable properties
    //

    public $form;

    public $modelClass = Location_areas_model::class;

    public $prompt = 'lang:admin::lang.locations.text_add_new_area';

    public $formName = 'lang:admin::lang.locations.text_edit_area';

    public $addLabel = 'New';

    public $editLabel = 'Edit';

    public $deleteLabel = 'Delete';

    //
    // Object properties
    //

    protected $defaultAlias = 'maparea';

    protected $areaColors;

    protected $shapeDefaultProperties = [
        'id' => null,
        'default' => 'address',
        'options' => [],
        'circle' => [],
        'polygon' => [],
        'vertices' => [],
        'serialized' => FALSE,
        'editable' => FALSE,
    ];

    protected $formWidget;

    protected $mapAreas;

    public function initialize()
    {
        $this->fillFromConfig([
            'modelClass',
            'prompt',
            'form',
            'formName',
            'addLabel',
            'editLabel',
            'deleteLabel',
        ]);

        $this->areaColors = Location_areas_model::$areaColors;
    }

    public function loadAssets()
    {
        $this->addJs('../../repeater/assets/vendor/sortablejs/Sortable.min.js', 'sortable-js');
        $this->addJs('../../repeater/assets/vendor/sortablejs/jquery-sortable.js', 'jquery-sortable-js');
        $this->addJs('../../repeater/assets/js/repeater.js', 'repeater-js');
        $this->addJs('../../recordeditor/assets/js/recordeditor.modal.js', 'recordeditor-modal-js');

        $this->addCss('css/maparea.css', 'maparea-css');
        $this->addJs('js/maparea.js', 'maparea-js');

        // Make the mapview assets available
        if (strlen($key = setting('maps_api_key'))) {
            $url = 'https://maps.googleapis.com/maps/api/js?key=%s&libraries=geometry';
            $this->addJs(sprintf($url, $key),
                ['name' => 'google-maps-js', 'async' => null, 'defer' => null]
            );
        }

        $this->addJs('../../mapview/assets/js/mapview.js', 'mapview-js');
        $this->addJs('../../mapview/assets/js/mapview.shape.js', 'mapview-shape-js');
    }

    public function render()
    {
        $this->prepareVars();

        return $this->makePartial('maparea/maparea');
    }

    public function prepareVars()
    {
        $this->vars['field'] = $this->formField;
        $this->vars['mapAreas'] = $this->getMapAreas();

        $this->vars['prompt'] = $this->prompt;
    }

    public function getSaveValue($value)
    {
        return FormField::NO_SAVE_DATA;
    }

    public function onLoadRecord()
    {
        $model = strlen($areaId = post('recordId'))
            ? $this->findFormModel($areaId)
            : $this->createFormModel();

        return $this->makePartial('maparea/area_form', [
            'formAreaId' => $areaId,
            'formTitle' => ($model->exists ? $this->editLabel : $this->addLabel).' '.lang($this->formName),
            'formWidget' => $this->makeAreaFormWidget($model, 'edit'),
        ]);
    }

    public function onSaveRecord()
    {
        $model = strlen($areaId = post('areaId'))
            ? $this->findFormModel($areaId)
            : $this->createFormModel();

        $form = $this->makeAreaFormWidget($model, 'edit');

        $modelsToSave = $this->prepareModelsToSave($model, $form->getSaveData());

        DB::transaction(function () use ($modelsToSave) {
            foreach ($modelsToSave as $modelToSave) {
                $modelToSave->saveOrFail();
            }
        });

        flash()->success(sprintf(lang('admin::lang.alert_success'),
            'Area '.($form->context == 'create' ? 'created' : 'updated')
        ))->now();

        $this->formField->value = null;
        $this->model->reloadRelations();

        $this->prepareVars();

        return [
            '#notification' => $this->makePartial('flash'),
            '.map-area-container' => $this->makePartial('maparea/areas'),
        ];
    }

    public function onDeleteArea()
    {
        if (!strlen($areaId = post('areaId')))
            throw new ApplicationException('Invalid area selected');

        $model = $this->getRelationModel()->find($areaId);
        if (!$model)
            throw new ApplicationException(sprintf(lang('admin::lang.form.not_found'), $areaId));

        $model->delete();

        flash()->success(sprintf(lang('admin::lang.alert_success'), lang($this->formName).' deleted'))->now();

        $this->prepareVars();

        return [
            '#notification' => $this->makePartial('flash'),
        ];
    }

    public function getMapShapeAttributes($area)
    {
        $areaColor = $area->color;

        $attributes = [
            'data-id' => $area->area_id ?? 1,
            'data-name' => $area->name ?? '',
            'data-default' => $area->type ?? 'address',
            'data-color' => $areaColor,
            'data-polygon' => $area->boundaries['polygon'] ?? null,
            'data-circle' => $area->boundaries['circle'] ?? null,
            'data-vertices' => $area->boundaries['vertices'] ?? null,
            'data-editable' => $this->previewMode ? 'false' : 'true',
            'data-options' => json_encode([
                'fillColor' => $areaColor,
                'strokeColor' => $areaColor,
                'distanceUnit' => setting('distance_unit'),
            ]),
        ];

        return Html::attributes($attributes);
    }

    protected function getMapAreas()
    {
        $loadValue = $this->getLoadValue();

        $loadValue = $loadValue instanceof Collection
            ? $loadValue->toArray()
            : $loadValue;

        $result = [];

        foreach ($loadValue as $key => $area) {
            if (!isset($area['color']) OR !strlen($area['color'])) {
                $index = min($key, count($this->areaColors));
                $area['color'] = $this->areaColors[$index] ?? $this->areaColors[0];
            }

            $result[$key] = (object)$area;
        }

        return $this->mapAreas = $result;
    }

    protected function makeAreaFormWidget($model, $context = null)
    {
        if (is_null($context))
            $context = $model->exists ? 'edit' : 'create';

        if (is_null($model->location_id))
            $model->location_id = $this->model->getKey();

        $config = is_string($this->form) ? $this->loadConfig($this->form, ['form'], 'form') : $this->form;
        $config['context'] = $context;
        $config['model'] = $model;
        $config['alias'] = $this->alias.'Form';
        $config['arrayName'] = $this->formField->arrayName.'[areaData]';

        $widget = $this->makeWidget('Admin\Widgets\Form', $config);
        $widget->bindToController();

        return $this->formWidget = $widget;
    }
}
