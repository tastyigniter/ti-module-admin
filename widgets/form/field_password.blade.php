@if ($this->previewMode)
    <div class="form-control-static">********</div>
@else
    <input
        type="password"
        name="{{ $field->getName() }}"
        id="{{ $field->getId() }}"
        value=""
        class="form-control"
        autocomplete="off"
        {!! $field->hasAttribute('maxlength') ? '' : 'maxlength="255"' !!}
        {!! $field->getAttributes() !!}
    />
@endif
