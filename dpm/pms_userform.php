<!DOCTYPE html>
<html>
<?php

// Check module 20 access - catch the redirect
ob_start();
try {
    SharedManager::checkAuthToModule(20);
    $isPMSAdmin = true;
} catch (Exception $e) {
    $isPMSAdmin = false;
}
ob_end_clean();

include_once 'core/index.php';
?>
<head>
    <!-- Font Awesome FIRST -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" integrity="sha512-SfTiTlX6kk+qitfevl/7LibUOeJWlt9rbyDn92a1DqWOw9vWG2MFoays0sgObmWazO5BQPiFucnnEAjpAB+/Sw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <link href="../css/semantic.min.css" rel="stylesheet"/>
    <link rel="stylesheet" type="text/css" href="../css/dataTables.semanticui.min.css">
    <link rel="stylesheet" type="text/css" href="../css/responsive.dataTables.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <link href="../css/main.css?13" rel="stylesheet"/>
    <?php $menu_header_display = 'PMS Module'; ?>
    <?php include_once 'shared/headerStyles.php' ?>
    <?php include_once '../assemblynotes/shared/headerScripts.php' ?>

<style>
/* CRITICAL: Prevent wildcard from overriding Font Awesome */
body, h1, h2, h3, h4, h5, h6, p, div:not(.fa), span:not(.fa), a:not(.fa), button, input, select, textarea,
.nav-label, label, .field {
    font-family: 'Siemens Sans', Arial, sans-serif !important;
}

/* Force Font Awesome to use its own font */
.fa, i.fa, span.fa,
.fa:before, i.fa:before, span.fa:before,
.fa:after, i.fa:after, span.fa:after {
    font-family: 'FontAwesome' !important;
    font-style: normal !important;
    font-weight: normal !important;
    font-variant: normal !important;
    text-transform: none !important;
    speak: none;
    line-height: 1;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    display: inline-block;
}

/* COMPACT FORM - Fit on one page */
.ui.form .field {
    margin-bottom: 0.6em !important;
}

.ui.form .fields {
    margin-bottom: 0.4em !important;
}

.ui.form .field > label {
    margin-bottom: 0.25em !important;
    font-size: 12.5px !important;
    line-height: 1.2 !important;
}

.ui.form input,
.ui.form select,
.ui.dropdown {
    font-size: 12.5px !important;
    padding: 0.55em 0.75em !important;
    min-height: 32px !important;
}

.ui.dividing.header {
    margin-top: 0.3em !important;
    margin-bottom: 0.7em !important;
    padding-bottom: 0.4em !important;
    font-size: 1.2em !important;
}

.ibox-content {
    padding: 12px 18px 40px 18px !important;
}

.row {
    margin-bottom: 0 !important;
}

.border-bottom {
    margin-bottom: 8px !important;
}

.required-field::after {
    content: " *";
    color: red;
    font-weight: bold;
}    

/* Common styles for all form inputs, selects, and dropdowns */
.form-control,
.ui.dropdown,
.ui.form input,
.ui.form select {
    transition: all 0.2s ease !important;
}

/* Hover and focus states for all form elements */
.form-control:hover,
.ui.dropdown:hover,
.ui.form input:hover,
.ui.form select:hover,
.ui.form select:focus {
    border-color: #1ab394 !important;
    box-shadow: 0 0 0 0.2rem rgba(26, 179, 148, 0.25) !important;
    outline: none !important;
}

/* Submit button styling - SMALLER & CENTERED */
.btn-primary {
    background-color: #1ab394 !important;
    border-color: #1ab394 !important;
    color: white !important;
    padding: 8px 30px !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    border-radius: 3px !important;
    transition: all 0.3s ease !important;
    cursor: pointer !important;
    display: inline-block !important;
}

.btn-primary:hover {
    background-color: #18a689 !important;
    border-color: #18a689 !important;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(26, 179, 148, 0.4) !important;
}

.btn-primary:active {
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(26, 179, 148, 0.3) !important;
}

.btn-primary i.fa {
    margin-right: 6px;
    font-size: 12px;
}

/* Style for dropdown items on hover and when selected */
.ui.dropdown .menu > .item:hover,
.ui.dropdown .menu > .item.selected,
.ui.dropdown .menu > .item:active {
    background-color: #1ab394 !important;
    color: white !important;
}

/* Style for the selected item in the dropdown */
.ui.dropdown .menu > .item.active {
    background-color: #1ab394 !important;
    color: white !important;
    font-weight: bold !important;
}

/* Style for the dropdown when focused/active */
.ui.dropdown:focus,
.ui.dropdown.active {
    border-color: #1ab394 !important;
}

/* Style for the dropdown arrow when hovering */
.ui.dropdown:hover .dropdown.icon {
    color: #1ab394 !important;
}

/* Style for the selected text in the dropdown */
.ui.dropdown.selected,
.ui.dropdown .menu .selected.item {
    background-color: #1ab394 !important;
    color: white !important;
}

/* Add a subtle transition effect */
.ui.dropdown .menu > .item {
    transition: background-color 0.2s ease, color 0.2s ease !important;
}

/* Style for disabled options */
.ui.dropdown .menu > .item.disabled {
    opacity: 0.5 !important;
    background: #f9f9f9 !important;
    color: rgba(0, 0, 0, 0.4) !important;
}

/* Style for the dropdown search input if using searchable dropdowns */
.ui.dropdown.search > input.search {
    border-color: #1ab394 !important;
}

/* Style for the dropdown when error state */
.ui.dropdown.error {
    border-color: #db2828 !important;
}

/* Style for the dropdown menu border */
.ui.dropdown .menu {
    border-color: #1ab394 !important;
    box-shadow: 0 2px 4px rgba(26, 179, 148, 0.2) !important;
}

.suggestions-container {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ccc;
    border-top: none;
    height: 200px;
    overflow-y: auto;
    z-index: 1000;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

    .suggestion-item {
    padding: 8px 12px;
    cursor: pointer;
    border-bottom: 1px solid #eee;
    height: 40px;
    line-height: 24px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    transition: background-color 0.2s ease; 
}

/* ✅ ADD THIS - Prevent supervisor suggestions from wrapping */
#supervisor_suggestions .suggestion-item {
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    display: block !important;
}

#supervisor_suggestions .suggestion-item strong {
    white-space: nowrap !important;
}
.suggestion-item:hover {
    background-color: #1ab394;
    color: white;
}

.suggestion-item.active {
    background-color: #1ab394;
    color: white;
}

.suggestion-item:hover strong,
.suggestion-item.active strong {
    color: #ffffff;
    font-weight: bold;
    text-decoration: underline;
}

.suggestion-item:last-child {
    border-bottom: none;
}

.field {
    position: relative;
}

.gid-input-container {
    position: relative;
    display: inline-block;
    width: 100%;
}

#loading-indicator {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(0,0,0,0.8);
    color: white;
    padding: 20px;
    border-radius: 5px;
    z-index: 9999;
}

/* Manager/sponsor sections - COMPACT */
.manager-section, .sponsor-section {
    transition: opacity 0.3s ease, background-color 0.3s ease;
    padding: 8px;
    border-radius: 5px;
    margin-bottom: 0.4em;
}

.manager-section {
    background-color: #f9f9f9;
}

.sponsor-section {
    background-color: #f0f7ff;
}

.section-disabled {
    opacity: 0.5;
    background-color: #f5f5f5;
    pointer-events: none;
}

.section-enabled {
    opacity: 1;
}

.section-note {
    font-size: 11px;
    margin-top: 2px;
    color: #666;
    font-style: italic;
}

.ui.dropdown .text,
.ui.dropdown .menu .item,
.ui.dropdown.active > .text,
.ui.dropdown .menu > .item.selected {
    color: #000000 !important;
    font-style: normal;
}

/* Only placeholder text should be grey */
.ui.dropdown .default.text,
select option:first-child {
    color: #888888 !important;
    font-style: italic;
}

/* Ensure disabled dropdowns maintain proper styling */
.ui.disabled.dropdown {
    opacity: 0.5 !important;
    pointer-events: none !important;
    background-color: #f5f5f5 !important;
}

/* Button container - COMPACT, CENTERED, SHIFTED UP */
.button-container {
    margin-top: 12px !important;
    margin-bottom: 8px !important;
    padding: 8px 0 !important;
    text-align: center;
}

/* Footer positioning fix */
#page-wrapper {
    position: relative;
    min-height: 100vh;
    padding-bottom: 50px;
}

.footer {
    position: absolute;
    bottom: 0;
    width: 100%;
    background-color: white;
    border-top: 1px solid #e7eaec;
    padding: 8px 15px;
    font-size: 12px;
}

/* Compact ibox */
.ibox {
    margin-bottom: 10px !important;
}

.mb-0 {
    margin-bottom: 0 !important;
}

.ui.multiple.dropdown > .label .delete.icon:hover {
    opacity: 1;
}

.ui.multiple.dropdown > .text {
    line-height: 1.2 !important;
}

/* Limit dropdown menu height for better UX */
.ui.dropdown .menu {
    max-height: 250px !important;
    overflow-y: auto !important;
}

.ui.multiple.dropdown.disabled > .label {
    background-color: #999 !important;
}

.supervisor-delete:before {
    content: "\f00d" !important;
    font-family: 'FontAwesome' !important;
}

/* Supervisor input container */
.supervisor-input-container {
    position: relative;
    display: inline-block;
    width: 100%;
}

/* Supervisor tags input wrapper - PREVENT WRAPPING */
.supervisor-tags-input {
    display: flex;
    flex-wrap: nowrap !important;
    align-items: center;
    gap: 4px;
    min-height: 38px !important;
    max-height: 38px !important;
    padding: 4px 8px;
    border: 1px solid rgba(34, 36, 38, 0.15);
    border-radius: 0.28571429rem;
    background: #fff;
    transition: all 0.2s ease;
    overflow-x: auto !important;
    overflow-y: hidden !important;
}

.supervisor-tags-input:hover {
    border-color: #1ab394 !important;
    box-shadow: 0 0 0 0.2rem rgba(26, 179, 148, 0.25) !important;
}

.supervisor-tags-input:focus-within {
    border-color: #1ab394 !important;
    box-shadow: 0 0 0 0.2rem rgba(26, 179, 148, 0.25) !important;
}

/* Selected items inline (inside input) - PREVENT WRAPPING */
.selected-items-inline {
    display: flex;
    flex-wrap: nowrap !important;
    gap: 4px;
    align-items: center;
    flex-shrink: 0;
}

/* Inline supervisor tags - PREVENT WRAPPING */
.selected-supervisor-item-inline {
    background-color: #1ab394 !important;
    color: white !important;
    padding: 4px 8px !important;
    border-radius: 3px;
    font-size: 11px !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    white-space: nowrap !important;
    flex-shrink: 0 !important;
    max-width: none !important;
}

.selected-supervisor-item-inline span:first-child {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.selected-supervisor-item-inline .remove-supervisor {
    cursor: pointer;
    opacity: 0.8;
    font-weight: bold;
    font-size: 14px;
    line-height: 1;
    transition: opacity 0.2s;
    margin-left: 2px;
    flex-shrink: 0;
}

.selected-supervisor-item-inline .remove-supervisor:hover {
    opacity: 1;
}

/* Inline input field */
.form-control-inline {
    flex: 1;
    min-width: 150px;
    border: none !important;
    outline: none !important;
    box-shadow: none !important;
    padding: 4px !important;
    font-size: 12.5px !important;
    background: transparent !important;
}

.form-control-inline:focus {
    border: none !important;
    outline: none !important;
    box-shadow: none !important;
}

/* Hide placeholder when tags are present */
.supervisor-tags-input.has-tags .form-control-inline::placeholder {
    color: transparent;
}

.selected-supervisor-item .remove-supervisor:hover {
    opacity: 1;
}

/* Match sponsor dropdown height to supervisor field */
#sponsor.ui.dropdown,
#sponsor.ui.dropdown > .dropdown.icon {
    min-height: 38px !important;
    height: 38px !important;
}

#sponsor.ui.dropdown > .text,
#sponsor.ui.dropdown > .default.text {
    line-height: 38px !important;
    padding-top: 0 !important;
    padding-bottom: 0 !important;
}

/* Match supervisor field height */
.supervisor-tags-input {
    min-height: 38px !important;
}

.two.fields {
  display: flex;
  gap: 1rem;
}

.two.fields .field {
  flex: 1;
}

#sponsorField {
  flex: 2.5;
}

.supervisor-input-container,
#supervisor_input_wrapper,
#supervisor_search,
#sponsor {
  width: 100%;
  box-sizing: border-box;
}

#sponsor {
  width: 100% !important;
  box-sizing: border-box;
}

.two.fields > .field {
  width: 50% !important;
}

#supervisor_input_wrapper {
  width: 100%;
}

#supervisor_search {
  width: 100%;
}

#sponsor {
  width: 100% !important;
}

/* Prevent supervisor suggestions from wrapping - keep on single line */
#supervisor_suggestions .suggestion-item {
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    display: block !important;
}

/* Ensure highlighted text doesn't break the single-line layout */
#supervisor_suggestions .suggestion-item strong {
    white-space: nowrap !important;
}

</style>
</head>
<body>
<div id="wrapper">
    <?php $activePage = '/dpm/pms_userform.php'; ?>
    <?php include_once 'shared/pms_sidebar.php' ?>
    
    <div id="page-wrapper" class="gray-bg">
        <div class="row border-bottom">
            <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">
                <div class="navbar-header">
                    <a class="navbar-minimalize minimalize-styl-2 btn btn-primary" href="#"><i class="fa fa-bars"></i></a>
                </div>
                <ul class="nav navbar-top-links navbar-right">
                    <li>
                        <h2 style="text-align: left;">User Registration Form</h2>
                    </li>
                </ul>
            </nav>
        </div>
        
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox mb-0">
                    <div id="headersegment">
                        <div class="ibox-content">
                            <form class="ui form" method="post" id="registrationForm" novalidate>
                                <input type="hidden" name="action" value="submitRegistration">
                                <h3 class="ui dividing header">Register New User</h3>
                                
                                <div class="two fields">
                                    <div class="field">
                                        <label class="required-field">GID</label>                                        
                                        <div class="gid-input-container">
                                            <input id="gid" name="gid" class="form-control required" value="" placeholder="-- Type to search GID --" autocomplete="off" required>
                                            <div id="gid_suggestions" class="suggestions-container" style="display: none;"></div>
                                        </div>                                        
                                    </div>
                                    <div class="field">
                                        <label class="required-field">Name</label>
                                        <input type="text" id="name" name="name" class="form-control" required>
                                    </div>
                                </div>
                                
                                <div class="two fields">
                                    <div class="field">
                                        <label class="required-field">Department</label>
                                        <select name="department" id="department" class="ui dropdown search" required>
                                            <option value="">-- Select Department --</option>
                                        </select>
                                    </div>
                                    <div class="field">
                                        <label class="required-field">Sub-Department</label>
                                        <select name="sub_department" class="ui dropdown search">
                                            <option value="">-- Select Sub-Department --</option>
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
                                
                                <div class="two fields">
                                    <div class="field">
                                        <label class="required-field">Role</label>
                                        <select name="role" id="role" class="ui dropdown search" required>
                                            <option value="">-- Select Role --</option>
                                        </select>
                                    </div>
                                    <div class="field">
                                        <label class="required-field">Group Type</label>
                                        <select name="group_type" class="ui dropdown">
                                            <option value="">-- Select Group Type --</option>
                                            <option value="A">Group A</option>
                                            <option value="B">Group B</option>
                                            <option value="NA">NA</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Manager Section -->
                                <div id="managerSection" class="two fields">
                                    <div class="field">
                                        <label>In-Company Manager</label>
                                        <select name="in_company_manager" id="in_company_manager" class="ui dropdown search">
                                            <option value="">-- Select In-Company Manager --</option>
                                        </select>
                                    </div>
                                    <div class="field">
                                        <label>Line Manager</label>
                                        <select name="line_manager" id="line_manager" class="ui dropdown search">
                                            <option value="">-- Select Line Manager --</option>
                                        </select>
                                    </div>    
                                </div>

                                <div class="two fields">
                                    <div class="field">
                                    <label>Supervisor</label>
                                    <div class="supervisor-input-container">
                                        <div id="supervisor_input_wrapper" class="supervisor-tags-input">
                                            <div id="selected_supervisors_inline" class="selected-items-inline"></div>
                                            <input type="text" id="supervisor_search" class="form-control-inline" placeholder="-- Type to search Supervisor (Max 5) --" autocomplete="off">
                                        </div>
                                        <div id="supervisor_suggestions" class="suggestions-container" style="display: none;"></div>
                                        <input type="hidden" name="supervisor" id="supervisor_hidden" value="">
                                    </div>
                                </div>
                                    
                                    <div class="field" id="sponsorField">
                                        <label>Sponsor</label>
                                        <select name="sponsor" id="sponsor" class="ui dropdown search" style="color: #000000 !important; width: 100%;">
                                            <option value="">-- Select Sponsor --</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="two fields">
                                    <div class="field">
                                        <label class="required-field">Type of Employment</label>
                                        <select name="employment_type" id="employment_type" class="ui dropdown search" required>
                                            <option value="">-- Select Employment Type --</option>
                                            <option value="Blue Collar">Blue Collar</option>
                                            <option value="Blue Collar Contract">Blue Collar Contract</option>
                                            <option value="Blue Collar Trainee">Blue Collar Trainee</option>
                                            <option value="White Collar">White Collar</option>
                                            <option value="White Collar Contract">White Collar Contract</option>
                                        </select>
                                    </div>
                                    
                                    <div class="field">
                                        <label class="required-field">Joined 01.01.2005</label>
                                        <select name="joined" id="joined" class="ui dropdown search">
                                            <option value="">-- Select Option --</option>
                                            <option value="before">Before</option>
                                            <option value="after">After</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="two fields">
                                    <div class="field">
                                        <label>Grade</label>
                                        <select name="grade" id="grade" class="ui dropdown">
                                            <option value="">-- Select Grade --</option>
                                            <option value="1">Grade 1</option>
                                            <option value="2">Grade 2</option>
                                            <option value="3">Grade 3</option>
                                            <option value="4">Grade 4</option>
                                            <option value="5">Grade 5</option>
                                            <option value="6">Grade 6</option>
                                            <option value="7">Grade 7</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <!-- Submit Button Container - COMPACT, CENTERED, SHIFTED UP -->
                                <div class="button-container">
                                    <button class="btn btn-primary" type="submit">
                                        Submit Registration
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div> 
                </div>
            </div>
        </div>
        
        <?php 
        $footer_display = 'PMS';
        include_once '../assemblynotes/shared/footer.php'; 
        ?>
    </div>
</div>

<!-- Loading indicator -->
<div id="loading-indicator">
    <i class="fa fa-spinner fa-spin" style="font-size: 24px; margin-right: 10px;"></i>
    Loading...
</div>

<!-- Mainly scripts -->
<?php include_once '../assemblynotes/shared/headerSemanticScripts.php' ?>
<script src="shared/shared.js"></script>

<script>
var currentGIDSuggestions = [];
var displayedGIDCount = 0;
var itemsPerPage = 5;
var isLoadingMore = false;
var allDropdownOptions = null;
var isValidGID = false;
var selectedSupervisors = [];
var allSupervisorOptions = [];
var currentSupervisorSuggestions = [];

$(document).ready(function() {
    // Set global AJAX timeout
    $.ajaxSetup({
        timeout: 30000,
        error: function(xhr, status, error) {
            if (status === 'timeout') {
                toastr.error('Request timed out. Please refresh the page and try again.', 'Connection Timeout');
                $('#loading-indicator').hide();
            }
        }
    });

    // Initialize regular Semantic UI dropdowns (non-multiple)
    $('.ui.dropdown:not(.multiple)').dropdown({
        fullTextSearch: true,
        filterRemoteData: true,
        ignoreDiacritics: true,
        selectOnKeydown: false,
        forceSelection: false,
        allowAdditions: false,
        message: {
            noResults: 'No results found.'
        },
        onShow: function() {
            $(this).find('.item').removeClass('selected');
        },
        onChange: function(value, text, $choice) {
            if (value) {
                $(this).dropdown('set selected', value);
            }
            
            if ($(this).attr('name') === 'in_company_manager' || $(this).attr('name') === 'line_manager' || $(this).attr('name') === 'sponsor') {
                toggleManagerSponsorFields();
            }
        }
    });

    $('.ui.dropdown').on('focus', function() {
        $(this).find('.menu .item').removeClass('selected active');
    });
    
    loadAllDropdownOptions();
    
    $('#gid').on('input', function() {
        const searchTerm = $(this).val().trim();
        if (searchTerm.length >= 2) {
            currentPage = 0;
            getGIDSuggestions(searchTerm);
        } else {
            $('#gid_suggestions').hide();
        }
    });

    $('#supervisor_search').on('input', function() {
        const searchTerm = $(this).val().trim();
        
        if (searchTerm.length >= 2) {
            getSupervisorSuggestions(searchTerm);
        } else {
            $('#gid_suggestions').hide();
        }
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('.gid-input-container').length) {
            $('#gid_suggestions').hide();
        }
        if (!$(e.target).closest('.supervisor-input-container').length) {
            $('#supervisor_suggestions').hide();
        }
    });

    // Prevent closing when clicking inside supervisor suggestions
    $('#supervisor_suggestions').on('click', function(e) {
        e.stopPropagation();
    });

    $('#gid_suggestions').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
    });

    $('#gid_suggestions').on('scroll', function() {
        var container = $(this);
        var scrollTop = container.scrollTop();
        var scrollHeight = container[0].scrollHeight;
        var containerHeight = container.height();
        
        if (scrollTop + containerHeight >= scrollHeight - 10) {
            loadMoreGIDSuggestions();
        }
    });
    
    toggleManagerSponsorFields();
});

function getSupervisorSuggestions(searchTerm) {
    $('#supervisor_suggestions').html('<div class="suggestion-item">Loading...</div>').show();
    
    $.ajax({
        url: '/dpm/api/PMSController.php',
        method: 'POST',
        data: {
            "action": "getGIDSuggestions",
            "searchTerm": searchTerm
        },
        success: function(response) {
            var data = typeof response === 'string' ? JSON.parse(response) : response;
            
            currentSupervisorSuggestions = data.data || [];
            
            displaySupervisorSuggestions(currentSupervisorSuggestions, searchTerm);
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            $('#supervisor_suggestions').html('<div class="suggestion-item">Error: ' + error + '</div>');
        }
    });
}

function displaySupervisorSuggestions(supervisors, searchTerm) {
    var suggestionsHtml = '';
    
    if (!supervisors || supervisors.length === 0) {
        suggestionsHtml = '<div class="suggestion-item">No supervisors found</div>';
        $('#supervisor_suggestions').html(suggestionsHtml).show();
        return;
    }
    
    const displayList = supervisors.slice(0, 10);
    
    displayList.forEach(function(supervisor) {
        const gid = supervisor.key;
        let name = supervisor.value;
        
        if (name && name.startsWith(gid)) {
            const parts = name.split(' - ');
            if (parts.length > 1) {
                name = parts.slice(1).join(' - '); 
            }
        }
        
        const displayText = `${gid} - ${name}`;
        
        const isSelected = selectedSupervisors.some(s => s.gid === gid);
        
        if (!isSelected) {
            const highlightedText = displayText.replace(
                new RegExp('(' + searchTerm + ')', 'gi'),
                '<strong>$1</strong>'
            );
            
            suggestionsHtml += '<div class="suggestion-item" onclick="selectSupervisor(\'' + 
                gid + '\', \'' + name.replace(/'/g, "\\'") + '\')" data-gid="' + gid + '">' + 
                highlightedText + '</div>';
        }
    });
    
    if (suggestionsHtml === '') {
        suggestionsHtml = '<div class="suggestion-item">All matching supervisors already selected</div>';
    }
    
    $('#supervisor_suggestions').html(suggestionsHtml).show();
}

function selectSupervisor(gid, name) {
    if (selectedSupervisors.length >= 5) {
        toastr.warning('Maximum of 5 supervisors allowed', 'Selection Limit', {
            closeButton: true,
            timeOut: 3000
        });
        $('#supervisor_suggestions').hide();
        $('#supervisor_search').val('');
        return;
    }
    
    const alreadySelected = selectedSupervisors.some(s => s.gid === gid);
    if (alreadySelected) {
        toastr.info('This supervisor is already selected', 'Already Selected', {
            closeButton: true,
            timeOut: 2000
        });
        $('#supervisor_suggestions').hide();
        $('#supervisor_search').val('');
        return;
    }
    
    selectedSupervisors.push({ gid: gid, name: name });
    
    updateSupervisorDisplay();
    
    $('#supervisor_search').val('');
    $('#supervisor_suggestions').hide();
    $('#supervisor_search').focus();
    
    console.log('Selected supervisors:', selectedSupervisors);
}

function removeSupervisor(gid) {
    selectedSupervisors = selectedSupervisors.filter(s => s.gid !== gid);
    updateSupervisorDisplay();
    console.log('Removed supervisor:', gid);
    console.log('Remaining supervisors:', selectedSupervisors);
}

function updateSupervisorDisplay() {
    const container = $('#selected_supervisors_inline');
    const wrapper = $('#supervisor_input_wrapper');
    container.empty();
    
    if (selectedSupervisors.length === 0) {
        $('#supervisor_hidden').val('');
        wrapper.removeClass('has-tags');
        $('#supervisor_search').attr('placeholder', '-- Type to search Supervisor (Max 5) --');
        return;
    }
    
    wrapper.addClass('has-tags');
    $('#supervisor_search').attr('placeholder', '');
    
    selectedSupervisors.forEach(function(supervisor) {
        const displayText = `${supervisor.gid} (${supervisor.name})`;
        
        const item = $(`
            <div class="selected-supervisor-item-inline">
                <span>${displayText}</span>
                <span class="remove-supervisor" onclick="removeSupervisor('${supervisor.gid}')">&times;</span>
            </div>
        `);
        container.append(item);
    });
    
    const gids = selectedSupervisors.map(s => s.gid).join(',');
    $('#supervisor_hidden').val(gids);
    
    console.log('Updated supervisor hidden field:', gids);
}

$('#supervisor_input_wrapper').on('click', function(e) {
    if (!$(e.target).hasClass('remove-supervisor')) {
        $('#supervisor_search').focus();
    }
});

$('#supervisor_search').on('input', function() {
    const searchTerm = $(this).val().trim();
    
    if (selectedSupervisors.length >= 5) {
        $('#supervisor_suggestions').hide();
        return;
    }
    
    if (searchTerm.length >= 2) {
    getSupervisorSuggestions(searchTerm);
    } else {
        $('#supervisor_suggestions').hide();
    }
});

function clearSupervisors() {
    selectedSupervisors = [];
    updateSupervisorDisplay();
}

function toggleManagerSponsorFields() {
    const inCompanyManager = $('#in_company_manager').val();
    const lineManager = $('#line_manager').val();
    const sponsor = $('#sponsor').val();
    
    console.log('Toggle fields - Managers:', inCompanyManager, lineManager, 'Sponsor:', sponsor);
    
    if ((!inCompanyManager && !lineManager) || sponsor) {
        console.log('Enabling sponsor field, disabling manager fields');
        
        $('#sponsor').prop('disabled', false);
        $('#sponsor').closest('.field').removeClass('disabled');
        
        $('#in_company_manager, #line_manager').prop('disabled', true);
        $('#in_company_manager, #line_manager').closest('.field').addClass('disabled');
        
        if (sponsor) {
            $('#in_company_manager, #line_manager').val('');
            $('#in_company_manager, #line_manager').dropdown('clear');
        }
    } else {
        console.log('Enabling manager fields, disabling sponsor field');
        
        $('#in_company_manager, #line_manager').prop('disabled', false);
        $('#in_company_manager, #line_manager').closest('.field').removeClass('disabled');
        
        $('#sponsor').prop('disabled', true);
        $('#sponsor').closest('.field').addClass('disabled');
        
        if (inCompanyManager || lineManager) {
            $('#sponsor').val('');
            $('#sponsor').dropdown('clear');
        }
    }
    
    $('.ui.dropdown').dropdown('refresh');
}

// Form submission handler
$('#registrationForm').on('submit', function(e) {
    e.preventDefault();

    const getDropdownValue = (selector) => {
        const element = $(selector);
        const dropdownValue = element.dropdown('get value');
        const selectValue = element.val();
        
        const finalValue = dropdownValue || selectValue || '';
        return finalValue.toString().trim();
    };

    const requiredFields = {
        gid: $('#gid').val().trim(),
        name: $('#name').val().trim(),
        department: getDropdownValue('select[name="department"]'),
        sub_department: getDropdownValue('select[name="sub_department"]'),
        role: getDropdownValue('select[name="role"]'),
        group_type: getDropdownValue('select[name="group_type"]'),
        joined: getDropdownValue('select[name="joined"]'),
        employment_type: getDropdownValue('select[name="employment_type"]')
    };

    const grade = getDropdownValue('select[name="grade"]');

    console.log('Required fields values:', requiredFields);

    const emptyFields = [];
    
    Object.entries(requiredFields).forEach(([field, value]) => {
        if (!value || value === '' || value === null || value === undefined) {
            emptyFields.push(field.replace('_', ' ').toUpperCase());
        }
    });

    if (emptyFields.length > 0) {
        toastr.error('Please fill in all required fields:\n' + emptyFields.join('\n'), 'Required Fields Missing', {
            closeButton: true,
            timeOut: 5000
        });
        
        const firstEmptyField = Object.entries(requiredFields)
            .find(([_, value]) => !value || value === '' || value === null || value === undefined)?.[0];
        
        if (firstEmptyField) {
            if (firstEmptyField === 'gid' || firstEmptyField === 'name') {
                $(`#${firstEmptyField}`).focus();
            } else {
                $(`select[name="${firstEmptyField}"]`).dropdown('open');
            }
        }
        return;
    }

    // ✅ FIXED: Added comma after ...requiredFields
    let formData = {
        ...requiredFields,
        grade: grade || ''
    };
    
    // ✅ NEW: Convert Blue Collar to Blue Collar Learner if joined is "after"
    if (formData.employment_type === 'Blue Collar' && formData.joined === 'after') {
        console.log('Converting Blue Collar to Blue Collar Learner');
        formData.employment_type = 'Blue Collar Learner';
        toastr.info('Employment type automatically set to "Blue Collar Learner" based on joining date', 'Auto-Conversion', {
            closeButton: true,
            timeOut: 3000
        });
    }
    
    formData.supervisor = $('#supervisor_hidden').val() || '';
    
    if ($('#in_company_manager').prop('disabled') || $('#in_company_manager').attr('disabled') === 'disabled') {
        formData.sponsor = getDropdownValue('select[name="sponsor"]') || '';
        formData.in_company_manager = '';
        formData.line_manager = '';
    } else {
        formData.in_company_manager = getDropdownValue('select[name="in_company_manager"]') || '';
        formData.line_manager = getDropdownValue('select[name="line_manager"]') || '';
        formData.sponsor = '';
    }

    console.log('Final form data to submit:', formData);

    const hasManager = formData.in_company_manager || formData.line_manager;
    const hasSponsor = formData.sponsor;

    if (!hasManager && !hasSponsor) {
        toastr.error('Please provide either Manager information or a Sponsor', 'Required Information Missing', {
            closeButton: true,
            timeOut: 5000
        });
        return;
    }

    $('#loading-indicator').show();

    $.ajax({
        url: '/dpm/api/PMSController.php',
        method: 'POST',
        data: {
            action: 'submitRegistration',
            ...formData
        },
        success: function(response) {
            $('#loading-indicator').hide();
            if (response.success) {
                toastr.success('User registration successful!', 'Success', {
                    closeButton: true,
                    timeOut: 3000,
                    onHidden: function() {
                        $('#registrationForm')[0].reset();
                        resetDropdowns();
                        isValidGID = false;
                    }
                });
            } else {
                toastr.error(response.message || 'Unknown error occurred', 'Error', {
                    closeButton: true,
                    timeOut: 5000
                });
            }
        },
        error: function(xhr, status, error) {
            $('#loading-indicator').hide();
            toastr.error('Error submitting form: ' + error, 'Error', {
                closeButton: true,
                timeOut: 5000
            });
            console.error('Form submission error:', xhr.responseText);
        }
    });
});

function loadAllDropdownOptions() {
    $.ajax({
        url: '/dpm/api/PMSController.php',
        method: 'POST',
        data: {
            "action": "getGIDDetails",
            "gid": ""
        },
        success: function(response) {
            console.log('All dropdown options response:', response);
            var data = typeof response === 'string' ? JSON.parse(response) : response;
            allDropdownOptions = data.dropdownOptions;
            populateDropdowns(data.dropdownOptions);
        },
        error: function(xhr, status, error) {
            console.error('Error loading dropdown options:', error);
            console.error('Response text:', xhr.responseText);
        }
    });
}

function getGIDSuggestions(searchTerm) {
    $('#gid_suggestions').html('<div class="suggestion-item">Loading...</div>').show();
    
    $.ajax({
        url: '/dpm/api/PMSController.php',
        method: 'POST',
        data: {
            "action": "getGIDSuggestions",
            "searchTerm": searchTerm
        },
        success: function(response) {
            var data = typeof response === 'string' ? JSON.parse(response) : response;
            
            currentGIDSuggestions = data.data || [];
            displayedGIDCount = 0;
            
            displayGIDSuggestions(searchTerm);
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            $('#gid_suggestions').html('<div class="suggestion-item">Error: ' + error + '</div>');
        }
    });
}

const ITEMS_PER_PAGE = 5;
let currentPage = 0;
let allGIDs = [];

function displayGIDSuggestions(searchTerm) {
    var suggestionsHtml = '';
    
    if (!currentGIDSuggestions || currentGIDSuggestions.length === 0) {
        suggestionsHtml = '<div class="suggestion-item">No GIDs found</div>';
        $('#gid_suggestions').html(suggestionsHtml).show();
        return;
    }
    
    var nextBatch = currentGIDSuggestions.slice(displayedGIDCount, displayedGIDCount + itemsPerPage);
    
    nextBatch.forEach(function(item) {
        var gidKey = item.key || item.gid || item.GID || item;
        var gidValue = item.value || item.gid || item.GID || item;
        
        if (gidKey && gidValue) {
            var highlightedValue = gidValue.toString().replace(
                new RegExp('(' + searchTerm + ')', 'gi'),
                '<strong>$1</strong>'
            );
            suggestionsHtml += '<div class="suggestion-item" onclick="selectGID(\'' + 
                gidKey + '\', \'' + gidValue.replace(/'/g, "\\'") + '\')" data-gid="' + gidKey + '">' + 
                highlightedValue + '</div>';
        }
    });
    
    displayedGIDCount += nextBatch.length;
    
    if (displayedGIDCount < currentGIDSuggestions.length) {
        suggestionsHtml += '<div class="suggestion-item load-more-indicator" style="background-color: #e9ecef; color: #6c757d; font-style: italic; text-align: center; cursor: default;" onclick="event.stopPropagation();">Scroll down to load more... (' + (currentGIDSuggestions.length - displayedGIDCount) + ' more)</div>';
    }
    
    $('#gid_suggestions').html(suggestionsHtml).show();
}

function loadMoreGIDSuggestions() {
    if (isLoadingMore || displayedGIDCount >= currentGIDSuggestions.length) {
        return;
    }
    
    isLoadingMore = true;
    var searchTerm = $('#gid').val().trim();
    
    var currentHtml = $('#gid_suggestions').html();
    currentHtml = currentHtml.replace(/<div class="suggestion-item load-more-indicator"[^>]*>.*?<\/div>/, '');
    
    var nextBatch = currentGIDSuggestions.slice(displayedGIDCount, displayedGIDCount + itemsPerPage);
    var newItemsHtml = '';
    
    nextBatch.forEach(function(item) {
        var gidKey = item.key || item.gid || item.GID || item;
        var gidValue = item.value || item.gid || item.GID || item;
        
        if (gidKey && gidValue) {
            var highlightedValue = gidValue.toString().replace(
                new RegExp('(' + searchTerm + ')', 'gi'),
                '<strong>$1</strong>'
            );
            newItemsHtml += '<div class="suggestion-item" onclick="selectGID(\'' + 
                gidKey + '\', \'' + gidValue.replace(/'/g, "\\'") + '\')" data-gid="' + gidKey + '">' + 
                highlightedValue + '</div>';
        }
    });
    
    displayedGIDCount += nextBatch.length;
    
    if (displayedGIDCount < currentGIDSuggestions.length) {
        newItemsHtml += '<div class="suggestion-item load-more-indicator" style="background-color: #e9ecef; color: #6c757d; font-style: italic; text-align: center; cursor: default;" onclick="event.stopPropagation();">Scroll down to load more... (' + (currentGIDSuggestions.length - displayedGIDCount) + ' more)</div>';
    }
    
    $('#gid_suggestions').html(currentHtml + newItemsHtml);
    
    isLoadingMore = false;
}

function selectGID(gidKey, gidValue) {
    isValidGID = true; 
    console.log('selectGID called with:', gidKey, gidValue);
    
    resetFormFields();
    
    $('#gid').val(gidKey);
    $('#gid_suggestions').hide();
    
    $('#loading-indicator').show();
    
    $.ajax({
        url: '/dpm/api/PMSController.php',
        method: 'POST',
        data: {
            "action": "getGIDDetails",
            "gid": gidKey
        },
        success: function(response) {
            console.log('Raw response:', response);
            var data = typeof response === 'string' ? JSON.parse(response) : response;
            console.log('Parsed response data:', data);
            
            if (data.dropdownOptions) {
                populateDropdowns(data.dropdownOptions);
                console.log('Populated dropdowns with options:', data.dropdownOptions);
            }
            
            setTimeout(function() {
                if (data.existingUser && data.userData) {
                    console.log('Setting values for existing user:', data.userData);
                    
                    $('#name').val(data.userData.full_name || '');
                    
                    setDropdownValues(data.userData);
                    
                    $('#name').prop('readonly', true);
                    
                    const hasInCompanyManager = !!data.userData.in_company_manager_name;
                    const hasLineManager = !!data.userData.line_manager_name;
                    const hasSponsor = !!data.userData.sponsor_name;
                    
                    console.log('Manager check - In-company:', hasInCompanyManager, 
                                'Line:', hasLineManager, 'Sponsor:', hasSponsor);
                    
                    if (!hasInCompanyManager && !hasLineManager && hasSponsor) {
                        $('#in_company_manager, #line_manager').prop('disabled', true);
                        $('#in_company_manager, #line_manager').closest('.field').addClass('disabled');
                        
                        $('#sponsor').prop('disabled', false);
                        $('#sponsor').closest('.field').removeClass('disabled');
                        
                        if (data.userData.sponsor_name) {
                            $('#sponsor').val(data.userData.sponsor_name);
                            $('#sponsor').dropdown('set selected', data.userData.sponsor_name);
                        }
                    } else {
                        $('#in_company_manager, #line_manager').prop('disabled', false);
                        $('#in_company_manager, #line_manager').closest('.field').removeClass('disabled');
                        
                        $('#sponsor').prop('disabled', true);
                        $('#sponsor').closest('.field').addClass('disabled');
                    }
                } else {
                    console.log('New user - form already cleared');
                    $('#name').prop('readonly', false);
                    
                    $('#in_company_manager, #line_manager').prop('disabled', false);
                    $('#in_company_manager, #line_manager').closest('.field').removeClass('disabled');
                    
                    $('#sponsor').prop('disabled', true);
                    $('#sponsor').closest('.field').addClass('disabled');
                    
                    toastr.info('GID not found in database. Please fill in the details for this new user.', 'New User', {
                        closeButton: true,
                        timeOut: 5000
                    });
                }
                
                $('.ui.dropdown').dropdown('refresh');
                
                toggleManagerSponsorFields();
            }, 100);
            
            $('#loading-indicator').hide();
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            console.error('Response Text:', xhr.responseText);
            toastr.error('Error loading GID details: ' + error, 'Error', {
                closeButton: true,
                timeOut: 5000
            });
            $('#loading-indicator').hide();
        }
    });
}

function resetFormFields() {
    $('form input[type="text"]').val('');
    
    $('.ui.dropdown').dropdown('clear');
    
    $('#name').prop('readonly', false);
    
    $('select').val('');
    
    clearSupervisors();
    
    $('.ui.dropdown').dropdown('refresh');
    
    $('#in_company_manager, #line_manager').prop('disabled', false);
    $('#in_company_manager, #line_manager').closest('.field').removeClass('disabled');
    
    $('#sponsor').prop('disabled', true);
    $('#sponsor').closest('.field').addClass('disabled');
}

function setDropdownValues(userData) {
    if (userData.department) {
        $('select[name="department"]').val(userData.department);
        $('.ui.dropdown').has('select[name="department"]').dropdown('set selected', userData.department);
    }
    
    if (userData.role) {
        $('select[name="role"]').val(userData.role);
        $('.ui.dropdown').has('select[name="role"]').dropdown('set selected', userData.role);
    }
    
    if (userData.sub_department) {
        $('select[name="sub_department"]').val(userData.sub_department);
        $('.ui.dropdown').has('select[name="sub_department"]').dropdown('set selected', userData.sub_department);
    }
    
    if (userData.employment_type) {
        $('select[name="employment_type"]').val(userData.employment_type);
        $('.ui.dropdown').has('select[name="employment_type"]').dropdown('set selected', userData.employment_type);
    }
    
    if (userData.group_type) {
        $('select[name="group_type"]').val(userData.group_type);
        $('.ui.dropdown').has('select[name="group_type"]').dropdown('set selected', userData.group_type);
    }

    if (userData.grade) {
        $('select[name="grade"]').val(userData.grade);
        $('.ui.dropdown').has('select[name="grade"]').dropdown('set selected', userData.grade);
    }
    
    if (userData.in_company_manager_name) {
        $('select[name="in_company_manager"]').val(userData.in_company_manager_name);
        $('.ui.dropdown').has('select[name="in_company_manager"]').dropdown('set selected', userData.in_company_manager_name);
    }
    
    if (userData.line_manager_name) {
        $('select[name="line_manager"]').val(userData.line_manager_name);
        $('.ui.dropdown').has('select[name="line_manager"]').dropdown('set selected', userData.line_manager_name);
    }
    
    // ✅ SIMPLIFIED - Handle supervisor field
    if (userData.supervisor_gid || userData.supervisor_name) {
        const supervisorGIDs = (userData.supervisor_gid || userData.supervisor_name).split(',').map(s => s.trim());
        
        selectedSupervisors = [];
        
        supervisorGIDs.forEach(function(gid) {
            const supervisor = allSupervisorOptions.find(s => s.key === gid);
            if (supervisor) {
                let name = supervisor.value;
                
                if (name && name.startsWith(gid)) {
                    const parts = name.split(' - ');
                    if (parts.length > 1) {
                        name = parts.slice(1).join(' - ');
                    }
                }
                
                selectedSupervisors.push({
                    gid: supervisor.key,
                    name: name
                });
            }
        });
        
        updateSupervisorDisplay();
    }
    
    if (userData.sponsor_name) {
        $('select[name="sponsor"]').val(userData.sponsor_name);
        $('.ui.dropdown').has('select[name="sponsor"]').dropdown('set selected', userData.sponsor_name);
    }
    
    if (userData.joined) {
        $('select[name="joined"]').val(userData.joined);
        $('.ui.dropdown').has('select[name="joined"]').dropdown('set selected', userData.joined);
    }
    
    toggleManagerSponsorFields();
}

function populateDropdowns(options) {
    console.log('populateDropdowns called with:', options);
    
    var deptDropdown = $('select[name="department"]');
    deptDropdown.empty().append('<option value="">-- Select Department --</option>');
    
    if (options.departments && Array.isArray(options.departments) && options.departments.length > 0) {
        options.departments.forEach(function(dept) {
            if (dept && dept.trim() !== '') {
                deptDropdown.append(`<option value="${dept}">${dept}</option>`);
            }
        });
    }

    var roleDropdown = $('select[name="role"]');
    roleDropdown.empty().append('<option value="">-- Select Role --</option>');
    
    if (options.roles && Array.isArray(options.roles) && options.roles.length > 0) {
        options.roles.forEach(function(role) {
            if (role && role.trim() !== '') {
                roleDropdown.append(`<option value="${role}">${role}</option>`);
            }
        });
    }

    // Store supervisor options for autocomplete ONLY
    if (options.managers && Array.isArray(options.managers) && options.managers.length > 0) {
        allSupervisorOptions = options.managers;
        console.log('Stored', allSupervisorOptions.length, 'supervisor options for autocomplete');
    } else {
        allSupervisorOptions = [];
        console.warn('No managers found for supervisor autocomplete');
    }

    // Populate regular manager dropdowns (single select)
    const managerDropdowns = ['select[name="line_manager"]', 'select[name="in_company_manager"]'];
    managerDropdowns.forEach(function(dropdown) {
        var managerDropdown = $(dropdown);
        managerDropdown.empty().append('<option value="">-- Select Manager --</option>');
        
        if (options.managers && Array.isArray(options.managers) && options.managers.length > 0) {
            options.managers.forEach(function(manager) {
                if (manager.value) {
                    managerDropdown.append(`<option value="${manager.value}" data-gid="${manager.key || manager.value}">${manager.value}</option>`);
                }
            });
        }
    });
    
    // Populate sponsor dropdown
    var sponsorDropdown = $('select[name="sponsor"]');
    sponsorDropdown.empty().append('<option value="">-- Select Sponsor --</option>');
    
    if (options.sponsors && Array.isArray(options.sponsors) && options.sponsors.length > 0) {
        options.sponsors.forEach(function(sponsor) {
            if (sponsor.value) {
                sponsorDropdown.append(`<option value="${sponsor.value}" data-gid="${sponsor.key || sponsor.value}">${sponsor.value}</option>`);
            }
        });
    } else {
        if (options.managers && Array.isArray(options.managers)) {
            console.log('No sponsors found, using managers as sponsors');
            options.managers.forEach(function(manager) {
                if (manager.value) {
                    sponsorDropdown.append(`<option value="${manager.value}" data-gid="${manager.key || manager.value}">${manager.value}</option>`);
                }
            });
        }
    }

    $('.ui.dropdown').dropdown('refresh');
    
    console.log('All dropdowns populated and refreshed');
    
    toggleManagerSponsorFields();
}

function resetDropdowns() {
    console.log('Resetting dropdowns');
    $('.ui.dropdown').dropdown('clear');
    
    clearSupervisors();

    $('#supervisor_search').val('');
    $('#supervisor_suggestions').hide();
    
    $('#in_company_manager, #line_manager').prop('disabled', false);
    $('#in_company_manager, #line_manager').closest('.field').removeClass('disabled');
    
    $('#sponsor').prop('disabled', true);
    $('#sponsor').closest('.field').addClass('disabled');
    
    toggleManagerSponsorFields();
}

</script>
</body>
</html>