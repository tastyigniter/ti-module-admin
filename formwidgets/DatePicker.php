<?php

namespace Admin\FormWidgets;

use Admin\Classes\BaseFormWidget;
use Admin\Classes\FormField;
use Carbon\Carbon;

/**
 * Date picker
 * Renders a date picker field.
 */
class DatePicker extends BaseFormWidget
{
    //
    // Configurable properties
    //

    /**
     * @var bool Display mode: datetime, date, time.
     */
    public $mode = 'date';

    /**
     * @var string the minimum/earliest date that can be selected.
     * eg: 2000-01-01
     */
    public $startDate = null;

    /**
     * @var string the maximum/latest date that can be selected.
     * eg: 2020-12-31
     */
    public $endDate = null;

    public $dateFormat = 'd-m-Y';

    public $timeFormat = 'H:i';

    protected $datesDisabled;

    //
    // Object properties
    //
    protected $defaultAlias = 'datepicker';

    public function initialize()
    {
        $this->fillFromConfig([
            'format',
            'mode',
            'startDate',
            'endDate',
        ]);

        $this->mode = strtolower($this->mode);

        if ($this->startDate !== null) {
            $this->startDate = is_int($this->startDate)
                ? Carbon::createFromTimestamp($this->startDate)
                : Carbon::parse($this->startDate);
        }

        if ($this->endDate !== null) {
            $this->endDate = is_int($this->endDate)
                ? Carbon::createFromTimestamp($this->endDate)
                : Carbon::parse($this->endDate);
        }
    }

    public function loadAssets()
    {
        $mode = $this->getConfig('mode', 'date');
        if ($mode == 'time') {
            $this->addCss('vendor/clockpicker/bootstrap-clockpicker.min.css', 'bootstrap-clockpicker-css');
            $this->addJs('vendor/clockpicker/bootstrap-clockpicker.min.js', 'bootstrap-clockpicker-js');
            $this->addCss('css/clockpicker.css', 'clockpicker-css');
            $this->addJs('js/clockpicker.js', 'clockpicker-js');
        }

        if ($mode == 'date') {
            $this->addJs('~/app/admin/assets/src/js/vendor/moment.min.js', 'moment-js');
            $this->addCss('vendor/datepicker/bootstrap-datepicker.min.css', 'bootstrap-datepicker-css');
            $this->addJs('vendor/datepicker/bootstrap-datepicker.min.js', 'bootstrap-datepicker-js');
            if (setting('default_language') != 'en')
                $this->addJs('vendor/datepicker/locales/bootstrap-datepicker.'.strtolower(str_replace('_', '-', setting('default_language'))).'.min.js', 'bootstrap-datepicker-js');
            $this->addCss('css/datepicker.css', 'datepicker-css');
            $this->addJs('js/datepicker.js', 'datepicker-js');
        }

        if ($mode == 'datetime') {
            $this->addJs('~/app/admin/assets/src/js/vendor/moment.min.js', 'moment-js');
            $this->addCss('vendor/datetimepicker/tempusdominus-bootstrap-4.min.css', 'tempusdominus-bootstrap-4-css');
            $this->addJs('vendor/datetimepicker/tempusdominus-bootstrap-4.min.js', 'tempusdominus-bootstrap-4-js');
            $this->addCss('css/datepicker.css', 'datepicker-css');
            $this->addJs('js/datepicker.js', 'datepicker-js');
        }
    }

    public function render()
    {
        $this->prepareVars();

        return $this->makePartial('datepicker/datepicker');
    }

    /**
     * Prepares the list data
     */
    public function prepareVars()
    {
        $this->vars['name'] = $this->formField->getName();

        if ($value = $this->getLoadValue()) {
            $value = make_carbon($value, false);
        }

        // Display alias, used by preview mode
        if ($this->mode == 'time') {
            $formatAlias = lang('system::lang.php.time_format');
        }
        elseif ($this->mode == 'date') {
            $formatAlias = lang('system::lang.php.date_format');
        }
        else {
            $formatAlias = lang('system::lang.php.date_time_format');
        }

        $find = ['d' => 'dd', 'D' => 'DD', 'm' => 'mm', 'M' => 'MM', 'y' => 'yy', 'Y' => 'yyyy', 'H' => 'HH', 'i' => 'i'];

        $this->vars['timeFormat'] = $this->timeFormat;
        $this->vars['dateFormat'] = $this->dateFormat;
        $this->vars['dateTimeFormat'] = $this->dateFormat.' '.$this->timeFormat;

        $this->vars['datePickerFormat'] = ($this->mode == 'datetime')
            ? convert_php_to_moment_js_format($formatAlias)
            : strtr($this->dateFormat, $find);

        $this->vars['formatAlias'] = $formatAlias;
        $this->vars['value'] = $value;
        $this->vars['field'] = $this->formField;
        $this->vars['mode'] = $this->mode;
        $this->vars['startDate'] = $this->startDate ?? null;
        $this->vars['endDate'] = $this->endDate ?? null;
        $this->vars['datesDisabled'] = $this->datesDisabled;
    }

    public function getSaveValue($value)
    {
        if ($this->formField->disabled || $this->formField->hidden) {
            return FormField::NO_SAVE_DATA;
        }

        if (!strlen($value)) {
            return null;
        }

        return $value;
    }
}
