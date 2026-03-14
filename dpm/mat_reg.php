<!DOCTYPE html>
<html lang="en">
<?php
SharedManager::checkAuthToModule(17);
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Material Registration Form</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- <script src="/dpm/dto/material_register_script.js"></script> -->
    <!-- CSS Styles -->
    <style>
        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .form-control {
            height: calc(1.5em + 0.75rem + 2px);
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .form-control:focus {
            border-color: #80bdff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .form-control.mt-2 {
            margin-top: 0.5rem;
        }

        .form-control.is-invalid {
            border-color: #dc3545;
            padding-right: calc(1.5em + 0.75rem);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' stroke='%23dc3545' viewBox='0 0 12 12'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }

        #additional-fields .form-row,
        #shroudsAssemblyFields .form-row {
            display: flex;
            flex-wrap: wrap;
            margin-right: -15px;
            margin-left: -15px;
        }

        .col-lg-3 {
            flex: 0 0 25%;
            max-width: 25%;
            padding-right: 15px;
            padding-left: 15px;
            position: relative;
        }

        #shortTextInput {
            min-height: 50px;
            resize: vertical;
            /* Allows vertical resizing only */
        }

        .error-message {
            display: none;
            color: #dc3545;
            font-size: 12px;
            margin-top: 5px;
        }

        .is-invalid {
            border-color: #dc3545 !important;
        }

        select.is-invalid {
            background-image: none !important;
        }

        .required {
            color: red;
        }

        /* Add to your existing <style> section */
        html,
        body {
            height: 100%;
            margin: 0;
        }

        #wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        #page-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding-bottom: 60px;
            /* Height of your footer */
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            height: 60px;
            /* Set your footer height */
            background-color: #ffffff;
            /* Or your preferred color */
            border-top: 1px solid #e7eaec;
            z-index: 1000;
        }

        /* Add padding to content to prevent overlap with fixed footer */
        .card {
            margin-bottom: 60px;
            /* Same as footer height */
        }

        /* If you have a specific container for scrollable content */
        .card-body {
            overflow-y: auto;
            max-height: calc(100vh - 120px);
            /* Viewport height minus header and footer */
        }

        /* this is for dropdown validation css */
        .form-control:valid,
        .form-select:valid {
            border-color: #ced4da !important;
            background-image: none !important;
        }

        .form-control.is-invalid,
        .form-select.is-invalid {
            border-color: #dc3545;
        }

        .was-validated .form-control:valid,
        .was-validated .form-select:valid {
            border-color: #ced4da !important;
            background-image: none !important;
        }

        #sheetMetalSubFields {
            width: 100%;
            margin-top: 15px;
            padding: 15px;
        }

        #sheetMetalSubFields .form-row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -15px;
        }

        #sheetMetalSubFields .form-group {
            flex: 0 0 25%;
            max-width: 25%;
            padding: 0 15px;
            margin-bottom: 1rem;
        }
    </style>

    <!-- <link rel="stylesheet" href="material_register_style.css"> -->
    <?php include_once 'shared/headerStyles.php' ?>
    <?php include_once '../assemblynotes/shared/headerScripts.php' ?>
</head>

<body>
    <div id="wrapper">
        <?php $activePage = '/dpm/material_register.php'; ?>
        <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/dpm/shared/dto_sidebar.php"; ?>

        <div id="page-wrapper" class="gray-bg">
            <div class="row border-bottom">
                <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">
                    <div class="navbar-header">
                        <a class="navbar-minimalize minimalize-styl-2 btn btn-primary" href="#">
                            <i class="fa fa-bars"></i>
                        </a>
                    </div>
                    <ul class="nav navbar-top-links navbar-right">
                        <li>
                            <h2 style="text-align: left; margin-top: 11px;">Material Registration Form</h2>
                        </li>
                    </ul>
                </nav>
            </div>

            <div class="wrapper wrapper-content">
                <div class="card">
                    <div class="card-body">
                        <div class="row justify-content-center m-lg-4" style="margin-top: 1rem !important;">
                            <div class="col-12">
                                <!-- Form row with 2 fields -->
                                <div class="form-row">
                                    <!-- Product Name Dropdown -->
                                    <div class="form-group col-lg-4">
                                        <label class="form-label product-name-label">Product Name <span style="color: red;">*</span></label>
                                        <select id="product_name" name="product_name" class="form-control required" onchange="checkSelections(); generateDrwNo();">
                                            <option value="" disabled selected>-- Select Product Name --</option>
                                            <option value="NXAIR">NXAIR</option>
                                            <option value="NXAIR H">NXAIR H</option>
                                        </select>
                                    </div>

                                    <!-- Drawing Name Dropdown -->
                                    <div class="form-group col-lg-4">
                                        <label class="form-label drawing-name-label">Drawing Name <span style="color: red;">*</span></label>
                                        <select id="drawing_name" name="drawing_name" class="form-control required" onchange="checkSelections(); generateDrwNo();">
                                            <option value="" disabled selected>-- Select Drawing Name --</option>
                                            <option value="0">.0 Drawing</option>
                                            <option value="3">.3 Drawing</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-lg-4">
                                        <label class="form-label">Drawing Number <span style="color: red;">*</span></label>
                                        <input type="text" id="drawing_number" name="drawing_number" class="form-control required" placeholder="Enter Drawing Number" readonly>
                                    </div>
                                    <script>
                                    function generateDrwNo() {

                                        const productName  = $('#product_name').val()?.trim();
                                        const drawingName  = $('#drawing_name').val()?.trim(); // expects 0 or 3
                                        const materialType = $('#material_type').val()?.trim();
                                        const material     = $('#material').val()?.trim();

                                        const drawingNumberInput = $('#drawing_number');

                                        if (!productName || !drawingName || !materialType) {
                                            return;
                                        }

                                        // Busbar requires material
                                        if (materialType === 'busbar' && !material) {
                                            return;
                                        }

                                        drawingNumberInput.val('Generating...');
                                        drawingNumberInput.prop('disabled', true);

                                        $.ajax({
                                            url: 'api/DTOController.php',
                                            type: 'POST',
                                            data: {
                                                action: "generateDrwNo",
                                                product_name: productName,
                                                drawing_name: drawingName,
                                                material_type: materialType,
                                                material: material
                                            },
                                            // success: function (response) {
                                            //     try {
                                            //         const data = typeof response === 'string'
                                            //             ? JSON.parse(response)
                                            //             : response;

                                            //         if (data.status === 'success') {
                                            //             drawingNumberInput.val(data.drawing_number);
                                            //         } else {
                                            //             drawingNumberInput.val('');
                                            //             Swal.fire('Error', data.message, 'error');
                                            //         }
                                            //     } catch (e) {
                                            //         drawingNumberInput.val('');
                                            //         Swal.fire('Error', 'Invalid server response', 'error');
                                            //     }
                                            // },
                                            success: function (response) {
                                                try {
                                                    const data = typeof response === 'string'
                                                        ? JSON.parse(response)
                                                        : response;

                                                    if (data.status === 'success') {
                                                        drawingNumberInput.val(data.drawing_number);
                                                        // ========== NEW CODE START ==========
                                                        // Store remaining numbers for use in saveData
                                                        window.remainingNumbers = parseInt(data.remaining_numbers) || 0;
                                                        // ========== NEW CODE END ==========
                                                    } else {
                                                        drawingNumberInput.val('');
                                                        Swal.fire('Error', data.message, 'error');
                                                    }
                                                } catch (e) {
                                                    drawingNumberInput.val('');
                                                    Swal.fire('Error', 'Invalid server response', 'error');
                                                }
                                            },
                                            error: function () {
                                                drawingNumberInput.val('');
                                                Swal.fire('Error', 'Failed to generate drawing number', 'error');
                                            },
                                            complete: function () {
                                                drawingNumberInput.prop('disabled', false);
                                            }
                                        });
                                    }
                                    </script>

                                    <!-- <script>
                                        function generateDrwNo() {
                                            // Get values from form
                                            const productName = $('#product_name').val();
                                            const drawingName = $('#drawing_name').val();
                                            const materialType = $('#material_type').val();
                                            const material = $('#material').val(); // Get material value
                                            const drawingNumberInput = $('#drawing_number');

                                            console.log('Values:', {
                                                productName,
                                                drawingName,
                                                materialType,
                                                material
                                            }); // Debug log

                                            // Check if we have required fields
                                            if (!productName || !drawingName || !materialType) {
                                                return;
                                            }

                                            // For busbar type, check if material is selected
                                            if (materialType === 'busbar' && !material) {
                                                console.log('Waiting for material selection for busbar');
                                                return;
                                            }

                                            // Show loading state
                                            drawingNumberInput.val('Generating...');
                                            drawingNumberInput.prop('disabled', true);

                                            // Prepare data for AJAX call
                                            const requestData = {
                                                action: "generateDrwNo",
                                                product_name: productName,
                                                drawing_name: drawingName,
                                                material_type: materialType,
                                                material: material
                                            };

                                            console.log('Sending request:', requestData); // Debug log

                                            // Make AJAX call
                                            $.ajax({
                                                url: 'api/DTOController.php',
                                                type: 'POST',
                                                data: requestData,
                                                success: function(response) {
                                                    console.log('Response received:', response); // Debug log
                                                    try {
                                                        const data = typeof response === 'string' ? JSON.parse(response) : response;
                                                        if (data.status === 'success') {
                                                            drawingNumberInput.val(data.drawing_number);
                                                        } else {
                                                            drawingNumberInput.val('');
                                                            Swal.fire({
                                                                icon: 'error',
                                                                title: 'Error',
                                                                text: data.message || 'Failed to generate drawing number'
                                                            });
                                                        }
                                                    } catch (e) {
                                                        console.error('Error parsing response:', e);
                                                        drawingNumberInput.val('');
                                                    }
                                                },
                                                error: function(xhr, status, error) {
                                                    console.error('AJAX error:', {
                                                        xhr,
                                                        status,
                                                        error
                                                    });
                                                    drawingNumberInput.val('');
                                                    Swal.fire({
                                                        icon: 'error',
                                                        title: 'Error',
                                                        text: 'Failed to generate drawing number'
                                                    });
                                                },
                                                complete: function() {
                                                    drawingNumberInput.prop('disabled', false);
                                                }
                                            });
                                        }
                                    </script> -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Add a divider -->
                    <!-- <hr class="my-4"> -->

                    <!-- Submit button section with proper spacing -->
                    <div class="card-footer bg-light">
                        <div class="row">
                            <div class="col-12 text-center">
                                <button type="button" class="btn btn-primary btn-lg px-5 mb-3" onclick="saveData()">
                                    Submit
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            $footer_display = 'Material Registration Form';
            include_once '../assemblynotes/shared/footer.php';
            ?>
        </div>
    </div>

    <!-- Your HTML code -->

    <?php include_once '../assemblynotes/shared/headerSemanticScripts.php' ?>
    <script src="shared/shared.js"></script>
    <!-- <script src="breaker/allBreaker.js?<?php echo rand(); ?>"></script> -->
    <!-- <script src="dto/material_register_script.js"></script> -->
    <script>
        function checkSelections() {
            const productName = document.getElementById('product_name').value;
            const drawingName = document.getElementById('drawing_name').value;

            if (productName === 'NXAIR' && drawingName === '3') {
                if (!document.getElementById('material_type')) {
                    const materialTypeDiv = document.createElement('div');
                    materialTypeDiv.className = 'row justify-content-center m-lg-4';
                    materialTypeDiv.innerHTML = `
                <div class="col-12">
                    <div class="form-row">
                        <div class="form-group col-lg-6">
                            <label class="form-label">Type of Material <span style="color: red;">*</span></label>
                            <select id="material_type" name="material_type" class="form-control required" onchange="loadAdditionalFields(); generateDrwNo();">
                                <option value="" disabled selected>-- Select Material Type --</option>
                                <option value="paw">PAW components/CET/BET/Accessories</option>
                                <option value="busbar">Busbar</option>
                                <option value="equipment">Equipment</option>
                                <option value="sheet_metal">Sheet Metal Assembly</option>
                                <option value="shrouds">Shrouds Assembly</option>
                                <option value="isolation">Isolation Partition Assly</option>
                                <option value="gfk">GFK Tube</option>
                                <option value="others">Others (Mimic, Painted Mimic etc)</option>
                            </select>
                        </div>
                    </div>
                </div>
            `;

                    const cardBody = document.querySelector('.card-body');
                    cardBody.appendChild(materialTypeDiv);
                }
            } else if (productName === 'NXAIR' && drawingName === '0') {
                if (!document.getElementById('material_type')) {
                    const materialTypeDiv = document.createElement('div');
                    materialTypeDiv.className = 'row justify-content-center m-lg-4';
                    materialTypeDiv.innerHTML = `
                <div class="col-12">
                    <div class="form-row">
                        <div class="form-group col-lg-6">
                            <label class="form-label">Type of Material <span style="color: red;">*</span></label>
                            <select id="material_type" name="material_type" class="form-control required" onchange="loadAdditionalFields(); generateDrwNo();">
                                <option value="" disabled selected>-- Select Material Type --</option>
                                <option value="busbar">Busbar</option>
                                <option value="sheet_metal">Sheet Metal</option>
                                <option value="gland_plate">Gland Plate</option>
                                <option value="insulation">Insulating Material</option>
                                <option value="gfk">GFK Tube</option>
                                <option value="others">Others (Label, Turn Component, Hardware etc.)</option>
                            </select>
                        </div>
                    </div>
                </div>
            `;

                    const cardBody = document.querySelector('.card-body');
                    cardBody.appendChild(materialTypeDiv);
                }
            } else {
                const materialTypeDiv = document.getElementById('material_type')?.closest('.row');
                if (materialTypeDiv) {
                    materialTypeDiv.remove();
                }
                const additionalFieldsDiv = document.getElementById('additional-fields');
                if (additionalFieldsDiv) {
                    additionalFieldsDiv.remove();
                }
            }
        }

        function loadAdditionalFields() {
            const existingFields = document.getElementById('additional-fields');
            if (existingFields) {
                existingFields.remove();
            }

            const materialType = document.getElementById('material_type').value;
            console.log("Selected Material Type:", materialType);

            if (materialType === 'busbar') {
    const drawingName = document.getElementById('drawing_name').value;

                        if (drawingName === '3') {
                            const additionalFieldsDiv = document.createElement('div');
                            additionalFieldsDiv.id = 'additional-fields';
                            additionalFieldsDiv.className = 'row justify-content-center m-lg-4';
                            additionalFieldsDiv.innerHTML = `
                                <div class="col-12">
                                    <div class="form-row">
                                        <div class="form-group col-lg-3">
                                            <label class="form-label">KA Rating <span class="required">*</span></label>
                                            <select class="form-control required" id="ka_rating" name="ka_rating" onchange="updateBusbarShortText()">
                                                <option value="" disabled selected>-- Select kA Rating --</option>
                                                <option value="Upto 31.5 kA">Upto 31.5 kA</option>
                                                <option value="40 kA">40 kA</option>
                                                <option value="50 kA">50 kA</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group col-lg-3">
                                            <label class="form-label">Width <span class="required">*</span></label>
                                            <select class="form-control required" id="width" name="width" onchange="handleOtherOption(this); updateBusbarShortText()">
                                                <option value="" disabled selected>Select Width</option>
                                                <option value="435W">435W</option>
                                                <option value="600W">600W</option>
                                                <option value="800W">800W</option>
                                                <option value="1000W">1000W</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group col-lg-3">
                                            <label class="form-label">Description <span class="required">*</span></label>
                                            <select class="form-control required" id="description" name="description" onchange="handleOtherOption(this); handleDescriptionChange(this); updateBusbarShortText()">
                                                <option value="" disabled selected>-- Select Description --</option>
                                                <option value="Busing to CT">Busing to CT</option>
                                                <option value="Busing to W/o CT">Busing to W/o CT</option>
                                                <option value="CT to CT">CT to CT</option>
                                                <option value="CT to Cable Conn">CT to Cable Conn</option>
                                                <option value="Busbar to Busbar Conn">Busbar to Busbar Conn</option>
                                                <option value="Cable Conn">Cable Conn</option>
                                                <option value="MBB">MBB</option>
                                                <option value="CT on MBB">CT on MBB</option>
                                                <option value="Direct to MBB">Direct to MBB</option>
                                                <option value="EBB">EBB</option>
                                                <option value="EBB Pnl to Pnl Link">EBB Pnl to Pnl Link</option>
                                                <option value="EBB End LHS">EBB End LHS</option>
                                                <option value="EBB END RHS">EBB END RHS</option>
                                                <option value="Feeder Bar">Feeder Bar</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group col-lg-3">
                                            <label class="form-label">Thickness <span class="required">*</span></label>
                                            <select class="form-control required" id="thickness" name="thickness" onchange="handleOtherOption(this); updateBusbarShortText()">
                                                <option value="" disabled selected>-- Select Thickness --</option>
                                                <option value="5">5</option>
                                                <option value="10">10</option>
                                                <option value="15">15</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-row mt-3">
                                        <div class="form-group col-lg-3">
                                            <label class="form-label">Compartment <span class="required">*</span></label>
                                            <select class="form-control required" id="rear_box" name="rear_box" onchange="handleOtherOption(this); updateBusbarShortText()">
                                                <option value="" disabled selected>-- Select Compartment --</option>
                                                <option value="CC">CC</option>
                                                <option value="MC">MC</option>
                                                <option value="BB">BB</option>
                                                <option value="200RB">200RB</option>
                                                <option value="400RB">400RB</option>
                                                <option value="600RB">600RB</option>
                                                <option value="800RB">800RB</option>
                                                <option value="1000RB">1000RB</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>

                                        <div class="form-group col-lg-3">
                                            <label class="form-label">Size of Busbar <span class="required">*</span></label>
                                            <select class="form-control required" id="sizeofbusbar" name="sizeofbusbar" onchange="handleOtherOption(this); updateBusbarShortText()">
                                                <option value="" disabled selected>Select Size of Busbar</option>
                                                <option value="25">25</option>
                                                <option value="30">30</option>
                                                <option value="40">40</option>
                                                <option value="50">50</option>
                                                <option value="60">60</option>
                                                <option value="80">80</option>
                                                <option value="100">100</option>
                                                <option value="120">120</option>
                                                <option value="160">160</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group col-lg-3">
                                            <label class="form-label">Material <span class="required">*</span></label>
                                            <select class="form-control required" id="material" name="material" onchange="handleOtherOption(this); updateBusbarShortText(); generateDrwNo();">
                                                <option value="" disabled selected>Select Material</option>
                                                <option value="AL">AL</option>
                                                <option value="Cu">Cu</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group col-lg-3">
                                            <label class="form-label">AG Plating <span class="required">*</span></label>
                                            <select class="form-control required" id="ag_plating" name="ag_plating" onchange="handleOtherOption(this); updateBusbarShortText()">
                                                <option value="" disabled selected>Select AG Plating</option>
                                                <option value="AG Full">AG Full</option>
                                                <option value="AG Alternate">AG Alternate</option>
                                                <option value="NA">NA</option>
                                            </select>
                                        </div>

                                        <div class="form-group col-lg-9">
                                            <label class="form-label">Short Text <span class="required">*</span></label>
                                            <textarea class="form-control required" id="shortTextInput" name="short_text" placeholder="Short Text will be generated automatically"></textarea>
                                        </div>
                                    </div>
                                    
                                    <div class="form-row mt-3">
                                        <div class="form-group col-lg-12">
                                            <label class="form-label">Remarks</label>
                                            <textarea class="form-control" id="remarks" name="remarks" placeholder="Enter Remarks"></textarea>
                                        </div>
                                    </div>
                                </div>
                            `;
                            // ADD THIS LINE - Insert the div into the DOM
                            const materialTypeDiv = document.getElementById('material_type').closest('.row');
                            materialTypeDiv.parentNode.insertBefore(additionalFieldsDiv, materialTypeDiv.nextSibling);
                            
                        } else if (drawingName === '0') {
                            const additionalFieldsDiv = document.createElement('div');
                            additionalFieldsDiv.id = 'additional-fields';
                            additionalFieldsDiv.className = 'row justify-content-center m-lg-4';
                            additionalFieldsDiv.innerHTML = `
                                <div class="col-12">
                                    <div class="form-row">
                                        <div class="form-group col-lg-3">
                                            <label class="form-label">KA Rating <span class="required">*</span></label>
                                            <select class="form-control required" id="ka_rating" name="ka_rating" onchange="updateBusbarShortText()">
                                                <option value="" disabled selected>-- Select kA Rating --</option>
                                                <option value="Upto 31.5 kA">Upto 31.5 kA</option>
                                                <option value="40 kA">40 kA</option>
                                                <option value="50 kA">50 kA</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group col-lg-3">
                                            <label class="form-label">Width <span class="required">*</span></label>
                                            <select class="form-control required" id="width" name="width" onchange="handleOtherOption(this); updateBusbarShortText()">
                                                <option value="" disabled selected>Select Width</option>
                                                <option value="435W">435W</option>
                                                <option value="600W">600W</option>
                                                <option value="800W">800W</option>
                                                <option value="1000W">1000W</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group col-lg-3">
                                            <label class="form-label">Description <span class="required">*</span></label>
                                            <select class="form-control required" id="description" name="description" 
                                                    onchange="handleOtherOption(this); handleDescriptionChange(this); updateBusbarShortText()">
                                                <option value="" disabled selected>-- Select Description --</option>
                                                <option value="Busing to CT">Busing to CT</option>
                                                <option value="Busing to W/o CT">Busing to W/o CT</option>
                                                <option value="CT to CT">CT to CT</option>
                                                <option value="CT to Cable Conn">CT to Cable Conn</option>
                                                <option value="Busbar to Busbar Conn">Busbar to Busbar Conn</option>
                                                <option value="Cable Conn">Cable Conn</option>
                                                <option value="MBB">MBB</option>
                                                <option value="CT on MBB">CT on MBB</option>
                                                <option value="Direct to MBB">Direct to MBB</option>
                                                <option value="EBB">EBB</option>
                                                <option value="EBB Pnl to Pnl Link">EBB Pnl to Pnl Link</option>
                                                <option value="EBB End LHS">EBB End LHS</option>
                                                <option value="EBB END RHS">EBB END RHS</option>
                                                <option value="Feeder Bar">Feeder Bar</option>
                                                <option value="Feeder Temp Sensor">Feeder Conn Temp Sensor</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group col-lg-3">
                                            <label class="form-label">Thickness <span class="required">*</span></label>
                                            <select class="form-control required" id="thickness" name="thickness" onchange="handleOtherOption(this); updateBusbarShortText()">
                                                <option value="" disabled selected>-- Select Thickness --</option>
                                                <option value="3">3</option>
                                                <option value="5">5</option>
                                                <option value="10">10</option>
                                                <option value="15">15</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-row mt-3">
                                        <div class="form-group col-lg-3">
                                            <label class="form-label">Compartment <span class="required">*</span></label>
                                            <select class="form-control required" id="rear_box" name="rear_box" onchange="handleOtherOption(this); updateBusbarShortText()">
                                                <option value="" disabled selected>-- Select Compartment --</option>
                                                <option value="CC">CC</option>
                                                <option value="MC">MC</option>
                                                <option value="BB">BB</option>
                                                <option value="200RB">200RB</option>
                                                <option value="400RB">400RB</option>
                                                <option value="600RB">600RB</option>
                                                <option value="800RB">800RB</option>
                                                <option value="1000RB">1000RB</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>

                                        <div class="form-group col-lg-3">
                                            <label class="form-label">Size of Busbar <span class="required">*</span></label>
                                            <select class="form-control required" id="sizeofbusbar" name="sizeofbusbar" onchange="handleOtherOption(this); updateBusbarShortText()">
                                                <option value="" disabled selected>Select Size of Busbar</option>
                                                <option value="25">25</option>
                                                <option value="30">30</option>
                                                <option value="40">40</option>
                                                <option value="50">50</option>
                                                <option value="60">60</option>
                                                <option value="80">80</option>
                                                <option value="100">100</option>
                                                <option value="120">120</option>
                                                <option value="160">160</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group col-lg-3">
                                            <label class="form-label">Material <span class="required">*</span></label>
                                            <select class="form-control required" id="material" name="material" onchange="handleOtherOption(this); updateBusbarShortText(); generateDrwNo();">
                                                <option value="" disabled selected>Select Material</option>
                                                <option value="AL">AL</option>
                                                <option value="Cu">Cu</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group col-lg-3">
                                            <label class="form-label">AG Plating <span class="required">*</span></label>
                                            <select class="form-control required" id="ag_plating" name="ag_plating" onchange="handleOtherOption(this); updateBusbarShortText()">
                                                <option value="" disabled selected>Select AG Plating</option>
                                                <option value="AG Full">AG Full</option>
                                                <option value="AG Alternate">AG Alternate</option>
                                                <option value="NA">NA</option>
                                            </select>
                                        </div>

                                        <div class="form-group col-lg-9">
                                            <label class="form-label">Short Text <span class="required">*</span></label>
                                            <textarea class="form-control required" id="shortTextInput" name="short_text" placeholder="Short Text will be generated automatically"></textarea>
                                        </div>
                                    </div>
                                    
                                    <div class="form-row mt-3">
                                        <div class="form-group col-lg-12">
                                            <label class="form-label">Remarks</label>
                                            <textarea class="form-control" id="remarks" name="remarks" placeholder="Enter Remarks"></textarea>
                                        </div>
                                    </div>
                                </div>
                            `;
                            const materialTypeDiv = document.getElementById('material_type').closest('.row');
                            materialTypeDiv.parentNode.insertBefore(additionalFieldsDiv, materialTypeDiv.nextSibling);
                        }
                    } else if (materialType === 'paw') {
                const additionalFieldsDiv = document.createElement('div');
                additionalFieldsDiv.id = 'additional-fields';
                additionalFieldsDiv.className = 'row justify-content-center m-lg-4';
                additionalFieldsDiv.innerHTML = `
            <div class="col-12">
                <div class="form-row">
                    <div class="form-group col-lg-3">
                        <label class="form-label">KA Rating <span class="required">*</span></label>
                        <select class="form-control required" id="ka_rating" name="ka_rating" onchange="updateShortText()">
                            <option value="" disabled selected>-- Select kA Rating --</option>
                            <option value="Upto 31.5 kA">Upto 31.5 kA</option>
                            <option value="40 kA">40 kA</option>
                            <option value="50 kA">50 kA</option>
                        </select>
                    </div>
                    
                    <div class="form-group col-lg-3">
                        <label class="form-label">Width <span class="required">*</span></label>
                        <select class="form-control required" id="width" name="width" onchange="handleOtherOption(this); updateShortText()">
                            <option value="" disabled selected>Select Width</option>
                            <option value="435W">435W</option>
                            <option value="600W">600W</option>
                            <option value="800W">800W</option>
                            <option value="1000W">1000W</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group col-lg-3">
                        <label class="form-label">Description <span class="required">*</span></label>
                        <select class="form-control required" id="description" name="description" onchange="handleOtherOption(this); updateShortText()">
                            <option value="" disabled selected>-- Select Description --</option>
                            <option value="BET">BET</option>
                            <option value="CET">CET</option>
                            <option value="Service truck">Service Truck</option>
                            <option value="VCB">VCB</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group col-lg-3">
                        <label class="form-label">Short Text <span class="required">*</span></label>
                        <textarea class="form-control required" id="shortTextInput" name="short_text" placeholder="Enter Short Text" ></textarea>
                    </div>
                </div>
                
                <div class="form-row mt-3">
                    <div class="form-group col-lg-12">
                        <label class="form-label">Remarks</label>
                        <textarea class="form-control" id="remarks" name="remarks" placeholder="Enter Remarks"></textarea>
                    </div>
                </div>
            </div>
        `;

                const materialTypeDiv = document.getElementById('material_type').closest('.row');
                materialTypeDiv.parentNode.insertBefore(additionalFieldsDiv, materialTypeDiv.nextSibling);
            } else if (materialType === 'equipment') {
                const additionalFieldsDiv = document.createElement('div');
                additionalFieldsDiv.id = 'additional-fields';
                additionalFieldsDiv.className = 'row justify-content-center m-lg-4';
                additionalFieldsDiv.innerHTML = `
        <div class="col-12">
            <div class="form-row">
                <div class="form-group col-lg-6">
                    <label class="form-label">Description <span class="required">*</span></label>
                    <select class="form-control required" id="description" name="description" onchange="handleOtherOption(this); updateEquipmentShortText()">
                        <option value="" disabled selected>-- Select Description --</option>
                        <option value="CVD">CVD</option>
                        <option value="CBCT">CBCT</option>
                        <option value="Window CT">Window CT</option>
                        <option value="Wound CT">Wound CT</option>
                        <option value="Surge Arrester">Surge Arrester</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                
                <div class="form-group col-lg-6">
                    <label class="form-label">Short Text <span class="required">*</span></label>
                    <textarea class="form-control required" id="shortTextInput" name="short_text" placeholder="Short Text will be generated automatically" ></textarea>
                </div>
            </div>
            
            <div class="form-row mt-3">
                <div class="form-group col-lg-12">
                    <label class="form-label">Remarks</label>
                    <textarea class="form-control" id="remarks" name="remarks" placeholder="Enter Remarks"></textarea>
                </div>
            </div>
        </div>
    `;

                const materialTypeDiv = document.getElementById('material_type').closest('.row');
                materialTypeDiv.parentNode.insertBefore(additionalFieldsDiv, materialTypeDiv.nextSibling);
            }
            //     else if (materialType === 'sheet_metal') {
            //     const additionalFieldsDiv = document.createElement('div');
            //     additionalFieldsDiv.id = 'additional-fields';
            //     additionalFieldsDiv.className = 'row justify-content-center m-lg-4';
            //     additionalFieldsDiv.innerHTML = `
            //         <div class="col-12">
            //             <div class="form-row">
            //                 <div class="form-group col-lg-3">
            //                     <label class="form-label">KA Rating <span class="required">*</span></label>
            //                     <select class="form-control required" id="ka_rating" name="ka_rating" onchange="updateSheetMetalShortText()">
            //                          <option value="" disabled selected>-- Select kA Rating --</option>
            //                          <option value="Upto 31.5 kA">Upto 31.5 kA</option>
            //                          <option value="40 kA">40 kA</option>
            //                          <option value="50 kA">50 kA</option>
            //                     </select>
            //                 </div>

            //                 <div class="form-group col-lg-3">
            //                     <label class="form-label">Width <span class="required">*</span></label>
            //                     <select class="form-control required" id="width" name="width" onchange="handleOtherOption(this); updateSheetMetalShortText()">
            //                         <option value="" disabled selected>Select Width</option>
            //                         <option value="435W">435W</option>
            //                         <option value="600W">600W</option>
            //                         <option value="800W">800W</option>
            //                         <option value="1000W">1000W</option>
            //                         <option value="Other">Other</option>
            //                     </select>
            //                 </div>

            //                 <div class="form-group col-lg-3">
            //                     <label class="form-label">Description <span class="required">*</span></label>
            //                     <select class="form-control required" id="description" name="description"  onchange="handleSheetMetalDescription(this); handleOtherOption(this); updateSheetMetalShortText()">
            //                         <option value="" disabled selected>-- Select Description --</option>
            //                         <option value="End Cover Assly">End Cover Assly</option>
            //                         <option value="Gland Plate Assly">Gland Plate Assly</option>
            //                         <option value="BASE FRAME">BASE FRAME</option>
            //                         <option value="Rear Cover Assly">Rear Cover Assly</option>
            //                         <option value="LHS Pnl End Wall Assly">LHS Pnl End Wall Assly</option>
            //                         <option value="RHS Pnl End Wall Assly">RHS Pnl End Wall Assly</option>
            //                         <option value="Rear Box Assly">Rear Box Assly</option>
            //                         <option value="RB Mid panel Ledges Assly">RB Mid panel Ledges Assly</option>
            //                         <option value="Insulator Supports Assly">Insulator Supports Assly</option>
            //                         <option value="CT Supports Assly">CT Supports Assly</option>
            //                         <option value="PT Supports Assly">PT Supports Assly</option>
            //                         <option value="Cable Supports Assly">Cable Supports Assly</option>
            //                         <option value="SA Supports Assly">SA Supports Assly</option>
            //                         <option value="CVD Supports Assly">CVD Supports Assly</option>
            //                         <option value="EM Interlock Assly">EM Interlock Assly</option>
            //                         <option value="Meshwire Assly">Meshwire Assly</option>
            //                         <option value="Limit S/W Assly">Limit S/W Assly</option>
            //                         <option value="Other">Other</option>
            //                     </select>
            //                 </div>

            //                 <div class="form-group col-lg-3">
            //                     <label class="form-label">Compartment <span class="required">*</span></label>
            //                     <select class="form-control required" id="rear_box" name="rear_box" onchange="handleOtherOption(this); updateSheetMetalShortText()">
            //                         <option value="" disabled selected>-- Select Compartment --</option>
            //                         <option value="CC">CC</option>
            //                             <option value="MC">MC</option>
            //                             <option value="BB">BB</option>
            //                             <option value="LVC">LVC</option>
            //                             <option value="PRC">PRC</option>
            //                             <option value="200RB">200RB</option>
            //                             <option value="400RB">400RB</option>
            //                             <option value="600RB">600RB</option>
            //                             <option value="800RB">800RB</option>
            //                             <option value="1000RB">1000RB</option>
            //                             <option value="Other">Other</option>
            //                     </select>
            //                 </div>
            //             </div>

            //             <!-- Add this div here -->
            //             <div id="dynamic-fields-container"></div>

            //             <div class="form-row mt-3">
            //                 <div class="form-group col-lg-12">
            //                     <label class="form-label">Short Text <span class="required">*</span></label>
            //                     <textarea class="form-control required" id="shortTextInput" name="short_text" placeholder="Short Text will be generated automatically" ></textarea>
            //                 </div>
            //             </div>

            //             <div class="form-row mt-3">
            //                 <div class="form-group col-lg-12">
            //                     <label class="form-label">Remarks</label>
            //                     <textarea class="form-control" id="remarks" name="remarks" placeholder="Enter Remarks"></textarea>
            //                 </div>
            //             </div>
            //         </div>
            //     `;

            //     const materialTypeDiv = document.getElementById('material_type').closest('.row');
            //     materialTypeDiv.parentNode.insertBefore(additionalFieldsDiv, materialTypeDiv.nextSibling);
            // }
            else if (materialType === 'sheet_metal') {
                const productName = document.getElementById('product_name').value;
                const drawingName = document.getElementById('drawing_name').value;

                if (productName === 'NXAIR' && drawingName === '3') {
                    // NXAIR 3 code
                    const additionalFieldsDiv = document.createElement('div');
                    additionalFieldsDiv.id = 'additional-fields';
                    additionalFieldsDiv.className = 'row justify-content-center m-lg-4';
                    additionalFieldsDiv.innerHTML = `
                    <div class="col-12">
                    <div class="form-row">
                    <div class="form-group col-lg-3">
                        <label class="form-label">KA Rating <span class="required">*</span></label>
                        <select class="form-control required" id="ka_rating" name="ka_rating" onchange="updateSheetMetalShortText()">
                            <option value="" disabled selected>-- Select kA Rating --</option>
                            <option value="Upto 31.5 kA">Upto 31.5 kA</option>
                            <option value="40 kA">40 kA</option>
                            <option value="50 kA">50 kA</option>
                        </select>
                    </div>
                    
                    <div class="form-group col-lg-3">
                        <label class="form-label">Width <span class="required">*</span></label>
                        <select class="form-control required" id="width" name="width" onchange="handleOtherOption(this); updateSheetMetalShortText()">
                            <option value="" disabled selected>Select Width</option>
                            <option value="435W">435W</option>
                            <option value="600W">600W</option>
                            <option value="800W">800W</option>
                            <option value="1000W">1000W</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group col-lg-3">
                        <label class="form-label">Description <span class="required">*</span></label>
                        <select class="form-control required" id="description" name="description"  
                            onchange="handleSheetMetalDescription(this); handleOtherOption(this); updateSheetMetalShortText()">
                            <option value="" disabled selected>-- Select Description --</option>
                            <option value="End Cover Assly">End Cover Assly</option>
                            <option value="Gland Plate Assly">Gland Plate Assly</option>
                            <option value="BASE FRAME">BASE FRAME</option>
                            <option value="Rear Cover Assly">Rear Cover Assly</option>
                            <option value="LHS Pnl End Wall Assly">LHS Pnl End Wall Assly</option>
                            <option value="RHS Pnl End Wall Assly">RHS Pnl End Wall Assly</option>
                            <option value="Rear Box Assly">Rear Box Assly</option>
                            <option value="RB Mid panel Ledges Assly">RB Mid panel Ledges Assly</option>
                            <option value="Insulator Supports Assly">Insulator Supports Assly</option>
                            <option value="CT Supports Assly">CT Supports Assly</option>
                            <option value="PT Supports Assly">PT Supports Assly</option>
                            <option value="Cable Supports Assly">Cable Supports Assly</option>
                            <option value="SA Supports Assly">SA Supports Assly</option>
                            <option value="CVD Supports Assly">CVD Supports Assly</option>
                            <option value="EM Interlock Assly">EM Interlock Assly</option>
                            <option value="Meshwire Assly">Meshwire Assly</option>
                            <option value="Limit S/W Assly">Limit S/W Assly</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                                    
                    <div class="form-group col-lg-3">
                        <label class="form-label">Compartment <span class="required">*</span></label>
                        <select class="form-control required" id="rear_box" name="rear_box" onchange="handleOtherOption(this); updateSheetMetalShortText()">
                            <option value="" disabled selected>-- Select Compartment --</option>
                            <option value="CC">CC</option>
                            <option value="MC">MC</option>
                            <option value="BB">BB</option>
                            <option value="LVC">LVC</option>
                            <option value="PRC">PRC</option>
                            <option value="200RB">200RB</option>
                            <option value="400RB">400RB</option>
                            <option value="600RB">600RB</option>
                            <option value="800RB">800RB</option>
                            <option value="1000RB">1000RB</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                <div id="dynamic-fields-container"></div>

                <div class="form-row mt-3">
                    <div class="form-group col-lg-12">
                        <label class="form-label">Short Text <span class="required">*</span></label>
                        <textarea class="form-control required" id="shortTextInput" name="short_text" placeholder="Short Text will be generated automatically" ></textarea>
                    </div>
                </div>
                
                <div class="form-row mt-3">
                    <div class="form-group col-lg-12">
                        <label class="form-label">Remarks</label>
                        <textarea class="form-control" id="remarks" name="remarks" placeholder="Enter Remarks"></textarea>
                    </div>
                </div>
            </div>
        `;

                    const materialTypeDiv = document.getElementById('material_type').closest('.row');
                    materialTypeDiv.parentNode.insertBefore(additionalFieldsDiv, materialTypeDiv.nextSibling);
                } else if (productName === 'NXAIR' && drawingName === '0') {
                    // NXAIR 0 code
                    const additionalFieldsDiv = document.createElement('div');
                    additionalFieldsDiv.id = 'additional-fields';
                    additionalFieldsDiv.className = 'row justify-content-center m-lg-4';
                    additionalFieldsDiv.innerHTML = `
                    <div class="col-12">
                    <div class="form-row">
                    <div class="form-group col-lg-3">
                        <label class="form-label">KA Rating <span class="required">*</span></label>
                        <select class="form-control required" id="ka_rating" name="ka_rating" onchange="updateNXAIR0SheetMetalShortText()">
                            <option value="" disabled selected>-- Select kA Rating --</option>
                            <option value="Upto 31.5 kA">Upto 31.5 kA</option>
                            <option value="40 kA">40 kA</option>
                            <option value="50 kA">50 kA</option>
                        </select>
                    </div>

                    <div class="form-group col-lg-3">
                        <label class="form-label">Width <span class="required">*</span></label>
                        <select class="form-control required" id="width" name="width" onchange="handleOtherOption(this); updateNXAIR0SheetMetalShortText()">
                            <option value="" disabled selected>Select Width</option>
                            <option value="435W">435W</option>
                            <option value="600W">600W</option>
                            <option value="800W">800W</option>
                            <option value="1000W">1000W</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group col-lg-3">
                        <label class="form-label">Compartment <span class="required">*</span></label>
                        <select class="form-control required" id="rear_box" name="rear_box" onchange="handleOtherOption(this); updateNXAIR0SheetMetalShortText()">
                            <option value="" disabled selected>-- Select Compartment --</option>
                            <option value="CC">CC</option>
                            <option value="MC">MC</option>
                            <option value="BB">BB</option>
                            <option value="LVC">LVC</option>
                            <option value="PRC">PRC</option>
                            <option value="200RB">200RB</option>
                            <option value="400RB">400RB</option>
                            <option value="600RB">600RB</option>
                            <option value="800RB">800RB</option>
                            <option value="1000RB">1000RB</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="form-group col-lg-3">
                        <label class="form-label">Description <span class="required">*</span></label>
                        <select class="form-control required" id="description" name="description" onchange="handleNXAIR0SheetMetalDescription(this); handleOtherOption(this); updateNXAIR0SheetMetalShortText()">
                            <option value="" disabled selected>-- Select Description --</option>
                            <option value="LHS_Pnl_End_Wall">LHS Pnl End Wall</option>
                            <option value="RHS_Pnl_End_Wall">RHS Pnl End Wall</option>
                            <option value="End_Wall_Ledges">End Wall Ledges</option>
                            <option value="RB_Side_Wall_LHS">RB Side Wall LHS</option>
                            <option value="RB_Side_Wall_RHS">RB Side Wall RHS</option>
                            <option value="RB_Top_Ledges_LHS">RB Top Ledges LHS</option>
                            <option value="RB_Top_Ledges_RHS">RB Top Ledges RHS</option>
                            <option value="RB_Bottom_Ledges_LHS">RB Bottom Ledges LHS</option>
                            <option value="RB_Bottom_Ledges_RHS">RB Bottom Ledges RHS</option>
                            <option value="RB_Top_Sheet">RB Top Sheet</option>
                            <option value="RB_Bottom_Sheet">RB Bottom Sheet</option>
                            <option value="RB_Top_Angle">RB Top Angle</option>
                            <option value="RB_Bottom_Angle">RB Bottom Angle</option>
                            <option value="Insulator_Support">Insulator Support</option>
                            <option value="CT_Support">CT Support</option>
                            <option value="PT_Support">PT Support</option>
                            <option value="Cable_Support">Cable Support</option>
                            <option value="CT_Support_on_Side_Wall">CT Support on Side Wall</option>
                            <option value="SA_Support">SA Support</option>
                            <option value="CVD_Support">CVD Support</option>
                            <option value="Front_Cover">Front Cover</option>
                            <option value="CBCT_Support">CBCT Support</option>
                            <option value="Rear_Cover">Rear Cover</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="form-group col-lg-3">
                        <label class="form-label">Thickness <span class="required">*</span></label>
                        <select class="form-control required" id="thickness" name="thickness" onchange="updateNXAIR0SheetMetalShortText()">
                            <option value="" disabled selected>-- Select Thickness --</option>
                            <option value="1">1mm</option>
                            <option value="1.5">1.5mm</option>
                            <option value="2">2mm</option>
                            <option value="2.5">2.5mm</option>
                            <option value="3">3mm</option>
                            <option value="4">4mm</option>
                            <option value="5">5mm</option>
                            <option value="6">6mm</option>
                        </select>
                    </div>

                    <div class="form-group col-lg-3">
                        <label class="form-label">Material <span class="required">*</span></label>
                        <select class="form-control required" id="material" name="material" onchange="handleOtherOption(this);updateNXAIR0SheetMetalShortText()">
                            <option value="" disabled selected>-- Select Material --</option>
                            <option value="Al">AL</option>
                            <option value="MS">MS</option>
                            <option value="SS">SS</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                <div id="dynamic-fields-container"></div>

                <div class="form-row mt-3">
                    <div class="form-group col-lg-12">
                        <label class="form-label">Short Text <span class="required">*</span></label>
                        <textarea class="form-control required" id="shortTextInput" name="short_text" placeholder="Short Text will be generated automatically" ></textarea>
                    </div>
                </div>
                
                <div class="form-row mt-3">
                    <div class="form-group col-lg-12">
                        <label class="form-label">Remarks</label>
                        <textarea class="form-control" id="remarks" name="remarks" placeholder="Enter Remarks"></textarea>
                    </div>
                </div>
            </div>
        `;

                    const materialTypeDiv = document.getElementById('material_type').closest('.row');
                    materialTypeDiv.parentNode.insertBefore(additionalFieldsDiv, materialTypeDiv.nextSibling);
                }
            } 
        else if (materialType === 'shrouds') {
                const additionalFieldsDiv = document.createElement('div');
                additionalFieldsDiv.id = 'additional-fields';
                additionalFieldsDiv.className = 'row justify-content-center m-lg-4';
                additionalFieldsDiv.innerHTML = `
            <div class="col-12">
                <div class="form-row">
                <div class="form-group col-lg-3">
                    <label class="form-label">KA Rating <span class="required">*</span></label>
                    <select class="form-control required" id="ka_rating" name="ka_rating" onchange="updateShroudsShortText()">
                        <option value="" disabled selected>-- Select kA Rating --</option>
                        <option value="Upto 31.5 kA">Upto 31.5 kA</option>
                        <option value="40 kA">40 kA</option>
                        <option value="50 kA">50 kA</option>
                    </select>
                </div>
                
                <div class="form-group col-lg-3">
                    <label class="form-label">Width <span class="required">*</span></label>
                    <select class="form-control required" id="width" name="width" onchange="handleOtherOption(this); updateShroudsShortText()">
                        <option value="" disabled selected>Select Width</option>
                        <option value="435W">435W</option>
                        <option value="600W">600W</option>
                        <option value="800W">800W</option>
                        <option value="1000W">1000W</option>                         
                        <option value="Other">Other</option>
                    </select>
                </div>
                
                <div class="form-group col-lg-3">
                    <label class="form-label">Compartment  <span class="required">*</span></label>
                    <select class="form-control required" id="rear_box" name="rear_box" onchange="handleOtherOption(this); updateShroudsShortText()">
                        <option value="" disabled selected>-- Select Compartment  --</option>
                        <option value="CC">CC</option>
                            <option value="BB">BB</option>
                            <option value="Other">Other</option>
                    </select>
                </div>

                <div class="form-group col-lg-3">
                    <label class="form-label">Number of MBB Run <span class="required">*</span></label>
                    <select class="form-control required" id="mbb_run" name="mbb_run" onchange="handleOtherOption(this); updateShroudsShortText()">
                        <option value="" disabled selected>-- Select MBB Run --</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>

            <div class="form-row mt-3">
                <div class="form-group col-lg-3">
                    <label class="form-label">MBB Size <span class="required">*</span></label>
                    <select class="form-control required" id="mbb_size" name="mbb_size" onchange="handleOtherOption(this); updateShroudsShortText()">
                        <option value="" disabled selected>-- Select MBB Size --</option>
                        <option value="60x10">60x10</option>
                        <option value="80x10">80x10</option>
                        <option value="100x10">100x10</option>
                        <option value="120x10">120x10</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="form-group col-lg-3">
                    <label class="form-label">Number of Feeder Run <span class="required">*</span></label>
                    <select class="form-control required" id="feeder_run" name="feeder_run" onchange="handleOtherOption(this); updateShroudsShortText()">
                        <option value="" disabled selected>-- Select Feeder Run --</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="form-group col-lg-3">
                    <label class="form-label">Feeder Size <span class="required">*</span></label>
                    <select class="form-control required" id="feeder_size" name="feeder_size" onchange="handleOtherOption(this); updateShroudsShortText()">
                        <option value="" disabled selected>-- Select Feeder Size --</option>
                        <option value="60x10">60x10</option>
                        <option value="80x10">80x10</option>
                        <option value="100x10">100x10</option>
                        <option value="120x10">120x10</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="form-group col-lg-3">
                    <label class="form-label">Material <span class="required">*</span></label>
                    <select class="form-control required" id="material" name="material" onchange="handleOtherOption(this); updateShroudsShortText()">
                        <option value="" disabled selected>-- Select Material --</option>
                        <option value="Shrouds">Shrouds</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>

            <div class="form-row mt-3">
                <div class="form-group col-lg-12">
                    <label class="form-label">Short Text <span class="required">*</span></label>
                    <textarea class="form-control required" id="shortTextInput" name="short_text" placeholder="Short Text will be generated automatically" ></textarea>
                </div>
            </div>
            
            <div class="form-row mt-3">
                <div class="form-group col-lg-12">
                    <label class="form-label">Remarks</label>
                    <textarea class="form-control" id="remarks" name="remarks" placeholder="Enter Remarks"></textarea>
                </div>
            </div>
        </div>
    `;

                const materialTypeDiv = document.getElementById('material_type').closest('.row');
                materialTypeDiv.parentNode.insertBefore(additionalFieldsDiv, materialTypeDiv.nextSibling);
            } else if (materialType === 'isolation') {
                const additionalFieldsDiv = document.createElement('div');
                additionalFieldsDiv.id = 'additional-fields';
                additionalFieldsDiv.className = 'row justify-content-center m-lg-4';
                additionalFieldsDiv.innerHTML = `
        <div class="col-12">
            <div class="form-row">
                <div class="form-group col-lg-3">
                    <label class="form-label">KA Rating <span class="required">*</span></label>
                    <select class="form-control required" id="ka_rating" name="ka_rating" onchange="updateIsolationShortText()">
                        <option value="" disabled selected>-- Select kA Rating --</option>
                        <option value="Upto 31.5 kA">Upto 31.5 kA</option>
                        <option value="40 kA">40 kA</option>
                        <option value="50 kA">50 kA</option>
                    </select>
                </div>
                
                <div class="form-group col-lg-3">
                    <label class="form-label">Width <span class="required">*</span></label>
                    <select class="form-control required" id="width" name="width" onchange="handleOtherOption(this); updateIsolationShortText()">
                        <option value="" disabled selected>Select Width</option>
                        <option value="435W">435W</option>
                        <option value="600W">600W</option>
                        <option value="800W">800W</option>
                        <option value="1000W">1000W</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                
                <div class="form-group col-lg-3">
                    <label class="form-label">Compartment  <span class="required">*</span></label>
                    <select class="form-control required" id="rear_box" name="rear_box" onchange="handleOtherOption(this); updateIsolationShortText()">
                        <option value="" disabled selected>-- Select Compartment  --</option>
                        <option value="CC">CC</option>
                            <option value="MC">MC</option>
                            <option value="BB">BB</option>
                            <option value="LVC">LVC</option>
                            <option value="PRC">PRC</option>
                            <option value="200RB">200RB</option>
                            <option value="400RB">400RB</option>
                            <option value="600RB">600RB</option>
                            <option value="800RB">800RB</option>
                            <option value="1000RB">1000RB</option>
                            <option value="Other">Other</option>
                    </select>
                </div>

                <div class="form-group col-lg-3">
                    <label class="form-label">Material <span class="required">*</span></label>
                    <select class="form-control required" id="material" name="material" onchange="handleOtherOption(this); updateIsolationShortText()">
                        <option value="" disabled selected>-- Select Material --</option>
                        <option value="FRP">FRP</option>
                        <option value="Polycarbonate">Polycarbonate</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>

            <div class="form-row mt-3">
                <div class="form-group col-lg-12">
                    <label class="form-label">Short Text <span class="required">*</span></label>
                    <textarea class="form-control required" id="shortTextInput" name="short_text" placeholder="Short Text will be generated automatically" ></textarea>
                </div>
            </div>
            
            <div class="form-row mt-3">
                <div class="form-group col-lg-12">
                    <label class="form-label">Remarks</label>
                    <textarea class="form-control" id="remarks" name="remarks" placeholder="Enter Remarks"></textarea>
                </div>
            </div>
        </div>
    `;

                const materialTypeDiv = document.getElementById('material_type').closest('.row');
                materialTypeDiv.parentNode.insertBefore(additionalFieldsDiv, materialTypeDiv.nextSibling);
            } else if (materialType === 'gfk') {
                const additionalFieldsDiv = document.createElement('div');
                additionalFieldsDiv.id = 'additional-fields';
                additionalFieldsDiv.className = 'row justify-content-center m-lg-4';
                additionalFieldsDiv.innerHTML = `
        <div class="col-12">
            <div class="form-row">
                <div class="form-group col-lg-3">
                    <label class="form-label">KA Rating <span class="required">*</span></label>
                    <select class="form-control required" id="ka_rating" name="ka_rating" onchange="updateGFKShortText()">
                        <option value="" disabled selected>-- Select kA Rating --</option>
                        <option value="Upto 31.5 kA">Upto 31.5 kA</option>
                        <option value="40 kA">40 kA</option>
                        <option value="50 kA">50 kA</option>
                    </select>
                </div>
                
                <div class="form-group col-lg-3">
                    <label class="form-label">Thickness <span class="required">*</span></label>
                    <select class="form-control required" id="thickness" name="thickness" onchange="handleOtherOption(this); updateGFKShortText()">
                        <option value="" disabled selected>-- Select Thickness --</option>
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <!-- <div class="form-group col-lg-3">
                    <label class="form-label">Width <span class="required">*</span></label>
                    <select class="form-control required" id="width" name="width" onchange="handleOtherOption(this); updateGFKShortText()">
                        <option value="" disabled selected>Select Width</option>
                        <option value="435W">435W</option>
                        <option value="600W">600W</option>
                        <option value="800W">800W</option>
                        <option value="1000W">1000W</option>
                        <option value="Other">Other</option>
                    </select>
                </div> -->
                
                <div class="form-group col-lg-3">
                    <label class="form-label">Compartment  <span class="required">*</span></label>
                    <select class="form-control required" id="rear_box" name="rear_box" onchange="handleOtherOption(this); updateGFKShortText()">
                        <option value="" disabled selected>-- Select Compartment  --</option>
                        <option value="CC">CC</option>
                            <option value="MC">MC</option>
                            <option value="BB">BB</option>
                            <option value="200RB">200RB</option>
                            <option value="400RB">400RB</option>
                            <option value="600RB">600RB</option>
                            <option value="800RB">800RB</option>
                            <option value="1000RB">1000RB</option>
                            <option value="Other">Other</option>
                    </select>
                </div>
            </div>

            <div class="form-row mt-3">
                <div class="form-group col-lg-3">
                    <label class="form-label">Size of Busbar <span class="required">*</span></label>
                    <select class="form-control required" id="sizeofbusbar" name="sizeofbusbar" onchange="handleOtherOption(this); updateGFKShortText()">
                        <option value="" disabled selected>-- Select Busbar Size --</option>
                        <option value="60">60</option>
                            <option value="80">80</option>
                            <option value="100">100</option>
                            <option value="120">120</option>
                            <option value="160">160</option>
                            <option value="Other">Other</option>
                    </select>
                </div>

                <div class="form-group col-lg-3">
                    <label class="form-label">Material <span class="required">*</span></label>
                    <select class="form-control required" id="material" name="material" onchange="handleOtherOption(this); updateGFKShortText()">
                        <option value="" disabled selected>-- Select Material --</option>
                        <option value="GFK">GFK</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="form-group col-lg-3">
                    <label class="form-label">Adjacent Panel <span class="required">*</span></label>
                    <select class="form-control required" id="panel_width" name="panel_width" onchange="updateGFKShortText()">
                        <option value="" disabled selected>-- Select Adjacent Panel --</option>
                        <option value="435-435">435-435</option>
                        <option value="435-600">435-600</option>
                        <option value="435-800">435-800</option>
                        <option value="435-1000">435-1000</option>
                        <option value="600-600">600-600</option>
                        <option value="600-800">600-800</option>
                        <option value="600-1000">600-1000</option>
                        <option value="800-800">800-800</option>
                        <option value="800-1000">800-1000</option>
                        <option value="1000-1000">1000-1000</option>
                    </select>
                </div>
            </div>

            <div class="form-row mt-3">
                <div class="form-group col-lg-12">
                    <label class="form-label">Short Text <span class="required">*</span></label>
                    <textarea class="form-control required" id="shortTextInput" name="short_text" placeholder="Short Text will be generated automatically" ></textarea>
                </div>
            </div>
            
            <div class="form-row mt-3">
                <div class="form-group col-lg-12">
                    <label class="form-label">Remarks</label>
                    <textarea class="form-control" id="remarks" name="remarks" placeholder="Enter Remarks"></textarea>
                </div>
            </div>
        </div>
    `;

                const materialTypeDiv = document.getElementById('material_type').closest('.row');
                materialTypeDiv.parentNode.insertBefore(additionalFieldsDiv, materialTypeDiv.nextSibling);
            } else if (materialType === 'others') {
                const productName = document.getElementById('product_name').value;
                const drawingName = document.getElementById('drawing_name').value;

                if (productName === 'NXAIR' && drawingName === '3') {
                const additionalFieldsDiv = document.createElement('div');
                additionalFieldsDiv.id = 'additional-fields';
                additionalFieldsDiv.className = 'row justify-content-center m-lg-4';
                additionalFieldsDiv.innerHTML = `
        <div class="col-12">
            <div class="form-row">
                <div class="form-group col-lg-4">
                    <label class="form-label">KA Rating <span class="required">*</span></label>
                    <select class="form-control required" id="ka_rating" name="ka_rating" onchange="updateOthersShortText()">
                        <option value="" disabled selected>-- Select kA Rating --</option>
                        <option value="Upto 31.5 kA">Upto 31.5 kA</option>
                        <option value="40 kA">40 kA</option>
                        <option value="50 kA">50 kA</option>
                    </select>
                </div>
                
                <div class="form-group col-lg-4">
                    <label class="form-label">Width <span class="required">*</span></label>
                    <select class="form-control required" id="width" name="width" onchange="handleOtherOption(this); updateOthersShortText()">
                        <option value="" disabled selected>Select Width</option>
                        <option value="435W">435W</option>
                        <option value="600W">600W</option>
                        <option value="800W">800W</option>
                        <option value="1000W">1000W</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                
                <div class="form-group col-lg-4">
                    <label class="form-label">Description <span class="required">*</span></label>
                    <select class="form-control required" id="description" name="description" onchange="handleOtherOption(this); updateOthersShortText()">
                        <option value="" disabled selected>-- Select Description --</option>
                        <option value="CB panel">CB panel</option>
                        <option value=Metering panel">Metering panel</option>
                        <option value="BC">BC</option>
                        <option value="BR">BR</option>
                        <option value="Extended MBB">Extended MBB</option>
                        <option value="CB Panel with DOVT">CB Panel with DOVT</option>
                        <option value="Contactor panel">Contactor panel</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>

            <div class="form-row mt-3">
                <div class="form-group col-lg-12">
                    <label class="form-label">Short Text <span class="required">*</span></label>
                    <textarea class="form-control required" id="shortTextInput" name="short_text" placeholder="Short Text will be generated automatically" ></textarea>
                </div>
            </div>
            
            <div class="form-row mt-3">
                <div class="form-group col-lg-12">
                    <label class="form-label">Remarks</label>
                    <textarea class="form-control" id="remarks" name="remarks" placeholder="Enter Remarks"></textarea>
                </div>
            </div>
        </div>
    `;

                const materialTypeDiv = document.getElementById('material_type').closest('.row');
                materialTypeDiv.parentNode.insertBefore(additionalFieldsDiv, materialTypeDiv.nextSibling);
            }
            else if (productName === 'NXAIR' && drawingName === '0') {
    // NXAIR 0 Others code
    const additionalFieldsDiv = document.createElement('div');
    additionalFieldsDiv.id = 'additional-fields';
    additionalFieldsDiv.className = 'row justify-content-center m-lg-4';
    additionalFieldsDiv.innerHTML = `
        <div class="col-12">
            <div class="form-row">
                <div class="form-group col-lg-3">
                    <label class="form-label">KA Rating <span class="required">*</span></label>
                    <select class="form-control required" id="ka_rating" name="ka_rating" onchange="updateOthers_0ShortText()">
                        <option value="" disabled selected>-- Select kA Rating --</option>
                        <option value="Upto 31.5 kA">Upto 31.5 kA</option>
                        <option value="40 kA">40 kA</option>
                        <option value="50 kA">50 kA</option>
                    </select>
                </div>

                <div class="form-group col-lg-3">
                    <label class="form-label">Width <span class="required">*</span></label>
                    <select class="form-control required" id="width" name="width" onchange="handleOtherOption(this); updateOthers_0ShortText()">
                        <option value="" disabled selected>Select Width</option>
                        <option value="435W">435W</option>
                        <option value="600W">600W</option>
                        <option value="800W">800W</option>
                        <option value="1000W">1000W</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                
                <div class="form-group col-lg-3">
                    <label class="form-label">Compartment <span class="required">*</span></label>
                    <select class="form-control required" id="rear_box" name="rear_box" onchange="handleOtherOption(this); updateOthers_0ShortText()">
                        <option value="" disabled selected>-- Select Compartment --</option>
                        <option value="CC">CC</option>
                        <option value="MC">MC</option>
                        <option value="BB">BB</option>
                        <option value="LVC">LVC</option>
                        <option value="PRC">PRC</option>
                        <option value="200RB">200RB</option>
                        <option value="400RB">400RB</option>
                        <option value="600RB">600RB</option>
                        <option value="800RB">800RB</option>
                        <option value="1000RB">1000RB</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="form-group col-lg-3">
                    <label class="form-label">Material <span class="required">*</span></label>
                    <select id="material" name="material" class="form-control required" onchange="handleOtherOption(this); updateOthers_0ShortText()">
                        <option value="" disabled selected>-- Select Material --</option>
                        <option value="Al">AL</option>
                        <option value="MS">MS</option>
                        <option value="SS">SS</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>

            <div id="dynamic-fields-container"></div>

            <div class="form-row mt-3">
                <div class="form-group col-lg-12">
                    <label class="form-label">Short Text <span class="required">*</span></label>
                    <textarea class="form-control required" id="shortTextInput" name="short_text" placeholder="Short Text will be generated automatically"></textarea>
                </div>
            </div>
            
            <div class="form-row mt-3">
                <div class="form-group col-lg-12">
                    <label class="form-label">Remarks</label>
                    <textarea class="form-control" id="remarks" name="remarks" placeholder="Enter Remarks"></textarea>
                </div>
            </div>
        </div>
    `;

    const materialTypeDiv = document.getElementById('material_type').closest('.row');
    materialTypeDiv.parentNode.insertBefore(additionalFieldsDiv, materialTypeDiv.nextSibling);
}
            }
            else if (materialType === 'gland_plate') {
        const additionalFieldsDiv = document.createElement('div');
        additionalFieldsDiv.id = 'additional-fields';  // Use consistent ID
        additionalFieldsDiv.className = 'row justify-content-center m-lg-4';
        
        additionalFieldsDiv.innerHTML = `
            <div class="col-12">
                <div class="form-row">
                    <!-- KA Rating -->
                    <div class="form-group col-lg-4">
                        <label class="form-label">KA Rating <span style="color: red;">*</span></label>
                        <select id="ka_rating" name="ka_rating" class="form-control required" onchange="handleOtherOption(this); updateGlandPlateShortText()">
                            <option value="" disabled selected>-- Select KA Rating --</option>
                            <option value="Upto 31.5 kA">Upto 31.5 kA</option>
                            <option value="40 kA">40 kA</option>
                            <option value="50 kA">50 kA</option>
                        </select>
                    </div>

                    <!-- Width -->
                    <div class="form-group col-lg-4">
                        <label class="form-label">Width <span style="color: red;">*</span></label>
                        <select id="width" name="width" class="form-control required" onchange="handleOtherOption(this); updateGlandPlateShortText()">
                            <option value="" disabled selected>-- Select Width --</option>
                            <option value="435W">435W</option>
                            <option value="600W">600W</option>
                            <option value="800W">800W</option>
                            <option value="1000W">1000W</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <!-- Rear Box -->
                    <div class="form-group col-lg-4">
                        <label class="form-label">Rear Box <span style="color: red;">*</span></label>
                        <select id="rear_box" name="rear_box" class="form-control required" onchange="handleOtherOption(this); updateGlandPlateShortText()">
                            <option value="" disabled selected>-- Select Compartment --</option>
                            <option value="CC">CC</option>
                            <option value="MC">MC</option>
                            <option value="BB">BB</option>
                            <option value="200RB">200RB</option>
                            <option value="400RB">400RB</option>
                            <option value="600RB">600RB</option>
                            <option value="800RB">800RB</option>
                            <option value="1000RB">1000RB</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <!-- Material -->
                    <div class="form-group col-lg-4">
                        <label class="form-label">Material <span style="color: red;">*</span></label>
                        <select id="material" name="material" class="form-control required" onchange="handleOtherOption(this); updateGlandPlateShortText()">
                            <option value="" disabled selected>-- Select Material --</option>
                            <option value="Al">AL</option>
                            <option value="MS">MS</option>
                            <option value="SS">SS</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <!-- Thickness -->
                    <div class="form-group col-lg-4">
                        <label class="form-label">Thickness <span style="color: red;">*</span></label>
                        <select id="thickness" name="thickness" class="form-control required" onchange="handleOtherOption(this); updateGlandPlateShortText()">
                            <option value="" disabled selected>-- Select Thickness --</option>
                            <option value="1">1mm</option>
                            <option value="1.5">1.5mm</option>
                            <option value="2">2mm</option>
                            <option value="2.5">2.5mm</option>
                            <option value="3">3mm</option>
                            <option value="4">4mm</option>
                            <option value="5">5mm</option>
                            <option value="6">6mm</option>
                        </select>
                    </div>

                    <!-- Description -->
                    <div class="form-group col-lg-4">
                        <label class="form-label">Description <span style="color: red;">*</span></label>
                        <select id="description" name="description" class="form-control required" onchange="handleOtherOption(this);updateGlandPlateShortText()">
                            <option value="" disabled selected>-- Select Description --</option>
                            <option value="Rear_Box_Gland_Plate_Top">Rear Box Gland Plate Top</option>
                            <option value="Rear_Box_Gland_Plate_Bottom">Rear Box Gland Plate Bottom</option>
                            <option value="Control_Cable_Gland_Plate">Control Cable Gland Plate</option>
                            <option value="Gland_Plate">Gland Plate</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <!-- Short Text -->
                    <div class="form-group col-lg-6">
                        <label class="form-label">Short Text <span class="required">*</span></label>
                    <textarea class="form-control required" id="shortTextInput" name="short_text" placeholder="Short Text will be generated automatically" ></textarea>
                    </div>

                    <!-- Remark -->
                    <div class="form-group col-lg-6">
                        <label class="form-label">Remark</label>
                        <textarea id="remark" name="remark" class="form-control"></textarea>
                    </div>
                </div>
            </div>
        `;

        const materialTypeDiv = document.getElementById('material_type').closest('.row');
        materialTypeDiv.parentNode.insertBefore(additionalFieldsDiv, materialTypeDiv.nextSibling);
    } else if (materialType === 'insulation') {
                    const additionalFieldsDiv = document.createElement('div');
                    additionalFieldsDiv.id = 'additional-fields';  // Use consistent ID
                    additionalFieldsDiv.className = 'row justify-content-center m-lg-4';

                    additionalFieldsDiv.innerHTML = `
                        <div class="col-12">
                            <div class="form-row">
                                <!-- KA Rating -->
                                <div class="form-group col-lg-4">
                                    <label class="form-label">KA Rating <span style="color: red;">*</span></label>
                                    <select id="ka_rating" name="ka_rating" class="form-control required" onchange="updateInsulationShortText()">
                                        <option value="" disabled selected>-- Select KA Rating --</option>
                                        <option value="Upto 31.5 kA">Upto 31.5 kA</option>
                                        <option value="40 kA">40 kA</option>
                                        <option value="50 kA">50 kA</option>
                                    </select>
                                </div>

                                <!-- Width -->
                                <div class="form-group col-lg-4">
                                    <label class="form-label">Width <span style="color: red;">*</span></label>
                                    <select id="width" name="width" class="form-control required" onchange="handleOtherOption(this); updateInsulationShortText()">
                                        <option value="" disabled selected>-- Select Width --</option>
                                        <option value="435W">435W</option>
                                        <option value="600W">600W</option>
                                        <option value="800W">800W</option>
                                        <option value="1000W">1000W</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>

                                <!-- Rear Box -->
                                <div class="form-group col-lg-4">
                                    <label class="form-label">Rear Box <span style="color: red;">*</span></label>
                                    <select id="rear_box" name="rear_box" class="form-control required" onchange="handleOtherOption(this); updateInsulationShortText()">
                                        <option value="" disabled selected>-- Select Compartment --</option>
                                        <option value="CC">CC</option>
                                        <option value="MC">MC</option>
                                        <option value="BB">BB</option>
                                        <option value="200RB">200RB</option>
                                        <option value="400RB">400RB</option>
                                        <option value="600RB">600RB</option>
                                        <option value="800RB">800RB</option>
                                        <option value="1000RB">1000RB</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <!-- Material -->
                                <div class="form-group col-lg-4">
                                    <label class="form-label">Material <span style="color: red;">*</span></label>
                                    <select id="material" name="material" class="form-control required" onchange="handleOtherOption(this); updateInsulationShortText()">
                                        <option value="" disabled selected>-- Select Material --</option>
                                        <option value="INS">INS</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>

                                <!-- Thickness -->
                                <div class="form-group col-lg-4">
                                    <label class="form-label">Thickness <span style="color: red;">*</span></label>
                                    <select id="thickness" name="thickness" class="form-control required" onchange="handleOtherOption(this); updateInsulationShortText()">
                                        <option value="" disabled selected>-- Select Thickness --</option>
                                        <option value="2">2mm</option>
                                        <option value="3">3mm</option>
                                        <option value="5">5mm</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>

                                <!-- Description -->
                                <div class="form-group col-lg-4">
                                    <label class="form-label">Description <span style="color: red;">*</span></label>
                                    <select id="description" name="description" class="form-control required" onchange="updateInsulationShortText()">
                                        <option value="" disabled selected>-- Select Description --</option>
                                        <option value="Insulating_Plate_Ph_to_Ph">Insulating Plate Ph to Ph</option>
                                        <option value="Insulating_Plate_Ph_to_Earth">Insulating Plate Ph to Earth</option>
                                        <option value="Transverse_partition_Sheet_80x10">Transverse partition Sheet 80x10</option>
                                        <option value="Transverse_partition_Sheet_100x10">Transverse partition Sheet 100x10</option>
                                        <option value="Transverse_partition_Sheet_120x10">Transverse partition Sheet 120x10</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row mt-3">
                <div class="form-group col-lg-12">
                    <label class="form-label">Short Text <span class="required">*</span></label>
                    <textarea class="form-control required" id="shortTextInput" name="short_text" placeholder="Short Text will be generated automatically" ></textarea>
                </div>
            </div>
            
            <div class="form-row mt-3">
                <div class="form-group col-lg-12">
                    <label class="form-label">Remarks</label>
                    <textarea class="form-control" id="remarks" name="remarks" placeholder="Enter Remarks"></textarea>
                </div>
            </div>
                        </div>
                    `;

                    const materialTypeDiv = document.getElementById('material_type').closest('.row');
                    materialTypeDiv.parentNode.insertBefore(additionalFieldsDiv, materialTypeDiv.nextSibling);
                }
            }

        function updateGlandPlateShortText() {
            const kaRating = document.getElementById('ka_rating');
            const width = document.getElementById('width');
            const rearBox = document.getElementById('rear_box');
            const material = document.getElementById('material');
            const thickness = document.getElementById('thickness');
            const shortTextInput = document.getElementById('shortTextInput'); // Changed from shortTextInput

            let kaValue = '';
            if (kaRating && kaRating.value) {
                if (kaRating.value.includes('31.5')) {
                    kaValue = '31.5kA';
                } else {
                    kaValue = kaRating.value;
                }
            }

            let widthValue = width?.value || '';
            let rearBoxValue = rearBox?.value || '';
            let materialValue = material?.value || '';
            let thicknessValue = thickness?.value || '';

            // Generate short text if all required fields are filled
            if (kaValue && widthValue && rearBoxValue && materialValue && thicknessValue) {
                const shortText = `${kaValue} ${widthValue} ${rearBoxValue} ${materialValue} ${thicknessValue}mm`;
                shortTextInput.value = shortText;
            } else {
                shortTextInput.value = '';
            }
        }


        function updateInsulationShortText() {
            const kaRating = document.getElementById('ka_rating');
            const width = document.getElementById('width');
            const rearBox = document.getElementById('rear_box');
            const material = document.getElementById('material');
            const thickness = document.getElementById('thickness');
            const description = document.getElementById('description');
            const shortTextInput = document.getElementById('shortTextInput');

            // Get KA Rating value
            let kaValue = '';
            if (kaRating && kaRating.value) {
                if (kaRating.value.includes('Upto')) {
                    kaValue = '31.5kA';
                } else if (kaRating.value.includes('40')) {
                    kaValue = '40kA';
                } else if (kaRating.value.includes('50')) {
                    kaValue = '50kA';
                }
            }

            // Get other values
            let widthValue = width?.value || '';
            let rearBoxValue = rearBox?.value || '';
            let materialValue = material?.value || '';
            let thicknessValue = thickness?.value || '';
            let descValue = description?.value || '';

            // Generate short text if all required fields are filled
            if (kaValue && widthValue && rearBoxValue && materialValue && thicknessValue && descValue) {
                const shortText = `${descValue} ${materialValue} ${thicknessValue}mm ${widthValue} ${rearBoxValue} ${kaValue}`;
                shortTextInput.value = shortText;
            } else {
                shortTextInput.value = '';
            }
        }


        function updateOthersShortText() {
            const kaRating = document.getElementById('ka_rating');
            const width = document.getElementById('width');
            const description = document.getElementById('description');
            const shortTextInput = document.getElementById('shortTextInput');

            let kaValue = '';
            if (kaRating && kaRating.value) {
                if (kaRating.value.includes('Upto')) {
                    kaValue = '31.5kA';
                } else {
                    kaValue = kaRating.value.split(' ')[0] + 'kA';
                }
            }

            let widthValue = width?.value || '';
            let descValue = description ? (description.tagName === 'INPUT' ? description.value : description.value) : '';

            // Generate short text if all required fields are filled
            if (kaValue && widthValue && descValue) {
                const shortText = `${descValue} ${widthValue} ${kaValue}`;
                shortTextInput.value = shortText;
            } else {
                shortTextInput.value = '';
            }
        }

        function updateOthers_0ShortText() {
            const kaRating = document.getElementById('ka_rating');
            const width = document.getElementById('width');
            const rearBox = document.getElementById('rear_box');
            const material = document.getElementById('material');
            const shortTextInput = document.getElementById('shortTextInput');

            // Get KA Rating value
            let kaValue = '';
            if (kaRating && kaRating.value) {
                if (kaRating.value.includes('Upto')) {
                    kaValue = '31.5kA';
                } else {
                    kaValue = kaRating.value.split(' ')[0] + 'kA';
                }
            }

            // Get other values
            let widthValue = width?.value || '';
            let rearBoxValue = rearBox?.value || '';
            let materialValue = material?.value || '';

            // Generate short text if all required fields are filled
            if (kaValue && widthValue && rearBoxValue && materialValue) {
                const shortText = `${materialValue} ${widthValue} ${rearBoxValue} ${kaValue}`;
                shortTextInput.value = shortText;
            } else {
                shortTextInput.value = '';
            }
        }

        // Update handleOtherOption function to include others case
        function handleOtherOption(selectElement) {
            if (selectElement.value === 'Other') {
                const inputElement = document.createElement('input');
                inputElement.type = 'text';
                inputElement.className = 'form-control required';
                inputElement.name = selectElement.name;
                inputElement.id = selectElement.id;
                inputElement.placeholder = 'Enter Description';

                // Add input event listener based on material type
                const materialType = document.getElementById('material_type').value;
                if (materialType === 'others') {
                    inputElement.addEventListener('input', updateOthersShortText);
                }

                selectElement.parentNode.replaceChild(inputElement, selectElement);
                inputElement.focus();

                inputElement.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        revertToSelect(this);
                    }
                });

                inputElement.addEventListener('blur', function() {
                    if (this.value.trim() === '') {
                        revertToSelect(this);
                    }
                    updateOthersShortText();
                });
            }
        }

        //Changed by geeta
        function updateGFKShortText() {
            const kaRating = document.getElementById('ka_rating');
            const thickness = document.getElementById('thickness');
            // const width = document.getElementById('width');
            const rearBox = document.getElementById('rear_box');
            const busbarSize = document.getElementById('sizeofbusbar');
            const material = document.getElementById('material');
            const panel = document.getElementById('panel_width');
            const shortTextInput = document.getElementById('shortTextInput');

            let kaValue = '';
            if (kaRating && kaRating.value) {
                if (kaRating.value.includes('Upto')) {
                    kaValue = '31.5kA';
                } else {
                    kaValue = kaRating.value.split(' ')[0] + 'kA';
                }
            }

            // let materialValue = material ? (material.tagName === 'INPUT' ? material.value : material.value) : '';
            let materialValue = material?.value || '';
            let thicknessValue = thickness?.value || '';
            //let widthValue = width?.value || '';
            let rearBoxValue = rearBox?.value || '';
            let busbarSizeValue = busbarSize?.value || '';
            let panelValue = panel?.value || '';

            // Generate short text if all required fields are filled
            if (thicknessValue && rearBoxValue && busbarSizeValue && panelValue && materialValue) {
                const shortText = `${materialValue} Assly ${panelValue} ${rearBoxValue} ${busbarSizeValue}x${thicknessValue}`;
                shortTextInput.value = shortText;
            } else {
                shortTextInput.value = '';
            }
        }

        function updateIsolationShortText() {
            const kaRating = document.getElementById('ka_rating');
            const width = document.getElementById('width');
            const rearBox = document.getElementById('rear_box');
            const material = document.getElementById('material');
            const shortTextInput = document.getElementById('shortTextInput');

            let kaValue = '';
            if (kaRating && kaRating.value) {
                if (kaRating.value.includes('Upto')) {
                    kaValue = '31.5kA';
                } else {
                    kaValue = kaRating.value.split(' ')[0] + 'kA';
                }
            }

            let widthValue = width?.value || '';
            let rearBoxValue = rearBox?.value || '';
            let materialValue = material?.value || '';

            // Generate short text if all required fields are filled
            if (kaValue && widthValue && rearBoxValue && materialValue) {
                const shortText = `${materialValue} Isolation Partition Assly ${widthValue} ${rearBoxValue} ${kaValue}`;
                shortTextInput.value = shortText;
            } else {
                shortTextInput.value = '';
            }
        }

        function updateShroudsShortText() {
            // const kaRating = document.getElementById('ka_rating');
            // const width = document.getElementById('width');
            // const rearBox = document.getElementById('rear_box');
            const mbbRun = document.getElementById('mbb_run');
            const mbbSize = document.getElementById('mbb_size');
            const feederRun = document.getElementById('feeder_run');
            const feederSize = document.getElementById('feeder_size');
            // const material = document.getElementById('material');
            const shortTextInput = document.getElementById('shortTextInput');

            // let kaValue = '';
            // if (kaRating && kaRating.value) {
            //     if (kaRating.value.includes('Upto')) {
            //         kaValue = '31.5kA';
            //     } else {
            //         kaValue = kaRating.value.split(' ')[0] + 'kA';
            //     }
            // }

            // let widthValue = width?.value || '';
            // let rearBoxValue = rearBox?.value || '';
            let mbbRunValue = mbbRun?.value || '';
            let mbbSizeValue = mbbSize?.value || '';
            let feederRunValue = feederRun?.value || '';
            let feederSizeValue = feederSize?.value || '';
            // let materialValue = material?.value || '';

            // Generate short text if all required fields are filled
            if (mbbRunValue && mbbSizeValue && feederRunValue && feederSizeValue) {
                const shortText = `Insulation Box FDR ${feederRunValue}x${feederSizeValue} MBB ${mbbRunValue}x${mbbSizeValue} `;
                shortTextInput.value = shortText;
            } else {
                shortTextInput.value = '';
            }
        }

        //function for NXAIR 3 drawing
        function handleSheetMetalDescription(selectElement) {
            // Remove existing dynamic fields
            const dynamicContainer = document.getElementById('dynamic-fields-container');
            if (dynamicContainer) {
                dynamicContainer.innerHTML = '';
            }

            // Handle different description selections
            if (selectElement.value === 'End Cover Assly') {
                const additionalFieldsHTML = `
            <div class="form-row mt-3">
                <div class="form-group col-lg-4">
                    <label class="form-label">End wall Location<span class="required">*</span></label>
                    <select class="form-control required" id="end_cover_location" name="end_cover_location" onchange="updateSheetMetalShortText()">
                        <option value="" disabled selected>-- Select Location --</option>
                        <option value="LHS">LHS</option>
                        <option value="RHS">RHS</option>
                    </select>
                </div>
                <div class="form-group col-lg-4">
                    <label class="form-label">EBB Cutout <span class="required">*</span></label>
                    <select class="form-control required" id="ebb_cutout" name="ebb_cutout" onchange="updateSheetMetalShortText()">
                        <option value="" disabled selected>-- Select EBB Cutout --</option>
                        <option value="With EBB C/O">With EBB C/O</option>
                        <option value="Without EBB C/O">Without EBB C/O</option>
                    </select>
                </div>
                <div class="form-group col-lg-4">
                    <label class="form-label">EBB Size <span class="required">*</span></label>
                    <select class="form-control required" id="ebb_size" name="ebb_size" onchange="updateSheetMetalShortText()">
                        <option value="" disabled selected>-- Select EBB Size --</option>
                        <option value="40x5">40x5</option>
                        <option value="40x10">40x10</option>
                    </select>
                </div>
            </div>
        `;
                dynamicContainer.innerHTML = additionalFieldsHTML;
            } else if (selectElement.value === 'Gland Plate Assly') {
                const additionalFieldsHTML = `
            <div class="form-row mt-3">
                <div class="form-group col-lg-4">
                    <label class="form-label">Cable Entry <span class="required">*</span></label>
                    <select class="form-control required" id="cable_entry" name="cable_entry" onchange="updateSheetMetalShortText()">
                        <option value="" disabled selected>-- Select Cable Entry --</option>
                        <option value="Top">Top</option>
                        <option value="Bottom">Bottom</option>
                    </select>
                </div>
                <div class="form-group col-lg-4">
                    <label class="form-label">Thickness <span class="required">*</span></label>
                    <select class="form-control required" id="gp_thickness" name="gp_thickness" onchange="updateSheetMetalShortText()">
                        <option value="" disabled selected>-- Select Thickness --</option>
                        <option value="2mm">2mm</option>
                        <option value="3mm">3mm</option>
                        <option value="4mm">4mm</option>
                        <option value="5mm">5mm</option>
                    </select>
                </div>
                <div class="form-group col-lg-4">
                    <label class="form-label">Material <span class="required">*</span></label>
                    <select class="form-control required" id="gp_material" name="gp_material" onchange="updateSheetMetalShortText()">
                        <option value="" disabled selected>-- Select Material --</option>
                        <option value="AL">AL</option>
                        <option value="MS">MS</option>
                        <option value="SS">SS</option>
                        <option value="Brass">Brass</option>
                        <option value="Insulating">Insulating</option>
                    </select>
                </div>
            </div>
        `;
                dynamicContainer.innerHTML = additionalFieldsHTML;
            } else if (selectElement.value === 'Rear Cover Assly') {
                const additionalFieldsHTML = `
        <div class="form-row mt-3">
            <div class="form-group col-lg-3">
                <label class="form-label">Interlock <span class="required">*</span></label>
                <select class="form-control required" id="interlock" name="interlock" onchange="updateSheetMetalShortText()">
                    <option value="" disabled selected>-- Select Interlock --</option>
                    <option value="NA">NA</option>
                    <option value="EM Interlock">EM Interlock</option>
                    <option value="Castle">Castle</option>
                    <option value="Ronis">Ronis</option>
                </select>
            </div>
            <div class="form-group col-lg-3">
                <label class="form-label">IR Window <span class="required">*</span></label>
                <select class="form-control required" id="ir_window" name="ir_window" onchange="handleIRWindowChange(this); updateSheetMetalShortText()">
                    <option value="" disabled selected>-- Select IR Window --</option>
                    <option value="NA">NA</option>
                    <option value="1 IR">1 IR</option>
                    <option value="2 IR">2 IR</option>
                </select>
            </div>
            <div class="form-group col-lg-3">
                <label class="form-label">Nameplate <span class="required">*</span></label>
                <select class="form-control required" id="nameplate" name="nameplate" onchange="updateSheetMetalShortText()">
                    <option value="" disabled selected>-- Select Nameplate --</option>
                    <option value="NA">NA</option>
                    <option value="Danger">Danger</option>
                    <option value="Board">Board</option>
                    <option value="Feeder">Feeder</option>
                </select>
            </div>
            <div class="form-group col-lg-3">
                <label class="form-label">Viewing Window <span class="required">*</span></label>
                <select class="form-control required" id="viewing_window" name="viewing_window" onchange="updateSheetMetalShortText()" disabled>
                    <option value="" disabled selected>-- Select Viewing Window --</option>
                    <option value="NA">NA</option>
                    <option value="Viewing window">Viewing window</option>
                </select>
            </div>
        </div>
    `;
                dynamicContainer.innerHTML = additionalFieldsHTML;
            } else if (selectElement.value === 'LHS Pnl End Wall Assly') {
                const additionalFieldsHTML = `
            <div class="form-row mt-3">
                <div class="form-group col-lg-6">
                    <label class="form-label">Panel Compartment  <span class="required">*</span></label>
                    <select class="form-control required" id="lhs_panel_rb" name="lhs_panel_rb" onchange="updateSheetMetalShortText()">
                        <option value="" disabled selected>-- Select Panel Compartment  --</option>
                        <option value="NA">NA</option>
                        <option value="200RB">200RB</option>
                        <option value="400RB">400RB</option>
                        <option value="600RB">600RB</option>
                        <option value="800RB">800RB</option>
                        <option value="1000RB">1000RB</option>
                    </select>
                </div>
            </div>
        `;
                dynamicContainer.innerHTML = additionalFieldsHTML;
            } else if (selectElement.value === 'RHS Pnl End Wall Assly') {
                const additionalFieldsHTML = `
            <div class="form-row mt-3">
                <div class="form-group col-lg-6">
                    <label class="form-label">Panel Compartment  <span class="required">*</span></label>
                    <select class="form-control required" id="rhs_panel_rb" name="rhs_panel_rb" onchange="updateSheetMetalShortText()">
                        <option value="" disabled selected>-- Select Panel Compartment  --</option>
                        <option value="NA">NA</option>
                        <option value="200RB">200RB</option>
                        <option value="400RB">400RB</option>
                        <option value="600RB">600RB</option>
                        <option value="800RB">800RB</option>
                        <option value="1000RB">1000RB</option>
                    </select>
                </div>
            </div>
        `;
                dynamicContainer.innerHTML = additionalFieldsHTML;
            } else if (selectElement.value === 'Rear Box Assly') {
                const additionalFieldsHTML = `
            <div class="form-row mt-3">
                <div class="form-group col-lg-6">
                    <label class="form-label">Rear Box Type <span class="required">*</span></label>
                    <select class="form-control required" id="rear_box_type" name="rear_box_type" onchange="updateSheetMetalShortText()">
                        <option value="" disabled selected>-- Select Rear Box Type --</option>
                        <option value="TOP Cable">TOP Cable</option>
                        <option value="Bottom Cable">Bottom Cable</option>
                        <option value="TOP Bus Duct">TOP Bus Duct</option>
                    </select>
                </div>
            </div>
        `;
                dynamicContainer.innerHTML = additionalFieldsHTML;
            } else if (selectElement.value === 'RB Mid panel Ledges Assly') {
                const additionalFieldsHTML = `
            <div class="form-row mt-3">
                <div class="form-group col-lg-6">
                    <label class="form-label">LHS Panel RB <span class="required">*</span></label>
                    <select class="form-control required" id="lhs_panel_rb" name="lhs_panel_rb" onchange="updateSheetMetalShortText()">
                        <option value="" disabled selected>-- Select LHS Panel RB --</option>
                        <option value="NA">NA</option>
                        <option value="200RB">200RB</option>
                        <option value="400RB">400RB</option>
                        <option value="600RB">600RB</option>
                        <option value="800RB">800RB</option>
                        <option value="1000RB">1000RB</option>
                    </select>
                </div>
                <div class="form-group col-lg-6">
                    <label class="form-label">RHS Panel RB <span class="required">*</span></label>
                    <select class="form-control required" id="rhs_panel_rb" name="rhs_panel_rb" onchange="updateSheetMetalShortText()">
                        <option value="" disabled selected>-- Select RHS Panel RB --</option>
                        <option value="NA">NA</option>
                        <option value="200RB">200RB</option>
                        <option value="400RB">400RB</option>
                        <option value="600RB">600RB</option>
                        <option value="800RB">800RB</option>
                        <option value="1000RB">1000RB</option>
                    </select>
                </div>
            </div>
        `;
                dynamicContainer.innerHTML = additionalFieldsHTML;
            } else if (selectElement.value === 'CT Supports Assly') {
                const additionalFieldsHTML = `
            <div class="form-row mt-3">
                <div class="form-group col-lg-6">
                    <label class="form-label">CT Type <span class="required">*</span></label>
                    <select class="form-control required" id="ct_type" name="ct_type" onchange="updateSheetMetalShortText()">
                        <option value="" disabled selected>-- Select CT Type --</option>
                        <option value="Window">Window</option>
                        <option value="Wound">Wound</option>
                    </select>
                </div>
            </div>
        `;
                dynamicContainer.innerHTML = additionalFieldsHTML;
            } else if (selectElement.value === 'Cable Supports Assly') {
                const additionalFieldsHTML = `
            <div class="form-row mt-3">
                <div class="form-group col-lg-6">
                    <label class="form-label">Number of Cables <span class="required">*</span></label>
                    <select class="form-control required" id="cable_number" name="cable_number" onchange="updateSheetMetalShortText()">
                        <option value="" disabled selected>-- Select Number of Cables --</option>
                        <option value="1R">1R</option>
                        <option value="2R">2R</option>
                        <option value="3R">3R</option>
                        <option value="4R">4R</option>
                        <option value="5R">5R</option>
                        <option value="6R">6R</option>
                        <option value="7R">7R</option>
                        <option value="8R">8R</option>
                    </select>
                </div>
                <div class="form-group col-lg-6">
                    <label class="form-label">CBCT <span class="required">*</span></label>
                    <select class="form-control required" id="cbct" name="cbct" onchange="updateSheetMetalShortText()">
                        <option value="" disabled selected>-- Select CBCT --</option>
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </select>
                </div>
            </div>
        `;
                dynamicContainer.innerHTML = additionalFieldsHTML;
            }
        }

        // Functions for NXAIR 0
        function handleNXAIR0SheetMetalDescription(selectElement) {
            const dynamicContainer = document.getElementById('dynamic-fields-container');
            if (dynamicContainer) {
                dynamicContainer.innerHTML = '';
            }

            if (selectElement.value === 'Rear_Cover') {
                const additionalFieldsHTML = `
            <div class="form-row mt-3">
            <div class="form-group col-lg-3">
                <label class="form-label">Interlock <span class="required">*</span></label>
                <select class="form-control required" id="interlock" name="interlock" onchange="updateNXAIR0SheetMetalShortText()">
                    <option value="" disabled selected>-- Select Interlock --</option>
                    <option value="NA">NA</option>
                    <option value="EM Interlock">EM Interlock</option>
                    <option value="Castle">Castle</option>
                    <option value="Ronis">Ronis</option>
                </select>
            </div>
            <div class="form-group col-lg-3">
                <label class="form-label">IR Window <span class="required">*</span></label>
                <select class="form-control required" id="ir_window" name="ir_window" onchange="handleIRWindowChange(this); updateNXAIR0SheetMetalShortText()">
                    <option value="" disabled selected>-- Select IR Window --</option>
                    <option value="NA">NA</option>
                    <option value="1 IR">1 IR</option>
                    <option value="2 IR">2 IR</option>
                </select>
            </div>
            <div class="form-group col-lg-3">
                <label class="form-label">Nameplate <span class="required">*</span></label>
                <select class="form-control required" id="nameplate" name="nameplate" onchange="updateNXAIR0SheetMetalShortText()">
                    <option value="" disabled selected>-- Select Nameplate --</option>
                    <option value="NA">NA</option>
                    <option value="Danger">Danger</option>
                    <option value="Board">Board</option>
                    <option value="Feeder">Feeder</option>
                </select>
            </div>
            <div class="form-group col-lg-3">
                <label class="form-label">Viewing Window <span class="required">*</span></label>
                <select class="form-control required" id="viewing_window" name="viewing_window" onchange="updateNXAIR0SheetMetalShortText()" disabled>
                    <option value="" disabled selected>-- Select Viewing Window --</option>
                    <option value="NA">NA</option>
                    <option value="Viewing window">Viewing window</option>
                </select>
            </div>
        </div>
        `;
                dynamicContainer.innerHTML = additionalFieldsHTML;
            }
        }

        function updateNXAIR0SheetMetalShortText() {
        const kaRating = document.getElementById('ka_rating');
        const width = document.getElementById('width');
        const description = document.getElementById('description');
        const thickness = document.getElementById('thickness');
        const material = document.getElementById('material');
        const rearBox = document.getElementById('rear_box');
        const shortTextInput = document.getElementById('shortTextInput');

    // Get KA Rating value
    let kaValue = '';
    if (kaRating && kaRating.value) {
        if (kaRating.value.includes('Upto')) {
            kaValue = '31.5kA';
        } else {
            kaValue = kaRating.value.split(' ')[0] + 'kA';
        }
    }

    // Get other values
    let widthValue = width ? width.value : '';
    let descValue = description ? description.value : '';
    let thicknessValue = thickness ? thickness.value : '';
    let materialValue = material ? material.value : '';
    let rearBoxValue = rearBox ? rearBox.value : '';

    let shortText = '';

    // Only generate if all required fields have values
    if (kaValue && widthValue && descValue && thicknessValue && materialValue && rearBoxValue) {
        
        // Handle Rear_Cover description
        if (descValue === 'Rear_Cover') {
            const interlockRaw = document.getElementById('interlock')?.value || '';
            const irWindowRaw = document.getElementById('ir_window')?.value || '';
            const nameplateRaw = document.getElementById('nameplate')?.value || '';
            const viewingWindowRaw = document.getElementById('viewing_window')?.value || '';

            const interlock = interlockRaw.toLowerCase();
            const irWindow = irWindowRaw.toLowerCase();
            const nameplate = nameplateRaw.toLowerCase();
            const viewingWindow = viewingWindowRaw.toLowerCase();

            let parts = [];

            // Logic: If irWindow is 'na' or empty, skip it
            if (irWindow === 'na' || irWindow === '') {
                if (interlock !== 'na' && interlock !== '') parts.push(interlockRaw);
                if (nameplate !== 'na' && nameplate !== '') parts.push(nameplateRaw);
                if (viewingWindow !== 'na' && viewingWindow !== '') parts.push(viewingWindowRaw);
            } else {
                // Otherwise include all non-na values
                if (interlock !== 'na' && interlock !== '') parts.push(interlockRaw);
                if (irWindow !== 'na' && irWindow !== '') parts.push(irWindowRaw);
                if (nameplate !== 'na' && nameplate !== '') parts.push(nameplateRaw);
                if (viewingWindow !== 'na' && viewingWindow !== '') parts.push(viewingWindowRaw);
            }

            const additionalInfo = parts.length > 0 ? ' ' + parts.join(' ') : '';
            shortText = `${descValue}${additionalInfo} ${widthValue} ${rearBoxValue} ${kaValue}`;
        } 
        else {
            // For all other descriptions
            shortText = `${descValue} ${materialValue} ${thicknessValue} ${widthValue} ${rearBoxValue} ${kaValue}`;
        }

        // Set the short text
        shortTextInput.value = shortText;
    } else {
        shortTextInput.value = '';
    }
}

function handleIRWindowChange(selectElement) {
    const viewingWindow = document.getElementById('viewing_window');
    
    // If IR Window is selected and not 'NA', enable Viewing Window
    if (selectElement.value && selectElement.value !== 'NA') {
        viewingWindow.disabled = false;
    } else {
        viewingWindow.disabled = true;
        viewingWindow.value = '';
    }
}
        // short text for drawing 3 sheet metal 
        function updateSheetMetalShortText() {
            const kaRating = document.getElementById('ka_rating');
            const width = document.getElementById('width');
            const description = document.getElementById('description');
            const rearBox = document.getElementById('rear_box');
            const shortTextInput = document.getElementById('shortTextInput');

            let kaValue = '';
            if (kaRating && kaRating.value) {
                if (kaRating.value.includes('Upto')) {
                    kaValue = '31.5kA';
                } else {
                    kaValue = kaRating.value.split(' ')[0] + 'kA';
                }
            }

            let widthValue = width ? (width.tagName === 'INPUT' ? width.value : width.value) : '';
            let descValue = description ? description.value : '';
            let rearBoxValue = rearBox ? rearBox.value : '';

            // Handle additional fields based on description
            let additionalInfo = '';
            if (descValue === 'End Cover Assly') {
                const location = document.getElementById('end_cover_location')?.value || '';
                const ebbCutout = document.getElementById('ebb_cutout')?.value || '';
                const ebb_size = document.getElementById('ebb_size')?.value || '';
                if (location && ebbCutout && ebb_size) {
                    additionalInfo = ` ${location} ${ebbCutout} ${ebb_size}`;
                }
            } else if (descValue === 'Gland Plate Assly') {
                const cableEntry = document.getElementById('cable_entry')?.value || '';
                const thickness = document.getElementById('gp_thickness')?.value || '';
                const material = document.getElementById('gp_material')?.value || '';
                if (cableEntry && thickness && material) {
                    additionalInfo = ` ${cableEntry} ${thickness} ${material}`;
                }
            } else if (descValue === 'Rear Cover Assly') {
                const interlockRaw = document.getElementById('interlock')?.value || '';
                const irWindowRaw = document.getElementById('ir_window')?.value || '';
                const nameplateRaw = document.getElementById('nameplate')?.value || '';
                const viewingWindowRaw = document.getElementById('viewing_window')?.value || '';

                const interlock = interlockRaw.toLowerCase();
                const irWindow = irWindowRaw.toLowerCase();
                const nameplate = nameplateRaw.toLowerCase();
                const viewingWindow = viewingWindowRaw.toLowerCase();

                let parts = [];

                if (irWindow === 'na' || irWindow === '') {
                    // irWindow is 'na' or empty, print viewingWindow + interlock + nameplate if valid
                    if (interlock !== 'na' && interlock !== '') parts.push(interlockRaw);
                    if (nameplate !== 'na' && nameplate !== '') parts.push(nameplateRaw);
                    if (viewingWindow !== 'na' && viewingWindow !== '') parts.push(viewingWindowRaw);
                } else {
                    // Otherwise print all valid values
                    if (interlock !== 'na' && interlock !== '') parts.push(interlockRaw);
                    if (irWindow !== 'na' && irWindow !== '') parts.push(irWindowRaw);
                    if (nameplate !== 'na' && nameplate !== '') parts.push(nameplateRaw);
                    if (viewingWindow !== 'na' && viewingWindow !== '') parts.push(viewingWindowRaw);
                }

                additionalInfo = parts.length > 0 ? ' ' + parts.join(' ') : '';
            } else if (descValue === 'LHS Pnl End Wall Assly') {
                const lhsPanelRb = document.getElementById('lhs_panel_rb')?.value || '';
                if (lhsPanelRb) {
                    additionalInfo = ` ${lhsPanelRb}`;
                }
            } else if (descValue === 'RHS Pnl End Wall Assly') {
                const rhsPanelRb = document.getElementById('rhs_panel_rb')?.value || '';
                if (rhsPanelRb) {
                    additionalInfo = ` ${rhsPanelRb}`;
                }
            } else if (descValue === 'Rear Box Assly') {
                const rearBoxType = document.getElementById('rear_box_type')?.value || '';
                if (rearBoxType) {
                    additionalInfo = ` ${rearBoxType}`;
                }
            } else if (descValue === 'RB Mid panel Ledges Assly') {
                const lhsPanelRb = document.getElementById('lhs_panel_rb')?.value || '';
                const rhsPanelRb = document.getElementById('rhs_panel_rb')?.value || '';
                if (lhsPanelRb && rhsPanelRb) {
                    additionalInfo = ` ${lhsPanelRb} ${rhsPanelRb}`;
                }
            } else if (descValue === 'CT Supports Assly') {
                const ctType = document.getElementById('ct_type')?.value || '';
                if (ctType) {
                    additionalInfo = ` ${ctType}`;
                }
            } else if (descValue === 'Cable Supports Assly') {
                const cableNumber = document.getElementById('cable_number')?.value || '';
                const cbct = document.getElementById('cbct')?.value || '';
                if (cableNumber && cbct) {
                    additionalInfo = ` ${cableNumber}Cable ${cbct === 'Yes' ? 'CBCT' : ''}`;
                }
            } else if (descValue === 'Rear_Cover') {
                const interlockRaw = document.getElementById('interlock')?.value || '';
                const irWindowRaw = document.getElementById('ir_window')?.value || '';
                const nameplateRaw = document.getElementById('nameplate')?.value || '';
                const viewingWindowRaw = document.getElementById('viewing_window')?.value || '';

                const interlock = interlockRaw.toLowerCase();
                const irWindow = irWindowRaw.toLowerCase();
                const nameplate = nameplateRaw.toLowerCase();
                const viewingWindow = viewingWindowRaw.toLowerCase();

                let parts = [];

                if (irWindow === 'na' || irWindow === '') {
                    // irWindow is 'na' or empty, print viewingWindow + interlock + nameplate if valid
                    if (interlock !== 'na' && interlock !== '') parts.push(interlockRaw);
                    if (nameplate !== 'na' && nameplate !== '') parts.push(nameplateRaw);
                    if (viewingWindow !== 'na' && viewingWindow !== '') parts.push(viewingWindowRaw);
                } else {
                    // Otherwise print all valid values
                    if (interlock !== 'na' && interlock !== '') parts.push(interlockRaw);
                    if (irWindow !== 'na' && irWindow !== '') parts.push(irWindowRaw);
                    if (nameplate !== 'na' && nameplate !== '') parts.push(nameplateRaw);
                    if (viewingWindow !== 'na' && viewingWindow !== '') parts.push(viewingWindowRaw);
                }

                additionalInfo = parts.length > 0 ? ' ' + parts.join(' ') : '';
            }
            // Generate short text if all required fields are filled
            if (kaValue && widthValue && descValue && rearBoxValue) {
                const shortText = `${descValue}${additionalInfo} ${widthValue} ${rearBoxValue} ${kaValue}`;
                shortTextInput.value = shortText;
            } else {
                shortTextInput.value = '';
            }

        }

        // Functions for NXAIR 0
        // function handleNXAIR0SheetMetalDescription(selectElement) {
        //     const dynamicContainer = document.getElementById('dynamic-fields-container');
        //     if (dynamicContainer) {
        //         dynamicContainer.innerHTML = '';
        //     }

        //     if (selectElement.value === 'Rear_Cover') {
        //         const additionalFieldsHTML = `
        //             <div class="form-row mt-3">
        //                 <div class="form-group col-lg-4">
        //                     <label class="form-label">Interlock <span class="required">*</span></label>
        //                     <select class="form-control required" id="interlock" name="interlock" onchange="updateSheetMetalShortText()">
        //                         <option value="" disabled selected>-- Select Interlock --</option>
        //                         <option value="NA">NA</option>
        //                         <option value="EM Interlock">EM Interlock</option>
        //                         <option value="Castle">Castle</option>
        //                         <option value="Ronis">Ronis</option>
        //                     </select>
        //                 </div>
        //                 <div class="form-group col-lg-4">
        //                     <label class="form-label">IR Window <span class="required">*</span></label>
        //                     <select class="form-control required" id="ir_window" name="ir_window" 
        //                             onchange="updateNXAIR0SheetMetalShortText()">
        //                         <option value="" disabled selected>-- Select IR Window --</option>
        //                         <option value="NA">NA</option>
        //                         <option value="1 IR">1 IR</option>
        //                         <option value="2 IR">2 IR</option>
        //                     </select>
        //                 </div>
        //                 <div class="form-group col-lg-4">
        //                     <label class="form-label">Nameplate <span class="required">*</span></label>
        //                     <select class="form-control required" id="nameplate" name="nameplate" 
        //                             onchange="updateNXAIR0SheetMetalShortText()">
        //                         <option value="" disabled selected>-- Select Nameplate --</option>
        //                         <option value="NA">NA</option>
        //                         <option value="Danger">Danger</option>
        //                         <option value="Board">Board</option>
        //                         <option value="Feeder">Feeder</option>
        //                     </select>
        //                 </div>
        //                 <div class="form-group col-lg-4">
        //                     <label class="form-label">Viewing Window <span class="required">*</span></label>
        //                     <select class="form-control required" id="viewing_window" name="viewing_window" onchange="updateNXAIR0SheetMetalShortText()">
        //                         <option value="" disabled selected>-- Select Viewing Window --</option>
        //                         <option value="NA">NA</option>
        //                         <option value="Viewing window">Viewing window</option>
        //                     </select>
        //                 </div>
        //             </div>
        //         `;
        //         dynamicContainer.innerHTML = additionalFieldsHTML;
        //     }
        // }

        // Update handleOtherOption function to include sheet metal case
        function handleOtherOption(selectElement) {
            if (selectElement.value === 'Other') {
                const inputElement = document.createElement('input');
                inputElement.type = 'text';
                inputElement.className = 'form-control required';
                inputElement.name = selectElement.name;
                inputElement.id = selectElement.id;
                inputElement.placeholder = 'Enter ' + selectElement.previousElementSibling.textContent.trim();

                // Add input event listener based on material type
                const materialType = document.getElementById('material_type').value;
                if (materialType === 'busbar') {
                    inputElement.addEventListener('input', updateBusbarShortText);
                } else if (materialType === 'equipment') {
                    inputElement.addEventListener('input', updateEquipmentShortText);
                } else if (materialType === 'sheet_metal') {
                    inputElement.addEventListener('input', updateSheetMetalShortText);
                } else {
                    inputElement.addEventListener('input', updateShortText);
                }

                selectElement.parentNode.replaceChild(inputElement, selectElement);
                inputElement.focus();

                // Add your existing event listeners
                // ... (keep your existing event listeners)
            }
        }


        function updateEquipmentShortText() {
            const description = document.getElementById('description');
            const shortTextInput = document.getElementById('shortTextInput');

            let descValue = description ? (description.tagName === 'INPUT' ? description.value : description.value) : '';

            // Only generate if description has a value
            if (descValue) {
                const shortText = `${descValue}`;
                shortTextInput.value = shortText;
            } else {
                shortTextInput.value = '';
            }
        }

        // Modify handleOtherOption function to include equipment case
        function handleOtherOption(selectElement) {
            if (selectElement.value === 'Other') {
                const inputElement = document.createElement('input');
                inputElement.type = 'text';
                inputElement.className = 'form-control required';
                inputElement.name = selectElement.name;
                inputElement.id = selectElement.id;
                inputElement.placeholder = 'Enter ' + selectElement.previousElementSibling.textContent.trim();

                // Add input event listener based on material type
                const materialType = document.getElementById('material_type').value;
                if (materialType === 'busbar') {
                    inputElement.addEventListener('input', updateBusbarShortText);
                } else if (materialType === 'equipment') {
                    inputElement.addEventListener('input', updateEquipmentShortText);
                } else {
                    inputElement.addEventListener('input', updateShortText);
                }

                selectElement.parentNode.replaceChild(inputElement, selectElement);
                inputElement.focus();

                inputElement.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        revertToSelect(this);
                    }
                });

                inputElement.addEventListener('blur', function() {
                    if (this.value.trim() === '') {
                        revertToSelect(this);
                    }
                    const materialType = document.getElementById('material_type').value;
                    if (materialType === 'busbar') {
                        updateBusbarShortText();
                    } else if (materialType === 'equipment') {
                        updateEquipmentShortText();
                    } else {
                        updateShortText();
                    }
                });
            }
        }


        //Changed by geeta
        function handleOtherOption(selectElement) {
            if (selectElement.value === 'Other') {
                const inputElement = document.createElement('input');
                inputElement.type = 'text';
                inputElement.className = 'form-control required';
                inputElement.name = selectElement.name;
                inputElement.id = selectElement.id;
                inputElement.placeholder = 'Enter ' + selectElement.previousElementSibling.textContent.trim();

                // Add input event listener based on material type
                const materialType = document.getElementById('material_type').value;
                if (materialType === 'busbar') {
                    inputElement.addEventListener('input', updateBusbarShortText);
                } else {
                    inputElement.addEventListener('input', updateShortText);
                }

                selectElement.parentNode.replaceChild(inputElement, selectElement);
                inputElement.focus();

                inputElement.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        revertToSelect(this);
                    }
                });

                inputElement.addEventListener('blur', function() {
                    if (this.value.trim() === '') {
                        revertToSelect(this);
                    }
                    const materialType = document.getElementById('material_type').value;
                    if (materialType === 'busbar') {
                        updateBusbarShortText();
                    } else if (materialType === 'paw') {
                        updateShortText();
                    } else if (materialType === 'equipment') {
                        updateEquipmentShortText();
                    } else if (materialType === 'sheet_metal') {
                        updateSheetMetalShortText();
                    } else if (materialType === 'shrouds') {
                        updateShroudsShortText();
                    } else if (materialType === 'isolation') {
                        updateIsolationShortText();
                    } else if (materialType === 'gfk') {
                        updateGFKShortText();
                    } else {
                        updateShortText();
                    }
                });
            }
        }
        
        function updateShortText() {
            const kaRating = document.getElementById('ka_rating');
            const width = document.getElementById('width');
            const description = document.getElementById('description');
            const shortTextInput = document.getElementById('shortTextInput');

            // Get values and handle both select and input elements
            let kaValue = '';
            if (kaRating && kaRating.value) {
                if (kaRating.value.includes('Upto')) {
                    kaValue = '31.5kA';
                } else {
                    kaValue = kaRating.value.split(' ')[0] + 'kA';
                }
            }

            let widthValue = width ? (width.tagName === 'INPUT' ? width.value : width.value) : '';
            let descValue = description ? (description.tagName === 'INPUT' ? description.value : description.value) : '';

            // Only generate if all required fields have values
            if (kaValue && widthValue && descValue) {
                const shortText = `${descValue} ${widthValue} ${kaValue}`;
                shortTextInput.value = shortText;
            } else {
                shortTextInput.value = '';
            }
        }

        function updateBusbarShortText() {
            const kaRating = document.getElementById('ka_rating');
            const width = document.getElementById('width');
            const description = document.getElementById('description');
            const thickness = document.getElementById('thickness');
            const rearBox = document.getElementById('rear_box');
            const panelWidth = document.getElementById('panel_width');
            const busbarsize = document.getElementById('sizeofbusbar');
            const agplating = document.getElementById('ag_plating');
            const feederbar = document.getElementById('feeder_bar_size');
            const mbbsize = document.getElementById('mbb_size');
            const shortTextInput = document.getElementById('shortTextInput');

            // Get values and handle both select and input elements
            let kaValue = '';
            if (kaRating && kaRating.value) {
                if (kaRating.value.includes('Upto')) {
                    kaValue = '31.5kA';
                } else {
                    kaValue = kaRating.value.split(' ')[0] + 'kA';
                }
            }

            let widthValue = width ? (width.tagName === 'INPUT' ? width.value : width.value) : '';
            let descValue = description ? (description.tagName === 'INPUT' ? description.value : description.value) : '';
            let thicknessValue = thickness ? thickness.value : '';
            let rearBoxValue = rearBox ? rearBox.value : '';
            let panelWidthValue = panelWidth ? panelWidth.value : '';
            let busbarsizeValue = busbarsize ? busbarsize.value : '';
            let agplatingValue = agplating ? agplating.value : '';
            let feederbarValue = feederbar ? feederbar.value : '';
            let mbbsizeValue = mbbsize ? mbbsize.value : '';

            // Only generate if all required fields have values
            if (kaValue && widthValue && descValue && thicknessValue && rearBoxValue) {
                let shortText = '';

                // Include panel width for MBB and EBB Pnl to Pnl Link descriptions
                if ((descValue === 'MBB' ||
                        descValue === 'CT on MBB' ||
                        descValue === 'EBB Pnl to Pnl Link') && panelWidthValue) {
                    shortText = `${descValue} ${panelWidthValue} ${widthValue} ${busbarsizeValue}x${thicknessValue} ${rearBoxValue} ${kaValue} ${agplatingValue === 'Yes' ? 'Ag' : ''}`;
                } else if ((descValue === 'Feeder Bar') && feederbarValue && mbbsizeValue) {
                    shortText = `${descValue} ${feederbarValue} MBB ${mbbsizeValue} ${widthValue} ${busbarsizeValue}x${thicknessValue} ${rearBoxValue} ${kaValue} ${agplatingValue === 'Yes' ? 'Ag' : ''}`;
                } else {
                    shortText = `${descValue} ${widthValue} ${busbarsizeValue}x${thicknessValue} ${rearBoxValue} ${kaValue} ${agplatingValue === 'Yes' ? 'Ag' : ''}`;
                }

                shortTextInput.value = shortText;
            } else {
                shortTextInput.value = '';
            }
        }

        function revertToSelect(inputElement) {
            const originalSelect = document.createElement('select');
            originalSelect.className = 'form-control required';
            originalSelect.name = inputElement.name;
            originalSelect.id = inputElement.id;
            originalSelect.innerHTML = getOriginalOptions(inputElement.id);

            // Add onchange handler based on material type
            const materialType = document.getElementById('material_type').value;
            originalSelect.onchange = function() {
                handleOtherOption(this);
                if (materialType === 'busbar') {
                    updateBusbarShortText();
                } else {
                    updateShortText();
                }
            };

            inputElement.parentNode.replaceChild(originalSelect, inputElement);

            // Update short text based on material type
            if (materialType === 'busbar') {
                updateBusbarShortText();
            } else {
                updateShortText();
            }
        }

        // Changed by Geeta
        function getOriginalOptions(fieldId) {
            const materialType = document.getElementById('material_type').value;

            if (fieldId === 'width') {
                return `
            <option value="" disabled selected>Select Width</option>
            <option value="435W">435W</option>
            <option value="600W">600W</option>
            <option value="800W">800W</option>
            <option value="1000W">1000W</option>
            <option value="Other">Other</option>
        `;
            } else if (fieldId === 'thickness') {
                if (materialType === 'busbar') {
                    return `
            <option value="" disabled selected>-- Select Thickness --</option>
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="15">15</option>
                            <option value="Other">Other</option>
        `;
                } else if (materialType === 'gfk') {
                    return `
                <select class="form-control required" id="thickness" name="thickness" onchange="handleOtherOption(this); updateGFKShortText()">
                        <option value="" disabled selected>-- Select Thickness --</option>
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="Other">Other</option>
                    </select>
        `;
                }
            } else if (fieldId === 'sizeofbusbar') {
                if (materialType === 'busbar') {
                    return `
                            <option value="" disabled selected>Select Size of Busbar</option>
                            <option value="25">25</option>
                            <option value="30">30</option>
                            <option value="40">40</option>
                            <option value="50">50</option>
                            <option value="60">60</option>
                            <option value="80">80</option>
                            <option value="100">100</option>
                            <option value="120">120</option>
                            <option value="160">160</option>
                            <option value="Other">Other</option>
        `;
                } else if (materialType === 'gfk') {
                    return `
                        <option value="" disabled selected>-- Select Busbar Size --</option>
                        <option value="60">60</option>
                            <option value="80">80</option>
                            <option value="100">100</option>
                            <option value="120">120</option>
                            <option value="160">160</option>
                            <option value="Other">Other</option>
        `;
                }
            } else if (fieldId === 'rear_box') {
                if (materialType === 'shrouds') {
                    return `
            <option value="" disabled selected>-- Select Compartment --</option>
                        <option value="CC">CC</option>
                            <option value="BB">BB</option>
                            <option value="Other">Other</option>
        `;
                } else {
                    return `
                <option value="" disabled selected>-- Select Compartment --</option>
                        <option value="CC">CC</option>
                            <option value="MC">MC</option>
                            <option value="BB">BB</option>
                            <option value="200RB">200RB</option>
                            <option value="400RB">400RB</option>
                            <option value="600RB">600RB</option>
                            <option value="800RB">800RB</option>
                            <option value="1000RB">1000RB</option>
                            <option value="Other">Other</option>
        `;
                }
            } else if (fieldId === 'material') {
                if (materialType === 'busbar') {
                    return `
                            <option value="" disabled selected>Select Material</option>
                            <option value="AL">AL</option>
                            <option value="Cu">Cu</option>
                            <option value="Other">Other</option>
        `;
                } else if (materialType === 'shrouds') {
                    return `
                        <option value="" disabled selected>-- Select Material --</option>
                        <option value="Shrouds">Shrouds</option>
                        <option value="Other">Other</option>
        `;
                } else if (materialType === 'isolation') {
                    return `
                        <option value="" disabled selected>-- Select Material --</option>
                        <option value="FRP">FRP</option>
                        <option value="Polycarbonate">Polycarbonate</option>
                        <option value="Other">Other</option>
        `;
                } else if (materialType === 'gfk') {
                    return `
                        <option value="" disabled selected>-- Select Material --</option>
                        <option value="GFK">GFK</option>
                        <option value="Other">Other</option>
        `;
                }
            } else if (fieldId === 'description') {
                if (materialType === 'busbar') {
                    return `
                            <option value="" disabled selected>-- Select Description --</option>
                            <option value="Busing to CT">Busing to CT</option>
                            <option value="Busing to W/o CT">Busing to W/o CT</option>
                            <option value="CT to CT">CT to CT</option>
                            <option value="CT to Cable Conn">CT to Cable Conn</option>
                            <option value="Busbar to Busbar Conn">Busbar to Busbar Conn</option>
                            <option value="Cable Conn">Cable Conn</option>
                            <option value="MBB">MBB</option>
                            <option value="CT on MBB">CT on MBB</option>
                            <option value="Direct to MBB">Direct to MBB</option>
                            <option value="EBB">EBB</option>
                            <option value="EBB Pnl to Pnl Link">EBB Pnl to Pnl Link</option>
                            <option value="EBB End LHS">EBB End LHS</option>
                            <option value="EBB END RHS">EBB END RHS</option>
                            <option value="Feeder Bar">Feeder Bar</option>
                            <option value="Other">Other</option>
            `;
                } else if (materialType === 'paw') {
                    return `
                            <option value="" disabled selected>-- Select Description --</option>
                            <option value="BET">BET</option>
                            <option value="CET">CET</option>
                            <option value="Service truck">Service Truck</option>
                            <option value="Other">Other</option>
            `;
                } else if (materialType === 'equipment') {
                    return `
                        <option value="" disabled selected>-- Select Description --</option>
                        <option value="CVD">CVD</option>
                        <option value="CBCT">CBCT</option>
                        <option value="Window CT">Window CT</option>
                        <option value="Wound CT">Wound CT</option>
                        <option value="Surge Arrester">Surge Arrester</option>
                        <option value="Other">Other</option>
            `;
                } else if (materialType === 'sheet_metal') {
                    return `
                        <option value="" disabled selected>-- Select Description --</option>
                        <option value="End Cover Assly">End Cover Assly</option>
                        <option value="Gland Plate Assly">Gland Plate Assly</option>
                        <option value="BASE FRAME">BASE FRAME</option>
                        <option value="Rear Cover Assly">Rear Cover Assly</option>
                        <option value="LHS Pnl End Wall Assly">LHS Pnl End Wall Assly</option>
                        <option value="RHS Pnl End Wall Assly">RHS Pnl End Wall Assly</option>
                        <option value="Rear Box Assly">Rear Box Assly</option>
                        <option value="RB Mid panel Ledges Assly">RB Mid panel Ledges Assly</option>
                        <option value="Insulator Supports Assly">Insulator Supports Assly</option>
                        <option value="CT Supports Assly">CT Supports Assly</option>
                        <option value="PT Supports Assly">PT Supports Assly</option>
                        <option value="Cable Supports Assly">Cable Supports Assly</option>
                        <option value="SA Supports Assly">SA Supports Assly</option>
                        <option value="CVD Supports Assly">CVD Supports Assly</option>
                        <option value="EM Interlock Assly">EM Interlock Assly</option>
                        <option value="Meshwire Assly">Meshwire Assly</option>
                        <option value="Limit S/W Assly">Limit S/W Assly</option>
                        <option value="Other">Other</option>
            `;
                } else if (materialType === 'others') {
                    return `
                        <option value="" disabled selected>-- Select Description --</option>
                        <option value="CB panel">CB panel</option>
                        <option value=Metering panel">Metering panel</option>
                        <option value="BC">BC</option>
                        <option value="BR">BR</option>
                        <option value="Extended MBB">Extended MBB</option>
                        <option value="CB Panel with DOVT">CB Panel with DOVT</option>
                        <option value="Contactor panel">Contactor panel</option>
                        <option value="Other">Other</option>
            `;
                }
            } else if (materialType === 'shrouds') {
                if (fieldId === 'mbb_run') {
                    return `
            <option value="" disabled selected>-- Select MBB Run --</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="Other">Other</option>
            `;
                } else if (fieldId === 'mbb_size') {
                    return `
            <option value="" disabled selected>-- Select MBB Size --</option>
                        <option value="60x10">60x10</option>
                        <option value="80x10">80x10</option>
                        <option value="100x10">100x10</option>
                        <option value="120x10">120x10</option>
                        <option value="Other">Other</option>
            `;
                } else if (fieldId === 'feeder_run') {
                    return `
            <option value="" disabled selected>-- Select Feeder Run --</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="Other">Other</option>
            `;
                } else if (fieldId === 'feeder_size') {
                    return `
                <option value="" disabled selected>-- Select Feeder Size --</option>
                        <option value="60x10">60x10</option>
                        <option value="80x10">80x10</option>
                        <option value="100x10">100x10</option>
                        <option value="120x10">120x10</option>
                        <option value="Other">Other</option>
            `;
                }
            }
        }

        function handleDescriptionChange(selectElement) {
            // Remove existing panel width field if it exists
            const existingPanelWidth = document.getElementById('panel_width_container');
            if (existingPanelWidth) {
                existingPanelWidth.remove();
            }

            // Remove existing feeder fields if they exist
            const existingFeederFields = document.getElementById('feeder_fields_container');
            if (existingFeederFields) {
                existingFeederFields.remove();
            }

            // If MBB or EBB Pnl to Pnl Link is selected, add panel width field
            if (selectElement.value === 'MBB' ||
                selectElement.value === 'EBB Pnl to Pnl Link') {

                const panelWidthDiv = document.createElement('div');
                panelWidthDiv.id = 'panel_width_container';
                panelWidthDiv.className = 'form-group col-lg-3';
                panelWidthDiv.innerHTML = `
            <label class="form-label">Panel Width <span class="required">*</span></label>
            <select class="form-control required" id="panel_width" name="panel_width" onchange="updateBusbarShortText()">
                <option value="" disabled selected>-- Select Panel Width --</option>
                <option value="435-435">435-435</option>
                        <option value="435-600">435-600</option>
                        <option value="435-800">435-800</option>
                        <option value="435-1000">435-1000</option>
                        <option value="600-600">600-600</option>
                        <option value="600-800">600-800</option>
                        <option value="600-1000">600-1000</option>
                        <option value="800-800">800-800</option>
                        <option value="800-1000">800-1000</option>
                        <option value="1000-1000">1000-1000</option>
            </select>
        `;

                // Insert after the thickness field
                const thicknessField = document.getElementById('thickness').closest('.form-group');
                thicknessField.parentNode.insertBefore(panelWidthDiv, thicknessField.nextSibling);
            }
            // If Feeder Bar is selected, add feeder bar specific fields
            else if (selectElement.value === 'Feeder Bar' ||
                selectElement.value === 'Feeder Temp Sensor') {

                const feederFieldsDiv = document.createElement('div');
                feederFieldsDiv.id = 'feeder_fields_container';
                feederFieldsDiv.className = 'row';
                feederFieldsDiv.style.marginLeft = '20px';
                feederFieldsDiv.innerHTML = `
            <div style="margin-right: 30px">
                <label class="form-label">Feeder Bar Size <span class="required">*</span></label>
                <select class="form-control required" id="feeder_bar_size" name="feeder_bar_size" style="width: 268px" onchange="updateBusbarShortText()">
                    <option value="" disabled selected>-- Select Feeder Bar Size --</option>
                    <option value="1x60x10">1x60x10</option>
                    <option value="2x60x10">2x60x10</option>
                    <option value="1x80x10">1x80x10</option>
                    <option value="2x80x10">2x80x10</option>
                    <option value="1x100x10">1x100x10</option>
                    <option value="2x100x10">2x100x10</option>
                    <option value="3x120x10">3x120x10</option>
                </select>
            </div>
            <div style="margin-right: 30px">
                <label class="form-label">MBB Size <span class="required">*</span></label>
                <select class="form-control required" id="mbb_size" name="mbb_size" style="width: 268px" onchange="updateBusbarShortText()">
                    <option value="" disabled selected>-- Select MBB Size --</option>
                    <option value="1x80x10">1x80x10</option>
                    <option value="1x100x10">1x100x10</option>
                    <option value="1x80x15">1x80x15</option>
                    <option value="2x80x10">2x80x10</option>
                    <option value="2x100x10">2x100x10</option>
                    <option value="3x100x10">3x100x10</option>
                    <option value="3x120x10">3x120x10</option>
                </select>
            </div>
        `;

                // Insert after the rear box field
                const rearBoxField = document.getElementById('rear_box').closest('.form-group');
                rearBoxField.parentNode.insertBefore(feederFieldsDiv, rearBoxField.nextSibling);

                // Hide unnecessary fields
                // document.getElementById('sizeofbusbar').closest('.form-group').style.display = 'none';
                // document.getElementById('Materail').closest('.form-group').style.display = 'none';
                // document.getElementById('ag_plating').closest('.form-group').style.display = 'none';
            }
            // For other descriptions, show all default fields
            else {
                // Show default fields
                const defaultFields = ['sizeofbusbar', 'Material', 'ag_plating'];
                defaultFields.forEach(fieldId => {
                    const field = document.getElementById(fieldId);
                    if (field) {
                        field.closest('.form-group').style.display = '';
                    }
                });
            }

            // Update short text
            updateBusbarShortText();
        }

        function handleIRWindowChange(selectElement) {
            const viewingWindow = document.getElementById('viewing_window');

            if (selectElement.value === 'NA') {
                viewingWindow.disabled = false;
                viewingWindow.value = ''; // Reset the viewing window value
            } else {
                viewingWindow.disabled = true;
                viewingWindow.value = ''; // Reset the viewing window value
            }

            // Update the short text
            updateSheetMetalShortText();

        }


        function saveData() {
            // Create FormData object
            const formData = new FormData();

            // Add action parameter
            formData.append('action', 'matregister');

            // Function to handle null values
            const getFieldValue = (fieldId) => {
                const value = $(fieldId).val()?.trim();
                return value || '';
            };

            // Add all form fields with null handling
            formData.append('product_name', getFieldValue('#product_name'));
            formData.append('drawing_name', getFieldValue('#drawing_name'));
            formData.append('drawing_number', getFieldValue('#drawing_number'));

            formData.append('material_type', getFieldValue('#material_type'));
            formData.append('ka_rating', getFieldValue('#ka_rating'));
            formData.append('width', getFieldValue('#width'));
            formData.append('description', getFieldValue('#description'));
            formData.append('thickness', getFieldValue('#thickness'));
            formData.append('rear_box', getFieldValue('#rear_box'));

            // Sheet Metal fields
            formData.append('end_cover_location', getFieldValue('#end_cover_location'));
            formData.append('ebb_cutout', getFieldValue('#ebb_cutout'));
            formData.append('ebb_size', getFieldValue('#ebb_size'));
            formData.append('cable_entry', getFieldValue('#cable_entry'));
            formData.append('gp_thickness', getFieldValue('#gp_thickness'));
            formData.append('gp_material', getFieldValue('#gp_material'));
            formData.append('interlock', getFieldValue('#interlock'));
            formData.append('ir_window', getFieldValue('#ir_window'));
            formData.append('nameplate', getFieldValue('#nameplate'));
            formData.append('viewing_window', getFieldValue('#viewing_window'));
            formData.append('lhs_panel_rb', getFieldValue('#lhs_panel_rb'));
            formData.append('rhs_panel_rb', getFieldValue('#rhs_panel_rb'));
            formData.append('rear_box_type', getFieldValue('#rear_box_type'));
            formData.append('ct_type', getFieldValue('#ct_type'));
            formData.append('cable_number', getFieldValue('#cable_number'));
            formData.append('cbct', getFieldValue('#cbct'));

            // Busbar fields
            formData.append('panel_width', getFieldValue('#panel_width'));
            formData.append('feeder_bar_size', getFieldValue('#feeder_bar_size'));
            formData.append('mbb_size', getFieldValue('#mbb_size'));
            formData.append('sizeofbusbar', getFieldValue('#sizeofbusbar'));
            formData.append('material', getFieldValue('#material'));
            formData.append('ag_plating', getFieldValue('#ag_plating'));

            //shroud fields 
            formData.append('mbb_run', getFieldValue('#mbb_run'));
            formData.append('feeder_run', getFieldValue('#feeder_run'));
            formData.append('feeder_size', getFieldValue('#feeder_size'));

            formData.append('short_text', getFieldValue('#shortTextInput'));
            formData.append('remarks', getFieldValue('#remarks'));
            formData.append('user', getFieldValue('#user'));

            // Validate required fields
            // Common Fields
            let missingFields = [];

            if (!$('#product_name').val()) {
                missingFields.push({
                    id: '#product_name',
                    name: 'Product Name'
                });
            }
            if (!$('#drawing_name').val()) {
                missingFields.push({
                    id: '#drawing_name',
                    name: 'Drawing Name'
                });
            }
            if (!$('#drawing_number').val()) {
                missingFields.push({
                    id: '#drawing_number',
                    name: 'Drawing Number'
                });
            }

            // Highlight the missing fields and display the error message
            if (missingFields.length > 0) {
                missingFields.forEach(field => {
                    $(`${field.id}`).addClass('is-invalid');
                    $(`${field.id}`).parent().find('.invalid-feedback').remove();
                    $(`${field.id}`).parent().append(`<div class="invalid-feedback">Please enter a ${field.name}.</div>`);
                });
                return;
            }

            // Remove the invalid state and error message from all fields
            $('.form-control').removeClass('is-invalid');
            $('.invalid-feedback').remove();

            // For Busbar
            const description = $('#description').val();
            if ($('#material_type').val() === 'busbar') {
                let missingFields = [];
                // Check the description
                if (description === 'MBB' || description === 'EBB Pnl to Pnl Link') {
                    // Check if the panel width field is empty
                    if (!$('#panel_width').val()) {
                        missingFields.push({
                            id: '#panel_width',
                            name: 'Panel Width'
                        });
                    }
                } else if (description === 'Feeder Bar') {
                    // Check if the feeder bar size and mbb size fields are empty
                    if (!$('#feeder_bar_size').val() || !$('#mbb_size').val()) {
                        if (!$('#feeder_bar_size').val()) {
                            missingFields.push({
                                id: '#feeder_bar_size',
                                name: 'Feeder Bar Size'
                            });
                        }
                        if (!$('#mbb_size').val()) {
                            missingFields.push({
                                id: '#mbb_size',
                                name: 'MBB Size'
                            });
                        }
                    }
                }
                // Check the other fields
                if (!$('#sizeofbusbar').val() || !$('#material').val() || !$('#ag_plating').val() || !$('#rear_box') || !$('#width') || !$('#thickness') || !$('#ka_rating') || !$('#description').val()) {
                    // Add the missing fields to the array
                    if (!$('#sizeofbusbar').val()) {
                        missingFields.push({
                            id: '#sizeofbusbar',
                            name: 'Size of Busbar'
                        });
                    }
                    if (!$('#material').val()) {
                        missingFields.push({
                            id: '#material',
                            name: 'Material'
                        });
                    }
                    if (!$('#ag_plating').val()) {
                        missingFields.push({
                            id: '#ag_plating',
                            name: 'Ag Plating'
                        });
                    }
                    if (!$('#rear_box').val()) {
                        missingFields.push({
                            id: '#rear_box',
                            name: 'Rear Box'
                        });
                    }
                    if (!$('#width').val()) {
                        missingFields.push({
                            id: '#width',
                            name: 'Width'
                        });
                    }
                    if (!$('#thickness').val()) {
                        missingFields.push({
                            id: '#thickness',
                            name: 'Thickness'
                        });
                    }
                    if (!$('#ka_rating').val()) {
                        missingFields.push({
                            id: '#ka_rating',
                            name: 'KA Rating'
                        });
                    }
                    if (!$('#description').val()) {
                        missingFields.push({
                            id: '#description',
                            name: 'Description'
                        });
                    }
                }
                // Highlight the missing fields and display the error message
                if (missingFields.length > 0) {
                    missingFields.forEach(field => {
                        $(`${field.id}`).addClass('is-invalid');
                        $(`${field.id}`).parent().find('.invalid-feedback').remove();
                        $(`${field.id}`).parent().append(`<div class="invalid-feedback">Please select a ${field.name}.</div>`);
                    });
                    return;
                }
                // Remove the invalid state and error message from all fields
                $('.form-control').removeClass('is-invalid');
                $('.invalid-feedback').remove();
            }

            // For Paw
            if ($('#material_type').val() === 'paw') {
                let missingFields = [];

                if (!$('#width').val() || !$('#ka_rating').val() || !$('#description').val()) {
                    if (!$('#width').val()) {
                        missingFields.push({
                            id: '#width',
                            name: 'Width'
                        });
                    }
                    if (!$('#ka_rating').val()) {
                        missingFields.push({
                            id: '#ka_rating',
                            name: 'KA Rating'
                        });
                    }
                    if (!$('#description').val()) {
                        missingFields.push({
                            id: '#description',
                            name: 'Description'
                        });
                    }
                }

                // Highlight the missing fields and display the error message
                if (missingFields.length > 0) {
                    missingFields.forEach(field => {
                        $(`${field.id}`).addClass('is-invalid');
                        $(`${field.id}`).parent().find('.invalid-feedback').remove();
                        $(`${field.id}`).parent().append(`<div class="invalid-feedback">Please select a ${field.name}.</div>`);
                    });
                    return;
                }

                // Remove the invalid state and error message from all fields
                $('.form-control').removeClass('is-invalid');
                $('.invalid-feedback').remove();
            }

            // For Equipment 
            if ($('#material_type').val() === 'equipment') {
                let missingFields = [];

                if (!$('#description').val()) {
                    if (!$('#description').val()) {
                        missingFields.push({
                            id: '#description',
                            name: 'Description'
                        });
                    }
                }

                // Highlight the missing fields and display the error message
                if (missingFields.length > 0) {
                    missingFields.forEach(field => {
                        $(`${field.id}`).addClass('is-invalid');
                        $(`${field.id}`).parent().find('.invalid-feedback').remove();
                        $(`${field.id}`).parent().append(`<div class="invalid-feedback">Please select a ${field.name}.</div>`);
                    });
                    return;
                }

                // Remove the invalid state and error message from all fields
                $('.form-control').removeClass('is-invalid');
                $('.invalid-feedback').remove();
            }

            // For Sheet Metal Assly
            // const description = $('#description').val();
            const materialType = $('#material_type').val();

            // let missingFields = [];

            // if (materialType === 'sheet_metal') {
            //     // Check the description-specific fields
            //     switch (description) {
            //         case 'End Cover Assly':
            //             checkEndCoverFields();
            //             break;
            //         case 'Gland Plate Assly':
            //             checkGlandPlateFields();
            //             break;
            //         case 'Rear Cover Assly':
            //             checkRearCoverFields();
            //             break;
            //         case 'LHS Pnl End Wall Assly':
            //             checkLHSPanelRBField();
            //             break;
            //         case 'RHS Pnl End Wall Assly':
            //             checkRHSPanelRBField();
            //             break;
            //         case 'Rear Box Assly':
            //             checkRearBoxTypeField();
            //             break;
            //         case 'RB Mid panel Ledges Assly':
            //             checkRBMidPanelLedgesFields();
            //             break;
            //         case 'CT Supports Assly':
            //             checkCTTypeField();
            //             break;
            //         case 'Cable Supports Assly':
            //             checkCableSupportFields();
            //             break;
            //     }

            //     // Check the common fields
            //     checkCommonFields();
            // }

            if (materialType === 'sheet_metal') {
                const productName = document.getElementById('product_name').value;
                const drawingName = document.getElementById('drawing_name').value;

                if (productName === 'NXAIR' && drawingName === '3') {
                    // NXAIR 3 description checks
                    switch (description) {
                        case 'End Cover Assly':
                            checkEndCoverFields();
                            break;
                        case 'Gland Plate Assly':
                            checkGlandPlateFields();
                            break;
                        case 'Rear Cover Assly':
                            checkRearCoverFields();
                            break;
                        case 'LHS Pnl End Wall Assly':
                            checkLHSPanelRBField();
                            break;
                        case 'RHS Pnl End Wall Assly':
                            checkRHSPanelRBField();
                            break;
                        case 'Rear Box Assly':
                            checkRearBoxTypeField();
                            break;
                        case 'RB Mid panel Ledges Assly':
                            checkRBMidPanelLedgesFields();
                            break;
                        case 'CT Supports Assly':
                            checkCTTypeField();
                            break;
                        case 'Cable Supports Assly':
                            checkCableSupportFields();
                            break;
                    }
                } else if (productName === 'NXAIR' && drawingName === '0') {
                    // NXAIR 0 description checks
                    switch (description) {

                        case 'Rear_Cover':
                            checkRearCoverFields();
                            break;

                    }
                }

                // Check common fields for both types
                checkCommonFields();
            }

            // Highlight the missing fields and display the error message
            if (missingFields.length > 0) {
                missingFields.forEach(field => {
                    $(`${field.id}`).addClass('is-invalid');
                    $(`${field.id}`).parent().find('.invalid-feedback').remove();
                    $(`${field.id}`).parent().append(`<div class="invalid-feedback">Please select a ${field.name}.</div>`);
                });
                return;
            }

            // Remove the invalid state and error message from all fields
            $('.form-control').removeClass('is-invalid');
            $('.invalid-feedback').remove();

            // Helper functions
            function checkEndCoverFields() {
                if (!$('#end_cover_location').val() || !$('#ebb_cutout').val() || !$('#ebb_size').val()) {
                    if (!$('#end_cover_location').val()) {
                        missingFields.push({
                            id: '#end_cover_location',
                            name: 'End Cover Location'
                        });
                    }
                    if (!$('#ebb_cutout').val()) {
                        missingFields.push({
                            id: '#ebb_cutout',
                            name: 'EBB Cutout'
                        });
                    }
                    if (!$('#ebb_size').val()) {
                        missingFields.push({
                            id: '#ebb_size',
                            name: 'EBB Size'
                        });
                    }
                }
            }

            function checkGlandPlateFields() {
                if (!$('#cable_entry').val() || !$('#gp_thickness').val() || !$('#gp_material').val()) {
                    if (!$('#cable_entry').val()) {
                        missingFields.push({
                            id: '#cable_entry',
                            name: 'Cable Entry'
                        });
                    }
                    if (!$('#gp_thickness').val()) {
                        missingFields.push({
                            id: '#gp_thickness',
                            name: 'GP Thickness'
                        });
                    }
                    if (!$('#gp_material').val()) {
                        missingFields.push({
                            id: '#gp_material',
                            name: 'GP Material'
                        });
                    }
                }
            }

            function checkRearCoverFields() {
                if (!$('#interlock').val() || !$('#ir_window').val() || !$('#nameplate').val() ||
                    ($('#ir_window').val() === 'NA' && !$('#viewing_window').val())) {

                    if (!$('#interlock').val()) {
                        missingFields.push({
                            id: '#interlock',
                            name: 'Interlock'
                        });
                    }

                    if (!$('#ir_window').val()) {
                        missingFields.push({
                            id: '#ir_window',
                            name: 'IR Window'
                        });
                    }

                    if (!$('#name_plate').val()) {
                        missingFields.push({
                            id: '#nameplate',
                            name: 'Name Plate'
                        });
                    }

                    // Only check viewing window if IR Window is 'NA'
                    if ($('#ir_window').val() === 'NA' && !$('#viewing_window').val()) {
                        missingFields.push({
                            id: '#viewing_window',
                            name: 'Viewing Window'
                        });
                    }
                }
            }

            function checkLHSPanelRBField() {
                if (!$('#lhs_panel_rb').val()) {
                    missingFields.push({
                        id: '#lhs_panel_rb',
                        name: 'Panel Rear Box'
                    });
                }
            }

            function checkRHSPanelRBField() {
                if (!$('#rhs_panel_rb').val()) {
                    missingFields.push({
                        id: '#rhs_panel_rb',
                        name: 'Panel Rear Box'
                    });
                }
            }

            function checkRearBoxTypeField() {
                if (!$('#rear_box_type').val()) {
                    missingFields.push({
                        id: '#rear_box_type',
                        name: 'Rear Box Type'
                    });
                }
            }

            function checkRBMidPanelLedgesFields() {
                if (!$('#lhs_panel_rb').val() || !$('#rhs_panel_rb').val()) {
                    if (!$('#lhs_panel_rb').val()) {
                        missingFields.push({
                            id: '#lhs_panel_rb',
                            name: 'Panel Rear Box'
                        });
                    }
                    if (!$('#rhs_panel_rb').val()) {
                        missingFields.push({
                            id: '#rhs_panel_rb',
                            name: 'Panel Rear Box'
                        });
                    }
                }
            }

            function checkCTTypeField() {
                if (!$('#ct_type').val()) {
                    missingFields.push({
                        id: '#ct_type',
                        name: 'CT Type'
                    });
                }
            }

            function checkCableSupportFields() {
                if (!$('#cable_number').val() || !$('#cbct').val()) {
                    if (!$('#cable_number').val()) {
                        missingFields.push({
                            id: '#cable_number',
                            name: 'Number of Cables'
                        });
                    }
                    if (!$('#cbct').val()) {
                        missingFields.push({
                            id: '#cbct',
                            name: 'CBCT'
                        });
                    }
                }
            }

            function checkCommonFields() {
                if (!$('#width').val() || !$('#ka_rating').val() || !$('#rear_box').val() || !$('#description').val()) {
                    if (!$('#width').val()) {
                        missingFields.push({
                            id: '#width',
                            name: 'Width'
                        });
                    }
                    if (!$('#ka_rating').val()) {
                        missingFields.push({
                            id: '#ka_rating',
                            name: 'KA Rating'
                        });
                    }
                    if (!$('#rear_box').val()) {
                        missingFields.push({
                            id: '#rear_box',
                            name: 'Rear Box'
                        });
                    }
                    if (!$('#description').val()) {
                        missingFields.push({
                            id: '#description',
                            name: 'Description'
                        });
                    }
                }
            }


            if ($('#material_type').val() === 'shrouds') {
                let missingFields = [];

                if (!$('#width').val() || !$('#ka_rating').val() || !$('#rear_box').val() || !$('#mbb_run').val() || !$('#mbb_size').val() || !$('#feeder_run').val() || !$('#feeder_size').val() || !$('#material').val()) {
                    if (!$('#width').val()) {
                        missingFields.push({
                            id: '#width',
                            name: 'Width'
                        });
                    }
                    if (!$('#ka_rating').val()) {
                        missingFields.push({
                            id: '#ka_rating',
                            name: 'KA Rating'
                        });
                    }
                    if (!$('#rear_box').val()) {
                        missingFields.push({
                            id: '#rear_box',
                            name: 'Rear Box'
                        });
                    }
                    if (!$('#mbb_run').val()) {
                        missingFields.push({
                            id: '#mbb_run',
                            name: 'Number of MBB Run'
                        });
                    }
                    if (!$('#mbb_size').val()) {
                        missingFields.push({
                            id: '#mbb_size',
                            name: 'MBB Size'
                        });
                    }
                    if (!$('#feeder_run').val()) {
                        missingFields.push({
                            id: '#feeder_run',
                            name: 'Number of Feeder Run'
                        });
                    }
                    if (!$('#feeder_size').val()) {
                        missingFields.push({
                            id: '#feeder_size',
                            name: 'Feeder Size'
                        });
                    }
                    if (!$('#material').val()) {
                        missingFields.push({
                            id: '#material',
                            name: 'Material'
                        });
                    }
                }

                // Highlight the missing fields and display the error message
                if (missingFields.length > 0) {
                    missingFields.forEach(field => {
                        $(`${field.id}`).addClass('is-invalid');
                        $(`${field.id}`).parent().find('.invalid-feedback').remove();
                        $(`${field.id}`).parent().append(`<div class="invalid-feedback">Please select a ${field.name}.</div>`);
                    });
                    return;
                }

                // Remove the invalid state and error message from all fields
                $('.form-control').removeClass('is-invalid');
                $('.invalid-feedback').remove();
            }

            if ($('#material_type').val() === 'isolation') {
                let missingFields = [];

                if (!$('#width').val() || !$('#ka_rating').val() || !$('#rear_box').val() || !$('#material').val()) {
                    if (!$('#width').val()) {
                        missingFields.push({
                            id: '#width',
                            name: 'Width'
                        });
                    }
                    if (!$('#ka_rating').val()) {
                        missingFields.push({
                            id: '#ka_rating',
                            name: 'KA Rating'
                        });
                    }
                    if (!$('#rear_box').val()) {
                        missingFields.push({
                            id: '#rear_box',
                            name: 'Rear Box'
                        });
                    }
                    if (!$('#material').val()) {
                        missingFields.push({
                            id: '#material',
                            name: 'Material'
                        });
                    }
                }

                // Highlight the missing fields and display the error message
                if (missingFields.length > 0) {
                    missingFields.forEach(field => {
                        $(`${field.id}`).addClass('is-invalid');
                        $(`${field.id}`).parent().find('.invalid-feedback').remove();
                        $(`${field.id}`).parent().append(`<div class="invalid-feedback">Please select a ${field.name}.</div>`);
                    });
                    return;
                }

                // Remove the invalid state and error message from all fields
                $('.form-control').removeClass('is-invalid');
                $('.invalid-feedback').remove();
            }

            if ($('#material_type').val() === 'gfk') {
                let missingFields = [];

                if (!$('#ka_rating').val() || !$('#thickness').val() || !$('#width').val() || !$('#rear_box').val() || !$('#sizeofbusbar').val() || !$('#material').val() || !$('#panel').val()) {
                    if (!$('#ka_rating').val()) {
                        missingFields.push({
                            id: '#ka_rating',
                            name: 'KA Rating'
                        });
                    }
                    if (!$('#thickness').val()) {
                        missingFields.push({
                            id: '#thickness',
                            name: 'Thickness'
                        });
                    }
                    // if (!$('#width').val()) {
                    //     missingFields.push({ id: '#width', name: 'Width' });
                    // }
                    if (!$('#rear_box').val()) {
                        missingFields.push({
                            id: '#rear_box',
                            name: 'Rear Box'
                        });
                    }
                    if (!$('#sizeofbusbar').val()) {
                        missingFields.push({
                            id: '#sizeofbusbar',
                            name: 'Size of Busbar'
                        });
                    }
                    if (!$('#material').val()) {
                        missingFields.push({
                            id: '#material',
                            name: 'Material'
                        });
                    }
                    if (!$('#panel_width').val()) {
                        missingFields.push({
                            id: '#panel_width',
                            name: 'Panel Width'
                        });
                    }
                }

                // Highlight the missing fields and display the error message
                if (missingFields.length > 0) {
                    missingFields.forEach(field => {
                        $(`${field.id}`).addClass('is-invalid');
                        $(`${field.id}`).parent().find('.invalid-feedback').remove();
                        $(`${field.id}`).parent().append(`<div class="invalid-feedback">Please select a ${field.name}.</div>`);
                    });
                    return;
                }

                // Remove the invalid state and error message from all fields
                $('.form-control').removeClass('is-invalid');
                $('.invalid-feedback').remove();
            }

            if ($('#material_type').val() === 'others') {
                let missingFields = [];

                if (!$('#ka_rating').val() || !$('#width').val() || !$('#description').val()) {
                    if (!$('#ka_rating').val()) {
                        missingFields.push({
                            id: '#ka_rating',
                            name: 'KA Rating'
                        });
                    }
                    if (!$('#width').val()) {
                        missingFields.push({
                            id: '#width',
                            name: 'Width'
                        });
                    }
                    if (!$('#description').val()) {
                        missingFields.push({
                            id: '#description',
                            name: 'Description'
                        });
                    }
                }

                // Highlight the missing fields and display the error messageeeeeeeeeeeeeee
                if (missingFields.length > 0) {
                    missingFields.forEach(field => {
                        $(`${field.id}`).addClass('is-invalid');
                        $(`${field.id}`).parent().find('.invalid-feedback').remove();
                        $(`${field.id}`).parent().append(`<div class="invalid-feedback">Please select a ${field.name}.</div>`);
                    });
                    return;
                }

                // Remove the invalid state and error message from all fields
                $('.form-control').removeClass('is-invalid');
                $('.invalid-feedback').remove();
            }
            $.ajax({
                url: '/dpm/api/DTOController.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                // success: function(response) {
                //     if (response.message === 'Registration Done Successfully') {
                //         Swal.fire({
                //             icon: 'success',
                //             title: 'Registration Successful',
                //             html: `<p>Drawing Number :<strong> ${response.drawing_number}</strong></p>
                //         <p>Short Text :<strong> ${response.short_text}</strong></p>`,
                //             showConfirmButton: true, // Show OK button
                //             timer: undefined // No auto-close timer
                //         }).then(() => {
                //             // Redirect after user clicks OK
                //             location.href = "mat_reg.php";
                //         });
                //     } else {
                //         Swal.fire({
                //             icon: 'error',
                //             title: 'Error',
                //             text: response.message || 'Error saving data',
                //             showConfirmButton: true
                //         });
                //     }
                // },
                success: function(response) {
                    // ========== NEW CODE START ==========
                    // Build base HTML
                    let alertHTML = `<p>Drawing Number :<strong> ${response.drawing_number}</strong></p>
                                    <p>Short Text :<strong> ${response.short_text}</strong></p>`;
                    
                    // Add remaining numbers alert
                    if (window.remainingNumbers !== undefined) {
                        const remainingNumbers = window.remainingNumbers;
                        
                        if (remainingNumbers <= 50 && remainingNumbers > 0) {
                            alertHTML += `<div style="margin-top: 15px; padding: 10px; background-color: #fff3cd; border: 1px solid #ffc107; border-radius: 4px; color: #856404;">
                                            <strong>⚠️ Warning:</strong> Only <strong>${remainingNumbers}</strong> numbers remaining!
                                        </div>`;
                        } else if (remainingNumbers <= 0) {
                            alertHTML += `<div style="margin-top: 15px; padding: 10px; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; color: #721c24;">
                                            <strong>🚨 Critical:</strong> No numbers remaining! Contact administrator.
                                        </div>`;
                        }
                    }
                    // ========== NEW CODE END ==========
                    
                    if (response.message === 'Registration Done Successfully') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Registration Successful',
                            html: alertHTML,  // ========== CHANGED THIS LINE ==========
                            showConfirmButton: true,
                            timer: undefined
                        }).then(() => {
                            location.href = "mat_reg.php";
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Error saving data',
                            showConfirmButton: true
                        });
                    }
                },
                error: function(xhr, message, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error saving data: ' + error,
                        showConfirmButton: true
                    });
                }
            });


        }

        $('#product_name, #drawing_name, #material_type').change(function() {
            generateDrwNo();
        });

        // Add material change event for busbar
        $('#material').change(function() {
            if ($('#material_type').val() === 'busbar') {
                generateDrwNo();
            }
        });
    </script>


</body>

</html>
