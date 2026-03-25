/**
 * OneX India - Shared Utility Functions
 * Merged from OneX Turkey with India-specific modifications
 * Last Updated: 2026-03-24
 */

// ============================================================================
// URL PARAMETER FUNCTIONS
// ============================================================================

/**
 * Get URL parameters as an object
 * @param {boolean} decoded - Whether to decode URI components
 * @returns {object} Object containing all URL parameters
 */
function getUrlParameters(decoded = true) {
    return Object.assign({}, ...window.location.search.substring(1).split('&').map(param => {
        const key = param.split('=')[0];
        const val = param.split('=')[1];
        return {[key]: (decoded) ? decodeURIComponent(val) : val}
    }));
}

/**
 * Get all URL parameters (alias for getUrlParameters)
 * @param {boolean} decoded - Whether to decode URI components
 * @returns {object} Object containing all URL parameters
 */
function getAllUrlParameters(decoded = true) {
    return Object.assign({}, ...window.location.search.substring(1).split('&').map(param => {
        const key = param.split('=')[0];
        const val = param.split('=')[1];
        return {[key]: (decoded) ? decodeURIComponent(val) : val}
    }));
}

/**
 * Get a specific URL parameter by key
 * @param {string} sParam - Parameter name to retrieve
 * @returns {string|boolean} Parameter value or false if not found
 */
if (typeof getUrlParameter === "undefined") {
    var getUrlParameter = function getUrlParameter(sParam) {
        var sPageURL = window.location.search.substring(1),
            sURLVariables = sPageURL.split('&'),
            sParameterName,
            i;

        for (i = 0; i < sURLVariables.length; i++) {
            sParameterName = sURLVariables[i].split('=');

            if (sParameterName[0] === sParam) {
                return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
            }
        }
        return false;
    };
}

/**
 * Set or update a URL parameter without page reload
 * @param {string} key - Parameter key
 * @param {string} val - Parameter value
 */
if (typeof setUrlParam === "undefined") {
    var setUrlParam = function (key, val){
        let url = new URL(window.location);
        (url.searchParams.has(key) ? url.searchParams.set(key, val) : url.searchParams.append(key, val));
        url = url.toString();
        history.pushState({}, null, url);
    }
}

// ============================================================================
// VALIDATION & UTILITY FUNCTIONS
// ============================================================================

/**
 * Check if variable is set, not null, and not empty
 * @param {*} variable - Variable to check
 * @returns {boolean} True if variable is set and not empty
 */
var isset = function (variable) {
    return typeof (variable) !== "undefined" && variable !== null && variable !== '';
}

/**
 * Sanitize HTML to prevent XSS attacks
 * @param {string} str - String to sanitize
 * @returns {string} Sanitized string
 */
function sanitizeHTML(str) {
    if (typeof str !== 'string') {
        return '';
    }
    return str.replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

/**
 * Clear input field by ID and trigger change event
 * @param {string} inputId - ID of input element
 */
if (typeof clearInputById === "undefined") {
    var clearInputById = function (inputId) {
        $(`#${inputId}`).val('').trigger('change');
    }
}

/**
 * Get page name from URL
 * @param {string} url - URL to parse (defaults to current page)
 * @returns {string} Page name without extension
 */
function getPageName(url = $(location).attr('href')) {
    var index = url.lastIndexOf("/") + 1;
    var filenameWithExtension = url.substr(index);
    var filename = filenameWithExtension.split(".")[0];
    return filename;
}

// ============================================================================
// LOGGING FUNCTIONS
// ============================================================================

/**
 * Save log entry to database
 * Requires `/js/jquery.min.js` to run
 * @param {string} table - Table name for logging
 * @param {string} what - Log message/action
 * @returns {object} jQuery AJAX promise
 */
async function saveLog(table, what) {
    return $.ajax({
        url: '/shared/api/logservice.php',
        type: 'POST',
        data: {
            table: table,
            what: what,
        },
        dataType: 'html'
    });
}

// ============================================================================
// NOTIFICATION FUNCTIONS
// ============================================================================

/**
 * Fire jQuery Toast notification (English version)
 * Requires `/js/jquery.min.js`, `/js/jquery.toast.min.js` and `/css/jquery.toast.min.css`
 * @param {string} type - Notification type: 'success', 'error', 'warning', 'info'
 * @param {string} text - Notification message
 * @param {boolean|number} disableHiding - Auto-hide duration in ms (false = use default)
 */
function fireJqueryToastr(type = "success", text = "", disableHiding = false) {
    const hideAfterVal = (disableHiding) ? disableHiding : 10000;

    if (!$)
        throw new Error("Jquery is not defined")
    if (!$.toast)
        throw new Error("/js/Notification/js/jquery.toast.min.js is not imported");

    if (type === "error") {
        $.toast({
            heading: 'Error',
            text: text,
            icon: 'error',
            position: 'top-right',
            bgColor: 'red',
            loaderBg: 'brown',
            hideAfter: hideAfterVal,
            stack: false
        })
    } else if (type === "success") {
        $.toast({
            heading: 'Success',
            text: text,
            icon: 'success',
            position: 'top-right',
            bgColor: 'green',
            loaderBg: 'yellow',
            hideAfter: 5000,
            stack: false
        })
    } else if (type === "warning") {
        $.toast({
            heading: 'Warning',
            text: text,
            icon: 'warning',
            position: 'top-right',
            bgColor: '#b3883e',
            loaderBg: 'yellow',
            hideAfter: hideAfterVal,
            stack: false
        })
    }
}

/**
 * Fire Toast notification (Localized for India - English)
 * Requires `/js/jquery.min.js` and `/js/jquery.toast.min.js`
 * @param {string} type - Notification type: 'success', 'error', 'warning', 'info'
 * @param {string} text - Notification message
 * @param {boolean|number} disableHiding - Auto-hide duration in ms
 */
function fireToastr(type = "success", text = "", disableHiding = false) {
    const hideAfterVal = (disableHiding) ? disableHiding : 10000;

    if (!$)
        throw new Error("Jquery is not defined")
    if (!$.toast)
        throw new Error("/js/jquery.toast.min.js is not imported");

    if (type === "error") {
        $.toast({
            heading: 'Error',
            text: text,
            icon: 'error',
            position: 'top-right',
            bgColor: '#FF4444',
            loaderBg: '#CC0000',
            hideAfter: hideAfterVal,
            stack: false
        })
    } else if (type === "success") {
        $.toast({
            heading: 'Success',
            text: text,
            icon: 'success',
            position: 'top-right',
            bgColor: '#00C851',
            loaderBg: '#007E33',
            hideAfter: 5000,
            stack: false
        })
    } else if (type === "warning") {
        $.toast({
            heading: 'Warning',
            text: text,
            icon: 'warning',
            position: 'top-right',
            bgColor: '#FFBB33',
            loaderBg: '#FF8800',
            hideAfter: hideAfterVal,
            stack: false
        })
    } else if (type === "info") {
        $.toast({
            heading: 'Info',
            text: text,
            icon: 'info',
            position: 'top-right',
            bgColor: '#33B5E5',
            loaderBg: '#0099CC',
            hideAfter: 5000,
            stack: false
        })
    }
}

/**
 * Fire SweetAlert2 notification with custom styling
 * Requires SweetAlert2 library
 * @param {string} icon - Icon type: 'success', 'error', 'warning', 'info', 'question'
 * @param {string} title - Alert title
 * @param {string} text - Alert message
 * @param {object} errorObj - Error object (optional, for auto-parsing)
 * @param {boolean} contactMsg - Show contact message (default: true)
 * @param {boolean} allowOutsideClick - Allow clicking outside to close (default: true)
 * @param {boolean} showConfirmButton - Show confirm button (default: true)
 * @param {number|boolean} timer - Auto-close timer in ms (default: false)
 */
const throwFireSwal = function (icon, title = null, text = null, errorObj = null, contactMsg = true, allowOutsideClick = true, showConfirmButton = true, timer = false){
    let titleVar = title ?? null;
    let textVal = text ?? null;
    
    if(!titleVar && !textVal){
        if(errorObj['responseJSON']){
            titleVar = `Failed (Code: ${errorObj['responseJSON']['code']})`
            textVal = errorObj['responseJSON']['message'];
        }else{
            titleVar = "Failed! "
            if(errorObj['responseText']){
                try {
                    textVal = errorObj['responseText'].split("DbManager")[1].slice(0,77) + "...";
                } catch (e) {
                    textVal = errorObj['responseText']
                }
            }else{
                textVal = "Failed! Please contact with DGT Team!";
            }
        }
    }

    Swal.fire({
        position: icon == "error" ? 'center' : "top-right",
        icon: icon,
        title: titleVar + (contactMsg ? " Please contact with Support Team!" : ""),
        text: textVal,
        width:'450px',
        confirmButtonText: 'OK',
        confirmButtonColor: '#35b142',
        cancelButtonText: 'Cancel',
        timer: timer,
        cancelButtonColor: '#db2828',
        allowOutsideClick: allowOutsideClick,
        showConfirmButton: showConfirmButton,
    })
}

// ============================================================================
// DATA MANIPULATION FUNCTIONS
// ============================================================================

/**
 * Unserialize PHP serialized data
 * @param {string} data - Serialized data string
 * @returns {*} Unserialized data
 */
function unserialize(data) {
    var error = function (type, msg, filename, line) {
        throw new window[type](msg, filename, line);
    };
    var read_until = function (data, offset, stopchr) {
        var buf = [];
        var chr = data.slice(offset, offset + 1);
        var i = 2;
        while (chr != stopchr) {
            if ((i + offset) > data.length) {
                error('Error', 'Invalid');
            }
            buf.push(chr);
            chr = data.slice(offset + (i - 1), offset + i);
            i += 1;
        }
        return [buf.length, buf.join('')];
    };
    var read_chrs = function (data, offset, length) {
        buf = [];
        for (var i = 0; i < length; i++) {
            var chr = data.slice(offset + (i - 1), offset + i);
            buf.push(chr);
        }
        return [buf.length, buf.join('')];
    };
    var _unserialize = function (data, offset) {
        if (!offset) offset = 0;
        var buf = [];
        var dtype = (data.slice(offset, offset + 1)).toLowerCase();

        var dataoffset = offset + 2;
        var typeconvert = new Function('x', 'return x');
        var chrs = 0;
        var datalength = 0;

        switch (dtype) {
            case "i":
                typeconvert = new Function('x', 'return parseInt(x)');
                var readData = read_until(data, dataoffset, ';');
                var chrs = readData[0];
                var readdata = readData[1];
                dataoffset += chrs + 1;
                break;
            case "b":
                typeconvert = new Function('x', 'return (parseInt(x) == 1)');
                var readData = read_until(data, dataoffset, ';');
                var chrs = readData[0];
                var readdata = readData[1];
                dataoffset += chrs + 1;
                break;
            case "d":
                typeconvert = new Function('x', 'return parseFloat(x)');
                var readData = read_until(data, dataoffset, ';');
                var chrs = readData[0];
                var readdata = readData[1];
                dataoffset += chrs + 1;
                break;
            case "n":
                readdata = null;
                break;
            case "s":
                var ccount = read_until(data, dataoffset, ':');
                var chrs = ccount[0];
                var stringlength = ccount[1];
                dataoffset += chrs + 2;

                var readData = read_chrs(data, dataoffset + 1, parseInt(stringlength));
                var chrs = readData[0];
                var readdata = readData[1];
                dataoffset += chrs + 2;
                if (chrs != parseInt(stringlength) && chrs != readdata.length) {
                    error('SyntaxError', 'String length mismatch');
                }
                break;
            case "a":
                var readdata = {};

                var keyandchrs = read_until(data, dataoffset, ':');
                var chrs = keyandchrs[0];
                var keys = keyandchrs[1];
                dataoffset += chrs + 2;

                for (var i = 0; i < parseInt(keys); i++) {
                    var kprops = _unserialize(data, dataoffset);
                    var kchrs = kprops[1];
                    var key = kprops[2];
                    dataoffset += kchrs;

                    var vprops = _unserialize(data, dataoffset);
                    var vchrs = vprops[1];
                    var value = vprops[2];
                    dataoffset += vchrs;

                    readdata[key] = value;
                }

                dataoffset += 1;
                break;
            default:
                error('SyntaxError', 'Unknown / Unhandled data type(s): ' + dtype);
                break;
        }
        return [dtype, dataoffset - offset, typeconvert(readdata)];
    };
    return _unserialize(data, 0)[2];
}

// ============================================================================
// DATE PICKER FUNCTIONS
// ============================================================================

/**
 * Initialize Date Picker with default settings
 * Requires: moment.js, jquery.daterangepicker
 * @param {string} element_id - Element ID for date picker
 * @param {number} min_year - Minimum year for selection
 */
function initializeDatePicker(element_id = "reportrange",
                              min_year = Number(moment().subtract(2, "year").format("YYYY")))
{
    initDateRangeCommon({
        elementId: element_id,
        minYear: min_year,
        onChange: ({ startISO, endISO }) => {
            console.log("Date selection changed:", startISO, endISO);
            // Trigger fetch/refresh here if needed
        }
    });
}

/**
 * Initialize Date Range Picker with common configuration
 * Requires: jQuery, moment.js, daterangepicker plugin
 * @param {object} opts - Configuration options
 * @returns {object} jQuery element
 */
function initDateRangeCommon(opts = {}) {
    if (typeof $ === "undefined") throw new Error("jQuery is not defined.");
    if (typeof moment === "undefined") throw new Error("Moment is not defined.");
    if (!$.fn || !$.fn.daterangepicker) throw new Error("Date Range Picker is not defined.");

    const {
        elementId     = "reportrange",
        minYear       = 2021,
        fyStartMonth  = 4,  // Changed from 9 (Sept) to 4 (April) for India FY
        fyStartDay    = 1,
        start         = moment().startOf("month"),
        end           = moment(),
        displayFormat = "DD-MM-YYYY",
        setGlobals    = true,
        ranges        = {},
        onChange
    } = opts;

    const $el = $("#" + elementId);
    if ($el.length === 0) throw new Error(`#${elementId} not found`);

    // FY calculations (India: 1 April to 31 March)
    const now = moment();
    const fyStartThis = now.month() >= fyStartMonth
        ? moment([now.year(), fyStartMonth, fyStartDay])
        : moment([now.year() - 1, fyStartMonth, fyStartDay]);
    const fyStartLast = fyStartThis.clone().subtract(1, "year");

    const defaultRanges = {
        "Today": [moment(), moment()],
        "Yesterday": [moment().subtract(1, "days"), moment().subtract(1, "days")],
        "Last 7 Days": [moment().subtract(6, "days"), moment()],
        "Last 30 Days": [moment().subtract(29, "days"), moment()],
        "This Month": [moment().startOf("month"), moment().endOf("month")],
        "Last Month": [
            moment().subtract(1, "month").startOf("month"),
            moment().subtract(1, "month").endOf("month")
        ],
        "Last Year": [
            moment().subtract(1, "year").startOf("year"),
            moment().subtract(1, "year").endOf("year")
        ],
        "Last 1 Year": [moment().subtract(365, "days"), moment()],
        "This Fiscal Year": [fyStartThis, now],
        "Last Fiscal Year": [fyStartLast, fyStartThis]
    };

    // Update UI label and global variables / callback
    function applySelection(startM, endM, picker) {
        // Label
        const label = `${startM.format(displayFormat)} / ${endM.format(displayFormat)}`;
        $el.find("span").html(label);

        // Global variables (for backward compatibility)
        if (setGlobals) {
            window.start_date  = startM.format("YYYY-MM-DD");
            window.finish_date = endM.format("YYYY-MM-DD");
        }

        // User callback
        if (typeof onChange === "function") {
            onChange({
                start: startM.clone(),
                end: endM.clone(),
                startISO: startM.format("YYYY-MM-DD"),
                endISO: endM.format("YYYY-MM-DD"),
                picker
            });
        }
    }

    // Initialize daterangepicker
    $el.daterangepicker({
        startDate: start,
        endDate: end,
        showDropdowns: true,
        minYear,
        maxYear: parseInt(moment().format("YYYY"), 10) + 1,
        alwaysShowCalendars: true,
        autoApply: true,
        ranges: { ...defaultRanges, ...ranges }
    }, function (startM, endM, label) {
        applySelection(startM, endM);
    });

    // Set label and variables on initial load
    applySelection(start, end);

    // Event: Ensure values are set when user clicks Apply button
    $el.on("apply.daterangepicker", function (ev, picker) {
        applySelection(picker.startDate, picker.endDate, picker);
    });

    return $el;
}

/**
 * Get selected date range from date picker
 * @param {string} format - Date format (default: DD-MM-YYYY)
 * @param {string} id - Element ID of date picker
 * @returns {array} Array with [start_date, finish_date]
 */
function getDates(format = "DD-MM-YYYY", id = "reportrange") {
    let dates = $(`#${id} span`).html();
    dates = dates.trim()
    dates = dates.split('/')
    let start_date = moment(dates[0], 'DD-MM-YYYY').format(`${format}`);
    let finish_date = moment(dates[1], 'DD-MM-YYYY').format(`${format}`);
    return [start_date, finish_date]
}

// ============================================================================
// DEBUG FUNCTIONS
// ============================================================================

/**
 * Toggle DataTables debug bookmarklet
 * Useful for debugging DataTables issues
 */
function debugDataTable() {
    var url = 'https://debug.datatables.net/bookmarklet/DT_Debug.js';
    if (typeof DT_Debug != 'undefined') {
        if (DT_Debug.instance !== null) {
            DT_Debug.close();
        } else {
            new DT_Debug();
        }
    } else {
        var n = document.createElement('script');
        n.setAttribute('language', 'JavaScript');
        n.setAttribute('src', url + '?rand=' + new Date().getTime());
        document.body.appendChild(n);
    }
}

// ============================================================================
// END OF SHARED UTILITIES
// ============================================================================
