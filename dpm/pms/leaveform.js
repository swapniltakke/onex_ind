document.addEventListener("DOMContentLoaded", function () {
    // ===== GET USER ROLE FROM GLOBAL VARIABLES =====
    const USER_IS_ADMIN = (typeof isAdmin !== 'undefined' && isAdmin === 1);
    const USER_IS_SUPERVISOR = (typeof isSupervisor !== 'undefined' && isSupervisor === 1);
    const USER_IS_REGULAR = (typeof isRegularUser !== 'undefined' && isRegularUser === 1);
    const USER_ROLE = (typeof userRole !== 'undefined') ? userRole : 'user';
    const SUPERVISOR_ID = (typeof supervisorId !== 'undefined') ? supervisorId : '';
    const IS_VALID_SUPERVISOR = (typeof isValidSupervisor !== 'undefined') ? isValidSupervisor : false;
    const HAS_FULL_ACCESS = (typeof hasFullAccess !== 'undefined') ? hasFullAccess : 0;

    console.log('✅ Leave Report Permissions:', {
        isAdmin: USER_IS_ADMIN,
        isSupervisor: USER_IS_SUPERVISOR,
        isRegularUser: USER_IS_REGULAR,
        userRole: USER_ROLE,
        supervisorId: SUPERVISOR_ID,
        isValidSupervisor: IS_VALID_SUPERVISOR,
        hasFullAccess: HAS_FULL_ACCESS
    });

    // ===== INITIALIZE ONLY IF USER HAS ACCESS =====
    if (!HAS_FULL_ACCESS) {
        console.warn('⚠️ User does not have full access - limiting functionality');
        // Table will still load but with limited features
    }

    saveLog("log_dto", "Open Leave Report page access");
    localStorage.removeItem('DataTables_table_open_items_/dpm/pms/leave_reports.php');

    new FilterFields('table_open_items');
    
    // ===== ADJUSTED INDICES - GID is now index 1 =====
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
    let leave_typeFilter = new Filter('leave_type', 'table_open_items', 13);
    let absence_detailFilter = new Filter('absence_detail', 'table_open_items', 14);
    let start_dateFilter = new Filter('start_date', 'table_open_items', 15);
    let end_dateFilter = new Filter('end_date', 'table_open_items', 16);
    let total_daysFilter = new Filter('total_days', 'table_open_items', 17);
    
    let sub_departmentFilterOptions = {};
    let sub_departmentFilterNextValue = 1;

    let filterArray = [
        gidFilter, nameFilter, departmentFilter, sub_departmentFilter, roleFilter, 
        group_typeFilter, in_company_managerFilter, line_managerFilter, supervisorFilter, 
        sponsorFilter, employment_typeFilter, joinedFilter, leave_typeFilter, 
        absence_detailFilter, start_dateFilter, end_dateFilter, total_daysFilter
    ];
    
    new FilterInitial("table_open_items", filterArray);
    filterArray.forEach(filter => {
        filter.init();
    });

    // ===== SORT DROPDOWN FUNCTION =====
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

        $('#date_filter').css('background-color', '#00646E');
        
        // ✅ STORE TABLE REFERENCE GLOBALLY
        window.leaveDataTable = $('#table_open_items').DataTable({
            "dom": 'Blfrtip',
            "pageLength": 20,
            "paging": true,
            "scrollX": false,
            "autoWidth": false,
            'responsive': false,
            'fixedHeader': true,
            "scrollCollapse": true,
            "columnDefs": [
                {
                    "targets": 0, // Action column
                    "width": "90px",
                    "orderable": false,
                    "className": "action-column",
                    // ✅ HIDE ACTION COLUMN FOR NON-ADMIN USERS
                    "visible": USER_IS_ADMIN
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
                url: 'api/PMSController.php?action=getLeaveRegistrationData',
                type: 'POST',
                data: function(d) {
                    // ✅ PASS ROLE INFORMATION
                    d.is_admin = USER_IS_ADMIN ? 1 : 0;
                    d.is_supervisor = USER_IS_SUPERVISOR ? 1 : 0;
                    d.supervisor_id = SUPERVISOR_ID;
                    
                    console.log('📤 Leave Data AJAX Request:', {
                        is_admin: d.is_admin,
                        is_supervisor: d.is_supervisor,
                        supervisor_id: d.supervisor_id,
                        draw: d.draw
                    });
                    
                    return d;
                },
                dataSrc: function(json) {
                    console.log('📥 Raw API Response:', json);
                    
                    // ✅ CHECK IF RESPONSE IS VALID
                    if (!json) {
                        console.error('❌ No response from API');
                        if (typeof showNotification === 'function') {
                            showNotification('error', 'No response from server');
                        }
                        return [];
                    }
                    
                    // ✅ CHECK IF DATA EXISTS
                    if (!json.data) {
                        console.error('❌ No data property in response:', json);
                        if (typeof showNotification === 'function') {
                            showNotification('error', 'Invalid response format');
                        }
                        return [];
                    }
                    
                    // ✅ CHECK IF DATA IS ARRAY
                    if (!Array.isArray(json.data)) {
                        console.error('❌ Data is not an array:', typeof json.data);
                        if (typeof showNotification === 'function') {
                            showNotification('error', 'Invalid data format');
                        }
                        return [];
                    }
                    
                    console.log(`✅ Returning ${json.data.length} leave records`);
                    if (json.data.length > 0) {
                        console.log('First record:', json.data[0]);
                    }
                    
                    // ✅ RETURN THE DATA DIRECTLY
                    return json.data;
                },
                error: function(xhr, error, code) {
                    console.error('❌ AJAX Error:', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        responseText: xhr.responseText,
                        error: error,
                        code: code
                    });
                    
                    // ✅ TRY TO PARSE ERROR RESPONSE
                    try {
                        const errorResponse = JSON.parse(xhr.responseText);
                        console.error('Parsed error:', errorResponse);
                    } catch (e) {
                        console.error('Could not parse error response');
                    }
                    
                    if (typeof showNotification === 'function') {
                        showNotification('error', 'Error loading leave data. Please check console for details.');
                    } else {
                        alert('Error loading leave data');
                    }
                }
            },
            
            "bStateSave": false,
            "stateSave": false,
            lengthMenu: [
                [20, 40, 60, -1],
                [20, 40, 60, "All"],
            ],
            
            "initComplete": function (settings, json) {
                console.log('✅ Leave DataTable initialized');
                
                document.querySelector('#detailsegment').setAttribute('style', 'display: block');
                $('#table_open_items th').css({'min-width': '80px'});
                $('.dt-button.buttons-copy.buttons-html5').addClass('ui white button');
                $('.dt-button.buttons-copy.buttons-html5').prepend('<i class="clipboard icon"></i>');
                $('.dt-button.buttons-excel.buttons-html5').addClass('ui white button');
                $('.dt-button.buttons-excel.buttons-html5').prepend('<i class="file excel outline icon"></i>');
                $("#table_open_items_formFields").css('margin-bottom', "2%");
                $("#mai_spinner_page").removeClass('active');
                
                // ✅ LOG RECORD COUNT AND ACCESS LEVEL
                const recordCount = json && json.data ? json.data.length : 0;
                if (USER_IS_SUPERVISOR) {
                    console.log(`📊 Supervisor viewing ${recordCount} leave records for assigned employees`);
                } else if (USER_IS_ADMIN) {
                    console.log(`📊 Admin viewing ${recordCount} total leave records`);
                }
            },
            
            columns: [
                { // 0 - Action Column
                    className: 'action-column',
                    orderable: false,
                    data: null,
                    render: function(data, type, row) {
                        // ✅ SHOW VIEW ONLY FOR NON-ADMIN USERS
                        if (!USER_IS_ADMIN) {
                            return '<span style="color: #1e1919ff; font-size: 12px;">View Only</span>';
                        }
                        
                        // ✅ SHOW ACTION BUTTONS FOR ADMIN ONLY
                        return `
                            <div class="action-buttons-container">
                                <button class="action-button update" onclick="openLeaveModal(event,'${data.leave_id}')" title="Update Leave">
                                    <i class="fa fa-edit"></i> Update
                                </button>
                                <button class="action-button delete" onclick="deleteLeaveRequest('${data.leave_id}')" title="Delete Leave">
                                    <i class="fa fa-trash"></i> Delete
                                </button>
                            </div>
                        `;
                    }
                },
                { // 1 - GID
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
                { // 2 - Name
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
                { // 3 - Department
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
                { // 4 - Sub Department
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
                { // 5 - Role
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
                { // 6 - Group Type
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
                { // 7 - In Company Manager
                    className: "ta-c",
                    data: "in_company_manager",
                    render: function (data, type, row) {
                        if (data && !in_company_managerFilter.data.includes(data)) {
                            in_company_managerFilter.data.push(data);
                            $("#in_company_managerFilter").append(new Option(data, data));
                        }
                        return `<span>${data || '-'}</span>`;
                    }
                },
                { // 8 - Line Manager
                    className: "ta-c",
                    data: "line_manager",
                    render: function (data, type, row) {
                        if (data && !line_managerFilter.data.includes(data)) {
                            line_managerFilter.data.push(data);
                            $("#line_managerFilter").append(new Option(data, data));
                        }
                        return `<span>${data || '-'}</span>`;
                    }
                },
                { // 9 - Supervisor
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
                { // 10 - Sponsor
                    className: "ta-c",
                    data: "sponsor",
                    render: function (data, type, row) {
                        if (data && !sponsorFilter.data.includes(data)) {
                            sponsorFilter.data.push(data);
                            $("#sponsorFilter").append(new Option(data, data));
                        }
                        return `<span>${data || '-'}</span>`;
                    }
                },
                { // 11 - Employment Type
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
                { // 12 - Joined
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
                { // 13 - Leave Type
                    className: "ta-c",
                    data: "leave_type",
                    render: function (data, type, row) {
                        if (data && !leave_typeFilter.data.includes(data)) {
                            leave_typeFilter.data.push(data);
                            $("#leave_typeFilter").append(new Option(data, data));
                        }
                        return `<span>${data || '-'}</span>`;
                    }
                },
                { // 14 - Absence Detail
                    className: "ta-c",
                    data: "absence_detail",
                    render: function (data, type, row) {
                        if (data && !absence_detailFilter.data.includes(data)) {
                            absence_detailFilter.data.push(data);
                            $("#absence_detailFilter").append(new Option(data, data));
                        }
                        return `<span>${data || '-'}</span>`;
                    }
                },
                { // 15 - Start Date
                    className: "ta-c",
                    data: "start_date",
                    render: function (data, type, row) {
                        if (data && !start_dateFilter.data.includes(data)) {
                            start_dateFilter.data.push(data);
                            $("#start_dateFilter").append(new Option(data, data));
                        }
                        return `<span>${data || '-'}</span>`;
                    }
                },
                { // 16 - End Date
                    className: "ta-c",
                    data: "end_date",
                    render: function (data, type, row) {
                        if (data && !end_dateFilter.data.includes(data)) {
                            end_dateFilter.data.push(data);
                            $("#end_dateFilter").append(new Option(data, data));
                        }
                        return `<span>${data || '-'}</span>`;
                    }
                },
                { // 17 - Total Days
                    className: "ta-c",
                    data: "total_days",
                    render: function (data, type, row) {
                        if (data && !total_daysFilter.data.includes(data)) {
                            total_daysFilter.data.push(data);
                            $("#total_daysFilter").append(new Option(data, data));
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
                        columns: ':visible:not(:first-child)' // ✅ EXCLUDE ACTION COLUMN
                    }
                },
                {
                    extend: 'copy',
                    text: 'Copy',
                    exportOptions: {
                        columns: ':visible:not(:first-child)' // ✅ EXCLUDE ACTION COLUMN
                    }
                }
            ],
            "drawCallback": function (settings) {
                $("#mai_spinner_page").removeClass('active');
            }
        });
    });
});

// ===== DELETE LEAVE REQUEST (ADMIN ONLY) =====
function deleteLeaveRequest(leave_id = 0) {
    // ✅ CHECK ADMIN PERMISSION
    const USER_IS_ADMIN = (typeof isAdmin !== 'undefined' && isAdmin === 1);
    
    if (!USER_IS_ADMIN) {
        if (typeof showNotification === 'function') {
            showNotification('error', 'You do not have permission to delete leave records');
        } else {
            alert('You do not have permission to delete leave records');
        }
        console.warn('⚠️ Non-admin user attempted to delete leave record:', leave_id);
        return;
    }
    
    if (!leave_id || leave_id < 1) {
        if (typeof showNotification === 'function') {
            showNotification('error', 'Could not identify leave record');
        } else {
            alert('Could not identify leave record');
        }
        console.error('❌ Invalid leave ID:', leave_id);
        return;
    }
    
    saveLog("log_dto", `deleteLeaveRequest; ID: ${leave_id}`);
    
    // ✅ USE SAME PATTERN AS USER DELETION
    Swal.fire({
        title: 'Are you sure to delete this leave record?',
        showCancelButton: true,
        cancelButtonColor: '#d33',
        cancelButtonText: 'Cancel!',
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'Delete',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return deleteLeaveRecord(leave_id);
        },
        allowOutsideClick: () => !Swal.isLoading()
    });
}

// ===== DELETE LEAVE PROMISE =====
const deleteLeaveRecord = function (leave_id = 0) {
    if (leave_id < 1) {
        if (typeof showNotification === 'function') {
            showNotification('error', 'Could not identify leave record');
        } else {
            alert('Could not identify leave record');
        }
        return Promise.reject('Invalid leave ID');
    }

    return new Promise((resolve, reject) => {
        $.ajax({
            url: 'api/PMSController.php?action=deleteLeave&leave_id=' + leave_id,
            dataType: 'json',
            type: 'GET',
            success: function (data) {
                console.log('✅ Leave record deleted:', data);
                
                if (typeof showNotification === 'function') {
                    showNotification('success', 'Leave record deleted successfully');
                } else {
                    alert('Leave record deleted successfully');
                }
                
                // ✅ RELOAD TABLE
                if (window.leaveDataTable) {
                    window.leaveDataTable.ajax.reload(null, false);
                }
                
                saveLog("log_dto", `Leave record deleted; ID: ${leave_id}`);
                resolve(data);
            },
            error: function (errResponse) {
                console.error('❌ Error deleting leave record:', errResponse);
                
                if (typeof showNotification === 'function') {
                    showNotification('error', 'Error deleting leave record');
                } else {
                    alert('Error deleting leave record');
                }
                
                saveLog("log_dto", `Leave record deletion failed; ID: ${leave_id}`);
                reject(errResponse);
            }
        });
    });
};

// ===== FILTER USERS BY DATE WITH SUPERVISOR SUPPORT =====
function filterUsersByDate() {
    let dateRangeText = $('#reportrange span').html().trim();
    
    // ✅ VALIDATE DATE RANGE IS SELECTED
    if (dateRangeText === 'Select Date Range' || !dateRangeText || dateRangeText === '') {
        if (typeof showNotification === 'function') {
            showNotification('warning', 'Please select a date range first');
        } else {
            alert('Please select a date range first');
        }
        return;
    }

    $("#mai_spinner_page").addClass('active');
    
    let dates = dateRangeText.split(' / ');
    
    if (dates.length !== 2) {
        if (typeof showNotification === 'function') {
            showNotification('error', 'Invalid date range selected');
        } else {
            alert('Invalid date range selected');
        }
        $("#mai_spinner_page").removeClass('active');
        return;
    }
    
    let start_date = dates[0].trim();
    let finish_date = dates[1].trim();
    
    const USER_IS_ADMIN = (typeof isAdmin !== 'undefined' && isAdmin === 1);
    const USER_IS_SUPERVISOR = (typeof isSupervisor !== 'undefined' && isSupervisor === 1);
    const SUPERVISOR_ID = (typeof supervisorId !== 'undefined') ? supervisorId : '';
    
    console.log('🔍 Filtering leave data by date:', start_date, 'to', finish_date, {
        isAdmin: USER_IS_ADMIN,
        isSupervisor: USER_IS_SUPERVISOR,
        supervisorId: SUPERVISOR_ID
    });
    
    // ✅ BUILD URL WITH ROLE PARAMETERS
    let url_string = `api/PMSController.php?action=getLeaveRegistrationData&start_date=${start_date}&finish_date=${finish_date}`;
    
    if (window.leaveDataTable) {
        window.leaveDataTable.ajax.url(url_string).load(function() {
            console.log('✅ Leave table reloaded with date filter');
            $("#mai_spinner_page").removeClass('active');
            
            if (typeof showNotification === 'function') {
                showNotification('success', `Filtering data for ${start_date} to ${finish_date}`);
            }
        });
    }
}

// ===== DATE RANGE PICKER INITIALIZATION =====
$(document).ready(function () {
    $("#mai_spinner_page").addClass('active');

    let thisFiscalYear = new Date().getFullYear();
    const thisMonth = new Date().getMonth();
    if (thisMonth < 9) {
        thisFiscalYear -= 1;
    }
    const thisyear = new Date().getFullYear();
    let lastFiscalYear2;
    let lastFiscalYear1;
    if (thisMonth < 9) {
        lastFiscalYear1 = thisyear - 2;
        lastFiscalYear2 = thisyear - 1;
    }
    
    // ✅ INITIALIZE DATE RANGE PICKER
    $('#reportrange').daterangepicker({
        autoUpdateInput: false,
        showDropdowns: true,
        minYear: 2021,
        maxYear: parseInt(moment().format('YYYY'), 10) + 1,
        alwaysShowCalendars: true,
        autoApply: false,
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            "Last Year": [moment().subtract(1, "y").startOf("year"), moment().subtract(1, "y").endOf("year")],
            "Last 1 Year": [moment().subtract(365, 'days'), moment()],
            "This Fiscal Year": [new Date(thisFiscalYear, 9, 1), moment()],
            "Last Fiscal Year": [new Date(lastFiscalYear1, 9, 1), new Date(lastFiscalYear2, 9, 1)]
        }
    }, function(start, end, label) {
        $('#reportrange span').html(start.format('DD-MM-YYYY') + ' / ' + end.format('DD-MM-YYYY'));
        console.log('📅 Date range selected:', start.format('DD-MM-YYYY'), '-', end.format('DD-MM-YYYY'));
    });

    // ✅ CLEAR DATE FILTER BUTTON
    $('#clearDateFilter').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        console.log('🗑️ Clear date filter clicked');
        
        // Reset date range display
        $('#reportrange span').html('Select Date Range');
        
        // Clear the datepicker selection
        $('#reportrange').data('daterangepicker').setStartDate(moment());
        $('#reportrange').data('daterangepicker').setEndDate(moment());
        
        // Reload table with ALL data (no date filter)
        if (window.leaveDataTable) {
            $("#mai_spinner_page").addClass('active');
            
            window.leaveDataTable.ajax.url('api/PMSController.php?action=getLeaveRegistrationData').load(function() {
                console.log('✅ Leave table reloaded without date filter - showing all records');
                $("#mai_spinner_page").removeClass('active');
                
                if (typeof showNotification === 'function') {
                    showNotification('info', 'Date filter cleared - showing all records');
                }
            });
        }
    });
});