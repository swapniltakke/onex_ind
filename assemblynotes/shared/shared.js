const baseUrl = '/assemblynotes';
toastr.options = {
    "closeButton": true,
    "debug": false,
    "progressBar": true,
    "preventDuplicates": false,
    "positionClass": "toast-top-right",
    "onclick": null,
    "showDuration": "400",
    "hideDuration": "1000",
    "timeOut": "7000",
    "extendedTimeOut": "1000",
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut"
}

const showNotification = function (type = '', message = '') {
    if (type == "success") {
        toastr.success('', message)
    } else if (type == "error") {
        toastr.error('', message)
    } else if (type == "warning") {
        toastr.warning('', message)
    }
}

$('#page-wrapper').tooltip({
    selector: "[data-toggle=tooltip]",
    container: "body"
});
$('.i-checks').iCheck({
    checkboxClass: 'icheckbox_square-green',
    radioClass: 'iradio_square-green'
});

$(document).on('select2:open', () => {
    document.querySelector('.select2-search__field').focus();
});