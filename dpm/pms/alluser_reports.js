document.addEventListener("DOMContentLoaded", function () {
    saveLog("log_dto", "Open List page access");
    localStorage.removeItem('DataTables_table_open_items_/dpm/dto/user_reports.php');

    new FilterFields('table_open_items');
    // ✅ ADJUSTED INDICES - GID is now index 1
    let gidFilter = new Filter('gid', 'table_open_items', 1);
    let nameFilter = new Filter('name', 'table_open_items', 2);
    let departmentFilter = new Filter('department', 'table_open_items', 3);
    let sub_departmentFilter = new Filter('sub_department', 'table_open_items', 4);
    let roleFilter = new Filter('role', 'table_open_items', 5);
    let group_typeFilter = new Filter('group_type', 'table_open_items', 6);
    let in_company_managerFilter = new Filter('in_company_manager', 'table_open_items', 7);
    let line_managerFilter = new Filter('line_manager', 'table_open_items', 8);
    let supervisorFilter = new Filter('supervisor', 'table_open_items', 9);
    let sponsorFilter = new Filter('sponsor', 'table_open_items', 10);
    let employment_typeFilter = new Filter('employment_type', 'table_open_items', 11);
    let joinedFilter = new Filter('joined', 'table_open_items', 12);
    let shift_typeFilter = new Filter('shift_type', 'table_open_items', 13);
    let temp_sub_departmentFilter = new Filter('temp_sub_department', 'table_open_items', 14);
    let temp_group_typeFilter = new Filter('temp_group_type', 'table_open_items', 15);
    let transfer_from_dateFilter = new Filter('transfer_from_date', 'table_open_items', 16);
    let transfer_to_dateFilter = new Filter('transfer_to_date', 'table_open_items', 17);
    
    let sub_departmentFilterOptions = {};
    let sub_departmentFilterNextValue = 1;
    let temp_sub_departmentFilterOptions = {};
    let temp_sub_departmentFilterNextValue = 1;

    let filterArray = [gidFilter, nameFilter, departmentFilter, sub_departmentFilter, roleFilter, group_typeFilter, in_company_managerFilter, line_managerFilter, supervisorFilter, sponsorFilter, employment_typeFilter, joinedFilter, shift_typeFilter, temp_sub_departmentFilter, temp_group_typeFilter, transfer_from_dateFilter, transfer_to_dateFilter];
    new FilterInitial("table_open_items", filterArray);
    filterArray.forEach(filter => {
        filter.init();
    });
    
    function sortDropdown(selectElementId) {
        var $select = $(selectElementId);
        var uniqueOptions = {};
        $select.find('option').each(function() {
            var text = $(this).text().trim();
            uniqueOptions[text] = $(this);
        });
        var sortedOptions = Object.keys(uniqueOptions)
            .sort((a, b) => a.localeCompare(b, undefined, {sensitivity: 'base'}))
            .map(key => uniqueOptions[key]);
        
        $select.empty().append(sortedOptions);
        $select.trigger('change');
    }
    
    $(document).ready(function() {
        setTimeout(function() {
            sortDropdown("#clientFilter");
            $('#table_open_items').DataTable().draw();
        }, 500);
    });

    function validateManagerSponsorData(data) {
        let displayData = {...data};
        
        const hasSponsor = displayData.sponsor && displayData.sponsor.trim() !== '';
        const hasInCompanyManager = displayData.in_company_manager && displayData.in_company_manager.trim() !== '';
        const hasLineManager = displayData.line_manager && displayData.line_manager.trim() !== '';

        if (hasSponsor && !hasInCompanyManager && !hasLineManager) {
            displayData.in_company_manager = '-';
            displayData.line_manager = '-';
        } 
        else if (hasInCompanyManager && hasLineManager && !hasSponsor) {
            displayData.sponsor = '-';
        }
        else if (!hasSponsor && !hasInCompanyManager && !hasLineManager) {
            displayData.sponsor = '-';
            displayData.in_company_manager = '-';
            displayData.line_manager = '-';
        }
        else if (hasInCompanyManager && !hasLineManager) {
            displayData.line_manager = '-';
        } else if (!hasInCompanyManager && hasLineManager) {
            displayData.in_company_manager = '-';
        }

        return displayData;
    }

    function displayEmptyAsHyphen(value) {
        return value && value.trim() !== '' ? value : '-';
    }

    $(document).ready(function() {
    $('#date_filter').css('background-color', '#00646E');
    
    // ✅ STORE TABLE REFERENCE GLOBALLY
    window.employeeTable = $('#table_open_items').DataTable({
        "dom": 'Blfrtip',
        "pageLength": 20,
        "scrollX": false,
        "scrollCollapse": true,
        "autoWidth": false,
        "columnDefs": [
            {
                "targets": 0,
                "width": "180px",
                "orderable": false,
                "className": "action-column"
            },
            { "targets": 1, "width": "120px" },
            { "targets": 2, "width": "150px" },
            { "targets": 3, "width": "150px" },
            { "targets": 4, "width": "150px" },
            { "targets": 5, "width": "120px" },
            { "targets": 6, "width": "150px" },
            { "targets": 7, "width": "200px" },
            { "targets": 8, "width": "200px" },
            { "targets": 9, "width": "250px" },
            { "targets": 10, "width": "120px" },
            { "targets": 11, "width": "120px" },
            { "targets": 12, "width": "120px" },
            { "targets": 13, "width": "120px" },
            { "targets": 14, "width": "120px" },
            { "targets": 15, "width": "120px" },
            { "targets": 16, "width": "120px" },
            { "targets": 17, "width": "120px" }
        ],
        
        ajax: {
            url: 'api/PMSController.php?action=getEmployeeRegistrationData',
            type: 'POST',
            data: function(d) {
                d.is_admin = isAdmin;
                d.is_supervisor = isSupervisor;
                d.supervisor_id = supervisorId;
                
                console.log('📤 DataTable AJAX Request:', {
                    is_admin: d.is_admin,
                    is_supervisor: d.is_supervisor,
                    supervisor_id: d.supervisor_id
                });
                
                return d;
            },
            dataSrc: function(json) {
                console.log('📥 API Response:', json);
                
                if (isSupervisor && !isValidSupervisor) {
                    console.warn('⚠️ Invalid supervisor - no employees assigned');
                    if (typeof showNotification === 'function') {
                        showNotification('warning', 'You do not have any employees assigned to you.');
                    } else {
                        alert('You do not have any employees assigned to you.');
                    }
                    return [];
                }
                
                if (isRegularUser) {
                    console.error('❌ Regular user attempted to access employee data');
                    if (typeof showNotification === 'function') {
                        showNotification('error', 'You do not have permission to view this data.');
                    } else {
                        alert('You do not have permission to view this data.');
                    }
                    return [];
                }
                
                if (json.data && json.data.length > 0) {
                    console.log(`✅ Loaded ${json.data.length} employee records`);
                } else {
                    console.warn('⚠️ No employee data returned');
                }
                
                return json.data || [];
            },
            error: function(xhr, error, code) {
                console.error('❌ DataTable AJAX Error:', {
                    xhr: xhr,
                    error: error,
                    code: code,
                    responseText: xhr.responseText
                });
                
                let errorMessage = 'Error loading data. Please try again.';
                
                if (isSupervisor && !isValidSupervisor) {
                    errorMessage = 'Access denied: Invalid supervisor credentials';
                } else if (isRegularUser) {
                    errorMessage = 'Access denied: Insufficient permissions';
                }
                
                if (typeof showNotification === 'function') {
                    showNotification('error', errorMessage);
                } else {
                    alert(errorMessage);
                }
            }
        },
        contentType: "application/json",
        "bStateSave": false,
        "stateSave": false,
        lengthMenu: [
            [20, 40, 60, -1],
            [20, 40, 60, "All"],
        ],
        
        "initComplete": function (settings, json) {
            console.log('✅ DataTable initialized');
            
            document.querySelector('#detailsegment').setAttribute('style', 'display: block');
            $('#table_open_items th').css({'min-width': '80px'});
            $('.dt-button.buttons-copy.buttons-html5').addClass('ui white button');
            $('.dt-button.buttons-copy.buttons-html5').prepend('<i class="clipboard icon"></i>');
            $('.dt-button.buttons-excel.buttons-html5').addClass('ui white button');
            $('.dt-button.buttons-excel.buttons-html5').prepend('<i class="file excel outline icon"></i>');
            $("#table_open_items_formFields").css('margin-bottom', "2%");
            $("#mai_spinner_page").removeClass('active');
            
            if (isAdmin !== 1) {
                console.log('🔒 Hiding action column for non-admin user');
                window.employeeTable.column(0).visible(false);
            } else {
                console.log('🔓 Showing action column for admin user');
                window.employeeTable.column(0).visible(true);
            }
        },
        
        columns: [
            {
                className: 'action-column',
                orderable: false,
                data: 'user_id',
                render: function(data, type, row) {
                    if (isAdmin !== 1) {
                        return '<span style="color: #999; font-size: 12px;">View Only</span>';
                    }
                    
                    return `
                        <div class="action-buttons-container">
                            <button class="action-button update" onclick="openUserModal(event, ${row.user_id})" title="Update Employee">
                                <i class="fa fa-edit"></i> Update
                            </button>
                            <button class="action-button delete" onclick="openDeleteUser(${row.user_id})" title="Delete Employee">
                                <i class="fa fa-trash"></i> Delete
                            </button>
                        </div>
                    `;
                }
            },
            {
                className: "ta-c",
                data: "gid",
                render: function (data, type, row) {
                    if (data && !gidFilter.data.includes(data)) {
                        gidFilter.data.push(data);
                        $("#gidFilter").append(new Option(data, data));
                    }
                    return `<span>${data || '-'}</span>`;
                }
            },
            {
                className: "ta-c",
                data: "name",
                render: function (data, type, row) {
                    if (data && !nameFilter.data.includes(data)) {
                        nameFilter.data.push(data);
                        $("#nameFilter").append(new Option(data, data));
                    }
                    return `<span>${data || '-'}</span>`;
                }
            },
            {
                className: "ta-c",
                data: "department",
                render: function (data, type, row) {
                    if (data && !departmentFilter.data.includes(data)) {
                        departmentFilter.data.push(data);
                        $("#departmentFilter").append(new Option(data, data));
                    }
                    return `<span>${data || '-'}</span>`;
                }
            },
            {
                className: "ta-c",
                data: "sub_department",
                render: function (data, type, row) {
                    let sub_departmentName = data || '-';
                    let sub_departmentUniqueNumber = row.sub_departmentUniqueNumber || data;
                    
                    if (sub_departmentUniqueNumber && !sub_departmentFilterOptions.hasOwnProperty(sub_departmentUniqueNumber)) {
                        sub_departmentFilterOptions[sub_departmentUniqueNumber] = sub_departmentFilterNextValue++;
                        $("#sub_departmentFilter").append(new Option(sub_departmentName, sub_departmentUniqueNumber));
                    }
                    return `<span>${sub_departmentName}</span>`;
                }
            },
            {
                className: "ta-c",
                data: "role",
                render: function (data, type, row) {
                    if (data && !roleFilter.data.includes(data)) {
                        roleFilter.data.push(data);
                        $("#roleFilter").append(new Option(data, data));
                    }
                    return `<span>${data || '-'}</span>`;
                }
            },
            {
                className: "ta-c",
                data: "group_type",
                render: function (data, type, row) {
                    if (data && !group_typeFilter.data.includes(data)) {
                        group_typeFilter.data.push(data);
                        $("#group_typeFilter").append(new Option(data, data));
                    }
                    return `<span>${data || '-'}</span>`;
                }
            },
            {
                className: "ta-c",
                data: "in_company_manager",
                render: function (data, type, row) {
                    let displayData = validateManagerSponsorData(row);
                    if (displayData.in_company_manager && !in_company_managerFilter.data.includes(displayData.in_company_manager)) {
                        in_company_managerFilter.data.push(displayData.in_company_manager);
                        $("#in_company_managerFilter").append(new Option(displayData.in_company_manager, displayData.in_company_manager));
                    }
                    return `<span>${displayEmptyAsHyphen(displayData.in_company_manager)}</span>`;
                }
            },
            {
                className: "ta-c",
                data: "line_manager",
                render: function (data, type, row) {
                    let displayData = validateManagerSponsorData(row);
                    if (displayData.line_manager && !line_managerFilter.data.includes(displayData.line_manager)) {
                        line_managerFilter.data.push(displayData.line_manager);
                        $("#line_managerFilter").append(new Option(displayData.line_manager, displayData.line_manager));
                    }
                    return `<span>${displayEmptyAsHyphen(displayData.line_manager)}</span>`;
                }
            },
            {
                className: "ta-c",
                data: "supervisor",
                render: function (data, type, row) {
                    if (data && !supervisorFilter.data.includes(data)) {
                        supervisorFilter.data.push(data);
                        $("#supervisorFilter").append(new Option(data, data));
                    }
                    return `<span>${data || '-'}</span>`;
                }
            },
            {
                className: "ta-c",
                data: "sponsor",
                render: function (data, type, row) {
                    let displayData = validateManagerSponsorData(row);
                    if (displayData.sponsor && !sponsorFilter.data.includes(displayData.sponsor)) {
                        sponsorFilter.data.push(displayData.sponsor);
                        $("#sponsorFilter").append(new Option(displayData.sponsor, displayData.sponsor));
                    }
                    return `<span>${displayEmptyAsHyphen(displayData.sponsor)}</span>`;
                }
            },
            {
                className: "ta-c",
                data: "employment_type",
                render: function (data, type, row) {
                    if (data && !employment_typeFilter.data.includes(data)) {
                        employment_typeFilter.data.push(data);
                        $("#employment_typeFilter").append(new Option(data, data));
                    }
                    return `<span>${data || '-'}</span>`;
                }
            },
            {
                className: "ta-c",
                data: "joined",
                render: function (data, type, row) {
                    let joinedValue = data || '';
                    if (joinedValue) {
                        joinedValue = joinedValue.charAt(0).toUpperCase() + joinedValue.slice(1).toLowerCase();
                    }
                    
                    if (joinedValue && !joinedFilter.data.includes(joinedValue)) {
                        joinedFilter.data.push(joinedValue);
                        $("#joinedFilter").append(new Option(joinedValue, joinedValue));
                    }
                    
                    return `<span>${joinedValue || '-'}</span>`;
                }
            },
            {
                className: "ta-c",
                data: "shift_type",
                render: function (data, type, row) {
                    if (data && !shift_typeFilter.data.includes(data)) {
                        shift_typeFilter.data.push(data);
                        $("#shift_typeFilter").append(new Option(data, data));
                    }
                    return `<span>${data || '-'}</span>`;
                }
            },
            {
                className: "ta-c",
                data: "temp_sub_department",
                render: function (data, type, row) {
                    let temp_sub_departmentName = data || '-';
                    let temp_sub_departmentUniqueNumber = row.temp_sub_departmentUniqueNumber || data;
                    
                    if (temp_sub_departmentUniqueNumber && !temp_sub_departmentFilterOptions.hasOwnProperty(temp_sub_departmentUniqueNumber)) {
                        temp_sub_departmentFilterOptions[temp_sub_departmentUniqueNumber] = temp_sub_departmentFilterNextValue++;
                        $("#temp_sub_departmentFilter").append(new Option(temp_sub_departmentName, temp_sub_departmentUniqueNumber));
                    }
                    return `<span>${temp_sub_departmentName}</span>`;
                }
            },
            {
                className: "ta-c",
                data: "temp_group_type",
                render: function (data, type, row) {
                    if (data && !temp_group_typeFilter.data.includes(data)) {
                        temp_group_typeFilter.data.push(data);
                        $("#temp_group_typeFilter").append(new Option(data, data));
                    }
                    return `<span>${data || '-'}</span>`;
                }
            },
            {
                className: "ta-c",
                data: "transfer_from_date",
                render: function (data, type, row) {
                    if (data && !transfer_from_dateFilter.data.includes(data)) {
                        transfer_from_dateFilter.data.push(data);
                        $("#transfer_from_dateFilter").append(new Option(data, data));
                    }
                    return `<span>${data || '-'}</span>`;
                }
            },
            {
                className: "ta-c",
                data: "transfer_to_date",
                render: function (data, type, row) {
                    if (data && !transfer_to_dateFilter.data.includes(data)) {
                        transfer_to_dateFilter.data.push(data);
                        $("#transfer_to_dateFilter").append(new Option(data, data));
                    }
                    return `<span>${data || '-'}</span>`;
                }
            }
        ],
        
        "order": [[1, 'asc']],
        buttons: [
            {
                extend: 'excelHtml5',
                autoFilter: true,
                exportOptions: {
                    columns: ':not(:first-child)'
                }
            },
            {
                extend: 'copy',
                text: 'Copy',
                exportOptions: {
                    columns: ':not(:first-child)'
                }
            }
        ],
        "drawCallback": function (settings) {
            if (settings.json) {
                $("#span_production_order_quantity_count").text(settings.json.ProductionOrderQuantityCount);
            }
            $("#mai_spinner_page").removeClass('active');
        }
    });
});
});

// ===== VIEW USER DETAILS =====
function viewUserDetails(user_id) {
    if (isSupervisor) {
        Swal.fire({
            title: 'Employee Details',
            html: '<p>Loading employee information...</p>',
            showConfirmButton: true,
            confirmButtonText: 'Close',
            didOpen: () => {
                $.ajax({
                    url: `api/PMSController.php?action=getUserDetails&user_id=${user_id}`,
                    type: 'GET',
                    success: function(response) {
                        const data = typeof response === 'string' ? JSON.parse(response) : response;
                        if (data.success) {
                            const employee = data.data;
                            Swal.update({
                                html: `
                                    <div style="text-align: left; padding: 20px;">
                                        <p><strong>GID:</strong> ${employee.gid || '-'}</p>
                                        <p><strong>Name:</strong> ${employee.name || '-'}</p>
                                        <p><strong>Department:</strong> ${employee.department || '-'}</p>
                                        <p><strong>Sub Department:</strong> ${employee.sub_department || '-'}</p>
                                        <p><strong>Role:</strong> ${employee.role || '-'}</p>
                                        <p><strong>Group Type:</strong> ${employee.group_type || '-'}</p>
                                        <p><strong>Employment Type:</strong> ${employee.employment_type || '-'}</p>
                                        <p><strong>Shift Type:</strong> ${employee.shift_type || '-'}</p>
                                    </div>
                                `
                            });
                        }
                    }
                });
            }
        });
    }
}

// ===== DELETE USER =====
function openDeleteUser(user_id = 0) {
    if (isAdmin !== 1) {
        if (typeof showNotification === 'function') {
            showNotification('error', 'You do not have permission to delete employees');
        } else {
            alert('You do not have permission to delete employees');
        }
        console.warn('⚠️ Non-admin user attempted to delete employee:', user_id);
        return;
    }
    
    saveLog("log_user", `deleteUserRequest in getEmployeeRegistrationData; ID: ${user_id}`);
    
    Swal.fire({
        title: 'Are you sure to delete this user?',
        showCancelButton: true,
        cancelButtonColor: '#d33',
        cancelButtonText: 'Cancel!',
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'Delete',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            $('#delete_user_id').val(user_id);
            Swal.close();
            setTimeout(() => {
                $('#reasonModal').show();
            }, 300);
            return false;
        },
        allowOutsideClick: () => !Swal.isLoading()
    });
}

// ===== DELETE FORM HANDLERS =====
$(document).ready(function() {
    $('#cancelReasonBtn, #closeReasonModal').on('click', function() {
        $('#reasonModal').hide();
        resetDeleteForm();
    });
    
    $('#deleteUserForm').on('submit', function(e) {
        e.preventDefault();
        
        if (isAdmin !== 1) {
            if (typeof showNotification === 'function') {
                showNotification('error', 'You do not have permission to delete employees');
            } else {
                alert('You do not have permission to delete employees');
            }
            $('#reasonModal').hide();
            resetDeleteForm();
            console.error('❌ Unauthorized delete attempt blocked');
            return;
        }
        
        const userId = $('#delete_user_id').val();
        const reason = $('#reason').val();
        const remarks = $('#remarks').val();
        
        if (!reason.trim()) {
            if (typeof showNotification === 'function') {
                showNotification('error', 'Please select a reason');
            } else {
                alert('Please select a reason');
            }
            $('#reason').focus();
            return;
        }
        
        if (!remarks.trim()) {
            if (typeof showNotification === 'function') {
                showNotification('error', 'Please enter remarks');
            } else {
                alert('Please enter remarks');
            }
            $('#remarks').focus();
            return;
        }
        
        const formData = new FormData();
        formData.append('user_id', userId);
        formData.append('reason', reason);
        formData.append('remarks', remarks);
        formData.append('action', 'deleteUser');
        formData.append('status', 'D');
        
        $('#deleteUserForm button[type="submit"]').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
        
        $.ajax({
            url: 'api/PMSController.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                const result = typeof response === 'string' ? JSON.parse(response) : response;
                
                if (result.success) {
                    if (typeof showNotification === 'function') {
                        showNotification('success', 'User deleted successfully');
                    } else {
                        alert('User deleted successfully');
                    }
                    
                    // ✅ FULL PAGE RELOAD AFTER SUCCESSFUL DELETION
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                    
                    saveLog("log_user", `User deleted successfully; ID: ${userId}`);
                } else {
                    if (typeof showNotification === 'function') {
                        showNotification('error', result.message || 'Unknown error');
                    } else {
                        alert(result.message || 'Unknown error');
                    }
                    
                    $('#reasonModal').hide();
                    resetDeleteForm();
                    $('#deleteUserForm button[type="submit"]').prop('disabled', false).html('Submit');
                }
            },
            error: function(xhr, status, error) {
                if (typeof showNotification === 'function') {
                    showNotification('error', 'Error deleting user: ' + error);
                } else {
                    alert('Error deleting user: ' + error);
                }
                $('#reasonModal').hide();
                saveLog("log_user", `User deletion failed; ID: ${userId}, Error: ${error}`);
                resetDeleteForm();
                $('#deleteUserForm button[type="submit"]').prop('disabled', false).html('Submit');
            }
        });
    });
    
    $(window).on('click', function(event) {
        if (event.target.id === 'reasonModal') {
            $('#reasonModal').hide();
            resetDeleteForm();
        }
    });
});

function resetDeleteForm() {
    $('#deleteUserForm')[0].reset();
    $('#deleteUserForm button[type="submit"]').prop('disabled', false).text('Submit');
}

// ===== DATE FILTER =====
function filterUsersByDate() {
    $("#mai_spinner_page").addClass('active');
    let dates = $('#reportrange span').html().split(' / ');
    
    if (dates.length !== 2 || dates[0] === 'Select Date Range') {
        if (typeof showNotification === 'function') {
            showNotification('warning', 'Please select a valid date range');
        } else {
            alert('Please select a valid date range');
        }
        $("#mai_spinner_page").removeClass('active');
        return;
    }
    
    let start_date = dates[0];
    let finish_date = dates[1];
    
    console.log('🔍 Filtering by date:', start_date, 'to', finish_date);
    
    let url_string = `api/PMSController.php?action=getEmployeeRegistrationData&start_date=${start_date}&finish_date=${finish_date}`;
    
    window.employeeTable.ajax.url(url_string).load(function() {
        console.log('✅ Table reloaded with date filter');
        $("#mai_spinner_page").removeClass('active');
    });
}

// ===== DATE RANGE PICKER =====
$(document).ready(function () {
    $("#mai_spinner_page").addClass('active');
    const start = moment().startOf('month');
    const end = moment();

    function cb(start, end) {
        $('#reportrange span').html(start.format("DD-MM-YYYY") + ' / ' + end.format("DD-MM-YYYY"));
        start_date = moment(start, 'DD-MM-YYYY').format('YYYY-MM-DD');
        finish_date = moment(end, 'DD-MM-YYYY').format('YYYY-MM-DD');
    }

    let thisFiscalYear = new Date().getFullYear();
    const thisMonth = new Date().getMonth();
    if (thisMonth < 9) {
        thisFiscalYear -= 1;
    }
    
    $('#reportrange').daterangepicker({
        startDate: start,
        endDate: end,
        showDropdowns: true,
        minYear: 2021,
        maxYear: parseInt(moment().format('YYYY'), 10) + 1,
        alwaysShowCalendars: true,
        autoApply: false,
        autoUpdateInput: false,
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            "Last Year": [moment().subtract(1, "y").startOf("year"), moment().subtract(1, "y").endOf("year")],
            "Last 1 Year": [moment().subtract(365, 'days'), moment()],
            "This Fiscal Year": [new Date(thisFiscalYear, 9, 1), moment()]
        }
    }, cb);
    
    $('#reportrange span').html('Select Date Range');
    
    $('#reportrange').on('apply.daterangepicker', function(ev, picker) {
        cb(picker.startDate, picker.endDate);
        filterUsersByDate();
    });
    
    $('#reportrange').on('cancel.daterangepicker', function(ev, picker) {
        console.log('🗑️ Date filter cancelled');
        $('#reportrange span').html('Select Date Range');
        window.employeeTable.ajax.url('api/PMSController.php?action=getEmployeeRegistrationData').load();
    });

    $('#clearDateFilter').on('click', function() {
        console.log('🗑️ Clearing date filter');
        $('#reportrange span').html('Select Date Range');
        
        // ✅ CLEAR ALL FILTER DROPDOWNS
        $('select[id$="Filter"]').each(function() {
            $(this).val('').trigger('change');
        });
        
        window.employeeTable.ajax.url('api/PMSController.php?action=getEmployeeRegistrationData').load(function() {
            console.log('✅ All filters cleared and table reloaded');
            if (typeof showNotification === 'function') {
                showNotification('success', 'All filters cleared');
            }
        });
    });
});