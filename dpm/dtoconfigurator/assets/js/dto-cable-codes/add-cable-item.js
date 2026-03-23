$(document).ready(function() {
    // Initialize page
    initializePage();

    function initializePage() {
        // Hide loader and show content
        setTimeout(function() {
            $('.ui.loader').removeClass('active');
            $('.addCableItemPageContainer').show();
        }, 500);

        // Initialize dropdown
        $('#cableTypeCategory').dropdown({
            onChange: function(value) {
                handleCableTypeChange(value);
            }
        });

        // Initialize product dropdown
        $('select[name="product_types[]"]').dropdown();

        // Load products and RAL colors
        loadProducts();
        loadRALColors();

        // Initialize form validation
        initializeFormValidation();

        // Bind event listeners
        bindEventListeners();
    }

    function handleCableTypeChange(value) {
        if (value === 'VTH') {
            // Update labels and placeholders
            $('#codeLabel').text('VTH Code');
            $('input[name="vthCode"]').attr('placeholder', 'e.g., VTH:11XXAAA1A1B3C1');

            // Show VTH colors, hide CTH colors and specific fields
            $('#vthColors').show();
            $('#cthColors').hide();
            $('#cthSpecificSection').hide();

            // Show common sections
            showCommonSections();

            setTimeout(function() {
                $('#vthColors .ral-color-dropdown').each(function() {
                    applyColorStyling($(this));
                });
            }, 300);

        } else if (value === 'CTH') {
            // Update labels and placeholders
            $('#codeLabel').text('CTH Code');
            $('input[name="vthCode"]').attr('placeholder', 'e.g., CTH:4442BIXXX2AA1A1C1');

            // Show CTH colors and specific fields, hide VTH colors
            $('#vthColors').hide();
            $('#cthColors').show();
            $('#cthSpecificSection').show();

            // Initialize CTH-specific dropdowns
            $('#cthSpecificSection .ui.dropdown').dropdown();

            // Show common sections
            showCommonSections();

            setTimeout(function() {
                $('#cthColors .ral-color-dropdown').each(function() {
                    applyColorStyling($(this));
                });
            }, 300);
        }
    }

    function showCommonSections() {
        // Show all form sections with smooth animation
        $('#basicInfoSection').slideDown(300);
        $('#cableColorsSection').slideDown(400);
        $('#productUsageSection').slideDown(500);
        $('#formActionsSection').slideDown(600);
    }
    function initializeFormValidation() {
        $('#checklistForm').form({
            fields: {
                cableTypeCategory: {
                    identifier: 'cableTypeCategory',
                    rules: [
                        {
                            type: 'empty',
                            prompt: 'Please select a cable type category'
                        }
                    ]
                },
                definition: {
                    identifier: 'definition',
                    rules: [
                        {
                            type: 'empty',
                            prompt: 'Please enter cable harness definition'
                        }
                    ]
                },
                numberHarness: {
                    identifier: 'numberHarness',
                    rules: [
                        {
                            type: 'empty',
                            prompt: 'Please enter harness number'
                        }
                    ]
                },
                vthCode: {
                    identifier: 'vthCode',
                    rules: [
                        {
                            type: 'empty',
                            prompt: 'Please enter VTH/CTH code'
                        }
                    ]
                },
                cableType: {
                    identifier: 'cableType',
                    rules: [
                        {
                            type: 'empty',
                            prompt: 'Please enter cable type'
                        }
                    ]
                },
                cableCrossSection: {
                    identifier: 'cableCrossSection',
                    rules: [
                        {
                            type: 'empty',
                            prompt: 'Please enter cable cross-section'
                        }
                    ]
                }
            },
            onSuccess: function(event) {
                event.preventDefault();
                submitForm();
            }
        });
    }

    function bindEventListeners() {
        // Cancel button
        $('#cancelBtn').on('click', function() {
            if (confirm('Are you sure you want to cancel? All unsaved changes will be lost.')) {
                resetForm();
            }
        });

        // Auto-format RAL color codes
        $('input[name*="Color"]').on('blur', function() {
            formatRALCode($(this));
        });
    }

    function formatRALCode(input) {
        let value = input.val().trim();
        if (value && !value.toLowerCase().includes('ral') && /^\d{4}$/.test(value)) {
            input.val(value + ' RAL ' + value);
        }
    }

    function submitForm() {
        // Show loading state
        $('#submitBtn').addClass('loading');

        // Collect form data and convert to FormData
        const data = collectFormData();

        // Create FormData object like your working pattern
        const formData = new FormData();
        formData.append('action', 'createCable');

        // Append all form fields
        formData.append('cableTypeCategory', data.cableTypeCategory);
        formData.append('definition', data.definition);
        formData.append('notes', data.notes || '');
        formData.append('numberHarness', data.numberHarness);
        formData.append('vthCode', data.vthCode);
        formData.append('numberDrawing', data.numberDrawing || '');
        formData.append('cableType', data.cableType);
        formData.append('cableCrossSection', data.cableCrossSection || '');
        formData.append('cableLengthType', data.cableLengthType || '');
        formData.append('placeToUse', data.placeToUse || '');
        formData.append('panelWidth', data.panelWidth || '');
        formData.append('core', data.core || '');
        formData.append('orderNo', data.orderNo || '');
        formData.append('additionalInfo', data.additionalInfo || '');

        // Append product types as JSON string
        formData.append('productTypes', JSON.stringify(data.productTypes || []));

        // Append colors as JSON string
        formData.append('colors', JSON.stringify(data.colors || {}));

        // Append CTH specific fields if exists
        if (data.cthSpecific) {
            formData.append('cthSpecific', JSON.stringify(data.cthSpecific));
        }

        // Validate required fields
        if (!validateFormData(data)) {
            $('#submitBtn').removeClass('loading');
            return;
        }

        axios.post('/dpm/dtoconfigurator/api/controllers/CableController.php', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        })
            .then(function(response) {
                handleSubmitSuccess(response);
            })
            .catch(function(error) {
                handleSubmitError(error);
            })
            .finally(function() {
                $('#submitBtn').removeClass('loading');
            });
    }

    function collectFormData() {
        const selectedProducts = $('select[name="product_types[]"]').val();
        const cableTypeCategory = $('input[name="cableTypeCategory"]').val();

        const formData = {
            action: 'createCable',
            cableTypeCategory: cableTypeCategory,
            definition: $('input[name="definition"]').val(),
            numberHarness: $('input[name="numberHarness"]').val(),
            vthCode: $('input[name="vthCode"]').val(),
            numberDrawing: $('input[name="numberDrawing"]').val(),
            cableType: $('input[name="cableType"]').val(),
            cableCrossSection: $('input[name="cableCrossSection"]').val(),
            cableLengthType: $('input[name="cableLengthType"]').val(),
            notes: $('textarea[name="notes"]').val(),
            productTypes: selectedProducts,
            placeToUse: $('input[name="placeToUse"]').val(),
            panelWidth: parseInt($('input[name="panelWidth"]').val()) || null,
            core: parseInt($('input[name="core"]').val()) || null,
            orderNo: $('input[name="orderNo"]').val(),
            additionalInfo: $('textarea[name="additionalInfo"]').val(),
            createdAt: new Date().toISOString()
        };

        // Add cable type specific colors
        if (cableTypeCategory === 'VTH') {
            formData.colors = {
                t5l1ColorA: $('select[name="t5l1ColorA"]').val(),
                t5l1ColorN: $('select[name="t5l1ColorN"]').val(),
                t5l2ColorA1: $('select[name="t5l2ColorA1"]').val(),
                t5l2ColorN1: $('select[name="t5l2ColorN1"]').val(),
                t5l2ColorA2: $('select[name="t5l2ColorA2"]').val(),
                t5l2ColorN2: $('select[name="t5l2ColorN2"]').val()
            };
        } else if (cableTypeCategory === 'CTH') {
            formData.colors = {
                t1l1_1s1: $('select[name="t1l1_1s1"]').val(),
                t1l1_1s2: $('select[name="t1l1_1s2"]').val(),
                t1l1_1s3: $('select[name="t1l1_1s3"]').val(),
                t1l2_1s1: $('select[name="t1l2_1s1"]').val(),
                t1l2_1s2: $('select[name="t1l2_1s2"]').val(),
                t1l2_1s3: $('select[name="t1l2_1s3"]').val(),
                t1l3_1s1: $('select[name="t1l3_1s1"]').val(),
                t1l3_1s2: $('select[name="t1l3_1s2"]').val(),
                t1l3_1s3: $('select[name="t1l3_1s3"]').val()
            };

            // Add CTH specific fields
            formData.cthSpecific = {
                cableLengthTotal: $('input[name="cableLengthTotal"]').val(),
                cableLengthGroups: $('input[name="cableLengthGroups"]').val(),
                totalCableLength: $('input[name="totalCableLength"]').val(),
                ctInRearBox: $('input[name="ctInRearBox"]').val(),
                panelWidthCT: parseInt($('input[name="panelWidthCT"]').val()) || null,
                coreCT: parseInt($('input[name="coreCT"]').val()) || null
            };
        }

        return formData;
    }

    function validateFormData(formData) {
        // Additional custom validation if needed
        if (!formData.cableTypeCategory) {
            showMessage('error', 'Please select a cable type category');
            return false;
        }

        if (!formData.definition) {
            showMessage('error', 'Please enter cable harness definition');
            return false;
        }

        if (!formData.vthCode) {
            showMessage('error', 'Please enter VTH/CTH code');
            return false;
        }

        return true;
    }

    function handleSubmitSuccess(response) {
        showMessage('success', 'Cable code created successfully!');

        setTimeout(function() {
            resetForm();
        }, 1500);
    }

    function handleSubmitError(error) {
        console.error('Error creating cable:', error);

        let errorMessage = 'An error occurred while creating the cable code.';

        if (error.response && error.response.data && error.response.data.message) {
            errorMessage = error.response.data.message;
        } else if (error.message) {
            errorMessage = error.message;
        }

        showMessage('error', errorMessage);
    }

    function resetForm() {
        $('#checklistForm')[0].reset();

        $('#cableTypeCategory').dropdown('clear');
        $('select[name="product_types[]"]').dropdown('clear');

        $('.ral-color-dropdown').dropdown('clear');

        $('.ui.form').form('remove prompt');

        $('#basicInfoSection').hide();
        $('#cableColorsSection').hide();
        $('#cthSpecificSection').hide();
        $('#productUsageSection').hide();
        $('#formActionsSection').hide();

        $('#vthColors').hide();
        $('#cthColors').hide();

        showMessage('info', 'Form has been reset');
    }

    function showMessage(type, message) {
        $('.ui.message').remove();

        const messageClass = type === 'success' ? 'positive' :
            type === 'error' ? 'negative' :
                type === 'warning' ? 'warning' : 'info';

        const iconClass = type === 'success' ? 'checkmark' :
            type === 'error' ? 'warning sign' :
                type === 'warning' ? 'warning' : 'info circle';

        const messageHtml = `
            <div class="ui ${messageClass} message">
                <div class="content">
                    <i class="${iconClass} icon"></i>
                    <div class="header">${type.charAt(0).toUpperCase() + type.slice(1)}</div>
                    <p>${message}</p>
                </div>
            </div>
        `;

        $('#addCableItemForm').prepend(messageHtml);

        if (type === 'success' || type === 'info') {
            setTimeout(function() {
                $('.ui.' + messageClass + '.message').fadeOut(500, function() {
                    $(this).remove();
                });
            }, 3000);
        }

        $('html, body').animate({
            scrollTop: $('#addCableItemForm').offset().top - 20
        }, 500);
    }

    // Utility functions
    function formatNumber(value, decimals = 2) {
        if (!value) return '';
        return parseFloat(value).toFixed(decimals);
    }

    function validateRALCode(code) {
        // Basic RAL code validation
        const ralPattern = /RAL\s?\d{4}/i;
        return ralPattern.test(code);
    }

    // Auto-save functionality (optional)
    function enableAutoSave() {
        let autoSaveTimeout;

        $('#checklistForm input, #checklistForm textarea, #checklistForm select').on('input change', function() {
            clearTimeout(autoSaveTimeout);
            autoSaveTimeout = setTimeout(function() {
                saveFormDraft();
            }, 2000); // Auto-save after 2 seconds of inactivity
        });
    }

    function saveFormDraft() {
        const formData = collectFormData();
        localStorage.setItem('cableFormDraft', JSON.stringify(formData));
        console.log('Form draft saved');
    }

    function loadFormDraft() {
        const draft = localStorage.getItem('cableFormDraft');
        if (draft) {
            try {
                const formData = JSON.parse(draft);
                populateForm(formData);
                showMessage('info', 'Draft loaded from previous session');
            } catch (e) {
                console.error('Error loading draft:', e);
            }
        }
    }

    function populateForm(data) {
        // Populate form fields with saved data
        Object.keys(data).forEach(key => {
            if (key === 'colors') {
                Object.keys(data.colors).forEach(colorKey => {
                    $(`input[name="${colorKey}"]`).val(data.colors[colorKey]);
                });
            } else {
                $(`input[name="${key}"], textarea[name="${key}"]`).val(data[key]);
            }
        });

        if (data.cableTypeCategory) {
            $('#cableTypeCategory').dropdown('set selected', data.cableTypeCategory);
        }
    }

    async function loadProducts() {
        try {
            const response = await axios.get('/dpm/dtoconfigurator/api/controllers/CableController.php', {
                params: { action: 'getCableProducts'}
            });

            const data = response.data;
            let html = '<option value="">Choose product types...</option>';

            data.forEach(function(product) {
                html += `<option value="${product.id}">${product.product_type}</option>`;
            });

            $('select[name="product_types[]"]').html(html);
        } catch (error) {
            console.error('Error fetching products:', error);
            showMessage('error', 'Error loading products');
        }
    }
});

// Add this function to load RAL colors
async function loadRALColors() {
    try {
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/CableController.php', {
            params: { action: 'getRALColors'}
        });

        const data = response.data;
        let html = '<option value="">Select RAL Color</option>';

        data.forEach(function(color) {
            html += `<option value="${color.id}" data-ral-code="${color.ral_code}" data-hex-code="${color.hex_code}" data-tr-color="${color.tr_color}" data-color-name="${color.color_name}">
                        ${color.ral_code} - ${color.tr_color} / ${color.color_name}
                     </option>`;
        });

        $('.ral-color-dropdown').html(html);

        initializeRALDropdowns();

    } catch (error) {
        console.error('Error fetching RAL colors:', error);
        showMessage('error', 'Error loading RAL colors');
    }
}

// Initialize RAL color dropdowns with custom templates
function initializeRALDropdowns() {

    $('.ral-color-dropdown').each(function() {
        const $dropdown = $(this);

        // Initialize dropdown first
        $dropdown.dropdown({
            onShow: function() {
                // Apply styling when dropdown opens
                setTimeout(function() {
                    applyColorStyling($dropdown);
                }, 50);
            }
        });

        // Apply styling immediately after initialization
        setTimeout(function() {
            applyColorStyling($dropdown);
        }, 100);
    });
}

// Function to apply color styling to dropdown items
function applyColorStyling($dropdown) {
    $dropdown.find('.menu .item').each(function() {
        const $item = $(this);
        const value = $item.attr('data-value');

        if (value && value !== '') {
            // Find the corresponding option
            const $option = $dropdown.find(`option[value="${value}"]`);
            const hexCode = $option.attr('data-hex-code');
            const ralCode = $option.attr('data-ral-code');
            const colorName = $option.attr('data-color-name');
            const trColor = $option.attr('data-tr-color');

            if (hexCode) {
                // Apply background color and text color
                $item.css({
                    'background-color': hexCode,
                    'color': getContrastColor(hexCode),
                    'padding': '8px 12px',
                    'border': '1px solid rgba(0,0,0,0.1)',
                    'margin': '1px 0'
                });

                // Update the text content with color preview
                $item.html(`
                    <span style="display:inline-block; width:20px; height:20px; background-color:${hexCode}; border:1px solid #000; margin-right:10px; vertical-align:middle; border-radius:3px;"></span>
                    ${ralCode} - ${trColor} / ${colorName}
                `);
            }
        }
    });
}

// Helper function to get contrasting text color (black or white)
function getContrastColor(hexColor) {
    // Remove # if present
    hexColor = hexColor.replace('#', '');

    // Convert to RGB
    const r = parseInt(hexColor.substr(0, 2), 16);
    const g = parseInt(hexColor.substr(2, 2), 16);
    const b = parseInt(hexColor.substr(4, 2), 16);

    // Calculate luminance
    const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;

    // Return black text for light colors, white text for dark colors
    return luminance > 0.5 ? '#000000' : '#ffffff';
}