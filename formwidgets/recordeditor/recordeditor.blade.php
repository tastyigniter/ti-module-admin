@unless ($this->previewMode)
    <div
        id="{{ $this->getId() }}"
        class="control-recordeditor"
        data-control="record-editor"
        data-alias="{{ $this->alias }}"
    >
        <div
            class="input-group" data-toggle="modal"
            data-target="#{{ $this->getId('form-modal') }}"
        >
            @if ($addonLeft)
                <div class="input-group-prepend">{{ $addonLeft }}</div>
            @endif
            <select
                id="{{ $field->getId() }}"
                name="{{ $field->getName() }}"
                class="form-control"
                data-control="choose-record"
                {!! $field->getAttributes() !!}
            >
                @if ($fieldPlaceholder = $field->placeholder ?: $this->emptyOption)
                    <option value="0">@lang($fieldPlaceholder)</option>
                @endif
                @foreach ($fieldOptions as $value => $option)
                    @php if (!is_array($option)) $option = [$option] @endphp
                    <option
                        {!! $value == $field->value ? 'selected="selected"' : '' !!}
                        @isset($option[1]) data-{{ strpos($option[1], '.') ? 'image' : 'icon' }}="{{ $option[1] }}" @endisset
                        value="{{ $value }}"
                    >{{ is_lang_key($option[0]) ? lang($option[0]) : $option[0] }}</option>
                @endforeach
            </select>
            <div class="input-group-append ml-1">
                @if ($addonRight)
                    {!! $addonRight !!}
                @endif
                <button
                    type="button"
                    class="btn btn-outline-default"
                    data-control="edit-record"
                    {!! ($this->previewMode) ? 'disabled="disabled"' : '' !!}
                ><i class="fa fa-pencil"></i>&nbsp;&nbsp;@lang($editLabel)&nbsp;@lang($this->formName)</button>
                <button
                    type="button"
                    class="btn btn-outline-danger"
                    title="{{ lang($deleteLabel).' '.lang($this->formName) }}"
                    data-control="delete-record"
                    data-confirm-message="@lang('admin::lang.alert_warning_confirm')"
                    {!! ($this->previewMode) ? 'disabled="disabled"' : '' !!}
                ><i class="fa fa-trash"></i></button>
                <button
                    type="button"
                    class="btn btn-outline-default"
                    data-control="create-record"
                    {!! ($this->previewMode) ? 'disabled="disabled"' : '' !!}
                ><i class="fa fa-plus"></i>&nbsp;&nbsp;@lang($addLabel)&nbsp;@lang($this->formName)</button>
            </div>
        </div>
    </div>
@endunless
