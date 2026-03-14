<!doctype html>
<html lang="en">
<?php
include_once 'core/index.php';
?>
<head>
    <link href="../css/main.css?13" rel="stylesheet"/>

    <!-- DataTables & Semantic UI -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.semanticui.min.css" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.5.0/semantic.min.css" rel="stylesheet"/>
</head>

<style>
    /* General styling for the form and fields */
    .form-group.row {
        margin-bottom: 1rem;
    }

    .form-group .col-lg-4 {
        padding-right: 15px;
    }

    .form-control {
        width: 100%;
        height: calc(1.5em + 0.75rem + 2px);
        padding: 0.75rem 1.25rem;
        font-size: 1rem;
        line-height: 1.5;
        color: #495057;
        background-color: #fff;
        border: 1px solid #ccc;
        border-radius: 0.375rem;
    }

    .form-label {
        font-size: 1rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 0.5rem;
    }

    .btn-primary {
        background-color: #1ab394;
        border-color: #1ab394;
        color: white;
        padding: 0.5rem 1.5rem;
        font-size: 1rem;
        border-radius: 0.375rem;
    }

    .btn-primary:hover {
        background-color: #18a086;
        border-color: #18a086;
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

    .dataTables_wrapper {
        padding: 1rem;
    }
</style>

<?php include_once 'shared/headerStyles.php' ?>
<?php include_once '../assemblynotes/shared/headerScripts.php' ?>

<body>
    <div id="wrapper">
        <?php $activePage = '/dpm/draw_reg.php'; ?>
        <?php require_once $_SERVER["DOCUMENT_ROOT"]."/dpm/shared/dto_sidebar.php"; ?>

        <div id="page-wrapper" class="gray-bg">
            <div class="row border-bottom">
                <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">
                    <div class="navbar-header">
                        <a class="navbar-minimalize minimalize-styl-2 btn btn-primary" href="#"><i class="fa fa-bars"></i></a>
                    </div>
                    <ul class="nav navbar-top-links navbar-right">
                        <li><h2 style="text-align: left; margin-top: 9px;">Add Drawing Number Form</h2></li>
                    </ul>
                </nav>
            </div>

            <!-- Form Section -->
            <div class="card">
                <div class="card-body">
                <!-- <form id="drawingForm" action="save_drawing.php" method="POST"> -->
                    <div class="row justify-content-center m-lg-4" style="margin-top: 1rem !important;">
                        <div class="col-12">
                            <div class="form-row">
                                
                                <div class="form-group col-lg-4">
                                    <label class="form-label">Product Name <span style="color: red;">*</span></label>
                                    <select id="prdName" name="prdName" class="form-control required" style="height: 40px; font-size: 1rem; width: 100%;" required>
                                        <option value="" disabled selected>-- Select Product Name --</option>
                                        <option value="NXAIR">NXAIR</option>
                                        <option value="NXAIRH">NXAIRH</option>
                                    </select>
                                </div>
                                <div class="form-group col-lg-4">
                                    <label class="form-label">Drawing Name <span style="color: red;">*</span></label>
                                    <select id="drwName" name="drwName" class="form-control required" style="height: 40px; font-size: 1rem; width: 100%;" required>
                                        <option value="" disabled selected>-- Select Drawing Name --</option>
                                        <option value="0">.0</option>
                                        <option value="3">.3</option>
                                        <option value="9">.9</option>
                                    </select>
                                </div>
                                <div class="form-group col-lg-4">
                                    <label class="form-label">Material Name <span style="color: red;">*</span></label>
                                    <select id="matName" name="matName" class="form-control required" style="height: 40px; font-size: 1rem; width: 100%;" required>
                                        <option value="" disabled selected>-- Select Material Name --</option>
                                    </select>
                                </div>
                                <div class="form-group col-lg-4">
                                    <label class="form-label">Enter Main Drawing Number <span style="color: red;">*</span></label>
                                    <input type="number" id="mainNumber" name="mainNumber" class="form-control" placeholder="0001" min="1" max="1000" style="height: 40px; font-size: 1rem; width: 100%;" required>
                                </div>
                                <div class="form-group col-lg-4">
                                    <label class="form-label">Set Starting Number <span style="color: red;">*</span></label>
                                    <input type="number" id="startNumber" name="startNumber" class="form-control" placeholder="0001" min="1" max="1000" style="height: 40px; font-size: 1rem; width: 100%;" required>
                                </div>
                                <div class="form-group col-lg-4">
                                    <label class="form-label">Set Ending Number <span style="color: red;">*</span></label>
                                    <input type="number" id="endNumber" name="endNumber" class="form-control" placeholder="0001" min="1" max="1000" style="height: 40px; font-size: 1rem; width: 100%;" required>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="form-row">
                                <div class="form-group col-lg-12 text-center">
                                    <button type="button" id="submit-button" class="btn btn-primary mt-3">Submit</button>

                                </div>
                            </div>
                        </div>
                    </div>
                <!-- </form> -->
                </div>
            </div>

    
            <?php $footer_display = 'Add Drawing Number Form';
            include_once '../assemblynotes/shared/footer.php'; ?>
        </div>
    </div>
    <script>
    $(document).ready(function () {
        // Define material options based on drawing name
        const materialOptions = {
            "0": [
                { value: "busbar_Al", text: "Busbar Aluminium" },
                { value: "busbar_Cu", text: "Busbar Copper" },
                { value: "Sheet Metal", text: "Sheet Metal" },
                { value: "Gland Plate", text: "Gland Plate" },
                { value: "Insulating Sheet", text: "Insulating Sheet" },
                { value: "GFK Tube", text: "GFK Tube" },
                { value: "Shrouds", text: "Shrouds" },
                { value: "Others(Label, Turn component, hardware etc)", text: "Others(Label, Turn component, hardware etc)" }
            ],
            "3": [
                    { value: "paw", text: "PAW components/CET/BET/Accessories" },
                    { value: "busbar_Al", text: "Busbar Aluminium" },
                    { value: "busbar_Cu", text: "Busbar Copper" },
                    { value: "equipment", text: "Equipment " },
                    { value: "sheet_metal", text: "Sheet Metal Assembly" },
                    { value: "shrouds", text: "Shrouds Assembly" },
                    { value: "isolation", text: "Isolation Partition Assembly" },
                    { value: "gfk", text: "GFK Tube" },
                    { value: "others", text: "Others(Painted Mimic)" }

            ],
            "9": [
                { value: "Execution Layout", text: "Execution Layout" },
                { value: "Offer Layout", text: "Offer Layout " }
                
            ]
        };

        // Listen for changes to the drawing name dropdown
        $('#drwName').on('change', function () {
            const selected = $(this).val();
            const matSelect = $('#matName');

            // Clear current options
            matSelect.empty();

            // Add default disabled option
            matSelect.append('<option value="" disabled selected>-- Select Material Name --</option>');

            // Add new options based on selection
            if (materialOptions[selected]) {
                materialOptions[selected].forEach(function (option) {
                    matSelect.append(new Option(option.text, option.value));
                });
            }
        });
    });
</script>

    <!-- JS Scripts -->
    <?php include_once '../assemblynotes/shared/headerSemanticScripts.php' ?>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.semanticui.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.5.0/semantic.min.js"></script>
    <script src="shared/shared.js"></script> 
    <script src="breaker/allBreaker.js?"></script>
<script>
        $(document).ready(function () {
            // Material options based on drawing name
            const materialOptions = {
                "0": [
                    { value: "Busbar Aluminium", text: "Busbar Aluminium" },
                    { value: "Busbar Copper", text: "Busbar Copper" },
                    { value: "sheet_metal", text: "Sheet Metal" },
                    { value: "gland_plate", text: "Gland Plate" },
                    { value: "isolation", text: "Insulating Sheet" },
                    { value: "gfk", text: "GFK Tube" },
                    { value: "others_0", text: "Others(Label, Turn component, hardware etc)" }
                ],
                "3": [
                    { value: "paw", text: "PAW components/CET/BET/Accessories" },
                    { value: "busbar_Al", text: "Busbar Aluminium" },
                    { value: "busbar_Cu", text: "Busbar Copper" },
                    { value: "equipment", text: "Equipment " },
                    { value: "sheet_metal", text: "Sheet Metal Assembly" },
                    { value: "shrouds", text: "Shrouds Assembly" },
                    { value: "isolation", text: "Isolation Partition Assembly" },
                    { value: "gfk", text: "GFK Tube" },
                    { value: "others", text: "Others(Painted Mimic)" }
                ],
                "9": [
                { value: "Execution Layout", text: "Execution Layout" },
                { value: "Offer Layout", text: "Offer Layout " }
                
            ]
            };

            // Update material options on drawing name change
            $('#drwName').on('change', function () {
                const selected = $(this).val();
                const matSelect = $('#matName');

                matSelect.empty();
                matSelect.append('<option value="" disabled selected>-- Select Material Name --</option>');

                if (materialOptions[selected]) {
                    materialOptions[selected].forEach(function (option) {
                        matSelect.append(new Option(option.text, option.value));
                    });
                }
            });

            // Helper: pad number to 5 digits with leading zeros
            function padWithZeros(num) {
                return String(num).padStart(5, '0');
            }

            // Validate fields for empty and number range
            function validateField(field) {
                if (!field.value || field.value.trim() === "") {
                    field.classList.add('is-invalid');
                    return false;
                }
                field.classList.remove('is-invalid');
                return true;
            }

            function validateNumberRange(field) {
                const val = parseInt(field.value, 10);
                if (isNaN(val) || val < 1 || val > 10000 || field.value.length > 5) {
                    field.classList.add('is-invalid');
                    return false;
                }
                field.classList.remove('is-invalid');
                return true;
            }

            $('#submit-button').on('click', function () {
                let isValid = true;

                const prdName = document.getElementById('prdName');
                const drwName = document.getElementById('drwName');
                const matName = document.getElementById('matName');
                const mainNumber = document.getElementById('mainNumber');
                const startNumber = document.getElementById('startNumber');
                const endNumber = document.getElementById('endNumber');

                isValid &= validateField(prdName);
                isValid &= validateField(drwName);
                isValid &= validateField(matName);

                isValid &= validateField(mainNumber) && validateNumberRange(mainNumber);
                isValid &= validateField(startNumber) && validateNumberRange(startNumber);
                isValid &= validateField(endNumber) && validateNumberRange(endNumber);

                if (parseInt(startNumber.value, 10) > parseInt(endNumber.value, 10)) {
                    toastr.warning("Starting number should not be greater than Ending number.");
                    startNumber.classList.add('is-invalid');
                    endNumber.classList.add('is-invalid');
                    isValid = false;
                }

                if (!isValid) {
                    toastr.warning("Please Fill the field in the form.");
                    return;
                }

                var formData = new FormData();
                formData.append('action', 'numregister');
                formData.append('prdName', prdName.value);
                formData.append('drwName', drwName.value);
                formData.append('matName', matName.value);
                formData.append('mainNumber', padWithZeros(mainNumber.value));
                formData.append('startNumber', padWithZeros(startNumber.value));
                formData.append('endNumber', padWithZeros(endNumber.value));

                $(this).prop('disabled', true).text('Processing');

                $.ajax({
                    url: '/dpm/api/DTOController.php',
                    method: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false
                }).done(function(response) {
                    if (response.code === 200) {
                        toastr.success('Registration Successful');
                        setTimeout(() => {
                            location.href = "numreg_details.php";
                        }, 2000);
                    } else {
                        toastr.error('Error: ' + (response.message || 'Unknown error'));
                        $('#submit-button').prop('disabled', false).text('Submit');
                    }
                }).fail(function(err) {
                    toastr.error('Error occurred while registration');
                    $('#submit-button').prop('disabled', false).text('Submit');
                    console.error(err);
                });
            });
        });
    </script>


</body>
</html>
