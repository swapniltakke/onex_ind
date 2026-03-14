<?php
    include_once 'core/index.php';
    SharedManager::checkAuthToModule(14);
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
<!doctype html>
<html lang="en">
<link href="../css/main.css?13" rel="stylesheet"/>
<style>
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
<?php include_once 'shared/headerStyles.php' ?>
<?php include_once '../assemblynotes/shared/headerScripts.php' ?>
<body>
    <div id="wrapper">
        <?php $activePage = '/dpm/breaker_registration_form.php'; ?>
        <?php require_once $_SERVER["DOCUMENT_ROOT"]."/dpm/shared/sidebar.php"; ?>
        <div id="page-wrapper" class="gray-bg">
            <div class="row border-bottom">
                <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">
                    <div class="navbar-header">
                        <a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="#"><i
                                class="fa fa-bars"></i> </a>
                    </div>
                    <ul class="nav navbar-top-links navbar-right">
                        <li>
                            <h2 style="text-align: left; margin-top: 9px;">Breaker Registration Form</h2>
                        </li>
                    </ul>
                </nav>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="row justify-content-center m-lg-4" style="margin-top: 1rem !important;">
                        <div class="col-12 col-lg-7">
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
                                    <div class="select2-selection__arrow" onclick="toggleSalesOrderSuggestions()">
                                        <b role="presentation"></b>
                                    </div>
                                    <input id="sales_order_no" name="sales_order_no" class="form-control required" value="" placeholder="-- Select Sales Order No. --">
                                    <div id="sales_order_suggestions" class="suggestions-container" style="display: none;"></div>
                                </div>
                            </div>
                            <div class="form-group row ">
                                <label class="col-lg-4 col-form-label">Item No. <span style="color: red;">*</span></label>
                                <div class="col-lg-8">
                                    <div class="select2-selection__arrow" onclick="toggleItemNoSuggestions()">
                                        <b role="presentation"></b>
                                    </div>
                                    <input id="item_no" name="item_no" class="form-control required" value="" placeholder="-- Select Item No. --">
                                    <div id="item_no_suggestions" class="suggestions-container" style="display: none;"></div>
                                </div>
                            </div>
                            <div class="form-group row ">
                                <label class="col-lg-4 col-form-label">Client <span style="color: red;">*</span></label>
                                <div class="col-lg-8">
                                    <div class="select2-selection__arrow" onclick="toggleClientNameSuggestions()">
                                        <b role="presentation"></b>
                                    </div>
                                    <input id="client" name="client" class="form-control required" value="" placeholder="-- Select Client --">
                                    <div id="client_name_suggestions" class="suggestions-container" style="display: none;"></div>
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
                                    <input id="production_order_quantity" name="production_order_quantity" class="form-control required" value="">
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
                                        <input type="text" class="form-control" id="c1_date" name="c1_date" placeholder="-- Select Date --">
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
                            <div class="form-group row">
                                <div class="col align-self-end">
                                    <button id="submit-button" class="btn btn-lg btn-primary float-right">
                                        Submit
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php $footer_display = 'Breaker Registration Form';
            include_once '../assemblynotes/shared/footer.php'; ?>
        </div>
    </div>
    <?php include_once '../assemblynotes/shared/headerSemanticScripts.php' ?>
    <script src="shared/shared.js"></script>
    <script src="breaker/allBreaker.js?<?php echo rand(); ?>"></script>
    <!-- JavaScript -->
    <script>
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

        $(document).ready(function() {
            $('#cdd').daterangepicker({
                singleDatePicker: true,
                showDropdowns: true,
                autoApply: true,
                locale: {
                    format: 'DD-MM-YYYY'
                }
            });

            $('#cdd').on('apply.daterangepicker', function(ev, picker) {
                var selectedDate = picker.startDate.format('DD-MM-YYYY');
                $('#cdd_date').val(selectedDate);
                validatePlanMonthDate("cdd_date");
            });

            $('#plan_month').daterangepicker({
                singleDatePicker: true,
                showDropdowns: true,
                autoApply: true,
                locale: {
                    format: 'DD-MM-YYYY'
                }
            });

            $('#plan_month').on('apply.daterangepicker', function(ev, picker) {
                var selectedDate = picker.startDate.format('DD-MM-YYYY');
                $('#plan_month_date').val(selectedDate);
                validatePlanMonthDate("plan_month_date");
            });

            $('#c1date').daterangepicker({
                singleDatePicker: true,
                showDropdowns: true,
                autoApply: true,
                opened: true,
                drops: 'up',
                locale: {
                    format: 'DD-MM-YYYY'
                }
            });

            $('#c1date').on('apply.daterangepicker', function(ev, picker) {
                var selectedDate = picker.startDate.format('DD-MM-YYYY');
                $('#c1_date').val(selectedDate);
                validatePlanMonthDate("c1_date");
            });

            $('#cdd_date').on('input', function() {
                validatePlanMonthDate("cdd_date");
            });

            $('#plan_month_date').on('input', function() {
                validatePlanMonthDate("plan_month_date");
            });

            $('#c1_date').on('input', function() {
                validatePlanMonthDate("c1_date");
            });
        });
    </script>
</body>
<script>
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

    $('#sales_order_no').on('click', function() {
        var searchTerm = $(this).val();
        getSalesOrderSuggestions(searchTerm);
    });
    $('#sales_order_no').on('input', function() {
        var searchTerm = $(this).val();
        if (searchTerm.length >= 0) { // Trigger auto-suggestion only when the input has at least 3 characters
            getSalesOrderSuggestions(searchTerm);
        } else {
            // Clear any existing suggestions and hide the container
            $('#sales_order_suggestions').empty().hide();
        }
        // Update the width of the suggestions container to match the input field
        $('#sales_order_suggestions').width($('#sales_order_no').outerWidth());
    });

    $('#item_no').on('click', function() {
        var searchTerm = $(this).val();
        getItemNoSuggestions(searchTerm);
    });
    $('#item_no').on('input', function() {
        var searchTerm = $(this).val();
        if (searchTerm.length >= 0) { // Trigger auto-suggestion only when the input has at least 3 characters
            getItemNoSuggestions(searchTerm);
        } else {
            // Clear any existing suggestions and hide the container
            $('#item_no_suggestions').empty().hide();
        }
        // Update the width of the suggestions container to match the input field
        $('#item_no_suggestions').width($('#item_no').outerWidth());
    });

    $('#client').on('click', function() {
        var searchTerm = $(this).val();
        getClientNameSuggestions(searchTerm);
    });
    $('#client').on('input', function() {
        var searchTerm = $(this).val();
        if (searchTerm.length >= 0) { // Trigger auto-suggestion only when the input has at least 3 characters
            getClientNameSuggestions(searchTerm);
        } else {
            // Clear any existing suggestions and hide the container
            $('#client_name_suggestions').empty().hide();
        }
        // Update the width of the suggestions container to match the input field
        $('#client_name_suggestions').width($('#client').outerWidth());
    });

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
                    $('#serial_no').val(''); // Clear the serial_no input field
                    $('#serialAsterisk').remove();
                    $('#ptd_no').val('');
                } else {
                    var mlfbDetails = response.data;
                    // Populate the corresponding fields
                    $('#rating').val(mlfbDetails.rating);
                    $('#product_name').val(mlfbDetails.product_name);
                    $('#width').val(mlfbDetails.width);
                    $('#vi_type').val(mlfbDetails.vi_type);
                    if (!$('#serialAsterisk').length) {
                        $('#serialNoLabel').append('<span id="serialAsterisk" style="color: red;">*</span>');
                    }
                    $('#serial_no').val(mlfbDetails.series_no);
                    $('#ptd_no').val(mlfbDetails.ptd_no);
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
                toastr.error('', 'An error occurred while fetching MLFB details.');
            }
        });
    }

    function getSerialNo(mlfbNo) {
        $.ajax({
            url: '/dpm/api/BreakerController.php',
            method: 'POST',
            data: {
                "action": "getSerialNo",
                "mlfb_no": mlfbNo
            },
            success: function(response) {
                var mlfbDetails = response.data;
                // Populate the corresponding fields
                $('#serial_no').val(mlfbDetails.series_no);
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

    let currentFormData = null;
    document.getElementById('submit-button').addEventListener('click', () => {
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
        if(!group_name){
            document.getElementById('group_name').classList.add('is-invalid');
            toastr.warning('','The group field is required');
            return;
        } else {
            document.getElementById('group_name').classList.remove('is-invalid');
        }

        if(!cdd_date){
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

        if(!plan_month_date){
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

        if(!sales_order_no){
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

        if(!item_no){
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
        if(!client){
            document.getElementById('client').classList.add('is-invalid');
            toastr.warning('','The client field is required');
            return;
        } else {
            document.getElementById('client').classList.remove('is-invalid');
        }

        if(!mlfb_no){
            document.getElementById('mlfb_no').classList.add('is-invalid');
            toastr.warning('','The MLFB no field is required');
            return;
        } else {
            document.getElementById('mlfb_no').classList.remove('is-invalid');
        }

        if(!rating){
            document.getElementById('rating').classList.add('is-invalid');
            toastr.warning('','The rating field is required');
            return;
        } else {
            document.getElementById('rating').classList.remove('is-invalid');
        }

        if(!product_name){
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
        // if(!serial_no){
        //     document.getElementById('serial_no').classList.add('is-invalid');
        //     toastr.warning('','The serial number start field is required');
        //     return;
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

        (async () => {
            document.getElementById('submit-button').disabled = true;
            document.getElementById('submit-button').innerText = "Processing"
            const userCreation = await $.ajax({
                url: '/dpm/api/BreakerController.php',
                method: 'POST',
                data: {
                    "action": "registration",
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
                    "remark": remark
                }
            }).catch(e => {
                console.error(e);
                toastr.error('','Error occurred while breaker registration');
                document.getElementById('submit-button').innerText = "Submit"
                document.getElementById('submit-button').disabled = false;
            });
            if(userCreation.code === 200){
                // toastr.success('','Registration Done Successfully');
                // document.getElementById('submit-button').innerText = "Registration Completed"
                // setTimeout(() => {
                //     window.location.reload();
                // }, 2000);
                currentFormData = {
                    group_name: document.getElementById('group_name').value,
                    cdd_date: document.getElementById('cdd_date').value,
                    plan_month_date: document.getElementById('plan_month_date').value,
                    sales_order_no: document.getElementById('sales_order_no').value,
                    item_no: document.getElementById('item_no').value,
                    client: document.getElementById('client').value,
                    mlfb_no: document.getElementById('mlfb_no').value,
                    rating: document.getElementById('rating').value,
                    product_name: document.getElementById('product_name').value,
                    width: document.getElementById('width').value,
                    trolley_type: document.getElementById('trolley_type').value,
                    trolley_refair: document.getElementById('trolley_refair').value,
                    total_quantity: document.getElementById('total_quantity').value,
                    production_order_quantity: document.getElementById('production_order_quantity').value,
                    addon: document.getElementById('addon').value,
                    serial_no: document.getElementById('serial_no').value,
                    ptd_no: document.getElementById('ptd_no').value,
                    production_order_no: document.getElementById('production_order_no').value,
                    vi_type: document.getElementById('vi_type').value,
                    c1_date: document.getElementById('c1_date').value,
                    remark: document.getElementById('remark').value
                };
                toastr.success('Registration Successful', '', {
                    timeOut: 0,
                    extendedTimeOut: 0,
                    closeButton: true,
                    tapToDismiss: false,
                    onShown: function() {
                        $('.toast-success').append(`
                            <div class="d-flex justify-content-between mt-3">
                                <button type="button" class="btn btn-primary btn-sm" onclick="addMoreData()">Add More</button>
                                <button type="button" class="btn btn-secondary btn-sm" onclick="startNewRegistration()">New Registration</button>
                            </div>
                        `);
                    }
                });
            }
        })();
    });

    function addMoreData() {
        var serial_no;
        serial_no = getSerialNo(currentFormData.mlfb_no);
        // Populate the form fields with the stored data
        document.getElementById('group_name').value = currentFormData.group_name;
        document.getElementById('cdd_date').value = currentFormData.cdd_date;
        document.getElementById('plan_month_date').value = currentFormData.plan_month_date;
        document.getElementById('sales_order_no').value = currentFormData.sales_order_no;
        document.getElementById('item_no').value = currentFormData.item_no;
        document.getElementById('client').value = currentFormData.client;
        document.getElementById('mlfb_no').value = currentFormData.mlfb_no;
        document.getElementById('rating').value = currentFormData.rating;
        document.getElementById('product_name').value = currentFormData.product_name;
        document.getElementById('width').value = currentFormData.width;
        document.getElementById('trolley_type').value = currentFormData.trolley_type;
        document.getElementById('trolley_refair').value = currentFormData.trolley_refair;
        document.getElementById('total_quantity').value = currentFormData.total_quantity;
        document.getElementById('production_order_quantity').value = currentFormData.production_order_quantity;
        document.getElementById('addon').value = currentFormData.addon;
        document.getElementById('serial_no').value = currentFormData.serial_no;
        document.getElementById('ptd_no').value = currentFormData.ptd_no;
        document.getElementById('production_order_no').value = currentFormData.production_order_no;
        document.getElementById('vi_type').value = currentFormData.vi_type;
        document.getElementById('c1_date').value = currentFormData.c1_date;
        document.getElementById('remark').value = currentFormData.remark;

        toastr.remove();
        document.getElementById('submit-button').innerText = "Submit";
        document.getElementById('submit-button').disabled = false;
    }

    function startNewRegistration() {
        // Clear the form fields
        clearFormFields();
        toastr.remove();
        document.getElementById('submit-button').innerText = "Submit";
        document.getElementById('submit-button').disabled = false;
    }

    function clearFormFields() {
        // Clear all the form fields
        document.getElementById('group_name').value = '';
        document.getElementById('cdd_date').value = '';
        document.getElementById('plan_month_date').value = '';
        document.getElementById('sales_order_no').value = '';
        document.getElementById('item_no').value = '';
        document.getElementById('client').value = '';
        document.getElementById('mlfb_no').value = '';
        document.getElementById('rating').value = '';
        document.getElementById('product_name').value = '';
        document.getElementById('width').value = '';
        document.getElementById('trolley_type').value = '';
        document.getElementById('trolley_refair').value = '';
        document.getElementById('total_quantity').value = '';
        document.getElementById('production_order_quantity').value = '';
        document.getElementById('addon').value = '';
        document.getElementById('serial_no').value = '';
        document.getElementById('ptd_no').value = '';
        document.getElementById('production_order_no').value = '';
        document.getElementById('vi_type').value = '';
        document.getElementById('c1_date').value = '';
        document.getElementById('remark').value = '';
    }
</script>
</html>