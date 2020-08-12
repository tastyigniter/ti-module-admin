<?php

namespace Admin\FormWidgets;

use Admin\Classes\BaseFormWidget;

/**
 * Color picker
 * Renders a color picker field.
 *
 * Adapted from october\backend\formwidgets\ColorPicker
 */
class ColorPicker extends BaseFormWidget
{
    //
    // Configurable properties
    //

    /**
     * @var array Default available colors
     */
    public $availableColors = [
        '#1abc9c', '#16a085',
        '#9b59b6', '#8e44ad',
        '#34495e', '#2b3e50',
        '#f1c40f', '#f39c12',
        '#e74c3c', '#c0392b',
        '#95a5a6', '#7f8c8d',
    ];

    //
    // Object properties
    //

    protected $defaultAlias = 'colorpicker';

    public function initialize()
    {
        $this->fillFromConfig([
            'availableColors',
        ]);
    }

    public function render()
    {
        $this->prepareVars();

        return $this->makePartial('colorpicker/colorpicker');
    }

    /**
     * Prepares the list data
     */
    public function prepareVars()
    {
        $this->vars['name'] = $this->formField->getName();
        $this->vars['value'] = $this->getLoadValue();
        $this->vars['availableColors'] = $this->availableColors();
    }

    public function loadAssets()
    {
        $this->addCss('vendor/colorpicker/css/bootstrap-colorpicker.min.css', 'bootstrap-colorpicker-css');
        $this->addJs('vendor/colorpicker/js/bootstrap-colorpicker.min.js', 'bootstrap-colorpicker-js');
        $this->addCss('css/colorpicker.css', 'colorpicker-css');
        $this->addJs('js/colorpicker.js', 'colorpicker-js');
    }

    public function getSaveValue($value)
    {
        return strlen($value) ? $value : null;
    }

    protected function availableColors()
    {
        $colors = [];
        foreach ($this->availableColors as $availableColor) {
            $colors[$availableColor] = $availableColor;
        }

        return $colors;
    }
}
