<!DOCTYPE html>
<html>
<?php
?>
<link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/43.3.0/ckeditor5.css">
<!-- Add these in your head section -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<style>
/* Modal Header */
.modal-header {
    background: linear-gradient(135deg, #009999 0%, #006666 100%);
    color: white;
    padding: 12px 20px;
    text-align: center;
    border-bottom: 2px solid #007777;
    min-height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.modal-title {
    font-size: 22px;
    font-weight: 600;
    margin: 0;
    padding: 0 15px;
    line-height: 1.3;
    letter-spacing: 0.5px;
}

/* Close button */
.modal-header .close {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    margin: 0;
    padding: 5px 10px;
    color: white;
    font-size: 28px;
    opacity: 0.9;
    transition: all 0.3s ease;
}

.modal-header .close:hover {
    opacity: 1;
    transform: translateY(-50%) scale(1.1);
}

/* Modal Dialog - Increased Size */
.modal-dialog {
    max-width: 1100px;
    margin: 20px auto;
    width: 95%;
}

@media (min-width: 1200px) {
    .modal-dialog {
        max-width: 1200px;
    }
}

/* Modal Content */
.modal-content {
    width: 100%;
    margin: auto;
    border-radius: 8px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    border: none;
}

/* Modal Body */
.modal-body {
    position: relative;
    padding: 20px 15px;
    background-color: #f8f9fa;
    max-height: 78vh;
    overflow-y: auto;
    overflow-x: hidden;
    display: flex;
    justify-content: center;
}

/* Custom Scrollbar */
.modal-body::-webkit-scrollbar {
    width: 8px;
}

.modal-body::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.modal-body::-webkit-scrollbar-thumb {
    background: #009999;
    border-radius: 10px;
}

.modal-body::-webkit-scrollbar-thumb:hover {
    background: #007777;
}

/* Form Container */
.modal-scrollable-content {
    width: 95%;
    max-width: 1100px;
    margin: 0 auto;
    padding: 15px 25px;
}

.col-12.col-lg-11 {
    padding: 0 15px;
    margin: 0 auto;
    max-width: 100%;
}

/* Form Group Styling */
.form-group {
    margin-bottom: 15px;
}

.form-group label {
    font-weight: 600;
    font-size: 14px;
    color: #333;
    margin-bottom: 6px;
    display: block;
}

.form-group label .required {
    color: #e74c3c;
    margin-left: 3px;
}

/* Input and Select Styling - Enhanced */
.form-control {
    height: 42px !important;
    font-size: 15px !important;
    padding: 10px 15px !important;
    border: 2px solid #ddd !important;
    border-radius: 6px !important;
    transition: all 0.3s ease !important;
    background-color: white !important;
}

.form-control:focus {
    border-color: #009999 !important;
    box-shadow: 0 0 0 0.2rem rgba(0, 153, 153, 0.15) !important;
    outline: none !important;
}

.form-control:hover:not(:disabled):not([readonly]) {
    border-color: #009999 !important;
}

/* Textarea Styling */
textarea.form-control {
    height: auto !important;
    min-height: 80px !important;
    resize: none !important;
}

/* Select Dropdown - Enhanced */
select.form-control {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23009999' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 15px center;
    background-size: 12px;
    padding-right: 40px !important;
    cursor: pointer;
}

select.form-control option {
    padding: 10px;
    font-size: 15px;
}

/* Style for disabled select - GREY */
select:disabled {
    background-color: #d3d3d3 !important;
    color: #6c757d !important;
    cursor: not-allowed !important;
    opacity: 1 !important;
    border-color: #adb5bd !important;
}

/* Date Input Styling */
input[type="text"].form-control {
    cursor: pointer;
}

/* Readonly field styling - GREY */
input[type="number"].form-control[readonly] {
    background-color: #d3d3d3 !important;
    color: #6c757d !important;
    cursor: not-allowed !important;
    border-color: #adb5bd !important;
}

/* General disabled/readonly styling - GREY */
input:disabled,
input[readonly],
textarea:disabled,
textarea[readonly] {
    background-color: #d3d3d3 !important;
    color: #6c757d !important;
    cursor: not-allowed !important;
    border-color: #adb5bd !important;
}

#joined {
    text-transform: capitalize;
}

/* Table Styling */
.table {
    width: 100%;
    border-collapse: collapse;
}

.table th, .table td {
    padding: 12px 15px;
    text-align: left;
    border: 1px solid #ddd;
}

.table th {
    background-color: #f8f8f8;
    font-weight: bold;
}

/* Fix the table header during scroll */
.table-responsive {
    overflow-y: auto;
    max-height: 100%;
    width: 100%;
}

.table thead th {
    position: sticky;
    top: 0;
    background-color: #f8f8f8;
    z-index: 1;
}

.table-striped tbody tr:nth-child(odd) {
    background-color: #f9f9f9;
}

/* Modal Footer */
.modal-footer {
    background-color: #f8f9fa;
    padding: 15px 25px;
    border-top: 2px solid #dee2e6;
    text-align: right;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.modal-footer button {
    font-size: 16px;
    padding: 10px 25px;
    border-radius: 6px;
    font-weight: 600;
    transition: all 0.3s ease;
    min-width: 100px;
}

/* Custom button styles */
.btn-white {
    background-color: white;
    border: 2px solid #ccc;
    color: #555;
}

.btn-white:hover {
    background-color: #f1f1f1;
    border-color: #999;
    transform: translateY(-1px);
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.btn-primary {
    background-color: #009999;
    border: 2px solid #009999;
    color: white;
}

.btn-primary:hover {
    background-color: #007777;
    border-color: #007777;
    transform: translateY(-1px);
    box-shadow: 0 2px 5px rgba(0,153,153,0.3);
}

/* Row Spacing */
.form-group.row {
    margin-bottom: 10px;
}

/* Calendar and Flatpickr Styling */
.calendar-timeline-container {
    margin: 15px 0;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.timeline-view {
    display: flex;
    overflow-x: auto;
    padding: 15px 0;
    margin-bottom: 15px;
}

.day-block {
    min-width: 80px;
    padding: 10px;
    text-align: center;
    border: 1px solid #ddd;
    margin-right: 2px;
    background: white;
    transition: all 0.3s ease;
}

.day-block.selected {
    background: #1ab394;
    color: white;
}

.day-block.weekend {
    background: #f8f9fa;
}

.duration-details {
    padding: 10px;
    background: white;
    border-radius: 4px;
    margin-top: 10px;
}

.flatpickr-calendar {
    box-shadow: 0 3px 15px rgba(0,0,0,0.2) !important;
    width: 307px !important;
}

.flatpickr-day.selected {
    background: #009999 !important;
    border-color: #009999 !important;
}

.date-time-container {
    display: flex;
    align-items: center;
    gap: 0;
}

.date-time-container input {
    height: 42px;
}

.date-time-container button {
    height: 42px;
    padding: 0 15px;
    white-space: nowrap;
    font-size: 0.9em;
}

.date-time-container button:hover {
    background-color: #009999;
    color: white;
    border-color: #009999;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .modal-dialog {
        max-width: 95%;
        margin: 10px auto;
    }
    
    .modal-scrollable-content {
        padding: 10px 15px;
    }
    
    .form-control {
        height: 38px !important;
        font-size: 14px !important;
    }
    
    .modal-title {
        font-size: 18px;
    }
    
    .modal-body {
        padding: 15px 10px;
    }
}

/* Field Focus Animation */
@keyframes fieldFocus {
    0% { transform: scale(1); }
    50% { transform: scale(1.02); }
    100% { transform: scale(1); }
}

.form-control:focus {
    animation: fieldFocus 0.3s ease;
}

/* Placeholder styling */
.form-control::placeholder {
    color: #999;
    font-style: italic;
    opacity: 0.7;
}

/* Ensure modal appears higher on screen */
.modal.fade .modal-dialog {
    transform: translate(0, -50px);
}

.modal.show .modal-dialog {
    transform: translate(0, 0);
}

/* Adjust modal vertical positioning */
.modal-dialog {
    display: flex;
    align-items: flex-start;
    min-height: calc(100% - 40px);
    padding-top: 20px;
}
</style>


<div class="modal inmodal" id="LeaveModal" role="dialog" aria-hidden="true" tabindex="-1">
    <input value="" type="hidden" name="Id" id="hdnId"/>
    <div class="modal-dialog">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                    <span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title">Leave Update</h4>
            </div>
            <div class="modal-body">
                <div class="modal-scrollable-content">
                    <div class="col-12 col-lg-11">
                    <!-- First Row - GID, Name, Department -->
 <div class="form-group row">
    <div class="col-md-4">
        <div class="form-group">
             <label>GID <span class="required">*</span></label>
            <input type="text" 
                   class="form-control" 
                   id="gid" 
                   name="gid" 
                   placeholder="Enter GID" 
                   required>
        </div>
    </div>
       
    <div class="col-md-4">
        <div class="form-group">
            <label>Name <span class="required">*</span></label>
            <input type="text" 
                   class="form-control" 
                   id="name" 
                   name="name" 
                   placeholder="Enter Name" 
                   required>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label>Department <span class="required">*</span></label>
             <input type="text" 
                   class="form-control" 
                   id="department" 
                   name="department" 
                   placeholder="Enter Department" 
                   required>
        </div>
    </div>
</div>

<!-- Second Row - Sub-Department, Role, Group Type -->
<div class="form-group row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Sub-Department <span class="required">*</span></label>
            <select name="sub_department" 
                    id="sub_department" 
                    class="form-control" 
                    required>
                <option value="">-- Select Sub-Department --</option>
                <option value="Manufacturing (Lean Line)">Manufacturing (Lean Line)</option>
                <option value="Manufacturing (Closing)">Manufacturing (Closing)</option>
                <option value="Manufacturing (NSD Line)">Manufacturing (NSD Line)</option>
                <option value="Manufacturing (SION)">Manufacturing (SION)</option>
                <option value="Manufacturing (OVCB)">Manufacturing (OVCB)</option>
                <option value="Manufacturing (36KV)">Manufacturing (36KV)</option>
                <option value="Manufacturing (Finishing)">Manufacturing (Finishing)</option>
                <option value="Mechanical Engineering">Mechanical Engineering</option>
                <option value="Product Care">Product Care</option>
                <option value="testing">Testing</option>
                <option value="warehouse">Warehouse</option>
                <option value="packing">Packing</option>
            </select>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label>Role <span class="required">*</span></label>
            <input type="text" 
                   class="form-control" 
                   id="role" 
                   name="role" 
                   placeholder="Enter Role" 
                   required>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label>Group Type <span class="required">*</span></label>
            <select name="group_type" 
                    id="group_type" 
                    class="form-control" 
                    required>
                <option value="">-- Select Group Type --</option>
                <option value="A">Group A</option>
                <option value="B">Group B</option>
                <option value="NA">NA</option>
            </select>
        </div>
    </div>
</div>

<!-- Third Row - Managers and Supervisor -->
<div class="form-group row">
    <div class="col-md-4">
        <div class="form-group">
            <label>In-Company Manager <span class="required">*</span></label>
            <input type="text" 
                   class="form-control" 
                   id="in_company_manager" 
                   name="in_company_manager" 
                   placeholder="Enter In-Company Manager" 
                   required>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="form-group">
            <label>Line Manager <span class="required">*</span></label>
            <input type="text" 
                   class="form-control" 
                   id="line_manager" 
                   name="line_manager" 
                   placeholder="Enter Line Manager" 
                   required>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="form-group">
            <label>Supervisor <span class="required supervisor-required">*</span></label>
            <input type="text" 
                   class="form-control" 
                   id="supervisor" 
                   name="supervisor" 
                   placeholder="Enter Supervisor">
        </div>
    </div>
</div>

<!-- Fourth Row - Sponsor and Leave Type -->
<div class="form-group row">
    <div class="col-lg-4 sponsor-field">
        <div class="form-group">
            <label>Sponsor <span class="required sponsor-required">*</span></label>
            <input type="text" 
                   class="form-control" 
                   id="sponsor" 
                   name="sponsor" 
                   placeholder="Enter Sponsor">
        </div>
    </div>

    <div class="col-lg-4">
        <div class="form-group">
            <label>Type of Leave <span class="required">*</span></label>
            <select name="leave_type" 
                    id="leave_type" 
                    class="form-control" 
                    required>
                <option value="">-- Select Leave Type --</option>
                <option value="IN_Casual Leave">IN_Casual Leave</option>
                <option value="IN_Earned Leave">IN_Earned Leave</option>
                <option value="IN_Education Leave">IN_Education Leave</option>
                <option value="IN_LTA">IN_LTA</option>
                <option value="IN_Outdoor Duty">IN_Outdoor Duty</option>
                <option value="IN_Paternity Leave_Adoption">IN_Paternity Leave_Adoption</option>
                <option value="IN_Paternity Leave_Child Birth">IN_Paternity Leave_Child Birth</option>
                <option value="IN_Sabbatical leave">IN_Sabbatical leave</option>
                <option value="IN_Sick w/o Attachment">IN_Sick w/o Attachment</option>
                <option value="IN_Sick with Attachment">IN_Sick with Attachment</option>
                <option value="IN_Special Leave with Pay">IN_Special Leave with Pay</option>
                <option value="IN_Training/Seminar">IN_Training/Seminar</option>
                <option value="IN_Transfer Leave">IN_Transfer Leave</option>
                <option value="IN_Volunteering Leave">IN_Volunteering Leave</option>
                <option value="IN_Work From Home">IN_Work From Home</option>
            </select>
        </div>
    </div>

    <div class="col-lg-4">
    </div>
</div>

<!-- Fifth Row - Absence Detail -->
<div class="form-group row">
    <div class="col-lg-12">
        <div class="form-group">
            <label>Detail <span class="required">*</span></label>
            <textarea class="form-control" 
                      id="absence_detail" 
                      name="absence_detail" 
                      rows="3" 
                      placeholder="Enter Absence Details"></textarea>
        </div>
    </div>
</div>

<!-- Sixth Row - Date Inputs -->
<div class="form-group row">
    <div class="col-lg-4">
        <div class="form-group">
            <label>Start Date and Time <span class="required">*</span></label>
            <div class="date-time-container">
                <input type="text" 
                       class="form-control" 
                       id="start_date" 
                       name="start_date" 
                       style="width: 100%;"
                       required>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="form-group">
            <label>End Date and Time <span class="required">*</span></label>
            <div class="date-time-container">
                <input type="text" 
                       class="form-control" 
                       id="end_date" 
                       name="end_date" 
                       style="width: 100%;"
                       required>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="form-group">
            <label>Total Leave Days</label>
            <input type="number" 
                   class="form-control" 
                   id="total_days" 
                   name="total_days" 
                   readonly>
        </div>
    </div>
</div>

<!-- Seventh Row - Employment Details (READ-ONLY) -->
<div class="form-group row">
    <div class="col-lg-4">
        <div class="form-group">
            <label>Joined 01.01.2005</label>
            <input type="text" 
                   class="form-control" 
                   id="joined" 
                   name="joined" 
                   readonly>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="form-group">
            <label>Type of Employment</label>
            <input type="text" 
                   class="form-control" 
                   id="employment_type" 
                   name="employment_type" 
                   readonly>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Empty column for spacing -->
    </div>
</div>

</div>
</div>
</div>

            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="updateLeave('<?= pathinfo(end(explode('/', $_SERVER["REQUEST_URI"])), PATHINFO_FILENAME) ?>')">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script type="importmap">
    {
        "imports": {
            "ckeditor5": "https://cdn.ckeditor.com/ckeditor5/43.3.0/ckeditor5.js",
            "ckeditor5/": "https://cdn.ckeditor.com/ckeditor5/43.3.0/"
        }
    }
</script>

<script>

$(document).ready(function () {
    // Toastr settings
    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: "toast-top-right",
        timeOut: 3000
    };

    // Flatpickr common configuration
    const commonConfig = {
        enableTime: true,
        dateFormat: "Y-m-d H:i",
        minuteIncrement: 15,
        time_24hr: true,
        weekNumbers: true,
        showMonths: 1,
        allowInput: true
    };

    // Initialize Start Date Picker
    const startPicker = flatpickr("#start_date", {
        ...commonConfig,
        onChange: function(selectedDates, dateStr) {
            if (selectedDates[0]) {
                validateDates('start_date');
            }
        }
    });

    // Initialize End Date Picker
    const endPicker = flatpickr("#end_date", {
        ...commonConfig,
        onChange: function(selectedDates, dateStr) {
            if (selectedDates[0]) {
                validateDates('end_date');
            }
        }
    });

    // "Now" button for Start Date
    $('#set_start_now').on('click', function () {
        const now = new Date();
        startPicker.setDate(now);
        validateDates('start_date');
    });

    // "Now" button for End Date
    $('#set_end_now').on('click', function () {
        const now = new Date();
        const startDate = startPicker.selectedDates[0];
        
        if (!startDate) {
            toastr.warning("Please select a start date first");
            return;
        }
        
        if (now < startDate) {
            toastr.warning("End date cannot be before start date");
            return;
        }
        
        endPicker.setDate(now);
        validateDates('end_date');
    });

    // Function to validate dates
    function validateDates(changedField) {
        const startDate = startPicker.selectedDates[0];
        const endDate = endPicker.selectedDates[0];

        // If trying to set end date first
        if (changedField === 'end_date' && !startDate) {
            toastr.warning("Please select a start date first");
            endPicker.clear();
            $('#total_days').val('');
            return;
        }

        // If both dates are set, validate them
        if (startDate && endDate) {
            if (endDate <= startDate) {
                toastr.warning("End date must be greater than start date");
                if (changedField === 'end_date') {
                    endPicker.clear();
                }
                $('#total_days').val('');
                return;
            }

            // Calculate days and update displays
            calculateTotalDays(startDate, endDate);
            updateCalendarView(startDate, endDate);
            updateDurationDetails(startDate, endDate);
        }
    }

    // Calculate total days difference
    function calculateTotalDays(startDate, endDate) {
        if (startDate && endDate) {
            const timeDiff = endDate - startDate;
            const daysDiff = Math.ceil(timeDiff / (1000 * 60 * 60 * 24));
            
            if (daysDiff > 30) {
                toastr.warning("Leave request exceeds 30 days. Please check your dates.");
            }

            $('#total_days').val(daysDiff > 0 ? daysDiff : 0);

            // Calculate working days
            const workingDays = calculateWorkingDays(startDate, endDate);
            updateDurationDetails(startDate, endDate, workingDays);
        } else {
            $('#total_days').val('');
        }
    }

    // Calculate working days
    function calculateWorkingDays(start, end) {
        let count = 0;
        const current = new Date(start);
        
        while (current <= end) {
            if (current.getDay() !== 0 && current.getDay() !== 6) {
                count++;
            }
            current.setDate(current.getDate() + 1);
        }
        
        return count;
    }

    // Update duration details
    function updateDurationDetails(start, end, workingDays) {
        const diffTime = Math.abs(end - start);
        const days = Math.floor(diffTime / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diffTime % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diffTime % (1000 * 60 * 60)) / (1000 * 60));

        if (document.getElementById('leave-duration-details')) {
            document.getElementById('leave-duration-details').innerHTML = `
                <div class="duration-info">
                    <strong>Total Duration:</strong> ${days} days, ${hours} hours, ${minutes} minutes<br>
                    <strong>Working Days:</strong> ${workingDays} days<br>
                    <strong>Start:</strong> ${formatDateTime(start)}<br>
                    <strong>End:</strong> ${formatDateTime(end)}
                </div>
            `;
        }
    }

    // Format date time
    function formatDateTime(date) {
        return date.toLocaleString('en-US', {
            weekday: 'short',
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    // Update calendar view
    function updateCalendarView(startDate, endDate) {
        if (startDate && endDate) {
            const timeline = document.getElementById('calendar-timeline');
            if (timeline) {
                timeline.innerHTML = '';

                const dateRange = getDateRange(startDate, endDate);
                const timelineHtml = dateRange.map(date => {
                    const isSelected = date >= startDate && date <= endDate;
                    const isWeekend = date.getDay() === 0 || date.getDay() === 6;
                    
                    return `
                        <div class="day-block ${isSelected ? 'selected' : ''} ${isWeekend ? 'weekend' : ''}">
                            <div class="date">${date.getDate()}</div>
                            <div class="day">${date.toLocaleDateString('en-US', { weekday: 'short' })}</div>
                            <div class="month">${date.toLocaleDateString('en-US', { month: 'short' })}</div>
                        </div>
                    `;
                }).join('');

                timeline.innerHTML = timelineHtml;
            }
        }
    }

    // Get date range helper
    function getDateRange(start, end) {
        const dates = [];
        const current = new Date(start);
        
        while (current <= end) {
            dates.push(new Date(current));
            current.setDate(current.getDate() + 1);
        }
        
        return dates;
    }
});
</script>

<script type="module">
    import {
        ClassicEditor,
        Essentials,
        Paragraph,
        Bold,
        Italic,
        Font,
        Strikethrough
    } from 'ckeditor5';

    // Get the modal element
    const modal = document.getElementById('LeaveModal');
    const modalContent = modal.querySelector('.modal-content');

    // Get the close button and cancel button
    const closeButton = modal.querySelector('.close');
    const cancelButton = modal.querySelector('.btn-white');

    // Add event listeners to prevent the modal from closing
    closeButton.addEventListener('click', () => {
        $('#LeaveModal').modal('hide');
    });

    cancelButton.addEventListener('click', () => {
        $('#LeaveModal').modal('hide');
    });

    // Prevent the modal from closing when clicking inside
    modalContent.addEventListener('click', (event) => {
        event.stopPropagation();
    });

    // Close the modal when clicking outside
    document.addEventListener('click', (event) => {
        if (event.target === modal) {
            $('#LeaveModal').modal('hide');
        }
    });
</script>
</html>