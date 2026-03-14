async function getBreakerData(id){
    return $.ajax({
        url: `api/BreakerController.php`,
        type: 'GET',
        data: {
            "action": "getBreakerData",
            "id": id
        }
    }).catch(e => {
        console.log(e)
        showNotification("error", "This breaker data could not be loaded");
    });
}

async function openUpdateModal(event, id){
    event.target.innerText = "Loading...";
    event.target.classList.add("disabled");

    const breakerData = await getBreakerData(id);
    
    $("#hdnId").val(breakerData.id).trigger('change');
    $("#group_name").val(breakerData.group_name).trigger('change');
    $("#cdd_date").val(breakerData.cdd_date_formatted).trigger('change');
    $("#plan_month_date").val(breakerData.plan_month_date_formatted).trigger('change');
    $("#sales_order_no").val(breakerData.sales_order_no).trigger('change');
    $("#item_no").val(breakerData.item_no).trigger('change');
    $("#client").val(breakerData.client).trigger('change');
    $("#mlfb_no").val(breakerData.mlfb_no).trigger('change');
    $("#rating").val(breakerData.rating).trigger('change');
    $("#product_name").val(breakerData.product_name).trigger('change');
    $("#width").val(breakerData.width).trigger('change');
    $("#trolley_type").val(breakerData.trolley_type).trigger('change');
    $("#trolley_refair").val(breakerData.trolley_refair).trigger('change');
    $("#total_quantity").val(breakerData.total_quantity).trigger('change');
    $("#production_order_quantity").val(breakerData.production_order_quantity).trigger('change');
    $("#addon").val(breakerData.addon).trigger('change');
    $("#serial_no").val(breakerData.serial_no).trigger('change');
    $("#ptd_no").val(breakerData.ptd_no).trigger('change');
    $("#production_order_no").val(breakerData.production_order_no).trigger('change');
    $("#vi_type").val(breakerData.vi_type).trigger('change');
    $("#c1_date").val(breakerData.c1_date_formatted).trigger('change');
    $("#cia_date").val(breakerData.cia_date_formatted).trigger('change');
    $("#remark").val(breakerData.remark).trigger('change');

    event.target.innerText = "Update";
    event.target.classList.remove("disabled");

    var selectedCddDate = breakerData.cdd_date_formatted;
    var selectedPlanDate = breakerData.plan_month_date_formatted;
    var selectedC1Date = breakerData.c1_date_formatted;
    var selectedCiaDate = breakerData.cia_date_formatted;

    if (!selectedCddDate) {
        selectedCddDate = moment().format('DD-MM-YYYY');
    }
    if (!selectedPlanDate) {
        selectedPlanDate = moment().format('DD-MM-YYYY');
    }
    if (!selectedC1Date) {
        selectedC1Date = moment().format('DD-MM-YYYY');
    }
    if (!selectedCiaDate) {
        selectedCiaDate = moment().format('DD-MM-YYYY');
    }

    $('#cdd').daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        autoApply: true,
        locale: {
            format: 'DD-MM-YYYY'
        },
        startDate: moment(selectedCddDate, 'DD-MM-YYYY')
    });

    $('#cdd').on('apply.daterangepicker', function(ev, picker) {
        var selectedCddDate = picker.startDate.format('DD-MM-YYYY');
        $('#cdd_date').val(selectedCddDate);
        validatePlanMonthDate("cdd_date");
    });
    
    $('#plan_month').daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        autoApply: true,
        locale: {
            format: 'DD-MM-YYYY'
        },
        startDate: moment(selectedPlanDate, 'DD-MM-YYYY')
    });

    $('#plan_month').on('apply.daterangepicker', function(ev, picker) {
        var selectedPlanDate = picker.startDate.format('DD-MM-YYYY');
        $('#plan_month_date').val(selectedPlanDate);
        validatePlanMonthDate("plan_month_date");
    });

    $('#c1date').daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        autoApply: true,
        locale: {
            format: 'DD-MM-YYYY'
        },
        opened: true,
        drops: 'up',
        startDate: moment(selectedC1Date, 'DD-MM-YYYY')
    });

    $('#c1date').on('apply.daterangepicker', function(ev, picker) {
        var selectedC1Date = picker.startDate.format('DD-MM-YYYY');
        $('#c1_date').val(selectedC1Date);
        validatePlanMonthDate("c1_date");
    });

    $('#ciadate').daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        autoApply: true,
        locale: {
            format: 'DD-MM-YYYY'
        },
        opened: true,
        drops: 'up',
        startDate: moment(selectedCiaDate, 'DD-MM-YYYY')
    });

    $('#ciadate').on('apply.daterangepicker', function(ev, picker) {
        var selectedCiaDate = picker.startDate.format('DD-MM-YYYY');
        $('#cia_date').val(selectedCiaDate);
        validatePlanMonthDate("cia_date");
    });

    $("#updateModal").modal('show');
    saveLog("log_assembly_breaker", `Opened updateBreakerModal in details page;`);
}

function updateBreaker(pageName) {
    const id = $("#hdnId").val();
    const group_name = document.getElementById('group_name').value;
    const cdd_date = document.getElementById('cdd_date').value;
    const plan_month_date = document.getElementById('plan_month_date').value;
    const sales_order_no = document.getElementById('sales_order_no').value;
    const item_no = document.getElementById('item_no').value;
    const client = document.getElementById('client').value;
    const mlfb_no = document.getElementById('mlfb_no').value;
    const rating = document.getElementById('rating').value;
    const product_name = document.getElementById('product_name').value;
    const width = document.getElementById('width').value;
    const trolley_type = document.getElementById('trolley_type').value;
    const trolley_refair = document.getElementById('trolley_refair').value;
    const total_quantity = document.getElementById('total_quantity').value;
    const production_order_quantity = document.getElementById('production_order_quantity').value;
    const addon = document.getElementById('addon').value;
    const serial_no = document.getElementById('serial_no').value;
    const ptd_no = document.getElementById('ptd_no').value;
    const production_order_no = document.getElementById('production_order_no').value;
    const vi_type = document.getElementById('vi_type').value;
    const c1_date = document.getElementById('c1_date').value;
    const cia_date = document.getElementById('cia_date').value;
    const remark = document.getElementById('remark').value;
    var check_cdd_date;
    var check_plan_month_date;
    var check_sales_order_no;
    var check_item_no;
    var check_total_quantity;
    var check_production_order_quantity;
    var check_serial_no;
    var check_ptd_no;
    var check_production_order_no;
    var check_c1_date;
    var check_cia_date;

    if (!id) {
        showNotification("warning", "Breaker can not be empty");
        return;
    }

    if(!group_name) {
        document.getElementById('group_name').classList.add('is-invalid');
        toastr.warning('','The group field is required');
        return;
    } else {
        document.getElementById('group_name').classList.remove('is-invalid');
    }
    
    if(!cdd_date) {
        document.getElementById('cdd_date').classList.add('is-invalid');
        toastr.warning('','An cdd date should be selected');
        return;
    } else {
        document.getElementById('cdd_date').classList.remove('is-invalid');
    }
    check_cdd_date = validatePlanMonthDate("cdd_date");
    if (check_cdd_date == false) {
        return;
    } else {
        document.getElementById('cdd_date').classList.remove('is-invalid');
    }

    if(!plan_month_date) {
        document.getElementById('plan_month_date').classList.add('is-invalid');
        toastr.warning('','An plan month date should be selected');
        return;
    } else {
        document.getElementById('plan_month_date').classList.remove('is-invalid');
    }
    check_plan_month_date = validatePlanMonthDate("plan_month_date");
    if (check_plan_month_date == false) {
        return;
    } else {
        document.getElementById('plan_month_date').classList.remove('is-invalid');
    }

    if(!sales_order_no) {
        document.getElementById('sales_order_no').classList.add('is-invalid');
        toastr.warning('','The sales order number field is required');
        return;
    } else {
        document.getElementById('sales_order_no').classList.remove('is-invalid');
    }
    check_sales_order_no = validateNumbers("sales_order_no", "10", "sales order number");
    if (check_sales_order_no == false) {
        document.getElementById('sales_order_no').classList.add('is-invalid');
        toastr.warning('', 'The sales order number field should be a 10-digit number.');
        return;
    } else {
        document.getElementById('sales_order_no').classList.remove('is-invalid');
    }

    if(!item_no) {
        document.getElementById('item_no').classList.add('is-invalid');
        toastr.warning('','The item number field is required');
        return;
    } else {
        document.getElementById('item_no').classList.remove('is-invalid');
    }
    // check_item_no = validateNumbers("item_no", "6", "item number");
    // if (check_item_no == false) {
    //     document.getElementById('item_no').classList.add('is-invalid');
    //     toastr.warning('', 'The item number field should be a 6-digit number.');
    //     return;
    // } else {
    //     document.getElementById('item_no').classList.remove('is-invalid');
    // }
    if(!client) {
        document.getElementById('client').classList.add('is-invalid');
        toastr.warning('','The client field is required');
        return;
    } else {
        document.getElementById('client').classList.remove('is-invalid');
    }

    if(!mlfb_no) {
        document.getElementById('mlfb_no').classList.add('is-invalid');
        toastr.warning('','The MLFB no field is required');
        return;
    } else {
        document.getElementById('mlfb_no').classList.remove('is-invalid');
    }

    if(!rating) {
        document.getElementById('rating').classList.add('is-invalid');
        toastr.warning('','The rating field is required');
        return;
    } else {
        document.getElementById('rating').classList.remove('is-invalid');
    }

    if(!product_name) {
        document.getElementById('product_name').classList.add('is-invalid');
        toastr.warning('','The product name field is required');
        return;
    } else {
        document.getElementById('product_name').classList.remove('is-invalid');
    }

    if(!width){
        document.getElementById('width').classList.add('is-invalid');
        toastr.warning('','The width field is required');
        return;
    } else {
        document.getElementById('width').classList.remove('is-invalid');
    }

    if(!trolley_type){
        document.getElementById('trolley_type').classList.add('is-invalid');
        toastr.warning('','The trolley type field is required');
        return;
    } else {
        document.getElementById('trolley_type').classList.remove('is-invalid');
    }

    if(!trolley_refair){
        document.getElementById('trolley_refair').classList.add('is-invalid');
        toastr.warning('','The trolley refair field is required');
        return;
    } else {
        document.getElementById('trolley_refair').classList.remove('is-invalid');
    }

    if(!total_quantity){
        document.getElementById('total_quantity').classList.add('is-invalid');
        toastr.warning('','The total quantity field is required');
        return;
    } else {
        document.getElementById('total_quantity').classList.remove('is-invalid');
    }
    check_total_quantity = validateDigitsOnly('total_quantity', total_quantity, 'total quantity');
    if (check_total_quantity == false) {
        document.getElementById('total_quantity').classList.add('is-invalid');
        toastr.warning('', 'The total quantity field should contain only digits');
        return;
    } else {
        document.getElementById('total_quantity').classList.remove('is-invalid');
    }
    
    if(!production_order_quantity){
        document.getElementById('production_order_quantity').classList.add('is-invalid');
        toastr.warning('','The production order quantity field is required');
        return;
    } else {
        document.getElementById('production_order_quantity').classList.remove('is-invalid');
    }
    check_production_order_quantity = validateDigitsOnly('production_order_quantity', production_order_quantity, 'production order quantity');
    if (check_production_order_quantity == false) {
        document.getElementById('production_order_quantity').classList.add('is-invalid');
        toastr.warning('', 'The production order quantity field should contain only digits');
        return;
    } else {
        document.getElementById('production_order_quantity').classList.remove('is-invalid');
    }

    if (parseInt(total_quantity) < parseInt(production_order_quantity)) {
        document.getElementById('total_quantity').classList.add('is-invalid');
        toastr.warning('', 'The total quantity must be greater than the production order quantity');
        return;
    } else {
        document.getElementById('total_quantity').classList.remove('is-invalid');
    }

    if(!addon){
        document.getElementById('addon').classList.add('is-invalid');
        toastr.warning('','The addon field is required');
        return;
    } else {
        document.getElementById('addon').classList.remove('is-invalid');
    }

    // if(!serial_no) {
    //     document.getElementById('serial_no').classList.add('is-invalid');
    //     toastr.warning('','The serial number start field is required');
    //     return;
    // } else {
    //     document.getElementById('serial_no').classList.remove('is-invalid');
    // }
    if (serial_no) {
        check_serial_no = validateNumbers("serial_no", "8", "serial number start");
        if (check_serial_no == false) {
            document.getElementById('serial_no').classList.add('is-invalid');
            toastr.warning('', 'The serial number start field should be a 8-digit number.');
            return;
        } else {
            document.getElementById('serial_no').classList.remove('is-invalid');
        }
    }
    if (ptd_no) {
        check_ptd_no = validateAlphaNumeric("ptd_no", "13", "ptd number");
        if (check_ptd_no == false) {
            document.getElementById('ptd_no').classList.add('is-invalid');
            toastr.warning('', 'The ptd number field should be a 13-digit alphanumeric value.');
            return;
        } else {
            document.getElementById('ptd_no').classList.remove('is-invalid');
        }
    }

    if (production_order_no) {
        check_production_order_no = validateNumbers("production_order_no", "12", "production order number");
        if (check_production_order_no == false) {
            document.getElementById('production_order_no').classList.add('is-invalid');
            toastr.warning('', 'The production order number field should be a 12-digit number.');
            return;
        } else {
            document.getElementById('production_order_no').classList.remove('is-invalid');
        }
    }
    
    if(!vi_type) {
        document.getElementById('vi_type').classList.add('is-invalid');
        toastr.warning('','The vi type field is required');
        return;
    } else {
        document.getElementById('vi_type').classList.remove('is-invalid');
    }

    if (c1_date) {
        check_c1_date = validatePlanMonthDate("c1_date");
        if (check_c1_date == false) {
            return;
        } else {
            document.getElementById('c1_date').classList.remove('is-invalid');
        }
    }
    if (cia_date) {
        check_cia_date = validatePlanMonthDate("cia_date");
        if (check_cia_date == false) {
            return;
        } else {
            document.getElementById('cia_date').classList.remove('is-invalid');
        }
    }

    $.ajax({
        url: 'api/BreakerController.php',
        method: 'POST',
        data: {
            "action": "edit",
            "id": id,
            "group_name": group_name,
            "cdd_date": cdd_date,
            "plan_month_date": plan_month_date,
            "sales_order_no": sales_order_no,
            "item_no": item_no,
            "client": client,
            "mlfb_no": mlfb_no,
            "rating": rating,
            "product_name": product_name,
            "width": width,
            "trolley_type": trolley_type,
            "trolley_refair": trolley_refair,
            "total_quantity": total_quantity,
            "production_order_quantity": production_order_quantity,
            "addon": addon,
            "serial_no": serial_no,
            "ptd_no": ptd_no,
            "production_order_no": production_order_no,
            "vi_type": vi_type,
            "c1_date": c1_date,
            "cia_date": cia_date,
            "remark": remark
        },
        success: function () {
            saveLog("log_assembly_breaker", `success on updateBreaker in details page; ID: ${id}, sales_order_no: ${sales_order_no}, production_order_no: ${production_order_no}`);
            showNotification('success', "Successfully updated");
            $('#table_open_items').DataTable().ajax.reload(null, false);
            $("#updateModal").modal('hide');
        },
        error: function (errResponse) {
            saveLog("log_assembly_breaker", `error on updateBreaker in details page; ID: ${id}, sales_order_no: ${sales_order_no}, production_order_no: ${production_order_no}`);
            showNotification('error', "An error occurred");
        }
    });
}

function validatePlanMonthDate(data) {
    var inputValue = $('#'+data).val();
    var dateRegex = /^[0-9]{2}-[0-9]{2}-[0-9]{4}$/;
    if (!dateRegex.test(inputValue)) {
        // Display an error message
        $('#'+data).addClass('is-invalid');
        $('#'+data).siblings('.invalid-feedback').remove();
        toastr.warning('','Please enter a valid date in the format DD-MM-YYYY');
        return false;
        // $('#'+data).after('<div class="invalid-feedback">Please enter a valid date in the format DD-MM-YYYY.</div>');
    } else {
        // Remove the error message
        $('#'+data).removeClass('is-invalid');
        $('#'+data).siblings('.invalid-feedback').remove();
        return true;
    }
}

function validateNumbers(fieldName, requiredLength, displayName) {
    // Get the input value
    var inputValue = document.getElementById(fieldName).value;
    // Check if the input value is a digit number
    var numberRegex = new RegExp(`^[0-9]{${requiredLength}}$`);

    if (!numberRegex.test(inputValue)) {
        // Display an error message
        return false;
    } else {
        return true;
    }
}

function validateDigitsOnly(fieldName, inputValue, displayName) {
    const digitsOnlyRegex = /^[0-9]+$/;
    if (digitsOnlyRegex.test(inputValue)) {
        // The input value is a valid number
        return true;
    } else {
        // The input value is not a valid number
        return false;
    }
}

function validateAlphaNumeric(fieldName, requiredLength, displayName) {
    // Get the input value
    var inputValue = document.getElementById(fieldName).value;
    // Check if the input value is an alphanumeric string of the required length
    var alphaNumericRegex = new RegExp(`^[a-zA-Z0-9]{${requiredLength}}$`);

    if (!alphaNumericRegex.test(inputValue)) {
        // The input value is not a valid alphanumeric string
        return false;
    } else {
        return true;
    }
}