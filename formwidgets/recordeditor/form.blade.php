<div class="modal-dialog modal-dialog-scrollable {{ $this->popupSize }}">
    {!! form_open([
        'id' => 'record-editor-form',
        'role' => 'form',
        'method' => $formWidget->context == 'create' ? 'POST' : 'PATCH',
        'data-request' => $this->alias.'::onSaveRecord',
        'class' => 'w-100',
    ]) !!}
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">@lang($formTitle)</h4>
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        </div>
        <input type="hidden" name="recordId" value="{{ $formRecordId }}">
        <div class="modal-body">
            <div class="form-fields p-0">
                @foreach ($formWidget->getFields() as $field)
                    {!! $formWidget->renderField($field) !!}
                @endforeach
            </div>
        </div>
        <div class="modal-footer text-right">
            <button
                type="button"
                class="btn btn-link"
                data-dismiss="modal"
            >@lang('admin::lang.button_close')</button>
            <button
                type="submit"
                class="btn btn-primary"
            >@lang('admin::lang.button_save')</button>
        </div>
    </div>
    {!! form_close() !!}
</div>
