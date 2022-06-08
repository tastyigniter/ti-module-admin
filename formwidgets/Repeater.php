<?php

namespace Admin\FormWidgets;

use Admin\Classes\BaseFormWidget;
use Admin\Traits\FormModelWidget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Repeater Form Widget
 */
class Repeater extends BaseFormWidget
{
    use FormModelWidget;

    const INDEX_SEARCH = '@@index';

    const SORT_PREFIX = '___dragged_';

    //
    // Configurable properties
    //

    /**
     * @var array Form field configuration
     */
    public $form;

    /**
     * @var string Prompt text for adding new items.
     */
    public $prompt;

    /**
     * @var bool Items can be sorted.
     */
    public $sortable = false;

    public $sortColumnName = 'priority';

    public $showAddButton = true;

    public $showRemoveButton = true;

    public $emptyMessage = 'lang:admin::lang.text_empty';

    //
    // Object properties
    //

    protected $defaultAlias = 'repeater';

    /**
     * @var int Count of repeated items.
     */
    protected $indexCount = 0;

    protected $itemDefinitions = [];

    protected $sortableInputName;

    /**
     * @var array Collection of form widgets.
     */
    protected $formWidgets = [];

    public function initialize()
    {
        $this->fillFromConfig([
            'form',
            'prompt',
            'emptyMessage',
            'sortable',
            'sortColumnName',
            'showAddButton',
            'showRemoveButton',
        ]);

        $this->processItemDefinitions();

        $fieldName = $this->formField->getId();
        $this->sortableInputName = self::SORT_PREFIX.$fieldName;

        $this->processExistingItems();
    }

    public function render()
    {
        $this->prepareVars();

        // Apply preview mode to widgets
        foreach ($this->formWidgets as $widget) {
            $widget->previewMode = $this->previewMode;
        }

        return $this->makePartial('repeater/repeater');
    }

    public function getLoadValue()
    {
        $value = parent::getLoadValue();

        if (!$this->sortable)
            return $value;

        if (is_array($value)) {
            $value = sort_array($value, $this->sortColumnName);
        }
        elseif ($value instanceof Collection) {
            $value = $value->sortBy($this->sortColumnName);
        }

        return $value;
    }

    public function getSaveValue($value)
    {
        return (array)$this->processSaveValue($value);
    }

    public function loadAssets()
    {
        $this->addJs('vendor/sortablejs/Sortable.min.js', 'sortable-js');
        $this->addJs('vendor/sortablejs/jquery-sortable.js', 'jquery-sortable-js');
        $this->addJs('js/repeater.js', 'repeater-js');
    }

    public function prepareVars()
    {
        $this->vars['formWidgets'] = $this->formWidgets;
        $this->vars['widgetTemplate'] = $this->getFormWidgetTemplate();
        $this->vars['formField'] = $this->formField;

        $this->indexCount++;
        $this->vars['nextIndex'] = $this->indexCount;
        $this->vars['prompt'] = $this->prompt;
        $this->vars['sortable'] = $this->sortable;
        $this->vars['emptyMessage'] = $this->emptyMessage;
        $this->vars['showAddButton'] = $this->showAddButton;
        $this->vars['showRemoveButton'] = $this->showRemoveButton;
        $this->vars['indexSearch'] = self::INDEX_SEARCH;
        $this->vars['sortableInputName'] = $this->sortableInputName;
    }

    public function getVisibleColumns()
    {
        if (!isset($this->itemDefinitions['fields']))
            return [];

        $columns = [];
        foreach ($this->itemDefinitions['fields'] as $name => $field) {
            if (isset($field['type']) && $field['type'] == 'hidden')
                continue;

            $columns[$name] = $field['label'] ?? null;
        }

        return $columns;
    }

    public function getFormWidgetTemplate()
    {
        $index = self::INDEX_SEARCH;

        return $this->makeItemFormWidget($index, []);
    }

    protected function processSaveValue($value)
    {
        if (!is_array($value) || !$value) return $value;

        $sortedIndexes = (array)post($this->sortableInputName);
        $sortedIndexes = array_flip($sortedIndexes);

        foreach ($value as $index => &$data) {
            if ($sortedIndexes && $this->sortable)
                $data[$this->sortColumnName] = $sortedIndexes[$index];

            $items[$index] = $data;
        }

        return $value;
    }

    protected function processItemDefinitions()
    {
        $form = $this->form;
        if (!is_array($form))
            $form = $this->loadConfig($form, ['form'], 'form');

        $this->itemDefinitions = ['fields' => array_get($form, 'fields')];
    }

    protected function processExistingItems()
    {
        $loadedIndexes = [];

        $loadValue = $this->getLoadValue();
        if (is_array($loadValue)) {
            $loadedIndexes = array_keys($loadValue);
        }
        elseif ($loadValue instanceof Collection) {
            $loadedIndexes = $loadValue->keys()->all();
        }

        $itemIndexes = post($this->sortableInputName, $loadedIndexes);

        if (!count($itemIndexes)) return;

        foreach ($itemIndexes as $itemIndex) {
            $model = $this->getLoadValueFromIndex($loadValue, $itemIndex);
            $this->formWidgets[$itemIndex] = $this->makeItemFormWidget($itemIndex, $model);
            $this->indexCount = max((int)$itemIndex, $this->indexCount);
        }
    }

    protected function makeItemFormWidget($index, $model)
    {
        $data = null;
        if (!$model instanceof Model) {
            $data = $model;
            $model = $this->getRelationModel();
        }

        $config = $this->itemDefinitions;
        $config['model'] = $model;
        $config['data'] = $data;
        $config['alias'] = $this->alias.'Form'.$index;
        $config['arrayName'] = $this->formField->getName().'['.$index.']';

        $widget = $this->makeWidget('Admin\Widgets\Form', $config);
        $widget->bindToController();

        return $widget;
    }

    /**
     * Returns the load data at a given index.
     *
     * @param int $index
     *
     * @return mixed
     */
    protected function getLoadValueFromIndex($loadValue, $index)
    {
        if (is_array($loadValue)) {
            return array_get($loadValue, $index, []);
        }
        elseif ($loadValue instanceof Collection) {
            return $loadValue->get($index);
        }

        return null;
    }

    protected function getRelationModel()
    {
        [$model, $attribute] = $this->resolveModelAttribute($this->valueFrom);

        if (!$model instanceof Model || !$model->hasRelation($attribute)) {
            return $this->model;
        }

        $related = $model->makeRelation($attribute);

        if (!$related->exists)
            $related->{$this->model->getKeyName()} = $this->model->getKey();

        return $related;
    }
}
