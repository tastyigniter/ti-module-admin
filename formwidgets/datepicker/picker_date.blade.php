<div class="input-group">
    <input
        type="text"
        id="{{ $this->getId('date') }}"
        class="form-control"
        autocomplete="off"
        value="{{ $value ? $value->format($dateFormat) : null }}"
        {!! $field->getAttributes() !!}
        {!! $this->previewMode ? 'readonly="readonly"' : '' !!}
        data-control="datepicker"
        @if ($startDate) data-start-date="{{ $startDate }}" @endif
        @if ($endDate) data-end-date="{{ $endDate }}" @endif
        @if ($datesDisabled) data-dates-disabled="{{ $datesDisabled }}" @endif
        data-format="{{ $datePickerFormat }}"
    />
    <input
        type="hidden"
        name="{{ $field->getName() }}"
        value="{{ $value ? $value->format($dateFormat) : null }}"
        data-datepicker-value
    />
    <div class="input-group-append">
        <span class="input-group-icon"><i class="fa fa-calendar-o"></i></span>
    </div>
</div>
