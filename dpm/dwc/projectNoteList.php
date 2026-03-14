<?php include_once $_SERVER["DOCUMENT_ROOT"] . "/checklogin.php"; ?>
<div class="ibox-title">
    <h5>Notes</h5>
    <div class="ibox-tools">
        <a class="collapse-link">
            <i class="fa fa-chevron-up"></i>
        </a>
        <a class="close-link">
            <i class="fa fa-times"></i>
        </a>
    </div>
</div>
<div class="ibox">
    <div class="ibox-content">
        <table class="table table-striped table-bordered table-hover dataTables-example"
               id="dtOrderNotes">
            <thead>
            <tr>
                <th>Status</th>
                <th>Panel No</th>
                <th>Main Category</th>
                <th>Sub Category</th>
                <th>Problem/Note/Missing</th>
                <th>Created on</th>
                <th>Created by</th>
                <th>Updated on</th>
                <th>Updated by</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>