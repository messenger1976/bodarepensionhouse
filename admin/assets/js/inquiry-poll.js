(function (window, $) {
    'use strict';

    var pollTimer = null;
    var pollInFlight = false;
    var pollTick = 0;
    var lastListRevision = null;
    var updatedHintTimer = null;

    function getLiveRoot() {
        return document.getElementById('inquiries-live-root');
    }

    function seedListRevision() {
        if (lastListRevision !== null) {
            return;
        }
        var root = getLiveRoot();
        if (root && root.getAttribute('data-revision')) {
            lastListRevision = root.getAttribute('data-revision');
        }
    }

    function isLiveListPage() {
        var root = getLiveRoot();
        return !!(root && root.getAttribute('data-live-list') === '1');
    }

    function getPollUrl(withMail) {
        var url = window.INQUIRY_POLL_URL || '';
        if (!url) {
            return '';
        }
        if (withMail) {
            url += (url.indexOf('?') >= 0 ? '&' : '?') + 'mail=1';
        }

        var root = getLiveRoot();
        if (root && root.getAttribute('data-live-list') === '1') {
            var params = [
                'list=1',
                'range=' + encodeURIComponent(root.getAttribute('data-range') || 'today'),
                'date_from=' + encodeURIComponent(root.getAttribute('data-date-from') || ''),
                'date_to=' + encodeURIComponent(root.getAttribute('data-date-to') || '')
            ];
            var status = root.getAttribute('data-status') || '';
            if (status) {
                params.push('status=' + encodeURIComponent(status));
            }
            url += (url.indexOf('?') >= 0 ? '&' : '?') + params.join('&');
        }

        return url;
    }

    function updateInquiryBadge(count) {
        var badge = document.getElementById('inquiry-menu-badge');
        if (!badge) {
            return;
        }

        count = parseInt(count, 10) || 0;
        if (count > 0) {
            badge.textContent = String(count);
            badge.style.display = '';
        } else {
            badge.style.display = 'none';
            badge.textContent = '';
        }
    }

    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function limitText(value, max) {
        var text = String(value == null ? '' : value);
        if (text.length <= max) {
            return text;
        }
        return text.substring(0, Math.max(0, max - 3)).replace(/\s+\S*$/, '') + '...';
    }

    function statusBadgeClass(status) {
        if (status === 'new') return 'danger';
        if (status === 'guest_replied') return 'primary';
        if (status === 'read') return 'warning';
        if (status === 'replied') return 'success';
        if (status === 'closed') return 'info';
        return 'secondary';
    }

    function statusLabel(status) {
        return String(status || '')
            .replace(/_/g, ' ')
            .replace(/\b\w/g, function (ch) { return ch.toUpperCase(); });
    }

    function canDelete() {
        var root = getLiveRoot();
        return !!(root && root.getAttribute('data-can-delete') === '1');
    }

    function buildTableRow(item, index) {
        var bold = (item.status === 'new' || item.status === 'guest_replied');
        var badge = statusBadgeClass(item.status);
        var deleteBtn = canDelete()
            ? '<a href="' + escapeHtml(item.delete_url) + '" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm(\'Delete this inquiry permanently?\');"><i class="bi bi-trash"></i></a>'
            : '';

        return '' +
            '<tr class="inquiry-row-link" data-href="' + escapeHtml(item.url) + '" style="cursor:pointer;' + (bold ? 'font-weight:600;' : '') + '">' +
                '<td>' + index + '</td>' +
                '<td>' + escapeHtml(item.name) + '</td>' +
                '<td>' + escapeHtml(item.email) + '</td>' +
                '<td>' + escapeHtml(limitText(item.subject, 40)) + '</td>' +
                '<td><span class="badge bg-' + badge + '">' + escapeHtml(statusLabel(item.status)) + '</span></td>' +
                '<td>' + escapeHtml(item.date_display) + '</td>' +
                '<td class="inquiry-row-actions" onclick="event.stopPropagation();">' +
                    '<a href="' + escapeHtml(item.url) + '" class="btn btn-sm btn-primary" title="View"><i class="bi bi-eye"></i></a> ' +
                    deleteBtn +
                '</td>' +
            '</tr>';
    }

    function buildMobileCard(item) {
        var bold = (item.status === 'new' || item.status === 'guest_replied');
        var badge = statusBadgeClass(item.status);
        var deleteBtn = canDelete()
            ? '<a href="' + escapeHtml(item.delete_url) + '" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm(\'Delete this inquiry permanently?\');"><i class="bi bi-trash"></i> Delete</a>'
            : '';

        return '' +
            '<div class="mob-list-card inquiry-row-link" data-href="' + escapeHtml(item.url) + '" style="cursor:pointer;' + (bold ? 'font-weight:600;' : '') + '">' +
                '<div class="mob-list-card-header">' +
                    '<div class="min-w-0 flex-grow-1">' +
                        '<div class="mob-list-card-title">' + escapeHtml(item.subject) + '</div>' +
                        '<div class="mob-list-card-meta">' + escapeHtml(item.name) + ' &middot; ' + escapeHtml(item.email) + '</div>' +
                    '</div>' +
                    '<span class="badge bg-' + badge + '">' + escapeHtml(statusLabel(item.status)) + '</span>' +
                '</div>' +
                '<div class="mob-list-card-meta"><i class="bi bi-clock"></i> ' + escapeHtml(item.date_display) + '</div>' +
                '<div class="mob-list-card-actions inquiry-row-actions" onclick="event.stopPropagation();">' +
                    '<a href="' + escapeHtml(item.url) + '" class="btn btn-sm btn-primary" title="View"><i class="bi bi-eye"></i> View</a> ' +
                    deleteBtn +
                '</div>' +
            '</div>';
    }

    function destroyInquiryDataTable() {
        if (!$ || !$.fn || !$.fn.DataTable) {
            return null;
        }
        var $table = $('#inquiriesTable');
        if ($table.length && $.fn.DataTable.isDataTable($table)) {
            return $table.DataTable();
        }
        return null;
    }

    function reinitInquiryDataTable() {
        if (!$ || !$.fn || !$.fn.DataTable) {
            return;
        }
        var $table = $('#inquiriesTable');
        if (!$table.length || $.fn.DataTable.isDataTable($table)) {
            return;
        }

        $table.DataTable({
            dom: "<'row'<'col-sm-12 col-md-4'B><'col-sm-12 col-md-4 inquiry-dt-date'><'col-sm-12 col-md-4'f>>rtip",
            order: [[0, 'asc']],
            buttons: [
                { extend: 'print', exportOptions: { columns: [0, 1, 2, 3, 4, 5] }, text: '<i class="fa fa-print fa-fw"></i> Print', titleAttr: 'Print', className: 'btn-primary mRight10' },
                { extend: 'copy', exportOptions: { columns: [0, 1, 2, 3, 4, 5] }, text: '<i class="fa fa-copy fa-fw"></i> Copy', titleAttr: 'Copy', className: 'btn-primary mRight10' },
                { extend: 'pdf', exportOptions: { columns: [0, 1, 2, 3, 4, 5] }, text: '<i class="fa fa-file-pdf-o fa-fw"></i> PDF', titleAttr: 'PDF', className: 'btn-primary mRight10' },
                { extend: 'excel', exportOptions: { columns: [0, 1, 2, 3, 4, 5] }, text: '<i class="fa fa-file-excel-o fa-fw"></i> Excel', titleAttr: 'Excel', className: 'btn-primary mRight10' },
                { extend: 'csv', exportOptions: { columns: [0, 1, 2, 3, 4, 5] }, text: '<i class="fa fa-file-text-o fa-fw"></i> CSV', titleAttr: 'CSV', className: 'btn-primary mRight10' }
            ]
        });

        var $filter = $('#inquiry-date-filter');
        var $slot = $('.inquiry-dt-date');
        if ($filter.length && $slot.length) {
            $filter.appendTo($slot).show();
        }
    }

    function refreshInquiryTableRows(inquiries) {
        var dt = destroyInquiryDataTable();
        var htmlRows = inquiries.length
            ? inquiries.map(function (item, idx) { return buildTableRow(item, idx + 1); }).join('')
            : '<tr><td colspan="7" class="text-center text-muted">No inquiries found</td></tr>';

        if (dt) {
            dt.clear();
            if (inquiries.length) {
                inquiries.forEach(function (item, idx) {
                    dt.row.add($(buildTableRow(item, idx + 1)));
                });
            }
            dt.draw(false);
            return;
        }

        var tbody = document.getElementById('inquiries-live-tbody');
        if (tbody) {
            tbody.innerHTML = htmlRows;
        }
        reinitInquiryDataTable();
    }

    function bindRowClicks() {
        var cards = document.querySelectorAll('#inquiries-live-cards .inquiry-row-link');
        Array.prototype.forEach.call(cards, function (row) {
            row.onclick = function () {
                var href = row.getAttribute('data-href');
                if (href) {
                    window.location = href;
                }
            };
        });
    }

    function showUpdatedHint(message) {
        var hint = document.getElementById('inquiries-live-updated');
        if (!hint) {
            return;
        }
        hint.textContent = message || 'Updated just now';
        hint.style.display = '';
        if (updatedHintTimer) {
            clearTimeout(updatedHintTimer);
        }
        updatedHintTimer = setTimeout(function () {
            hint.style.display = 'none';
        }, 4000);
    }

    function updateStatusCounts(counts) {
        if (!counts) {
            return;
        }
        Object.keys(counts).forEach(function (key) {
            var nodes = document.querySelectorAll('#inquiries-status-chips [data-count="' + key + '"]');
            Array.prototype.forEach.call(nodes, function (node) {
                node.textContent = String(parseInt(counts[key], 10) || 0);
            });
        });

        var total = document.getElementById('inquiries-live-count');
        if (total && typeof counts.all !== 'undefined') {
            total.textContent = String(parseInt(counts.all, 10) || 0);
        }
    }

    function refreshInquiryList(data) {
        if (!isLiveListPage() || !data || !data.list) {
            return;
        }

        seedListRevision();

        if (lastListRevision === null) {
            lastListRevision = data.revision || '';
            return;
        }

        if (data.revision && data.revision === lastListRevision) {
            return;
        }

        lastListRevision = data.revision || lastListRevision;

        var root = getLiveRoot();
        if (root && data.revision) {
            root.setAttribute('data-revision', data.revision);
        }

        var inquiries = Array.isArray(data.inquiries) ? data.inquiries : [];
        var cards = document.getElementById('inquiries-live-cards');
        var latest = document.getElementById('inquiries-live-latest');

        refreshInquiryTableRows(inquiries);

        if (cards) {
            if (!inquiries.length) {
                cards.innerHTML = '<div class="mob-list-card text-center text-muted py-4">No inquiries found</div>';
            } else {
                cards.innerHTML = inquiries.map(buildMobileCard).join('');
            }
        }

        if (latest && data.latest_label) {
            latest.textContent = data.latest_label;
        }

        updateStatusCounts(data.counts);
        bindRowClicks();

        var imported = parseInt(data.imported, 10) || 0;
        if (imported > 0) {
            showUpdatedHint(imported === 1 ? '1 new email reply imported' : (imported + ' new email replies imported'));
        } else {
            showUpdatedHint('List updated');
        }
    }

    function handlePollResponse(data) {
        if (data && typeof data.count !== 'undefined') {
            updateInquiryBadge(data.count);
        }
        refreshInquiryList(data);
    }

    function pollWithFetch(url) {
        return fetch(url, {
            method: 'GET',
            credentials: 'same-origin',
            cache: 'no-store',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(function (response) {
            if (!response.ok) {
                throw new Error('Poll failed');
            }
            return response.json();
        }).then(handlePollResponse).catch(function () {
            /* silent - keep UI responsive */
        });
    }

    function pollWithJquery(url) {
        return $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            cache: false,
            timeout: 20000
        }).done(handlePollResponse).fail(function () {
            /* silent - keep UI responsive */
        });
    }

    function pollInquiries() {
        if (pollInFlight || !document.getElementById('inquiry-menu-badge')) {
            return;
        }

        pollTick += 1;
        var checkMail = (pollTick % 6 === 0);
        var url = getPollUrl(checkMail);
        if (!url) {
            return;
        }

        pollInFlight = true;
        if ($ && $.ajax) {
            pollWithJquery(url).always(function () {
                pollInFlight = false;
            });
            return;
        }

        pollWithFetch(url).finally(function () {
            pollInFlight = false;
        });
    }

    function startInquiryPolling() {
        if (!document.getElementById('inquiry-menu-badge') || !getPollUrl(false)) {
            return;
        }

        seedListRevision();
        pollInquiries();
        if (pollTimer) {
            clearInterval(pollTimer);
        }
        pollTimer = setInterval(pollInquiries, 5000);
    }

    if ($) {
        $(startInquiryPolling);
    } else if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', startInquiryPolling);
    } else {
        startInquiryPolling();
    }

    window.addEventListener('beforeunload', function () {
        if (pollTimer) {
            clearInterval(pollTimer);
        }
    });
})(window, window.jQuery);
