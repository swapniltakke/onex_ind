// ============================================
// GLOBAL VARIABLES
// ============================================
var selectedSupervisors = [];
var existingData = {};
var managerData = {
    in_company_manager: { gid: '', name: '' },
    line_manager: { gid: '', name: '' },
    sponsor: { gid: '', name: '' }
};

// ============================================
// HELPER FUNCTION TO EXTRACT GID FROM NAME
// ============================================
function extractGIDFromName(fullName) {
    // If the name contains a GID pattern (e.g., "Z002CU0J")
    const gidMatch = fullName.match(/Z[0-9A-Z]{7}/);
    return gidMatch ? gidMatch[0] : null;
}

// ============================================
// VALIDATION - CONDITIONAL MANAGER FIELDS
// ============================================
function validateForm() {
    let isValid = true;
    let errorMessages = [];
    
    // ✅ CONDITIONAL VALIDATION FOR MANAGERS
    // Either (In-Company Manager AND Line Manager) OR Sponsor must be filled
    const hasInCompanyManager = $('#in_company_manager').val() && $('#in_company_manager').val().trim() !== '';
    const hasLineManager = $('#line_manager').val() && $('#line_manager').val().trim() !== '';
    const hasSponsor = $('#sponsor').val() && $('#sponsor').val().trim() !== '';
    
    const hasBothManagers = hasInCompanyManager && hasLineManager;
    
    if (!hasBothManagers && !hasSponsor) {
        isValid = false;
        errorMessages.push('Please fill either (In-Company Manager AND Line Manager) OR Sponsor');
    }
    
    // ✅ SUPERVISOR IS ALWAYS REQUIRED
    if (!$('#supervisor').val() || $('#supervisor').val().trim() === '') {
        isValid = false;
        errorMessages.push('Supervisor is required');
    }
    
    // ✅ CHECK SPECIFIC REQUIRED FIELDS INDIVIDUALLY
    const requiredSelectFields = [
        { id: 'sub_department', label: 'Sub-Department' },
        { id: 'group_type', label: 'Group Type' },
        { id: 'employment_type', label: 'Employment Type' },
        { id: 'joined', label: 'Joined 01.01.2005' }
    ];
    
    requiredSelectFields.forEach(field => {
        const value = $('#' + field.id).val();
        if (!value || value.trim() === '' || value === '-- Select --') {
            isValid = false;
            errorMessages.push(`${field.label} is required`);
            console.warn(`❌ ${field.label} is empty. Value:`, value);
        }
    });

    // ✅ SHOW ALL ERROR MESSAGES
    if (!isValid) {
        errorMessages.forEach(msg => {
            showNotification('error', msg);
        });
    }

    return isValid;
}

// ============================================
// DUAL MODE SWITCHING
// ============================================
function switchToEditMode(fieldType) {
    console.log('🔄 Switching to edit mode:', fieldType);
    
    if (fieldType === 'supervisor') {
        $('#supervisor_display').addClass('hidden');
        $('#supervisor_edit').addClass('active');
        setTimeout(() => $('#supervisor_search').focus(), 100);
    } else {
        $(`#${fieldType}_display`).addClass('hidden');
        $(`#${fieldType}_edit`).addClass('active');
        
        // Pre-fill search with current value if exists
        if (managerData[fieldType].name) {
            $(`#${fieldType}_search`).val(managerData[fieldType].name);
        }
        
        setTimeout(() => $(`#${fieldType}_search`).focus(), 100);
    }
}

function switchToDisplayMode(fieldType) {
    console.log('🔄 Switching to display mode:', fieldType);
    
    if (fieldType === 'supervisor') {
        $('#supervisor_edit').removeClass('active');
        $('#supervisor_display').removeClass('hidden');
        updateSupervisorDisplayTags();
    } else {
        $(`#${fieldType}_edit`).removeClass('active');
        $(`#${fieldType}_display`).removeClass('hidden');
        
        // Update display text
        if (managerData[fieldType].name) {
            $(`#${fieldType}_display_text`).text(managerData[fieldType].name);
            console.log(`✅ Updated ${fieldType} display:`, managerData[fieldType].name);
        } else {
            $(`#${fieldType}_display_text`).text('-');
        }
    }
}

// ============================================
// DOCUMENT READY
// ============================================
$(document).ready(function() {
    console.log('📋 Document ready - Initializing User Modal');
    
    // Initialize date pickers
    flatpickr("#transfer_from_date", {
        dateFormat: "Y-m-d",
        onChange: function(selectedDates) {
            toDatePicker.set('minDate', selectedDates[0]);
        }
    });

    const toDatePicker = flatpickr("#transfer_to_date", {
        dateFormat: "Y-m-d"
    });

    // Reset form on modal close
    $('#UserModal').on('hidden.bs.modal', function() {
        console.log('🔄 Modal closed - Resetting form');
        resetModalForm();
    });

    // Handle employment type change
    $('#employment_type').on('change', function() {
        const selectedEmploymentType = $(this).val();
        const $joinedSelect = $('#joined');
        
        $joinedSelect.val('');
        $joinedSelect.find('option:not(:first)').remove();
        
        if (selectedEmploymentType === 'Blue Collar') {
            $joinedSelect.append('<option value="before">Before</option>');
            $joinedSelect.val('before');
        } else if (selectedEmploymentType === 'Blue Collar Learner') {
            $joinedSelect.append('<option value="after">After</option>');
            $joinedSelect.val('after');
        } else {
            $joinedSelect.append('<option value="before">Before</option>');
            $joinedSelect.append('<option value="after">After</option>');
        }
        
        console.log('✅ Employment type changed to:', selectedEmploymentType);
    });

    // Manager search inputs
    $('#in_company_manager_search').on('input', function() {
        const searchTerm = $(this).val().trim();
        if (searchTerm.length >= 2) {
            getManagerSuggestions(searchTerm, 'in_company_manager');
        } else {
            $('#in_company_manager_suggestions').hide();
        }
    });

    $('#line_manager_search').on('input', function() {
        const searchTerm = $(this).val().trim();
        if (searchTerm.length >= 2) {
            getManagerSuggestions(searchTerm, 'line_manager');
        } else {
            $('#line_manager_suggestions').hide();
        }
    });

    $('#sponsor_search').on('input', function() {
        const searchTerm = $(this).val().trim();
        if (searchTerm.length >= 2) {
            getManagerSuggestions(searchTerm, 'sponsor');
        } else {
            $('#sponsor_suggestions').hide();
        }
    });

    $('#supervisor_search').on('input', function() {
        const searchTerm = $(this).val().trim();
        if (searchTerm.length >= 2) {
            getSupervisorSuggestions(searchTerm);
        } else {
            $('#supervisor_suggestions').hide();
        }
    });

    // Close suggestions when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.manager-input-container').length && 
            !$(e.target).closest('.dual-mode-field').length) {
            $('.suggestions-container').hide();
        }
    });

    // Focus supervisor search when clicking wrapper
    $('#supervisor_input_wrapper').on('click', function(e) {
        if (!$(e.target).hasClass('remove-supervisor')) {
            $('#supervisor_search').focus();
        }
    });
});

// ============================================
// MANAGER SUGGESTIONS
// ============================================
function getManagerSuggestions(searchTerm, fieldType) {
    const suggestionsId = '#' + fieldType + '_suggestions';
    
    $(suggestionsId).html('<div class="suggestion-item">Loading...</div>').show();
    
    $.ajax({
        url: 'api/PMSController.php',
        method: 'POST',
        data: {
            "action": "getGIDSuggestions",
            "searchTerm": searchTerm
        },
        success: function(response) {
            var data = typeof response === 'string' ? JSON.parse(response) : response;
            var suggestions = data.data || [];
            displayManagerSuggestions(suggestions, searchTerm, fieldType);
        },
        error: function(xhr, status, error) {
            console.error('❌ Error fetching suggestions:', error);
            $(suggestionsId).html('<div class="suggestion-item">Error loading suggestions</div>');
        }
    });
}

function displayManagerSuggestions(managers, searchTerm, fieldType) {
    const suggestionsId = '#' + fieldType + '_suggestions';
    var suggestionsHtml = '';
    
    if (!managers || managers.length === 0) {
        suggestionsHtml = '<div class="suggestion-item">No results found</div>';
        $(suggestionsId).html(suggestionsHtml).show();
        return;
    }
    
    const displayList = managers.slice(0, 10);
    
    displayList.forEach(function(manager) {
        const gid = manager.key;
        let name = manager.value;
        
        // Remove GID from name if it's at the start
        if (name && name.startsWith(gid)) {
            const parts = name.split(' - ');
            if (parts.length > 1) {
                name = parts.slice(1).join(' - ');
            }
        }
        
        const displayText = name;
        const highlightedText = displayText.replace(
            new RegExp('(' + searchTerm + ')', 'gi'),
            '<strong>$1</strong>'
        );
        
        suggestionsHtml += `<div class="suggestion-item" onclick="selectManager('${gid}', '${name.replace(/'/g, "\\'")}', '${fieldType}')" data-gid="${gid}">${highlightedText}</div>`;
    });
    
    $(suggestionsId).html(suggestionsHtml).show();
}

function selectManager(gid, name, fieldType) {
    console.log(`✅ Selected ${fieldType}:`, gid, '-', name);
    
    // Store in manager data
    managerData[fieldType] = { gid: gid, name: name };
    
    // Update hidden field with GID
    $('#' + fieldType).val(gid);
    
    // Update search field with name only
    $('#' + fieldType + '_search').val(name);
    
    // Hide suggestions
    $('#' + fieldType + '_suggestions').hide();
    
    // Switch back to display mode
    switchToDisplayMode(fieldType);
}

// ============================================
// SUPERVISOR SUGGESTIONS - SHOW GID + NAME IN DROPDOWN
// ============================================
function getSupervisorSuggestions(searchTerm) {
    $('#supervisor_suggestions').html('<div class="suggestion-item">Loading...</div>').show();
    
    $.ajax({
        url: 'api/PMSController.php',
        method: 'POST',
        data: {
            "action": "getGIDSuggestions",
            "searchTerm": searchTerm
        },
        success: function(response) {
            var data = typeof response === 'string' ? JSON.parse(response) : response;
            var suggestions = data.data || [];
            displaySupervisorSuggestions(suggestions, searchTerm);
        },
        error: function(xhr, status, error) {
            console.error('❌ Error fetching supervisor suggestions:', error);
            $('#supervisor_suggestions').html('<div class="suggestion-item">Error loading suggestions</div>');
        }
    });
}

function displaySupervisorSuggestions(supervisors, searchTerm) {
    var suggestionsHtml = '';
    
    if (!supervisors || supervisors.length === 0) {
        suggestionsHtml = '<div class="suggestion-item">No supervisors found</div>';
        $('#supervisor_suggestions').html(suggestionsHtml).show();
        return;
    }
    
    const displayList = supervisors.slice(0, 10);
    
    displayList.forEach(function(supervisor) {
        const gid = supervisor.key;
        let name = supervisor.value;
        
        // Remove GID from name if it's at the start
        if (name && name.startsWith(gid)) {
            const parts = name.split(' - ');
            if (parts.length > 1) {
                name = parts.slice(1).join(' - ');
            }
        }
        
        // ✅ SHOW GID + NAME IN DROPDOWN
        const displayText = `${gid} - ${name}`;
        const isSelected = selectedSupervisors.some(s => s.gid === gid);
        
        if (!isSelected) {
            const highlightedText = displayText.replace(
                new RegExp('(' + searchTerm + ')', 'gi'),
                '<strong>$1</strong>'
            );
            
            suggestionsHtml += `<div class="suggestion-item" onclick="selectSupervisor('${gid}', '${name.replace(/'/g, "\\'")}') " data-gid="${gid}">${highlightedText}</div>`;
        }
    });
    
    if (suggestionsHtml === '') {
        suggestionsHtml = '<div class="suggestion-item">All matching supervisors already selected</div>';
    }
    
    $('#supervisor_suggestions').html(suggestionsHtml).show();
}

function selectSupervisor(gid, name) {
    if (selectedSupervisors.length >= 5) {
        showNotification('warning', 'Maximum of 5 supervisors allowed');
        $('#supervisor_suggestions').hide();
        $('#supervisor_search').val('');
        return;
    }
    
    const alreadySelected = selectedSupervisors.some(s => s.gid === gid);
    if (alreadySelected) {
        showNotification('info', 'This supervisor is already selected');
        $('#supervisor_suggestions').hide();
        $('#supervisor_search').val('');
        return;
    }
    
    console.log('✅ Adding supervisor:', gid, '-', name);
    selectedSupervisors.push({ gid: gid, name: name });
    updateSupervisorDisplay();
    updateSupervisorDisplayTags();
    
    $('#supervisor_search').val('');
    $('#supervisor_suggestions').hide();
    $('#supervisor_search').focus();
}

function removeSupervisor(gid) {
    console.log('🗑️ Removing supervisor:', gid);
    selectedSupervisors = selectedSupervisors.filter(s => s.gid !== gid);
    updateSupervisorDisplay();
    updateSupervisorDisplayTags();
}

// ============================================
// UPDATE SUPERVISOR DISPLAY - SHOW ONLY GID IN TAGS
// ============================================
function updateSupervisorDisplay() {
    const container = $('#selected_supervisors_inline');
    const wrapper = $('#supervisor_input_wrapper');
    container.empty();
    
    if (selectedSupervisors.length === 0) {
        $('#supervisor').val('');
        wrapper.removeClass('has-tags');
        $('#supervisor_search').attr('placeholder', 'Type to search (Max 5)...');
        return;
    }
    
    wrapper.addClass('has-tags');
    $('#supervisor_search').attr('placeholder', '');
    
    selectedSupervisors.forEach(function(supervisor) {
        // ✅ SHOW ONLY GID IN TAG
        const displayText = supervisor.gid;
        
        const item = $(`
            <div class="selected-supervisor-item-inline" title="${supervisor.gid} - ${supervisor.name}">
                <span>${displayText}</span>
                <span class="remove-supervisor" onclick="removeSupervisor('${supervisor.gid}')">&times;</span>
            </div>
        `);
        container.append(item);
    });
    
    const gids = selectedSupervisors.map(s => s.gid).join(',');
    $('#supervisor').val(gids);
    
    console.log('✅ Supervisor display updated. GIDs:', gids);
}

// ============================================
// UPDATE SUPERVISOR DISPLAY TAGS - SHOW ONLY GID
// ============================================
function updateSupervisorDisplayTags() {
    const container = $('#supervisor_display_tags');
    container.empty();
    
    console.log('🏷️ Updating supervisor display tags. Count:', selectedSupervisors.length);
    
    if (selectedSupervisors.length === 0) {
        container.html('<span class="field-display-text">-</span>');
        return;
    }
    
    selectedSupervisors.forEach(function(supervisor) {
        // ✅ SHOW ONLY GID IN DISPLAY TAG (with tooltip showing full name)
        const displayText = supervisor.gid;
        const tag = $(`<div class="supervisor-display-tag" title="${supervisor.gid} - ${supervisor.name}">${displayText}</div>`);
        container.append(tag);
        console.log('  ✅ Added tag:', displayText);
    });
}

// ============================================
// RESET FORM
// ============================================
function resetModalForm() {
    console.log('🔄 Resetting modal form');
    
    selectedSupervisors = [];
    managerData = {
        in_company_manager: { gid: '', name: '' },
        line_manager: { gid: '', name: '' },
        sponsor: { gid: '', name: '' }
    };
    
    // Reset all fields to display mode
    $('.field-edit-mode').removeClass('active');
    $('.field-display-mode').removeClass('hidden');
    $('.supervisor-edit-mode').removeClass('active');
    $('.supervisor-display-mode').removeClass('hidden');
    
    // Clear display texts
    $('.field-display-text').text('-');
    $('#supervisor_display_tags').html('<span class="field-display-text">-</span>');
    
    // Clear hidden fields
    $('#in_company_manager').val('');
    $('#line_manager').val('');
    $('#sponsor').val('');
    $('#supervisor').val('');
    
    // Clear search fields
    $('#in_company_manager_search').val('');
    $('#line_manager_search').val('');
    $('#sponsor_search').val('');
    $('#supervisor_search').val('');
    
    // Clear other fields
    $('#hdnId').val('');
    $('#gid').val('');
    $('#name').val('');
    $('#department').val('');
    $('#role').val('');
    $('#sub_department').val('');
    $('#group_type').val('');
    $('#shift_type').val('');
    $('#temp_sub_department').val('');
    $('#temp_group_type').val('');
    $('#employment_type').val('');
    $('#joined').val('');
    $('#transfer_from_date').val('');
    $('#transfer_to_date').val('');
    
    updateSupervisorDisplay();
    $('.suggestions-container').hide();
    
    existingData = {};
}

// ============================================
// GET USER EMPLOYEE DATA
// ============================================
async function getUserEmpData(id){
    return $.ajax({
        url: `api/PMSController.php`,
        type: 'GET',
        dataType: 'json',
        data: {
            "action": "getUserEmpData",
            "id": id
        }
    }).catch(e => {
        console.log(e)
        showNotification("error", "User data could not be loaded");
    });
}

// ============================================
// OPEN USER MODAL - LOAD DATA
// ============================================
async function openUserModal(event, id){
    event.target.innerText = "Loading...";
    event.target.classList.add("disabled");
    
    const userEmpData = await getUserEmpData(id);
    
    console.log('📥 User data received:', userEmpData);    
    
    if (!userEmpData || userEmpData.length === 0) {
        showNotification("error", "Failed to load user data");
        event.target.innerText = "Update";
        event.target.classList.remove("disabled");
        return;
    }
    
    const userData = userEmpData[0];
    existingData = userData;
    
    // ✅ POPULATE BASIC FIELDS
    $("#hdnId").val(userData.id);
    
    // ✅ DISABLED FIELDS (Read-only)
    $("#gid").val(userData.gid?.trim()).prop('disabled', true);
    $("#name").val(userData.name?.trim()).prop('disabled', true);
    $("#department").val(userData.department).prop('disabled', true);
    $("#role").val(userData.role).prop('disabled', true);
    
    // ✅ ENABLED FIELDS (Editable) - WITH LOGGING
    $("#sub_department").val(userData.sub_department || '').prop('disabled', false);
    console.log('✅ Sub-Department set to:', userData.sub_department, '| Current value:', $('#sub_department').val());
    
    $("#group_type").val(userData.group_type || '').prop('disabled', false);
    $("#employment_type").val(userData.employment_type || '').prop('disabled', false);
    $("#shift_type").val(userData.shift_type || '').prop('disabled', false);
    $("#temp_sub_department").val(userData.temp_sub_department || '').prop('disabled', false);
    $("#temp_group_type").val(userData.temp_group_type || '').prop('disabled', false);
    $("#joined").val(userData.joined || '').prop('disabled', false);
    $("#transfer_from_date").val(userData.transfer_from_date || '').prop('disabled', false);
    $("#transfer_to_date").val(userData.transfer_to_date || '').prop('disabled', false);
    
    console.log('✅ Basic fields populated');
    
    // ========================================
    // POPULATE IN-COMPANY MANAGER (DIRECT NAME)
    // ========================================
    if (userData.in_company_manager) {
        const name = userData.in_company_manager.trim();
        const gid = extractGIDFromName(name) || name;
        
        managerData.in_company_manager = { gid: gid, name: name };
        $('#in_company_manager').val(gid);
        $('#in_company_manager_display_text').text(name);
        
        console.log('✅ In-Company Manager:', name);
    } else {
        $('#in_company_manager').val('');
        $('#in_company_manager_display_text').text('-');
    }
    
    // ========================================
    // POPULATE LINE MANAGER (DIRECT NAME)
    // ========================================
    if (userData.line_manager) {
        const name = userData.line_manager.trim();
        const gid = extractGIDFromName(name) || name;
        
        managerData.line_manager = { gid: gid, name: name };
        $('#line_manager').val(gid);
        $('#line_manager_display_text').text(name);
        
        console.log('✅ Line Manager:', name);
    } else {
        $('#line_manager').val('');
        $('#line_manager_display_text').text('-');
    }
    
    // ========================================
    // POPULATE SPONSOR (DIRECT NAME)
    // ========================================
    if (userData.sponsor) {
        const name = userData.sponsor.trim();
        const gid = extractGIDFromName(name) || name;
        
        managerData.sponsor = { gid: gid, name: name };
        $('#sponsor').val(gid);
        $('#sponsor_display_text').text(name);
        
        console.log('✅ Sponsor:', name);
    } else {
        $('#sponsor').val('');
        $('#sponsor_display_text').text('-');
    }
    
    // ========================================
    // POPULATE SUPERVISORS (GIDs ONLY)
    // ========================================
    if (userData.supervisor) {
        selectedSupervisors = [];
        // supervisor contains: "Z0054FUF, Z002CU0J,Z0054M7D"
        const supervisorGIDs = userData.supervisor.split(',').map(g => g.trim()).filter(g => g);
        
        console.log('👥 Processing supervisors:', supervisorGIDs);
        
        // For each GID, fetch the name
        let supervisorPromises = supervisorGIDs.map(gid => {
            return $.ajax({
                url: 'api/PMSController.php',
                method: 'POST',
                data: {
                    "action": "getGIDSuggestions",
                    "searchTerm": gid
                }
            });
        });
        
        Promise.all(supervisorPromises).then(responses => {
            responses.forEach((response, index) => {
                const data = typeof response === 'string' ? JSON.parse(response) : response;
                const gid = supervisorGIDs[index];
                
                if (data.success && data.data && data.data.length > 0) {
                    const match = data.data.find(item => item.key === gid);
                    if (match) {
                        let name = match.value;
                        // Remove GID prefix if present
                        if (name.startsWith(gid)) {
                            const parts = name.split(' - ');
                            if (parts.length > 1) {
                                name = parts.slice(1).join(' - ');
                            }
                        }
                        selectedSupervisors.push({ gid: gid, name: name });
                        console.log('  ✅ Added supervisor:', gid, '-', name);
                    } else {
                        selectedSupervisors.push({ gid: gid, name: gid });
                    }
                } else {
                    selectedSupervisors.push({ gid: gid, name: gid });
                }
            });
            
            updateSupervisorDisplay();
            updateSupervisorDisplayTags();
            console.log('✅ Supervisors loaded. Total:', selectedSupervisors.length);
        });
    } else {
        selectedSupervisors = [];
        updateSupervisorDisplayTags();
    }
    
    // Trigger employment type change to populate joined options
    $('#employment_type').trigger('change');
    
    setTimeout(function() {
        $('#joined').val(userData.joined);
        console.log('✅ Joined field set to:', userData.joined);
    }, 100);
    
    console.log('✅ All user data loaded successfully');
    
    event.target.innerText = "Update";
    event.target.classList.remove("disabled");

    $("#UserModal").modal('show');
    
    if (typeof saveLog === 'function') {
        saveLog("log_assembly_leave", `Opened update User Modal in details page;`);
    }
}

// ============================================
// UPDATE USER - FIXED VERSION
// ============================================
function updateUser(pageName) {
    console.log('💾 Attempting to save user data');
    
    // ✅ LOG ALL FIELD VALUES BEFORE VALIDATION
    console.log('📋 Field Values:');
    console.log('  Sub-Department:', $('#sub_department').val());
    console.log('  Group Type:', $('#group_type').val());
    console.log('  Employment Type:', $('#employment_type').val());
    console.log('  Shift Type:', $('#shift_type').val());
    console.log('  Temp Sub-Department:', $('#temp_sub_department').val());
    console.log('  Temp Group Type:', $('#temp_group_type').val());
    console.log('  Joined:', $('#joined').val());
    console.log('  Transfer From Date:', $('#transfer_from_date').val());
    console.log('  Transfer To Date:', $('#transfer_to_date').val());
    console.log('  In-Company Manager:', $('#in_company_manager').val());
    console.log('  Line Manager:', $('#line_manager').val());
    console.log('  Sponsor:', $('#sponsor').val());
    console.log('  Supervisor:', $('#supervisor').val());
    
    if (!validateForm()) {
        console.warn('⚠️ Form validation failed');
        return;
    }
    
    const formData = {
        action: 'editUser',
        id: $('#hdnId').val(),
        gid: $('#gid').val(),
        name: $('#name').val(),
        department: $('#department').val(),
        sub_department: $('#sub_department').val(),
        role: $('#role').val(),
        group_type: $('#group_type').val(),
        in_company_manager: $('#in_company_manager').val() || '',
        line_manager: $('#line_manager').val() || '',
        supervisor: $('#supervisor').val(),
        sponsor: $('#sponsor').val() || '',
        employment_type: $('#employment_type').val(),
        shift_type: $('#shift_type').val(),
        temp_sub_department: $('#temp_sub_department').val(),
        temp_group_type: $('#temp_group_type').val(),
        joined: $('#joined').val(),
        transfer_from_date: $('#transfer_from_date').val(),
        transfer_to_date: $('#transfer_to_date').val()
    };
    
    console.log('📤 Submitting form data:', formData);
    
    const updateBtn = document.querySelector('[onclick*="updateUser"]');
    if (updateBtn) {
        updateBtn.innerText = "Saving...";
        updateBtn.disabled = true;
    }
    
    $.ajax({
        url: 'api/PMSController.php',
        type: 'POST',
        dataType: 'json',
        data: formData,
        success: function(response) {
            console.log('📥 Update response:', response);
            
            if (response.success || response === 'success' || !response.error) {
                showNotification('success', 'User updated successfully');
                $('#UserModal').modal('hide');
                
                // Reload table if it exists
                if ($.fn.DataTable && $.fn.DataTable.isDataTable('#table_open_items')) {
                    $('#table_open_items').DataTable().ajax.reload(null, false);
                }
                
                if (typeof saveLog === 'function') {
                    saveLog("log_assembly_leave", `User updated successfully in ${pageName || 'details page'}; ID: ${formData.id}, Name: ${formData.name}`);
                }
                
                console.log('✅ User updated successfully');
            } else {
                showNotification('error', response.message || 'Failed to update user');
                console.error('❌ Update failed:', response.message);
            }
            
            if (updateBtn) {
                updateBtn.innerText = "Save Changes";
                updateBtn.disabled = false;
            }
        },
        error: function(xhr, status, error) {
            showNotification('error', 'Error updating user: ' + error);
            console.error('❌ AJAX error:', error);
            console.error('Response:', xhr.responseText);
            
            if (typeof saveLog === 'function') {
                saveLog("log_assembly_leave", `User could not be updated in ${pageName || 'details page'}; ID: ${formData.id}, Error: ${error}`);
            }
            
            if (updateBtn) {
                updateBtn.innerText = "Save Changes";
                updateBtn.disabled = false;
            }
        }
    });
}