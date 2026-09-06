/**
 * Asia/Manila (Philippine Time) helpers for admin UI.
 * Naive DATETIME strings from the server are treated as Manila wall time.
 */
(function (global) {
    'use strict';

    var TZ = 'Asia/Manila';

    function pad(n) {
        return String(n).padStart(2, '0');
    }

    /** Today's calendar date in Manila as YYYY-MM-DD. */
    function todayYmd(date) {
        var d = date instanceof Date ? date : new Date();
        return new Intl.DateTimeFormat('en-CA', {
            timeZone: TZ,
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
        }).format(d);
    }

    /**
     * Format a Date using its local Y/M/D components (for booking date math).
     * Prefer this over toISOString().split('T')[0] which shifts to UTC.
     */
    function formatDateLocal(date) {
        if (!(date instanceof Date) || Number.isNaN(date.getTime())) {
            return '';
        }
        return date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate());
    }

    /** Parse server DATETIME / date as Manila when no timezone is present. */
    function parseServerDate(value) {
        if (value == null || value === '') {
            return null;
        }
        if (value instanceof Date) {
            return Number.isNaN(value.getTime()) ? null : value;
        }
        var raw = String(value).trim();
        if (!raw) {
            return null;
        }
        if (/^\d{4}-\d{2}-\d{2}$/.test(raw)) {
            var parts = raw.split('-');
            return new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]), 0, 0, 0, 0);
        }
        var normalized = raw.replace(' ', 'T');
        var hasTz = /[zZ]|[+-]\d{2}:?\d{2}$/.test(normalized);
        if (!hasTz) {
            // Treat naive timestamps as Manila wall time.
            normalized = normalized + '+08:00';
        }
        var parsed = new Date(normalized);
        return Number.isNaN(parsed.getTime()) ? null : parsed;
    }

    function formatDisplay(value, withTime) {
        var date = parseServerDate(value);
        if (!date) {
            return value == null || value === '' ? '—' : String(value);
        }
        var opts = withTime
            ? { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true }
            : { year: 'numeric', month: 'short', day: 'numeric' };
        opts.timeZone = TZ;
        return date.toLocaleString('en-US', opts);
    }

    global.BODARE_TIMEZONE = TZ;
    global.bodareTodayYmd = todayYmd;
    global.bodareFormatDateLocal = formatDateLocal;
    global.bodareParseServerDate = parseServerDate;
    global.bodareFormatManilaDate = function (value) { return formatDisplay(value, false); };
    global.bodareFormatManilaDateTime = function (value) { return formatDisplay(value, true); };

    // Back-compat aliases used across booking forms.
    if (typeof global.formatDateLocal !== 'function') {
        global.formatDateLocal = formatDateLocal;
    }
})(typeof window !== 'undefined' ? window : this);
