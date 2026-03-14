const materialOptions = {
  'Sheet Metal': ['Al', 'MS', 'SS'],
  'Busbar': ['Al', 'Cu'],
  'Gland Plate': ['Al', 'MS', 'SS'],
  'Insulating Plate': ['INS'],
  'GFK Tube': ['GFK'],
  'Shrouds Assembly': ['Shrouds'],
  'Turn Component': ['Other'],
  'Label': ['Other'],
  'Other': ['Other']
};

const thicknessOptionsByType = {
  'Sheet Metal': ['1mm', '1.5mm', '2mm', '2.5mm', '3mm', '4mm', '5mm', '6mm', 'Other'],
  'Busbar': ['3', '5', '10', '15', 'Other'],
  'Gland Plate': ['1mm', '1.5mm', '2mm', '2.5mm', '3mm', '4mm', '5mm', '6mm', 'Other'],
  'Insulating Plate': ['2mm', '3mm', '5mm', 'Other'],
  'GFK Tube': ['10', '15', 'Other'],
  'Other': ['Other']
};

const busbarSizesByType = {
        'Busbar': ['25', '30', '40', '50', '60', '80', '100', '120', '160', 'Other'],
        'GFK Tube': ['60', '80', '100', '120', '160', 'Other']  
    };

const descriptionOptionsByType = {
  'Sheet Metal': ['LHS Pnl End Wall', 'RHS Pnl End Wall', 'End Wall Ledges', 'RB Side Wall LHS', 'RB Side Wall RHS', 'RB Mid Ledges', 'RB Top Ledges LHS', 'RB Top Ledges RHS', 'RB Bottom Ledges LHS', 'RB Bottom Ledges RHS', 'RB Top Sheet', 'RB Bottom Sheet', 'RB Top Angle', 'RB Bottom Angle', 'Insulator Support', 'CT Support', 'PT Support', 'Cable Support', 'CT Support on Side Wall', 'SA Support', 'CVD Support', 'CBCT Support', 'Other'],
  'Busbar': ['Busing to CT', 'Busing to W/o CT', 'CT to CT', 'CT to Cable Conn', 'Busbar to Busbar Conn', 'Cable Conn', 'MBB', 'CT on MBB', 'Direct to MBB', 'EBB', 'EBB Pnl to Pnl Link', 'EBB End LHS', 'EBB END RHS', 'Feeder Conn', 'Feeder Conn Temp Sensor', 'Other'],
  'Gland Plate': ['Rear Box Gland Plate Top', 'Rear Box Gland Plate Bottom', 'Control Cable Gland Plate', 'Gland Plate', 'Other'],
  'Insulating Plate': ['Insulating Plate Ph to Ph', 'Insulating Plate Ph to Earth', 'Transverse partition Sheet 80x10', 'Transverse partition Sheet 100x10', 'Transverse partition Sheet 120x10'],
  'Other': ['Other']
};

const phaseOptionsByDescription = {
  'Panel Width Combination': {
    'MBB': ['435-435', '435-600', '435-800', '435-1000', '600-600', '600-800', '600-1000', '800-800', '800-1000', '1000-1000', 'other'],
    'EBB Pnl to Pnl Link': ['435-435', '435-600', '435-800', '435-1000', '600-600', '600-800', '600-1000', '800-800', '800-1000', '1000-1000', 'other'],
    'GFK Tube': ['435-435', '435-600', '435-800', '435-1000', '600-600', '600-800', '600-1000', '800-800', '800-1000', '1000-1000']
  },
  'Phase': {
    'Feeder Conn': ['L1', 'L2', 'L3', 'L1_1', 'L1_2', 'L1_3', 'L2_1', 'L2_2', 'L2_3', 'L3_1', 'L3_2', 'L3_3'],
    'Feeder Conn Temp Sensor': ['L1', 'L2', 'L3', 'L1_1', 'L1_2', 'L1_3', 'L2_1', 'L2_2', 'L2_3', 'L3_1', 'L3_2', 'L3_3']
  }
};

function checkSelections() {
    const product = document.getElementById("product_name").value;
    const drawing = document.getElementById("drawing_name").value;
    const fieldsContainer = document.getElementById("additional-fields");

    // Show fields only if both values match
    if (product === "NXAIR" && drawing === ".0 Drawing") {
        fieldsContainer.style.display = "flex";
    } else {
        fieldsContainer.style.display = "none";
    }
}

  function initializeAdditionalFields() {
    const productName = document.getElementById('product_name').value;
    const drawingName = document.getElementById('drawing_name').value;

    if (productName === 'NXAIR' && drawingName === '.0 Drawing') {
        document.getElementById('additional-fields').style.display = 'flex';
        updateMaterialOptions();
    } else {
        document.getElementById('additional-fields').style.display = 'none';
    }

    // Add event listeners for the dropdowns
    document.getElementById('product_name').addEventListener('change', checkSelections);
    document.getElementById('drawing_name').addEventListener('change', checkSelections);
}

// Call the initialization function on page load
document.addEventListener('DOMContentLoaded', initializeAdditionalFields);

    function handleWidthSelection(selectElement) {
    const input = document.getElementById("customWidthInput");

    if (selectElement.value === "Other") {
        selectElement.style.display = "none";
        input.style.display = "block";
        input.required = true;
        input.focus();
        input.onblur = function() {
            handleCustomWidth(this);
        };
    } else {
        input.style.display = "none";
        input.required = false;
        input.value = "";
        updateShortText();
    }
}

function handleShroudsCustomInput(inputElement, fieldType) {
    try {
        const value = inputElement.value.trim();
        if (!value) return false;

        // Validate input based on field type
        let isValid = true;
        let errorMessage = '';

        switch (fieldType) {
            case 'feederSize':
            case 'mbbSize':
                isValid = /^\d+x\d+$/.test(value);
                errorMessage = 'Please enter a valid size (e.g., 60x10)';
                break;
            case 'feederRun':
            case 'mbbRun':
                isValid = /^[1-9]\d*$/.test(value);
                errorMessage = 'Please enter a valid number';
                break;
        }

        if (!isValid) {
            alert(errorMessage);
            inputElement.focus();
            return false;
        }

        // Store the custom value
        inputElement.setAttribute('data-custom-value', value);
        updateShortText();
        return true;
    } catch (error) {
        console.error('Error in handleShroudsCustomInput:', error);
        return false;
    }
}

function handleShroudsAssemblyField(selectElement, fieldType) {
    const existingCustomInput = document.getElementById(`custom${fieldType}Input`);
    if (existingCustomInput) {
        existingCustomInput.remove();
    }

    if (selectElement.value === 'Other') {
        const customInput = document.createElement('input');
        customInput.type = 'text';
        customInput.className = 'form-control mt-2';
        customInput.id = `custom${fieldType}Input`;
        customInput.placeholder = `Enter custom ${fieldType.toLowerCase().replace(/([A-Z])/g, ' $1').trim()}`;
        
        // Hide the dropdown
        selectElement.style.display = 'none';
        
        // Insert custom input after the dropdown
        selectElement.parentNode.insertBefore(customInput, selectElement.nextSibling);
        
        // Focus on the new input
        customInput.focus();
        
        // Add event listeners
        customInput.addEventListener('blur', function() {
            const value = this.value.trim();
            if (value) {
                this.setAttribute('data-custom-value', value);
                updateShortText();
            } else {
                // If empty, revert to dropdown
                this.remove();
                selectElement.style.display = 'block';
                selectElement.value = '';
            }
        });
    }
}



    function setCustomWidth(inputElement) {
        const select = document.getElementById("widthSelector");
       
        if (inputElement.value.trim() !== "") {
            // You could also store this in a hidden field if needed
            console.log("Custom width set:", inputElement.value);
        } else {
            // If nothing entered, go back to dropdown
            inputElement.style.display = "none";
            select.style.display = "block";
            select.value = "";
        }
         updateShortText();
    }

    function toggleRearBoxInput(selectElement) {
    try {
        const input = document.getElementById("customRearBoxInput");
        if (!input) {
            console.error('Custom rear box input not found');
            return;
        }
             
        if (selectElement.value === "Other") {
            selectElement.style.display = "none";
            input.style.display = "block";
            input.required = true;
            input.focus();
            input.onblur = function() {
                handleCustomRearBox(this);
            };
        } else {
            input.style.display = "none";
            input.required = false;
            input.value = "";
        }
    } catch (error) {
        console.error('Error in toggleRearBoxInput:', error);
    }
}

    function setCustomRearBox(inputElement) {
    try {
        const select = document.getElementById('rearBoxDropdown');
        if (!select) {
            console.error('Rear box dropdown not found');
            return;
        }

        if (inputElement.value.trim() !== "") {
            console.log("Custom Rear Box:", inputElement.value);
        } else {
            inputElement.style.display = 'none';
            select.style.display = 'block';
            select.value = "";
        }
        updateShortText();
    } catch (error) {
        console.error('Error in setCustomRearBox:', error);
    }
}


    function toggleSizeBusbarInput(selectElement) {
        const input = document.getElementById("customSizeBusbarInput");

        if (selectElement.value === "Other") {
            selectElement.style.display = "none";
            input.style.display = "block";
            input.required = true;
            input.focus();
        } else {
            input.style.display = "none";
            input.required = false;
            input.value = "";
        }
        updateShortText();
    }

    function setCustomSizeBusbar(inputElement) {
        const select = document.getElementById("sizeBusbarDropdown");

        if (inputElement.value.trim() !== "") {
            console.log("Custom Busbar Size:", inputElement.value);
        } else {
            // Revert to dropdown if input is empty
            inputElement.style.display = "none";
            select.style.display = "block";
            select.value = "";
        }
        updateShortText();
    }

// Restore original select element
function restoreSelect(inputElement, fieldName) {
  const select = document.createElement("select");
  select.className = "form-control";
  select.name = fieldName;
  select.id = inputElement.id;
  select.onchange = function () {
    if (this.value === "Other") {
      handleOther(this, fieldName, inputElement.placeholder);
    }
  };

  const options = JSON.parse(inputElement.getAttribute("data-original-options"));
  options.forEach(opt => {
    const option = document.createElement("option");
    option.value = opt.value;
    option.textContent = opt.text;
    select.appendChild(option);
  });

  inputElement.parentNode.replaceChild(select, inputElement);
}

// Utility to extract options from a select element
function getSelectOptions(select) {
  const options = [];
  for (let i = 0; i < select.options.length; i++) {
    options.push({
      value: select.options[i].value,
      text: select.options[i].text
    });
  }
  return options;
}




  function updateMaterialOptions() {
  const typeMaterial = document.getElementById('typeMaterial').value;
  const materialDropdown = document.getElementById('materialDropdown');
  const customMaterialInput = document.getElementById('customMaterialInput');
  const customThicknessInput = document.getElementById('customThicknessInput');
  const customDescriptionInput = document.getElementById('customDescriptionInput');
  const busbarSizeGroup = document.getElementById('sizeBusbarDropdown').parentElement;
   const shroudsAssemblyFields = document.getElementById('shroudsAssemblyFields');
   
if (customMaterialInput) {
        customMaterialInput.style.display = 'none';
    }
    materialDropdown.style.display = 'block';

    // Show/hide busbar size field based on material type
    busbarSizeGroup.style.display = (typeMaterial === 'Busbar' || typeMaterial === 'GFK Tube') ? 'block' : 'none';
    shroudsAssemblyFields.style.display = typeMaterial === 'Shrouds Assembly' ? 'block' : 'none';

    // Populate materials
    const options = materialOptions[typeMaterial] || ['Other'];
    materialDropdown.innerHTML = '';
    const defaultOption = document.createElement('option');
    defaultOption.text = '-- Select Material --';
    defaultOption.disabled = true;
    defaultOption.selected = true;
    materialDropdown.add(defaultOption);

    options.forEach(opt => {
        const option = document.createElement('option');
        option.value = opt;
        option.text = opt;
        materialDropdown.add(option);
    });

    // Add change event listener for Turn Component and Label
    if (typeMaterial === 'Turn Component' || typeMaterial === 'Label') {
        materialDropdown.onchange = function() {
            if (this.value === 'Other') {
                handleCustomMaterial(this);
            } else {
                if (customMaterialInput) {
                    customMaterialInput.style.display = 'none';
                }
                updateShortText();
            }
        };
    }

  if (typeMaterial === 'GFK Tube') {
        const panelWidthCombinationGroup = document.getElementById('panelWidthCombinationGroup');
        if (panelWidthCombinationGroup) {
            panelWidthCombinationGroup.style.display = 'block';
            handlePanelWidthCombination('GFK Tube');
        }
    }

  // Update Rear Box dropdown options for "Turn Component" material type
  if (typeMaterial === 'Turn Component') {
    rearBoxDropdown.innerHTML = '';
    const defaultRearBoxOption = document.createElement('option');
    defaultRearBoxOption.text = '-- Select Rear Box --';
    defaultRearBoxOption.disabled = true;
    defaultRearBoxOption.selected = true;
    rearBoxDropdown.add(defaultRearBoxOption);

    const rearBoxOptions = ['CC', 'BB', 'Other'];
    rearBoxOptions.forEach(opt => {
      const option = document.createElement('option');
      option.value = opt;
      option.text = opt;
      rearBoxDropdown.add(option);
    });
  } else {
    // Restore default Rear Box dropdown options
    updateRearBoxOptions();
  }

  updateThicknessOptions(typeMaterial);
  updateDescriptionOptions(typeMaterial);
}

  function updateRearBoxOptions() {
    const rearBoxDropdown = document.getElementById('rearBoxDropdown');
    rearBoxDropdown.innerHTML = '';
    const defaultOption = document.createElement('option');
    defaultOption.text = '-- Select Rear Box --';
    defaultOption.disabled = true;
    defaultOption.selected = true;
    rearBoxDropdown.add(defaultOption);

    const rearBoxOptions = ['CC', 'MC', 'BB', '200RB', '400RB', '600RB', '800RB', '1000RB', 'Other'];
    rearBoxOptions.forEach(opt => {
    const option = document.createElement('option');
    option.value = opt;
    option.text = opt;
    rearBoxDropdown.add(option);
  });
}

    function updateBusbarSizeOptions(materialType) {
     const busbarSizeDropdown = document.getElementById('sizeBusbarDropdown');
     if (!busbarSizeDropdown) {
        console.error('Busbar size dropdown not found');
        return;
     }
        // Clear existing options
        busbarSizeDropdown.innerHTML = '<option value="" disabled selected>-- Select Size of Busbar --</option>';
        
        // Populate based on selected material type
        const sizes = busbarSizesByType[materialType] || [];
        sizes.forEach(size => {
            const option = document.createElement('option');
            option.value = size;
            option.text = size;
            busbarSizeDropdown.appendChild(option);
        });
    }

function updateThicknessOptions(materialType) {
    try {
        const thicknessDropdown = document.getElementById('thicknessDropdown');
        const thicknessGroup = document.getElementById('thicknessGroup');
        const customThicknessInput = document.getElementById('customThicknessInput');

        if (!thicknessDropdown || !thicknessGroup || !customThicknessInput) {
            console.error('Required thickness elements not found');
            return;
        }

        // Hide custom input initially
        customThicknessInput.style.display = 'none';
        thicknessDropdown.style.display = 'block';

        const thicknessList = thicknessOptionsByType[materialType] || [];

        if (thicknessList.length === 0) {
            thicknessGroup.style.display = 'none';
            return;
        }

        thicknessGroup.style.display = 'block';

        // Clear and populate dropdown
        thicknessDropdown.innerHTML = '';
        
        // Add default option
        const defaultOption = document.createElement('option');
        defaultOption.text = '-- Select Thickness --';
        defaultOption.value = '';
        defaultOption.disabled = true;
        defaultOption.selected = true;
        thicknessDropdown.add(defaultOption);

        // Add thickness options
        thicknessList.forEach(opt => {
            const option = document.createElement('option');
            option.value = opt;
            option.text = opt;
            thicknessDropdown.add(option);
        });

        // Add change event listener
        thicknessDropdown.onchange = function() {
            if (this.value === 'Other') {
                this.style.display = 'none';
                customThicknessInput.style.display = 'block';
                customThicknessInput.value = '';
                customThicknessInput.focus();
            }
            updateShortText();
        };

        // Add blur event listener to custom input
        customThicknessInput.onblur = function() {
            const value = this.value.trim();
            if (!value) {
                // If empty, show dropdown again
                this.style.display = 'none';
                thicknessDropdown.style.display = 'block';
                thicknessDropdown.value = '';
                return;
            }

            // Validate input
            if (!/^\d*\.?\d+(?:mm)?$/.test(value)) {
                alert('Please enter a valid thickness value (e.g., 2.5 or 2.5mm)');
                this.focus();
                return;
            }

            // Store the custom value
            this.setAttribute('data-custom-value', value);
            updateShortText();
        };

    } catch (error) {
        console.error('Error in updateThicknessOptions:', error);
    }
}

function handleCustomThickness() {
    const thicknessDropdown = document.getElementById('thicknessDropdown');
    const customThicknessInput = document.getElementById('customThicknessInput');
    
    if (!thicknessDropdown || !customThicknessInput) {
        console.error('Thickness elements not found');
        return;
    }

    thicknessDropdown.style.display = 'none';
    customThicknessInput.style.display = 'block';
    customThicknessInput.value = '';
    customThicknessInput.focus();
}


function handleCustomInput(inputElement, fieldType) {
    const value = inputElement.value.trim();
    
    if (!value) return false;

    // Store the custom value in a data attribute
    inputElement.setAttribute('data-custom-value', value);
    
    // Update the short text with the custom value
    updateShortText();
    
    return true;
}

function handleCustomMaterial(selectElement) {
    const customMaterialInput = document.getElementById('customMaterialInput');
    
    if (!customMaterialInput) {
        // Create custom input if it doesn't exist
        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'form-control mt-2';
        input.id = 'customMaterialInput';
        input.placeholder = 'Enter custom material';
        
        selectElement.parentNode.insertBefore(input, selectElement.nextSibling);
        input.focus();
        
        input.addEventListener('blur', function() {
            validateAndSetCustomMaterial(this, selectElement);
        });
    } else {
        // Show existing custom input
        customMaterialInput.style.display = 'block';
        selectElement.style.display = 'none';
        customMaterialInput.focus();
    }
}

function getFieldValue(elementId, customInputId) {
    const element = document.getElementById(elementId);
    const customInput = document.getElementById(customInputId);
    
    if (customInput && customInput.style.display !== 'none') {
        return customInput.value || customInput.getAttribute('data-custom-value') || '';
    }
    
    return element?.value || '';
}


function updateDescriptionOptions(materialType) {
    try {
        const descriptionDropdown = document.getElementById('descriptionDropdown');
        const descriptionGroup = document.getElementById('descriptionGroup');
        const phaseGroup = document.getElementById('phaseGroup');
        const panelWidthCombinationGroup = document.getElementById('panelWidthCombinationGroup');

        if (!descriptionDropdown || !descriptionGroup || !phaseGroup) {
            console.error('Required description elements not found');
            return;
        }

        // Reset and hide phase and panel width groups initially
        phaseGroup.style.display = 'none';
        panelWidthCombinationGroup.style.display = 'none';

        const descriptionList = descriptionOptionsByType[materialType] || [];

        if (descriptionList.length === 0) {
            descriptionGroup.style.display = 'none';
            return;
        }

        descriptionGroup.style.display = 'block';
        descriptionDropdown.innerHTML = '<option value="" disabled selected>-- Select Description --</option>';
        
        descriptionList.forEach(opt => {
            const option = document.createElement('option');
            option.value = opt;
            option.text = opt;
            descriptionDropdown.appendChild(option);
        });

        // Add change event listener
        descriptionDropdown.onchange = function() {
            checkForOtherDescription();
        };
    } catch (error) {
        console.error('Error in updateDescriptionOptions:', error);
    }
}

function getShroudsAssemblyValues() {
    try {
        const fields = {
            feederSize: {
                dropdown: 'feederSizeDropdown',
                custom: 'customFeederSizeInput'
            },
            feederRun: {
                dropdown: 'feederRunDropdown',
                custom: 'customFeederRunInput'
            },
            mbbSize: {
                dropdown: 'mbbSizeDropdown',
                custom: 'customMbbSizeInput'
            },
            mbbRun: {
                dropdown: 'mbbRunDropdown',
                custom: 'customMbbRunInput'
            }
        };

        const values = {};

        for (const [key, elements] of Object.entries(fields)) {
            const dropdown = document.getElementById(elements.dropdown);
            const customInput = document.getElementById(elements.custom);

            if (customInput && customInput.style.display !== 'none') {
                values[key] = customInput.value || customInput.getAttribute('data-custom-value') || '';
            } else if (dropdown) {
                values[key] = dropdown.value || '';
            } else {
                values[key] = '';
            }
        }

        return values;
    } catch (error) {
        console.error('Error in getShroudsAssemblyValues:', error);
        return null;
    }
}


function updateShortText() {
    try {
        const typeMaterial = document.getElementById('typeMaterial');
        const shortTextInput = document.getElementById('shortTextInput');
        const phaseGroup = document.getElementById('phaseGroup');
        const phaseEl = document.getElementById('phaseDropdown');

        // Early check for material type
        if (!typeMaterial || !shortTextInput) {
            console.error('Essential elements not found');
            return;
        }

        // Special handling for Turn Component and Label
        if (typeMaterial.value === 'Turn Component' || typeMaterial.value === 'Label') {
            shortTextInput.readOnly = false;
            shortTextInput.value = shortTextInput.value || '';
            shortTextInput.placeholder = `Enter custom text for ${typeMaterial.value}`;
            shortTextInput.focus();
            return;
        }

        // Get values including custom inputs
        const description = getFieldValue('descriptionDropdown', 'customDescriptionInput');
        const width = getFieldValue('widthSelector', 'customWidthInput');
        const rearBox = getFieldValue('rearBoxDropdown', 'customRearBoxInput');
        const material = getFieldValue('materialDropdown', 'customMaterialInput');
        const thickness = getFieldValue('thicknessDropdown', 'customThicknessInput');
        const panelWidth = getFieldValue('panelWidthCombinationDropdown', 'customPanelWidthInput');
        const busbarSize = getFieldValue('sizeBusbarDropdown', 'customSizeBusbarInput');

        // Special handling for GFK Tube
        if (typeMaterial.value === 'GFK Tube') {
            if (material && panelWidth && rearBox && busbarSize && thickness) {
                shortTextInput.value = `${material} ${panelWidth} ${rearBox} ${busbarSize}x${thickness}`;
            }
            return;
        }

        // Special handling for Shrouds Assembly
        if (typeMaterial.value === 'Shrouds Assembly') {
    const values = getShroudsAssemblyValues();
    if (values) {
        const { feederSize, feederRun, mbbSize, mbbRun } = values;
        if (feederSize && feederRun && mbbSize && mbbRun) {
            const shortText = `Insulation Box FDR ${feederRun}x${feederSize} MBB ${mbbRun}x${mbbSize}`;
            shortTextInput.value = shortText;
            console.log('Updated Shrouds Assembly short text:', shortText);
        } else {
            console.log('Missing Shrouds Assembly values:', values);
        }
    }
    return;
}

        // Make short text input readonly for other material types
        shortTextInput.readOnly = true;
        shortTextInput.placeholder = '';

        // Generate short text for other material types
        if (description && width && rearBox && material && thickness) {
            let shortText = '';

            if (panelWidth) {
                shortText = `${description} ${panelWidth}`;
            }
            else if (phaseGroup?.style.display !== 'none' && phaseEl?.value) {
                const phase = phaseEl.value.replace(/\s+/g, '');
                shortText = `${description} ${phase}`;
            } else {
                shortText = description;
            }
           
            shortText += ` ${width} ${rearBox} ${material}`;
            
            if (typeMaterial.value === 'Busbar') {
                if (busbarSize) {
                    shortText += ` ${busbarSize}`;
                }
            }
            
            shortText += `x${thickness}`;
            shortTextInput.value = shortText;
        }
    } catch (error) {
        console.error('Error updating short text:', error);
        console.log('Error details:', error.message);
        const shortTextInput = document.getElementById('shortTextInput');
        shortTextInput.value = 'Error generating short text';
    }
}


// Add event listeners for Shrouds Assembly fields
document.addEventListener('DOMContentLoaded', function() {
    const shroudsFields = ['feederSizeDropdown', 'feederRunDropdown', 
                          'mbbSizeDropdown', 'mbbRunDropdown'];
    
    shroudsFields.forEach(fieldId => {
        const element = document.getElementById(fieldId);
        if (element) {
            element.addEventListener('change', updateShortText);
        }
    });
    const typeMaterial = document.getElementById('typeMaterial');
    if (typeMaterial) {
        typeMaterial.addEventListener('change', function() {
            if (this.value === 'GFK Tube') {
                const panelWidthCombinationGroup = document.getElementById('panelWidthCombinationGroup');
                if (panelWidthCombinationGroup) {
                    panelWidthCombinationGroup.style.display = 'block';
                    handlePanelWidthCombination('GFK Tube');
                }
            }
        });

        const customInputs = {
        'customWidthInput': 'widthSelector',
        'customRearBoxInput': 'rearBoxDropdown',
        'customThicknessInput': 'thicknessDropdown',
        'customPanelWidthInput': 'panelWidthCombinationDropdown',
        'customSizeBusbarInput': 'sizeBusbarDropdown'
    };

    Object.entries(customInputs).forEach(([customId, originalId]) => {
        const customInput = document.getElementById(customId);
        if (customInput) {
            customInput.addEventListener('input', function() {
                this.setAttribute('data-custom-value', this.value);
                updateShortText();
            });
        }
    });

     const shroudsFields = {
        'feederSizeDropdown': 'FeederSize',
        'feederRunDropdown': 'FeederRun',
        'mbbSizeDropdown': 'MbbSize',
        'mbbRunDropdown': 'MbbRun'
    };

    Object.entries(shroudsFields).forEach(([id, fieldType]) => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('change', function() {
                if (this.value === 'Other') {
                    handleShroudsAssemblyField(this, fieldType);
                }
                updateShortText();
            });
        }
    });
    }

    // Add change listener for panel width combination
    const panelWidthDropdown = document.getElementById('panelWidthCombinationDropdown');
    if (panelWidthDropdown) {
        panelWidthDropdown.addEventListener('change', updateShortText);
    }
});

// Add event listener for material type change
document.getElementById('typeMaterial')?.addEventListener('change', function() {
    const materialType = this.value;
    const shortTextInput = document.getElementById('shortTextInput');

    if (materialType === 'Turn Component' || materialType === 'Label') {
        // Make short text input editable
        shortTextInput.readOnly = false;
        shortTextInput.value = '';  // Clear existing value
        shortTextInput.placeholder = `Enter custom text for ${materialType}`;
        shortTextInput.focus();
    } else {
        // Make short text input readonly and update as normal
        shortTextInput.readOnly = true;
        shortTextInput.placeholder = '';
        updateMaterialOptions();
    }
});


function addShroudsAssemblyListeners() {
    const fields = ['feederSizeDropdown', 'feederRunDropdown', 
                   'mbbSizeDropdown', 'mbbRunDropdown'];
    
    fields.forEach(fieldId => {
        const element = document.getElementById(fieldId);
        if (element) {
            element.addEventListener('change', function() {
                console.log(`${fieldId} changed to:`, this.value); // Debug log
                updateShortText();
            });
        }
    });
}



function checkForOtherThickness(e) {
    if (e) e.preventDefault();
    
    const dropdown = document.getElementById('thicknessDropdown');
    if (!dropdown) return;
    
    if (dropdown.value === 'Other') {
        handleCustomThickness();
    }
}

function checkForOtherDescription() {
    try {
        const dropdown = document.getElementById('descriptionDropdown');
        const phaseGroup = document.getElementById('phaseGroup');
        const panelWidthCombinationGroup = document.getElementById('panelWidthCombinationGroup');
        const phaseDropdown = document.getElementById('phaseDropdown');
        const typeMaterial = document.getElementById('typeMaterial');
        
        if (!dropdown || !phaseGroup || !phaseDropdown) {
            console.error('Required elements not found');
            return;
        }

        // Reset dropdowns
        phaseDropdown.innerHTML = '<option value="" disabled selected>-- Select Phase --</option>';
        
        if (dropdown.value === 'Other') {
            handleCustomDescription(dropdown);
            return;
        }

        const selectedDescription = dropdown.value;

        if (typeMaterial.value === 'GFK Tube') {
            handlePanelWidthCombination('GFK Tube');
            return;
        }

        // Check for Panel Width Combination
        if (['MBB', 'EBB Pnl to Pnl Link'].includes(selectedDescription)) {
            handlePanelWidthCombination(selectedDescription);
        }
        // Check for Phase options
        else if (['Feeder Conn', 'Feeder Conn Temp Sensor'].includes(selectedDescription)) {
            handlePhaseOptions(selectedDescription);
        } else {
            phaseGroup.style.display = 'none';
            panelWidthCombinationGroup.style.display = 'none';
        }

     updateShortText();
    } catch (error) {
        console.error('Error in checkForOtherDescription:', error);
    }
}
function handleCustomDescription(dropdown) {
    const phaseGroup = document.getElementById('phaseGroup');
    const panelWidthCombinationGroup = document.getElementById('panelWidthCombinationGroup');

    dropdown.outerHTML = `<input type="text" 
                               class="form-control" 
                               id="descriptionDropdown" 
                               name="description" 
                               placeholder="Enter description"
                               onblur="setCustomDescription(this); updateShortText()">`;
    document.getElementById('descriptionDropdown').focus();
    phaseGroup.style.display = 'none';
    panelWidthCombinationGroup.style.display = 'none';
}

function handlePanelWidthCombination(description) {
    const panelWidthCombinationGroup = document.getElementById('panelWidthCombinationGroup');
    const phaseGroup = document.getElementById('phaseGroup');
    const panelWidthDropdown = document.getElementById('panelWidthCombinationDropdown');

    if (!panelWidthCombinationGroup || !panelWidthDropdown) return;

    panelWidthCombinationGroup.style.display = 'block';
    phaseGroup.style.display = 'none';

    // Clear and populate panel width options
    panelWidthDropdown.innerHTML = '<option value="" disabled selected>-- Select Panel Width Combination --</option>';
    
    const options = phaseOptionsByDescription['Panel Width Combination'][description] || [];
    options.forEach(opt => {
        const option = document.createElement('option');
        option.value = opt;
        option.text = opt;
        panelWidthDropdown.appendChild(option);
    });

    // Add event listener for the dropdown
    panelWidthDropdown.addEventListener('change', function() {
        handlePanelWidthChange(this, description);
    });
}

// New function to handle panel width change
function handlePanelWidthChange(selectElement, description) {
    if (selectElement.value.toLowerCase() === 'other') {
        const customInput = document.createElement('input');
        customInput.type = 'text';
        customInput.className = 'form-control mt-2';
        customInput.id = 'customPanelWidthInput';
        customInput.placeholder = 'Enter custom panel width (e.g., 500-600)';
        
        selectElement.style.display = 'none';
        selectElement.parentNode.insertBefore(customInput, selectElement.nextSibling);
        customInput.focus();
        
        customInput.onblur = function() {
            handleCustomPanelWidth(this);
        };
    }
}

// New function to validate and set custom panel width
function validateAndSetCustomPanelWidth(inputElement, selectElement, description) {
    const value = inputElement.value.trim();
    
    // Validation pattern: should be like "000-000"
    const pattern = /^\d{3,4}-\d{3,4}$/;
    
    if (!value) {
        // If empty, revert to dropdown
        inputElement.remove();
        selectElement.style.display = 'block';
        selectElement.value = '';
        return;
    }

    if (!pattern.test(value)) {
        alert('Please enter a valid panel width combination (e.g., 500-600)');
        inputElement.focus();
        return;
    }

    // If valid, update the dropdown and remove input
    const option = document.createElement('option');
    option.value = value;
    option.text = value;
    option.selected = true;

    // Clear previous options except the first (default) option
    while (selectElement.options.length > 1) {
        selectElement.remove(1);
    }

    // Add all options back
    const options = phaseOptionsByDescription['Panel Width Combination'][description] || [];
    options.forEach(opt => {
        const newOption = document.createElement('option');
        newOption.value = opt;
        newOption.text = opt;
        selectElement.add(newOption);
    });

    // Add and select the custom value
    selectElement.add(option);
    selectElement.value = value;

    // Remove the input field and show the dropdown
    inputElement.remove();
    selectElement.style.display = 'block';

    // Update the short text if needed
    updateShortText();
}

function validateAndSetCustomMaterial(inputElement, selectElement) {
    const value = inputElement.value.trim();
    
    if (!value) {
        // If empty, revert to dropdown
        inputElement.style.display = 'none';
        selectElement.style.display = 'block';
        selectElement.value = '';
        return;
    }

    // Store the custom value
    inputElement.setAttribute('data-custom-value', value);
    updateShortText();
}

function handlePhaseOptions(description) {
    const phaseGroup = document.getElementById('phaseGroup');
    const panelWidthCombinationGroup = document.getElementById('panelWidthCombinationGroup');
    const phaseDropdown = document.getElementById('phaseDropdown');

    if (!phaseGroup || !phaseDropdown) return;

    phaseGroup.style.display = 'block';
    panelWidthCombinationGroup.style.display = 'none';

    // Clear and populate phase options
    phaseDropdown.innerHTML = '<option value="" disabled selected>-- Select Phase --</option>';
    
    const options = phaseOptionsByDescription['Phase'][description] || [];
    options.forEach(opt => {
        const option = document.createElement('option');
        option.value = opt;
        option.text = opt;
        phaseDropdown.appendChild(option);
    });
}

   function setCustomThickness(input) {
    if (!input) return;

    const value = input.value.trim();
    const thicknessDropdown = document.getElementById('thicknessDropdown');
    
    if (!value) {
        // If empty, revert to original dropdown
        const materialType = document.getElementById('typeMaterial').value;
        updateThicknessOptions(materialType);
        return;
    }


    // Validate input (optional)
 if (!/^\d*\.?\d+(?:mm)?$/.test(value)) {
        alert('Please enter a valid thickness value (e.g., 2.5 or 2.5mm)');
        input.focus();
        return;
    }

    // Store the custom value
    input.setAttribute('data-value', value);
    updateShortText();
}

// document.getElementById('typeMaterial')?.addEventListener('change', function() {
//     updateThicknessOptions(this.value);
// });

 function setCustomDescription(input) {
    if (!input) return;

    const value = input.value.trim() || 'Other';
    const group = document.getElementById('descriptionGroup');
    const phaseGroup = document.getElementById('phaseGroup');
    const panelWidthCombinationGroup = document.getElementById('panelWidthCombinationGroup');

    group.innerHTML = `
        <label for="descriptionDropdown">Description</label>
        <select class="form-control" name="description" id="descriptionDropdown" onchange="checkForOtherDescription()">
            <option selected>${value}</option>
        </select>
    `;

    // Hide both groups for custom descriptions
    phaseGroup.style.display = 'none';
    panelWidthCombinationGroup.style.display = 'none';
    updateShortText();
}


function handleCustomWidth(inputElement) {
    const value = inputElement.value.trim();
    if (value) {
        inputElement.setAttribute('data-custom-value', value);
        updateShortText();
    }
}

function handleCustomRearBox(inputElement) {
    const value = inputElement.value.trim();
    if (value) {
        inputElement.setAttribute('data-custom-value', value);
        updateShortText();
    }
}

function handleCustomThickness(inputElement) {
    const value = inputElement.value.trim();
    if (value && /^\d*\.?\d+(?:mm)?$/.test(value)) {
        inputElement.setAttribute('data-custom-value', value);
        updateShortText();
    }
}

function handleCustomPanelWidth(inputElement) {
    const value = inputElement.value.trim();
    if (value && /^\d{3,4}-\d{3,4}$/.test(value)) {
        inputElement.setAttribute('data-custom-value', value);
        updateShortText();
    }
}


document.addEventListener('DOMContentLoaded', function() {
    // Create an object to store all dropdown elements
    const dropdowns = {
        material: document.getElementById('materialDropdown'),
        thickness: document.getElementById('thicknessDropdown'),
        description: document.getElementById('descriptionDropdown'),
        sizeBusbar: document.getElementById('sizeBusbarDropdown'),
        phase: document.getElementById('phaseDropdown')
    };

    // Function to safely add event listeners
    function addSafeEventListener(element, event, handler) {
        if (element) {
            element.addEventListener(event, handler);
        } else {
            console.warn(`Element not found: ${element}`);
        }
    }

    // Add change event listeners to all dropdowns
    Object.entries(dropdowns).forEach(([key, element]) => {
        addSafeEventListener(element, 'change', () => {
            try {
                updateShortText();
            } catch (error) {
                console.error(`Error in ${key} dropdown change handler:`, error);
                // Optionally show user feedback
                showErrorMessage(`Error updating form. Please try again.`);
            }
        });
    });

    const shroudsAssemblyFields = document.getElementById('shroudsAssemblyFields');
    if (shroudsAssemblyFields) {
        addShroudsAssemblyListeners();
    }

    // Add change listener for material type
    const typeMaterial = document.getElementById('typeMaterial');
    if (typeMaterial) {
        typeMaterial.addEventListener('change', function() {
            if (this.value === 'Shrouds Assembly') {
                const shroudsFields = document.getElementById('shroudsAssemblyFields');
                if (shroudsFields) {
                    shroudsFields.style.display = 'block';
                }
            }
        });
    }

    // Optional: Function to show error messages to user
    function showErrorMessage(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'alert alert-danger mt-2';
        errorDiv.textContent = message;
        
        // Insert error message into the form
        const form = document.querySelector('.card-body');
        if (form) {
            form.insertBefore(errorDiv, form.firstChild);
            
            // Remove error message after 5 seconds
            setTimeout(() => {
                errorDiv.remove();
            }, 5000);
        }
    }

    // Optional: Add form reset handler
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('reset', () => {
            Object.values(dropdowns).forEach(dropdown => {
                if (dropdown) {
                    dropdown.selectedIndex = 0;
                }
            });
            document.getElementById('shortTextInput').value = '';
        });
    }

    // Optional: Add validation before updating short text
    function validateDropdowns() {
        const requiredDropdowns = ['material', 'thickness', 'description'];
        return requiredDropdowns.every(key => {
            const dropdown = dropdowns[key];
            return dropdown && dropdown.value;
        });
    }

    // Optional: Initialize dropdowns if needed
    function initializeDropdowns() {
        Object.entries(dropdowns).forEach(([key, dropdown]) => {
            if (dropdown) {
                // Set default values or perform initial setup if needed
                dropdown.selectedIndex = 0;
            }
        });
    }

    // Call initialization function
    initializeDropdowns();
});


document.querySelector('form').addEventListener('submit', function(event) {
    event.preventDefault(); 

    if (validateForm()) {
        submitForm();
    }
});

function validateForm() {
    return true;
}

function submitForm() {
    console.log('Form submitted!');
}


// Add event listener for form fields
document.querySelectorAll('form .required').forEach(field => {
    field.addEventListener('input', checkFormCompletion);
});

// Function to check if all required fields are filled
function checkFormCompletion() {
    const requiredFields = document.querySelectorAll('form [data-required="true"]');
    const submitButton = document.querySelector('form button[type="submit"]');

    let allFieldsFilled = true;
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            allFieldsFilled = false;
        }
    });

    if (allFieldsFilled) {
        submitButton.style.display = 'block';
    } else {
        submitButton.style.display = 'none';
    }
}


// Add this to your material_register_script.js file

function toggleCustomInput(selectElement, customInputId) {
    const customInput = document.getElementById(customInputId);
    if (selectElement.value === 'Other') {
        customInput.style.display = 'block';
        customInput.required = true;
    } else {
        customInput.style.display = 'none';
        customInput.required = false;
    }
}

// Event listeners for each dropdown
document.getElementById('feederSizeDropdown').addEventListener('change', function() {
    toggleCustomInput(this, 'customFeederSizeInput');
});

document.getElementById('feederRunDropdown').addEventListener('change', function() {
    toggleCustomInput(this, 'customFeederRunInput');
});

document.getElementById('mbbSizeDropdown').addEventListener('change', function() {
    toggleCustomInput(this, 'customMbbSizeInput');
});

document.getElementById('mbbRunDropdown').addEventListener('change', function() {
    toggleCustomInput(this, 'customMbbRunInput');
});

// Validation function
function validateShroudsAssemblyFields() {
    const requiredFields = document.querySelectorAll('#shroudsAssemblyFields .required');
    let isValid = true;

    requiredFields.forEach(field => {
        if (!field.value) {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });

    return isValid;
}