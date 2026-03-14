<link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/43.3.0/ckeditor5.css">

<script>
$(document).ready(function() {
    // Initialize modal behavior
    $('#updateModal').modal({
        show: false, // Ensure modal doesn't show by default
        backdrop: 'static', // Prevent closing when clicking outside the modal
        keyboard: false // Disable closing on keyboard events (e.g. Escape key)
    });

    // Allow text selection within the modal
    $('.modal-body').css('user-select', 'text');

    // Prevent modal from closing when clicking anywhere inside the modal
    $('#updateModal').on('click', function(e) {
        // If the click is on the modal body or table, prevent closing
        if ($(e.target).closest('.modal-body').length) {
            e.stopPropagation();
        }
    });
});
</script>

<style>
/* General styling for the modal */
.modal-dialog {
    max-width: 50%; /* Set the modal width to 50% of the screen */
    margin: 30px auto; /* Center the modal horizontally and vertically */
}

/* Modal Header */
.modal-header {
    background-color: #009999;
    color: white;
    padding: 15px 25px;
    text-align: center;
    border-bottom: 1px solid #ddd;
}

.modal-title {
    font-size: 24px;
    font-weight: bold;
}

/* Close button */
.modal-header .close {
    color: white;
    font-size: 24px;
    opacity: 1;
}

/* Modal Body */
.modal-body {
    position: relative;
    padding: 30px;
    display: flex;
    justify-content: center;
    background-color: #f9f9f9;
    max-height: 70vh; /* Set a max height */
    overflow: auto; /* Allow scroll if content overflows */
    user-select: text; /* Allow text selection inside modal */
}

/* Table Styling */
.table {
    width: 100%;
    border-collapse: collapse;
}

.table th, .table td {
    padding: 12px 15px;
    text-align: left;
    border: 1px solid #ddd;
}

.table th {
    background-color: #f8f8f8;
    font-weight: bold;
}

/* Fix the table header during scroll */
.table-responsive {
    overflow-y: auto;
    max-height: 100%;
    width: 100%;
}

.table thead th {
    position: sticky;
    top: 0;
    background-color: #f8f8f8;
    z-index: 1;
}

.table-striped tbody tr:nth-child(odd) {
    background-color: #f9f9f9;
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
</style>

<div class="modal inmodal" id="updateModal" role="dialog" aria-hidden="true" tabindex="-1">
    <input value="" type="hidden" name="Id" id="hdnId"/>
    <div class="modal-dialog modal-lg">
        <div class="modal-content animated bounceInRight">
            <!-- Modal Header -->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                            class="sr-only">Close</span></button>
                <h4 class="modal-title">DTO Data</h4>
            </div>
            <!-- Modal Body -->
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Sr No.</th>
                                <th>Document Number</th>
                                <th>New Material No.</th>
                                <th>Deleted Material No.</th>
                                <th>Quantity</th>
                                <th>Description</th>
                                <th>K-Mat</th>
                            </tr>
                        </thead>
                        <tbody id="breakerDataTableBody">
                             <!-- Rows will be inserted dynamically here -->
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Modal Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-dismiss="modal">Cancel</button>
                <!-- <button type="button" class="btn btn-primary" onclick="updateBreaker('<?= pathinfo(end(explode('/', $_SERVER["REQUEST_URI"])), PATHINFO_FILENAME) ?>')">Save</button> -->
            </div>
        </div>
    </div>
</div>
