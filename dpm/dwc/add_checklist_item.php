<!DOCTYPE html>
<html>
<?php
include_once '../core/index.php';
$check = SharedManager::checkAuthToModule(10);
include_once '/api/checklistAPI.php';
$menu_header_display = 'Add Checklist';

$line = "SELECT * FROM tbl_line";
$line_data = DbManager::fetchPDOQueryData('spectra_db', $line)["data"];
$product = "SELECT * FROM tbl_chk_product";
$product_data = DbManager::fetchPDOQueryData('spectra_db', $product)["data"];
$station = "SELECT * FROM tbl_chk_station";
$station_data = DbManager::fetchPDOQueryData('spectra_db', $station)["data"];
?>
<head>
    <title>OneX | Add Checklist</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=yes"/>

    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <meta charset="utf-8">

    <link href="../../css/semantic.min.css" rel="stylesheet"/>
    <link rel="stylesheet" type="text/css" href="../../css/dataTables.semanticui.min.css">
    <link rel="stylesheet" type="text/css" href="../../css/responsive.dataTables.min.css">

    <link href="../../css/main.css?13" rel="stylesheet"/>

    <?php include_once '../shared/headerStyles.php' ?>
    
    <script src="../../js/jquery.min.js"></script>
    <script src="../../js/semantic.min.js"></script>
    <script src="../../js/jquery.dataTables.js"></script>
    <script src="../../js/dataTables.semanticui.min.js"></script>
    <script src="../../js/dataTables.buttons.min.js"></script>
    <script src="../../js/buttons.flash.min.js"></script>
    <script src="../../js/jszip.min.js"></script>
    <script src="../../js/pdfmake.min.js"></script>
    <script src="../../js/vfs_fonts.js"></script>
    <script src="../../js/buttons.html5.min.js"></script>
    <script src="../../js/buttons.print.min.js"></script>
    <script src="../../js/buttons.colVis.min.js"></script>
    <script src="../../js/tablesort.js"></script>
    <script src="../../js/Semantic-UI-Alert.js"></script>
    <script src="../../shared/inspia_gh_assets/js/plugins/metisMenu/jquery.metisMenu.js"></script>
    <link rel="stylesheet" href="../../css/jquery.toast.min.css">

    <script src="/shared/inspia_gh_assets/js/popper.min.js"></script>
    <script src="/shared/inspia_gh_assets/js/bootstrap.min.js"></script>
    <script src="/shared/inspia_gh_assets/js/bootstrap-select.min.js"></script>
    <script src="/shared/inspia_gh_assets/js/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="/shared/inspia_gh_assets/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

    <script src="/shared/inspia_gh_assets/js/plugins/dataTables/datatables.min.js"></script>
    <script src="/shared/inspia_gh_assets/js/plugins/dataTables/dataTables.bootstrap4.min.js"></script>
    <script src="/shared/inspia_gh_assets/js/plugins/select2/js/select2.min.js"></script>
    <script src="/shared/inspia_gh_assets/js/plugins/toastr/toastr.min.js"></script>
    <script src="/shared/inspia_gh_assets/js/plugins/switchery/switchery.js"></script>
    <script src="/shared/inspia_gh_assets/js/plugins/iCheck/icheck.min.js"></script>
    <script src="/shared/inspia_gh_assets/js/plugins/sweetalert/sweetalert.min.js"></script>
    <script src="/shared/inspia_gh_assets/js/moment.min.js"></script>
    <script src="/shared/inspia_gh_assets/js/plugins/chosen/chosen.jquery.js"></script>
    <script src="/shared/inspia_gh_assets/js/inspinia.js"></script>
    <script src="/shared/inspia_gh_assets/js/daterangepicker.js"></script>
    <!-- Include the necessary CSS and JavaScript files -->
</head>
<style>
    /* Combine styles from both files, removing duplicates */
    .full-loader{
        position: fixed !important;
        top: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(50, 50, 50, 0.8) !important;
        z-index: 10000;
    }

    .full-loader > .loader{
        display: flex !important;
        height: 58px;
        flex-direction: column-reverse;
        width: fit-content;
        font-size: 17px;
    }

    .cont {
        padding-top: 40px !important;
        padding-bottom: 41px !important;
    }

    .active.item h3 {
        transform: scale(1.2) !important;
        color: white !important;
    }

    .active.item i {
        transform: scale(1.5) !important;
        color: white !important;
    }

    .active.item {
        background-color: #00b5ad !important;
    }

    .item {
        background-color: white !important;
    }

    .item i {
        color: black !important;
    }

    .item h3 {
        color: black !important;
    }

    .align-center {
        text-align: center !important;
    }

    .ui .label {
        margin-bottom: 10px !important;
    }

    .ui .segment {
        margin-top: 0px !important;
    }

    .date-none {
        display: none;
    }

    .align-center {
        text-align: center !important;
    }

    .ui.search > .results {
        position: relative !important;
        margin: auto !important;
    }

    .ui.tiny.button,
    .ui.tiny.buttons .button,
    .ui.tiny.buttons .or {
        font-size: 14px !important;
    }

    .ui.search .results {
        max-width: 100% !important;
        width: 100% !important;
        min-width: 100% !important;
        left: 0 !important;
        right: 0 !important;
    }

    .ui.search .results .result {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px !important;
        border-bottom: 1px solid #f0f0f0;
    }

    .ui.search .results .result .content {
        display: flex;
        flex-direction: column;
        margin-left: 10px;
    }

    .ui.search .results .result .title {
        font-weight: bold;
        margin-bottom: 5px;
    }

    .ui.search .results .result .description {
        color: #888;
        font-size: 0.9em;
    }

    .dt-center{ text-align: center !important; }

    #materialSearchDataParent {
        width: 100%;
        overflow-x: auto;
    }

    #orderInfoDataTable {
        width: 100% !important;
        max-width: 100% !important;
    }

    .dataTables_wrapper {
        width: 100%;
        overflow-x: auto;
    }

    .dataTables_wrapper .dataTables_scroll {
        overflow-x: auto;
        overflow-y: hidden;
    }

    .dataTables_wrapper .dataTables_scrollBody {
        overflow-x: auto;
        overflow-y: hidden;
    }

    #customFilterParent {
        width: 100% !important;
        display: block !important;
    }

    /* Scanner icon styles */
    .scanner-icon {
        cursor: pointer;
        margin-left: 10px;
        vertical-align: middle;
    }
    .scanner-icon img {
        width: 24px;
        height: 24px;
    }
    .ui.container {
        max-width: 100% !important;
    }
    .checklist-item-row {
        display: flex;
        align-items: center;
    }
    .checklist-item-row .field {
        flex-grow: 1;
        margin-right: 10px;
    }
    .checklist-item-row .button {
        white-space: nowrap;
    }
    .required-field {
        color: red;
    }
    /* Update these styles in your CSS */
    #checklistSuggestions {
        position: absolute;
        z-index: 1000;
        background: white;
        border: 1px solid #ddd;
        border-top: none;
        max-height: 200px;
        overflow-y: auto;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        display: none;
    }

    .suggestion-item {
        padding: 8px 12px;
        cursor: pointer;
        transition: background-color 0.2s ease;
        font-size: 14px;
        line-height: 1.4;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .suggestion-item:hover {
        background-color: #1ab394;
        color: white;
    }

    /* Ensure the parent container has relative positioning */
    #checklistName {
        position: relative;
    }

    /* Scrollbar styling for the dropdown */
    #checklistSuggestions::-webkit-scrollbar {
        width: 8px;
    }

    #checklistSuggestions::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    #checklistSuggestions::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    #checklistSuggestions::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
</style>
<body>
<div id="wrapper">
    <?php $activePage = '/add_checklist_item.php'; ?>
    <?php include_once '../shared/sidebar.php'; ?>
    <div id="page-wrapper" class="gray-bg">
        <div class="row border-bottom" style="position: relative;">
            <div class="ui fixed menu" style="padding: 21px; color:teal; width: 100%;">
                <div class="ui container" style="position: relative; width: 100%;">
                    <div style="position: absolute; right: 0; top: 50%; transform: translateY(-50%); display: flex; align-items: center;">
                        <a href="/" style="display: flex; align-items: center; text-decoration: none;">
                            <div style="margin-right: 10px;">
                                <img src="/images/onex_icon.png" width="25" height="36" class="logo-icon">
                            </div>
                            <div class="logo-text">
                                <h5 style="margin: 0; font-size: 18px; line-height: 1.2;">
                                    DWC <sup class="badge badge-danger" style="font-size: 0.4em; background-color: #dc3545; color: white; padding: 0.2em 0.3em; border-radius: 0.25rem; vertical-align: super;">OneX</sup>
                                </h5>
                                <p style="margin: 0; text-transform: uppercase; font-size: 10px; color: #6c757d; line-height: 1.2;">Digital Work Center</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="ui inverted segment full-loader" style="display: flex">
            <div class="ui active inverted loader">Loading</div>
        </div>
        <div class="ui fluid" style="margin-top: 60px;">
            <h1 class="card-title">Add Checklist</h1>
            <form class="ui form" id="addChecklistForm">
                <div class="column">
                    <div class="ui grid checklist-item-row">
                        <div class="four wide column">
                            <label><span class="required-field">*</span>Checklist Name:</label>
                            <input type="text" id="checklistName" name="checklistName" placeholder="Enter checklist name">
                        </div>
                        <div class="four wide column">
                            <label><span class="required-field">*</span>Department Name:</label>
                            <select id="lineName"  name="lineName">
                                <option value="">Select department name</option>
                                <?php foreach ($line_data as $lines) {
                                    echo '<option value="'.$lines['id'].'">'.$lines['line_name'].'</option>';
                                } ?>
                            </select>
                        </div>
                        <div class="four wide column">
                            <label><span class="required-field">*</span>Product Name:</label>
                            <select id="productName"  name="productName">
                                <option value="">Select product name</option>
                                <?php foreach ($product_data as $products) {
                                    echo '<option value="'.$products['id'].'">'.$products['product_name'].'</option>';
                                } ?>
                            </select>
                        </div>
                        <div class="four wide column">
                            <label>Station Name:</label>
                            <select id="stationName"  name="stationName">
                                <option value="">Select station name</option>
                                <?php foreach ($station_data as $stations) {
                                    echo '<option value="'.$stations['id'].'">'.$stations['station_name'].'</option>';
                                } ?>
                            </select>
                        </div>
                    </div>
                </div>
                </br>
                <!-- New fields for Document Description and Revision -->
                <div class="column">
                    <div class="ui grid checklist-item-row">
                        <div class="eight wide column">
                            <label><span class="required-field">*</span>Document Description/No:</label>
                            <textarea id="documentDescription" name="documentDescription" placeholder="Enter document description" rows="3"></textarea>
                        </div>
                        <div class="eight wide column">
                            <label><span class="required-field">*</span>Revision Date/No:</label>
                            <input type="text" id="revisionDate" name="revisionDate" placeholder="Enter revision date/number">
                        </div>
                    </div>
                </div>
                </br>
                <div class="column">
                    <label><span class="required-field">*</span>Checklist Items:</label>
                    <div class="ui segment" id="checklist-items-container">
                        <div class="ui grid checklist-item-row">
                            <div class="two wide column">
                                <input type="text" name="checklist_references[]" placeholder="Enter reference">
                            </div>
                            <div class="nine wide column">
                                <input type="text" name="checklist_items[]" placeholder="Enter checklist item">
                            </div>
                            <div class="four wide column" style="display: flex; justify-content: center; align-items: center;">
                                <div class="ui radio checkbox">
                                    <input type="radio" name="inputType_0" id="noneRadio_0" value="0" checked><label for="noneRadio_0">None</label>
                                </div>&nbsp;&nbsp;
                                <div class="ui radio checkbox">
                                    <input type="radio" name="inputType_0" id="freeTextRadio_0" value="1"><label for="freeTextRadio_0">Free Text</label>
                                </div>&nbsp;&nbsp;
                                <div class="ui radio checkbox">
                                    <input type="radio" name="inputType_0" id="serialNumberRadio_0" value="2"><label for="serialNumberRadio_0">Serial No.</label>
                                </div>
                            </div>
                            <!-- <div class="two wide column">
                                <button type="button" class="ui red button remove-item-btn">Remove</button>
                            </div> -->
                        </div>
                    </div>
                    <div class="ui grid checklist-item-row">
                        <div class="fourteen wide column">
                            <button type="button" class="ui primary button add-item-btn">Add Item</button>
                            <button type="submit" class="ui green button">Save Checklist</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        </br>
        
    </div>
    <?php $footer_display = 'Add Checklist';
    include_once '../../assemblynotes/shared/footer.php'; ?>
</div>

<!-- Mainly scripts -->
<?php include_once '../../assemblynotes/shared/headerSemanticScripts.php' ?>
<script src="../../shared/shared.js"></script>
<script>
    let activeAjaxRequests = 0;        

    function showFullLoader(){
        const loader = document.querySelector('.full-loader');
        if (loader) {
            loader.style.display = 'flex';
        }
    }

    function hideFullLoader(){
        const loader = document.querySelector('.full-loader');
        if (loader) {
            loader.style.display = 'none';
        }
    }

    $(document).ready(function() {
        // Initialize autocomplete for checklist name
        $('#checklistName').on('input', function() {
            const searchTerm = $(this).val().trim();
            
            if (searchTerm.length >= 3) {
                $.ajax({
                    url: '/dpm/dwc/api/checklistAPI.php?type=get_checklist_names',
                    type: 'POST',
                    data: { search: searchTerm },
                    success: function(response) {
                        const data = JSON.parse(response);
                        showAutocompleteSuggestions(data);
                    }
                });
            } else {
                hideAutocompleteSuggestions();
            }
        });
        // Add these functions to handle the autocomplete dropdown
        function showAutocompleteSuggestions(data) {
            let suggestionList = $('#checklistSuggestions');
            if (!suggestionList.length) {
                suggestionList = $('<div id="checklistSuggestions"></div>');
                $('#checklistName').parent().append(suggestionList);
                
                // Set the width and position to match the input field
                const inputField = $('#checklistName');
                const inputPosition = inputField.position();
                suggestionList.css({
                    'width': inputField.outerWidth() + 'px',
                    'left': inputPosition.left + 'px',
                    'top': inputPosition.top + inputField.outerHeight() + 'px'
                });
            }

            suggestionList.empty();
            
            if (data.length > 0) {
                data.forEach(item => {
                    const suggestion = $(`<div class="suggestion-item">${item.checklist_name}</div>`);
                    suggestion.on('click', function() {
                        $('#checklistName').val(item.checklist_name);
                        hideAutocompleteSuggestions();
                    });
                    suggestionList.append(suggestion);
                });
                suggestionList.show();
            } else {
                hideAutocompleteSuggestions();
            }
        }

        function hideAutocompleteSuggestions() {
            $('#checklistSuggestions').hide();
        }

        // Hide suggestions when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#checklistName, #checklistSuggestions').length) {
                hideAutocompleteSuggestions();
            }
        });

        // Update dropdown position on window resize
        $(window).on('resize', function() {
            const suggestionList = $('#checklistSuggestions');
            if (suggestionList.length) {
                const inputField = $('#checklistName');
                const inputPosition = inputField.position();
                suggestionList.css({
                    'width': inputField.outerWidth() + 'px',
                    'left': inputPosition.left + 'px',
                    'top': inputPosition.top + inputField.outerHeight() + 'px'
                });
            }
        });

        // Add new checklist item row
        $('.add-item-btn').click(function() {
            var newRow = $('<div class="ui grid checklist-item-row"></div>');

            // Reference input
            var referenceInput = $('<div class="two wide column"></div>');
            referenceInput.append('<input type="text" name="checklist_references[]" placeholder="Enter reference">');
            newRow.append(referenceInput);

            // Checklist item input
            var checklistItemInput = $('<div class="nine wide column"></div>');
            checklistItemInput.append('<input type="text" name="checklist_items[]" placeholder="Enter checklist item">');
            newRow.append(checklistItemInput);

            // Radio buttons
            var radioButtons = $('<div class="four wide column" style="display: flex; justify-content: center; align-items: center;"></div>');
            radioButtons.append('<div class="ui radio checkbox"><input type="radio" name="inputType_' + $('.checklist-item-row').length + '" id="noneRadio_' + $('.checklist-item-row').length + '" value="0" checked><label for="noneRadio_' + $('.checklist-item-row').length + '">None</label></div>&nbsp;&nbsp;');
            radioButtons.append('<div class="ui radio checkbox"><input type="radio" name="inputType_' + $('.checklist-item-row').length + '" id="freeTextRadio_' + $('.checklist-item-row').length + '" value="1"><label for="freeTextRadio_' + $('.checklist-item-row').length + '">Free Text</label></div>&nbsp;&nbsp;');
            radioButtons.append('<div class="ui radio checkbox"><input type="radio" name="inputType_' + $('.checklist-item-row').length + '" id="serialNumberRadio_' + $('.checklist-item-row').length + '" value="2"><label for="serialNumberRadio_' + $('.checklist-item-row').length + '">Serial No.</label></div>');
            newRow.append(radioButtons);

            // Remove button (commented out)
            var removeButton = $('<div class="two wide column"></div>');
            removeButton.append('<button type="button" class="ui red button remove-item-btn">Remove</button>');
            newRow.append(removeButton);
            
            $('#checklist-items-container').append(newRow);
            
            // Show the "Remove" button if there are more than 1 row
            if ($('.checklist-item-row').length > 1) {
                $('.remove-item-btn').show();
            }
        });

        // Remove checklist item row
        $(document).on('click', '.remove-item-btn', function() {
            $(this).closest('.checklist-item-row').remove();

            // Hide the "Remove" button if there is only 1 row left
            if ($('.checklist-item-row').length === 1) {
                $('.remove-item-btn').hide();
            }
        });

        // Function to check if any of the form fields already exist in the database
        function checkFormFieldsExist(checklistName, lineName, productName, stationName, checklistReferences, checklistItems, callback) {
            showFullLoader();
            activeAjaxRequests++;

            $.ajax({
                url: '/dpm/dwc/api/checklistAPI.php?type=check_form_fields',
                type: 'POST',
                data: {
                    checklistName: checklistName,
                    lineName: lineName,
                    productName: productName,
                    stationName: stationName,
                    checklistReferences: checklistReferences,
                    checklistItems: checklistItems
                },
                success: function(response) {
                    callback(response);
                    activeAjaxRequests--;
                    if (activeAjaxRequests === 0) {
                        hideFullLoader();
                    }
                },
                error: function() {
                    activeAjaxRequests--;
                    if (activeAjaxRequests === 0) {
                        hideFullLoader();
                    }
                }
            });
        }
        
        // Handle form submission
        $('#addChecklistForm').submit(function(e) {
            e.preventDefault();
            const checklistName = $('#checklistName').val().trim();
            const lineName = $('#lineName').val().trim();
            const productName = $('#productName').val().trim();
            const stationName = $('#stationName').val().trim();
            const documentDescription = $('#documentDescription').val().trim();
            const revisionDate = $('#revisionDate').val().trim();
            const checklistItems = $('input[name="checklist_items[]"]').map(function() {
                return $(this).val();
            }).get();
            const checklistReferences = $('input[name="checklist_references[]"]').map(function() {
                return $(this).val();
            }).get();
            const addOns = $('input[name^="inputType_"]:checked').map(function() {
                return $(this).val();
            }).get();

            if (checklistName === '') {
                alert('Checklist name cannot be blank.');
                return;
            }
            if (lineName === '') {
                alert('Department name cannot be blank.');
                return;
            }
            if (productName === '') {
                alert('Product name cannot be blank.');
                return;
            }
            if (documentDescription === '') {
                alert('Document description cannot be blank.');
                return;
            }
            if (revisionDate === '') {
                alert('Revision date cannot be blank.');
                return;
            }

            // Validate checklist items and references
            let isValid = true;
            for (let i = 0; i < checklistItems.length; i++) {
                if (checklistItems[i] === '') {
                    alert(`Checklist item ${i + 1} cannot be blank.`);
                    isValid = false;
                    break;
                }
                if (checklistReferences[i] === '') {
                    alert(`Checklist reference ${i + 1} cannot be blank.`);
                    isValid = false;
                    break;
                }
                if (addOns[i] === undefined) {
                    alert(`Please select an input type for checklist item ${i + 1}.`);
                    isValid = false;
                    break;
                }
            }

            if (!isValid) {
                return;
            }
            
            checkFormFieldsExist(checklistName, lineName, productName, stationName, checklistReferences, checklistItems, function(response) {    
                const existingFields = JSON.parse(response);
                if (existingFields.length > 0) {
                    let errorMessage = 'The following fields already exist in the database:\n\n';
                    existingFields.forEach(function(field) {
                        errorMessage += `- Checklist Name: ${field.checklist_name}\n`;
                        errorMessage += `- Department Name: ${field.line_name}\n`;
                        errorMessage += `- Product Name: ${field.product_name}\n`;
                        errorMessage += `- Station Name: ${field.station_name}\n`;
                        errorMessage += `- Checklist Reference: ${field.checklist_reference}\n`;
                        errorMessage += `- Checklist Item: ${field.checklist_item}\n\n`;
                    });
                    errorMessage += 'Please enter unique values for these fields.';
                    alert(errorMessage);
                } else {
                    // Save the checklist
                    showFullLoader();
                    var formData = $(this).serialize();
                    activeAjaxRequests++;

                    $.ajax({
                        url: '/dpm/dwc/api/checklistAPI.php?type=insert_checklist',
                        type: 'POST',
                        data: {
                            checklistName: checklistName,
                            lineName: lineName,
                            productName: productName,
                            stationName: stationName,
                            documentDescription: documentDescription,
                            revisionDate: revisionDate,
                            checklistReferences: checklistReferences,
                            checklistItems: checklistItems,
                            addOns: addOns
                        },
                        success: function(response) {
                            if (response === 'Checklist saved successfully') {
                                alert('Checklist saved successfully!');
                                window.location.reload();
                            } else {
                                alert('Error while saving checklist');
                            }
                            activeAjaxRequests--;
                            if (activeAjaxRequests === 0) {
                                hideFullLoader();
                            }
                        },
                        error: function() {
                            alert('Error while saving checklist');
                            activeAjaxRequests--;
                            if (activeAjaxRequests === 0) {
                                hideFullLoader();
                            }
                        }
                    });
                }
            });
        });
        hideFullLoader();
    });
</script>
</body>
</html>