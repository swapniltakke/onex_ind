<!-- Mainly scripts -->
<script src="/js/jquery.min.js"></script>

<script src="/js/semantic.min.js"></script>
<script src="/shared/inspia_gh_assets/js/popper.min.js"></script>
<script src="/shared/inspia_gh_assets/js/bootstrap.js"></script>
<script src="/shared/inspia_gh_assets/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="/shared/inspia_gh_assets/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

<!-- Custom and plugin javascript -->
<script src="/shared/inspia_gh_assets/js/inspinia.js"></script>
<script src="/shared/inspia_gh_assets/js/plugins/pace/pace.min.js"></script>

<!-- DataTable -->
<script src="/shared/inspia_gh_assets/js/plugins/dataTables/datatables.min.js"></script>
<script src="/shared/inspia_gh_assets/js/plugins/dataTables/dataTables.bootstrap4.min.js"></script>

<!-- Toastr script -->
<script src="/shared/inspia_gh_assets/js/plugins/toastr/toastr.min.js"></script>

<!-- Switchery -->
<script src="/shared/inspia_gh_assets/js/plugins/switchery/switchery.js"></script>

<!-- d3 and c3 charts -->
<script src="/shared/inspia_gh_assets/js/plugins/d3/d3.min.js"></script>
<script src="/shared/inspia_gh_assets/js/plugins/c3/c3.min.js"></script>

<!-- Tinycon -->
<script src="/shared/inspia_gh_assets/js/plugins/tinycon/tinycon.min.js"></script>

<!-- DataTables Fixed Header & Columns -->
<script src="/shared/inspia_gh_assets/js/dataTables.fixedColumns.min.js"></script>
<script src="/shared/inspia_gh_assets/js/dataTables.fixedHeader.min.js"></script>
<script src="/shared/inspia_gh_assets/js/dataTables.bootstrap4.min.js"></script>
<script src="/shared/inspia_gh_assets/js/plugins/select2/select2.full.min.js"></script>
<script src="/shared/inspia_gh_assets/js/plugins/chosen/chosen.jquery.js"></script>
<script src="/shared/inspia_gh_assets/js/plugins/jasny/jasny-bootstrap.min.js"></script>
<!-- Sweet alert -->
<script src="/shared/inspia_gh_assets/js/plugins/sweetalert/sweetalert.min.js"></script>

<!-- Moment JS -->
<script src="/shared/inspia_gh_assets/js/moment.min.js"></script>
<script src="/shared/inspia_gh_assets/js/datetime-moment.js"></script>

<!-- Chosen -->
<script src="/shared/inspia_gh_assets/js/plugins/chosen/chosen.jquery.js"></script>

<!-- TouchSpin -->
<script src="/shared/inspia_gh_assets/js/plugins/touchspin/jquery.bootstrap-touchspin.min.js"></script>

<!-- jqGrid -->
<script src="/shared/inspia_gh_assets/js/plugins/jqGrid/i18n/grid.locale-en.js"></script>
<script src="/shared/inspia_gh_assets/js/plugins/jqGrid/jquery.jqGrid.min.js"></script>

<!-- ChartJS-->
<script src="/shared/inspia_gh_assets/js/plugins/chartJs/Chart.min.js"></script>

<!-- Date Range Picker -->
<script type="text/javascript" src="/shared/inspia_gh_assets/js/daterangepicker.js"></script>
<script src="/shared/shared.js"></script>
<script src="/js/flowbite_1_4_7_datepicker.js"></script>
<script src="/layout/js/html_templates.js?2"></script>

<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/language/translator.php"; ?>
<script type="text/javascript">
    const LINE = getUrlParameters()["line"] ?? '<?= $DEFAULT_LINE ?? "" ?>';
    const WEEK = getUrlParameters()["week"] ?? '<?= $WEEK ?? "" ?>';
    const PROJECT = getUrlParameters()["project"];

    window.onload = function () {
        $(".rocket-loader").addClass("display-none");
        $(".blur").addClass("display-none");
    };

    function setURL(line, week){
        const params = new URLSearchParams(location.search);

        params.set('line', line);
        params.set('week', week);

        window.history.replaceState({}, '', `${location.pathname}?${params.toString()}`);
    }

    function updateDate(year, week) {
        if (week === 53) {
            year = year + 1;
            week = 1;
        } else if (week <= 0) {
            year = year - 1;
            week = 52;
        }

        if (week <= 9)
            week = '0' + week;

        const weekValue = year + '-W' + week;
        $("input#planWeekSelection").val(weekValue).change();
    }

    async function getLines(){
        return $.ajax({
            url: `/layout/api/getlines.php`,
            type: "GET"
        }).catch((e) => {
            console.error(e);
        })
    }

    function appendLines(lines){
        let linesHtml = "";
        for(let [index, line] of Object.entries(lines)){
            line = encodeURIComponent(line);
            let isSelectedLine = (line === LINE) ? "active" : "";
            linesHtml += `
                <li>
                    <a class="nav-link ${isSelectedLine}" data-toggle="tab" data-line="${line}">
                        <span data-translate="${line}">${line}</span>
                    </a>
                </li>
            `;
        }
        lineTabs.innerHTML = linesHtml;

        document.querySelectorAll('a[data-toggle="tab"]').forEach(lineTab => {
            lineTab.addEventListener('click', function() {
                const lineNumber = this.getAttribute("data-line");
                if(getUrlParameters()["line"] === lineNumber)
                    return;

                const week = $('#planWeekSelection').val().replace('W', '').replace('-', '');

                appendPlanData(lineNumber, week);
            })
        })
    }

    async function getData(line, week){
        return $.ajax({
            type: "GET",
            url: "api/getdata.php",
            dataType: "json",
            data: {
                line: line,
                week: week
            }
        })
    }

    function showPlanLoader(){
        $('#weeklyData').removeClass('animated fadeInUp');
        document.getElementById('planLoader').setAttribute('style', 'display: block !important;');
    }

    function hidePlanLoader(){
        document.getElementById('planLoader').setAttribute('style', 'display: none !important;');
        $('#weeklyData').addClass('animated fadeInUp');
    }

    function removePlanContent(){
        document.getElementById('weeklyPlan').innerHTML = "";
    }

    async function appendPlanData(line, week) {
        setURL(line, week);
        removePlanContent();
        showPlanLoader();
        const planData = await getData(line, week);

        let reworkSection = new ReworkTemplate({line: line});
        for(const reworkData of planData["rework"])
            reworkSection.appendData({projectNo: reworkData["reworkData"], panelQuantity: reworkData["panelQuantity"], lot: reworkData["lot"]})

        if(planData["rework"].length === 0)
            reworkSection.appendNoRework();

        reworkSection.appendContentToPage();

        for(const [productionDay, productionDayData] of Object.entries(planData["plan"])){
            const ctorParameterObj = {
                "planDateDMY": productionDay,
                "serverDateDMY": productionDayData.serverDateDMY,
                "weekDay": productionDayData.weekDay,
                "quantity": productionDayData.dailyQuantity,
                "line": line
            }
            let planSection = new PlanTemplate(ctorParameterObj);

            if(productionDayData.dailyQuantity === 0){
                planSection.appendNoProduction();
                planSection.appendToPage();
                continue;
            }
            for(const productionData of productionDayData.productionPlan){
                const appendDataParameterObj = {
                    "projectNo": productionData.projectNo,
                    "projectName": productionData.projectName,
                    "lot": productionData.lot,
                    "panelType": productionData.panelType,
                    "quantity": productionData.quantity,
                    "productionLine": productionData.productionLine
                }
                planSection.appendPanelNumbers(productionData.panelNumbers);
                planSection.appendData(appendDataParameterObj)
                //planSection.appendResponsibles(productionData.responsibles);
            }
            planSection.appendToPage();
        }
        hidePlanLoader();
        $('a.tabsLine').removeClass('active show');
    }

    /*$('.ui.search')
        .search({
            apiSettings: {
                url: 'api/searchproject.php?project={query}'
            },
            fields: {
                results: 'items',
                title: 'text',
                url: 'html_url'
            },
            minCharacters: 3
        });*/

    $(document).on('change', '#planWeekSelection', function () {
        const weekNumber = this.value.replace('-W', '');
        const lineNumber = $('a[data-toggle="tab"].active').attr("data-line");
        appendPlanData(lineNumber, weekNumber);
    });

    $(document).on('click', '#prevCalendar', function () {
        let date = $('#planWeekSelection').val().replace('W', '').split('-');
        let year = +date[0];
        let week = +date[1];
        updateDate(year, week - 1);
    });

    $(document).on('click', '#nextCalendar', function () {
        let date = $('#planWeekSelection').val().replace('W', '').split('-');
        let year = +date[0];
        let week = +date[1];
        updateDate(year, week + 1);
    });

    $(document).ready(function () {
        (async () => {
            const lines = await getLines();
            if(!lines.includes(LINE))
                throw new Error(`Given line '${LINE}' parameter does not belong to production plan`);
            appendLines(lines);
            await appendPlanData(LINE, WEEK);
            const translator = new Translator('layout', '#languageSelection', ['en'], 'en')
        })();
    });
</script>