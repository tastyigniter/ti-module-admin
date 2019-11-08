<div class="modal fade" id="<?= $listId ?>-setup-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><?= sprintf(lang('admin::lang.list.setup_title'), lang($this->getConfig('title'))); ?></h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div id="<?= $listId ?>-setup-modal-content">
                <div class="modal-body text-center">
                    <span class="spinner text-muted"><span class="ti-loading fa-3x fa-fw"></span></span>
                </div>
            </div>
        </div>
    </div>
</div>
