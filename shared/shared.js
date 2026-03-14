function getUrlParameters(decoded = true) {
    return Object.assign({}, ...window.location.search.substring(1).split('&').map(param => {
        const key = param.split('=')[0];
        const val = param.split('=')[1];
        return {[key]: (decoded) ? decodeURIComponent(val) : val}
    }));
}


/**
 * Requires `/js/jquery.min.js`, `/js/jquery.toast.min.js` and `/css/jquery.toast.min.css` to run
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
 * Requires `/js/jquery.min.js` to run
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