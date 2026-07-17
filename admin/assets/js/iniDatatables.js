/* 
 * Initialization DataTables 
 * Initialize for Exports
 */


$('.dtApplicants').DataTable({
    dom: 'Bfrtip',
    searching: false,
    info: false,
    buttons: [
        {
            extend: 'print',
            exportOptions: {
                columns: [0, 2, 3, 4, 5, 6]
            },
            text: '<i class="fa fa-print fa-fw"></i> Print',
            titleAttr: 'Print',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'copy',
            exportOptions: {
                columns: [0, 2, 3, 4, 5, 6]
            },
            text: '<i class="fa fa-copy fa-fw"></i> Copy',
            titleAttr: 'Print',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'pdf',
            exportOptions: {
                columns: [0, 2, 3, 4, 5, 6]
            },
            text: '<i class="fa fa-file-pdf-o fa-fw"></i> PDF',
            titleAttr: 'PDF',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'excel',
            exportOptions: {
                columns: [0, 2, 3, 4, 5, 6]
            },
            text: '<i class="fa fa-file-excel-o fa-fw"></i> Excel',
            titleAttr: 'Excel',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'csv',
            exportOptions: {
                columns: [0, 2, 3, 4, 5, 6]
            },
            text: '<i class="fa fa-file-text-o fa-fw"></i> CSV',
            titleAttr: 'CSV',
            className: 'btn-primary mRight10'
        }

    ]
});


$('.dtEvent').DataTable({
    dom: 'Bfrtip',
    searching: false,
    info: false,
    buttons: [
        {
            extend: 'print',
            exportOptions: {
                columns: [0, 2, 3, 4, 5]
            },
            text: '<i class="fa fa-print fa-fw"></i> Print',
            titleAttr: 'Print',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'copy',
            exportOptions: {
                columns: [0, 2, 3, 4, 5]
            },
            text: '<i class="fa fa-copy fa-fw"></i> Copy',
            titleAttr: 'Copy',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'pdf',
            exportOptions: {
                columns: [0, 2, 3, 4, 5]
            },
            text: '<i class="fa fa-file-pdf-o fa-fw"></i> PDF',
            titleAttr: 'PDF',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'excel',
            exportOptions: {
                columns: [0, 2, 3, 4, 5]
            },
            text: '<i class="fa fa-file-excel-o fa-fw"></i> Excel',
            titleAttr: 'Excel',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'csv',
            exportOptions: {
                columns: [0, 2, 3, 4, 5]
            },
            text: '<i class="fa fa-file-text-o fa-fw"></i> CSV',
            titleAttr: 'CSV',
            className: 'btn-primary mRight10'
        }
    ]
});


$('.dtPrayer').DataTable({
    dom: 'Bfrtip',
    searching: false,
    info: false,
    buttons: [
        {
            extend: 'print',
            exportOptions: {
                columns: [0, 1, 2]
            },
            text: '<i class="fa fa-print fa-fw"></i> Print',
            titleAttr: 'Print',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'copy',
            exportOptions: {
                columns: [0, 1, 2]
            },
            text: '<i class="fa fa-coy fa-fw"></i> Copy',
            titleAttr: 'Copy',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'pdf',
            exportOptions: {
                columns: [0, 1, 2]
            },
            text: '<i class="fa fa-file-pdf-o fa-fw"></i> PDF',
            titleAttr: 'PDF',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'excel',
            exportOptions: {
                columns: [0, 1, 2]
            },
            text: '<i class="fa fa-file-excel-o fa-fw"></i> Excel',
            titleAttr: 'Excel',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'csv',
            exportOptions: {
                columns: [0, 1, 2]
            },
            text: '<i class="fa fa-file-text-o fa-fw"></i> CSV',
            titleAttr: 'CSV',
            className: 'btn-primary mRight10'
        }
    ]
});


$('.dtInquiry').DataTable({
    dom: "<'row'<'col-sm-12 col-md-4'B><'col-sm-12 col-md-4 inquiry-dt-date'><'col-sm-12 col-md-4'f>>rtip",
    order: [[0, 'asc']],
    buttons: [
        {
            extend: 'print',
            exportOptions: {
                columns: [0, 1, 2, 3, 4, 5]
            },
            text: '<i class="fa fa-print fa-fw"></i> Print',
            titleAttr: 'Print',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'copy',
            exportOptions: {
                columns: [0, 1, 2, 3, 4, 5]
            },
            text: '<i class="fa fa-copy fa-fw"></i> Copy',
            titleAttr: 'Copy',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'pdf',
            exportOptions: {
                columns: [0, 1, 2, 3, 4, 5]
            },
            text: '<i class="fa fa-file-pdf-o fa-fw"></i> PDF',
            titleAttr: 'PDF',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'excel',
            exportOptions: {
                columns: [0, 1, 2, 3, 4, 5]
            },
            text: '<i class="fa fa-file-excel-o fa-fw"></i> Excel',
            titleAttr: 'Excel',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'csv',
            exportOptions: {
                columns: [0, 1, 2, 3, 4, 5]
            },
            text: '<i class="fa fa-file-text-o fa-fw"></i> CSV',
            titleAttr: 'CSV',
            className: 'btn-primary mRight10'
        }
    ]
});

(function () {
    function formatDate(dateObj) {
        var y = dateObj.getFullYear();
        var m = ('0' + (dateObj.getMonth() + 1)).slice(-2);
        var d = ('0' + dateObj.getDate()).slice(-2);
        return y + '-' + m + '-' + d;
    }

    function setRangeDates(range) {
        var today = new Date();
        var from = new Date(today.getTime());
        var to = new Date(today.getTime());

        if (range === '7') {
            from.setDate(today.getDate() - 6);
        } else if (range === '30') {
            from.setDate(today.getDate() - 29);
        }

        $('#inquiry-date-from').val(formatDate(from));
        $('#inquiry-date-to').val(formatDate(to));
    }

    function syncDateInputs() {
        var range = $('#inquiry-range').val();
        var $inputs = $('#inquiry-date-inputs');
        var isCustom = range === 'custom';

        if (!isCustom) {
            setRangeDates(range);
        }

        $inputs.toggleClass('is-locked', !isCustom);
        $('#inquiry-date-from, #inquiry-date-to').prop('readonly', !isCustom);
    }

    var $filter = $('#inquiry-date-filter');
    var $slot = $('.inquiry-dt-date');
    if ($filter.length && $slot.length) {
        $filter.appendTo($slot).show();
    } else if ($filter.length) {
        $filter.insertBefore($('.dtInquiry')).show();
    }

    $(document).on('change', '#inquiry-range', function () {
        syncDateInputs();
        if ($(this).val() !== 'custom') {
            $('#inquiry-date-filter').submit();
        }
    });
})();

$(document).on('click', '.dtInquiry tbody tr.inquiry-row-link', function (e) {
    if ($(e.target).closest('.inquiry-row-actions, .tb-tnx-action, a, button, .dropdown-menu').length) {
        return;
    }
    var href = $(this).attr('data-href');
    if (href) {
        window.location.href = href;
    }
});


$('.dtMembers').DataTable({
    dom: 'Bfrtip',
    searching: false,
    info: false,
    buttons: [
        {
            extend: 'print',
            exportOptions: {
                columns: [0, 2, 3, 4, 5]
            },
            text: '<i class="fa fa-print fa-fw"></i> Print',
            titleAttr: 'Print',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'copy',
            exportOptions: {
                columns: [0, 2, 3, 4, 5]
            },
            text: '<i class="fa fa-copy fa-fw"></i> Copy',
            titleAttr: 'Copy',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'pdf',
            exportOptions: {
                columns: [0, 2, 3, 4, 5]
            },
            text: '<i class="fa fa-file-pdf-o fa-fw"></i> PDF',
            titleAttr: 'PDF',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'excel',
            exportOptions: {
                columns: [0, 2, 3, 4, 5]
            },
            text: '<i class="fa fa-file-excel-o fa-fw"></i> Excel',
            titleAttr: 'Excel',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'csv',
            exportOptions: {
                columns: [0, 2, 3, 4, 5]
            },
            text: '<i class="fa fa-file-text-o fa-fw"></i> CSV',
            titleAttr: 'CSV',
            className: 'btn-primary mRight10'
        }
    ]
});


$('.dtNotices').DataTable({
    dom: 'Bfrtip',
    searching: false,
    info: false,
    buttons: [
        {
            extend: 'print',
            exportOptions: {
                columns: [0, 1, 2]
            },
            text: '<i class="fa fa-print fa-fw"></i> Print',
            titleAttr: 'Print',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'copy',
            exportOptions: {
                columns: [0, 1, 2]
            },
            text: '<i class="fa fa-Copy fa-fw"></i> Copy',
            titleAttr: 'Copy',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'pdf',
            exportOptions: {
                columns: [0, 1, 2]
            },
            text: '<i class="fa fa-file-pdf-o fa-fw"></i> PDF',
            titleAttr: 'PDF',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'excel',
            exportOptions: {
                columns: [0, 1, 2]
            },
            text: '<i class="fa fa-file-pdf-o fa-fw"></i> Excel',
            titleAttr: 'Excel',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'csv',
            exportOptions: {
                columns: [0, 1, 2]
            },
            text: '<i class="fa fa-file-text-o fa-fw"></i> CSV',
            titleAttr: 'CSV',
            className: 'btn-primary mRight10'
        }
    ]
});


$('.dtFunds').DataTable({
    dom: 'Bfrtip',
    searching: false,
    info: false,
    sorting: false,
    paging: false,
    buttons: [
        {
            extend: 'print',
            exportOptions: {
                columns: [0, 1, 2, 3, 4]
            },
            text: '<i class="fa fa-print fa-fw"></i> Print',
            titleAttr: 'Print',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'copy',
            exportOptions: {
                columns: [0, 1, 2, 3, 4]
            },
            text: '<i class="fa fa-copy fa-fw"></i> Copy',
            titleAttr: 'Copy',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'pdf',
            exportOptions: {
                columns: [0, 1, 2, 3, 4]
            },
            text: '<i class="fa fa-file-pdf-o fa-fw"></i> PDF',
            titleAttr: 'PDF',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'excel',
            exportOptions: {
                columns: [0, 1, 2, 3, 4]
            },
            text: '<i class="fa fa-file-excel-o fa-fw"></i> Excel',
            titleAttr: 'Excel',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'csv',
            exportOptions: {
                columns: [0, 1, 2, 3, 4]
            },
            text: '<i class="fa fa-file-text-o fa-fw"></i> CSV',
            titleAttr: 'CSV',
            className: 'btn-primary mRight10'
        }
    ]
});

$('.dtFBMonth').DataTable({
    dom: 'Bfrtip',
    searching: false,
    info: false,
    sorting: false,
    buttons: [
        {
            extend: 'print',
            exportOptions: {
                columns: [0, 1, 2, 3, 4]
            },
            text: '<i class="fa fa-print fa-fw"></i> Print',
            titleAttr: 'Print',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'copy',
            exportOptions: {
                columns: [0, 1, 2, 3, 4]
            },
            text: '<i class="fa fa-copy fa-fw"></i> Copy',
            titleAttr: 'Copy',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'pdf',
            exportOptions: {
                columns: [0, 1, 2, 3, 4]
            },
            text: '<i class="fa fa-file-pdf-o fa-fw"></i> PDF',
            titleAttr: 'PDF',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'excel',
            exportOptions: {
                columns: [0, 1, 2, 3, 4]
            },
            text: '<i class="fa fa-file-excel-o fa-fw"></i> Excel',
            titleAttr: 'Excel',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'csv',
            exportOptions: {
                columns: [0, 1, 2, 3, 4]
            },
            text: '<i class="fa fa-file-text-o fa-fw"></i> CSV',
            titleAttr: 'CSV',
            className: 'btn-primary mRight10'
        }
    ]
});


$('.dtFBYear').DataTable({
    dom: 'Bfrtip',
    searching: false,
    info: false,
    sorting: false,
    paging: false,
    buttons: [
        {
            extend: 'print',
            text: '<i class="fa fa-print fa-fw"></i> Print',
            titleAttr: 'Print',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'copy',
            text: '<i class="fa fa-files-o fa-fw"></i> Copy',
            titleAttr: 'Copy',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'pdf',
            text: '<i class="fa fa-file-pdf-o fa-fw"></i> PDF',
            titleAttr: 'PDF',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'excel',
            text: '<i class="fa fa-file-excel-o fa-fw"></i> Excel',
            titleAttr: 'Excel',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'csv',
            text: '<i class="fa fa-file-text-o fa-fw"></i> CSV',
            titleAttr: 'CSV',
            className: 'btn-primary mRight10'
        }
    ]
});


$('.dtUser').DataTable({
    dom: 'Bfrtip',
    searching: false,
    info: false,
    buttons: [
        {
            extend: 'print',
            exportOptions: {
                columns: [0, 2, 3, 4, 5]
            },
            text: '<i class="fa fa-print fa-fw"></i> Print',
            titleAttr: 'Print',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'copy',
            exportOptions: {
                columns: [0, 2, 3, 4, 5]
            },
            text: '<i class="fa fa-copy fa-fw"></i> Copy',
            titleAttr: 'Copy',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'pdf',
            exportOptions: {
                columns: [0, 2, 3, 4, 5]
            },
            text: '<i class="fa fa-file-pdf-o fa-fw"></i> PDF',
            titleAttr: 'PDF',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'excel',
            exportOptions: {
                columns: [0, 2, 3, 4, 5]
            },
            text: '<i class="fa fa-file-excel-o fa-fw"></i> Excel',
            titleAttr: 'Excel',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'csv',
            exportOptions: {
                columns: [0, 2, 3, 4, 5]
            },
            text: '<i class="fa fa-file-text-o fa-fw"></i> CSV',
            titleAttr: 'CSV',
            className: 'btn-primary mRight10'
        }
    ]
});

$('.dtClan').DataTable({
    dom: 'Bfrtip',
    searching: false,
    info: false,
    buttons: [
        {
            extend: 'print',
            exportOptions: {
                columns: [0, 2, 3, 4, 5, 6]
            },
            text: '<i class="fa fa-print fa-fw"></i> Print',
            titleAttr: 'Print',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'copy',
            exportOptions: {
                columns: [0, 2, 3, 4, 5, 6]
            },
            text: '<i class="fa fa-copy fa-fw"></i> Copy',
            titleAttr: 'Print',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'pdf',
            exportOptions: {
                columns: [0, 2, 3, 4, 5, 6]
            },
            text: '<i class="fa fa-file-pdf-o fa-fw"></i> PDF',
            titleAttr: 'PDF',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'excel',
            exportOptions: {
                columns: [0, 2, 3, 4, 5, 6]
            },
            text: '<i class="fa fa-file-excel-o fa-fw"></i> Excel',
            titleAttr: 'Excel',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'csv',
            exportOptions: {
                columns: [0, 2, 3, 4, 5, 6]
            },
            text: '<i class="fa fa-file-text-o fa-fw"></i> CSV',
            titleAttr: 'CSV',
            className: 'btn-primary mRight10'
        }

    ]
});


$('.dtChorus').DataTable({
    dom: 'Bfrtip',
    searching: false,
    info: false,
    buttons: [
        {
            extend: 'print',
            exportOptions: {
                columns: [0, 2, 3, 4, 5, 6]
            },
            text: '<i class="fa fa-print fa-fw"></i> Print',
            titleAttr: 'Print',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'copy',
            exportOptions: {
                columns: [0, 2, 3, 4, 5, 6]
            },
            text: '<i class="fa fa-copy fa-fw"></i> Copy',
            titleAttr: 'Print',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'pdf',
            exportOptions: {
                columns: [0, 2, 3, 4, 5, 6]
            },
            text: '<i class="fa fa-file-pdf-o fa-fw"></i> PDF',
            titleAttr: 'PDF',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'excel',
            exportOptions: {
                columns: [0, 2, 3, 4, 5, 6]
            },
            text: '<i class="fa fa-file-excel-o fa-fw"></i> Excel',
            titleAttr: 'Excel',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'csv',
            exportOptions: {
                columns: [0, 2, 3, 4, 5, 6]
            },
            text: '<i class="fa fa-file-text-o fa-fw"></i> CSV',
            titleAttr: 'CSV',
            className: 'btn-primary mRight10'
        }

    ]
});


$('.dtStaff').DataTable({
    dom: 'Bfrtip',
    searching: false,
    info: false,
    buttons: [
        {
            extend: 'print',
            exportOptions: {
                columns: [0, 2, 3, 4, 5, 6]
            },
            text: '<i class="fa fa-print fa-fw"></i> Print',
            titleAttr: 'Print',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'copy',
            exportOptions: {
                columns: [0, 2, 3, 4, 5, 6]
            },
            text: '<i class="fa fa-copy fa-fw"></i> Copy',
            titleAttr: 'Print',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'pdf',
            exportOptions: {
                columns: [0, 2, 3, 4, 5, 6]
            },
            text: '<i class="fa fa-file-pdf-o fa-fw"></i> PDF',
            titleAttr: 'PDF',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'excel',
            exportOptions: {
                columns: [0, 2, 3, 4, 5, 6]
            },
            text: '<i class="fa fa-file-excel-o fa-fw"></i> Excel',
            titleAttr: 'Excel',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'csv',
            exportOptions: {
                columns: [0, 2, 3, 4, 5, 6]
            },
            text: '<i class="fa fa-file-text-o fa-fw"></i> CSV',
            titleAttr: 'CSV',
            className: 'btn-primary mRight10'
        }

    ]
});


$('.dtStudent').DataTable({
    dom: 'Bfrtip',
    searching: false,
    info: false,
    buttons: [
        {
            extend: 'print',
            exportOptions: {
                columns: [0, 2, 3, 4, 5, 6, 7]
            },
            text: '<i class="fa fa-print fa-fw"></i> Print',
            titleAttr: 'Print',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'copy',
            exportOptions: {
                columns: [0, 2, 3, 4, 5, 6, 7]
            },
            text: '<i class="fa fa-copy fa-fw"></i> Copy',
            titleAttr: 'Print',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'pdf',
            exportOptions: {
                columns: [0, 2, 3, 4, 5, 6, 7]
            },
            text: '<i class="fa fa-file-pdf-o fa-fw"></i> PDF',
            titleAttr: 'PDF',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'excel',
            exportOptions: {
                columns: [0, 2, 3, 4, 5, 6, 7]
            },
            text: '<i class="fa fa-file-excel-o fa-fw"></i> Excel',
            titleAttr: 'Excel',
            className: 'btn-primary mRight10'
        },
        {
            extend: 'csv',
            exportOptions: {
                columns: [0, 2, 3, 4, 5, 6, 7]
            },
            text: '<i class="fa fa-file-text-o fa-fw"></i> CSV',
            titleAttr: 'CSV',
            className: 'btn-primary mRight10'
        }

    ]
});

/* Slider table with pagination */
if ($('.sorted_slider_table').length) {
    $('.sorted_slider_table').DataTable({
        paging: true,
        pageLength: 10,
        lengthChange: true,
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
        searching: true,
        info: true,
        ordering: false
    });
}

/* Gallery table with pagination */
if ($('.sorted_gallery_table').length) {
    $('.sorted_gallery_table').DataTable({
        paging: true,
        pageLength: 10,
        lengthChange: true,
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
        searching: true,
        info: true,
        ordering: false
    });
}

/* Section table with pagination */
if ($('.sorted_table').length) {
    $('.sorted_table').DataTable({
        paging: true,
        pageLength: 10,
        lengthChange: true,
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
        searching: true,
        info: true,
        ordering: false
    });
}

/* Page table with pagination */
if ($('.sorted_page_table').length) {
    $('.sorted_page_table').DataTable({
        paging: true,
        pageLength: 10,
        lengthChange: true,
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
        searching: true,
        info: true
    });
}