<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Shared bulk-delete helper: "select all" + "Delete Selected" counter/confirm.
 *
 * Include this once per list view (after the table/list markup) and pass:
 *   $bd_form_id        string  id of the <form> that wraps the list rows
 *   $bd_checkbox_class string  class applied to every row checkbox
 *   $bd_button_id      string  id of the "Delete Selected" submit button
 *   $bd_label          string  human label used in the confirm text, e.g. "invoice"
 *
 * The header/mobile "select all" checkbox(es) must carry:
 *   data-bd-select-all="<form id>"
 * The "Delete Selected" button must carry the form attribute:
 *   form="<form id>"  (so it can live outside the form)
 */
$bd_form_id        = isset($bd_form_id) ? $bd_form_id : 'batch-form';
$bd_checkbox_class = isset($bd_checkbox_class) ? $bd_checkbox_class : 'batch-row-checkbox';
$bd_button_id      = isset($bd_button_id) ? $bd_button_id : 'batch-delete-btn';
$bd_label          = isset($bd_label) ? $bd_label : 'record';
?>
<script>
(function () {
    var formId = <?php echo json_encode($bd_form_id); ?>;
    var boxClass = <?php echo json_encode($bd_checkbox_class); ?>;
    var buttonId = <?php echo json_encode($bd_button_id); ?>;
    var label = <?php echo json_encode($bd_label); ?>;

    function isVisible(el) {
        return !!(el && (el.offsetWidth || el.offsetHeight || el.getClientRects().length));
    }

    function init() {
        var form = document.getElementById(formId);
        if (!form) { return; }

        var allBoxes = Array.prototype.slice.call(form.querySelectorAll('.' + boxClass));
        var selectAlls = Array.prototype.slice.call(
            document.querySelectorAll('[data-bd-select-all="' + formId + '"]')
        );
        var button = document.getElementById(buttonId);
        var allowSubmit = false;

        // Only boxes currently attached to the form will actually be submitted
        // (DataTables detaches rows from other pages).
        function activeBoxes() {
            return allBoxes.filter(function (cb) { return form.contains(cb); });
        }
        function visibleBoxes() {
            return activeBoxes().filter(isVisible);
        }
        function selectedCount() {
            return activeBoxes().filter(function (cb) { return cb.checked; }).length;
        }

        function updateState() {
            var selected = selectedCount();
            var visible = visibleBoxes();
            var visibleSelected = visible.filter(function (cb) { return cb.checked; }).length;

            if (button) {
                button.disabled = selected === 0;
                var counter = button.querySelector('[data-bd-count]');
                if (counter) {
                    counter.textContent = selected > 0 ? ' (' + selected + ')' : '';
                }
            }
            selectAlls.forEach(function (sa) {
                sa.checked = visible.length > 0 && visibleSelected === visible.length;
                sa.indeterminate = visibleSelected > 0 && visibleSelected < visible.length;
            });
        }

        selectAlls.forEach(function (sa) {
            sa.addEventListener('change', function () {
                visibleBoxes().forEach(function (cb) { cb.checked = sa.checked; });
                updateState();
            });
        });

        allBoxes.forEach(function (cb) {
            cb.addEventListener('change', updateState);
        });

        // DataTables (search / page-length controls) renders inside the wrapped
        // form. Block Enter-key implicit submits from those inputs so only the
        // "Delete Selected" button can submit this form.
        form.addEventListener('keydown', function (e) {
            if (e.key !== 'Enter' || !e.target || e.target.tagName !== 'INPUT') {
                return;
            }
            var type = (e.target.type || '').toLowerCase();
            if (type === 'checkbox' || type === 'radio' || type === 'submit') {
                return;
            }
            e.preventDefault();
        });

        form.addEventListener('submit', function (e) {
            var selected = selectedCount();
            if (selected === 0) {
                e.preventDefault();
                return;
            }

            // Already confirmed — let the resubmission go through.
            if (allowSubmit) {
                return;
            }

            e.preventDefault();

            var message = 'Delete ' + selected + ' selected ' + label + '(s)? This cannot be undone.';
            var confirmed = (typeof window.swalConfirm === 'function')
                ? window.swalConfirm(message, { title: 'Confirm Delete', confirmText: 'Delete' })
                : Promise.resolve(window.confirm(message));

            confirmed.then(function (ok) {
                if (!ok) {
                    return;
                }
                allowSubmit = true;
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                } else {
                    HTMLFormElement.prototype.submit.call(form);
                }
            });
        });

        updateState();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>
