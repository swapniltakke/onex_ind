<!-- Required CSS -->
<link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/43.3.0/ckeditor5.css">

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Modal Styling -->
<style>
.modal-dialog {
    max-width: 50%;
    margin: 30px auto;
}

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

.modal-header .close {
    color: white;
    font-size: 24px;
    opacity: 1;
}

.modal-body {
    position: relative;
    padding: 30px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    background-color: #f9f9f9;
    max-height: 70vh;
    overflow: hidden;
}

#modalImage {
    max-width: 100%;
    max-height: 90%;
    object-fit: contain;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    margin-bottom: 20px;
}

#modalPDF {
    width: 100%;
    height: 500px;
    border: none;
    margin-bottom: 20px;
}

#fileUploadContainer {
    width: 100%;
    text-align: center;
    margin-top: 15px;
}

#exampleInputFile {
    padding: 10px;
    font-size: 16px;
    border: 1px solid #ccc;
    border-radius: 5px;
    cursor: pointer;
}

#fileUploadLabel {
    display: block;
    font-size: 16px;
    margin-top: 10px;
    color: #333;
}

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

.btn-white {
    background-color: white;
    border: 1px solid #ccc;
    color: #555;
}

.btn-white:hover {
    background-color: #f1f1f1;
}
</style>

<!-- Modal HTML -->
<div class="modal inmodal" id="ImageModal" role="dialog" aria-labelledby="modalTitle" aria-hidden="true" tabindex="-1">
    <input value="" type="hidden" name="Id" id="hdnId" />
    <div class="modal-dialog modal-lg">
        <div class="modal-content animated bounceInRight">
            <!-- Header -->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                    <span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="modalTitle">DTO Layout</h4>
            </div>

            <!-- Body -->
            <div class="modal-body">
                <!-- Preview Section -->
                <!-- <div id="previewContainer">
                    <img id="modalImage" src="" alt="Image Preview" style="display: none;">
                    <iframe id="modalPDF" style="display: none;"></iframe>
                </div> -->
                <div id="previewContainer" style="width: 100%; max-width: 800px; height: 600px; margin: 0 auto;">
                    <img id="modalImage" src="" alt="Image Preview" style="display: none; width: 100%;  height: 100%; object-fit: contain;">
                </div>
                <!-- Upload -->
                
            </div>

            <!-- Footer -->
            <div id="fileUploadContainer">
                    <input type="hidden" id="docNo" value="">
                    <input type="file" id="exampleInputFile" class="form-control" />
                    <label for="exampleInputFile" id="fileUploadLabel">Upload a new file (Image or PDF)</label>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="updateImage('<?= pathinfo(end(explode('/', $_SERVER['REQUEST_URI'])), PATHINFO_FILENAME) ?>')">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
$(document).ready(function () {
    // Prevent modal from closing on background click or Esc
    $('#ImageModal').modal({
        show: false,
        backdrop: 'static',
        keyboard: false
    });

    // Prevent modal from closing when clicking on the file input
    $('#fileUploadContainer, #exampleInputFile').on('click', function (event) {
        event.stopPropagation();
    });

    // Handle file preview
    $('#exampleInputFile').on('change', function (event) {
        const file = event.target.files[0];

        if (file) {
            const fileType = file.type;

            if (fileType.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    $('#modalImage').attr('src', e.target.result).show();
                    $('#modalPDF').hide();
                };
                reader.readAsDataURL(file);
            } else if (fileType === 'application/pdf') {
                const reader = new FileReader();
                reader.onload = function (e) {
                    $('#modalPDF').attr('src', e.target.result).show();
                    $('#modalImage').hide();
                };
                reader.readAsDataURL(file);
            } else {
                alert('Unsupported file type. Please upload an image or a PDF.');
                $('#modalImage, #modalPDF').hide();
            }
        }
    });
});
</script>

<!-- Optional External Script -->
<script src="dto/updateDtoModal.js?<?= time() ?>"></script>
