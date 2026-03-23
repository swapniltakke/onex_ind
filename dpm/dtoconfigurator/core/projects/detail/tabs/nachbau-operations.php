<link href="/dpm/dtoconfigurator/assets/css/projects/detail/tabs/nachbau-operations.css" rel="stylesheet" type="text/css"/>

<div class="ui active inline indeterminate text loader" style="display:none;!important;"><b>Loading Nachbau Operations</b></div>

<div id="nachbauOperationsContainer" class="ui container-fluid">
    <h3 class="ui header" style="margin-top:1rem; margin-bottom: 3rem;">
        <i class="cogs icon"></i>
        <div class="content">
            Nachbau Operations
            <div class="sub header">This section allows you to compare nachbaus or perform transfer operations between nachbau files.</div>
        </div>
    </h3>

    <div class="ui grid centered">
        <div class="four wide column">
            <label for="currentNachbau">Current Nachbau</label>
            <select id="currentNachbau" name="currentNachbau" class="ui fluid selection search dropdown" disabled>
                <option value=""></option>
            </select>
        </div>
        <div class="four wide column">
            <label for="allNachbaus">All Nachbaus</label>
            <select id="allNachbaus" name="allNachbaus" class="ui fluid selection search dropdown">
                <option value="">Select</option>
            </select>
            <div id="allNachbausErrorMsg" class="ui pointing red basic label hidden">Please select a different Nachbau file, not the current one!</div>
        </div>
    </div>
    <div class="ui secondary placeholder segment">
        <div class="ui two column stackable center aligned grid">
            <div class="ui vertical divider">Or</div>
            <div class="middle aligned row">
                <div class="column" style="padding:1rem;">
                    <div class="ui icon header" style="color:darkblue;">
                        <i class="balance scale icon"></i>
                        Compare Nachbaus
                    </div><br>
                    <div id="btnCompareNachbaus" class="ui linkedin button" style="background-color:darkblue;">
                        Compare
                    </div>
                </div>
                <div class="column" style="padding:1rem;">
                    <div class="ui icon header" style="color:brown;">
                        <i class="exchange icon"></i>
                        Transfer Nachbaus
                    </div><br>
                    <div id="btnCheckNachbauDifferences" class="ui linkedin button" style="background-color:brown;">
                        Transfer
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--    nachbau-operations.php-->
    <div id="nachbauDifferencesModal" class="ui modal">
        <div class="header" style="text-align:center;">Nachbau Transfer Confirmation</div>
        <div class="content">
            <!-- Top Warning Message -->
            <div class="ui icon message negative">
                <i class="exclamation circle icon"></i>
                <div class="content">
                    <div class="header" style="margin-bottom:0.5rem;">
                        Important Note
                    </div>
                    If there is any work in the nachbau file <span class="transferToNachbauVal" style="font-weight:bold;"></span>, it will be permanently deleted.
                </div>
            </div>
            <div class="ui icon message warning">
                <i class="exclamation circle icon"></i>
                <div class="content">
                    <div class="header" style="margin-bottom:0.5rem;">
                        Nachbau Errors
                    </div>
                    Please note that <b>Nachbau Error</b> works <b>will not be transferred</b> to target nachbau <span class="transferToNachbauVal" style="font-weight:bold;"></span>.
                </div>
            </div>


            <!-- Visual Transfer Flow -->
            <div style="margin: 2rem 0; padding: 1.5rem; background: #f8f9fa; border-radius: 8px;">
                <h3 style="text-align:center;color: #2185d0;">
                    Transfer Direction
                </h3>

                <div style="display: flex; align-items: center; justify-content: center; gap: 2rem;">
                    <!-- Source File -->
                    <div style="text-align: center;">
                        <div style="font-size: 0.9em; color: #666; margin-bottom: 0.5rem; font-weight: 600;">
                            FROM
                        </div>
                        <div style="background: #e3f2fd; padding: 1rem 1.5rem; border-radius: 8px; border: 2px solid #2196f3; min-width: 200px;">
                            <i class="file alternate outline icon" style="color: #2196f3; font-size: 1.5em;"></i>
                            <div style="font-weight: bold; font-size: 1.1em; margin-top: 0.5rem; color: #1976d2;" class="currentNachbauVal"></div>
                            <div style="font-weight: 600;font-size: 0.9em;margin-top: 0.2rem;" class="currentNachbauTime"></div>
                        </div>
                    </div>

                    <!-- Arrow -->
                    <div style="font-size: 2.5em; color: #2185d0;margin-top:1.3rem;">
                        <i class="arrow right icon"></i>
                    </div>

                    <!-- Destination File -->
                    <div style="text-align: center;">
                        <div style="font-size: 0.9em; color: #666; margin-bottom: 0.5rem; font-weight: 600;">
                            TO
                        </div>
                        <div style="background: #e8f5e9; padding: 1rem 1.5rem; border-radius: 8px; border: 2px solid #4caf50; min-width: 200px;">
                            <i class="file alternate icon" style="color: #4caf50; font-size: 1.5em;"></i>
                            <div style="font-weight: bold; font-size: 1.1em; margin-top: 0.5rem; color: #388e3c;" class="transferToNachbauVal"></div>
                            <div style="font-weight: 600;font-size: 0.9em;margin-top: 0.2rem;" class="transferToNachbauTime"></div>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Split Content Grid -->
            <div id="nachbauDifferencesGrid" class="ui two column grid" style="margin-top: 2rem;">
                <!-- Left Column: DTOs that won't exist -->
                <div class="eight wide column" id="dtosNotExistInTransferToNachbau">
                    <h4 class="ui dividing header" style="text-align:center;display: flex;justify-content: center;">
                        <i class="minus circle icon red"></i>
                        DTO Numbers Not in&nbsp;<span class="transferToNachbauVal"></span>
                    </h4>
                    <div class="ui list"></div>
                </div>

                <!-- Right Column: New DTOs -->
                <div class="eight wide column" id="newDtosInTransferToNachbau">
                    <h4 class="ui dividing header" style="text-align:center;display: flex;justify-content: center;">
                        <i class="plus circle icon green"></i>
                        New DTO Numbers in&nbsp;<span class="transferToNachbauVal"></span>
                    </h4>
                    <div class="ui list"></div>
                </div>
            </div>
        </div>
        <div class="actions" style="display:flex;justify-content:space-between;">
            <div class="ui cancel button">Cancel</div>
            <div id="btnConfirmNachbauTransfer" class="ui positive approve button">Confirm Transfer</div>
        </div>
    </div>

    <div id="transferSkippedModal" class="ui fullscreen modal">
        <div class="header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1rem;">
            <i class="warning sign icon"></i>
            Transfer Summary - Skipped Items
        </div>
        <div class="content" style="padding: 0; display: flex; flex-direction: column; height: calc(100vh - 120px);">
            <div class="ui container-fluid" style="width: 98%; margin: 0 auto; padding: 1rem 0; flex-shrink: 0;">
                <!-- Summary Cards -->
                <div id="skippedSummaryCards" class="ui five stackable cards" style="margin-bottom: 1rem;"></div>

                <!-- Transfer Info -->
                <div class="ui segment" style="background: #f8f9fa; margin-bottom: 1rem; padding: 0;">
                    <div class="ui two column grid" style="margin: 0;">
                        <div class="column">
                            <h4 style="margin: 0;"><i class="arrow right icon"></i>From: <span id="skippedFromNachbau" style="color: #e74c3c;"></span></h4>
                        </div>
                        <div class="column" style="text-align:right;">
                            <h4 style="margin: 0;"><i class="arrow right icon"></i>Target Nachbau: <span id="skippedToNachbau" style="color: #27ae60;"></span></h4>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="ui top attached tabular menu" id="skippedTabMenu" style="margin-bottom: 0;">
                    <!-- Tabs will be generated dynamically -->
                </div>
            </div>

            <!-- Tab Contents - Takes remaining space -->
            <div class="ui bottom attached segment" style="flex: 1; overflow-y: auto; margin: 0; border-radius: 0; padding: 1rem; min-height: 0;">
                <div id="skippedTabContents">
                    <!-- Tab contents will be generated dynamically -->
                </div>
            </div>
        </div>
        <div class="actions" style="background: #f8f9fa; padding: 0.75rem 1rem; margin: 0; position: sticky; bottom: 0; border-top: 1px solid #ddd;display:flex; justify-content:space-between;">
            <button class="ui button" onclick="$('#transferSkippedModal').modal('hide');">
                <i class="close icon"></i> Close
            </button>
            <button id="nachbauTransferCompleteBtn" class="ui green button" onclick="completeTransferAndClose();">
                <i class="check icon"></i> I Understand
            </button>
        </div>
    </div>
</div>

<script src="/dpm/dtoconfigurator/assets/js/projects/detail/tabs/nachbau-operations.js?<?=uniqid()?>"></script>
