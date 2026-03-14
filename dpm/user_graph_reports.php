<?php
include_once 'core/index.php';

// Query: Get department-wise count for pie chart (ACTIVE USERS ONLY)
$sql = "
    SELECT department, COUNT(*) AS count
    FROM employee_registration
    WHERE status = 'A'
    GROUP BY department
";
$result = DbManager::fetchPDOQueryData('spectra_db', $sql)["data"];

// Build dataPoints array for the pie chart
$dataPoints = array();
$totalEmployees = 0;
foreach ($result as $row) {
    $dataPoints[] = array(
        "label" => $row['department'] ?: 'Unassigned',
        "y"     => (int)$row['count']
    );
    $totalEmployees += (int)$row['count'];
}

// Get total number of departments
$totalDepartments = count($dataPoints);

// Query: Get department and sub-department counts for stacked bar graph (ACTIVE USERS ONLY)
$sql_stacked = "
    SELECT 
        er.department,
        er.sub_department,
        COUNT(*) AS count
    FROM 
        employee_registration er
    WHERE 
        er.status = 'A'
    GROUP BY 
        er.department, er.sub_department
    ORDER BY 
        er.department, er.sub_department
";

$result_stacked = DbManager::fetchPDOQueryData('spectra_db', $sql_stacked)["data"];

// Process data for stacked chart
$departments = array();
$subDepartments = array();
$stackedData = array();

// Get unique departments and sub-departments
foreach ($result_stacked as $row) {
    $department = $row['department'] ?: 'Unassigned';
    $subDepartment = $row['sub_department'] ?: 'Unassigned';
    
    if (!in_array($department, $departments)) {
        $departments[] = $department;
    }
    
    if (!in_array($subDepartment, $subDepartments)) {
        $subDepartments[] = $subDepartment;
    }
    
    if (!isset($stackedData[$subDepartment])) {
        $stackedData[$subDepartment] = array();
    }
    
    $stackedData[$subDepartment][$department] = (int)$row['count'];
}

// Create data series for each sub-department
$stackedSeries = array();
foreach ($subDepartments as $subDepartment) {
    $dataPoints_stacked = array();
    $hasNonZeroValue = false;
    
    foreach ($departments as $department) {
        $count = isset($stackedData[$subDepartment][$department]) ? $stackedData[$subDepartment][$department] : 0;
        
        $dataPoints_stacked[] = array(
            "label" => $department,
            "y" => $count
        );
        
        if ($count > 0) {
            $hasNonZeroValue = true;
        }
    }
    
    if ($hasNonZeroValue) {
        $stackedSeries[] = array(
            "type" => "stackedColumn",
            "name" => $subDepartment,
            "showInLegend" => true,
            "dataPoints" => $dataPoints_stacked
        );
    }
}

// Query: Get quarterly department registration data for the last 2 years
$sql_quarterly = "
    SELECT 
        department,
        YEAR(created_at) AS year,
        QUARTER(created_at) AS quarter,
        COUNT(*) AS count
    FROM 
        employee_registration
    WHERE 
        status = 'A'
        AND created_at >= DATE_SUB(NOW(), INTERVAL 2 YEAR)
    GROUP BY 
        department, 
        YEAR(created_at), 
        QUARTER(created_at)
    ORDER BY 
        department,
        YEAR(created_at),
        QUARTER(created_at)
";

$result_quarterly = DbManager::fetchPDOQueryData('spectra_db', $sql_quarterly)["data"];

// Process quarterly data
$quarterlyDepartments = array();
$quarters = array();
$quarterlyData = array();

// Function to get quarter name
function getQuarterName($quarter, $year) {
    switch($quarter) {
        case 1:
            return $year . " (Jan - Mar)";
        case 2:
            return $year . " (Apr - Jun)";
        case 3:
            return $year . " (Jul - Sep)";
        case 4:
            return $year . " (Oct - Dec)";
        default:
            return $year . " Q" . $quarter;
    }
}

// Get unique departments and quarters
foreach ($result_quarterly as $row) {
    $department = $row['department'] ?: 'Unassigned';
    $year = (int)$row['year'];
    $quarter = (int)$row['quarter'];
    $quarterKey = $year . "-Q" . $quarter;
    $quarterName = getQuarterName($quarter, $year);
    
    if (!in_array($department, $quarterlyDepartments)) {
        $quarterlyDepartments[] = $department;
    }
    
    if (!isset($quarters[$quarterKey])) {
        $quarters[$quarterKey] = array(
            'label' => $quarterName,
            'year' => $year,
            'quarter' => $quarter
        );
    }
    
    if (!isset($quarterlyData[$department])) {
        $quarterlyData[$department] = array();
    }
    
    $quarterlyData[$department][$quarterKey] = (int)$row['count'];
}

// Sort quarters chronologically
uasort($quarters, function($a, $b) {
    if ($a['year'] == $b['year']) {
        return $a['quarter'] - $b['quarter'];
    }
    return $a['year'] - $b['year'];
});

// Create data series for quarterly chart
$quarterlySeries = array();
foreach ($quarterlyDepartments as $department) {
    $dataPoints_quarterly = array();
    $i = 0;
    foreach ($quarters as $quarterKey => $qInfo) {
        $count = isset($quarterlyData[$department][$quarterKey]) ? $quarterlyData[$department][$quarterKey] : 0;
        $dataPoints_quarterly[] = array(
            "x" => $i,
            "y" => $count,
            "label" => $qInfo['label']
        );
        $i++;
    }
    
    $quarterlySeries[] = array(
        "type" => "stackedColumn",
        "name" => $department,
        "showInLegend" => true,
        "dataPoints" => $dataPoints_quarterly
    );
}

// Get quarter labels for x-axis
$quarterLabels = array();
foreach ($quarters as $quarterKey => $qInfo) {
    $quarterLabels[] = $qInfo['label'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Employee Registration Analysis</title>
    
    <!-- CRITICAL: Font Awesome MUST load FIRST before ANY other CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" integrity="sha512-SfTiTlX6kk+qitfevl/7LibUOeJWlt9rbyDn92a1DqWOw9vWG2MFoays0sgObmWazO5BQPiFucnnEAjpAB+/Sw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Include CSS files -->
    <link href="../css/semantic.min.css" rel="stylesheet"/>
    <link rel="stylesheet" type="text/css" href="../css/dataTables.semanticui.min.css">
    <link rel="stylesheet" type="text/css" href="../css/responsive.dataTables.min.css">
    <link href="../css/main.css?13" rel="stylesheet"/>
    
    <?php $menu_header_display = 'PMS Module'; ?>
    <?php include_once 'shared/headerStyles.php' ?>
    
    <style>
        /* Import Siemens Sans font */
        @import url('https://assets.brand.siemens.com/font/siemens-sans/siemens-sans.css');
        
        /* Apply Siemens Sans to all elements EXCEPT Font Awesome icons */
        body, h1, h2, h3, h4, h5, h6, p, div, span, a, button, input, select, textarea,
        .nav-label, .chart-title, .stat-label, .stat-value {
            font-family: 'Siemens Sans', sans-serif !important;
        }
        
        /* CRITICAL: Force Font Awesome to use its own font - DO NOT override */
        .fa, i.fa, span.fa,
        .fa:before, i.fa:before, span.fa:before,
        .fa:after, i.fa:after, span.fa:after {
            font-family: 'FontAwesome' !important;
            font-style: normal !important;
            font-weight: normal !important;
            font-variant: normal !important;
            text-transform: none !important;
            speak: none;
            line-height: 1;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            display: inline-block;
        }
        
        /* Sidebar icon styling */
        #side-menu i.fa {
            width: 20px;
            text-align: center;
            margin-right: 10px;
            font-size: 16px;
            color: #a7b1c2;
            vertical-align: middle;
        }
        
        #side-menu .nav-second-level i.fa {
            margin-right: 8px;
            font-size: 14px;
            width: 18px;
        }
        
        #side-menu .nav-third-level i.fa {
            margin-right: 6px;
            font-size: 13px;
            width: 16px;
        }
        
        /* Active menu item icon color */
        #side-menu li.active > a i.fa {
            color: #ffffff;
        }
        
        /* Hover state */
        #side-menu a:hover i.fa {
            color: #ffffff;
        }
        
        /* Arrow icons for dropdowns */
        .fa.arrow {
            float: right;
            margin-top: 3px;
        }
        
        .fa.arrow:before {
            content: "\f104";
        }
        
        .active > a > .fa.arrow:before,
        .nav-second-level.in + a > .fa.arrow:before {
            content: "\f107";
        }
        
        /* Ensure specific icons render correctly */
        .fa-users:before { content: "\f0c0"; }
        .fa-user-plus:before { content: "\f234"; }
        .fa-clock-o:before { content: "\f017"; }
        .fa-exchange:before { content: "\f0ec"; }
        .fa-file-text-o:before { content: "\f0f6"; }
        .fa-table:before { content: "\f0ce"; }
        .fa-bar-chart:before { content: "\f080"; }
        .fa-calendar:before { content: "\f073"; }
        .fa-bars:before { content: "\f0c9"; }
        .fa-calendar-plus-o:before { content: "\f271"; }
        .fa-calendar-check-o:before { content: "\f274"; }
        .fa-calendar-minus-o:before { content: "\f272"; }
        
        /* Loading indicator */
        #loading-indicator {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 20px;
            border-radius: 5px;
            z-index: 9999;
        }
        
        /* Chart container styles */
        .chart-container {
            padding: 15px;
            background-color: #fff;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .chart-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }
        
        .row {
            display: flex;
            flex-wrap: wrap;
            margin-right: -15px;
            margin-left: -15px;
        }
        
        .col-md-6 {
            flex: 0 0 50%;
            max-width: 50%;
            padding-right: 15px;
            padding-left: 15px;
            box-sizing: border-box;
        }
        
        .col-md-12 {
            flex: 0 0 100%;
            max-width: 100%;
            padding-right: 15px;
            padding-left: 15px;
            box-sizing: border-box;
        }
        
        @media (max-width: 992px) {
            .col-md-6 {
                flex: 0 0 100%;
                max-width: 100%;
            }
        }
        
        @media (max-width: 768px) {
            #page-wrapper {
                margin-left: 0;
            }
        }
        
        .navbar-static-top {
            background-color: #fff;
            border-bottom: 1px solid #e7eaec;
            padding: 0 15px;
        }
        
        .navbar-header {
            float: left;
            padding: 15px 0;
        }
        
        .navbar-top-links {
            float: right;
            margin: 0;
            padding: 0;
            list-style: none;
        }
        
        .navbar-minimalize {
            padding: 8px 12px;
            background-color: #009999;
            color: white;
            border: none;
            border-radius: 3px;
        }
        
        .navbar-minimalize i.fa {
            color: white;
            font-size: 16px;
        }
        
        h2 {
            margin: 15px 0;
        }
        
        #wrapper {
            width: 100%;
            overflow-x: hidden;
        }
        
        #page-wrapper {
            margin-left: 220px;
            padding: 0 15px;
            min-height: 568px;
            background-color: #f3f3f4;
            transition: all 0.4s;
        }
        
        body.mini-navbar #page-wrapper {
            margin-left: 70px;
        }
        
        .gray-bg {
            background-color: #f3f3f4;
        }
        
        .chart-responsive {
            height: 370px;
            width: 100%;
        }
        
        .stats-container {
            display: flex;
            justify-content: space-around;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        
        .stat-box {
            background-color: #fff;
            border-radius: 4px;
            padding: 10px 15px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            text-align: center;
            min-width: 120px;
            margin: 5px;
        }
        
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #009999;
        }
        
        .stat-label {
            font-size: 12px;
            color: #000000;
            text-transform: uppercase;
            font-weight: 500;
        }
        
        .canvasjs-chart-credit {
            display: none !important;
        }
        
        .siemens-teal {
            color: #009999;
        }
        
        .siemens-bg-teal {
            background-color: #009999;
            color: white;
        }
        
        .btn-primary {
            background-color: #009999;
            border-color: #009999;
        }
        
        .btn-primary:hover {
            background-color: #007777;
            border-color: #007777;
        }
    </style>
</head>
<body>
<div id="wrapper">
    <!-- Set active page for sidebar highlighting -->
    <?php $activePage = '/pms/user_graph_reports.php'; ?>
    <?php include_once 'shared/pms_sidebar.php' ?>
    
    <div id="page-wrapper" class="gray-bg">
        <div class="row border-bottom">
            <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">
                <div class="navbar-header">
                    <a class="navbar-minimalize minimalize-styl-2 btn siemens-bg-teal" href="#"><i class="fa fa-bars"></i></a>
                </div>
                <ul class="nav navbar-top-links navbar-right">
                    <li>
                        <h2 style="text-align: left;">Employee Registration Analysis</h2>
                    </li>
                </ul>
            </nav>
        </div>
        
        <!-- Summary Stats Section -->
        <div class="row" style="margin-top: 20px;">
            <div class="col-md-12">
                <div class="stats-container">
                    <div class="stat-box">
                        <div class="stat-value"><?php echo $totalDepartments; ?></div>
                        <div class="stat-label">Total Departments</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value"><?php echo count($subDepartments); ?></div>
                        <div class="stat-label">Sub-Departments</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value"><?php echo $totalEmployees; ?></div>
                        <div class="stat-label">Active Employees</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value"><?php echo count($quarters); ?></div>
                        <div class="stat-label">Quarters Analyzed</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quarterly Trend Chart -->
        <div class="row">
            <div class="col-md-12">
                <div class="chart-container">
                    <div class="chart-title">Quarterly Department Registration Trends (Last 2 Years)</div>
                    <div id="quarterlyChartContainer" class="chart-responsive"></div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Pie Chart -->
            <div class="col-md-6">
                <div class="chart-container">
                    <div class="chart-title">Active Employee Distribution by Department</div>
                    <div id="pieChartContainer" class="chart-responsive"></div>
                </div>
            </div>
            
            <!-- Stacked Bar Chart -->
            <div class="col-md-6">
                <div class="chart-container">
                    <div class="chart-title">Department Breakdown by Sub-Department (Active Employees)</div>
                    <div id="stackedChartContainer" class="chart-responsive"></div>
                </div>
            </div>
        </div>
        
        <?php 
        $footer_display = 'PMS';
        include_once '../assemblynotes/shared/footer.php'; 
        ?>
    </div>
    
    <!-- Loading indicator -->
    <div id="loading-indicator">Loading...</div>
</div>

<!-- Include scripts -->
<?php include_once '../assemblynotes/shared/headerSemanticScripts.php' ?>
<script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>

<script>
$(document).ready(function() {
    // CRITICAL: Fix Font Awesome icons if they're showing as boxes
    function ensureFontAwesomeLoaded() {
        // Test if Font Awesome is working
        var testIcon = $('<i class="fa fa-check" style="position:absolute;left:-9999px;"></i>').appendTo('body');
        var computedFont = testIcon.css('font-family');
        testIcon.remove();
        
        console.log('Font Awesome font-family:', computedFont);
        
        // If Font Awesome isn't loaded properly, reload it
        if (computedFont.indexOf('FontAwesome') === -1) {
            console.warn('Font Awesome not loaded correctly, attempting to fix...');
            
            // Force reload Font Awesome
            $('link[href*="font-awesome"]').remove();
            $('<link>')
                .attr('rel', 'stylesheet')
                .attr('href', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css')
                .attr('integrity', 'sha512-SfTiTlX6kk+qitfevl/7LibUOeJWlt9rbyDn92a1DqWOw9vWG2MFoays0sgObmWazO5BQPiFucnnEAjpAB+/Sw==')
                .attr('crossorigin', 'anonymous')
                .appendTo('head');
            
            // Check again after a delay
            setTimeout(ensureFontAwesomeLoaded, 500);
        } else {
            console.log('✓ Font Awesome loaded successfully');
        }
    }
    
    // Run Font Awesome check
    ensureFontAwesomeLoaded();
    
    // Clean up any duplicate icons in sidebar
    $('#side-menu i.fa').each(function() {
        var $icon = $(this);
        // Remove any text content
        if ($icon.text().trim() !== '') {
            $icon.text('');
        }
    });
    
    // Initialize charts
    initializeCharts();
    
    // Handle sidebar toggle
    $('.navbar-minimalize').on('click', function(event) {
        event.preventDefault();
        $("body").toggleClass("mini-navbar");
        setTimeout(function() {
            resizeChartsToMatchQuarterly();
        }, 400);
    });
    
    // Handle window resize
    $(window).resize(function() {
        resizeChartsToMatchQuarterly();
    });
    
    // Hide CanvasJS credits
    hideCanvasJSCredits();
    adjustChartLayout();
    setInterval(hideCanvasJSCredits, 1000);
    
    // Override CanvasJS prototype
    if (typeof CanvasJS !== 'undefined' && CanvasJS.Chart) {
        var originalRender = CanvasJS.Chart.prototype.render;
        CanvasJS.Chart.prototype.render = function() {
            var result = originalRender.apply(this, arguments);
            hideCanvasJSCredits();
            return result;
        };
    }
});

// Rest of your JavaScript functions remain exactly the same...
function adjustChartLayout() {
    $('.col-md-6').css({
        'flex': '0 0 100%',
        'max-width': '100%',
        'margin-bottom': '30px'
    });
    
    $('.chart-responsive').css({
        'height': '400px',
        'width': '100%'
    });
    
    $('.chart-container').css({
        'margin-bottom': '30px',
        'padding': '20px',
        'box-shadow': '0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24)'
    });
    
    $('.chart-container').not(':first').css('margin-top', '30px');
    
    setTimeout(function() {
        if (window.pieChart) window.pieChart.render();
        if (window.stackedChart) window.stackedChart.render();
        if (window.quarterlyChart) window.quarterlyChart.render();
        hideCanvasJSCredits();
    }, 100);
}

function resizeChartsToMatchQuarterly() {
    var quarterlyContainer = $('#quarterlyChartContainer');
    if (quarterlyContainer.length === 0) return;
    
    var quarterlyHeight = quarterlyContainer.height();
    
    $('#pieChartContainer, #stackedChartContainer').css({
        'height': quarterlyHeight + 'px',
        'width': '100%'
    });
    
    if (window.pieChart) window.pieChart.render();
    if (window.stackedChart) window.stackedChart.render();
    if (window.quarterlyChart) window.quarterlyChart.render();
    
    hideCanvasJSCredits();
}

function hideCanvasJSCredits() {
    try {
        var credits = document.getElementsByClassName("canvasjs-chart-credit");
        for (var i = 0; i < credits.length; i++) {
            if (credits[i]) {
                credits[i].style.display = "none";
            }
        }
        
        var svgTexts = document.querySelectorAll('text');
        for (var i = 0; i < svgTexts.length; i++) {
            if (svgTexts[i] && svgTexts[i].textContent && 
                typeof svgTexts[i].textContent === 'string' && 
                svgTexts[i].textContent.indexOf && 
                svgTexts[i].textContent.indexOf('Trial') !== -1) {
                svgTexts[i].textContent = '';
            }
        }
        
        if (!document.getElementById('hide-canvasjs-credit')) {
            var style = document.createElement('style');
            style.id = 'hide-canvasjs-credit';
            style.type = 'text/css';
            style.innerHTML = '.canvasjs-chart-credit { display: none !important; }';
            document.head.appendChild(style);
        }
    } catch (e) {
        console.error("Error hiding CanvasJS credits:", e);
    }
}

function initializeCharts() {
    $('#loading-indicator').show();
    
    try {
        if (typeof CanvasJS === 'undefined') {
            console.error("CanvasJS library not loaded");
            $('#loading-indicator').hide();
            return;
        }
        
        try {
            CanvasJS.addCultureInfo("en", {
                fontFamily: "Siemens Sans, sans-serif"
            });
        } catch (e) {
            console.warn("Could not set culture info:", e);
        }
        
        try {
            CanvasJS.addColorSet("siemensColors", [
                "#009999", "#0066CC", "#FF9E1B", "#4C9900", "#CC0099",
                "#999999", "#333333", "#66CCCC", "#99CCFF", "#FFCC99"
            ]);
        } catch (e) {
            console.warn("Could not add color set:", e);
        }
        
        var tooltipOptions = {
            animationEnabled: false,
            borderColor: "#009999",
            borderThickness: 2,
            cornerRadius: 4,
            fontFamily: "Siemens Sans, sans-serif",
            fontSize: 12,
            padding: 2,
            contentFormatter: null,
            shared: true,
            outside: false,
            animationDuration: 0,
            followMouse: true
        };
        
        // PIE CHART
        try {
            var pieDataPoints = <?php echo json_encode($dataPoints, JSON_NUMERIC_CHECK); ?>;
            
            for (var i = 0; i < pieDataPoints.length; i++) {
                if (pieDataPoints[i]) {
                    if (pieDataPoints[i].label !== undefined && pieDataPoints[i].label !== null) {
                        pieDataPoints[i].label = String(pieDataPoints[i].label);
                    } else {
                        pieDataPoints[i].label = "Undefined";
                    }
                    pieDataPoints[i].y = Number(pieDataPoints[i].y || 0);
                }
            }
            
            var pieTooltipOptions = Object.assign({}, tooltipOptions, {
                contentFormatter: function(e) {
                    var content = "<div style='padding: 5px;'>";
                    content += "<strong>" + e.entries[0].dataPoint.label + "</strong><br/>";
                    content += "Employees: <strong>" + e.entries[0].dataPoint.y + "</strong><br/>";
                    content += "Percentage: <strong>" + e.entries[0].dataPoint.percentage + "%</strong>";
                    content += "</div>";
                    return content;
                }
            });
            
            window.pieChart = new CanvasJS.Chart("pieChartContainer", {
                animationEnabled: true,
                exportEnabled: true,
                theme: "light1",
                culture: "en",
                colorSet: "siemensColors",
                title: {
                    text: "Active Employee Registration by Department",
                    fontFamily: "Siemens Sans, sans-serif"
                },
                subtitles: [{
                    text: "Total Active Departments: <?php echo $totalDepartments; ?>",
                    fontSize: 16,
                    fontColor: "#009999",
                    fontWeight: "normal",
                    fontFamily: "Siemens Sans, sans-serif"
                }],
                creditText: "",
                creditHref: "",
                toolTip: pieTooltipOptions,
                data: [{
                    type: "pie",
                    showInLegend: true,
                    legendText: "{label}",
                    indexLabelFontSize: 12,
                    indexLabelFontFamily: "Siemens Sans, sans-serif",
                    legendFontFamily: "Siemens Sans, sans-serif",
                    indexLabel: "{label}: {y} ({percentage}%)",
                    dataPoints: pieDataPoints,
                    mouseover: function(e) {
                        e.chart.render();
                    }
                }]
            });
            
            var total = 0;
            for(var i = 0; i < pieChart.options.data[0].dataPoints.length; i++) {
                total += pieChart.options.data[0].dataPoints[i].y;
            }
            
            for(var i = 0; i < pieChart.options.data[0].dataPoints.length; i++) {
                pieChart.options.data[0].dataPoints[i].percentage = ((pieChart.options.data[0].dataPoints[i].y / total) * 100).toFixed(1);
            }
            
            pieChart.render();
        } catch (e) {
            console.error("Error rendering pie chart:", e);
        }
        
        // STACKED CHART
        try {
            var stackedSeriesData = <?php echo json_encode($stackedSeries, JSON_NUMERIC_CHECK); ?>;
            
            for (var i = 0; i < stackedSeriesData.length; i++) {
                var series = stackedSeriesData[i];
                
                if (series.name !== undefined && series.name !== null) {
                    series.name = String(series.name);
                } else {
                    series.name = "Series " + i;
                }
                
                if (series.dataPoints) {
                    for (var j = 0; j < series.dataPoints.length; j++) {
                        var point = series.dataPoints[j];
                        
                        if (point.label !== undefined && point.label !== null) {
                            point.label = String(point.label);
                        } else {
                            point.label = "Item " + j;
                        }
                        
                        point.y = Number(point.y || 0);
                    }
                }
            }
            
            var departmentTotals = {};
            for (var i = 0; i < stackedSeriesData.length; i++) {
                var series = stackedSeriesData[i];
                if (series.dataPoints) {
                    for (var j = 0; j < series.dataPoints.length; j++) {
                        var point = series.dataPoints[j];
                        var label = point.label;
                        
                        if (!departmentTotals[label]) {
                            departmentTotals[label] = 0;
                        }
                        
                        departmentTotals[label] += point.y;
                    }
                }
            }
            
            if (stackedSeriesData.length > 0) {
                var lastSeries = stackedSeriesData[stackedSeriesData.length - 1];
                if (lastSeries.dataPoints) {
                    for (var j = 0; j < lastSeries.dataPoints.length; j++) {
                        var point = lastSeries.dataPoints[j];
                        var label = point.label;
                        point.indexLabel = "Total: " + departmentTotals[label];
                        point.indexLabelFontColor = "#009999";
                        point.indexLabelFontWeight = "bold";
                        point.indexLabelFontFamily = "Siemens Sans, sans-serif";
                        point.indexLabelFontSize = 12;
                        point.indexLabelPlacement = "outside";
                    }
                }
            }
            
            var stackedTooltipOptions = Object.assign({}, tooltipOptions, {
                contentFormatter: function(e) {
                    if (!e || !e.entries || !e.entries.length) return "";
                    
                    var content = "<div style='padding: 5px;'>";
                    content += "<strong>" + e.entries[0].dataPoint.label + "</strong><br/>";
                    var total = 0;
                    var hasContent = false;
                    
                    for(var i = 0; i < e.entries.length; i++) {
                        if(e.entries[i].dataSeries.visible && e.entries[i].dataPoint.y > 0) {
                            content += "<span style='color: " + e.entries[i].dataSeries.color + ";'>• " + 
                                e.entries[i].dataSeries.name + "</span>: <strong>" + 
                                e.entries[i].dataPoint.y + "</strong><br/>";
                            total += e.entries[i].dataPoint.y;
                            hasContent = true;
                        }
                    }
                    
                    if (hasContent) {
                        content += "<hr style='margin: 2px 0;'>";
                        content += "<span style='color: #333;'>Total: <strong>" + total + "</strong></span>";
                    } else {
                        content += "<span style='color: #333;'>No data available</span>";
                    }
                    
                    content += "</div>";
                    return content;
                }
            });
            
            window.stackedChart = new CanvasJS.Chart("stackedChartContainer", {
                animationEnabled: true,
                exportEnabled: true,
                theme: "light1",
                culture: "en",
                colorSet: "siemensColors",
                title: {
                    text: "Active Sub-Department Breakdown",
                    fontFamily: "Siemens Sans, sans-serif"
                },
                subtitles: [{
                    text: "Total Active Sub-Departments: <?php echo count($subDepartments); ?>",
                    fontSize: 16,
                    fontColor: "#009999",
                    fontWeight: "normal",
                    fontFamily: "Siemens Sans, sans-serif"
                }],
                creditText: "",
                creditHref: "",
                axisY: {
                    title: "Number of Active Employees",
                    includeZero: true,
                    titleFontFamily: "Siemens Sans, sans-serif",
                    labelFontFamily: "Siemens Sans, sans-serif"
                },
                axisX: {
                    title: "Departments",
                    labelAngle: -45,
                    titleFontFamily: "Siemens Sans, sans-serif",
                    labelFontFamily: "Siemens Sans, sans-serif"
                },
                toolTip: stackedTooltipOptions,
                legend: {
                    cursor: "pointer",
                    fontFamily: "Siemens Sans, sans-serif",
                    itemclick: function(e) {
                        e.dataSeries.visible = typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible ? false : true;
                        e.chart.render();
                    }
                },
                data: stackedSeriesData
            });
            
            stackedChart.render();
        } catch (e) {
            console.error("Error rendering stacked chart:", e);
        }
        
        // QUARTERLY CHART
        try {
            var quarterlySeriesData = <?php echo json_encode($quarterlySeries, JSON_NUMERIC_CHECK); ?>;
            var quarterLabels = <?php echo json_encode($quarterLabels); ?>;
            
            for (var i = 0; i < quarterlySeriesData.length; i++) {
                var series = quarterlySeriesData[i];
                
                if (series.name !== undefined && series.name !== null) {
                    series.name = String(series.name);
                } else {
                    series.name = "Series " + i;
                }
                
                if (series.dataPoints) {
                    for (var j = 0; j < series.dataPoints.length; j++) {
                        var point = series.dataPoints[j];
                        point.x = Number(point.x || 0);
                        point.y = Number(point.y || 0);
                        
                        if (point.label !== undefined && point.label !== null) {
                            point.label = String(point.label);
                        } else {
                            point.label = "Quarter " + point.x;
                        }
                    }
                }
            }
            
            var quarterTotals = {};
            for (var i = 0; i < quarterlySeriesData.length; i++) {
                var series = quarterlySeriesData[i];
                if (series.dataPoints) {
                    for (var j = 0; j < series.dataPoints.length; j++) {
                        var point = series.dataPoints[j];
                        var x = point.x;
                        
                        if (!quarterTotals[x]) {
                            quarterTotals[x] = 0;
                        }
                        
                        quarterTotals[x] += point.y;
                    }
                }
            }
            
            if (quarterlySeriesData.length > 0) {
                var lastSeries = quarterlySeriesData[quarterlySeriesData.length - 1];
                if (lastSeries.dataPoints) {
                    for (var j = 0; j < lastSeries.dataPoints.length; j++) {
                        var point = lastSeries.dataPoints[j];
                        var x = point.x;
                        point.indexLabel = "Total: " + quarterTotals[x];
                        point.indexLabelFontColor = "#009999";
                        point.indexLabelFontWeight = "bold";
                        point.indexLabelFontFamily = "Siemens Sans, sans-serif";
                        point.indexLabelFontSize = 12;
                        point.indexLabelPlacement = "outside";
                    }
                }
            }
            
            var quarterlyTooltipOptions = Object.assign({}, tooltipOptions, {
                contentFormatter: function(e) {
                    if (!e || !e.entries || !e.entries.length) return "";
                    
                    var content = "<div style='padding: 5px;'>";
                    content += "<strong>" + e.entries[0].dataPoint.label + "</strong><br/>";
                    var total = 0;
                    var hasContent = false;
                    
                    for(var i = 0; i < e.entries.length; i++) {
                        if(e.entries[i].dataSeries && e.entries[i].dataSeries.visible && e.entries[i].dataPoint.y > 0) {
                            content += "<span style='color: " + e.entries[i].dataSeries.color + ";'>• " + 
                                e.entries[i].dataSeries.name + "</span>: <strong>" + 
                                e.entries[i].dataPoint.y + "</strong><br/>";
                            total += e.entries[i].dataPoint.y;
                            hasContent = true;
                        }
                    }
                    
                    if (hasContent) {
                        content += "<hr style='margin: 2px 0;'>";
                        content += "<span style='color: #333;'>Total: <strong>" + total + "</strong></span>";
                    } else {
                        content += "<span style='color: #333;'>No data available</span>";
                    }
                    
                    content += "</div>";
                    return content;
                }
            });
            
            window.quarterlyChart = new CanvasJS.Chart("quarterlyChartContainer", {
                animationEnabled: true,
                exportEnabled: true,
                theme: "light1",
                culture: "en",
                colorSet: "siemensColors",
                title: {
                    text: "Quarterly Department Registration Trends",
                    fontFamily: "Siemens Sans, sans-serif"
                },
                axisX: {
                    title: "Quarter",
                    titleFontFamily: "Siemens Sans, sans-serif",
                    labelFontFamily: "Siemens Sans, sans-serif",
                    labelAngle: -30,
                    labelFormatter: function(e) {
                        var index = parseInt(e.value);
                        if (quarterLabels && index >= 0 && index < quarterLabels.length) {
                            return quarterLabels[index];
                        }
                        return "";
                    },
                    interval: 1
                },
                axisY: {
                    title: "Number of Employees",
                    titleFontFamily: "Siemens Sans, sans-serif",
                    labelFontFamily: "Siemens Sans, sans-serif",
                    includeZero: true
                },
                toolTip: quarterlyTooltipOptions,
                legend: {
                    cursor: "pointer",
                    reversed: true,
                    verticalAlign: "center",
                    horizontalAlign: "right",
                    fontFamily: "Siemens Sans, sans-serif",
                    itemclick: function(e) {
                        e.dataSeries.visible = typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible ? false : true;
                        e.chart.render();
                    }
                },
                data: quarterlySeriesData
            });
            
            quarterlyChart.render();
        } catch (e) {
            console.error("Error rendering quarterly chart:", e);
        }
        
        setTimeout(hideCanvasJSCredits, 100);
    } catch (e) {
        console.error("Error initializing charts:", e);
    } finally {
        $('#loading-indicator').hide();
    }
}
</script>
</body>
</html>