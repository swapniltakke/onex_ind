<!-- updatematnumber.php - Complete Modal with Material Type Logic -->
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
    .field-group {
        margin-bottom: 15px;
    }
    .hidden-field {
        display: none;
    }
</style>

<div class="modal inmodal" id="updateMatNumModal" role="dialog" aria-hidden="true" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                    <span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title">Update Material Register Data</h4>
            </div>
            <div class="modal-body">
                <div class="modal-scrollable-content">
                    <form id="updateMaterialForm">
                        <input type="hidden" id="id" name="id">
                        
                        <div class="row">
                            <!-- First Row -->
                            <div class="form-group col-lg-4">
                                <label class="form-label product-name-label">Product Name <span style="color: red;">*</span></label>
                                <select id="product_name" name="product_name" class="form-control required">
                                    <option value="" disabled selected>-- Select Product Name --</option>
                                    <option value="NXAIR">NXAIR</option>
                                    <option value="NXAIR H">NXAIR H</option>
                                </select>
                            </div>

                            <div class="form-group col-lg-4">
                                <label class="form-label drawing-name-label">Drawing Name <span style="color: red;">*</span></label>
                                <select id="drawing_name" name="drawing_name" class="form-control required">
                                    <option value="" disabled selected>-- Select Drawing Name --</option>
                                    <option value=".0 Drawing">.0 Drawing</option>
                                    <option value=".3 Drawing">.3 Drawing</option>
                                </select>
                            </div>
                            
                            <div class="form-group col-lg-4">
                                <label class="form-label">Drawing Number <span style="color: red;">*</span></label>
                                <input type="text" id="drawing_number" name="drawing_number" class="form-control required" readonly>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Material Type Selection - Controls visibility of other fields -->
                            <div class="form-group col-lg-4">
                                <label class="form-label">Material Type <span style="color: red;">*</span></label>
                                <select id="material_type" name="material_type" class="form-control required">
                                    <option value="" disabled selected>-- Select Material Type --</option>
                                    <option value="sheet_metal">Sheet Metal</option>
                                    <option value="busbar">Busbar</option>
                                    <option value="shrouds">Shrouds</option>
                                    <option value="equipment">Equipment</option>
                                    <option value="gfk">GFK</option>
                                    <option value="isolation">Isolation</option>
                                    <option value="paw">PAW</option>
                                    <option value="others">Others</option>
                                </select>
                            </div>

                            <!-- Material field - visible for specific material types -->
                            <div class="form-group col-lg-4 field-group" data-field-type="busbar gfk isolation shrouds">
                                <label class="form-label">Material</label>
                                <input type="text" id="material" name="material" class="form-control">
                            </div>

                            <!-- KA Rating -->
                            <div class="form-group col-lg-4 field-group" data-field-type="sheet_metal busbar shrouds gfk isolation paw others">
                                <label class="form-label">KA Rating</label>
                                <input type="text" id="ka_rating" name="ka_rating" class="form-control">
                            </div>
                        </div>

                        <div class="row">
                            <!-- Width -->
                            <div class="form-group col-lg-4 field-group" data-field-type="sheet_metal busbar shrouds gfk isolation paw others">
                                <label class="form-label">Width</label>
                                <input type="text" id="width" name="width" class="form-control">
                            </div>

                            <!-- Description -->
                            <div class="form-group col-lg-4 field-group" data-field-type="sheet_metal paw others equipment">
                                <label class="form-label">Description</label>
                                <input type="text" id="description" name="description" class="form-control">
                            </div>

                            <!-- Thickness -->
                            <div class="form-group col-lg-4 field-group" data-field-type="busbar gfk">
                                <label class="form-label">Thickness</label>
                                <input type="text" id="thickness" name="thickness" class="form-control">
                            </div>
                        </div>

                        <div class="row">
                            <!-- Rear Box -->
                            <div class="form-group col-lg-4 field-group" data-field-type="sheet_metal busbar shrouds gfk isolation">
                                <label class="form-label">Rear Box</label>
                                <input type="text" id="rear_box" name="rear_box" class="form-control">
                            </div>

                            <!-- Sheet Metal Specific Fields -->
                            <div class="form-group col-lg-4 field-group" data-field-type="sheet_metal">
                                <label class="form-label">End Cover Location</label>
                                <input type="text" id="end_cover_location" name="end_cover_location" class="form-control">
                            </div>

                            <div class="form-group col-lg-4 field-group" data-field-type="sheet_metal">
                                <label class="form-label">Ebb Cutout</label>
                                <input type="text" id="ebb_cutout" name="ebb_cutout" class="form-control">
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-lg-4 field-group" data-field-type="sheet_metal">
                                <label class="form-label">Ebb Size</label>
                                <input type="text" id="ebb_size" name="ebb_size" class="form-control">
                            </div>

                            <div class="form-group col-lg-4 field-group" data-field-type="sheet_metal">
                                <label class="form-label">Cable Entry</label>
                                <input type="text" id="cable_entry" name="cable_entry" class="form-control">
                            </div>

                            <div class="form-group col-lg-4 field-group" data-field-type="sheet_metal">
                                <label class="form-label">GP Thickness</label>
                                <input type="text" id="gp_thickness" name="gp_thickness" class="form-control">
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-lg-4 field-group" data-field-type="sheet_metal">
                                <label class="form-label">GP Material</label>
                                <input type="text" id="gp_material" name="gp_material" class="form-control">
                            </div>

                            <div class="form-group col-lg-4 field-group" data-field-type="sheet_metal">
                                <label class="form-label">Interlock</label>
                                <input type="text" id="interlock" name="interlock" class="form-control">
                            </div>

                            <div class="form-group col-lg-4 field-group" data-field-type="sheet_metal">
                                <label class="form-label">IR Window</label>
                                <input type="text" id="ir_window" name="ir_window" class="form-control">
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-lg-4 field-group" data-field-type="sheet_metal">
                                <label class="form-label">Nameplate</label>
                                <input type="text" id="nameplate" name="nameplate" class="form-control">
                            </div>

                            <div class="form-group col-lg-4 field-group" data-field-type="sheet_metal">
                                <label class="form-label">Viewing Window</label>
                                <input type="text" id="viewing_window" name="viewing_window" class="form-control">
                            </div>

                            <div class="form-group col-lg-4 field-group" data-field-type="sheet_metal">
                                <label class="form-label">LHS Panel RB</label>
                                <input type="text" id="lhs_panel_rb" name="lhs_panel_rb" class="form-control">
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-lg-4 field-group" data-field-type="sheet_metal">
                                <label class="form-label">RHS Panel RB</label>
                                <input type="text" id="rhs_panel_rb" name="rhs_panel_rb" class="form-control">
                            </div>

                            <div class="form-group col-lg-4 field-group" data-field-type="sheet_metal">
                                <label class="form-label">Rear Box Type</label>
                                <input type="text" id="rear_box_type" name="rear_box_type" class="form-control">
                            </div>

                            <div class="form-group col-lg-4 field-group" data-field-type="sheet_metal">
                                <label class="form-label">CT Type</label>
                                <input type="text" id="ct_type" name="ct_type" class="form-control">
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-lg-4 field-group" data-field-type="sheet_metal">
                                <label class="form-label">No. Cable</label>
                                <input type="text" id="cable_number" name="cable_number" class="form-control">
                            </div>

                            <!-- Busbar Specific Fields -->
                            <div class="form-group col-lg-4 field-group" data-field-type="busbar">
                                <label class="form-label">CBCT</label>
                                <input type="text" id="cbct" name="cbct" class="form-control">
                            </div>

                            <div class="form-group col-lg-4 field-group" data-field-type="busbar gfk">
                                <label class="form-label">Panel Width</label>
                                <input type="text" id="panel_width" name="panel_width" class="form-control">
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-lg-4 field-group" data-field-type="busbar">
                                <label class="form-label">Feeder Bar Size</label>
                                <input type="text" id="feeder_bar_size" name="feeder_bar_size" class="form-control">
                            </div>

                            <div class="form-group col-lg-4 field-group" data-field-type="busbar">
                                <label class="form-label">MBB Size</label>
                                <input type="text" id="mbb_size" name="mbb_size" class="form-control">
                            </div>

                            <div class="form-group col-lg-4 field-group" data-field-type="busbar gfk">
                                <label class="form-label">Busbar Size</label>
                                <input type="text" id="sizeofbusbar" name="sizeofbusbar" class="form-control">
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-lg-4 field-group" data-field-type="busbar">
                                <label class="form-label">AG Plating</label>
                                <input type="text" id="ag_plating" name="ag_plating" class="form-control">
                            </div>

                            <!-- Shrouds Specific Fields -->
                            <div class="form-group col-lg-4 field-group" data-field-type="shrouds">
                                <label class="form-label">MBB Run</label>
                                <input type="text" id="mbb_run" name="mbb_run" class="form-control">
                            </div>

                            <div class="form-group col-lg-4 field-group" data-field-type="shrouds">
                                <label class="form-label">Feeder Run</label>
                                <input type="text" id="feeder_run" name="feeder_run" class="form-control">
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-lg-4 field-group" data-field-type="shrouds">
                                <label class="form-label">Feeder Size</label>
                                <input type="text" id="feeder_size" name="feeder_size" class="form-control">
                            </div>

                            <!-- Common Fields for All Material Types -->
                            <div class="form-group col-lg-4">
                                <label class="form-label">Short Text</label>
                                <input type="text" id="short_text" name="short_text" class="form-control">
                            </div>

                            <div class="form-group col-lg-4">
                                <label class="form-label">Remarks</label>
                                <input type="text" id="remarks" name="remarks" class="form-control">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="updateMaterialBtn">Update</button>
            </div>
        </div>
    </div>
</div>
<script>
$(document).ready(function() {
    // Initialize modal behavior
    $('#updateMatNumModal').modal({
        show: false, // Ensure modal doesn't show by default
        backdrop: 'static', // Prevent closing when clicking outside the modal
        keyboard: false // Disable closing on keyboard events (e.g. Escape key)
    });

    // Allow text selection within the modal
    $('.modal-body').css('user-select', 'text');

    // Prevent modal from closing when clicking anywhere inside the modal
    $('#updateMatNumModal').on('click', function(e) {
        // If the click is on the modal body or table, prevent closing
        if ($(e.target).closest('.modal-body').length) {
            e.stopPropagation();
        }
    });
});
</script>