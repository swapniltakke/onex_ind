
<link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/43.3.0/ckeditor5.css">
<style>
    .modal-body {
        height: 81vh; /* Replace <your-desired-height> with the value you want, e.g., 60vh */
        overflow-y: auto;
    }
    .inmodal .modal-header {
        padding: 2px 20px !important;
        text-align: center !important;
        display: block !important;
    }
    .col-lg-4.col-form-label {
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
</style>
<div class="modal" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">Image</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Image will be displayed here -->
                <img id="modalImage" src="" alt="Image" style="width: 100%; height: auto;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
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
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>