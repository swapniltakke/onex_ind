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
        height: 90vh; /* Replace <your-desired-height> with the value you want, e.g., 60vh */
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
    .modal-scrollable-content {
  max-height: 90vh;
  overflow-y: auto;
  padding: 20px 30px 5px 80px; /* move padding here */
}
</style>
<div class="modal inmodal" id="updateModal1" role="dialog" aria-hidden="true" tabindex="-1">
    <input value="" type="hidden" name="Id" id="hdnId"/>
    <div class="modal-dialog modal-lg">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                            class="sr-only">Close</span></button>
                <h4 class="modal-title">DTO Update</h4>
            </div>
            <div class="modal-body">
                <div class="modal-scrollable-content">
                <div class="col-12 col-lg-11">
                    <!-- First Row -->
                    <div class="form-group row ">

                        <div class="col-md-2">
                        <div class="form-group">
                        <label>Select Stage Name <span class="required">*</span></label>
                        <select class="form-control" id="stageName" name="stageName" required style="height: 30px;">
                        <option value="">Select a Product</option>
                        <option>Execution Layout</option>
                        <option>Offer Layout</option>
                        </select>
                        <div class="error-message" id="stageNameNameError">Please select a Stage name.</div>
                        </div>
                        </div>


                        <div class="col-lg-3">
                        <label>Select Product Name <span class="required">*</span></label>
                        <select class="form-control" id="productName" name="productName" required>
                        <option value="">Select a Product</option>
                        <option>NXAIR H MP0</option>
                        <option>NXAIR H MP1</option>
                        <option>NXAIR H MP2</option>
                        <option>NXAIR 50kA</option>
                        <option>NXAIR IND</option>
                        <option>NXAIR World</option>
                        </select>
                        </div>
                        
                    
                        <div class="col-lg-3">
                        <label>Enter IAC Ratings <span class="required">*</span></label>
                                            <select class="form-control" id="iacRating" name="iacRating" required style="height: 30px;">
                                            <option value="">Select Rating</option>
                                            <option>0.1</option>
                                            <option>1</option>
                                            <option>NA</option>
                                            </select>   
                        </div>
                    
                    
                        <div class="col-lg-3">
                        <label>Document Number</label>
                                    <input type="text" class="form-control" id="docNo" name="docNo" placeholder="Please Check Document No." required style="height: 30px;" readonly>
                        </div>
                    
                    </div>
                    <!-- 2nd Row -->
                    <div class="form-group row ">
                    
                        <div class="col-lg-3">
                        <label>Short Description </label>
                        <textarea class="form-control" id="shortDescription" name="shortDescription" rows="3" placeholder="Please Read Short description ..." required ></textarea>       
                        </div>
                        <script>
                                            document.addEventListener("DOMContentLoaded", function () {
                                                document.querySelectorAll('select').forEach(function(selectElement) {
                                                    selectElement.addEventListener('change', function() {
                                                        
                                                        var width = document.getElementById('width') ? document.getElementById('width').value : '';
                                                        var rearBoxDepth = document.getElementById('rearBoxDepth') ? document.getElementById('rearBoxDepth').value : '';
                                                        var ratedCurrent = document.getElementById('ratedCurrent') ? document.getElementById('ratedCurrent').value : '';
                                                        var feederMat = document.getElementById('feederMat') ? document.getElementById('feederMat').value : '';
                                                        var woundCTs = document.getElementById('woundCTs') ? document.getElementById('woundCTs').value : '';
                                                        var windowCTs = document.getElementById('windowCTs') ? document.getElementById('windowCTs').value : '';
                                                        var cablesBus = document.getElementById('cablesBus') ? document.getElementById('cablesBus').value : '';
                                                        var cabCore = document.getElementById('cabCore') ? document.getElementById('cabCore').value : '';
                                                        
                                                        console.log('Width:', width);
                                                        console.log('Rear Box Depth:', rearBoxDepth);
                                                        console.log('Rated Current:', ratedCurrent);
                                                        console.log('Feeder Material:', feederMat);
                                                        console.log('Wound CTs:', woundCTs);
                                                        console.log('Window CTs:', windowCTs);
                                                        console.log('Cables/Bus:', cablesBus);
                                                        console.log('Core Cable:', cabCore);

                                                        var widthAbbr = width ? `${width}(W)` : '';
                                                        var rearBoxDepthAbbr = rearBoxDepth ? `${rearBoxDepth}(RB)` : '';
                                                        var feederMatAbbr = feederMat ? `${feederMat}` : '';
                                                        var ratedCurrentAbbr = ratedCurrent ? `${ratedCurrent}(A)` : '';
                                                        var woundCTsAbbr = woundCTs ? `${woundCTs}(Wound CT)` : '';
                                                        var windowCTsAbbr = windowCTs ? `${windowCTs}(Window CT)` : '';
                                                        var cablesBusAbbr = cablesBus ? `${cablesBus}(X)` : '';
                                                        var cabCoreAbbr = cabCore ? `${cabCore}` : '';

                                                        var description = [widthAbbr, rearBoxDepthAbbr, ratedCurrentAbbr, feederMatAbbr, woundCTsAbbr, windowCTsAbbr, cablesBusAbbr, cabCoreAbbr].filter(Boolean).join(', ');

                                                        console.log('Generated Description:', description);

                                                        document.getElementById('shortDescription').value = description;
                                                    });
                                                });
                                            });
                                        </script>
                    
                        <div class="col-lg-3">
                                    <label>Enter Wound CTs (No of Sets) <span class="required">*</span></label>
                                            <select class="form-control" id="woundCTs" name="woundCTs" required style="height: 30px;">
                                            <option value="">Select a Set</option>
                                            <option>1 Set</option>
                                            <option>2 Set</option>
                                            <option>2 Set + 1 Set</option>
                                            <option>3 Set</option>
                                            <option>4 Set</option>
                                            <option>5 Set</option>
                                            <option>Without</option>
                                            <option>NA</option>
                                            </select>  
                        </div>
                    
                        <div class="col-lg-3">
                        <label>Enter Window CTs (No of Sets) <span class="required">*</span></label>
                                            <select class="form-control" id="windowCTs" name="windowCTs" required style="height: 30px;">
                                            <option value="">Select a Set</option>
                                            <option>1 Set</option>
                                            <option>2 Set</option>
                                            <option>NA</option>
                                            </select> 
                        </div>
                    </div>
                    <!-- 3rd Row -->
                    <div class="form-group row ">
                    
                        <div class="col-lg-3">
                        <label>Enter Number Of Cables/Bus Duct <span class="required">*</span></label>
                                            <select class="form-control" id="cablesBus" name="cablesBus" required style="height: 30px;">
                                            <option value="">Select an Item</option>
                                            <option>10R</option>
                                            <option>11R/12R</option>
                                            <option>1R</option>
                                            <option>1R/2R</option>
                                            <option>1R/2R/3R</option>
                                            <option>2R</option>
                                            <option>2R/3R</option>
                                            <option>3R</option>
                                            <option>3R/4R</option>
                                            <option>3R/4R - 1C only</option>
                                            <option>4R/5R</option>
                                            <option>5R</option>
                                            <option>5R/6R</option>
                                            <option>6R/7R</option>
                                            <option>7R/8R</option>
                                            <option>8R/9R</option>
                                            <option>8R/9R/10R</option>
                                            <option>Bus Coupler</option>
                                            <option>Bus Zone</option>
                                            <option>Bus Duct</option>
                                            <option>Up to 5R</option>
                                            <option>NA</option>
                                            <option>All</option>
                                            </select>
                                
                        </div>
                    
                    
                        <div class="col-lg-3">
                        <label>Enter No of Cable Core <span class="required">*</span></label>
                                            <select class="form-control" id="cabCore" name="cabCore" required style="height: 30px;">
                                            <option value="">Select an Option</option>
                                            <option>1C</option>
                                            <option>3C</option>
                                            <option>NA</option>
                                            </select>   
                        </div>
                    
                    
                        <div class="col-lg-3">
                        <label>Enter Cable/Bus Entry <span class="required">*</span></label>
                                            <select class="form-control" id="cabEntry" name="cabEntry" required style="height: 30px;">
                                            <option value="">Select an Option</option>
                                            <option>TOP</option>
                                            <option>Bottom</option>
                                            <option>NA</option>
                                            </select>
                        </div>
                    </div>
                    <!-- 4 Row -->
                    <div class="form-group row ">
                    
                        <div class="col-lg-3">
                        <label>Enter Rated Voltage(kV) <span class="required">*</span></label>
                                            <select class="form-control" id="ratedVol" name="ratedVol" required style="height: 30px;">
                                            <option value="">Select a Voltage</option>
                                            <option>12</option>
                                            <option>17.5</option>
                                            <option>36</option>
                                            <option>NA</option>
                                            </select> 
                                
                        </div>
                    
                    
                        <div class="col-lg-3">
                        <label>Enter Rated Short Circuit (kA) <span class="required">*</span></label>
                                            <select class="form-control" id="ratedCir" name="ratedCir" required style="height: 30px;">
                                            <option value="">Select a Circuit</option>
                                            <option>25</option>
                                            <option>26.3</option>
                                            <option>31.5</option>
                                            <option>40</option>
                                            <option>50</option>
                                            <option>NA</option>
                                            </select>  
                        </div>
                    
                    
                        <div class="col-lg-3">
                        <label>Enter Rated Current (A) (Feeder) <span class="required">*</span></label>
                                            <select class="form-control" id="ratedCurrent" name="ratedCurrent" required style="height: 30px;">
                                            <option value="">Select Current</option>
                                            <option>800</option>
                                            <option>1000</option>
                                            <option>1250</option>
                                            <option>1600</option>
                                            <option>2000</option>
                                            <option>2500</option>
                                            <option>3150</option>
                                            <option>4000</option>
                                            <option>1250/1600</option>
                                            <option>1600/2000</option>
                                            <option>2000A Force Cooling</option>
                                            <option>2500 with Force Cooling</option>
                                            <option>2500A Force Cooling</option>
                                            <option>3150 with Force Cooling</option>
                                            <option>3150A Force Cooling</option>
                                            <option>3150A/ 4000 Force Cooling</option>
                                            <option>4000 Force Cooling</option>
                                            <option>NA</option>
                                            </select> 
                        </div>
                    
                    </div>
                    <!-- 5 Row -->
                    <div class="form-group row ">
                    
                        <div class="col-lg-3">
                        <label>Enter Width <span class="required">*</span></label>
                                            <select class="form-control" id="width" name="width" required style="height: 30px;">
                                            <option value="">Select Width</option>
                                            <option>600</option>
                                            <option>800</option>
                                            <option>1000</option>
                                            <option>1000+1000+800</option>
                                            <option>1000+1000</option>
                                            <option>1000+800</option>
                                            <option>800+800</option>
                                            <option>All</option>
                                            </select>        
                        </div>
                    
                    
                        <div class="col-lg-3">
                        <label>Enter Rear Box Depth <span class="required">*</span></label>
                                            <select class="form-control" id="rearBoxDepth" name="rearBoxDepth" required style="height: 30px;">
                                            <option value="">Select Depth</option>
                                            <option>200</option>
                                            <option>300</option>
                                            <option>400</option>
                                            <option>500</option>
                                            <option>600</option>
                                            <option>700</option>
                                            <option>800</option>
                                            <option>900</option>
                                            <option>1000</option>
                                            <option>1200</option>
                                            <option>1300</option>
                                            <option>0+600+0</option>
                                            <option>1000+0</option>
                                            <option>1000+600</option>
                                            <option>1000+700</option>
                                            <option>600+0</option>
                                            <option>600+600</option>
                                            <option>600+800</option>
                                            <option>700+1000</option>
                                            <option>700+700</option>
                                            <option>800+800</option>
                                            <option>Any depth of rear box</option>
                                            <option>NA</option>
                                            <option>All</option>
                                            </select>
                        </div>
                    
                    
                        <div class="col-lg-3">
                        <label>Enter Feeder Material <span class="required">*</span></label>
                                            <select class="form-control" id="feederMat" name="feederMat" required style="height: 30px;">
                                            <option value="">Select Material</option>
                                            <option>Al</option>
                                            <option>Cu</option>
                                            <option>NA</option>
                                            </select>
                        </div>
                    
                    </div>
                   <!-- 6 Row -->
                   <div class="form-group row ">
                    
                    <div class="col-lg-3">
                    <label>Released By <span class="required">*</span></label>
                                            <select class="form-control" id="realBy" name="realBy" required style="height: 30px;">
                                            <option value="">Select a Name</option>
                                            <option value="Aishwarya">Aishwarya</option>
                                            <option value="Amit">Amit</option>
                                            <option value="Amol">Amol</option>
                                            <option value="Chaitnya">Chaitnya</option>
                                            <option value="Dinesh">Dinesh</option>
                                            <option value="Ganesh">Ganesh</option>
                                            <option value="Gaurav">Gaurav</option>
                                            <option value="Prerna">Prerna</option>
                                            <option value="Rupesh">Rupesh</option>
                                            <option value="Sneha">Sneha</option>
                                            <option value="Swapnil">Swapnil</option>
                                            <option value="Swaroop">Swaroop</option>
                                            <option value="Swardnya">Swardnya</option>
                                            <option value="Vikas">Vikas</option>
                                            </select>
                            
                    </div>
                    
                    <div class="col-lg-3">
                    <label for="orderNo">Enter Sale Order No. <span class="required">*</span></label>
                                                <input type="text" class="form-control" id="orderNo" name="orderNo" placeholder="Please Enter Details ..." required onclick="toggleSalesOrderSuggestions()">
                                                <div id="sales_order_suggestions" class="suggestions-container" style="display: none;"></div> 
                    </div>
                    
                    <div class="col-lg-3">
                    <label for="info">Enter client info <span class="required">*</span></label>
                                                <input type="text" class="form-control" id="info" name="info" placeholder="Please Enter Details ..." required onclick="toggleClientNameSuggestions()">
                                                <div id="client_name_suggestions" class="suggestions-container" style="display: none;"></div>  
                    </div>


                </div> 
                <!-- 7 Row -->
                <div class="form-group row ">
                    
                    <div class="col-lg-3">
                    <label>Enter Drawing No.</label>
                    <input class="form-control" id="DrawNo" name="DrawNo" rows="3" placeholder="Please Read Details ..." required></input>
                            
                    </div>
                
                
                    <div class="col-lg-3">
                    <label>Enter Earthing Switch <span class="required">*</span></label>
                                                        <select class="form-control" id="eartSwitch" name="eartSwitch" required style="height: 30px;">
                                                        <option value="">Select Switch</option>
                                                        <option>Yes</option>
                                                        <option>NA</option>
                                                        </select>  
                    </div>
                
                
                    <div class="col-lg-3">
                    <label>Enter PT - DOVT/FIX <span class="required">*</span></label>
                                                        <select class="form-control" id="doVt" name="doVt" required style="height: 30px;">
                                                        <option value="">Select</option>
                                                        <option>DOVT</option>
                                                        <option>Fix</option>
                                                        <option>NA</option>
                                                        </select>  
                    </div>
                
                </div>    
                <!-- 8 Row -->
                <div class="form-group row ">
                    
                    <div class="col-lg-3">
                    <label>Enter Ref NXTOOL Selection <span class="required">*</span></label>
                    <textarea class="form-control" id="toolSel" name="toolSel" rows="3" placeholder="Please Read Details ..." required></textarea>
                            
                    </div>
                
                
                    <div class="col-lg-3">
                    <label>Enter Possible ADD ON / Remark </label>
                    <textarea class="form-control" id="addOn" name="addOn" rows="3" placeholder="Please Read Details ..."></textarea>
                    </div>
                
                
                    <div class="col-lg-3">
                    <label>Enter Solenoid Interlocking </label>
                    <textarea class="form-control" id="solenoid" name="solenoid" rows="3" placeholder="Please Read Details ..."></textarea>   
                    </div>
                
                </div>    
                 <!-- 9 Row -->
                 <div class="form-group row ">
                    
                    <div class="col-lg-3">
                    <label>Enter Limit Switch </label>
                                            <textarea class="form-control" id="limSwi" name="limSwi" rows="3" placeholder="Please Read Details ..."></textarea>
                            
                    </div>
                
                
                    <div class="col-lg-3">
                    <label>Enter Meshwire Assembly </label>
                    <textarea class="form-control" id="meshAss" name="meshAss" rows="3" placeholder="Please Read Details ..."></textarea> 
                    </div>
                
                
                    <div class="col-lg-3">
                    <label>Enter Indication Lamp on Rear Cover </label>
                    <textarea class="form-control" id="lampRearCover" name="lampRearCover" rows="3" rows="3" placeholder="Please Read Details ..."></textarea>
                    </div>
                
                </div>   
                <!-- 10 Row -->
                <div class="form-group row ">
                    
                    <div class="col-lg-3">
                    <label>Enter Gland plate </label>
                    <textarea class="form-control" id="glandPlate" name="glandPlate" rows="3" placeholder="Please Read Details ..."></textarea>
                            
                    </div>
                
                
                    <div class="col-lg-3">
                    <label>Enter Rear Cover </label>
                    <textarea class="form-control" id="rearCover" name="rearCover" rows="3" placeholder="Please Read Details ..."></textarea>  
                    </div>
                
                    <div class="col-lg-3">
                    <select class="form-control" id="DispUser" name="DispUser" required style="height: 30px;">
                                            <option value="">Select a Name</option>
                                            <option>Internal</option>
                                            <option>Sales</option>
                                            </select> 
                    </div>
                
                </div> 
  </div>
            </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="updateBreaker1('<?= pathinfo(end(explode('/', $_SERVER["REQUEST_URI"])), PATHINFO_FILENAME) ?>')">Save</button>
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
                $('#orderNo').trigger('input');
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
                $('#info').trigger('input');
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
    const modal = document.getElementById('updateModal1');
    const modalContent = modal.querySelector('.modal-content');

    // Get the close button and cancel button
    const closeButton = modal.querySelector('.close');
    const cancelButton = modal.querySelector('.btn-white');

    // Add event listeners to prevent the modal from closing
    closeButton.addEventListener('click', () => {
        $('#updateModal1').modal('hide');
    });

    cancelButton.addEventListener('click', () => {
        $('#updateModal1').modal('hide');
    });

    // Prevent the modal from closing when clicking inside
    modalContent.addEventListener('click', (event) => {
        event.stopPropagation();
    });

    // Close the modal when clicking outside
    document.addEventListener('click', (event) => {
        if (event.target === modal) {
            $('#updateModal1').modal('hide');
        }
    });
</script>