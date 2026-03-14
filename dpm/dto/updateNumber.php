<div class="modal inmodal" id="updateModal11" role="dialog" aria-hidden="true" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                    <span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title">Add Drawing Number</h4>
            </div>
            <div class="modal-body">
                <div class="modal-scrollable-content">
                    <div class="col-12 col-lg-11">
                        <!-- First Row -->
                        <div class="form-group row">
                            <input type="hidden" id="id" name="id">
                            <div class="col-lg-3">
                                <label>Product Name <span class="required">*</span></label>
                                <select class="form-control" id="prdName" name="prdName" required>
                                    <option value="">Select Product Name</option>
                                    <option value="NXAIR">NXAIR</option>
                                    <option value="NXAIRH">NXAIRH</option>
                                </select>
                            </div>

                            <div class="col-lg-3">
                                <label>Drawing Name <span class="required">*</span></label>
                                <select class="form-control" id="drwName" name="drwName" required style="height: 30px;">
                                    <option value="">Select Drawing Name</option>
                                    <option value="0">.0</option>
                                    <option value="3">.3</option>
                                    <option value="9">.9</option>
                                </select>
                            </div>

                            <div class="col-lg-3">
                                <label>Material Name <span class="required">*</span></label>
                                <select class="form-control" id="matName" name="matName" required style="height: 30px;">
                                    <option value="">Select Material Name</option>
                                </select>
                            </div>
                        </div>

                        <!-- Second Row -->
                        <div class="form-group row">
                            <div class="col-lg-3">
                                <label>Main Drawing Number <span class="required">*</span></label>
                                <input type="number" class="form-control" id="mainNumber" name="mainNumber" 
                                       placeholder="00001" min="1" max="99999" required style="height: 30px;">
                            </div>

                            <div class="col-lg-3">
                                <label>Starting Number <span class="required">*</span></label>
                                <input type="number" class="form-control" id="startNumber" name="startNumber" 
                                       placeholder="00001" min="1" max="99999" required style="height: 30px;">
                            </div>

                            <div class="col-lg-3">
                                <label>Ending Number <span class="required">*</span></label>
                                <input type="number" class="form-control" id="endNumber" name="endNumber" 
                                       placeholder="00001" min="1" max="99999" required style="height: 30px;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="submit-button">Submit</button>
            </div>
        </div>
    </div>
</div>

<style>
    .modal-body {
        height: 50vh;
        overflow-y: auto;
    }
    .inmodal .modal-header {
        padding: 2px 20px !important;
        text-align: center !important;
        display: block !important;
    }
    .col-lg-3.col-form-label {
        text-align: left !important;
    }
    .modal-body {
        padding: 20px 30px 5px 80px !important;
    }
    .modal-footer {
        margin-top: 3px !important;
    }
    .is-invalid {
        border-color: red !important;
    }
    .modal-scrollable-content {
        max-height: 50vh;
        overflow-y: auto;
        padding: 20px 30px 5px 80px;
    }
</style>

<script>
        $(document).ready(function () {
            // Your existing JavaScript logic here
            // Make sure to update the selectors to match the new structure

            const materialOptions = {
                // Your existing material options object
            };

            // Update modal trigger
            $('#updateModal1').on('click', function() {
                $('#drawingModal').modal('show');
            });

            // Prevent modal from closing on any click inside the modal, except for the close button
            $('.modal').on('click', function(e) {
                if (!$(e.target).closest('.close').length) {
                    e.stopPropagation();
                }
            });

            // Prevent modal from closing on dropdown selection or input focus
            $('select, input').on('click focus', function(e) {
                e.stopPropagation();
            });

            // Rest of your existing JavaScript...
        });
    </script>
     <script>
    $(document).ready(function () {
        // Define material options based on drawing name
        const materialOptions = {
            "0": [
                { value: "Busbar Aluminium", text: "Busbar Aluminium" },
                { value: "Busbar Copper", text: "Busbar Copper" },
                { value: "Sheet Metal", text: "Sheet Metal" },
                { value: "Gland Plate", text: "Gland Plate" },
                { value: "Insulating Sheet", text: "Insulating Sheet" },
                { value: "GFK Tube", text: "GFK Tube" },
                { value: "Shrouds", text: "Shrouds" },
                { value: "Others(Label, Turn component, hardware etc)", text: "Others(Label, Turn component, hardware etc)" }
            ],
            "3": [
                { value: "PAW components/CET/BET/Accessories", text: "PAW components/CET/BET/Accessories" },
                { value: "Busbar", text: "Busbar" },
                { value: "Equipment", text: "Equipment " },
                { value: "Sheet Metal Assembly", text: "Sheet Metal Assembly" },
                { value: "Shrouds Assembly", text: "Shrouds Assembly" },
                { value: "Isolation Partition Assembly", text: "Isolation Partition Assembly" },
                { value: "GFK Tube", text: "GFK Tube" },
                { value: "Others(Painted Mimic)", text: "Others(Painted Mimic)" }

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