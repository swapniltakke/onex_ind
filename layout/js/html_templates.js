class PlanTemplate{
    template = null;
    panelNumberClass = "m-1 p-2 text-sm font-medium text-gray-900 bg-gray-300 focus:z-10 focus:ring-2 focus:ring-gray-500 focus:bg-gray-900 focus:text-white dark:border-white dark:text-white dark:hover:text-white dark:hover:bg-gray-700 dark:focus:bg-gray-700";
    panelNumbersTemplate = `
        <div class="border-t border-gray-300 bg-gray-100 text-center panelNumbersGroup">
            <div class="inline-grid grid-cols-3 py-2 panelNumbers" role="group">
                
            </div>
        </div>
    `;
    responsiblesClass = "inline-flex overflow-hidden relative justify-center items-center w-10 h-10 bg-gray-100 rounded-full dark:bg-gray-600";
    responsiblesTemplate = `
        <div class="flex space-x-4 responsiblesGroup">
            <span class="inline-flex responsibles">
            
            </span>
        </div>
    `;

    constructor(params){
        const {
            planDateDMY,
            serverDateDMY,
            weekDay,
            quantity,
            line
        } = {...params};
        const isTodayClass = (planDateDMY === serverDateDMY) ? "panel-danger": "panel-primary";

        const breakerLines = ["Line-SION-M40-31plus", "Line-SION-M25", "Line-SION-M36"];
        //console.log(line)
        const panelOrBreaker = (breakerLines.includes(line)) ? "Breaker(s)" : "Panel(s)";

        this.template = `
            <div class="col panels-col dailyPlanDiv">
                <div class="panel ${isTodayClass}">
                    <div class="panel-heading text-center" style="font-weight: bold;font-size: 20px;">
                        ${planDateDMY} <br>
                        <span data-translate="${weekDay.toLowerCase()}">${weekDay}</span>
                        <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded text-blue-600 bg-blue-200 uppercase last:mr-0 mr-1">
                        ${quantity} <span data-translate="panel">${panelOrBreaker}</span>
                        </span>
                    </div>
                    
                </div>
            </div>
        `;
    }

    appendData(params){
        const {
            projectNo,
            projectName,
            lot,
            panelType,
            quantity,
            productionLine
        } = {...params};
        const breakerLines = ["Line-SION-M40-31plus", "Line-SION-M25", "Line-SION-M36"];
        const panelOrBreaker = (breakerLines.includes(productionLine)) ? "Breaker(s)" : "Panel(s)";

        let doc = (new DOMParser()).parseFromString(this.template, 'text/html');
        let dailyPlanDiv = doc.querySelector('.dailyPlanDiv');
        let panel = dailyPlanDiv.querySelector('.panel');
        panel.innerHTML += `
            <div class="max-w-6xl mx-auto p-1 dailyPlanDiv">
                <div class="flex items-center justify-center">
                    <div class="w-full">
                        <div class="bg-white shadow-xl rounded-lg overflow-hidden">
                            <!--<div class="flex space-x-2 px-4 pt-2">
                                <span class=" font-bold bg-blue-600 text-white rounded-full px-2 fs-2">ST</span>
                            </div>-->
                            <div class="px-4 py-4">
                                <p class="uppercase tracking-wide text-sm font-bold text-gray-700">${projectName}</p>
                                <p class="text-3xl text-gray-900">${projectNo}</p>
                                <p class="text-gray-700">
                                    <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded text-teal-600 bg-teal-200 uppercase last:mr-0 mr-1">
                                      ${panelType}
                                    </span>
                                    <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded text-blue-600 bg-blue-200 uppercase last:mr-0 mr-1">
                                      ${quantity} <span data-translate="panel">${panelOrBreaker}</span>
                                    </span>
                                    <!-- <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded text-purple-600 bg-purple-200 uppercase last:mr-0 mr-1">
                                      Lot-${lot}
                                    </span> -->
                                </p>
                                <div class="flex space-x-4">
                                    <span class="inline-flex">
                                        ${this.responsiblesTemplate}
                                    </span>
                                </div>
                            </div>
                            <div class="flex border-t border-gray-300 text-gray-700 justify-center">
                                <a style="margin: 0.2rem; width: 50%;" target="_blank" href="#" class="text-center bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold w-full rounded  items-center">
                                    <span data-translate="material-search">Material Search</span>
                                    <svg class="h-5 w-5 m-auto" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                                    </svg>
                                </a>
                            </div>
                            ${this.panelNumbersTemplate}
                        </div>
                    </div>
                </div>
            </div>
        `;
        //console.log(dailyPlanDiv.outerHTML)
        this.template = dailyPlanDiv.outerHTML;
    }

    appendPanelNumbers(panelNumbers){
        let panelNumbersHTML = ``;
        for(const panelNumber of panelNumbers)
            panelNumbersHTML += `<a type="button" class="${this.panelNumberClass}">${panelNumber}</a>`;

        let doc = (new DOMParser()).parseFromString(this.panelNumbersTemplate, 'text/html');
        let panelNumbersGroup = doc.querySelector('.panelNumbersGroup');
        let panelNumbersDiv = panelNumbersGroup.querySelector('.panelNumbers');
        panelNumbersDiv.innerHTML = panelNumbersHTML;

        this.panelNumbersTemplate = panelNumbersGroup.outerHTML;
    }

    appendResponsibles(responsiblesObj){
        const {
            om,
            ee,
            me
        } = {...responsiblesObj};

        let doc = (new DOMParser()).parseFromString(this.responsiblesTemplate, 'text/html');
        let responsiblesGroup = doc.querySelector('.responsiblesGroup');
        let responsibles = responsiblesGroup.querySelector('.responsibles');
        responsibles.innerHTML = `
            <a data-toggle="tooltip" data-placement="top" title="${om['fullname'] ?? ''}" src="" alt="${om['fullname'] ?? ''}" target="_blank" href="https://teams.microsoft.com/l/chat/0/0?users=${om['email'] ?? ''}" class="${this.responsiblesClass}">
                <span class="font-medium text-gray-600 dark:text-gray-300">HI</span>
            </a>
            <a data-toggle="tooltip" data-placement="top" title="${ee['fullname'] ?? ''}" src="" alt="${ee['fullname'] ?? ''}" target="_blank" href="https://teams.microsoft.com/l/chat/0/0?users=${ee['email'] ?? ''}" class="${this.responsiblesClass}">
                <span class="font-medium text-gray-600 dark:text-gray-300">EE</span>
            </a>
            <a data-toggle="tooltip" data-placement="top" title="${me['fullname'] ?? ''}" src="" alt="${me['fullname'] ?? ''}" target="_blank" href="https://teams.microsoft.com/l/chat/0/0?users=${me['email'] ?? ''}" class="${this.responsiblesClass}">
                <span class="font-medium text-gray-600 dark:text-gray-300">BY</span>
            </a>
        `;
        this.responsiblesTemplate = responsibles.outerHTML;
    }

    appendNoProduction(){
        let doc = (new DOMParser()).parseFromString(this.template, 'text/html');
        let dailyPlanDiv = doc.querySelector('.dailyPlanDiv');
        let panel = dailyPlanDiv.querySelector('.panel');
        panel.innerHTML += `
            <div style="margin: 1em; text-align: center">
                <div class="alert alert-danger">
                    <div class="alert-link">
                        <span data-translate="no-production-today">No Production Today</span>
                    </div>
                </div>
            </div>
        `;
        this.template = dailyPlanDiv.outerHTML;
    }

    appendToPage(){
        document.getElementById('weeklyPlan').innerHTML += this.template;
        //console.log(document.getElementById('weeklyPlan').innerHTML)
    }
}

class ReworkTemplate{
    template = null;

    constructor(params){
        const {
            line,
            totalRework = 0
        } = {...params};

        const breakerLines = ["Line-SION-M40-31plus", "Line-SION-M25", "Line-SION-M36"];
        //console.log(line)
        const panelOrBreaker = (breakerLines.includes(line)) ? "Breaker(s)" : "Panel(s)";

        this.template = `
            <div id="reworks" class="col panels-col animated slideInLeft">
                <div class="panel panel-warning" style="height: min-content;">
                    <div class="panel-heading text-center" style="font-weight: bold; font-size: 20px;">
                        <span data-translate="${line}">${line}</span> <br>
                        <span data-translate="rework">Rework</span>
                        <h3>
                            <span data-translate="total">Total</span> ${totalRework}
                            <span data-translate="panel">${panelOrBreaker}</span>
                        </h3>
                    </div>
                    <div class="panel-body" style="max-height: 600px; overflow: auto; ">
                        <div id="reworkData" class="ibox">
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    appendData(projectNo, panelQuantity, lot){
        let doc = (new DOMParser()).parseFromString(this.template, 'text/html');
        let reworkPanelBody = doc.querySelector('#reworks');
        let reworkData = reworkPanelBody.querySelector('#reworkData');
        reworkData.innerHTML += `
            <div class="text-center ibox-title " style="padding-right: 0 !important; padding-left: 0 !important;">
                <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded text-black-600 bg-green-200 uppercase last:mr-0 mr-1">
                   ${projectNo}
                </span>
                <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded text-purple-600 bg-purple-200 uppercase last:mr-0 mr-1">
                  Lot-${lot}
                </span>
                <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded text-blue-600 bg-blue-200 uppercase last:mr-0 mr-1">
                  ${panelQuantity} Pano
                </span>
            </div>
        `;

        this.template = reworkData.outerHTML;
    }

    appendNoRework(){
        let doc = (new DOMParser()).parseFromString(this.template, 'text/html');
        doc.querySelector('#reworkData').innerHTML += `
            <div style="margin: 1em; text-align: center">
                <div class="alert alert-danger">
                    <div class="alert-link">
                        <span data-translate="no-rework-found">No Rework Found</span>
                    </div>
                </div>
            </div>
        `;
        this.template = doc.querySelector('#reworks').innerHTML;
    }

    appendContentToPage(){
        document.getElementById('weeklyPlan').innerHTML += this.template;
    }
}
