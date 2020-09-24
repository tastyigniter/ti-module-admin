{!! form_open(current_url()) !!}
<div class="modal-header">
    <h4 class="modal-title">@lang('admin::lang.dashboard.text_add_widget')</h4>
    <button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body">
    <div class="form-group">
        <label class="control-label">@lang('admin::lang.dashboard.label_widget')</label>
        <select class="form-control" name="className">
            <option value="">@lang('admin::lang.dashboard.text_select_widget')</option>
            @foreach ($widgets as $className => $widgetInfo)
                <option
                    value="{{ $className }}"
                >{{ isset($widgetInfo['label']) ? lang($widgetInfo['label']) : $className }}</option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label class="control-label">@lang('admin::lang.dashboard.label_widget_columns')</label>
        <select class="form-control" name="size">
            <option></option>
            @foreach ($gridColumns as $column => $name)
                <option
                    value="{{ $column }}"
                    @if ($column == 12) selected="selected" @endif
                >{{ $name }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="modal-footer">
    <button
        type="button"
        class="btn btn-primary"
        data-request="{{ $this->getEventHandler('onAddWidget') }}"
        data-dismiss="modal"
    >@lang('admin::lang.button_add')</button>
    <button
        type="button"
        class="btn btn-default"
        data-dismiss="modal"
    >@lang('admin::lang.button_close')</button>
</div>
{!! form_close() !!}
