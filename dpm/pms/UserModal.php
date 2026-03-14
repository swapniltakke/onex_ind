<!DOCTYPE html>
<html>
<?php
?>
<link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/43.3.0/ckeditor5.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<style>
/* ============================================
   MODAL BACKDROP - FULL BLUR EFFECT
   ============================================ */
.modal-backdrop {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    z-index: 1040 !important;
    background-color: rgba(0, 0, 0, 0.65) !important;
    backdrop-filter: blur(10px) !important;
    -webkit-backdrop-filter: blur(10px) !important;
}

/* Additional blur overlay for better effect */
.modal-backdrop.show {
    opacity: 1 !important;
}

.modal.inmodal {
    z-index: 1050 !important;
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 100% !important;
    overflow: hidden !important;
}

/* Ensure modal covers entire screen */
.modal.show {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

/* ============================================
   MODAL DIALOG - CENTERED & COMPACT
   ============================================ */
.modal-dialog {
    z-index: 1050 !important;
    margin: 0 auto !important;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    max-height: 90vh;
}

.modal-dialog.modal-lg {
    max-width: 800px;
    width: calc(100% - 40px);
    transform: none !important;
}

/* Modal positioning - Centered with animation */
.modal.fade .modal-dialog {
    transform: translateY(-50px) scale(0.95);
    transition: transform 0.3s ease-out, opacity 0.3s ease-out;
    opacity: 0;
}

.modal.show .modal-dialog {
    transform: translateY(0) scale(1);
    opacity: 1;
}

/* Modal Content */
.modal-content {
    width: 100%;
    margin: auto;
    border-radius: 6px;
    box-shadow: 0 15px 50px rgba(0, 0, 0, 0.4);
    border: none;
    z-index: 1051 !important;
    position: relative;
    animation: modalFadeIn 0.3s ease-out;
}

@keyframes modalFadeIn {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

/* Modal Header - Compact */
.modal-header {
    background: linear-gradient(135deg, #009999 0%, #006666 100%);
    color: white;
    padding: 8px 16px;
    text-align: center;
    border-bottom: 2px solid #007777;
    min-height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-radius: 6px 6px 0 0;
}

.modal-title {
    font-size: 17px;
    font-weight: 600;
    margin: 0;
    padding: 0;
    line-height: 1.2;
    letter-spacing: 0.4px;
}

/* Close button */
.modal-header .close {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    margin: 0;
    padding: 2px 8px;
    color: white;
    font-size: 24px;
    opacity: 0.9;
    transition: all 0.3s ease;
    line-height: 1;
    background: transparent;
    border: none;
}

.modal-header .close:hover {
    opacity: 1;
    transform: translateY(-50%) scale(1.15);
    text-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
}

/* Modal Body and Scrollable Content - Compact */
.modal-scrollable-content {
    width: 100%;
    padding: 10px 16px;
}

.col-12.col-lg-11 {
    padding: 0;
    margin: 0 auto;
    max-width: 100%;
}

/* Modal Body - Compact */
.modal-body {
    position: relative;
    padding: 10px;
    display: flex;
    justify-content: center;
    background-color: #f8f9fa;
    max-height: calc(90vh - 120px);
    overflow-y: auto;
    overflow-x: hidden;
}

/* Custom Scrollbar */
.modal-body::-webkit-scrollbar {
    width: 5px;
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

/* Form Group Styling - Compact */
.form-group {
    margin-bottom: 5px;
}

.form-group label {
    font-weight: 600;
    font-size: 10px;
    color: #333;
    margin-bottom: 2px;
    display: block;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.form-group label .required {
    color: #e74c3c;
    margin-left: 2px;
}

/* Input and Select Styling - Very Compact */
.form-control {
    height: 30px !important;
    font-size: 12px !important;
    padding: 4px 8px !important;
    border: 1px solid #ddd !important;
    border-radius: 3px !important;
    transition: all 0.2s ease !important;
    background-color: white !important;
}

.form-control:focus {
    border-color: #009999 !important;
    box-shadow: 0 0 0 0.1rem rgba(0, 153, 153, 0.1) !important;
    outline: none !important;
}

.form-control:hover:not(:disabled) {
    border-color: #009999 !important;
}

/* Select Dropdown - Compact */
select.form-control {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8' viewBox='0 0 12 12'%3E%3Cpath fill='%23009999' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 8px center;
    background-size: 8px;
    padding-right: 25px !important;
    cursor: pointer;
}

select.form-control option {
    padding: 6px;
    font-size: 12px;
}

/* Date Input Styling */
input[type="date"].form-control {
    cursor: pointer;
}

/* Disabled field styling */
input:disabled, select:disabled {
    background-color: #e9ecef !important;
    cursor: not-allowed !important;
    opacity: 0.7 !important;
    border-color: #ced4da !important;
}

/* Row Spacing - Very Compact */
.form-group.row {
    margin-bottom: 3px;
}

.col-md-4, .col-md-6, .col-lg-4 {
    padding-left: 4px !important;
    padding-right: 4px !important;
}

/* Modal Footer - Compact */
.modal-footer {
    background-color: #f8f9fa;
    padding: 8px 16px;
    border-top: 1px solid #dee2e6;
    text-align: right;
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    border-radius: 0 0 6px 6px;
}

.modal-footer button {
    font-size: 12px;
    padding: 6px 16px;
    border-radius: 3px;
    font-weight: 600;
    transition: all 0.3s ease;
    min-width: 80px;
}

/* Custom button styles */
.btn-white {
    background-color: white;
    border: 1px solid #ccc;
    color: #555;
}

.btn-white:hover {
    background-color: #f1f1f1;
    border-color: #999;
    box-shadow: 0 2px 5px rgba(0,0,0,0.15);
    transform: translateY(-1px);
}

.btn-primary {
    background-color: #009999;
    border: 1px solid #009999;
    color: white;
}

.btn-primary:hover {
    background-color: #007777;
    border-color: #007777;
    box-shadow: 0 2px 5px rgba(0,153,153,0.3);
    transform: translateY(-1px);
}

/* Placeholder styling */
.form-control::placeholder {
    color: #999;
    font-style: italic;
    opacity: 0.6;
}

/* Responsive adjustments */
@media (max-width: 1024px) {
    .modal-dialog.modal-lg {
        max-width: 85%;
    }
}

@media (max-width: 768px) {
    .modal-dialog.modal-lg {
        max-width: 95%;
        margin: 0 auto !important;
    }
    
    .modal-body {
        max-height: calc(90vh - 100px);
    }
    
    .modal-scrollable-content {
        padding: 8px 12px;
    }
    
    .form-control {
        height: 28px !important;
        font-size: 11px !important;
    }
    
    .modal-title {
        font-size: 15px;
    }
    
    .modal-body {
        padding: 8px;
    }
    
    .col-md-4, .col-md-6, .col-lg-4 {
        padding-left: 3px !important;
        padding-right: 3px !important;
    }
}

/* Field Focus Animation */
@keyframes fieldFocus {
    0% { transform: scale(1); }
    50% { transform: scale(1.005); }
    100% { transform: scale(1); }
}

.form-control:focus {
    animation: fieldFocus 0.2s ease;
}

/* Reduce overall modal size */
.modal-body {
    font-size: 12px;
}

.form-group {
    line-height: 1.2;
}

/* ============================================
   DUAL MODE FIELD STYLES (Display/Edit)
   ============================================ */

.dual-mode-field {
    position: relative;
    width: 100%;
}

/* Display Mode - Shows existing data as text */
.field-display-mode {
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 30px;
    padding: 4px 8px;
    border: 1px solid #ddd;
    border-radius: 3px;
    background-color: #f8f9fa;
    cursor: pointer;
    transition: all 0.2s ease;
}

.field-display-mode:hover {
    border-color: #009999;
    background-color: #e8f5f5;
}

.field-display-text {
    flex: 1;
    font-size: 12px;
    color: #333;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.field-edit-icon {
    color: #009999;
    font-size: 13px;
    margin-left: 6px;
    opacity: 0.6;
    transition: opacity 0.2s;
    flex-shrink: 0;
}

.field-display-mode:hover .field-edit-icon {
    opacity: 1;
}

/* Edit Mode - Shows searchable dropdown */
.field-edit-mode {
    display: none;
}

.field-edit-mode.active {
    display: block;
}

.field-display-mode.hidden {
    display: none;
}

/* Manager Input Container */
.manager-input-container {
    position: relative;
    width: 100%;
}

/* Suggestions Container */
.suggestions-container {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ccc;
    border-top: none;
    max-height: 180px;
    overflow-y: auto;
    z-index: 1100;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    display: none;
    border-radius: 0 0 3px 3px;
}

.suggestion-item {
    padding: 8px 10px;
    cursor: pointer;
    border-bottom: 1px solid #eee;
    font-size: 12px;
    transition: background-color 0.2s ease;
}

.suggestion-item:hover {
    background-color: #009999;
    color: white;
}

.suggestion-item.active {
    background-color: #009999;
    color: white;
}

.suggestion-item strong {
    font-weight: bold;
}

.suggestion-item:last-child {
    border-bottom: none;
}

/* Supervisor Tags Display Mode */
.supervisor-display-mode {
    display: flex;
    flex-wrap: wrap;
    gap: 3px;
    min-height: 30px;
    padding: 4px 8px;
    border: 1px solid #ddd;
    border-radius: 3px;
    background-color: #f8f9fa;
    cursor: pointer;
    transition: all 0.2s ease;
    align-items: center;
}

.supervisor-display-mode:hover {
    border-color: #009999;
    background-color: #e8f5f5;
}

.supervisor-display-tag {
    background-color: #009999;
    color: white;
    padding: 3px 6px;
    border-radius: 2px;
    font-size: 10px;
    white-space: nowrap;
}

.supervisor-display-mode .field-edit-icon {
    margin-left: auto;
}

/* Supervisor Tags Edit Mode */
.supervisor-edit-mode {
    display: none;
}

.supervisor-edit-mode.active {
    display: block;
}

.supervisor-display-mode.hidden {
    display: none;
}

/* ============================================
   SUPERVISOR TAGS INPUT - MINIMAL SCROLLBAR
   ============================================ */

.supervisor-tags-input {
    display: flex;
    flex-wrap: nowrap !important;
    align-items: center;
    gap: 3px;
    min-height: 30px !important;
    max-height: 30px !important;
    padding: 4px 8px;
    border: 1px solid #ddd;
    border-radius: 3px;
    background: #fff;
    transition: all 0.2s ease;
    overflow-x: auto !important;
    overflow-y: hidden !important;
    scrollbar-width: thin;
    scrollbar-color: rgba(0, 153, 153, 0.3) transparent;
}

/* Minimal Scrollbar */
.supervisor-tags-input::-webkit-scrollbar {
    height: 3px !important;
}

.supervisor-tags-input::-webkit-scrollbar-track {
    background: transparent;
}

.supervisor-tags-input::-webkit-scrollbar-thumb {
    background: rgba(0, 153, 153, 0.3);
    border-radius: 10px;
}

.supervisor-tags-input::-webkit-scrollbar-thumb:hover {
    background: rgba(0, 153, 153, 0.5);
}

.supervisor-tags-input:hover {
    border-color: #009999 !important;
}

.supervisor-tags-input:focus-within {
    border-color: #009999 !important;
    box-shadow: 0 0 0 0.1rem rgba(0, 153, 153, 0.1) !important;
}

.selected-items-inline {
    display: flex;
    flex-wrap: nowrap !important;
    gap: 3px;
    align-items: center;
    flex-shrink: 0;
}

.selected-supervisor-item-inline {
    background-color: #009999 !important;
    color: white !important;
    padding: 3px 6px !important;
    border-radius: 2px;
    font-size: 10px !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 3px !important;
    white-space: nowrap !important;
    flex-shrink: 0 !important;
}

.selected-supervisor-item-inline .remove-supervisor {
    cursor: pointer;
    opacity: 0.8;
    font-weight: bold;
    font-size: 12px;
    transition: opacity 0.2s;
    margin-left: 2px;
}

.selected-supervisor-item-inline .remove-supervisor:hover {
    opacity: 1;
}

.form-control-inline {
    flex: 1;
    min-width: 70px;
    border: none !important;
    outline: none !important;
    box-shadow: none !important;
    padding: 2px 4px !important;
    font-size: 12px !important;
    background: transparent !important;
    height: auto !important;
}

.form-control-inline:focus {
    border: none !important;
    outline: none !important;
    box-shadow: none !important;
}

.supervisor-tags-input.has-tags .form-control-inline::placeholder {
    color: transparent;
}

/* Loading Indicator */
#loading-indicator {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(0,0,0,0.85);
    color: white;
    padding: 20px 30px;
    border-radius: 6px;
    z-index: 9999;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
}

/* Existing data indicator */
.has-existing-data {
    background-color: #e8f5e9 !important;
    border-color: #4caf50 !important;
}

/* ============================================
   PREVENT BODY SCROLL WHEN MODAL IS OPEN
   ============================================ */
body.modal-open {
    overflow: hidden !important;
    padding-right: 0 !important;
}

</style>

<div class="modal inmodal" id="UserModal" role="dialog" aria-hidden="true" tabindex="-1">
    <input value="" type="hidden" name="Id" id="hdnId"/>
    <div class="modal-dialog modal-lg">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                    <span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title">User Update</h4>
            </div>
            <div class="modal-body">
                <div class="modal-scrollable-content">
                    <div class="col-12 col-lg-11">
                    
                    <!-- Row 1 - GID, Name, Department -->
                    <div class="form-group row">
                        <div class="col-md-4">
                            <label>GID <span class="required">*</span></label>
                            <input type="text" class="form-control" id="gid" name="gid" placeholder="GID" required readonly>
                        </div>
                        <div class="col-md-4">
                            <label>Name <span class="required">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="Name" required readonly>
                        </div>
                        <div class="col-md-4">
                            <label>Department <span class="required">*</span></label>
                            <input type="text" class="form-control" id="department" name="department" placeholder="Department" required readonly>
                        </div>
                    </div>

                    <!-- Row 2 - Sub-Department, Role, Group Type -->
                    <div class="form-group row">
                        <div class="col-md-4">
                            <label>Sub-Department <span class="required">*</span></label>
                            <select name="sub_department" id="sub_department" class="form-control" required>
                                <option value="">-- Select --</option>
                                <option value="700">700</option>
                                <option value="704">704</option>
                                <option value="720">720</option>
                                <option value="750">750</option>
                                <option value="Mechanical Engineering">Mechanical Engineering</option>
                                <option value="Product Care">Product Care</option>
                                <option value="warehouse">Warehouse</option>
                                <option value="packing">Packing</option>
                                <option value="QC - AISP Domestic">QC - AISP Domestic</option>
                                <option value="QC - AISP Export">QC - AISP Export</option>
                                <option value="QC - AISP TF">QC - AISP TF</option>
                                <option value="QC - AISP">QC - AISP</option>
                                <option value="QC - SD">QC - SD</option>
                                <option value="QC - INSP">QC - INSP</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Role <span class="required">*</span></label>
                            <input type="text" class="form-control" id="role" name="role" placeholder="Role" required readonly>
                        </div>
                        <div class="col-md-4">
                            <label>Group Type <span class="required">*</span></label>
                            <select name="group_type" id="group_type" class="form-control" required>
                                <option value="">-- Select --</option>
                                <option value="A">Group A</option>
                                <option value="B">Group B</option>
                                <option value="NA">NA</option>
                            </select>
                        </div>
                    </div>

                    <!-- Row 3 - Managers with Dual Mode -->
                    <div class="form-group row">
                        <!-- In-Company Manager -->
                        <div class="col-md-4">
                            <label>In-Company Manager </label>
                            <div class="dual-mode-field">
                                <!-- Display Mode -->
                                <div class="field-display-mode" id="in_company_manager_display" onclick="switchToEditMode('in_company_manager')">
                                    <span class="field-display-text" id="in_company_manager_display_text">-</span>
                                    <i class="fa fa-pencil field-edit-icon"></i>
                                </div>
                                
                                <!-- Edit Mode -->
                                <div class="field-edit-mode" id="in_company_manager_edit">
                                    <div class="manager-input-container">
                                        <input type="text" class="form-control" id="in_company_manager_search" placeholder="Type to search..." autocomplete="off">
                                        <div id="in_company_manager_suggestions" class="suggestions-container"></div>
                                    </div>
                                </div>
                                
                                <input type="hidden" id="in_company_manager" name="in_company_manager" required>
                            </div>
                        </div>

                        <!-- Line Manager -->
                        <div class="col-md-4">
                            <label>Line Manager </label>
                            <div class="dual-mode-field">
                                <!-- Display Mode -->
                                <div class="field-display-mode" id="line_manager_display" onclick="switchToEditMode('line_manager')">
                                    <span class="field-display-text" id="line_manager_display_text">-</span>
                                    <i class="fa fa-pencil field-edit-icon"></i>
                                </div>
                                
                                <!-- Edit Mode -->
                                <div class="field-edit-mode" id="line_manager_edit">
                                    <div class="manager-input-container">
                                        <input type="text" class="form-control" id="line_manager_search" placeholder="Type to search..." autocomplete="off">
                                        <div id="line_manager_suggestions" class="suggestions-container"></div>
                                    </div>
                                </div>
                                
                                <input type="hidden" id="line_manager" name="line_manager" required>
                            </div>
                        </div>

                        <!-- Supervisor Multiple Select -->
                        <div class="col-md-4">
                            <label>Supervisor </label>
                            <div class="dual-mode-field">
                                <!-- Display Mode -->
                                <div class="supervisor-display-mode" id="supervisor_display" onclick="switchToEditMode('supervisor')">
                                    <div id="supervisor_display_tags" style="display: flex; flex-wrap: wrap; gap: 3px; flex: 1;">
                                        <span class="field-display-text">-</span>
                                    </div>
                                    <i class="fa fa-pencil field-edit-icon"></i>
                                </div>
                                
                                <!-- Edit Mode -->
                                <div class="supervisor-edit-mode" id="supervisor_edit">
                                    <div class="manager-input-container">
                                        <div id="supervisor_input_wrapper" class="supervisor-tags-input">
                                            <div id="selected_supervisors_inline" class="selected-items-inline"></div>
                                            <input type="text" id="supervisor_search" class="form-control-inline" placeholder="Type to search (Max 5)..." autocomplete="off">
                                        </div>
                                        <div id="supervisor_suggestions" class="suggestions-container"></div>
                                    </div>
                                </div>
                                
                                <input type="hidden" id="supervisor" name="supervisor" required>
                            </div>
                        </div>
                    </div>

                    <!-- Row 4 - Sponsor, Shift Type, Temp Sub-Department -->
                    <div class="form-group row">
                        <!-- Sponsor with Dual Mode -->
                        <div class="col-md-4">
                            <label>Sponsor </label>
                            <div class="dual-mode-field">
                                <!-- Display Mode -->
                                <div class="field-display-mode" id="sponsor_display" onclick="switchToEditMode('sponsor')">
                                    <span class="field-display-text" id="sponsor_display_text">-</span>
                                    <i class="fa fa-pencil field-edit-icon"></i>
                                </div>
                                
                                <!-- Edit Mode -->
                                <div class="field-edit-mode" id="sponsor_edit">
                                    <div class="manager-input-container">
                                        <input type="text" class="form-control" id="sponsor_search" placeholder="Type to search..." autocomplete="off">
                                        <div id="sponsor_suggestions" class="suggestions-container"></div>
                                    </div>
                                </div>
                                
                                <input type="hidden" id="sponsor" name="sponsor" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label>Shift Type</label>
                            <select name="shift_type" id="shift_type" class="form-control" required>
                                <option value="">-- Select --</option>
                                <option value="1">Shift 1</option>
                                <option value="2">Shift 2</option>
                                <option value="3">Shift 3</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Temp Sub-Department </label>
                            <select name="temp_sub_department" id="temp_sub_department" class="form-control" required>
                                <option value="">-- Select --</option>
                                <option value="700">700</option>
                                <option value="704">704</option>
                                <option value="720">720</option>
                                <option value="750">750</option>
                                <option value="Mechanical Engineering">Mechanical Engineering</option>
                                <option value="Product Care">Product Care</option>
                                <option value="warehouse">Warehouse</option>
                                <option value="packing">Packing</option>
                                <option value="QC - AISP Domestic">QC - AISP Domestic</option>
                                <option value="QC - AISP Export">QC - AISP Export</option>
                                <option value="QC - AISP TF">QC - AISP TF</option>
                                <option value="QC - AISP">QC - AISP</option>
                                <option value="QC - SD">QC - SD</option>
                                <option value="QC - INSP">QC - INSP</option>
                            </select>
                        </div>
                    </div>

                    <!-- Row 5 - Temp Group Type, Employment Type, Joined -->
                    <div class="form-group row">
                        <div class="col-md-4">
                            <label>Temp Group Type </label>
                            <select name="temp_group_type" id="temp_group_type" class="form-control" required>
                                <option value="">-- Select --</option>
                                <option value="A">Group A</option>
                                <option value="B">Group B</option>
                                <option value="NA">NA</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Employment Type <span class="required">*</span></label>
                            <select name="employment_type" id="employment_type" class="form-control" required>
                                <option value="">-- Select --</option>
                                <option value="Blue Collar">Blue Collar</option>
                                <option value="Blue Collar Learner">Blue Collar Learner</option>
                                <option value="Blue Collar Trainee">Blue Collar Trainee</option>
                                <option value="Blue Collar Contract">Blue Collar Contract</option>
                                <option value="White Collar">White Collar</option>
                                <option value="White Collar Contract">White Collar Contract</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Joined 01.01.2005 <span class="required">*</span></label>
                            <select name="joined" id="joined" class="form-control" required>
                                <option value="">-- Select --</option>
                            </select>
                        </div>
                    </div>

                    <!-- Row 6 - Date Range -->
                    <div class="form-group row">
                        <div class="col-md-4">
                            <label>Transfer From Date</label>
                            <input type="date" id="transfer_from_date" name="transfer_from_date" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label>Transfer To Date</label>
                            <input type="date" id="transfer_to_date" name="transfer_to_date" class="form-control">
                        </div>
                    </div>

                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="updateUser('<?= pathinfo(end(explode('/', $_SERVER["REQUEST_URI"])), PATHINFO_FILENAME) ?>')">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Loading Indicator -->
<div id="loading-indicator">
    <i class="fa fa-spinner fa-spin" style="font-size: 24px; margin-right: 10px;"></i>
    Loading...
</div>

<script type="importmap">
    {
        "imports": {
            "ckeditor5": "https://cdn.ckeditor.com/ckeditor5/43.3.0/ckeditor5.js",
            "ckeditor5/": "https://cdn.ckeditor.com/ckeditor5/43.3.0/"
        }
    }
</script>

<script src="js/usermodal.js"></script>

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

    const modal = document.getElementById('UserModal');
    const modalContent = modal.querySelector('.modal-content');
    const closeButton = modal.querySelector('.close');
    const cancelButton = modal.querySelector('.btn-white');

    closeButton.addEventListener('click', () => {
        $('#UserModal').modal('hide');
    });

    cancelButton.addEventListener('click', () => {
        $('#UserModal').modal('hide');
    });

    modalContent.addEventListener('click', (event) => {
        event.stopPropagation();
    });

    document.addEventListener('click', (event) => {
        if (event.target === modal) {
            $('#UserModal').modal('hide');
        }
    });
</script>
</html>