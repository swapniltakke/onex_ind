<?php
    include_once 'core/index.php';
    SharedManager::checkAuthToModule(17);
    $mode = isset($_GET['mode']) ? $_GET['mode'] : 'database';
    $isConfigurator = ($mode === 'configurator');
    $isDatabase = ($mode === 'database');
?>
<!doctype html>
<html lang="en">
<link href="../css/main.css?13" rel="stylesheet"/>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
<script>
        $(document).ready(function() {
            $('#step1Breadcrumb').click(function() {
                $('#step1').show();
                $('#step2').hide();
                $('#step1Breadcrumb').addClass('active');
                $('#step2Breadcrumb').removeClass('active');
            });
            $('#step2Breadcrumb').click(function() {
                $('#step1').hide();
                $('#step2').show();
                $('#step2Breadcrumb').addClass('active');
                $('#step1Breadcrumb').removeClass('active');
            });
        });
        // Preserve mode parameter when navigating
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const mode = urlParams.get('mode') || 'database';
            
            // Update all sidebar links to include mode parameter
            document.querySelectorAll('.nav a[href*="/dpm/"]').forEach(link => {
                const href = link.getAttribute('href');
                if (!href.includes('?')) {
                    link.setAttribute('href', href + '?mode=' + mode);
                } else if (!href.includes('mode=')) {
                    link.setAttribute('href', href + '&mode=' + mode);
                }
            });
            
            // Store mode in sessionStorage for reference
            sessionStorage.setItem('dtoMode', mode);
        });
</script>

    
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

.table-container {
    margin-top: 20px;
    overflow-x: auto; /* Allows table to be scrollable horizontally */
}

table {
    width: 100%;
    border-collapse: collapse; /* Ensures that border between cells is shared */
}

th, td {
    padding: 8px;
    text-align: left;
    border: 1px solid #ccc; /* Adds border to both th and td elements */
}

th {
    background-color: #f4f4f4; /* Slightly darker shade of grey than td for header */
    color: #333; /* Dark grey text for better readability */
}

tr:nth-child(even) {
    background-color: #f2f2f2; /* Zebra striping for even rows */
}

tr:hover {
    background-color: #ddd; /* Light grey background on hover */
}

.editable-cell {
    border: none;
    background: transparent;
    padding: 8px;
    width: 100%;
}

.delete-button {
    color: #dd4b39; /* Red color typical for delete actions */
    cursor: pointer;
}

.delete-button:hover {
    text-decoration: underline;
}

.breadcrumb {
    list-style-type: none;
    padding: 0;
    margin: 0;
    display: flex;
}

/* Style for individual breadcrumb items */
.breadcrumb li {
    display: inline-block;
    padding: 10px 20px;
    cursor: pointer;
    transition: background-color 0.3s ease;  /* Smooth transition for hover effect */
}

/* Green color for active breadcrumb and bold font */
.breadcrumb li.active {
    color: green;  /* Active breadcrumb color */
    font-weight: bold;  /* Optional: Makes the active breadcrumb bold */
}

/* Add separator bar between breadcrumb items except the last one */
.breadcrumb li:not(:last-child) {
    border-right: 2px solid #ddd;  /* Add a separator line between breadcrumbs */
}

/* Hover effect on breadcrumbs */
.breadcrumb li:hover {
    background-color: #f0f0f0; /* Change background color on hover */
}

/* Optional: Style to highlight the breadcrumb when clicked */
.breadcrumb li:focus {
    outline: none; /* Remove the outline on click */
}

 /* Error messages for invalid fields */
 .error-message {
            color: red;
            font-size: 12px;
            display: none; /* Hide by default */
        }

        /* Red border for invalid fields */
.invalid {
            border: 2px solid red;
        }

        .required {
  color: red;
}
</style>    
<?php include_once 'shared/dto_headerStyles.php' ?>
<?php include_once '../assemblynotes/shared/headerScripts.php' ?>
<body>
    <div id="wrapper">
        <?php $activePage = '/dpm/add_dto_data.php'; ?>
        <?php require_once $_SERVER["DOCUMENT_ROOT"]."/dpm/shared/dto_sidebar.php"; ?>
        <div id="page-wrapper" class="gray-bg">
            <div class="row border-bottom">
                <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">
                    <div class="navbar-header">
                        <a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="#"><i
                                class="fa fa-bars"></i> </a>
                    </div>
                    <ul class="nav navbar-top-links navbar-right">
                        <li>
                            <h2 style="text-align: left; margin-top: 9px;">DTO Database</h2>
                        </li>
                    </ul>
                </nav>
            </div>
            <div class="card">
                <div class="card-body">
                <div style="background-color: #f8f9fa; padding: 10px; border-radius: 5px;">
                <ul class="breadcrumb">
                  <li id="step1Breadcrumb" class="active" onclick="showStep(1)">Step 1</li>
                  <li id="step2Breadcrumb" onclick="showStep(2)">Step 2</li>
                </ul>
            </div>
                
                <div id="step1">
                    <div class="row justify-content-center m-lg-4" style="margin-top: 1rem !important;">
                    
                        <div class="col-12 col-lg-10">
                            <div class="form-group row ">
                                <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Select Product Name <span class="required">*</span></label>
                                            <select class="form-control" id="productName" name="productName" required style="height: 30px;">
                                            <option value="">Select a Product</option>
                                                <optgroup label="NXAIR H Series">
                                                    <option value="NXAIR H MP0">NXAIR H MP0</option>
                                                    <option value="NXAIR H MP1">NXAIR H MP1</option>
                                                    <option value="NXAIR H MP2">NXAIR H MP2</option>
                                                </optgroup>
                                                <optgroup label="NXAIR Series">
                                                    <option value="NXAIR 50kA">NXAIR 50kA</option>
                                                    <option value="NXAIR IND">NXAIR IND</option>
                                                    <option value="NXAIR World">NXAIR World</option>
                                                </optgroup>
                                            </select>
                                            <div class="error-message" id="productNameError">Please select a product name.</div>
                                        </div>
                                </div>

                                <div class="col-md-2">
                                        <div class="form-group">
                                                <label>Select Stage Name <span class="required">*</span></label>
                                                <select class="form-control" id="stageName" name="stageName" required style="height: 30px;">
                                                <option value="">Select a Stage</option>
                                                <option>Execution Stage</option>
                                                <option>Offer Stage</option>
                                                </select>
                                                <div class="error-message" id="stageNameError">Please select a Stage name.</div>
                                        </div>
                                </div>
                            
                             <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Enter IAC Ratings <span class="required">*</span></label>
                                            <select class="form-control" id="iacRating" name="iacRating" required style="height: 30px;">
                                            <option value="">Select Rating</option>
                                            <option>0.1</option>
                                            <option>1</option>
                                            <option>NA</option>
                                            </select>
                                            <div class="error-message" id="iacRatingError">Please select a IAC rating.</div>
                                        </div>
                             </div>
                            
                                        <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Document Number</label>
                                            <input type="text" class="form-control" id="docNo" name="docNo" placeholder="Please Check Document No." required style="height: 30px;" readonly>
                                        </div>
                                        </div>
                                        <!-- <script>
                                             let docNumber = localStorage.getItem('docNumber') ? parseInt(localStorage.getItem('docNumber')) : '0198';
                                             function generateDocNo() {
                                                docNumber++; 
                                                const docNo = 'DTO_S_' + docNumber; 
                                                document.getElementById('docNo').value = docNo;
                                                document.getElementById('docNo2').value = docNo;
                                                localStorage.setItem('docNumber', docNumber);
                                            }
                                            window.onload = function() {
                                                generateDocNo();
                                            };
                                        </script> -->
                                        <!-- <script>
                                            function generateDocNo() {
                                                $.ajax({
                                                    url: 'api/DTOController.php', // Adjust path if needed
                                                    type: 'GET',
                                                    data: {
                                                        "action": "generateDocNo"
                                                    },
                                                    success: function(response) {
                                                        const docNo = typeof response === 'string' ? JSON.parse(response).docNo : response.docNo;
                                                        $('#docNo').val(docNo);
                                                        $('#docNo2').val(docNo);
                                                    },
                                                    error: function(xhr, status, error) {
                                                        console.error('Error generating doc number:', error);
                                                    }
                                                });
                                            }

                                            $(document).ready(function() {
                                                generateDocNo();
                                            });
                                        </script> -->
                                       
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Short Description </label>
                                                <textarea class="form-control" id="shortDescription" name="shortDescription" rows="3" placeholder="Please Read Short description ..." required></textarea>
                                            </div>
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
                                </div>
                                        <div class="form-group row ">
                                        <div class="col-md-3">
                                        <div class="form-group">
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
                                            <div class="error-message" id="woundCTsError">Please select Wound CT.</div>
                                        </div>
                                        </div>
                            
                                        <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Enter Window CTs (No of Sets) <span class="required">*</span></label>
                                            <select class="form-control" id="windowCTs" name="windowCTs" required style="height: 30px;">
                                            <option value="">Select a Set</option>
                                            <option>1 Set</option>
                                            <option>2 Set</option>
                                            <option>NA</option>
                                            </select>
                                            <div class="error-message" id="windowCTsError">Please select Window CTs.</div>
                                        </div>
                                        </div> 
                                        <div class="col-md-3">
                                        <div class="form-group">
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
                                            <div class="error-message" id="cablesBusError">Please select cables Bus.</div>
                                        </div>
                                        </div>
                                        <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Enter No of Cable Core <span class="required">*</span></label>
                                            <select class="form-control" id="cabCore" name="cabCore" required style="height: 30px;">
                                            <option value="">Select an Option</option>
                                            <option>1C</option>
                                            <option>3C</option>
                                            <option>NA</option>
                                            </select>
                                            <div class="error-message" id="cabCoreError">Please select cables core.</div>
                                        </div>
                                        </div>
                                        </div>
                            <div class="form-group row ">
                            <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Enter Cable/Bus Entry <span class="required">*</span></label>
                                            <select class="form-control" id="cabEntry" name="cabEntry" required style="height: 30px;">
                                            <option value="">Select an Option</option>
                                            <option>TOP</option>
                                            <option>Bottom</option>
                                            <option>NA</option>
                                            </select>
                                            <div class="error-message" id="cabEntryError">Please select cables entry.</div>
                                        </div>
                                        </div>

                                        <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Enter Rated Voltage(kV) <span class="required">*</span></label>
                                            <select class="form-control" id="ratedVol" name="ratedVol" required style="height: 30px;">
                                            <option value="">Select a Voltage</option>
                                            <option>12</option>
                                            <option>17.5</option>
                                            <option>36</option>
                                            <option>NA</option>
                                            </select>
                                            <div class="error-message" id="ratedVolError">Please select rated Vol.</div>
                                        </div>
                                        </div>

                                        <div class="col-md-3">
                                        <div class="form-group">
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
                                            <div class="error-message" id="ratedCirError">Please select rated Cir.</div>
                                        </div>
                                        </div>

                                        <div class="col-md-3">
                                        <div class="form-group">
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
                                            <div class="error-message" id="ratedCurrentError">Please select rated Current</div>
                                        </div>
                                        </div>
                            
                            </div>
                            <div class="form-group row ">
                            <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Enter Width <span class="required">*</span></label>
                                            <select class="form-control" id="width" name="width" required style="height: 30px;">
                                            <option value="">Select Width</option>
                                            <option>435</option>
                                            <option>600</option>
                                            <option>800</option>
                                            <option>1000</option>
                                            <option>1000+1000+800</option>
                                            <option>1000+1000</option>
                                            <option>1000+800</option>
                                            <option>800+800</option>
                                            <option>All</option>
                                            </select>
                                            <div class="error-message" id="widthError">Please select width</div>
                                        </div>
                                        </div>

                                        <div class="col-md-3">
                                        <div class="form-group">
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
                                            <div class="error-message" id="rearBoxDepthError">Please select rear box depth</div>
                                        </div>
                                        </div>

                                        <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Enter Feeder Material <span class="required">*</span></label>
                                            <select class="form-control" id="feederMat" name="feederMat" required style="height: 30px;">
                                            <option value="">Select Material</option>
                                            <option>Al</option>
                                            <option>Cu</option>
                                            <option>NA</option>
                                            </select>
                                            <div class="error-message" id="feederMatError">Please select feeder Material</div>
                                        </div>
                                        </div>

                                        <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Released By <span class="required">*</span></label>
                                            <select class="form-control" id="realBy" name="realBy" required style="height: 30px;">
                                            <option value="">Select a Name</option>
                                            <option value="Amit">Amit</option>
                                            <option value="Amol">Amol</option>
                                            <option value="Dinesh">Dinesh</option>
                                            <option value="Dnyaneshwar">Dnyaneshwar</option>
                                            <option value="Ganesh">Ganesh</option>
                                            <option value="Gaurav">Gaurav</option>
                                            <option value="Rupesh">Rupesh</option>
                                            <option value="Sakshi">Sakshi</option>
                                            <option value="Sneha">Sneha</option>
                                            <option value="Swapnil">Swapnil</option>
                                            <option value="Swaroop">Swaroop</option>
                                            <option value="Sarvadnya">Sarvadnya</option>
                                            <option value="Vikas">Vikas</option>
                                            </select>
                                            <div class="error-message" id="realByError">Please select release By</div>
                                        </div>
                                        </div>
                            </div>
                            <div class="form-group row ">
                                        <!-- <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Enter Client Name <span class="required">*</span></label>
                                            <textarea class="form-control" id="info" name="info" rows="3" placeholder="Please Read Details ..." required></textarea>
                                            <div class="error-message" id="infoError">Please select Info</div>
                                        </div>
                                        </div> -->
                                        
                                        <!-- <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Enter Sale Order No. <span class="required">*</span></label>
                                            <textarea class="form-control" id="orderNo" name="orderNo" rows="3" placeholder="Please Read Details ..." required></textarea>
                                            <div class="error-message" id="orderNoError">Please select order No.</div>
                                        </div>
                                        </div> -->
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="orderNo">Enter Work Order No. <span class="required">*</span></label>
                                                <input type="text" class="form-control" id="orderNo" name="orderNo" placeholder="Please Enter Details ..." required onclick="toggleSalesOrderSuggestions()">
                                                <div id="sales_order_suggestions" class="suggestions-container" style="display: none;"></div>
                                                <div class="error-message" id="orderNoError">Please select order No.</div>
                                            </div>
                                       </div>

                                        <div class="col-md-3">
                                             <div class="form-group">
                                                <label for="info">Enter client info <span class="required">*</span></label>
                                                <input type="text" class="form-control" id="info" name="info" placeholder="Please Enter Details ..." required onclick="toggleClientNameSuggestions()">
                                                <div id="client_name_suggestions" class="suggestions-container" style="display: none;"></div>
                                                <div class="error-message" id="infoError">Please select order No.</div>
                                            </div>
                                       </div>

                                        <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Enter Drawing No.</label>
                                            <textarea class="form-control" id="DrawNo" name="DrawNo" rows="3" placeholder="Please Read Details ..." required readonly></textarea>
                                            <div class="error-message" id="DrawNoError">Please select Drawing No.</div>
                                        </div>
                                        </div>
                                        
                                        <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Enter Earthing Switch <span class="required">*</span></label>
                                                        <select class="form-control" id="eartSwitch" name="eartSwitch" required style="height: 30px;">
                                                        <option value="">Select Switch</option>
                                                        <option>Yes</option>
                                                        <option>NA</option>
                                                        </select>
                                                        <div class="error-message" id="eartSwitchError">Please select earthing switch</div>
                                                    </div>
                                        </div>
                                        <!-- <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Enter Earthing Switch</label>
                                            <select class="form-control" id="eartSwitch" name="eartSwitch" required style="height: 30px;">
                                            <option value="">Select Switch</option>
                                            <option>Y</option>
                                            <option>Yes</option>
                                            <option>NA</option>
                                            </select>
                                        </div>
                                        </div> -->

                                        <!-- <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Enter PT - DOVT/FIX</label>
                                            <select class="form-control" id="doVt" name="doVt" required style="height: 30px;">
                                            <option value="">Select</option>
                                            <option>DOVT</option>
                                            <option>Fix</option>
                                            <option>NA</option>
                                            </select>
                                        </div>
                                        </div> -->

                                        <!-- <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Enter Ref NXTOOL Selection</label>
                                            <textarea class="form-control" id="toolSel" name="toolSel" rows="3" placeholder="Please Read Details ..." required></textarea>
                                        </div>
                                        </div>   -->
                            </div>
                                        <div class="form-group row ">
                                                    <!-- <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Enter Client/Sale Order No/Typical/Drwaing No.</label>
                                                        <textarea class="form-control" id="info" name="info" rows="3" placeholder="Please Read Details ..." required></textarea>
                                                    </div>
                                                    </div> -->
                                                    
                                                   

                                                    <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Enter PT - DOVT/FIX <span class="required">*</span></label>
                                                        <select class="form-control" id="doVt" name="doVt" required style="height: 30px;">
                                                        <option value="">Select</option>
                                                        <option>DOVT</option>
                                                        <option>Fix</option>
                                                        <option>NA</option>
                                                        </select>
                                                        <div class="error-message" id="doVtError">Please select option</div>
                                                    </div>
                                                    </div>
                                                
                                                    <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Enter Ref NXTOOL Selection </label>
                                                        <textarea class="form-control" id="toolSel" name="toolSel" rows="3" placeholder="Please Read Details ..." required></textarea>
                                                        <div class="error-message" id="toolSelError">Please select tool selection</div>
                                                    </div>
                                                    </div>  
                                                    <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Upload Layout File </label>
                                                        <input type="file" id="exampleInputFile" name="exampleInputFile" style="height: 30px;">
                                                        <p class="help-block">Upload layout image/screenshot here.</p>
                                                        <div class="error-message" id="exampleInputFileError">Please select Image</div>
                                                    </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label>Upload Support Files</label>
                                                            <input type="file" id="layoutFiles" name="layoutFiles[]" multiple required style="height: 30px;">
                                                            <p class="help-block">Upload multiple layout images/screenshots here.</p>
                                                            <div class="error-message" id="layoutFilesError">Please select at least one image</div>
                                                        </div>
                                                    </div>

                                                    
                                                   
                                                    
                                        </div>
                                        <div class="form-group row ">
                                        <!-- <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Upload Layout File</label>
                                            <input type="file" id="exampleInputFile" name="exampleInputFile" required style="height: 30px;">
                                            <p class="help-block">Upload layout image/screenshot here.</p>
                                        </div>
                                        </div> -->
                                        <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Enter Possible ADD ON / Remark </label>
                                            <textarea class="form-control" id="addOn" name="addOn" rows="3" placeholder="Please Read Details ..."></textarea>
                                        </div>
                                        </div>

                                        <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Enter Solenoid Interlocking </label>
                                            <textarea class="form-control" id="solenoid" name="solenoid" rows="3" placeholder="Please Read Details ..."></textarea>
                                        </div>
                                        </div>

                                        <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Enter Limit Switch </label>
                                            <textarea class="form-control" id="limSwi" name="limSwi" rows="3" placeholder="Please Read Details ..."></textarea>
                                        </div>
                                        </div> 

                                        <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Enter Meshwire Assembly </label>
                                            <textarea class="form-control" id="meshAss" name="meshAss" rows="3" placeholder="Please Read Details ..."></textarea>
                                        </div>
                                        </div>
                                     </div>
                            <div class="form-group row ">
                                        <!-- <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Enter Meshwire Assembly</label>
                                            <textarea class="form-control" id="meshAss" name="meshAss" rows="3" placeholder="Please Read Details ..."></textarea>
                                        </div>
                                        </div> -->

                                        <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Enter Indication Lamp on Rear Cover </label>
                                            <textarea class="form-control" id="lampRearCover" name="lampRearCover" rows="3" rows="3" placeholder="Please Read Details ..."></textarea>
                                        </div>
                                        </div>

                                        <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Enter Gland plate </label>
                                            <textarea class="form-control" id="glandPlate" name="glandPlate" rows="3" placeholder="Please Read Details ..."></textarea>
                                        </div>
                                        </div>

                                        <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Enter Rear Cover </label>
                                            <textarea class="form-control" id="rearCover" name="rearCover" rows="3" placeholder="Please Read Details ..."></textarea>
                                        </div>
                                        </div>

                                        <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Use For <span class="required">*</span></label>
                                            <select class="form-control" id="DispUser" name="DispUser" required style="height: 30px;">
                                            <option value="">Select a Name</option>
                                            <option>Internal</option>
                                            <option>Sales</option>
                                            </select>
                                            <div class="error-message" id="realByError1">Please select Priority</div>
                                        </div>
                                        </div>
                            </div>
                            
                            
                            <div class="form-group row">
                                <div class="col align-self-end">
                                <!-- <button type="button" class="btn btn-lg btn-primary float-right" onclick="$('#step2Breadcrumb').click()">Next Step</button> -->
                                <button type="button" id="nextButtonStep1" class="btn btn-lg btn-primary float-right">Next Step</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="step2" style="display:none;">
                                        <div class="col-md-12">
                                        <!-- <div class="form-group">
                                        <label for="docNo22">Document Number </label>
                                            <input type="text" id="docNo2" name="docNo2" class="form-control" placeholder="Document Number (One per line)" readonly></input>
                                        </div> -->
                                        <div class="form-group" style="text-align: center; width: 100%; display: flex; flex-direction: column; align-items: center;">
                                            <label for="docNo2" style="margin-bottom: 10px; text-align: center;">Document Number</label>
                                            <input type="text" id="docNo2" name="docNo2" class="form-control" placeholder="Document Number (One per line)" readonly style="display: inline-block; margin: 0 auto; width: 100%; text-align: center;font-weight: bold;color: #000000; font-size: 18px;">
                                        </div>

                                        </div>
                                        <div class="row">
                                        <div class="col-md-2">
                                        <div class="form-group">
                                        <label for="matNo">New Material No.<span class="required">*</span></label>
                                        <textarea id="matNo" name="matNo" class="form-control" placeholder="Material No. (One per line)"></textarea>
                                        <div class="error-message" id="matNoError">Please select material number</div>
                                        </div>
                                        </div>
                                        <div class="col-md-2">
                                        <div class="form-group">
                                        <label for="dMat">Deleted Material No.<span class="required">*</span></label>
                                        <textarea id="dMat" name="dMat" class="form-control" placeholder="Deleted Material No. (One per line)"></textarea>
                                        <div class="error-message" id="dMatError">Please select deleted material number</div>
                                        </div>
                                        </div>
                                        <div class="col-md-2">
                                        <div class="form-group">
                                        <label for="quanTt">Quantity<span class="required">*</span></label>
                                        <textarea id="quanTt" name="quanTt" class="form-control" placeholder="Quantity (One per line)"></textarea>
                                        <div class="error-message" id="quanTtError">Please select quantity</div>
                                        </div>
                                        </div>
                                        <div class="col-md-2">
                                        <div class="form-group">
                                        <label for="descRp">Description<span class="required">*</span></label>
                                        <textarea id="descRp" name="descRp" class="form-control" placeholder="Description (One per line)"></textarea>
                                        <div class="error-message" id="descRpError">Please select description</div>
                                        </div>
                                        </div>
                                        <div class="col-md-2">
                                        <div class="form-group">
                                        <label for="kMat">K-Mat<span class="required">*</span></label>
                                        <textarea id="kMat" name="kMat" class="form-control" placeholder="K-Mat (One per line)"></textarea>
                                        <div class="error-message" id="kMatError">Please select K-Mat number</div>
                                        </div>
                                        </div>

            </div>
            <button type="button" class="btn btn-primary" onclick="addRow()">Add Row</button>
            <div class="table-container">
                                            <table id="excelTableModal">
                                                <thead>
                                                    <tr>
                                                        <th>Document Number</th>
                                                        <th>New Material No.</th>
                                                        <th>Deleted Material No.</th>
                                                        <th>Quantity</th>
                                                        <th>Description</th>
                                                        <th>K-Mat</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Data rows will be dynamically inserted here -->
                                                </tbody>
                                            </table>
                                        </div>
                                        

                                        <br><br>
                                        <button type="button" class="btn btn-primary" onclick="$('#step1Breadcrumb').click()">Back</button>
                                        <button id="submit-button" class="btn btn-lg btn-primary float-right">Submit</button>
            
        </div>
        
    </div>
    </div>
      <br><br>
    <?php $footer_display = 'DTO Database';
            include_once '../assemblynotes/shared/footer.php'; ?>
    <?php include_once '../assemblynotes/shared/headerSemanticScripts.php' ?>
    <script src="shared/shared.js"></script>
    <script src="breaker/allBreaker.js?<?php echo rand(); ?>"></script>
    <!-- JavaScript -->
    <script>
        function addRow() {
            // Get the values from the textarea inputs
            var docNo2 = $('#docNo2').val();
            var matNo = $('#matNo').val().split('\n');
            var dMat = $('#dMat').val().split('\n');
            var quanTt = $('#quanTt').val().split('\n');
            var descRp = $('#descRp').val().split('\n');
            var kMat = $('#kMat').val().split('\n');

            // Create a new table row
            for (var i = 0; i < Math.max(matNo.length, dMat.length, quanTt.length, descRp.length, kMat.length); i++) {
                var newRow = $('<tr>');

                // Add the common "Document Number" field
                newRow.append($('<td>').text(docNo2 || ''));

                // Add the other fields
                newRow.append($('<td>').text(matNo[i] || ''));
                newRow.append($('<td>').text(dMat[i] || ''));
                newRow.append($('<td>').text(quanTt[i] || ''));
                newRow.append($('<td>').text(descRp[i] || ''));
                newRow.append($('<td>').text(kMat[i] || ''));
                newRow.append($('<td>').html('<button class="btn btn-danger btn-sm">Delete</button>'));

                // Append the new row to the table body
                $('#excelTableModal tbody').append(newRow);
            }

            // Clear the textarea inputs
            $('#matNo, #dMat, #quanTt, #descRp, #kMat').val('');
        }
        
         // Function to delete a row
         
         function addRow() {
            var docNo2 = $('#docNo2').val();
            var matNo = $('#matNo').val().split('\n');
            var dMat = $('#dMat').val().split('\n');
            var quanTt = $('#quanTt').val().split('\n');
            var descRp = $('#descRp').val().split('\n');
            var kMat = $('#kMat').val().split('\n');

            // Create a new table row
            for (var i = 0; i < Math.max(matNo.length, dMat.length, quanTt.length, descRp.length, kMat.length); i++) {
                var newRow = $('<tr>');

                // Add the common "Document Number" field
                newRow.append($('<td>').text(docNo2 || ''));

                // Add the other fields
                newRow.append($('<td>').text(matNo[i] || ''));
                newRow.append($('<td>').text(dMat[i] || ''));
                newRow.append($('<td>').text(quanTt[i] || ''));
                newRow.append($('<td>').text(descRp[i] || ''));
                newRow.append($('<td>').text(kMat[i] || ''));

                // Add delete button to the row
                newRow.append($('<td>')
                    .html('<button class="btn btn-danger btn-sm delete-btn">Delete</button>'));

                // Append the new row to the table body
                $('#excelTableModal tbody').append(newRow);
            }

            // Clear the textarea inputs after adding the row
            $('#matNo, #dMat, #quanTt, #descRp, #kMat').val('');
        }

        //NEWWWWW CODEEEEEEE
        $(document).on('dblclick', '#excelTableModal td', function() {
        var columnIndex = $(this).index();  // Get the column index
        if (columnIndex >= 0 && columnIndex <= 5) { // Only allow editing of the first 6 columns
            enableEditing(this); // Call the function to enable editing
        }
    });

   
    function enableEditing(cell) {
        if (cell.isContentEditable) return;  // If already in edit mode, don't allow further edits

        var initialContent = cell.innerText;  // Save the initial content of the cell

        // Make the cell editable
        cell.setAttribute('contenteditable', true);
        cell.classList.add('editable-cell');
        cell.focus();

        // Add event listeners to save or cancel the changes
        cell.addEventListener('blur', function() {
            saveCell(cell, initialContent);
        });

        // Allow saving changes when Enter key is pressed or cancel when Escape is pressed
        cell.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                saveCell(cell, initialContent);
            } else if (event.key === 'Escape') {
                cancelCellEdit(cell, initialContent);
            }
        });
    }

    // Function to save changes to the cell
    function saveCell(cell, initialContent) {
        var value = cell.innerText;  // Get the new value
        cell.innerHTML = value;  // Set the new value
        cell.removeAttribute('contenteditable');  // Make it non-editable
        cell.classList.remove('editable-cell');  // Remove the editable class
    }

    // Function to cancel editing and revert to the original content
    function cancelCellEdit(cell, initialContent) {
        cell.innerHTML = initialContent;  // Revert to original content
        cell.removeAttribute('contenteditable');  // Make it non-editable
        cell.classList.remove('editable-cell');  // Remove the editable class
    }

        $(document).on('click', '.delete-btn', function() {
            var row = $(this).closest('tr'); 
            row.remove(); 
        });

        $('#addRowBtn').click(addRow);

        

    
        

        function showStep(stepNumber) {
            if (stepNumber === 1) {
                $('#step1').show();
                $('#step2').hide();
                $('#step1Breadcrumb').addClass('active');
                $('#step2Breadcrumb').removeClass('active');
            } else if (stepNumber === 2) {
                $('#step1').hide();
                $('#step2').show();
                $('#step2Breadcrumb').addClass('active');
                $('#step1Breadcrumb').removeClass('active');
            }
        }

        $('#step1Breadcrumb').click(function() {
            showStep(1);
        });

        $('#step2Breadcrumb').click(function() {
            showStep(2);
        });
        
        let currentFormData = null;
        document.getElementById('submit-button').addEventListener('click', () => {
            var tableData = [];
            $('#excelTableModal tbody tr').each(function() {
                var rowData = {
                    document_number: $(this).find('td:eq(0)').text(),
                    material_no: $(this).find('td:eq(1)').text(),
                    deleted_material_no: $(this).find('td:eq(2)').text(),
                    quantity: $(this).find('td:eq(3)').text(),
                    description: $(this).find('td:eq(4)').text(),
                    k_mat: $(this).find('td:eq(5)').text()
                };
                tableData.push(rowData);
            });

            const urlParams = new URLSearchParams(window.location.search);
            const mode = urlParams.get('mode') || 'database';

            var formData = new FormData();
            formData.append('action', 'registration');
            formData.append('stageName', $('#stageName').val());
            formData.append('productName', $('#productName').val());
            formData.append('iacRating', $('#iacRating').val());
            formData.append('docNo', $('#docNo').val());
            formData.append('shortDescription', $('#shortDescription').val());
            formData.append('woundCTs', $('#woundCTs').val());
            formData.append('windowCTs', $('#windowCTs').val());
            formData.append('cablesBus', $('#cablesBus').val());
            formData.append('cabCore', $('#cabCore').val());
            formData.append('cabEntry', $('#cabEntry').val());
            formData.append('ratedVol', $('#ratedVol').val());
            formData.append('ratedCir', $('#ratedCir').val());
            formData.append('ratedCurrent', $('#ratedCurrent').val());
            formData.append('width', $('#width').val());
            formData.append('rearBoxDepth', $('#rearBoxDepth').val());
            formData.append('feederMat', $('#feederMat').val());
            formData.append('realBy', $('#realBy').val());
            formData.append('info', $('#info').val());
            formData.append('orderNo', $('#orderNo').val());
            formData.append('DrawNo', $('#DrawNo').val());
            formData.append('eartSwitch', $('#eartSwitch').val());
            formData.append('doVt', $('#doVt').val());
            formData.append('toolSel', $('#toolSel').val());
            formData.append('mode', mode);
            
            formData.append('exampleInputFile', $('#exampleInputFile')[0].files[0]);
            var file = $('#exampleInputFile')[0].files[0];
            if (file) {
                formData.append('exampleInputFile', file);
            }

            var fileInputElement = $('#layoutFiles')[0];
            var files = fileInputElement.files;
            for (var i = 0; i < files.length; i++) {
                formData.append('layoutFiles[]', files[i]);
            }
            
            formData.append('addOn', $('#addOn').val());
            formData.append('solenoid', $('#solenoid').val());
            formData.append('limSwi', $('#limSwi').val());
            formData.append('meshAss', $('#meshAss').val());
            formData.append('lampRearCover', $('#lampRearCover').val());
            formData.append('glandPlate', $('#glandPlate').val());
            formData.append('rearCover', $('#rearCover').val());
            formData.append('DispUser', $('#DispUser').val());
            formData.append('tableData', JSON.stringify(tableData));
            
           // Send the data to the server
            (async () => {
                document.getElementById('submit-button').disabled = true;
                document.getElementById('submit-button').innerText = "Processing"
                const userCreation = await $.ajax({
                        url: '/dpm/api/DTOController.php',
                        method: 'POST',
                        data: formData,
                        // dataSrc: function(json) {
                        //     console.log(json);
                        contentType: false,
                        processData: false
                }).catch(e => {
                    console.error(e);
                    toastr.error('','Error occurred while breaker registration');
                    document.getElementById('submit-button').innerText = "Submit"
                    document.getElementById('submit-button').disabled = false;
                });
                if(userCreation.code === 200){
                    toastr.success('Registration Successful', '', {
                        timeOut: 0,
                        extendedTimeOut: 0,
                        closeButton: true,
                        tapToDismiss: false
                    });
                    setTimeout(() => {
                        location.reload(); // Reloads the page
                        }, 2000);
                }
            })();
        });
    </script>
    <script>
    // Function to validate Step 1
    function validateStep1() {
        const stageName = document.getElementById('stageName').value;
        const productName = document.getElementById('productName').value;
        const iacRating = document.getElementById('iacRating').value;
        const woundCTs = document.getElementById('woundCTs').value;
        const windowCTs = document.getElementById('windowCTs').value;  
        const cablesBus = document.getElementById('cablesBus').value; 
        const cabCore = document.getElementById('cabCore').value;  
        const cabEntry = document.getElementById('cabEntry').value; 
        const ratedVol = document.getElementById('ratedVol').value;
        const ratedCir = document.getElementById('ratedCir').value;
        const ratedCurrent = document.getElementById('ratedCurrent').value;
        const width = document.getElementById('width').value;  
        const rearBoxDepth = document.getElementById('rearBoxDepth').value; 
        const feederMat = document.getElementById('feederMat').value;  
        const realBy = document.getElementById('realBy').value;  
        const info = document.getElementById('info').value;
        const orderNo = document.getElementById('orderNo').value;
        const DrawNo = document.getElementById('DrawNo').value;
        const eartSwitch = document.getElementById('eartSwitch').value; 
        const doVt = document.getElementById('doVt').value;  
        const toolSel = document.getElementById('toolSel').value;  
        const exampleInputFile = document.getElementById('exampleInputFile').value;
        const DispUser = document.getElementById('DispUser').value;

        let valid = true;
        
        if (!stageName) {
            document.getElementById('stageName').classList.add('invalid');
            document.getElementById('stageNameError').style.display = 'block';
            valid = false;
        } else {
            document.getElementById('stageName').classList.remove('invalid');
            document.getElementById('stageNameError').style.display = 'none';
        }
        if (!productName) {
            document.getElementById('productName').classList.add('invalid');
            document.getElementById('productNameError').style.display = 'block';
            valid = false;
        } else {
            document.getElementById('productName').classList.remove('invalid');
            document.getElementById('productNameError').style.display = 'none';
        }

        if (!iacRating) {
            document.getElementById('iacRating').classList.add('invalid');
            document.getElementById('iacRatingError').style.display = 'block';
            valid = false;
        } else {
            document.getElementById('iacRating').classList.remove('invalid');
            document.getElementById('iacRatingError').style.display = 'none';
        }

        if (!woundCTs) {
            document.getElementById('woundCTs').classList.add('invalid');
            document.getElementById('woundCTsError').style.display = 'block';
            valid = false;
        } else {
            document.getElementById('woundCTs').classList.remove('invalid');
            document.getElementById('woundCTsError').style.display = 'none';
        }

        if (!windowCTs) {
            document.getElementById('windowCTs').classList.add('invalid');
            document.getElementById('windowCTsError').style.display = 'block';
            valid = false;
        } else {
            document.getElementById('windowCTs').classList.remove('invalid');
            document.getElementById('windowCTsError').style.display = 'none';
        }

        if (!cablesBus) {
            document.getElementById('cablesBus').classList.add('invalid');
            document.getElementById('cablesBusError').style.display = 'block';
            valid = false;
        } else {
            document.getElementById('cablesBus').classList.remove('invalid');
            document.getElementById('cablesBusError').style.display = 'none';
        }

        if (!cabCore) {
            document.getElementById('cabCore').classList.add('invalid');
            document.getElementById('cabCoreError').style.display = 'block';
            valid = false;
        } else {
            document.getElementById('cabCore').classList.remove('invalid');
            document.getElementById('cabCoreError').style.display = 'none';
        }

        if (!cabEntry) {
            document.getElementById('cabEntry').classList.add('invalid');
            document.getElementById('cabEntryError').style.display = 'block';
            valid = false;
        } else {
            document.getElementById('cabEntry').classList.remove('invalid');
            document.getElementById('cabEntryError').style.display = 'none';
        }

        if (!ratedVol) {
            document.getElementById('ratedVol').classList.add('invalid');
            document.getElementById('ratedVolError').style.display = 'block';
            valid = false;
        } else {
            document.getElementById('ratedVol').classList.remove('invalid');
            document.getElementById('ratedVolError').style.display = 'none';
        }

        if (!ratedCir) {
            document.getElementById('ratedCir').classList.add('invalid');
            document.getElementById('ratedCirError').style.display = 'block';
            valid = false;
        } else {
            document.getElementById('ratedCir').classList.remove('invalid');
            document.getElementById('ratedCirError').style.display = 'none';
        }

        if (!ratedCurrent) {
            document.getElementById('ratedCurrent').classList.add('invalid');
            document.getElementById('ratedCurrentError').style.display = 'block';
            valid = false;
        } else {
            document.getElementById('ratedCurrent').classList.remove('invalid');
            document.getElementById('ratedCurrentError').style.display = 'none';
        }

        if (!width) {
            document.getElementById('width').classList.add('invalid');
            document.getElementById('widthError').style.display = 'block';
            valid = false;
        } else {
            document.getElementById('width').classList.remove('invalid');
            document.getElementById('widthError').style.display = 'none';
        }

        if (!rearBoxDepth) {
            document.getElementById('rearBoxDepth').classList.add('invalid');
            document.getElementById('rearBoxDepthError').style.display = 'block';
            valid = false;
        } else {
            document.getElementById('rearBoxDepth').classList.remove('invalid');
            document.getElementById('rearBoxDepthError').style.display = 'none';
        }

        if (!feederMat) {
            document.getElementById('feederMat').classList.add('invalid');
            document.getElementById('feederMatError').style.display = 'block';
            valid = false;
        } else {
            document.getElementById('feederMat').classList.remove('invalid');
            document.getElementById('feederMatError').style.display = 'none';
        }

        if (!realBy) {
            document.getElementById('realBy').classList.add('invalid');
            document.getElementById('realByError').style.display = 'block';
            valid = false;
        } else {
            document.getElementById('realBy').classList.remove('invalid');
            document.getElementById('realByError').style.display = 'none';
        }

        if (!orderNo) {
            document.getElementById('orderNo').classList.add('invalid');
            document.getElementById('orderNoError').style.display = 'block';
            valid = false;
        } else {
            document.getElementById('orderNo').classList.remove('invalid');
            document.getElementById('orderNoError').style.display = 'none';
        }

        if (!info) {
            document.getElementById('info').classList.add('invalid');
            document.getElementById('infoError').style.display = 'block';
            valid = false;
        } else {
            document.getElementById('info').classList.remove('invalid');
            document.getElementById('infoError').style.display = 'none';
        }

        if (!DrawNo) {
            document.getElementById('DrawNo').classList.add('invalid');
            document.getElementById('DrawNoError').style.display = 'block';
            valid = false;
        } else {
            document.getElementById('DrawNo').classList.remove('invalid');
            document.getElementById('DrawNoError').style.display = 'none';
        }

        if (!eartSwitch) {
            document.getElementById('eartSwitch').classList.add('invalid');
            document.getElementById('eartSwitchError').style.display = 'block';
            valid = false;
        } else {
            document.getElementById('eartSwitch').classList.remove('invalid');
            document.getElementById('eartSwitchError').style.display = 'none';
        }

        if (!doVt) {
            document.getElementById('doVt').classList.add('invalid');
            document.getElementById('doVtError').style.display = 'block';
            valid = false;
        } else {
            document.getElementById('doVt').classList.remove('invalid');
            document.getElementById('doVtError').style.display = 'none';
        }

        if (!toolSel) {
            document.getElementById('toolSel').classList.add('invalid');
            document.getElementById('toolSelError').style.display = 'block';
            valid = false;
        } else {
            document.getElementById('toolSel').classList.remove('invalid');
            document.getElementById('toolSelError').style.display = 'none';
        }

        // if (!exampleInputFile) {
        //     document.getElementById('exampleInputFile').classList.add('invalid');
        //     document.getElementById('exampleInputFileError').style.display = 'block';
        //     valid = false;
        // } else {
        //     document.getElementById('exampleInputFile').classList.remove('invalid');
        //     document.getElementById('exampleInputFileError').style.display = 'none';
        // }

        if (!DispUser) {
            document.getElementById('DispUser').classList.add('invalid');
            document.getElementById('realByError1').style.display = 'block';  // <-- is this the right error id?
            valid = false;
        } else {
            document.getElementById('DispUser').classList.remove('invalid');
            document.getElementById('realByError1').style.display = 'none';   // <-- same here
        }

        return valid;
    }

    // Function to validate Step 2
    function validateStep2() {
        const matNo = document.getElementById('matNo').value;
        const dMat = document.getElementById('dMat').value;
        const quanTt = document.getElementById('quanTt').value;
        const descRp = document.getElementById('descRp').value;
        const kMat = document.getElementById('kMat').value;

        let valid = true;

        if (!matNo) {
            document.getElementById('matNo').classList.add('invalid');
            document.getElementById('matNoError').style.display = 'block';
            valid = false;
        } else {
            document.getElementById('matNo').classList.remove('invalid');
            document.getElementById('matNoError').style.display = 'none';
        }

        if (!dMat) {
            document.getElementById('dMat').classList.add('invalid');
            document.getElementById('dMatError').style.display = 'block';
            valid = false;
        } else {
            document.getElementById('dMat').classList.remove('invalid');
            document.getElementById('dMatError').style.display = 'none';
        }

        if (!quanTt) {
            document.getElementById('quanTt').classList.add('invalid');
            document.getElementById('quanTtError').style.display = 'block';
            valid = false;
        } else {
            document.getElementById('quanTt').classList.remove('invalid');
            document.getElementById('quanTtError').style.display = 'none';
        }

        if (!descRp) {
            document.getElementById('descRp').classList.add('invalid');
            document.getElementById('descRpError').style.display = 'block';
            valid = false;
        } else {
            document.getElementById('descRp').classList.remove('invalid');
            document.getElementById('descRpError').style.display = 'none';
        }

        if (!kMat) {
            document.getElementById('kMat').classList.add('invalid');
            document.getElementById('kMatError').style.display = 'block';
            valid = false;
        } else {
            document.getElementById('kMat').classList.remove('invalid');
            document.getElementById('kMatError').style.display = 'none';
        }

        return valid;
    }

    // Function to show Step 1 or Step 2
    function showStep(stepNumber) {
        if (stepNumber === 1) {
            $('#step1').show();
            $('#step2').hide();
            $('#step1Breadcrumb').addClass('active');
            $('#step2Breadcrumb').removeClass('active');
        } else if (stepNumber === 2) {
            $('#step1').hide();
            $('#step2').show();
            $('#step2Breadcrumb').addClass('active');
            $('#step1Breadcrumb').removeClass('active');
        }
    }

    // Step 1 breadcrumb click
    $('#step1Breadcrumb').click(function() {
        showStep(1);
    });

    // Step 2 breadcrumb click with validation
    $('#step2Breadcrumb').click(function() {
        if (validateStep1()) {  // Only proceed if Step 1 validation passes
            showStep(2);  // Show Step 2 if validation is successful
        }
    });

    // Next Step button click in Step 1
    $('#nextButtonStep1').click(function() {
        if (validateStep1()) {
            showStep(2);  // Move to Step 2 if Step 1 validation passes
        }
    });

    // Final Submit button click for both steps validation
    $('#submit-button').click(function(event) {
        // Prevent default form submission
        event.preventDefault();

        // Validate Step 1 and Step 2 before final submission
        const step1Valid = validateStep1();
        const step2Valid = validateStep2();

        // Only show alert once and prevent form submission
        // if (!step1Valid || !step2Valid) {
        //     alert("Please complete all required fields in both Step 1 and Step 2.");
        //     return;  // Exit early if validation fails
        // }

        // If both steps are valid, proceed with the final submission (AJAX, form submit, etc.)
       // alert("Both Step 1 and Step 2 are valid. Proceeding with final submission...");
        
        // Example of form submission (if you have a form):
        // document.getElementById('myForm').submit();
    });

    function getSalesOrderSuggestions(searchTerm) {
        $.ajax({
            url: '/dpm/api/DTOController.php',
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

    function getClientNameSuggestions(searchTerm) {
        $.ajax({
            url: '/dpm/api/DTOController.php',
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
     

    $('#orderNo').on('click', function() {
    var searchTerm = $(this).val();
    getSalesOrderSuggestions(searchTerm);   
    });

    $('#orderNo').on('input', function() {
        var searchTerm = $(this).val();
        if (searchTerm.length >= 0) { // Trigger auto-suggestion only when the input has at least 3 characters
            getSalesOrderSuggestions(searchTerm);
        } else {
            // Clear any existing suggestions and hide the container
            $('#sales_order_suggestions').empty().hide();
        }
        // Update the width of the suggestions container to match the input field
        $('#sales_order_suggestions').width($('#orderNo').outerWidth());
    });

    $(document).on('click', function(event) {
        
        if (!$(event.target).closest('#orderNo, #sales_order_suggestions').length) {
            // Click occurred outside of the #orderNo and #sales_order_suggestions elements
            $('#sales_order_suggestions').hide();
        }
        // if (!$(event.target).closest('#info, #client_name_suggestions').length) {
        //     // Click occurred outside of the #client and #client_name_suggestions elements
        //     $('#client_name_suggestions').hide();
        // }
    });

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

        $('#info').on('click', function() {
        var searchTerm = $(this).val();
        getClientNameSuggestions(searchTerm);
    });
    $('#info').on('input', function() {
        var searchTerm = $(this).val();
        if (searchTerm.length >= 0) { // Trigger auto-suggestion only when the input has at least 3 characters
            getClientNameSuggestions(searchTerm);
        } else {
            // Clear any existing suggestions and hide the container
            $('#client_name_suggestions').empty().hide();
        }
        // Update the width of the suggestions container to match the input field
        $('#client_name_suggestions').width($('#info').outerWidth());
    });

    $(document).on('click', function(event) {
        
        
        if (!$(event.target).closest('#info, #client_name_suggestions').length) {
            // Click occurred outside of the #client and #client_name_suggestions elements
            $('#client_name_suggestions').hide();
        }
    }); 

    function displaySalesOrderSuggestions(suggestions, searchTerm) {
        var suggestionsContainer = $('#sales_order_suggestions');
        suggestionsContainer.empty(); // Clear any existing suggestions

        if (suggestions.length > 0) {
            // Set the width of the suggestions container to match the width of the orderNo input field
            suggestionsContainer.css('width', $('#orderNo').outerWidth());

            suggestions.forEach(function(suggestion) {
                var suggestionText = suggestion.sales_order_no;
                // Compare the suggestion with the searchTerm and apply highlighting if they match exactly
                var suggestionItem = $('<div>')
                    .addClass('suggestion-item')
                    .text(suggestionText)
                    .toggleClass('select-highlight', suggestionText === searchTerm);
                suggestionItem.on('click', function() {
                    $('#orderNo').val(suggestion.sales_order_no);
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
            suggestionsContainer.css('width', $('#info').outerWidth());

            suggestions.forEach(function(suggestion) {
                var suggestionText = suggestion.client;
                // Compare the suggestion with the searchTerm and apply highlighting if they match exactly
                var suggestionItem = $('<div>')
                    .addClass('suggestion-item')
                    .text(suggestionText)
                    .toggleClass('select-highlight', suggestionText === searchTerm);
                suggestionItem.on('click', function() {
                    $('#info').val(suggestion.client);
                    suggestionsContainer.empty().hide(); // Clear the suggestions and hide the container after selecting an item
                });
                suggestionsContainer.append(suggestionItem);
            });
            suggestionsContainer.show();
        } else {
            suggestionsContainer.hide();
        }
    }
    
</script>
<script>
$(document).ready(function() {
    
    // Generate Document Number
    function generateDocNo() {
        const stageName = $('#stageName').val();
        
        if (!stageName) {
            return;
        }

        $.ajax({
            url: 'api/DTOController.php',
            type: 'GET',
            data: {
                "action": "generateDocNo",
                "stageName": stageName
            },
            dataType: 'json',
            success: function(response) {
                $('#docNo').val(response.docNo);
                $('#docNo2').val(response.docNo);
            },
            error: function() {
                alert('Error generating document number');
            }
        });
    }

    // Generate Drawing Number
    function generateDrawingNumber() {
        const stageName = $('#stageName').val();
        const productName = $('#productName').val();

        if (!stageName || !productName) {
            return;
        }

        $('#DrawNo').val('Generating...');

        $.ajax({
            url: 'api/DTOController.php',
            type: 'POST',
            data: {
                action: 'generateOrderDrawingNo',
                stage_name: stageName,
                product_name: productName
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#DrawNo').val(response.drawing_number);
                } else {
                    $('#DrawNo').val('');
                    alert(response.message);
                }
            },
            error: function() {
                $('#DrawNo').val('');
                alert('Error generating drawing number');
            }
        });
    }

    // When Stage Name changes
    $('#stageName').change(function() {
        generateDocNo();
        generateDrawingNumber();
    });

    // When Product Name changes
    $('#productName').change(function() {
        generateDrawingNumber();
    });
});
</script>
<!-- <script>
$(document).ready(function() {
    function generateDrawingNumber() {
        const stageName = $('#stageName').val();
        const productName = $('#productName').val();
        const drawingNumberInput = $('#DrawNo');

        console.log('Stage Name:', stageName); // Debug log

        if (!stageName || !productName) {
            return;
        }

        drawingNumberInput.val('Generating...');

        $.ajax({
            url: 'api/DTOController.php',
            type: 'POST',
            data: {
                action: 'generateOrderDrawingNo',
                stage_name: stageName,
                product_name: productName
            },
            dataType: 'json',
            success: function(response) {
                console.log('Response:', response); // Debug log
                if (response.status === 'success') {
                    drawingNumberInput.val(response.drawing_number);
                    $('#DrawNoError').hide();
                } else {
                    drawingNumberInput.val('');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to generate drawing number'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', {xhr, status, error});
                drawingNumberInput.val('');
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to generate drawing number'
                });
            }
        });
    }

    $('#stageName, #productName').change(function() {
        const stageName = $('#stageName').val();
        const productName = $('#productName').val();
        
        $('#stageNameError').toggle(!stageName);
        $('#productNameError').toggle(!productName);

        if (stageName && productName) {
            generateDrawingNumber();
        }
    });
});  
</script> -->
</body>

</html>
