(function (window, $) {
    'use strict';

    var pollTimer = null;
    var pollInFlight = false;
    var pollTick = 0;

    function getPollUrl(withMail) {
        var url = window.INQUIRY_POLL_URL || '';
        if (!url) {
            return '';
        }
        if (withMail) {
            url += (url.indexOf('?') >= 0 ? '&' : '?') + 'mail=1';
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
        }
    }

    function handlePollResponse(data) {
        if (data && typeof data.count !== 'undefined') {
            updateInquiryBadge(data.count);
        }
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
            timeout: 15000
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
