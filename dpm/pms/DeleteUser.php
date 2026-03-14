<!DOCTYPE html>
<html>
<head>
    <!-- Bootstrap CSS (required for Bootstrap modals) -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/43.3.0/ckeditor5.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    
    <style>
        /* Modal Header */
        .modal-header {
            background-color: #009999;
            color: white;
            padding: 8px 5px;
            text-align: center;
            border-bottom: 1px solid #ddd;
            min-height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-title {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
            padding: 0 15px;
            line-height: 1.2;
        }

        /* Close button */
        .modal-header .close {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            margin: 0;
            padding: 0;
            color: white;
            font-size: 20px;
            opacity: 1;
        }

        /* Modal Dialog */
        .modal-dialog.modal-lg {
            max-width: 800px;
            margin: 1.75rem auto;
            width: 95%;
        }

        /* Modal Content */
        .modal-content {
            width: 100%;
            margin: auto;
        }

        /* Modal Body */
        .modal-body {
            position: relative;
            padding: 20px;
            background-color: #f9f9f9;
            max-height: 70vh;
            overflow: auto;
        }

        /* Modal Footer */
        .modal-footer {
            background-color: #f1f1f1;
            padding: 10px;
            border-top: 1px solid #ddd;
            text-align: right;
        }

        .modal-footer button {
            font-size: 16px;
            padding: 8px 15px;
            margin-left: 10px;
        }

        /* Custom button styles */
        .btn-white {
            background-color: white;
            border: 1px solid #ccc;
            color: #555;
        }

        .btn-white:hover {
            background-color: #f1f1f1;
        }

        /* Custom modal styles */
        #reasonModal {
            display: none;
            position: fixed;
            z-index: 1050;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }

        #reasonModal .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
            max-width: 500px;
        }

        #reasonModal label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }

        #reasonModal select,
        #reasonModal input,
        #reasonModal textarea {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        #reasonModal .modal-footer {
            margin-top: 20px;
            text-align: right;
        }

        /* Alert styling */
        .alert-warning {
            color: #856404;
            background-color: #fff3cd;
            border-color: #ffeeba;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<!-- Delete User Confirmation Modal (Bootstrap) -->
<div class="modal fade" id="DeleteUser" tabindex="-1" role="dialog" aria-hidden="true">
    <input type="hidden" name="Id" id="hdnId"/>
    <div class="modal-dialog modal-lg">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header">
                <h4 class="modal-title">Delete User</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                    <span class="sr-only">Close</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <strong>Warning!</strong> Are you sure you want to delete this user? This action cannot be undone.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="proceedDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Reason for Leaving Modal (Custom) -->
<div id="reasonModal">
    <div class="modal-content">
        <form id="deleteUserForm">
            <input type="hidden" name="user_id" id="delete_user_id">
            
            <div class="modal-header">
                <h4 class="modal-title">Reason for Job Leaving</h4>
                <button type="button" class="close" id="closeReasonModal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
            <div class="modal-body">
                <label for="reason">Select Reason:</label>
                <select name="reason" id="reason" required>
                    <option value="">-- Select --</option>
                    <option value="Resignation">Resignation</option>
                    <option value="Internal Transfer">Internal Transfer</option>
                    <option value="Retirement">Retirement</option>
                    <option value="Termination">Termination</option>
                    <option value="Contract Ended">Contract Ended</option>
                    <option value="Absenteeism">Absenteeism</option>
                    <option value="Layoff / Downsizing">Layoff / Downsizing</option>
                    <option value="Other">Other</option>
                </select>

                <div id="customReasonContainer" style="display:none;">
                    <label for="customReason">Custom Reason:</label>
                    <input type="text" name="customReason" id="customReason">
                </div>

                <label for="remarks">Remarks:</label>
                <textarea name="remarks" id="remarks" rows="3" placeholder="Enter remarks..."></textarea>
            </div>
            
            <div class="modal-footer">
                <button type="button" id="cancelReasonBtn" class="btn btn-white">Cancel</button>
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>
    </div>
</div>

<!-- Required JavaScript libraries -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
$(document).ready(function() {
    // Store user ID when opening delete modal
    window.openDeleteModal = function(userId) {
        $('#hdnId').val(userId);
        $('#DeleteUser').modal('show');
    };
    
    // Proceed to reason modal when delete is confirmed
    $('#proceedDeleteBtn').on('click', function() {
        const userId = $('#hdnId').val();
        $('#delete_user_id').val(userId);
        $('#DeleteUser').modal('hide');
        $('#reasonModal').show();
    });
    
    // Close reason modal
    $('#cancelReasonBtn, #closeReasonModal').on('click', function() {
        $('#reasonModal').hide();
    });
    
    // Show/hide custom reason field
    $('#reason').on('change', function() {
        if ($(this).val() === 'Other') {
            $('#customReasonContainer').show();
            $('#customReason').prop('required', true);
        } else {
            $('#customReasonContainer').hide();
            $('#customReason').prop('required', false);
        }
    });
    
    // Handle form submission
    $('#deleteUserForm').on('submit', function(e) {
        e.preventDefault();
        
        const userId = $('#delete_user_id').val();
        const reason = $('#reason').val();
        const customReason = $('#customReason').val();
        const remarks = $('#remarks').val();
        
        // Create form data
        const formData = new FormData();
        formData.append('user_id', userId);
        formData.append('reason', reason);
        formData.append('custom_reason', customReason);
        formData.append('remarks', remarks);
        formData.append('action', 'deleteUser');
        
        // Send AJAX request
        $.ajax({
            url: 'api/PMSController.php', // Adjust this to your API endpoint
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                // Parse response if needed
                const result = typeof response === 'string' ? JSON.parse(response) : response;
                
                if (result.success) {
                    alert('User deleted successfully');
                    // Reload or redirect
                    window.location.reload();
                } else {
                    alert('Error: ' + (result.message || 'Unknown error'));
                }
                
                // Hide modal
                $('#reasonModal').hide();
            },
            error: function(xhr, status, error) {
                alert('An error occurred: ' + error);
                $('#reasonModal').hide();
            }
        });
    });
    
    // Close modal when clicking outside
    $(window).on('click', function(event) {
        if (event.target.id === 'reasonModal') {
            $('#reasonModal').hide();
        }
    });
});
</script>

<!-- Add a demo button to test the modal -->
<button onclick="openDeleteModal(123)" class="btn btn-danger">Delete User (Demo)</button>

</body>
</html>