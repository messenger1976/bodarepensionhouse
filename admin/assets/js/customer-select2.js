/**
 * Turns a customer <select> (options carrying data-name / data-email / data-phone)
 * into a searchable Select2 dropdown using the Bootstrap 5 theme.
 */
(function (window) {
    'use strict';

    function formatCustomerOption(option) {
        if (!option.id || !option.element) {
            return option.text;
        }
        var $ = window.jQuery;
        var el = option.element;
        var name = el.getAttribute('data-name') || option.text;
        var meta = [el.getAttribute('data-email'), el.getAttribute('data-phone')].filter(Boolean).join(' • ');
        var $item = $('<div class="customer-option"></div>');
        $item.append($('<div class="fw-semibold"></div>').text(name));
        if (meta) {
            $item.append($('<small class="text-muted"></small>').text(meta));
        }
        return $item;
    }

    function formatCustomerSelection(option) {
        return option.id && option.element
            ? (option.element.getAttribute('data-name') || option.text)
            : option.text;
    }

    function matchCustomer(params, data) {
        var term = String(params.term || '').trim().toLowerCase();
        if (term === '') {
            return data;
        }
        if (!data.element) {
            return null;
        }
        var haystack = [
            data.text,
            data.element.getAttribute('data-email'),
            data.element.getAttribute('data-phone')
        ].join(' ').toLowerCase();
        return haystack.indexOf(term) !== -1 ? data : null;
    }

    window.initCustomerSelect2 = function (selectEl) {
        if (!selectEl || !window.jQuery || !window.jQuery.fn.select2) {
            return;
        }
        var $select = window.jQuery(selectEl);

        $select.select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: '-- Select a customer or enter manually --',
            allowClear: true,
            templateResult: formatCustomerOption,
            templateSelection: formatCustomerSelection,
            matcher: matchCustomer,
            language: {
                noResults: function () {
                    return 'No matching customer — fill in the guest details manually';
                }
            }
        });

        // Select2 fires jQuery-only change events; re-dispatch natively so addEventListener('change') handlers run.
        $select.on('change', function (e) {
            if (!e.originalEvent) {
                this.dispatchEvent(new Event('change'));
            }
        });

        $select.on('select2:open', function () {
            var searchField = document.querySelector('.select2-container--open .select2-search__field');
            if (searchField) {
                searchField.placeholder = 'Search by name, email or phone...';
                searchField.focus();
            }
        });
    };
})(window);
