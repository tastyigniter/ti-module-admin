@if ($this->previewMode)
    <div class="form-control-static">{!! $value !!}</div>
@else
    <div
        class="field-richeditor size-{{ $size }}"
        data-control="rich-editor"
        data-height="{{ $size == 'small' ? 150 : 300 }}">
        <textarea
            name="{{ $name }}"
            id="{{ $this->getId('textarea') }}"
            class="form-control"
        >{!! trim($value) !!}</textarea>
    </div>
@endif
