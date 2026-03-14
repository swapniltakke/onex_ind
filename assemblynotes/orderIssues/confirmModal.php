<div class="modal inmodal" id="confirmModal" tabindex="-1" role="dialog" aria-hidden="true">
    <input type="hidden" value="" name="Id" id="hdnDeactiveId"/>
    <input type="hidden" value="" name="Id" id="hdnDeactiveOrderNo"/>
    <div class="modal-dialog">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                            class="sr-only">Close</span></button>
                <h4 class="modal-title">Project - Question deletion</h4>
            </div>
            <div class="modal-body" id="confirmModalBody">
                Are you sure to delete this order note?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="deactiveOrderIssue()">Yes, Delete</button>
            </div>
        </div>
    </div>
</div>