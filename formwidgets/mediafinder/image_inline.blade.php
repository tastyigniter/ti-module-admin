<div class="media-finder">
    <div class="input-group">
        <div class="input-group-prepend">
            <i class="input-group-icon" style="width: 50px;">
                @if (!is_null($mediaItem))
                    <img
                        data-find-image
                        src="{{ $this->getMediaThumb($mediaItem) }}"
                        class="img-responsive"
                        width="24px"
                    >
                @endif
            </i>
        </div>
        <span
            class="form-control{{ (!is_null($mediaItem) AND $useAttachment) ? ' find-config-button' : '' }}"
            data-find-name>{{ $this->getMediaName($mediaItem) }}</span>
        <input
            id="{{ $field->getId() }}"
            type="hidden"
            {!! !$useAttachment ? 'name="'.$fieldName.'"' : '' !!}
            data-find-value
            value="{{ $this->getMediaPath($mediaItem) }}"
            {!! $this->previewMode ? 'disabled="disabled"' : '' !!}
        >
        <input
            type="hidden"
            value="{{ $this->getMediaIdentifier($mediaItem) }}"
            data-find-identifier
        />
        @unless ($this->previewMode)
            <div class="input-group-append">
                <button class="btn btn-outline-primary find-button{{ !is_null($mediaItem) ? ' hide' : '' }}" type="button">
                    <i class="fa fa-picture-o"></i>
                </button>
                <button
                    class="btn btn-outline-danger find-remove-button{{ !is_null($mediaItem) ? '' : ' hide' }}"
                    type="button">
                    <i class="fa fa-times-circle"></i>
                </button>
            </div>
        @endunless
    </div>
</div>
