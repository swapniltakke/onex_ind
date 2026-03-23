<link href="/dpm/dtoconfigurator/assets/css/projects/detail/tabs/checklist.css" rel="stylesheet" type="text/css"/>

<div class="ui active inline indeterminate text loader"><b>Loading Checklist</b></div>

<div id="checklistTabContainer" class="ui container-fluid" style="display:none!important;">
    <h3 class="ui header" style="margin-top:1rem; margin-bottom: 1rem;">
        <i class="cogs icon"></i>
        <div class="content">
            Checklist
            <div class="sub header">View all checklist items and mark all items to publish project.</div>
        </div>
    </h3>

    <div id="checklistTabContent" class="ui container-fluid">
        <!-- Progress Overview -->
        <div class="ui segment">
            <div class="ui statistic">
                <div class="value">
                    <span id="completedCount">0</span> / <span id="totalCount">0</span>
                </div>
                <div class="label">
                    Completed Items
                </div>
            </div>
            <div class="ui indicating progress" id="checklistProgress">
                <div class="bar"></div>
                <div class="label">Progress</div>
            </div>
        </div>

        <!-- Checklist Items Table -->
        <div id="projectChecklistTableContainer" class="ui segment">
            <table id="projectChecklistTable" class="ui celled table striped stackable compact padded hover">
                <thead>
                <tr>
                    <th style="width: 50px;">Status</th>
                    <th>Checklist Detail</th>
                    <th style="width: 150px;">Category</th>
                    <th style="width: 100px;">Image</th>
                    <th style="width: 150px;">Completed Date</th>
                    <th style="width: 80px;">Action</th>
                </tr>
                </thead>
                <tbody>
                <!-- Will be populated by DataTable -->
                </tbody>
            </table>
        </div>

    </div>
</div>

<script src="/dpm/dtoconfigurator/assets/js/projects/detail/tabs/checklist.js?<?=uniqid()?>"></script>
