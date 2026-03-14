<link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/43.3.0/ckeditor5.css">
<style>
    .ck.ck-editor__main > .ck-editor__editable {
        min-height: 80px !important;
        max-height: 80px;
    }
</style>
<div class="modal inmodal" id="updateModal" role="dialog" aria-hidden="true" tabindex="-1">
    <input value="" type="hidden" name="Id" id="hdnId"/>
    <input value="" type="hidden" name="hdnPanelNo" id="hdnPanelNo"/>
    <div class="modal-dialog modal-lg">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                            class="sr-only">Close</span></button>
                <h4 class="modal-title">Note Update</h4>
                <p style="padding-right: 2rem;">
                    Project No: <b id="updateModalProjectNoSpan"></b>
                    - Panel No: <b id="updateModalPanelNoSpan"></b></p>
            </div>
            <div class="modal-body">
                <div class="form-row mb-1r category">
                    <div class="ta-c col">
                        <label class="control-label">Category</label>
                        <select class="form-control form-select col-sm-8 m-auto" id="updateModalCategory">
                        </select>
                    </div>
                    <div class="ta-c col subCategory">
                        <label class="control-label">Sub Category</label>
                        <select class="form-control col-sm-8 m-auto" id="updateModalSubCategory"></select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="ta-c col" id="divUpdateModalMissingCategory">
                        <label class="control-label">Missing Category</label>
                        <select
                            class="form-control col-sm-8 m-auto" id="updateModalMissingCategory"
                            data-placeholder="Missing Category">
                        </select>
                    </div>
                </div>
                <div class="ta-c form-group" style="margin-top: 1rem">
                    <label class="control-label">Note</label>
                    <textarea rows="5" id="textAreaUpdateNote" placeholder="Enter description"
                              class="form-control"></textarea>
                </div>
                <div class="form-group col-sm-6 m-auto" style="display: flex;">
                    <label style="white-space: nowrap; margin: 0 1rem; line-height: 32px;">Status: </label>
                    <select class="form-control" id="updateModalStatus">
                        <option value="1">Closed</option>
                        <option value="0">Open</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="updateOrderNote('<?= pathinfo(end(explode('/', $_SERVER["REQUEST_URI"])), PATHINFO_FILENAME) ?>')">Save</button>
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
    <script type="module">
        import {
            ClassicEditor,
            Essentials,
            Paragraph,
            Bold,
            Italic,
            Font,
            Strikethrough,
            List // Import the List plugin
        } from 'ckeditor5';

        window.editor = null; // Declare the global editor variable
        ClassicEditor
            .create(document.querySelector('#textAreaUpdateNote'), {
                plugins: [Essentials, Paragraph, Bold, Italic, Font, Strikethrough, List],
                toolbar: [
                    'undo', 'redo', '|', 'bold', 'italic', 'strikethrough', '|',
                    'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor', '|',
                    'bulletedList', 'numberedList' // Add the list buttons to the toolbar
                ]
            })
            .then(editorInstance => {
                window.editor = editorInstance; // Assign the editorInstance to the global editor variable
                const id = $("#hdnId").val();
                // Call the openUpdateModal function from the second file
                openUpdateModal(null, id); // Replace 123 with the actual id
            })
            .catch(error => {
                console.error(error);
            });
    </script>