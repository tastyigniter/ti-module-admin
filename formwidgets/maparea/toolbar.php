<div
    class="btn-toolbar justify-content-between"
    data-control="map-toolbar"
    role="toolbar"
>
    <div class="toolbar-item">
        <div class="btn-group">
            <button
                type="button"
                class="btn btn-default"
                data-control="add-area"
                data-handler="<?= $this->getEventHandler('onAddArea') ?>"
                data-attach-loading
            ><i class="fa fa-plus"></i>&nbsp;&nbsp;<?= $prompt ? e(lang($prompt)) : '' ?></button>
            <button
                type="button"
                class="btn btn-outline-default"
                data-toggle="modal"
                data-target="#<?= $this->getId('map-modal') ?>"
            ><i class="fa fa-map"></i>&nbsp;&nbsp;<?= $mapPrompt ? e(lang($mapPrompt)) : '' ?></button>
        </div>
    </div>
</div>
