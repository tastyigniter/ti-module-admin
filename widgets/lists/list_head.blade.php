<tr>
    @if ($showDragHandle)
        <th class="list-action"></th>
    @endif

    @if ($showCheckboxes)
        <th class="list-action">
            <div class="custom-control custom-checkbox">
                <input
                    type="checkbox" id="{{ 'checkboxAll-'.$listId }}"
                    class="custom-control-input" onclick="$('input[name*=\'checked\']').prop('checked', this.checked)"/>
                <label class="custom-control-label" for="{{ 'checkboxAll-'.$listId }}">&nbsp;</label>
            </div>
        </th>
    @endif

    @foreach ($columns as $key => $column)
        @continue($column->type != 'button')
        <th class="list-action {{ $column->cssClass }}"></th>
    @endforeach
    @foreach ($columns as $key => $column)
        @continue($column->type == 'button')

        @if ($showSorting AND $column->sortable)
            <th
                class="list-cell-name-{{ $column->getName() }} list-cell-type-{{ $column->type }} {{ $column->cssClass }}"
                @if ($column->width) style="width: {{ $column->width }}" @endif>
                <a
                    class="sort-col"
                    data-request="{{ $this->getEventHandler('onSort') }}"
                    data-request-form="#list-form"
                    data-request-data="sort_by: '{{ $column->columnName }}'">
                    {{ $this->getHeaderValue($column) }}
                    <i class="fa fa-sort-{{ ($sortColumn == $column->columnName) ? strtoupper($sortDirection).' active' : 'ASC' }}"></i>
                </a>
            </th>
        @else
            <th
                class="list-cell-name-{{ $column->getName() }} list-cell-type-{{ $column->type }}"
                @if ($column->width) style="width: {{ $column->width }}" @endif
            >
                <span>{{ $this->getHeaderValue($column) }}</span>
            </th>
        @endif
    @endforeach

    @if ($showFilter)
        <th class="list-setup">
            <button
                type="button"
                class="btn btn-outline-default btn-sm border-none"
                title="@lang('admin::lang.button_filter')"
                data-toggle="list-filter"
                data-target=".list-filter"
            ><i class="fa fa-filter"></i></button>
        </th>
    @endif
    @if ($showSetup)
        <th class="list-setup">
            <button
                type="button"
                class="btn btn-outline-default btn-sm border-none"
                title="@lang('admin::lang.list.text_setup')"
                data-toggle="modal"
                data-target="#{{ $listId }}-setup-modal"
                data-request="{{ $this->getEventHandler('onLoadSetup') }}"
            ><i class="fa fa-sliders"></i></button>
        </th>
    @endif
</tr>
