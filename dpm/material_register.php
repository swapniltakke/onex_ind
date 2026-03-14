<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Material Registration Form</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="/dpm/dto/material_register_script.js"></script>
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
            resize: vertical; /* Allows vertical resizing only */
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
html, body {
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
    padding-bottom: 60px; /* Height of your footer */
}

.footer {
    position: fixed;
    bottom: 0;
    width: 100%;
    height: 60px; /* Set your footer height */
    background-color: #ffffff; /* Or your preferred color */
    border-top: 1px solid #e7eaec;
    z-index: 1000;
}

/* Add padding to content to prevent overlap with fixed footer */
.card {
    margin-bottom: 60px; /* Same as footer height */
}

/* If you have a specific container for scrollable content */
.card-body {
    overflow-y: auto;
    max-height: calc(100vh - 120px); /* Viewport height minus header and footer */
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
        <?php require_once $_SERVER["DOCUMENT_ROOT"]."/dpm/shared/sidebar.php"; ?>
        
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

<form id="materialRegistrationForm">
    <input type="hidden" id="currentVersion" name="version">
            <div class="card">
                <div class="card-body">
                    <div class="row justify-content-center m-lg-4" style="margin-top: 1rem !important;">
                        <div class="col-12">
                            <!-- Form row with 2 fields -->
                            <div class="form-row">
                                <!-- Product Name Dropdown -->
                                <div class="form-group col-lg-6">
                                    <label class="form-label product-name-label">Product Name <span style="color: red;">*</span></label>
                                    <select id="product_name" name="product_name" class="form-control required" onchange="checkSelections()">
                                        <option value="" disabled selected>-- Select Product Name --</option>
                                        <option value="NXAIR">NXAIR</option>
                                        <option value="NXAIR H">NXAIR H</option>
                                    </select>
                                </div>

                                <!-- Drawing Name Dropdown -->
                                <div class="form-group col-lg-6">
                                    <label class="form-label drawing-name-label">Drawing Name <span style="color: red;">*</span></label>
                                    <select id="drawing_name" name="drawing_name" class="form-control required" onchange="checkSelections()">
                                        <option value="" disabled selected>-- Select Drawing Name --</option>
                                        <option value=".0 Drawing">.0 Drawing</option>
                                        <option value=".3 Drawing">.3 Drawing</option>
                                    </select>
                                </div>
                            </div>

                    <!-- Second row with more fields if needed -->
                    <div id="additional-fields" class="form-row mt-3" style="display: none;">
                        <div class="form-group col-lg-3">
    <label>Drawing Number <span class="required">*</span></label>
    <input type="text" class="form-control required" name="drawNumber" id="drawNumber">
    <div class="error-message" id="drawNumberError">Please enter drawing number</div>
</div>

<!-- HTML -->
<div class="form-group col-lg-3">
    <label>Type of Material <span class="required">*</span></label>
    <select class="form-control required" name="typeMaterial" id="typeMaterial">
        <option value="" disabled selected>-- Select Material Type --</option>
    </select>
    <div class="error-message" id="typeMaterialError" style="display: none; color: #dc3545; font-size: 12px; margin-top: 5px;"></div>
</div>
                        
<div class="form-group col-lg-3"  id="kaRatingGroup">
    <label  for="kARating">kA Rating <span class="required">*</span></label>
    <select class="form-control required" name="kARating" id="kARating" >
        <option value="" disabled selected>-- Select kA Rating --</option>
        <option value="Upto 31.5 kA">Upto 31.5 kA</option>
        <option value="40 kA">40 kA</option>
        <option value="50 kA">50 kA</option>
    </select>
    <div class="error-message" id="kARatingError">Please select kA rating</div>
</div>
                        
                    <div class="form-group col-lg-3">
    <label>Width <span class="required">*</span></label>
    <select class="form-control required" name="widthGroup" id="widthGroup" onchange="handleWidthSelection(this)">
        <option value="" disabled selected>-- Select Width --</option>
        <option value="435W">435W</option>
        <option value="600W">600W</option>
        <option value="800W">800W</option>
        <option value="1000W">1000W</option>
        <option value="Other">Other</option>
    </select>
    <input type="text" 
        class="form-control mt-2" 
        id="customWidthInput" 
        style="display: none;" 
        placeholder="Enter width (e.g., 435W)">
    <div class="error-message" id="widthSelectorError"></div>
</div>






                    <!-- HTML for new options -->
<!-- Shrouds Assembly Fields -->
<div id="shroudsAssemblyFields" style="display: none;">
    <div class="form-row">
        <!-- Feeder Size -->
        <div class="form-group col-lg-3">
            <label>Feeder Size <span class="required">*</span></label>
            <select class="form-control" id="feederSizeDropdown" name="feederSizeDropdown">
                <option value="" disabled selected>-- Select Feeder Size --</option>
            </select>
            <input type="text" class="form-control mt-2" id="customFeederSizeInput" style="display: none;">
            <div class="error-message" id="feederSizeDropdownError"></div>
        </div>

        <!-- Feeder Run -->
        <div class="form-group col-lg-3">
            <label>No. of Feeder Run <span class="required">*</span></label>
            <select class="form-control" id="feederRunDropdown" name="feederRunDropdown">
                <option value="" disabled selected>-- Select Number of Feeder Run --</option>
            </select>
            <input type="text" class="form-control mt-2" id="customFeederRunInput" style="display: none;">
            <div class="error-message" id="feederRunDropdownError"></div>
        </div>

        <!-- MBB Size -->
        <div class="form-group col-lg-3">
            <label>MBB Size <span class="required">*</span></label>
            <select class="form-control" id="mbbSizeDropdown" name="mbbSizeDropdown">
                <option value="" disabled selected>-- Select MBB Size --</option>
            </select>
            <input type="text" class="form-control mt-2" id="customMbbSizeInput" style="display: none;">
            <div class="error-message" id="mbbSizeDropdownError"></div>
        </div>

        <!-- MBB Run -->
        <div class="form-group col-lg-3">
            <label>No. of MBB Run <span class="required">*</span></label>
            <select class="form-control" id="mbbRunDropdown" name="mbbRunDropdown">
                <option value="" disabled selected>-- Select Number of MBB Run --</option>
            </select>
            <input type="text" class="form-control mt-2" id="customMbbRunInput" style="display: none;">
            <div class="error-message" id="mbbRunDropdownError"></div>
        </div>
    </div>
</div>


<div class="form-group col-lg-3" id="rearBoxGroup">
    <label>Rear Box <span class="required">*</span></label>
    <select class="form-control required" name="rearBoxDropdown" id="rearBoxDropdown" onchange="toggleRearBoxInput(this)">
        <option value="" disabled selected>-- Select Rear Box --</option>
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

            <input type="text" 
        class="form-control" 
        id="customRearBoxInput" 
        style="display: none;" 
        placeholder="Enter custom rear box"
        onblur="setCustomRearBox(this)">
    <div class="error-message" id="rearBoxDropdownError" style="color: #dc3545; font-size: 12px; margin-top: 5px;"></div>
</div>


                        
                        <!-- Material Dropdown -->
<div class="form-group col-lg-3">
    <label for="materialDropdown">Material <span class="required">*</span></label>
    <select 
        class="form-control required" 
        name="materialDropdown" 
        id="materialDropdown"
        required
        aria-label="Material selection"
        aria-required="true"
        aria-describedby="materialDropdownError">
        <option value="" disabled selected>-- Select Material --</option>
    </select>
    <input 
        type="text" 
        class="form-control" 
        id="customMaterialInput" 
        name="customMaterial" 
        placeholder="Enter custom material"
        style="display: none;"
        aria-label="Custom material input">
    <div class="error-message" id="materialDropdownError"></div>
</div>


            

                        <!-- Thickness Dropdown -->
<div class="form-group col-lg-3" id="thicknessGroup">
    <label for="thicknessDropdown">Thickness <span class="required">*</span></label>
    <select class="form-control required" name="thicknessDropdown" id="thicknessDropdown">
        <option value="" disabled selected>-- Select Thickness --</option>
    </select>

    
    <input type="text" 
        class="form-control" 
        id="customThicknessInput" 
        style="display: none;" 
        placeholder="Enter custom thickness (e.g., 2.5mm)">
  <div class="error-message" id="thicknessDropdownError"></div>
</div>

                        <div class="form-group col-lg-3" id="busbarSizeGroup">
    <label for="sizeBusbarDropdown">Size of Busbar</label>
    <select class="form-control" name="sizeBusbarDropdown" id="sizeBusbarDropdown">
        <option value="" disabled selected>-- Select Size of Busbar --</option>
    </select>
    <input type="text" 
        class="form-control mt-2" 
        id="customSizeBusbarInput" 
        style="display: none;" 
        placeholder="Enter custom busbar size">
    <div class="error-message" id="sizeBusbarDropdownError"></div>
</div>


                        <!-- Description Dropdown -->
    <div class="form-group col-lg-3" id="descriptionGroup">
    <label  for="descriptionDropdown">Description <span class="required">*</span></label>
    <select class="form-control required" name="descriptionDropdown" id="descriptionDropdown">
        <option value="" disabled selected>-- Select Description --</option>
    </select>

    <input type="text" class="form-control mt-2" id="customDescriptionInput" name="customDescription" placeholder="Enter custom description" style="display: none;" onblur="setCustomDescription(this)">
    <div class="error-message" id="descriptionDropdownError"></div>
</div>


                                                    <!-- Phase Dropdown (conditional) -->
<div class="form-group col-lg-3" id="panelWidthCombinationGroup">
    <label for="panelWidthCombinationDropdown">Panel Width Combination</label>
    <select class="form-control" id="panelWidthCombinationDropdown" name="panelWidthCombinationDropdown">
        <option value="" disabled selected>-- Select Panel Width Combination --</option>
    </select>
    <!-- Add this input field -->
    <input type="text" 
        class="form-control mt-2" 
        id="customPanelWidthInput" 
        style="display: none;" 
        placeholder="Enter panel width combination">
    <div class="error-message" id="panelWidthCombinationDropdownError"></div>
</div>

<div class="form-group col-lg-3" id="phaseGroup" style="display: none;">
<label for="phaseDropdown">Phase</label>
<select class="form-control" id="phaseDropdown" name="phaseDropdown">
    <option value="" disabled selected>-- Select Phase --</option>
</select>
<div class="error-message" id="phaseDropdownError"></div>
</div>

<div class="form-group col-lg-3" id="agPlatingGroup" style="display: none;">
    <label for="agPlatingDropdown">Ag Plating</label>
    <select class="form-control" id="agPlatingDropdown" name="agPlatingDropdown">
        <option value="" disabled selected>-- Select Ag Plating --</option>
        <option value="Yes">Yes</option>
        <option value="No">No</option>
    </select>
<div class="error-message" id="agPlatingDropdownError"></div>
</div>

<div class="form-group col-lg-3">
    <label>Short Text <span class="required">*</span></label>
    <textarea class="form-control" 
            name="shortTextInput" 
            id="shortTextInput" 
            rows="4"
            placeholder="Short text will be generated as you select options. You can also edit manually."
            oninput="handleManualEdit(this)"></textarea>
</div>

<div id="sheetMetalSubFields" class="subfields-container" style="display: none;">

<!-- Create separate template divs that are hidden by default -->
<template id="endCoverAssemblyTemplate">
    <!-- End Wall Location -->
    <div class="form-group col-lg-3">
        <label>End Wall Location <span class="required">*</span></label>
        <select id="dependency_End_wall_Location" class="form-control" required name="dependency_End_wall_Location" data-error-message="Please select End Wall Location">
            <option value="" disabled selected>-- Select End Wall Location --</option>
            <option value="LHS">LHS</option>
            <option value="RHS">RHS</option>
        </select>
        <div class="error-message">Please select End Wall Location</div>
    </div>

    <!-- EBB Cutout -->
    <div class="form-group col-lg-3">
        <label>EBB Cutout <span class="required">*</span></label>
        <select id="dependency_EBB_Cutout" class="form-control" required name="dependency_EBB_Cutout">
            <option value="" disabled selected>-- Select EBB Cutout --</option>
            <option value="With EBB C/O">With EBB C/O</option>
            <option value="Without EBB C/O">Without EBB C/O</option>
        </select>
        <div class="error-message">Please select EBB Cutout</div>
    </div>

    <!-- EBB Size -->
    <div class="form-group col-lg-3">
        <label>EBB Size <span class="required">*</span></label>
        <select id="dependency_EBB_Size" class="form-control" required name="dependency_EBB_Size">
            <option value="" disabled selected>-- Select EBB Size --</option>
            <option value="40x5">40x5</option>
            <option value="40x10">40x10</option>
        </select>
        <div class="error-message">Please select EBB Size</div>
</div>
</template>

<template id="glandPlateAssemblyTemplate">
    <!-- Cable Entry -->
    <div class="form-group col-lg-3">
        <label>Cable Entry <span class="required">*</span></label>
        <select id="dependency_Cable_entry" class="form-control" required name="dependency_Cable_entry">
            <option value="" disabled selected>-- Select Cable Entry --</option>
            <option value="TOP">TOP</option>
            <option value="Bottom">Bottom</option>
        </select>
        <div class="error-message">Please select Cable Entry</div>
    </div>
</template>

<template id="rearCoverAssemblyTemplate">
        <!-- Interlock -->
    <div class="form-group col-lg-3">
        <label>Interlock <span class="required">*</span></label>
        <select id="dependency_Interlock" class="form-control" required name="dependency_Interlock">
            <option value="" disabled selected>-- Select Interlock --</option>
            <option value="NA">NA</option>
            <option value="EM Interlock">EM Interlock</option>
            <option value="Castle">Castle</option>
            <option value="Ronis">Ronis</option>
        </select>
        <div class="error-message">Please select Interlock</div>
    </div>

    <!-- IR Window -->
    <div class="form-group col-lg-3">
        <label>IR Window <span class="required">*</span></label>
        <select id="dependency_IR_Window" class="form-control" required name="dependency_IR_Window">
            <option value="" disabled selected>-- Select IR Window --</option>
            <option value="NA">NA</option>
            <option value="1 IR">1 IR</option>
            <option value="2 IR">2 IR</option>
        </select>
        <div class="error-message">Please select IR Window</div>
    </div>

    <!-- Nameplate -->
    <div class="form-group col-lg-3">
        <label>Nameplate <span class="required">*</span></label>
        <select id="dependency_Nameplate" class="form-control" required name="dependency_Nameplate">
            <option value="" disabled selected>-- Select Nameplate --</option>
            <option value="NA">NA</option>
            <option value="Danger">Danger</option>
            <option value="Board">Board</option>
            <option value="Feeder">Feeder</option>
        </select>
        <div class="error-message">Please select Nameplate</div>
    </div>

    <!-- Viewing Window -->
    <div class="form-group col-lg-3">
        <label>Viewing Window <span class="required">*</span></label>
        <select id="dependency_Viewing_Window" class="form-control" required name="dependency_Viewing_Window">
            <option value="" disabled selected>-- Select Viewing Window --</option>
            <option value="NA">NA</option>
            <option value="Viewing window">Viewing window</option>
        </select>
        <div class="error-message">Please select Viewing Window</div>
    </div>
</template>

<template id="panelEndWallAssemblyTemplate">
    <div class="form-group col-lg-3">
        <label>Panel RB <span class="required">*</span></label>
        <select id="dependency_Panel_RB" class="form-control" required name="dependency_Panel_RB">
            <option value="" disabled selected>-- Select Panel RB --</option>
            <option value="NA">NA</option>
            <option value="200RB">200RB</option>
            <option value="400RB">400RB</option>
            <option value="600RB">600RB</option>
            <option value="800RB">800RB</option>
            <option value="1000RB">1000RB</option>
        </select>
        <div class="error-message">Please select Panel RB</div>
    </div>
</template>

<template id="rearBoxAssemblyTemplate">
    <div class="form-group col-lg-3">
        <label>Rear Box Type <span class="required">*</span></label>
        <select id="dependency_Rear_Box_Type" class="form-control" required name="dependency_Rear_Box_Type">
            <option value="" disabled selected>-- Select Rear Box Type --</option>
            <option value="TOP Cable">TOP Cable</option>
            <option value="Bottom Cable">Bottom Cable</option>
            <option value="TOP Bus Duct">TOP Bus Duct</option>
        </select>
        <div class="error-message">Please select Rear Box Type</div>
    </div>
</template>

<template id="rbMidPanelLedgesAssemblyTemplate">
<div class="form-group col-lg-3">
        <label>LHS Panel RB <span class="required">*</span></label>
        <select id="dependency_LHS_Panel_RB" class="form-control" required name="dependency_LHS_Panel_RB">
            <option value="" disabled selected>-- Select LHS Panel RB --</option>
            <option value="NA">NA</option>
            <option value="200RB">200RB</option>
            <option value="400RB">400RB</option>
            <option value="600RB">600RB</option>
            <option value="800RB">800RB</option>
            <option value="1000RB">1000RB</option>
        </select>
        <div class="error-message">Please select LHS Panel RB</div>
    </div>

    <!-- RHS Panel RB -->
    <div class="form-group col-lg-3">
        <label>RHS Panel RB <span class="required">*</span></label>
        <select id="dependency_RHS_Panel_RB" class="form-control" required name="dependency_RHS_Panel_RB">
            <option value="" disabled selected>-- Select RHS Panel RB --</option>
            <option value="NA">NA</option>
            <option value="200RB">200RB</option>
            <option value="400RB">400RB</option>
            <option value="600RB">600RB</option>
            <option value="800RB">800RB</option>
            <option value="1000RB">1000RB</option>
        </select>
        <div class="error-message">Please select RHS Panel RB</div>
    </div>
</template>

<template id="ctSupportsAssemblyTemplate">
    <div class="form-group col-lg-3">
        <label>CT Type <span class="required">*</span></label>
        <select id="dependency_CT_Type" class="form-control" required name="dependency_CT_Type">
            <option value="" disabled selected>-- Select CT Type --</option>
            <option value="Window">Window</option>
            <option value="Wound">Wound</option>
        </select>
        <div class="error-message">Please select CT Type</div>
    </div>
</template>

<template id="cableSupportsAssemblyTemplate">
    <div class="form-group col-lg-3">
        <label>No of Cables <span class="required">*</span></label>
        <select id="dependency_No_of_Cables" class="form-control" required name="dependency_No_of_Cables">
            <option value="" disabled selected>-- Select No of Cables --</option>
            <option value="1R">1R</option>
            <option value="2R">2R</option>
            <option value="3R">3R</option>
            <option value="4R">4R</option>
            <option value="5R">5R</option>
            <option value="6R">6R</option>
            <option value="7R">7R</option>
            <option value="8R">8R</option>
        </select>
        <div class="error-message">Please select No of Cables</div>
    </div>

    <!-- CBCT -->
    <div class="form-group col-lg-3">
        <label>CBCT <span class="required">*</span></label>
        <select id="dependency_CBCT" class="form-control" required name="dependency_CBCT">
            <option value="" disabled selected>-- Select CBCT --</option>
            <option value="Yes">Yes</option>
            <option value="No">No</option>
        </select>
        <div class="error-message">Please select CBCT</div>
    </div>
</template>
</div>

                        <div class="form-group col-lg-3">
    <label>Remark </label>
    <input type="text" class="form-control " name="remark" id="remark">
</div>

                            <div class="form-group col-lg-12 text-right" style="margin-top: -20px;">
    <button type="submit" id ="submit-button" class="btn btn-primary">Submit</button>
</div>
</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<?php $footer_display = 'Material Registration Form';
    include_once '../assemblynotes/shared/footer.php'; ?>
    </div>
<!-- Your HTML code -->

<?php include_once '../assemblynotes/shared/headerSemanticScripts.php' ?>
<script src="shared/shared.js"></script>
<!-- <script src="breaker/allBreaker.js?<?php echo rand(); ?>"></script> -->
<!-- <script src="dto/material_register_script.js"></script> -->



</body>
</html>
