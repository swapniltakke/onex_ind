<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dynamic Modal</title>
    <link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/43.3.0/ckeditor5.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* Modal Styling */
.modal-dialog {
    max-width: 70%; /* Smaller than before */
    width: 70%;
    margin: 40px auto;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.modal-content {
    display: flex;
    flex-direction: column;
    max-height: 85vh; /* Keeps modal from overflowing screen */
    overflow: hidden;
}

/* Modal Header */
.modal-header {
    background-color: #5c9ea8;
    color: white;
    padding: 20px;
    text-align: center;
    border-top-left-radius: 10px;
    border-top-right-radius: 10px;
    font-size: 22px;
}

.modal-header .close {
    color: white;
    font-size: 26px;
    opacity: 1;
}

/* Modal Body */
.modal-body {
    padding: 20px;
    background-color: #fafafa;
    overflow-y: auto; /* Scrolls if content is too tall */
    flex-grow: 1;
}

/* Responsive for small screens */
@media (max-width: 768px) {
    .modal-dialog {
        max-width: 95%;
        width: 95%;
        margin: 20px auto;
    }

    .modal-body {
        padding: 15px;
    }

    .form-group label,
    .btn,
    textarea.form-control {
        font-size: 14px;
    }
}

        .form-group {
            margin-bottom: 15px;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .col {
            flex: 1 1 calc(50% - 15px); /* 2 columns per row */
        }

        textarea.form-control {
            border-radius: 8px;
            padding: 12px;
            font-size: 14px;
            border: 1px solid #ccc;
            width: 100%;
            resize: vertical;
        }

        .table-container {
            margin-top: 20px;
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        table#excelTableModal {
            width: 100%;
            border-collapse: collapse;
        }

        table#excelTableModal th, table#excelTableModal td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }

        /* Modal Footer */
        .modal-footer {
            background-color: #f1f1f1;
            padding: 15px;
            text-align: right;
            border-bottom-left-radius: 10px;
            border-bottom-right-radius: 10px;
        }

        .modal-footer button {
            font-size: 16px;
            padding: 8px 20px;
            border-radius: 6px;
        }

        .btn-primary {
            background-color: #009999;
            color: white;
            border: 1px solid #007c7c;
        }

        .btn-primary:hover {
            background-color: #007c7c;
        }

        .delete-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        .delete-btn:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>

    <!-- Modal Structure -->
    <div class="modal inmodal" id="updateModaloffer" role="dialog" aria-hidden="true" tabindex="-1">
        <input value="" type="hidden" name="Id" id="hdnId"/>
        <div class="modal-dialog modal-lg">
            <div class="modal-content animated bounceInRight">
                <!-- Modal Header -->
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
                    </button>
                    <h4 class="modal-title">DTO Data</h4>
                </div>
                <!-- Modal Body -->
                <div class="modal-body">
                    <div class="table-responsive">
                        <!-- Form Fields -->
                        <div class="row">
                        <div class="form-group" style="text-align: center; width: 100%; display: flex; flex-direction: column; align-items: center;">
                                            <label for="docNo2" style="margin-bottom: 10px; text-align: center;">Document Number</label>
                                            <input type="text" id="docNo2" name="docNo2" class="form-control" placeholder="" readonly style="display: inline-block; margin: 0 auto; width: 100%; text-align: center;font-weight: bold;color: #000000; font-size: 18px;">
                                        </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label for="matNo">New Material No.<span class="required">*</span></label>
                                    <textarea id="matNo" name="matNo" class="form-control" placeholder="Material No. (One per line)"></textarea>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label for="dMat">Deleted Material No.<span class="required">*</span></label>
                                    <textarea id="dMat" name="dMat" class="form-control" placeholder="Deleted Material No. (One per line)"></textarea>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label for="quanTt">Quantity<span class="required">*</span></label>
                                    <textarea id="quanTt" name="quanTt" class="form-control" placeholder="Quantity (One per line)"></textarea>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label for="descRp">Description<span class="required">*</span></label>
                                    <textarea id="descRp" name="descRp" class="form-control" placeholder="Description (One per line)"></textarea>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label for="kMat">K-Mat<span class="required">*</span></label>
                                    <textarea id="kMat" name="kMat" class="form-control" placeholder="K-Mat (One per line)"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Add Row Button -->
                        <button type="button" class="btn btn-primary mt-3" onclick="addRow()">Add Row</button>
                        
                        <!-- Table to display data -->
                        <div class="table-container mt-3">
                            <table id="excelTableModal" class="table">
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
                                    <!-- Data rows will be inserted here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" id="cancelBtn">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="updateOffer('<?= pathinfo(end(explode('/', $_SERVER["REQUEST_URI"])), PATHINFO_FILENAME) ?>')">Save</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#updateModaloffer').modal({
                show: false,
                backdrop: 'static',
                keyboard: false
            });

            $('#updateModaloffer').on('click', function(e) {
                e.stopPropagation();
            });

            $('#matNo, #dMat, #quanTt, #descRp, #kMat').on('focus', function(e) {
                e.stopPropagation();
            });

            $('#cancelBtn').click(function() {
                $('#updateModaloffer').modal('hide');
            });
        });

        function addRow() {
    var docNo2 = $('#docNo2').val();
    var matNo = $('#matNo').val().split('\n');
    var dMat = $('#dMat').val().split('\n');
    var quanTt = $('#quanTt').val().split('\n');
    var descRp = $('#descRp').val().split('\n');
    var kMat = $('#kMat').val().split('\n');

    for (var i = 0; i < Math.max(matNo.length, dMat.length, quanTt.length, descRp.length, kMat.length); i++) {
        var newRow = $('<tr>');

        newRow.append($('<td>').text(docNo2 || ''));
        newRow.append($('<td>').text(matNo[i] || ''));
        newRow.append($('<td>').text(dMat[i] || ''));
        newRow.append($('<td>').text(quanTt[i] || ''));
        newRow.append($('<td>').text(descRp[i] || ''));
        newRow.append($('<td>').text(kMat[i] || ''));
        newRow.append($('<td>').html('<button class="btn btn-danger btn-sm delete-btn">Delete</button>'));

        $('#excelTableModal tbody').append(newRow); // adds rows to the bottom
    }

    // Clear input fields after adding rows
    $('#matNo, #dMat, #quanTt, #descRp, #kMat').val('');

    // 🔥 Retrieve current full table data
    const tableData = getTableData();
    console.log('Full Table Data:', tableData); // <-- or do something with it
}


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

        // $(document).on('click', '.delete-btn', function() {
        //     var row = $(this).closest('tr'); 
        //     row.remove(); 
        // });
        $(document).on('click', '.delete-btn', function() {
            var row = $(this).closest('tr'); 
            row.remove(); 
        });
        $('#excelTableModal tbody').on('click', '.delete-btn', function () {
            $(this).closest('tr').remove();
        });
        $('#addRowBtn').click(addRow);

       
    </script>
   
</body>
</html>
