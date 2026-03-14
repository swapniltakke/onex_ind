<?php SharedManager::checkAuthToModule(5); ?>
<html>
<head>
    <title>Panel Quantities of Project Typicals</title>
    <meta name="viewport" content="width=device-width, initial-scale=0.85, maximum-scale=1, user-scalable=yes"/>
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
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
    <!--<link href="/css/Semantic-UI-Alert.css" rel="stylesheet"/>-->

    <link href="/plugins/pacejs/pace.css" rel="stylesheet"/>
    <link href="/css/main.css" rel="stylesheet"/>
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
</head>
<style>
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

    .ui.input {
        margin-bottom: 10px;
    }
</style>

<body>
    <div class="pusher">
        <div class="ui secondary segment">
            <a style='text-align:center' id="eleman" href="/index.php"
               class="center aligned item"><h1 style="font-size:21px;">
                    <img src="/images/onex.png"
                         style="max-width:150px;"
                         class="center aligned small image;"/></br>
                    <span  data-translate="project-typicals">Project Typicals</span></h1></a>
        </div>
        <div class="ui container">
            <div class="ui inverted blue segment">
                <h4 class="ui horizontal divider header" data-translate="opt-h-1">
                    Panel Quantities of Project Typicals
                </h4>
                <div class="left aligned field">
                    <label data-translate="projectNo">
                        Project No
                    </label>
                    <div class="ui big labeled fluid input">
                        <input name="orderno" autofocus placeholder="" pattern="[0-9]*" inputmode="numeric" maxLength=15>
                    </div>
                    <div>
                        <div id='resultTable' style="display: none">
                            <table class='ui compact striped celled table'>
                                <thead style='text-align: center;'>
                                    <tr>
                                        <th class='thTypicalName'>Typical</th>
                                        <th class='thPanelQty'>Panel Quantity</th>
                                    </tr>
                                </thead>
                                <tbody id='resultTableBody'>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </body>
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/language/translator.php"; ?>
    <script>
        $(document).ready(function () {
            $('input[name=orderno]').on('input', async function () {
                const orderno = $("input[name=orderno]").val();
                if (orderno.length !== 10)
                    return;

                const resultData = await $.ajax({
                    url: '/opt/api/getordertypicals.php',
                    type: 'GET',
                    data: {
                        project: orderno
                    }
                }).catch(error => {
                    console.log(error);
                });

                console.log(resultData)
                for(const row of resultData){
                    console.log(row)
                    const {typicalQty, typicalName} = {...row};

                    const tableRow = document.createElement('tr');
                    const typicalTd = document.createElement('td');
                    typicalTd.innerText = typicalName;
                    typicalTd.classList.add('center');
                    typicalTd.classList.add('aligned');

                    const typicalQtyTd = document.createElement('td');
                    typicalQtyTd.innerText = typicalQty;
                    typicalQtyTd.classList.add('center');
                    typicalQtyTd.classList.add('aligned');

                    tableRow.appendChild(typicalTd);
                    tableRow.appendChild(typicalQtyTd);

                    document.querySelector('#resultTableBody').appendChild(tableRow);
                }

                document.querySelector('#resultTable').style.display = "block";
            });
        });

        document.addEventListener("DOMContentLoaded", function() {
            const translator = new Translator('typicals', 'body', ['en'], 'en')
        });
    </script>
</html>