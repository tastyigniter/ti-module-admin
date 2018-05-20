<?php foreach ($records as $record) { ?>
    <tr>
        <?php if ($showDragHandle) { ?>
            <td class="list-action">
                <div class="btn btn-handle">
                    <i class="fa fa-sort handle"></i>
                </div>
            </td>
        <?php } ?>

        <?php if ($showCheckboxes) { ?>
            <td class="list-action">
                <div class="custom-control custom-checkbox">
                    <input
                        type="checkbox"
                        id="<?= 'checkbox-'.$record->getKey() ?>"
                        class="custom-control-input"
                        value="<?= $record->getKey(); ?>" name="checked[]"
                    />
                    <label class="custom-control-label" for="<?= 'checkbox-'.$record->getKey() ?>"></label>
                </div>
            </td>
        <?php } ?>

        <?php $index = $url = 0; ?>
        <?php foreach ($columns as $key => $column) { ?>
            <?php $index++; ?>
            <?php if ($column->type == 'button') { ?>
                <td class="list-action <?= $column->cssClass ?>">
                    <?= $this->makePartial('lists/list_button', ['record' => $record, 'column' => $column]) ?>
                </td>
            <?php } else { ?>
                <td
                    class="list-col-index-<?= $index ?> list-col-name-<?= $column->getName() ?> list-col-type-<?= $column->type ?> <?= $column->cssClass ?>"
                >
                    <?= $this->getColumnValue($record, $column) ?>
                </td>
            <?php } ?>
        <?php } ?>

        <?php if ($showSetup) { ?>
            <td class="list-setup">&nbsp;</td>
        <?php } ?>
    </tr>
<?php } ?>
