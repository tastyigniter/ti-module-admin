<?php $lockerValue = $value ? $value->format($dateTimeFormat) : null ?>
<div class="input-group">
    <input
        type="text"
        id="<?= $this->getId('datetime') ?>"
        class="form-control"
        autocomplete="off"
        value="<?= $lockerValue ?>"
        <?= $field->getAttributes() ?>
        <?= $this->previewMode ? 'readonly="readonly"' : '' ?>
        data-control="datepicker"
        data-mode="<?= $this->mode ?>"
        <?php if ($startDate) { ?>data-start-date="<?= $startDate ?>"<?php } ?>
        <?php if ($endDate) { ?>data-end-date="<?= $endDate ?>"<?php } ?>
        <?php if ($datesDisabled) { ?>data-dates-disabled="<?= $datesDisabled ?>"<?php } ?>
        data-format="<?= $datePickerFormat ?>"
    />
    <span class="input-group-prepend">
        <span class="input-group-icon"><i class="fa fa-calendar-o"></i></span>
    </span>
    <input
        type="hidden"
        name="<?= $field->getName() ?>"
        value="<?= $value ? $value->format('Y-m-d H:i:s') : null ?>"
        data-datepicker-value
    />
</div>
