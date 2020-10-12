// List Filter State Toggle
// Uses user cookie value to show/hide list filter bar
// todo: move to widget assets file.
$(function () {
    var $listFilterButton = $('[data-toggle="list-filter"]'),
        $listFilterTarget = $($listFilterButton.data('target')),
        listFilterStoreName = $listFilterTarget.data('storeName'),
        displayFilterPanel = Cookies.get(listFilterStoreName)

    $listFilterButton.on('click', function () {
        var $button = $(this)

        $listFilterTarget.slideToggle(function () {
            $button.button('toggle')
            if (!listFilterStoreName || !listFilterStoreName.length)
                return

            Cookies.set(listFilterStoreName, $listFilterTarget.is(':visible') ? 1 : 0)
        })
    })

    if (displayFilterPanel > 0) {
        $listFilterButton.addClass('active')
    }
})

// Submit list filter form on select change
$(function () {
    $(document).on('change', '.filter-scope select, .filter-scope input[type="checkbox"]', function (event) {
        $(event.currentTarget).closest('form').submit()
    })
})

$(function ($) {
    // List setup form sortables
    $('#lists-setup-modal-content').on('ajaxUpdate', function() {
        Sortable.create($('#lists-setup-sortable').get(0), {
            handle: '.form-check-handle',
        })
    })
})
