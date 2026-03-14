<?php
SharedManager::checkAuthToModule(7);
SharedManager::saveLog("log_opt", "Main Page Access QR Code Generation");
?>

<html>
<head>
    <title>QR Code Generator for Panels</title>
    <meta name="viewport" content="width=device-width, initial-scale=0.85, maximum-scale=1, user-scalable=yes"/>
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <meta http-equiv="Content-Script-Type" content="text/jscript"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <meta charset="utf-8">

    <link href="/css/semantic.min.css" rel="stylesheet"/>
    <link rel="stylesheet" type="text/css" href="/css/select2.css">
    <link rel="stylesheet" type="text/css" href="/css/dataTables.semanticui.min.css">
    <link rel="stylesheet" type="text/css" href="/css/icon.min.css">
    <link rel="stylesheet" type="text/css" href="/css/responsive.semanticui.min.css">
    <link rel="stylesheet" type="text/css" href="/css/fixedHeader.semanticui.min.css">

    <link href="/plugins/datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet"/>

    <link href="/plugins/chartist/chartist.min.css" rel="stylesheet"/>
    <link href="/css/chat-page.css" rel="stylesheet"/>

    <link href="/plugins/pacejs/pace.css" rel="stylesheet"/>
    <link href="/css/main.css?13" rel="stylesheet"/>
    <link href="/css/calendar.min.css" rel="stylesheet" type="text/css"/>

    <script src="/js/jquery.min.js"></script>
    <script src="/js/semantic.min.js"></script>
    <script src="/js/jquery.dataTables.js"></script>
    <script src="/js/dataTables.semanticui.min.js"></script>
    <script src="/js/dataTables.buttons.min.js"></script>
    <script src="/js/buttons.flash.min.js"></script>
    <script src="/js/jszip.min.js"></script>
    <script src="/js/pdfmake.min.js"></script>
    <script src="/js/vfs_fonts.js"></script>
    <script src="/js/buttons.html5.min.js"></script>
    <script src="/js/buttons.print.min.js"></script>
    <script src="/js/buttons.colVis.min.js"></script>
    <script src="/js/tablesort.js"></script>
    <script src="/js/select2.full.js"></script>
    <script src="/js/Semantic-UI-Alert.js"></script>
    <script src="/js/calendar.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.1/build/qrcode.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</head>
<style>
    .buttonscss {
        text-align: center !important;
    }

    .align-right {
        text-align: right;
        max-width: 80px;
    }

    .align-left {
        text-align: left;
        max-width: 80px;
    }

    .align-center {
        text-align: center !important;
    }

    .red {
        background-color: #ee3131 !important;
    }

    .orange {
        background-color: #FFA500 !important;
    }

    .green {
        background-color: #21ba45 !important;
    }

    .open.button {
        cursor: pointer;
    }

    td.details-control {
        background: url('https://www.datatables.net/examples/resources/details_open.png') no-repeat center center;
        cursor: pointer;
    }

    tr.shown td.details-control {
        background: url('https://www.datatables.net/examples/resources/details_close.png') no-repeat center center;
    }

    .clicked {
        background-color: #41da41;
        font-weight: bolder;
    }

    .example_filter {
        float: center !important;
    }

    .ui[class*="very-compact"].table th {
        padding-left: 0.6em;
        padding-right: 0.6em;
    }

    .ui[class*="very-compact"].table td {
        padding: 0.6em 0.6em;
    }

    .ui.table thead th {
        text-align: center !important;
    }

    .ui.table tr[class*="inner-row"] {
        text-align: center !important;
    }

    .ui.table tr[class*="inner-row2"] {
        text-align: center !important;
    }

    i.hamburger.icon {
        margin-top: -5px;
    }
    
    .action-buttons {
        display: flex;
        gap: 10px;
        margin-top: 10px;
    }
    
    .action-buttons .button {
        flex: 1;
    }

    /* Loader Styles */
    .loader-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        justify-content: center;
        align-items: center;
    }

    .loader-overlay.active {
        display: flex;
    }

    .loader-container {
        background-color: white;
        padding: 40px;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        text-align: center;
    }

    .spinner {
        border: 4px solid #f3f3f3;
        border-top: 4px solid #3498db;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        animation: spin 1s linear infinite;
        margin: 0 auto 20px;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .loader-text {
        font-size: 18px;
        color: #333;
        font-weight: 500;
    }
</style>
<body>
    <div class="pusher">
        <div class="ui secondary segment">
            <a href="/opt/index.php">
                <h1 class="ui center aligned header" style="
                padding-top: 3px;
                margin-bottom: 1px;
                color:blue;
            ">
                    <a href="/index.php"><img src="/images/onex.png" width='70'
                                              style="margin-left:20px;margin-top: -30px;"></br>
                        <i class="qrcode alternate icon" style='color:blue;'></i>
                        Panel QR</a>
                </h1>
            </a>
        </div>
        <div class="ui container">
            <div class="ui inverted teal segment">
                <h4 class="ui horizontal divider header">
                    <i class="bar chart icon"></i>
                    Project Assembly Information
                </h4>
                <div class="left aligned field">
                    <label>
                        Project Number(*)
                    </label>
                    <div class="ui big left action fluid input">
                        <button class="ui big red labeled icon button">
                            <i class="line chart icon"></i>
                                Project:
                        </button>
                        <input name="orderno" autofocus placeholder="" pattern="[0-9]*" inputmode="numeric" maxLength=10>
                    </div>
                    <div id="orderInfo">
                        <div class="ui error big message" id="notFoundError" style="display: none">
                            <b>No MTool record is found for the project!</b>
                        </div>
                        <div id="orderInfoDetails" style="display: none">
                            <div class="ui big message">
                                <div class="ui large red label" id="projectInfoPanelType"></div>
                                <div class="ui large teal label" id="projectInfoProjectName"></div>
                                <div class="ui large blue label" id="projectInfoOM"></div>
                            </div>
                            <div id="resultTable">
                                <table class="ui compact striped celled unstackable table">
                                    <thead>
                                    <tr>
                                        <th>Panel Type</th>
                                        <th>Panel No</th>
                                        <th>Panel Name</th>
                                        <th>Typical Name</th>
                                    </tr>
                                    </thead>
                                    <tbody id="resultTableBody">

                                    </tbody>
                                </table>
                            </div>
                            <div id="result"></div>
                            <div class="action-buttons">
                                <button class="ui fluid blue big button" id="qrgenerate">
                                    <i class="qrcode icon"></i>QR Code Generate (PDF)
                                </button>
                                <button class="ui fluid green big button" id="excelgenerate">
                                    <i class="file excel icon"></i>Generate Excel
                                </button>
                            </div>
                        </div>
                    </div>
                    <div id="panelinfo"></div>
                    <div id="failuredescription"></div>
                </div>
                <div class="left aligned field">
                    <div id="origininfo"></div>
                    <div class="ui form">
                        <div id="errorinfo"></div>
                    </div>
                    <div id="failuredescription"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loader Overlay -->
    <div class="loader-overlay" id="loaderOverlay">
        <div class="loader-container">
            <div class="spinner"></div>
            <div class="loader-text" id="loaderText">Generating PDF...</div>
        </div>
    </div>
</body>
<script src="/js/2_29_3_moment.min.js"></script>
<script src="/js/1_3_8_FileSaver.js"></script>
<script src="/js/xlsx.full.min.js"></script>
<script type="text/javascript">
   function showOrderInfoDetails(){
        document.getElementById('orderInfoDetails').setAttribute('style', 'display: block');
    }

    function hideOrderInfoDetails(){
        document.getElementById('orderInfoDetails').setAttribute('style', 'display: none');
    }

    function clearResultTableBody(){
        document.getElementById('resultTableBody').innerHTML = "";
    }

    function showNotFoundError(){
        document.getElementById('notFoundError').setAttribute('style', 'display: block');
    }

    function hideNotFoundError(){
        document.getElementById('notFoundError').setAttribute('style', 'display: none');
    }

    function showLoader(message = 'Processing...') {
        document.getElementById('loaderText').innerText = message;
        document.getElementById('loaderOverlay').classList.add('active');
    }

    function hideLoader() {
        document.getElementById('loaderOverlay').classList.remove('active');
    }

    $(document).ready(function () {
        $('.pusher').css('overflow-y', 'inherit');

        $('input[name=orderno]').on('input', function () {
            const projectNo = $("input[name=orderno]").val();
            if(projectNo.length !== 10)
                return;

            clearResultTableBody();
            hideOrderInfoDetails();
            hideNotFoundError();

            (async () => {
                const qrData = await $.ajax({
                    url: '/opt/api/getqrdata.php',
                    type: 'GET',
                    data: {
                        project: projectNo
                    }
                }).catch(e => {
                    console.log(e)
                });

                if(qrData.length === 0){
                    hideOrderInfoDetails();
                    showNotFoundError();
                    return;
                }

                document.getElementById('projectInfoPanelType').innerText = qrData[0]["product"];
                document.getElementById('projectInfoProjectName').innerText = qrData[0]["projectName"];
                document.getElementById('projectInfoOM').innerText = qrData[0]["orderManager"];

                for(const row of qrData){
                    const {posNo, panelName, typicalName, projectName, orderManager, product} = {...row};

                    const tableRow = document.createElement('tr');
                    const panelTypeTd = document.createElement('td');
                    panelTypeTd.classList.add("center", "aligned");
                    panelTypeTd.setAttribute('data-label', "panelType");
                    panelTypeTd.innerText = product;
                    tableRow.appendChild(panelTypeTd);

                    const panelNoTd = document.createElement('td');
                    panelNoTd.classList.add("center", "aligned");
                    panelNoTd.setAttribute('data-label', "panelNo");
                    panelNoTd.innerText = posNo;
                    tableRow.appendChild(panelNoTd);

                    const panelNameTd = document.createElement('td');
                    panelNameTd.classList.add("center", "aligned");
                    panelNameTd.setAttribute('data-label', "panelName");
                    panelNameTd.innerText = panelName;
                    tableRow.appendChild(panelNameTd);

                    const typicalNameTd = document.createElement('td');
                    typicalNameTd.classList.add("center", "aligned");
                    typicalNameTd.setAttribute('data-label', "typicalName");
                    typicalNameTd.innerText = typicalName;
                    tableRow.appendChild(typicalNameTd);

                    document.getElementById('resultTableBody').appendChild(tableRow);
                }

                $('#qrgenerate').off('click').on('click', function (e) {
                    e.preventDefault();
                    const projectNo = $("input[name=orderno]").val();
                    generateQRCodePDF(projectNo, qrData);
                });
                
                $('#excelgenerate').off('click').on('click', function (e) {
                    e.preventDefault();
                    const projectNo = $("input[name=orderno]").val();
                    printExcel(projectNo, qrData);
                });

                showOrderInfoDetails();
            })();
        });
    });

    async function generateQRCodePDF(projectNo, qrData) {
        showLoader('Generating QR Code PDF...');
        $('#qrgenerate').addClass('disabled');
        
        try {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF({
                orientation: 'landscape',
                unit: 'mm',
                format: [74, 57]  // Zebra printer sticker dimensions
            });
            
            const margin = 3;
            const qrSize = 25;
            const cellWidth = 68;
            const cellHeight = 51;
            
            let pageCount = 0;
            
            for (let i = 0; i < qrData.length; i++) {
                const panel = qrData[i];
                
                if (!panel.panelName || panel.panelName.trim() === '') {
                    console.log(`Skipping QR code for index ${i}`);
                    continue;
                }
                
                // Add new page for each card (except first)
                if (pageCount > 0) {
                    doc.addPage([74, 57], 'landscape');
                }
                pageCount++;
                
                const xPos = margin;
                const yPos = margin;
                
                const qrContent = `${projectNo}*${panel.posNo}*${panel.product}*${panel.orderManager}*${panel.projectName}*${panel.typicalName}*${panel.panelName}`;
                
                const qrCodeDataURL = await new Promise(resolve => {
                    QRCode.toDataURL(qrContent, { 
                        errorCorrectionLevel: 'H',
                        margin: 1,
                        width: 400,
                        height: 400
                    }, function(err, url) {
                        resolve(url);
                    });
                });
                
                // Draw border
                doc.setDrawColor(0);
                doc.setLineWidth(0.3);
                doc.rect(xPos, yPos, cellWidth, cellHeight);
                
                // Add project number at top left
                doc.setFontSize(12);
                doc.setFont('helvetica', 'bold');
                doc.text(projectNo, xPos + 2, yPos + 5);

                // Add product name at top right
                doc.setFontSize(9);
                doc.setFont('helvetica', 'normal');
                doc.text(panel.product, xPos + cellWidth - 2, yPos + 5, { align: 'right' });

                // Add project name with text wrapping (moved down with extra spacing)
                doc.setFontSize(9);
                doc.setFont('helvetica', 'bold');
                const maxWidth = cellWidth - qrSize - 6;
                const projectNameLines = doc.splitTextToSize(panel.projectName, maxWidth);
                doc.text(projectNameLines, xPos + 2, yPos + 11);

                const projectNameHeight = projectNameLines.length * 3;

                // Add order manager (with extra spacing)
                // doc.setFontSize(9);
                // doc.setFont('helvetica', 'normal');
                // doc.text(panel.orderManager, xPos + 2, yPos + 10 + projectNameHeight + 5);
                
                // Add QR code on the right side
                const qrY = yPos + 12;
                const qrX = xPos + cellWidth - qrSize - 2;
                doc.addImage(qrCodeDataURL, 'PNG', qrX, qrY, qrSize, qrSize);
                
                // Add panel information at bottom with smaller font
                doc.setFontSize(12);

                const panelNoLabel = 'Panel No.:';
                const typicalNameLabel = 'Typical:';
                const panelNameLabel = 'Panel:';

                const extraSpace = doc.getTextWidth(' ');

                // Panel No
                doc.setFont('helvetica', 'normal');
                doc.text(panelNoLabel, xPos + 2, yPos + cellHeight - 14);
                doc.setFont('helvetica', 'bold');
                const panelNoWidth = doc.getTextWidth(panelNoLabel) + extraSpace;
                doc.text(panel.posNo, xPos + 2 + panelNoWidth, yPos + cellHeight - 14);

                // Typical Name
                doc.setFont('helvetica', 'normal');
                doc.text(typicalNameLabel, xPos + 2, yPos + cellHeight - 9);
                doc.setFont('helvetica', 'bold');
                const typicalNameWidth = doc.getTextWidth(typicalNameLabel) + extraSpace;
                doc.text(panel.typicalName, xPos + 2 + typicalNameWidth, yPos + cellHeight - 9);

                // Panel Name
                doc.setFont('helvetica', 'normal');
                doc.text(panelNameLabel, xPos + 2, yPos + cellHeight - 4);
                doc.setFont('helvetica', 'bold');
                const panelNameWidth = doc.getTextWidth(panelNameLabel) + extraSpace;
                const panelNameText = doc.splitTextToSize(panel.panelName, maxWidth - panelNameWidth);
                doc.text(panelNameText, xPos + 2 + panelNameWidth, yPos + cellHeight - 4);
            }
            
            if (pageCount === 0) {
                hideLoader();
                alert("No QR codes were generated. All panel names were blank.");
                $('#qrgenerate').removeClass('disabled');
                return;
            }
            
            doc.save(`${projectNo}-QR_Codes.pdf`);
            
            // Hide loader after a short delay to ensure download starts
            setTimeout(() => {
                hideLoader();
                $('#qrgenerate').removeClass('disabled');
            }, 500);
            
        } catch (error) {
            console.error("Error generating PDF:", error);
            hideLoader();
            $('#qrgenerate').removeClass('disabled');
            alert("Error generating PDF. Please try again.");
        }
    }

    // Excel generation function with filtering for blank panel names
    async function printExcel(projectNo, qrData) {
        showLoader('Generating Excel File...');
        $('#excelgenerate').addClass('disabled');

        try {
            let panelType = qrData[0]["product"];
            let projectName = qrData[0]["projectName"];
            let orderManager = qrData[0]["orderManager"];

            var wb = XLSX.utils.book_new();
            wb.Props = {
                Title: `${projectNo}-OPT_OneX.xls`,
                Subject: "OPT_OneX",
                Author: "OneX",
                CreatedDate: moment().format('YYYY-MM-DD')
            };

            wb.SheetNames.push("Template");

            var ws_data = [
                ['DATE CREATED', moment().format('DD-MM-YYYY')],
                ['PROJECT NO', projectNo],
                ['PANEL TYPE', panelType],
                ['PROJECT NAME', projectName],
                ['ORDER MANAGER', orderManager],
                ['.', '..'],
                ['..', '.'],
                ['CREATED ON', moment().format('DD-MM-YYYY HH:mm')],
                ['PANEL NO', 'TYPICAL NAME', 'PANEL NAME']
            ];
            
            // Only include panels with non-blank panel names
            for(const row of qrData){
                const {posNo, panelName, typicalName} = {...row};
                if (panelName && panelName.trim() !== '') {
                    ws_data.push([posNo, typicalName, panelName]);
                }
            }

            var ws = XLSX.utils.aoa_to_sheet(ws_data);
            wb.Sheets["Template"] = ws;

            var wbout = XLSX.write(wb, {bookType: 'xls', type: 'binary'});
            function s2ab(s) {
                var buf = new ArrayBuffer(s.length);
                var view = new Uint8Array(buf);
                for (var i = 0; i < s.length; i++) view[i] = s.charCodeAt(i) & 0xFF;
                return buf;
            }
            saveAs(
                new Blob([s2ab(wbout)],
                {type: "application/octet-stream"}),
                `${projectNo}-OPT_OneX.xls`
            );

            // Hide loader after a short delay to ensure download starts
            setTimeout(() => {
                hideLoader();
                $('#excelgenerate').removeClass('disabled');
            }, 500);

        } catch (error) {
            console.error("Error generating Excel:", error);
            hideLoader();
            $('#excelgenerate').removeClass('disabled');
            alert("Error generating Excel. Please try again.");
        }
    }
</script>
</html>