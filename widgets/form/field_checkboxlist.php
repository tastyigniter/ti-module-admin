<?php
$fieldOptions = $field->options();
$checkedValues = (array)$field->value;
$isScrollable = count($fieldOptions) > 10;
$inlineMode = (bool)$field->getConfig('inlineMode');
?>
<?php if ($this->previewMode && $field->value) { ?>

    <div class="field-checkboxlist">
        <?php $index = 0;
        foreach ($fieldOptions as $value => $option) { ?>
            <?php
            $index++;
            $checkboxId = 'checkbox_'.$field->getId().'_'.$index;
            if (!in_array($value, $checkedValues)) continue;
            if (!is_array($option)) $option = [$option];
            ?>
            <div class="custom-control custom-checkbox<?= $inlineMode ? ' custom-control-inline' : '' ?> mb-2">
                <input
                    type="checkbox"
                    id="<?= $checkboxId ?>"
                    class="custom-control-input"
                    name="<?= $field->getName() ?>[]"
                    value="<?= $value ?>"
                    disabled="disabled"
                    checked="checked"
                >
                <label class="custom-control-label" for="<?= $checkboxId ?>">
                    <?= e(is_lang_key($option[0]) ? lang($option[0]) : $option[0]) ?>
                    <?php if (isset($option[1])) { ?>
                        <p class="help-block font-weight-normal"><?= e(is_lang_key($option[1]) ? lang($option[1]) : $option[1]) ?></p>
                    <?php } ?>
                </label>
            </div>
        <?php } ?>
    </div>

<?php } elseif (!$this->previewMode AND count($fieldOptions)) { ?>

    <div class="field-checkboxlist <?= $isScrollable ? 'is-scrollable' : '' ?>">
        <?php if ($isScrollable) { ?>
        <small>
            <?= e(lang('admin::lang.text_select')) ?>:
            <a href="javascript:;" data-field-checkboxlist-all><?= e(lang('admin::lang.text_select_all')) ?></a>,
            <a href="javascript:;" data-field-checkboxlist-none><?= e(lang('admin::lang.text_select_none')) ?></a>
        </small>

        <div class="field-checkboxlist-scrollable">
            <div class="scrollbar">
                <?php } ?>

                <input
                    type="hidden"
                    name="<?= $field->getName() ?>"
                    value="0"/>

                <?php $index = 0;
                foreach ($fieldOptions as $value => $option) { ?>
                    <?php
                    $index++;
                    $checkboxId = 'checkbox_'.$field->getId().'_'.$index;
                    if (is_string($option)) $option = [$option];
                    ?>
                    <div class="custom-control custom-checkbox<?= $inlineMode ? ' custom-control-inline' : '' ?> mb-2">
                        <input
                            type="checkbox"
                            id="<?= $checkboxId ?>"
                            class="custom-control-input"
                            name="<?= $field->getName() ?>[]"
                            value="<?= $value ?>"
                            <?= in_array($value, $checkedValues) ? 'checked="checked"' : '' ?>>

                        <label class="custom-control-label" for="<?= $checkboxId ?>">
                            <?= isset($option[0]) ? e(lang($option[0])) : '&nbsp;' ?>
                            <?php if (isset($option[1])) { ?>
                                <p class="help-block font-weight-normal"><?= e(lang($option[1])) ?></p>
                            <?php } ?>
                        </label>
                    </div>
                <?php } ?>

                <?php if ($isScrollable) { ?>
            </div>
        </div>
    <?php } ?>

    </div>

<?php } else { ?>

    <?php if ($field->placeholder) { ?>
        <p><?= e(lang($field->placeholder)) ?></p>
    <?php } ?>

<?php } ?>
