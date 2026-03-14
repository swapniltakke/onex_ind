<?php
function getNextAutoIncrementNumber() {
    // Check if the table has any records
    $sql = "SELECT MAX(serial_no) AS max_serial_no FROM tbl_breaker_details";
    $result = DbManager::fetchPDOQueryData('spectra_db', $sql)["data"];
    $max_id = $result[0]["max_serial_no"];
    if ((is_int($max_id) || ctype_digit($max_id))) {
        // If the table has records, increment the last number by 1
        $next_id = str_pad($max_id + 1, 8, "0", STR_PAD_LEFT);
    } else {
        // If the table is empty, start with 00000001
        $next_id = "00000001";
    }
    return $next_id;
}
$auto_serial_no = getNextAutoIncrementNumber();
?>
<link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/43.3.0/ckeditor5.css">
<style>
    .modal-body {
        height: 81vh; /* Replace <your-desired-height> with the value you want, e.g., 60vh */
        overflow-y: auto;
    }
    .inmodal .modal-header {
        padding: 2px 20px !important;
        text-align: center !important;
        display: block !important;
    }
    .col-lg-4.col-form-label {
        text-align: left !important;
    }
    .modal-body {
        padding: 20px 30px 5px 80px !important;
    }
    .modal-footer {
        margin-top: 3px !important;
    }
    .ck.ck-editor__main > .ck-editor__editable {
        min-height: 80px !important;
        max-height: 80px;
    }
    .input-group {
        position: relative;
    }

    .input-group-append {
        position: absolute;
        right: 0;
        top: 0;
        height: 100%;
        display: flex;
        align-items: center;
        padding: 0 10px;
        background-color: #f1f1f1;
        border-left: 1px solid #ccc;
        cursor: pointer;
    }

    .input-group-append .dropdown-toggle::after {
        display: none;
    }
    
    .suggestions-container {
        position: absolute;
        background-color: #fff;
        border: 1px solid #ccc;
        padding: 5px;
        z-index: 1;
        max-height: 154px;
        overflow-y: auto;
        text-align: left;
    }

    .suggestion-item {
        padding: 5px;
        cursor: pointer;
    }

    .suggestion-item:hover {
        background-color: #f1f1f1;
    }

    .select2-selection__arrow {
        height: 30px;
        position: absolute;
        top: 1px;
        right: 15px;
        width: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #f1f1f1;
        border-right: 1px solid #ccc;
        cursor: pointer;
    }

    .select2-selection__arrow b {
        border-color: #888 transparent transparent transparent;
        border-style: solid;
        border-width: 5px 4px 0 4px;
        height: 0;
        left: 55%;
        margin-left: -4px;
        margin-top: -4px;
        position: absolute;
        top: 50%;
        width: 0;
    }

    #group_name {
        height: auto !important;
    }

    /* Autocomplete suggestions */
    .suggestions-container .suggestion-item:hover {
        background-color: #1ab394;
        color: #fff;
    }

    /* Normal select dropdown */
    .form-control.required + .select2-selection__arrow b,
    .form-control.required + .select2-selection__arrow:hover b {
        border-color: #1ab394 transparent transparent transparent;
    }

    .form-control.required + .select2-selection__arrow:hover {
        background-color: #1ab394;
    }

    .form-control.required + .select2-selection__arrow:hover b {
        border-color: #fff transparent transparent transparent;
    }

    .form-control.required + .select2-selection__arrow {
        background-color: #1ab394;
    }

    .form-control.required + .select2-selection__arrow:hover {
        background-color: #1ab394;
    }

    .select-highlight {
        background-color: #1ab394;
        color: white;
    }
</style>
<div class="modal inmodal" id="updateModal" role="dialog" aria-hidden="true" tabindex="-1">
    <input value="" type="hidden" name="Id" id="hdnId"/>
    <div class="modal-dialog modal-lg">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                            class="sr-only">Close</span></button>
                <h4 class="modal-title">Breaker Update</h4>
            </div>
            <div class="modal-body">
                <div class="col-12 col-lg-11">
                    <div class="form-group row ">
                        <label class="col-lg-4 col-form-label">Group <span style="color: red;">*</span></label>
                        <div class="col-lg-8">
                            <div class="select2-selection__arrow" onclick="toggleGroupNameSuggestions()">
                                <b role="presentation"></b>
                            </div>
                            <input id="group_name" name="group_name" class="form-control required" value="" placeholder="-- Select Group --">
                            <div id="group_name_suggestions" class="suggestions-container" style="display: none;"></div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-lg-4 col-form-label">CDD Date <span style="color: red;">*</span></label>
                        <div class="col-lg-8">
                            <div class="input-group date" id="cdd" style="width: unset !important;">
                                <input type="text" class="form-control" id="cdd_date" name="cdd_date" placeholder="-- Select CDD Date --">
                                <span class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-lg-4 col-form-label">Plan Month <span style="color: red;">*</span></label>
                        <div class="col-lg-8">
                            <div class="input-group date" id="plan_month" style="width: unset !important;">
                                <input type="text" class="form-control" id="plan_month_date" name="plan_month_date" placeholder="-- Select Date --">
                                <span class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row ">
                        <label class="col-lg-4 col-form-label">Sales Order No. <span style="color: red;">*</span></label>
                        <div class="col-lg-8">
                            <!-- <div class="select2-selection__arrow">
                                <b role="presentation"></b>
                            </div> -->
                            <input id="sales_order_no" name="sales_order_no" class="form-control required" value="" placeholder="-- Select Sales Order No. --" readonly>
                            <!-- <div id="sales_order_suggestions" class="suggestions-container" style="display: none;"></div> -->
                        </div>
                    </div>
                    <div class="form-group row ">
                        <label class="col-lg-4 col-form-label">Item No. <span style="color: red;">*</span></label>
                        <div class="col-lg-8">
                            <!-- <div class="select2-selection__arrow">
                                <b role="presentation"></b>
                            </div> -->
                            <input id="item_no" name="item_no" class="form-control required" value="" placeholder="-- Select Item No. --" readonly>
                            <!-- <div id="item_no_suggestions" class="suggestions-container" style="display: none;"></div> -->
                        </div>
                    </div>
                    <div class="form-group row ">
                        <label class="col-lg-4 col-form-label">Client <span style="color: red;">*</span></label>
                        <div class="col-lg-8">
                            <!-- <div class="select2-selection__arrow">
                                <b role="presentation"></b>
                            </div> -->
                            <input id="client" name="client" class="form-control required" value="" placeholder="-- Select Client --" readonly>
                            <!-- <div id="client_name_suggestions" class="suggestions-container" style="display: none;"></div> -->
                        </div>
                    </div>
                    <div class="form-group row ">
                        <label class="col-lg-4 col-form-label">MLFB <span style="color: red;">*</span></label>
                        <div class="col-lg-8">
                            <div class="select2-selection__arrow" onclick="toggleMlfbSuggestions()">
                                <b role="presentation"></b>
                            </div>
                            <input id="mlfb_no" name="mlfb_no" class="form-control required" value="" placeholder="-- Select MLFB --">
                            <div id="mlfb_no_suggestions" class="suggestions-container" style="display: none;"></div>
                        </div>
                    </div>
                    <div class="form-group row ">
                        <label class="col-lg-4 col-form-label">Rating <span style="color: red;">*</span></label>
                        <div class="col-lg-8">
                            <div class="select2-selection__arrow" onclick="toggleRatingSuggestions()">
                                <b role="presentation"></b>
                            </div>
                            <input id="rating" name="rating" class="form-control required" value="" placeholder="-- Select Rating --">
                            <div id="rating_suggestions" class="suggestions-container" style="display: none;"></div>
                        </div>
                    </div>
                    <div class="form-group row ">
                        <label class="col-lg-4 col-form-label">Product Name <span style="color: red;">*</span></label>
                        <div class="col-lg-8">
                            <div class="select2-selection__arrow" onclick="toggleProductNameSuggestions()">
                                <b role="presentation"></b>
                            </div>
                            <input id="product_name" name="product_name" class="form-control required" value="" placeholder="-- Select Product Name --">
                            <div id="product_name_suggestions" class="suggestions-container" style="display: none;"></div>
                        </div>
                    </div>
                    <div class="form-group row ">
                        <label class="col-lg-4 col-form-label">Width <span style="color: red;">*</span></label>
                        <div class="col-lg-8">
                            <div class="select2-selection__arrow" onclick="toggleWidthSuggestions()">
                                <b role="presentation"></b>
                            </div>
                            <input id="width" name="width" class="form-control required" value="" placeholder="-- Select Width --">
                            <div id="width_suggestions" class="suggestions-container" style="display: none;"></div>
                        </div>
                    </div>
                    <div class="form-group row ">
                        <label class="col-lg-4 col-form-label">Trolley Type Of Siemens<span style="color: red;">*</span></label>
                        <div class="col-lg-8">
                            <div class="select2-selection__arrow" onclick="toggleTrolleyTypeSuggestions()">
                                <b role="presentation"></b>
                            </div>
                            <input id="trolley_type" name="trolley_type" class="form-control required" value="" placeholder="-- Select Trolley Type --">
                            <div id="trolley_type_suggestions" class="suggestions-container" style="display: none;"></div>
                        </div>
                    </div>
                    <div class="form-group row ">
                        <label class="col-lg-4 col-form-label">Trolley Required For Refair<span style="color: red;">*</span></label>
                        <div class="col-lg-8">
                            <div class="select2-selection__arrow" onclick="toggleTrolleyRefairSuggestions()">
                                <b role="presentation"></b>
                            </div>
                            <input id="trolley_refair" name="trolley_refair" class="form-control required" value="" placeholder="-- Select Trolley Refair --">
                            <div id="trolley_refair_suggestions" class="suggestions-container" style="display: none;"></div>
                        </div>
                    </div>
                    <div class="form-group row ">
                        <label class="col-lg-4 col-form-label">Total Quantity <span style="color: red;">*</span></label>
                        <div class="col-lg-8">
                            <input id="total_quantity" name="total_quantity" class="form-control required" value="">
                        </div>
                    </div>
                    <div class="form-group row ">
                        <label class="col-lg-4 col-form-label">Production Order Quantity <span style="color: red;">*</span></label>
                        <div class="col-lg-8">
                            <input id="production_order_quantity" name="production_order_quantity" class="form-control required" value="" readonly>
                        </div>
                    </div>
                    <div class="form-group row ">
                        <label class="col-lg-4 col-form-label">Addon <span style="color: red;">*</span></label>
                        <div class="col-lg-8">
                            <div class="select2-selection__arrow" onclick="toggleAddonSuggestions()">
                                <b role="presentation"></b>
                            </div>
                            <input id="addon" name="addon" class="form-control required" value="" placeholder="-- Select Addon --">
                            <div id="addon_suggestions" class="suggestions-container" style="display: none;"></div>
                        </div>
                    </div>
                    <div class="form-group row ">
                        <label id="serialNoLabel" class="col-lg-4 col-form-label">Serial No. <span id="serialAsterisk" style="color: red;">*</span></label>
                        <div class="col-lg-8">
                            <input id="serial_no" name="serial_no" class="form-control required" value="">
                        </div>
                    </div>
                    <div class="form-group row ">
                        <label class="col-lg-4 col-form-label">PTD No.</label>
                        <div class="col-lg-8">
                            <input id="ptd_no" name="ptd_no" class="form-control required" value="">
                        </div>
                    </div>
                    <div class="form-group row ">
                        <label class="col-lg-4 col-form-label">Production Order No.</label>
                        <div class="col-lg-8">
                            <input id="production_order_no" name="production_order_no" class="form-control required" value="">
                        </div>
                    </div>
                    <div class="form-group row ">
                        <label class="col-lg-4 col-form-label">VI Type <span style="color: red;">*</span></label>
                        <div class="col-lg-8">
                            <input id="vi_type" name="vi_type" class="form-control required" value="">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-lg-4 col-form-label">C1 Date</label>
                        <div class="col-lg-8">
                            <div class="input-group date" id="c1date" style="width: unset !important;">
                                <input type="text" class="form-control" id="c1_date" name="c1_date" placeholder="Select Date">
                                <span class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-lg-4 col-form-label">CIA Date</label>
                        <div class="col-lg-8">
                            <div class="input-group date" id="ciadate" style="width: unset !important;">
                                <input type="text" class="form-control" id="cia_date" name="cia_date" placeholder="Select Date">
                                <span class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row ">
                        <label class="col-lg-4 col-form-label">Remark</label>
                        <div class="col-lg-8">
                            <textarea id="remark" name="remark" class="form-control required" rows="3"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="updateBreaker('<?= pathinfo(end(explode('/', $_SERVER["REQUEST_URI"])), PATHINFO_FILENAME) ?>')">Save</button>
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
    $(document).ready(function() {
        $('#cdd_date').on('input', function() {
            validatePlanMonthDate("cdd_date");
        });

        $('#plan_month_date').on('input', function() {
            validatePlanMonthDate("plan_month_date");
        });

        $('#c1_date').on('input', function() {
            validatePlanMonthDate("c1_date");
        });

        $('#cia_date').on('input', function() {
            validatePlanMonthDate("cia_date");
        });

        // Attach a click event handler specifically to the modal-body area
        $('.modal-body').on('click', function(event) {
            const $target = $(event.target);

            // Check if the click is outside suggestions and the specific input fields or select arrow
            if (!$target.closest('.suggestions-container, .form-control.required, .select2-selection__arrow').length) {
                console.log('Click detected outside input-related areas. Hiding suggestions.');
                $('.suggestions-container').hide(); // Hide all suggestion containers
            }
        });

        // Keep suggestions open when clicking on relevant input fields or arrows
        $('.form-control.required, .select2-selection__arrow').on('click', function(event) {
            const $suggestionContainer = $(this).siblings('.suggestions-container');
            
            // Toggle the specific suggestion container visibility
            $suggestionContainer.toggle();
            
            // Ensure other suggestion containers are hidden
            $('.suggestions-container').not($suggestionContainer).hide();

            event.stopPropagation(); // Prevent this click from propagating to the modal body
        });
    });

    function toggleGroupNameSuggestions() {
        var suggestionsContainer = $('#group_name_suggestions');
        if (suggestionsContainer.is(':visible')) {
            // If the suggestions container is already visible, hide it
            suggestionsContainer.hide();
        } else {
            // If the suggestions container is not visible, hide all other suggestions containers and show the current one
            $('.suggestions-container').hide();
            suggestionsContainer.show();
            // Trigger the input event to load the suggestions
            $('#group_name').trigger('input');
        }
    }

    function toggleSalesOrderSuggestions() {
        var suggestionsContainer = $('#sales_order_suggestions');
        if (suggestionsContainer.is(':visible')) {
            // If the suggestions container is already visible, hide it
            suggestionsContainer.hide();
        } else {
            // If the suggestions container is not visible, hide all other suggestions containers and show the current one
            $('.suggestions-container').hide();
            suggestionsContainer.show();
            // Trigger the input event to load the suggestions
            $('#sales_order_no').trigger('input');
        }
    }

    function toggleItemNoSuggestions() {
        var suggestionsContainer = $('#item_no_suggestions');
        if (suggestionsContainer.is(':visible')) {
            // If the suggestions container is already visible, hide it
            suggestionsContainer.hide();
        } else {
            // If the suggestions container is not visible, hide all other suggestions containers and show the current one
            $('.suggestions-container').hide();
            suggestionsContainer.show();
            // Trigger the input event to load the suggestions
            $('#item_no').trigger('input');
        }
    }

    function toggleClientNameSuggestions() {
        var suggestionsContainer = $('#client_name_suggestions');
        if (suggestionsContainer.is(':visible')) {
            // If the suggestions container is already visible, hide it
            suggestionsContainer.hide();
        } else {
            // If the suggestions container is not visible, hide all other suggestions containers and show the current one
            $('.suggestions-container').hide();
            suggestionsContainer.show();
            // Trigger the input event to load the suggestions
            $('#client').trigger('input');
        }
    }

    function toggleMlfbSuggestions() {
        var suggestionsContainer = $('#mlfb_no_suggestions');
        if (suggestionsContainer.is(':visible')) {
            // If the suggestions container is already visible, hide it
            suggestionsContainer.hide();
        } else {
            // If the suggestions container is not visible, hide all other suggestions containers and show the current one
            $('.suggestions-container').hide();
            suggestionsContainer.show();
            // Trigger the input event to load the suggestions
            $('#mlfb_no').trigger('input');
        }
    }

    function toggleRatingSuggestions() {
        var suggestionsContainer = $('#rating_suggestions');
        if (suggestionsContainer.is(':visible')) {
            // If the suggestions container is already visible, hide it
            suggestionsContainer.hide();
        } else {
            // If the suggestions container is not visible, hide all other suggestions containers and show the current one
            $('.suggestions-container').hide();
            suggestionsContainer.show();
            // Trigger the input event to load the suggestions
            $('#rating').trigger('input');
        }
    }

    function toggleProductNameSuggestions() {
        var suggestionsContainer = $('#product_name_suggestions');
        if (suggestionsContainer.is(':visible')) {
            // If the suggestions container is already visible, hide it
            suggestionsContainer.hide();
        } else {
            // If the suggestions container is not visible, hide all other suggestions containers and show the current one
            $('.suggestions-container').hide();
            suggestionsContainer.show();
            // Trigger the input event to load the suggestions
            $('#product_name').trigger('input');
        }
    }

    function toggleWidthSuggestions() {
        var suggestionsContainer = $('#width_suggestions');
        if (suggestionsContainer.is(':visible')) {
            // If the suggestions container is already visible, hide it
            suggestionsContainer.hide();
        } else {
            // If the suggestions container is not visible, hide all other suggestions containers and show the current one
            $('.suggestions-container').hide();
            suggestionsContainer.show();
            // Trigger the input event to load the suggestions
            $('#width').trigger('input');
        }
    }

    function toggleTrolleyTypeSuggestions() {
        var suggestionsContainer = $('#trolley_type_suggestions');
        if (suggestionsContainer.is(':visible')) {
            // If the suggestions container is already visible, hide it
            suggestionsContainer.hide();
        } else {
            // If the suggestions container is not visible, hide all other suggestions containers and show the current one
            $('.suggestions-container').hide();
            suggestionsContainer.show();
            // Trigger the input event to load the suggestions
            $('#trolley_type').trigger('input');
        }
    }

    function toggleTrolleyRefairSuggestions() {
        var suggestionsContainer = $('#trolley_refair_suggestions');
        if (suggestionsContainer.is(':visible')) {
            // If the suggestions container is already visible, hide it
            suggestionsContainer.hide();
        } else {
            // If the suggestions container is not visible, hide all other suggestions containers and show the current one
            $('.suggestions-container').hide();
            suggestionsContainer.show();
            // Trigger the input event to load the suggestions
            $('#trolley_refair').trigger('input');
        }
    }

    function toggleAddonSuggestions() {
        var suggestionsContainer = $('#addon_suggestions');
        if (suggestionsContainer.is(':visible')) {
            // If the suggestions container is already visible, hide it
            suggestionsContainer.hide();
        } else {
            // If the suggestions container is not visible, hide all other suggestions containers and show the current one
            $('.suggestions-container').hide();
            suggestionsContainer.show();
            // Trigger the input event to load the suggestions
            $('#addon').trigger('input');
        }
    }

    $('#group_name').on('click', function() {
        var searchTerm = $(this).val();
        getGroupNameSuggestions(searchTerm);
    });
    $('#group_name').on('input', function() {
        var searchTerm = $(this).val();
        if (searchTerm.length >= 0) { // Trigger auto-suggestion only when the input has at least 3 characters
            getGroupNameSuggestions(searchTerm);
        } else {
            // Clear any existing suggestions and hide the container
            $('#group_name_suggestions').empty().hide();
        }
        // Update the width of the suggestions container to match the input field
        $('#group_name_suggestions').width($('#group_name').outerWidth());
    });

    // $('#sales_order_no').on('click', function() {
    //     var searchTerm = $(this).val();
    //     getSalesOrderSuggestions(searchTerm);
    // });
    // $('#sales_order_no').on('input', function() {
    //     var searchTerm = $(this).val();
    //     if (searchTerm.length >= 0) { // Trigger auto-suggestion only when the input has at least 3 characters
    //         getSalesOrderSuggestions(searchTerm);
    //     } else {
    //         // Clear any existing suggestions and hide the container
    //         $('#sales_order_suggestions').empty().hide();
    //     }
    //     // Update the width of the suggestions container to match the input field
    //     $('#sales_order_suggestions').width($('#sales_order_no').outerWidth());
    // });

    // $('#item_no').on('click', function() {
    //     var searchTerm = $(this).val();
    //     getItemNoSuggestions(searchTerm);
    // });
    // $('#item_no').on('input', function() {
    //     var searchTerm = $(this).val();
    //     if (searchTerm.length >= 0) { // Trigger auto-suggestion only when the input has at least 3 characters
    //         getItemNoSuggestions(searchTerm);
    //     } else {
    //         // Clear any existing suggestions and hide the container
    //         $('#item_no_suggestions').empty().hide();
    //     }
    //     // Update the width of the suggestions container to match the input field
    //     $('#item_no_suggestions').width($('#item_no').outerWidth());
    // });

    // $('#client').on('click', function() {
    //     var searchTerm = $(this).val();
    //     getClientNameSuggestions(searchTerm);
    // });
    // $('#client').on('input', function() {
    //     var searchTerm = $(this).val();
    //     if (searchTerm.length >= 0) { // Trigger auto-suggestion only when the input has at least 3 characters
    //         getClientNameSuggestions(searchTerm);
    //     } else {
    //         // Clear any existing suggestions and hide the container
    //         $('#client_name_suggestions').empty().hide();
    //     }
    //     // Update the width of the suggestions container to match the input field
    //     $('#client_name_suggestions').width($('#client').outerWidth());
    // });

    $('#mlfb_no').on('click', function() {
        var searchTerm = $(this).val();
        getMlfbNoSuggestions(searchTerm);
    });
    $('#mlfb_no').on('input', function() {
        var searchTerm = $(this).val();
        if (searchTerm.length >= 0) { // Trigger auto-suggestion only when the input has at least 3 characters
            getMlfbNoSuggestions(searchTerm);
        } else {
            // Clear any existing suggestions and hide the container
            $('#mlfb_no_suggestions').empty().hide();
        }
        // Update the width of the suggestions container to match the input field
        $('#mlfb_no_suggestions').width($('#mlfb_no').outerWidth());
    });

    $('#rating').on('click', function() {
        var searchTerm = $(this).val();
        getRatingSuggestions(searchTerm);
    });
    $('#rating').on('input', function() {
        var searchTerm = $(this).val();
        if (searchTerm.length >= 0) { // Trigger auto-suggestion only when the input has at least 3 characters
            getRatingSuggestions(searchTerm);
        } else {
            // Clear any existing suggestions and hide the container
            $('#rating_suggestions').empty().hide();
        }
        // Update the width of the suggestions container to match the input field
        $('#rating_suggestions').width($('#rating').outerWidth());
    });

    $('#product_name').on('click', function() {
        var searchTerm = $(this).val();
        getProductNameSuggestions(searchTerm);
    });
    $('#product_name').on('input', function() {
        var searchTerm = $(this).val();
        if (searchTerm.length >= 0) { // Trigger auto-suggestion only when the input has at least 3 characters
            getProductNameSuggestions(searchTerm);
        } else {
            // Clear any existing suggestions and hide the container
            $('#product_name_suggestions').empty().hide();
        }
        // Update the width of the suggestions container to match the input field
        $('#product_name_suggestions').width($('#product_name').outerWidth());
    });

    $('#width').on('click', function() {
        var searchTerm = $(this).val();
        getWidthSuggestions(searchTerm);
    });
    $('#width').on('input', function() {
        var searchTerm = $(this).val();
        if (searchTerm.length >= 0) { // Trigger auto-suggestion only when the input has at least 3 characters
            getWidthSuggestions(searchTerm);
        } else {
            // Clear any existing suggestions and hide the container
            $('#width_suggestions').empty().hide();
        }
        // Update the width of the suggestions container to match the input field
        $('#width_suggestions').width($('#width').outerWidth());
    });

    $('#trolley_type').on('click', function() {
        var searchTerm = $(this).val();
        getTrolleyTypeSuggestions(searchTerm);
    });
    $('#trolley_type').on('input', function() {
        var searchTerm = $(this).val();
        if (searchTerm.length >= 0) { // Trigger auto-suggestion only when the input has at least 2 characters
            getTrolleyTypeSuggestions(searchTerm);
        } else {
            // Clear any existing suggestions and hide the container
            $('#trolley_type_suggestions').empty().hide();
        }
        // Update the width of the suggestions container to match the input field
        $('#trolley_type_suggestions').width($('#trolley_type').outerWidth());
    });

    $('#trolley_refair').on('click', function() {
        var searchTerm = $(this).val();
        getTrolleyRefairSuggestions(searchTerm);
    });
    $('#trolley_refair').on('input', function() {
        var searchTerm = $(this).val();
        if (searchTerm.length >= 0) { // Trigger auto-suggestion only when the input has at least 2 characters
            getTrolleyRefairSuggestions(searchTerm);
        } else {
            // Clear any existing suggestions and hide the container
            $('#trolley_refair_suggestions').empty().hide();
        }
        // Update the width of the suggestions container to match the input field
        $('#trolley_refair_suggestions').width($('#trolley_refair').outerWidth());
    });

    $('#addon').on('click', function() {
        var searchTerm = $(this).val();
        getAddonSuggestions(searchTerm);
    });
    $('#addon').on('input', function() {
        var searchTerm = $(this).val();
        if (searchTerm.length >= 0) { // Trigger auto-suggestion only when the input has at least 2 characters
            getAddonSuggestions(searchTerm);
        } else {
            // Clear any existing suggestions and hide the container
            $('#addon_suggestions').empty().hide();
        }
        // Update the width of the suggestions container to match the input field
        $('#addon_suggestions').width($('#addon').outerWidth());
    });

    function getGroupNameSuggestions(searchTerm) {
        $.ajax({
            url: '/dpm/api/BreakerController.php',
            method: 'POST',
            data: {
                "action": "getGroupNameSuggestions",
                "searchTerm": searchTerm
            },
            success: function(response) {
                displayGroupNameSuggestions(response.data, searchTerm);
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    function getSalesOrderSuggestions(searchTerm) {
        $.ajax({
            url: '/dpm/api/BreakerController.php',
            method: 'POST',
            data: {
                "action": "getSalesOrderSuggestions",
                "searchTerm": searchTerm
            },
            success: function(response) {
                displaySalesOrderSuggestions(response.data, searchTerm);
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    function getItemNoSuggestions(searchTerm) {
        $.ajax({
            url: '/dpm/api/BreakerController.php',
            method: 'POST',
            data: {
                "action": "getItemNoSuggestions",
                "searchTerm": searchTerm
            },
            success: function(response) {
                displayItemNoSuggestions(response.data, searchTerm);
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    function getClientNameSuggestions(searchTerm) {
        $.ajax({
            url: '/dpm/api/BreakerController.php',
            method: 'POST',
            data: {
                "action": "getClientNameSuggestions",
                "searchTerm": searchTerm
            },
            success: function(response) {
                displayClientNameSuggestions(response.data, searchTerm);
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    function getMlfbNoSuggestions(searchTerm) {
        $.ajax({
            url: '/dpm/api/BreakerController.php',
            method: 'POST',
            data: {
                "action": "getMlfbNoSuggestions",
                "searchTerm": searchTerm
            },
            success: function(response) {
                displayMlfbNoSuggestions(response.data, searchTerm);
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    function getMlfbDetails(mlfbNo) {
        $.ajax({
            url: '/dpm/api/BreakerController.php',
            method: 'POST',
            data: {
                "action": "getMlfbDetails",
                "mlfb_no": mlfbNo
            },
            success: function(response) {
                if (response.error) {
                    toastr.error('', response.error);
                    $('#rating').val('');
                    $('#product_name').val('');
                    $('#width').val('');
                    $('#vi_type').val('');
                    $('#ptd_no').val('');
                } else {
                    var mlfbDetails = response.data;
                    // Populate the corresponding fields
                    $('#rating').val(mlfbDetails.rating);
                    $('#product_name').val(mlfbDetails.product_name);
                    $('#width').val(mlfbDetails.width);
                    $('#vi_type').val(mlfbDetails.vi_type);
                    $('#ptd_no').val(mlfbDetails.ptd_no);
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
                toastr.error('', 'An error occurred while fetching MLFB details.');
            }
        });
    }
    
    function getSerialNo(id) {
        $.ajax({
            url: '/dpm/api/BreakerController.php',
            method: 'POST',
            data: {
                "action": "getMlfbDetailsById",
                "id": id
            },
            success: function(response) {
                var mlfbDetails = response.data;
                // Populate the corresponding fields
                $('#serial_no').val(mlfbDetails.serial_no);
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    function getRatingSuggestions(searchTerm) {
        $.ajax({
            url: '/dpm/api/BreakerController.php',
            method: 'POST',
            data: {
                "action": "getRatingSuggestions",
                "searchTerm": searchTerm
            },
            success: function(response) {
                displayRatingSuggestions(response.data, searchTerm);
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    function getProductNameSuggestions(searchTerm) {
        $.ajax({
            url: '/dpm/api/BreakerController.php',
            method: 'POST',
            data: {
                "action": "getProductNameSuggestions",
                "searchTerm": searchTerm
            },
            success: function(response) {
                displayProductNameSuggestions(response.data, searchTerm);
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    function getWidthSuggestions(searchTerm) {
        $.ajax({
            url: '/dpm/api/BreakerController.php',
            method: 'POST',
            data: {
                "action": "getWidthSuggestions",
                "searchTerm": searchTerm
            },
            success: function(response) {
                displayWidthSuggestions(response.data, searchTerm);
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    function getTrolleyTypeSuggestions(searchTerm) {
        $.ajax({
            url: '/dpm/api/BreakerController.php',
            method: 'POST',
            data: {
                "action": "getTrolleyTypeSuggestions",
                "searchTerm": searchTerm
            },
            success: function(response) {
                displayTrolleyTypeSuggestions(response.data, searchTerm);
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    function getTrolleyRefairSuggestions(searchTerm) {
        $.ajax({
            url: '/dpm/api/BreakerController.php',
            method: 'POST',
            data: {
                "action": "getTrolleyRefairSuggestions",
                "searchTerm": searchTerm
            },
            success: function(response) {
                displayTrolleyRefairSuggestions(response.data, searchTerm);
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    function getAddonSuggestions(searchTerm) {
        $.ajax({
            url: '/dpm/api/BreakerController.php',
            method: 'POST',
            data: {
                "action": "getAddonSuggestions",
                "searchTerm": searchTerm
            },
            success: function(response) {
                displayAddonSuggestions(response.data, searchTerm);
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    function displayGroupNameSuggestions(suggestions, searchTerm) {
        var suggestionsContainer = $('#group_name_suggestions');
        suggestionsContainer.empty(); // Clear any existing suggestions

        if (suggestions.length > 0) {
            // Set the width of the suggestions container to match the width of the group_name input field
            suggestionsContainer.css('width', $('#group_name').outerWidth());

            suggestions.forEach(function(suggestion) {
                var suggestionText = suggestion.group_name;
                // Compare the suggestion with the searchTerm and apply highlighting if they match exactly
                var suggestionItem = $('<div>')
                    .addClass('suggestion-item')
                    .text(suggestionText)
                    .toggleClass('select-highlight', suggestionText === searchTerm);
                suggestionItem.on('click', function() {
                    $('#group_name').val(suggestion.group_name);
                    suggestionsContainer.empty().hide(); // Clear the suggestions and hide the container after selecting an item
                });
                suggestionsContainer.append(suggestionItem);
            });
            suggestionsContainer.show();
        } else {
            suggestionsContainer.hide();
        }
    }

    function displaySalesOrderSuggestions(suggestions, searchTerm) {
        var suggestionsContainer = $('#sales_order_suggestions');
        suggestionsContainer.empty(); // Clear any existing suggestions

        if (suggestions.length > 0) {
            // Set the width of the suggestions container to match the width of the sales_order_no input field
            suggestionsContainer.css('width', $('#sales_order_no').outerWidth());

            suggestions.forEach(function(suggestion) {
                var suggestionText = suggestion.sales_order_no;
                // Compare the suggestion with the searchTerm and apply highlighting if they match exactly
                var suggestionItem = $('<div>')
                    .addClass('suggestion-item')
                    .text(suggestionText)
                    .toggleClass('select-highlight', suggestionText === searchTerm);
                suggestionItem.on('click', function() {
                    $('#sales_order_no').val(suggestion.sales_order_no);
                    suggestionsContainer.empty().hide(); // Clear the suggestions and hide the container after selecting an item
                });
                suggestionsContainer.append(suggestionItem);
            });
            suggestionsContainer.show();
        } else {
            suggestionsContainer.hide();
        }
    }

    function displayItemNoSuggestions(suggestions, searchTerm) {
        var suggestionsContainer = $('#item_no_suggestions');
        suggestionsContainer.empty(); // Clear any existing suggestions

        if (suggestions.length > 0) {
            // Set the width of the suggestions container to match the width of the item_no input field
            suggestionsContainer.css('width', $('#item_no').outerWidth());

            suggestions.forEach(function(suggestion) {
                var suggestionText = suggestion.item_no;
                // Compare the suggestion with the searchTerm and apply highlighting if they match exactly
                var suggestionItem = $('<div>')
                    .addClass('suggestion-item')
                    .text(suggestionText)
                    .toggleClass('select-highlight', suggestionText === searchTerm);
                suggestionItem.on('click', function() {
                    $('#item_no').val(suggestion.item_no);
                    suggestionsContainer.empty().hide(); // Clear the suggestions and hide the container after selecting an item
                });
                suggestionsContainer.append(suggestionItem);
            });
            suggestionsContainer.show();
        } else {
            suggestionsContainer.hide();
        }
    }

    function displayClientNameSuggestions(suggestions, searchTerm) {
        var suggestionsContainer = $('#client_name_suggestions');
        suggestionsContainer.empty(); // Clear any existing suggestions

        if (suggestions.length > 0) {
            // Set the width of the suggestions container to match the width of the client input field
            suggestionsContainer.css('width', $('#client').outerWidth());

            suggestions.forEach(function(suggestion) {
                var suggestionText = suggestion.client;
                // Compare the suggestion with the searchTerm and apply highlighting if they match exactly
                var suggestionItem = $('<div>')
                    .addClass('suggestion-item')
                    .text(suggestionText)
                    .toggleClass('select-highlight', suggestionText === searchTerm);
                suggestionItem.on('click', function() {
                    $('#client').val(suggestion.client);
                    suggestionsContainer.empty().hide(); // Clear the suggestions and hide the container after selecting an item
                });
                suggestionsContainer.append(suggestionItem);
            });
            suggestionsContainer.show();
        } else {
            suggestionsContainer.hide();
        }
    }

    function displayMlfbNoSuggestions(suggestions, searchTerm) {
        var suggestionsContainer = $('#mlfb_no_suggestions');
        suggestionsContainer.empty(); // Clear any existing suggestions

        if (suggestions.length > 0) {
            // Set the width of the suggestions container to match the width of the mlfb_no input field
            suggestionsContainer.css('width', $('#mlfb_no').outerWidth());

            suggestions.forEach(function(suggestion) {
                var suggestionText = suggestion.mlfb_no;
                // Compare the suggestion with the searchTerm and apply highlighting if they match exactly
                var suggestionItem = $('<div>')
                    .addClass('suggestion-item')
                    .text(suggestionText)
                    .toggleClass('select-highlight', suggestionText === searchTerm);
                suggestionItem.on('click', function() {
                    $('#mlfb_no').val(suggestion.mlfb_no);
                    suggestionsContainer.empty().hide(); // Clear the suggestions and hide the container after selecting an item
                    getMlfbDetails(suggestion.mlfb_no);
                });
                suggestionsContainer.append(suggestionItem);
            });
            suggestionsContainer.show();
        } else {
            suggestionsContainer.hide();
            var mlfbno = document.getElementById('mlfb_no').value;
            getMlfbDetails(mlfbno);
        }
    }

    function displayRatingSuggestions(suggestions, searchTerm) {
        var suggestionsContainer = $('#rating_suggestions');
        suggestionsContainer.empty(); // Clear any existing suggestions

        if (suggestions.length > 0) {
            // Set the width of the suggestions container to match the width of the rating input field
            suggestionsContainer.css('width', $('#rating').outerWidth());

            suggestions.forEach(function(suggestion) {
                var suggestionText = suggestion.rating;
                // Compare the suggestion with the searchTerm and apply highlighting if they match exactly
                var suggestionItem = $('<div>')
                    .addClass('suggestion-item')
                    .text(suggestionText)
                    .toggleClass('select-highlight', suggestionText === searchTerm);
                suggestionItem.on('click', function() {
                    $('#rating').val(suggestion.rating);
                    suggestionsContainer.empty().hide(); // Clear the suggestions and hide the container after selecting an item
                });
                suggestionsContainer.append(suggestionItem);
            });
            suggestionsContainer.show();
        } else {
            suggestionsContainer.hide();
        }
    }

    function displayProductNameSuggestions(suggestions, searchTerm) {
        var suggestionsContainer = $('#product_name_suggestions');
        suggestionsContainer.empty(); // Clear any existing suggestions

        if (suggestions.length > 0) {
            // Set the width of the suggestions container to match the width of the product_name input field
            suggestionsContainer.css('width', $('#product_name').outerWidth());

            suggestions.forEach(function(suggestion) {
                var suggestionText = suggestion.product_name;
                // Compare the suggestion with the searchTerm and apply highlighting if they match exactly
                var suggestionItem = $('<div>')
                    .addClass('suggestion-item')
                    .text(suggestionText)
                    .toggleClass('select-highlight', suggestionText === searchTerm);
                suggestionItem.on('click', function() {
                    $('#product_name').val(suggestion.product_name);
                    suggestionsContainer.empty().hide(); // Clear the suggestions and hide the container after selecting an item
                });
                suggestionsContainer.append(suggestionItem);
            });
            suggestionsContainer.show();
        } else {
            suggestionsContainer.hide();
        }
    }

    function displayWidthSuggestions(suggestions, searchTerm) {
        var suggestionsContainer = $('#width_suggestions');
        suggestionsContainer.empty(); // Clear any existing suggestions

        if (suggestions.length > 0) {
            // Set the width of the suggestions container to match the width of the width input field
            suggestionsContainer.css('width', $('#width').outerWidth());

            suggestions.forEach(function(suggestion) {
                var suggestionText = suggestion.width;
                // Compare the suggestion with the searchTerm and apply highlighting if they match exactly
                var suggestionItem = $('<div>')
                    .addClass('suggestion-item')
                    .text(suggestionText)
                    .toggleClass('select-highlight', suggestionText === searchTerm);
                suggestionItem.on('click', function() {
                    $('#width').val(suggestion.width);
                    suggestionsContainer.empty().hide(); // Clear the suggestions and hide the container after selecting an item
                });
                suggestionsContainer.append(suggestionItem);
            });
            suggestionsContainer.show();
        } else {
            suggestionsContainer.hide();
        }
    }

    function displayTrolleyTypeSuggestions(suggestions, searchTerm) {
        var suggestionsContainer = $('#trolley_type_suggestions');
        suggestionsContainer.empty(); // Clear any existing suggestions

        if (suggestions.length > 0) {
            // Set the width of the suggestions container to match the width of the trolley_type input field
            suggestionsContainer.css('width', $('#trolley_type').outerWidth());

            suggestions.forEach(function(suggestion) {
                var suggestionText = suggestion.trolley_type;
                // Compare the suggestion with the searchTerm and apply highlighting if they match exactly
                var suggestionItem = $('<div>')
                    .addClass('suggestion-item')
                    .text(suggestionText)
                    .toggleClass('select-highlight', suggestionText === searchTerm);
                suggestionItem.on('click', function() {
                    $('#trolley_type').val(suggestion.trolley_type);
                    suggestionsContainer.empty().hide(); // Clear the suggestions and hide the container after selecting an item
                });
                suggestionsContainer.append(suggestionItem);
            });
            suggestionsContainer.show();
        } else {
            suggestionsContainer.hide();
        }
    }

    function displayTrolleyRefairSuggestions(suggestions, searchTerm) {
        var suggestionsContainer = $('#trolley_refair_suggestions');
        suggestionsContainer.empty(); // Clear any existing suggestions

        if (suggestions.length > 0) {
            // Set the width of the suggestions container to match the width of the trolley_refair input field
            suggestionsContainer.css('width', $('#trolley_refair').outerWidth());

            suggestions.forEach(function(suggestion) {
                var suggestionText = suggestion.trolley_refair;
                // Compare the suggestion with the searchTerm and apply highlighting if they match exactly
                var suggestionItem = $('<div>')
                    .addClass('suggestion-item')
                    .text(suggestionText)
                    .toggleClass('select-highlight', suggestionText === searchTerm);
                suggestionItem.on('click', function() {
                    $('#trolley_refair').val(suggestion.trolley_refair);
                    suggestionsContainer.empty().hide(); // Clear the suggestions and hide the container after selecting an item
                });
                suggestionsContainer.append(suggestionItem);
            });
            suggestionsContainer.show();
        } else {
            suggestionsContainer.hide();
        }
    }

    function displayAddonSuggestions(suggestions, searchTerm) {
        var suggestionsContainer = $('#addon_suggestions');
        suggestionsContainer.empty(); // Clear any existing suggestions

        if (suggestions.length > 0) {
            // Set the width of the suggestions container to match the width of the addon input field
            suggestionsContainer.css('width', $('#addon').outerWidth());

            suggestions.forEach(function(suggestion) {
                var suggestionText = suggestion.addon;
                // Compare the suggestion with the searchTerm and apply highlighting if they match exactly
                var suggestionItem = $('<div>')
                    .addClass('suggestion-item')
                    .text(suggestionText)
                    .toggleClass('select-highlight', suggestionText === searchTerm);
                suggestionItem.on('click', function() {
                    $('#addon').val(suggestion.addon);
                    suggestionsContainer.empty().hide(); // Clear the suggestions and hide the container after selecting an item
                });
                suggestionsContainer.append(suggestionItem);
            });
            suggestionsContainer.show();
        } else {
            suggestionsContainer.hide();
        }
    }

    $(document).on('click', function(event) {
        if (!$(event.target).closest('#group_name, #group_name_suggestions').length) {
            // Click occurred outside of the #group_name and #group_name_suggestions elements
            $('#group_name_suggestions').hide();
        }
        if (!$(event.target).closest('#sales_order_no, #sales_order_suggestions').length) {
            // Click occurred outside of the #sales_order_no and #sales_order_suggestions elements
            $('#sales_order_suggestions').hide();
        }
        if (!$(event.target).closest('#item_no, #item_no_suggestions').length) {
            // Click occurred outside of the #item_no and #item_no_suggestions elements
            $('#item_no_suggestions').hide();
        }
        if (!$(event.target).closest('#client, #client_name_suggestions').length) {
            // Click occurred outside of the #client and #client_name_suggestions elements
            $('#client_name_suggestions').hide();
        }
        if (!$(event.target).closest('#mlfb_no, #mlfb_no_suggestions').length) {
            // Click occurred outside of the #mlfb_no and #mlfb_no_suggestions elements
            $('#mlfb_no_suggestions').hide();
        }
        if (!$(event.target).closest('#rating, #rating_suggestions').length) {
            // Click occurred outside of the #rating and #rating_suggestions elements
            $('#rating_suggestions').hide();
        }
        if (!$(event.target).closest('#product_name, #product_name_suggestions').length) {
            // Click occurred outside of the #product_name and #product_name_suggestions elements
            $('#product_name_suggestions').hide();
        }
        if (!$(event.target).closest('#width, #width_suggestions').length) {
            // Click occurred outside of the #width and #width_suggestions elements
            $('#width_suggestions').hide();
        }
        if (!$(event.target).closest('#trolley_type, #trolley_type_suggestions').length) {
            // Click occurred outside of the #trolley_type and #trolley_type_suggestions elements
            $('#trolley_type_suggestions').hide();
        }
        if (!$(event.target).closest('#trolley_refair, #trolley_refair_suggestions').length) {
            // Click occurred outside of the #trolley_refair and #trolley_refair_suggestions elements
            $('#trolley_refair_suggestions').hide();
        }
        if (!$(event.target).closest('#addon, #addon_suggestions').length) {
            // Click occurred outside of the #addon and #addon_suggestions elements
            $('#addon_suggestions').hide();
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
        Strikethrough // Import the Strikethrough plugin
    } from 'ckeditor5';

    // Get the modal element
    const modal = document.getElementById('updateModal');
    const modalContent = modal.querySelector('.modal-content');

    // Get the close button and cancel button
    const closeButton = modal.querySelector('.close');
    const cancelButton = modal.querySelector('.btn-white');

    // Add event listeners to prevent the modal from closing
    closeButton.addEventListener('click', () => {
        $('#updateModal').modal('hide');
    });

    cancelButton.addEventListener('click', () => {
        $('#updateModal').modal('hide');
    });

    // Prevent the modal from closing when clicking inside
    modalContent.addEventListener('click', (event) => {
        event.stopPropagation();
    });

    // Close the modal when clicking outside
    document.addEventListener('click', (event) => {
        if (event.target === modal) {
            $('#updateModal').modal('hide');
        }
    });
</script>