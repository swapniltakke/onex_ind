<link href="/dpm/dtoconfigurator/assets/css/projects/detail/tabs/nc-list.css" rel="stylesheet" type="text/css"/>

<div id="openNcCheckMsg" class="ui compact message"></div>

<div id="projectNcListContainer" class="ui container-fluid">
    <div class="ui active inline indeterminate text loader" style="margin-top:10%;display:none;"><b>Loading NC List</b></div>

    <!-- NC List Table -->
    <div id="projectNcListTableContainer" class="sixteen wide column">
        <h3 class="ui header" style="margin-bottom: -2.5rem;margin-top: 0.5rem;">
            <i class="wpforms icon"></i>
            <div class="content">
                Mechanical Group NC's
                <!-- <div class="sub header">The form below allows you to add material lists into TK Forms for use on the Projects page</div>-->
            </div>
        </h3>
        <table id="projectNcListTable" class="ui celled table" style="width:100%">
            <thead>
                <tr>
                    <th>Project Number</th>
                    <th>DTO Number</th>
                    <th>Panel Number</th>
                    <th>NC Number</th>
                    <th>Status</th>
                    <th>Description</th>
                    <th>NC Details</th>
                    <th>NC Date</th>
                    <th>Origin</th>
                </tr>
            </thead>
            <tbody>
                <!-- Data will be populated here by JavaScript -->
            </tbody>
        </table>
    </div>
</div>

<script src="/dpm/dtoconfigurator/assets/js/projects/detail/tabs/nc-list.js?<?=uniqid()?>"></script>
