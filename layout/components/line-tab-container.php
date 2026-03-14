<div role="tabpanel" id="tab-1" class="tab-pane active show">
    <div class="panel-body" style="min-height: 85vh;">
        <div class="ui grid center aligned wideColumn">
            <div class="sixteen wide column wideColumn">
                <button id="prevCalendar" type=""  class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2  rounded-lg justify-self-start w-24">
                    <svg class="h-5 w-5 inline-flex" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                              d="M10 18a8 8 0 100-16 8 8 0 000 16zm.707-10.293a1 1 0 00-1.414-1.414l-3 3a1 1 0 000 1.414l3 3a1 1 0 001.414-1.414L9.414 11H13a1 1 0 100-2H9.414l1.293-1.293z"
                              clip-rule="evenodd"/>
                    </svg>
                    <span data-translate="prev">Previous</span>
                </button>
                <input type="week" name="planWeekSelection" required id="planWeekSelection"
                       class="border-solid border border-indigo-600 p-2  rounded-lg justify-self-center w-32"
                       style="width: 8.5rem" value="<?= $CALENDER_WEEK ?>"/>
                <button id="nextCalendar" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 rounded-lg justify-self-end w-24">
                    <span data-translate="next">Next</span>
                    <svg class="h-5 w-5 inline-flex" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                              d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 1.414L10.586 9H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z"
                              clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
        </div>
        <div class="ui grid center aligned wideColumn">
            <div class="three wide column wideColumn">
            </div>
            <div class="ten wide column wideColumn">
                <div class="ui search" style="text-align: center;">
                    <div class="ui left icon input" style="width:400px">
                        <input class="prompt" type="text" placeholder="Search Project"
                               data-placeholder-translate="projectSearch" autofocus="" autocomplete="off" disabled>
                        <i class="search icon"></i>
                    </div>

                    <div class="results" style="width:45em;position:static;text-align: center;margin:auto;"></div>
                </div>
            </div>
            <div class="three wide column wideColumn">
                <button id="btnProductionLineNameCount" disabled="" type="button"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4  rounded-lg float-right mx-1"
                        style="display:none">
                    <span id="productionLineNameCount"></span>
                </button>
            </div>
        </div>

        <div class="row m-1"> <!--id="weeklyData"-->
            <div id="weeklyData" class="row m-1 animated fadeInUp" style="width: 100%;">
                <div class="d-block w-full" id="planLoader">
                    <h4 class="text-center">Loading...</h4>
                    <div class="spiner-example p-0">
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
                    </div>
                </div>
                <div id="weeklyPlan" style="display: flex; width: 100%;">

                </div>
            </div>
        </div>
    </div>
</div>