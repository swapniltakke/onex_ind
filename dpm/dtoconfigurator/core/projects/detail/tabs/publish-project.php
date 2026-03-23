<link href="/dpm/dtoconfigurator/assets/css/projects/detail/tabs/publish-project.css" rel="stylesheet" type="text/css"/>

<div id="publishProjectContainer" class="ui container-fluid">
    <div id="publishProjectInfo">
        <div class="ui icon message warning">
            <i class="info circle icon"></i>
            <div class="content">
                <div class="header">
                    Important: Project Publishing Process
                </div>
                <p style="line-height:1.6rem;">Once the project is published, it will be sent to the managers for review and approval. <b>During this review process, no changes can be made to the project.</b><br>
                If the managers approve, the project will be published, and no further modifications will be allowed. However, if the publishing is rejected by the managers, the project will be returned for necessary revisions.</p>
            </div>
        </div>
        <button id="seeOverviewOfProjectButton" class="ui labeled icon brown button disabled">
            <i class="eye large icon"></i>
            See Overview of Project Work
        </button>
    </div>

    <div id="publishProjectSummary" style="display:none;">
        <div id="projectInfoOverview" class="ui attached" style="margin-top:1rem;">
            <div class="ui styled fluid accordion">
                <div class="title active">
                    <i class="dropdown icon"></i>
                    Project Information Overview
                </div>
                <div class="content active">
                    <div id="projectInfoOverview" class="ui attached" style="margin-top:1rem;">
                        <h3 class="ui horizontal divider header">
                            <i class="cogs icon tiny"></i>
                            Project Information Overview
                        </h3>

                        <div style="display: flex; align-items: center; justify-content: center; gap: 1rem; width: 100%;">
                            <div id="projectInfoOverviewInfoMsg" class="ui warning message compact" style="width: 85%;">
                                <i class="lightbulb outline large icon"></i>
                                <b>Important:&nbsp;</b> Please ensure all project details are complete and accurate before proceeding.
                            </div>
                        </div>

                        <div style="margin-top:1.2rem;text-align: center;">
                            <div id="totalChangeDiv" class="ui teal big label">
                                <span>Total Change Rows : </span> <span id="totalChangeCount"></span>
                            </div>
                        </div>
                        <table id="projectInfoOverviewDataTable" class="ui celled table" style="width:100%">
                            <thead>
                            <tr>
                                <th>Project No</th>
                                <th>Project Name</th>
                                <th>Nachbau No</th>
                                <th>Nachbau Date</th>
                                <th>Product</th>
                                <th>Panel Qty</th>
                                <th>Voltage</th>
                                <th>Short Circuit</th>
                                <th>Current</th>
                                <th>EE</th>
                                <th>OM</th>
                                <th>ME</th>
                                <th>Worked By</th>
                            </tr>
                            </thead>
                            <tbody>
                            <!-- Data will be populated here by JavaScript -->
                            </tbody>
                        </table>
                        <button id="publishProjectButton" class="ui labeled icon green big button" style="display: flex;margin: 0 auto;">
                            <i class="rocket icon"></i>
                            Publish Project
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script src="/dpm/dtoconfigurator/assets/js/projects/detail/tabs/publish-project.js?<?=uniqid()?>"></script>
