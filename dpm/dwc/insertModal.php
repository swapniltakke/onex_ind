<link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/43.3.0/ckeditor5.css">
<style>
   .ck.ck-editor__main > .ck-editor__editable {
   min-height: 80px !important;
   max-height: 80px;
   }
</style>
<div class="modal inmodal" id="insertModal" role="dialog" aria-hidden="true" tabindex="-1">
   <input type="hidden" value="0" name="Id" id="hdnId"/>
   <input type="hidden" value="0" name="hdnPanelNo" id="hdnPanelNo"/>
   <div class="modal-dialog modal-lg">
      <div class="modal-content animated bounceInRight">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
               class="sr-only">Close</span></button>
            <h4 class="modal-title">Add New Note</h4>
         </div>
         <div class="modal-body" style="min-height: 280px">
            <div class="form-row mb-1r category">
               <div class="ta-c col">
                  <label class="control-label">Category</label>
                  <select onChange="showSelects(this.options[this.selectedIndex].value)"
                     class="form-control form-select col-12" id="slCategory">
                  </select>
               </div>
               <div class="ta-c col d-none subCategory">
                  <label class="control-label">Sub Category</label>
                  <select class="form-control col-sm-8 m-auto" id="slSubCategory"></select>
               </div>
            </div>
            <div class="form-row">
               <div class="ta-c col" id="divSlMissingCategory">
                  <label class="control-label">Missing Category</label>
                  <select
                     class="form-control" id="slMissingCategory"
                     data-placeholder="Missing Category">
                  </select>
               </div>
               <div class="ta-c col hasMultiSelect" id="divSlPanoNos">
                  <label class="control-label">Panel No</label>
                  <select class="form-control m-auto" id="slPanoNos" name="panoNos[]"
                     multiple="multiple"></select>
               </div>
            </div>
            <div class="form-row mt-4" id="parent_divSlMaterialNos">
               <div class="ta-c col" id="divSlMaterialNos">
                  <label class="control-label">Material No</label>
                  <select class="form-control m-auto" id="slMaterialNos" name="MaterialNos"></select>
               </div>
            </div>
            <div class="form-row mt-4">
               <div class="ta-c col">
                  <label class="control-label">Note</label>
                  <textarea class="form-control" name="textAreaNewNote" id="textAreaNewNote"></textarea>
               </div>
            </div>
            <div class="form-row">
               <div class="ta-c col d-none" id="divEmergencyLineReworkTime" data-toggle="tooltip" data-placement="top"
                  title="Enter in minutes">
                  <label class="control-label">Duration (min)</label>
                  <input class="form-control col-sm-8 m-auto"
                     id="inpReworkTime_emergency_line"
                     type="number" disabled>
               </div>
            </div>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="forceModalCleanup()">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="insertOrderNote()">Save</button>
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

        window.myEditor = null; // Declare the global editor variable
        ClassicEditor
            .create(document.querySelector('#textAreaNewNote'), {
                plugins: [Essentials, Paragraph, Bold, Italic, Font, Strikethrough, List],
                toolbar: [
                    'undo', 'redo', '|', 'bold', 'italic', 'strikethrough', '|',
                    'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor', '|',
                    'bulletedList', 'numberedList' // Add the list buttons to the toolbar
                ]
            })
            .then(editorInstance => {
                window.myEditor = editorInstance; // Assign the editorInstance to the global editor variable
            })
            .catch(error => {
                console.error(error);
            });
</script>