<div class="modal inmodal" id="insertModal" role="dialog" aria-hidden="true" tabindex="-1">
    <input type="hidden" value="" name="orderNoForInsertModal" id="hdnOrderNoForInsertModal"/>
    <input type="hidden" value="" name="Id" id="hdnId"/>
    <input type="hidden" value="" name="ParentId" id="hdnParentId"/>
    <input type="hidden" value="" id="hdnPanelNumberForInsertModal"/>
    <div class="modal-dialog modal-lg">
        <div class="modal-content animated bounceInRight">
            <div id="insert_modal_content" class="ibox-content">
                <div class="sk-spinner sk-spinner-cube-grid">
                    <div class="sk-cube"></div>
                    <div class="sk-cube"></div>
                    <div class="sk-cube"></div>
                    <div class="sk-cube"></div>
                    <div class="sk-cube"></div>
                    <div class="sk-cube"></div>
                    <div class="sk-cube"></div>
                    <div class="sk-cube"></div>
                    <div class="sk-cube"></div>
                </div>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span
                                aria-hidden="true">&times;</span><span
                                class="sr-only">Close</span></button>
                    <div class="d-flex justify-content-center">
                        <h4 class="modal-title mr-1" id="titleProjectNo"></h4> <h4 class="modal-title"
                                                                                   data-translate="questions-notify-project"></h4>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <div class="form-group" id="divModalGroup">
                            <div class="input-group m-b">
                        <span class="input-group-prepend">
                                            <button type="button" class="btn btn-primary"><i
                                                        class="fa fa-user-circle"></i></button> </span>
                                <input type="text" class="form-control" id="inpOrderManager" readonly/>
                            </div>
                        </div>
                        <div class="custom-file">
                            <input name="orderIssueAttachments[]" multiple id="orderIssueAttachments" type="file"
                                   class="custom-file-input">
                            <label for="orderIssueAttachments" class="custom-file-label" data-translate="choose-file">Select F,le</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Mail To</label>
                        <select id="mailSelect" class="form-control form-select" multiple="multiple"></select>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Mail CC</label>
                        <select id="externalMailSelect" class="form-control form-select" multiple="multiple"></select>
                    </div>
                    <div class="form-group" id="divIssueCodeSelect">
                        <label class="control-label"><span data-translate="error-code">Error code</span> (<span
                                    data-translate="fill-by-om"></span>)</label>
                        <select id="issueCodeSelect" <?php if (in_array((int) SharedManager::getUser()["GroupID"], [5, 29])) {
                            echo("disabled");
                        } ?>
                                class="form-control form-select">
                        </select>
                    </div>
                    <div class="form-group" style="display:flex;" id="divReferenceInp">
                        <div class="input-group m-b">
                        <span style="
    display: flex;
    justify-content: center;
">
                          <input class="form-control" id="chechbox_hasLineStopEffect" type="checkbox" style="
    width: 50px;
    height: 25px;
    margin: auto;
">
         </span>
                            <label class="form-control text-danger" data-translate="line-stop-effect">Affects line stop?</label>
                        </div>
                        <div class="input-group m-b" style="margin-left: 5px;">
                        <span class="input-group-prepend">
                                            <button type="button" class="btn btn-success"><i
                                                        class="fa fa-bolt"></i></button> </span>
                            <input id="referenceCode" placeholder="Reference" data-placeholder="reference"
                                   class="form-control">
                        </div>

                    </div>
                    <div class="form-group has-success">
                        <textarea rows="5" id="note" data-placeholder-translate="fill-description"
                                  placeholder="Enter description" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-dismiss="modal" data-translate="cancel">Cancel
                    </button>
                    <button type="button" class="btn btn-primary" onclick="saveOrderIssue()"
                            data-translate="save-and-send-mail">Save and send email
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>