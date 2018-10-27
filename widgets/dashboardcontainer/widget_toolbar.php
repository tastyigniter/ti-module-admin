<div class="toolbar-action">
    <button
        type="button"
        class="btn btn-outline-primary"
        data-toggle="modal"
        data-target="#newWidgetModal"
        data-request="<?= $this->getEventHandler('onLoadAddPopup') ?>"
        tabindex="-1"
    >
        <i class="fa fa-plus"></i>&nbsp;&nbsp;<?= e(lang('admin::lang.dashboard.button_add_widget')) ?>
    </button>
    <button
        type="button"
        class="btn btn-outline-danger"
        data-request="<?= $this->getEventHandler('onResetWidgets') ?>"
        data-request-confirm="<?= e(trans('admin::lang.alert_warning_confirm')) ?>"
        tabindex="-1"
    >
        <i class="fa fa-refresh"></i>&nbsp;&nbsp;<?= e(trans('admin::lang.dashboard.button_reset_widgets')) ?>
    </button>
    <button
        type="button"
        class="btn btn-outline-default"
        data-request="<?= $this->getEventHandler('onSetAsDefault') ?>"
        data-request-confirm="<?= e(trans('admin::lang.dashboard.alert_set_default_confirm')) ?>"
        tabindex="-1"
    >
        <i class="fa fa-save"></i>&nbsp;&nbsp;<?= e(trans('admin::lang.dashboard.button_set_default')) ?>
    </button>
</div>
<div class="modal slideInDown fade"
     id="newWidgetModal"
     tabindex="-1"
     role="dialog"
     aria-labelledby="newWidgetModalTitle"
     aria-hidden="true"
>
    <div class="modal-dialog" role="document">
        <div id="<?= $this->getId('new-widget-modal-content') ?>" class="modal-content">
            <div class="modal-body">
                <div class="loading">
                    <span class="spinner"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i></span>
                </div>
            </div>
        </div>
    </div>
</div>