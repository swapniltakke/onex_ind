document.addEventListener("DOMContentLoaded", function () {
    saveLog("log_dto", "Open List page access");
    localStorage.removeItem('DataTables_table_open_items_/dpm/dto/allmat.php');

    
    // Material type column definitions with corrected indices
    const materialTypeColumns = {
        'sheet_metal': {
            show: [0, 1, 2, 3, 4, 5, 7, 8, 9,  11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 36, 37],
            hide: [6, 10, 28, 29, 30, 31, 32, 33, 34, 35]
        },
        'busbar': {
            show: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 28, 29, 30, 31, 32, 36, 37],
            hide: [12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 33, 34, 35]
        },
        'shrouds': {
            show: [0, 1, 2, 3, 4, 5, 6, 7, 8, 11, 30, 33, 34, 35, 36, 37],
            hide: [9, 10, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 31, 32]
        },
        'equipment': {
            show: [0, 1, 2, 3, 4, 5, 9, 36, 37],
            hide: [6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35]
        },
        'gfk': {
            show: [0, 1, 2, 3, 4, 5, 6, 7, 8, 10, 11, 28, 31, 36, 37],
            hide: [9, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 29, 30,  32, 33, 34, 35]
        },
        'isolation': {
            show: [0, 1, 2, 3, 4, 5, 6, 7, 8, 11, 36, 37],
            hide: [9, 10, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35]
        },
        'paw': {
            show: [0, 1, 2, 3, 4, 5,  7, 8, 9, 36, 37],
            hide: [6, 10, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35]
        },
        'others': {
            show: [0, 1, 2, 3, 4, 5,  7, 8, 9, 36, 37],
            hide: [6, 10, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35]
        }
    };
    
    // Function to handle material type filtering
    function handleMaterialTypeFilter(materialType) {
        const table = $('#table_open_items').DataTable();
        const allColumns = Array.from({ length: table.columns().count() }, (_, i) => i);
        
        if (!materialType || !materialTypeColumns[materialType]) {
            // Show all columns if no material type is selected
            allColumns.forEach(i => table.column(i).visible(true));
        } else {
            // Hide all columns first
            allColumns.forEach(i => table.column(i).visible(false));
            
            // Show only the columns for selected material type
            materialTypeColumns[materialType].show.forEach(i => table.column(i).visible(true));
        }
        
        // Adjust table layout and redraw
        table.columns.adjust().draw();
    }
    // Initialize filters
    new FilterFields('table_open_items');
    let material_typeFilter = new Filter('material_type', 'table_open_items', 5);
    let productNameFilter = new Filter('productName', 'table_open_items', 2);
    let drawing_nameFilter = new Filter('drawing_name', 'table_open_items', 3);
    let ka_ratingFilter = new Filter('ka_rating', 'table_open_items', 7);
    let widthFilter = new Filter('width', 'table_open_items', 8);
    let descriptionFilter = new Filter('description', 'table_open_items', 9);
    let rear_boxFilter = new Filter('rear_box', 'table_open_items', 11);
    

    // Add material type filter handler
    material_typeFilter.onchange = function(value) {
        handleMaterialTypeFilter(value);
    };

    let filterArray = [material_typeFilter, productNameFilter, drawing_nameFilter, ka_ratingFilter, widthFilter, descriptionFilter, rear_boxFilter];
    new FilterInitial("table_open_items", filterArray);
    filterArray.forEach(filter => filter.init());

    // Function to sort dropdown options
    function sortDropdown(selectElementId) {
        var $select = $(selectElementId);
        var uniqueOptions = {};
        $select.find('option').each(function() {
            var text = $(this).text().trim();
            uniqueOptions[text] = $(this);
        });
        var sortedOptions = Object.keys(uniqueOptions)
            .sort((a, b) => a.localeCompare(b, undefined, {sensitivity: 'base'}))
            .map(key => uniqueOptions[key]);
        
        $select.empty().append(sortedOptions);
        $select.trigger('change');
    }

    // DataTable initialization
    $(document).ready(function() {
        setTimeout(function() {
            sortDropdown("#material_typeFilter");
            $('#table_open_items').DataTable().draw();
        }, 500);

        $("#mai_spinner_page").addClass('active');
        $('#date_filter').css('background-color', '#00646E');

        var table = $('#table_open_items').DataTable({
            "dom": 'Blfrtip',
            "pageLength": 20,
            "paging": true,
            "scrollX": false,
            "autoWidth": false,
            'responsive': false,
            'fixedHeader': true,
            'fixedColumns': { 
                leftColumns: 6 
            },
            ajax: {
                url: 'api/DTOController.php?action=allmat',
                dataSrc: function(json) {
                    return json.data.filter(function(item) {
                        return item;
                    });
                }
            },
            contentType: "application/json",
            //"bStateSave": true,
            "stateSave": false,
            lengthMenu: [
                [20, 40, 60, -1],
                [20, 40, 60, "All"],
            ],
            "initComplete": function (settings, json) {
                document.querySelector('#detailsegment').setAttribute('style', 'display: block');
                $('#table_open_items th').css({'min-width': '80px'});
                $('.dt-button.buttons-copy.buttons-html5').addClass('ui white button');
                $('.dt-button.buttons-copy.buttons-html5').prepend('<i class="clipboard icon"></i>');
                $('.dt-button.buttons-excel.buttons-html5').addClass('ui white button');
                $('.dt-button.buttons-excel.buttons-html5').prepend('<i class="file excel outline icon"></i>');
                $("#table_open_items_formFields").css('margin-bottom', "2%");
                $("#mai_spinner_page").removeClass('active');

                // Add material type filter change handler
                $('#material_typeFilter').on('change', function() {
                    handleMaterialTypeFilter($(this).val());
                });

                // Initial filter if material type is selected
                const initialMaterialType = $('#material_typeFilter').val();
                if (initialMaterialType) {
                    handleMaterialTypeFilter(initialMaterialType);
                }
            },
            columns: [
                { // 0 Action
                    className: 'details-control',
                    orderable: false,
                    data: null,
                    render: function(data, type, row) {
                        let actionButtonsHtmlContent = `<div class="ui mini buttons" style="flex-direction: column;">`;
                        if (data.showCollapse == '1') {
                            actionButtonsHtmlContent += `
                                <button class="ui mini blue button mt-1 details-control">${row.isExpanded ? '-' : '+'}</button>
                            `;
                        } else {
                            actionButtonsHtmlContent += `
                                <button class="ui mini blue button mt-1 details-control" style="display: none;"></button>
                            `;
                        }
                        actionButtonsHtmlContent += `
                            <button class="ui mini orange button mt-1" onClick='openMaterailRegisterModal(event, ${data.id})'>Update</button>
                          <!-- <button class="ui mini negative button mt-1" onclick="openDeleteRequest(${data.dto_id})">Delete</button> -->
                        </div>`;
                
                        return actionButtonsHtmlContent;
                    }
                },
                { visible: false, className: "ta-c", data: "id" }, // 1
                { className: "ta-c", data: data => { 
                    if (!productNameFilter.data.includes(data.product_name)) {
                        productNameFilter.data.push(data.product_name);
                        $("#productNameFilter").append(new Option(data.product_name, data.product_name));
                    }
                    return `<span>${data.product_name}</span>`
                }}, // 2
                { className: "ta-c", data: data => { 
                    if (!drawing_nameFilter.data.includes(data.drawing_name)) {
                        drawing_nameFilter.data.push(data.drawing_name);
                        $("#drawing_nameFilter").append(new Option(data.drawing_name, data.drawing_name));
                    }
                    return `<span>${data.drawing_name}</span>`
                }}, // 3
                { className: "ta-c", data: "drawing_number" }, // 4
                { className: "ta-c", data: data => { 
                    if (!material_typeFilter.data.includes(data.material_type)) {
                        material_typeFilter.data.push(data.material_type);
                        $("#material_typeFilter").append(new Option(data.material_type, data.material_type));
                    }
                    return `<span>${data.material_type}</span>`
                }}, // 5
                { className: "ta-c", data: "material" }, // 6
                { className: "ta-c", data: data => { 
                    if (!ka_ratingFilter.data.includes(data.ka_rating)) {
                        ka_ratingFilter.data.push(data.ka_rating);
                        $("#ka_ratingFilter").append(new Option(data.ka_rating, data.ka_rating));
                    }
                    return `<span>${data.ka_rating}</span>`
                }}, // 7
                
                { className: "ta-c", data: data => { 
                    if (!widthFilter.data.includes(data.width)) {
                        widthFilter.data.push(data.width);
                        $("#widthFilter").append(new Option(data.width, data.width));
                    }
                    return `<span>${data.width}</span>`
                }}, // 8
                
                { className: "ta-c", data: data => { 
                    if (!descriptionFilter.data.includes(data.description)) {
                        descriptionFilter.data.push(data.description);
                        $("#descriptionFilter").append(new Option(data.description, data.description));
                    }
                    return `<span>${data.description}</span>`
                }}, // 9
                { className: "ta-c", data: "thickness" }, // 10

                { className: "ta-c", data: data => { 
                    if (!rear_boxFilter.data.includes(data.rear_box)) {
                        rear_boxFilter.data.push(data.rear_box);
                        $("#rear_boxFilter").append(new Option(data.rear_box, data.rear_box));
                    }
                    return `<span>${data.rear_box}</span>`
                }}, // 11
                { className: "ta-c", data: "end_cover_location" }, // 12
                { className: "ta-c", data: "ebb_cutout" }, // 13
                { className: "ta-c", data: "ebb_size" }, // 14
                { className: "ta-c", data: "cable_entry" }, // 15
                { className: "ta-c", data: "gp_thickness" }, // 16
                { className: "ta-c", data: "gp_material" }, // 17
                { className: "ta-c", data: "interlock" }, // 18
                { className: "ta-c", data: "ir_window" }, // 19
                { className: "ta-c", data: "nameplate" }, // 20
                { className: "ta-c", data: "viewing_window" }, // 21
                { className: "ta-c", data: "lhs_panel_rb" }, // 22
                { className: "ta-c", data: "rhs_panel_rb" }, // 23
                { className: "ta-c", data: "rear_box_type" }, // 24
                { className: "ta-c", data: "ct_type" }, // 25
                { className: "ta-c", data: "cable_number" }, // 26
                { className: "ta-c", data: "cbct" }, // 27
                { className: "ta-c", data: "panel_width" }, // 28
                { className: "ta-c", data: "feeder_bar_size" }, // 29
                { className: "ta-c", data: "mbb_size" }, // 30
                { className: "ta-c", data: "sizeofbusbar" }, // 31
                { className: "ta-c", data: "ag_plating" }, // 32
                { className: "ta-c", data: "mbb_run" }, // 33
                { className: "ta-c", data: "feeder_run" }, // 34
                { className: "ta-c", data: "feeder_size" }, // 35
                { className: "ta-c", data: "short_text" }, // 36
                { className: "ta-c", data: "remarks" }, // 37
                { className: "ta-c", data: "user" } // 38
            ],
            "order": [[1, 'desc']],
            buttons: [
                {
                    extend: 'excelHtml5',
                    autoFilter: true,
                    exportOptions: {
                        columns: ':visible:not(:first-child)'
                    }
                },
                {
                    extend: 'copy',
                    text: 'Copy',
                    exportOptions: {
                        columns: ':visible:not(:first-child)'
                    }
                }
            ],
            "drawCallback": function (settings) {
                if (settings.json) {
                    $("#span_production_order_quantity_count").text(settings.json.ProductionOrderQuantityCount);
                }
                $("#mai_spinner_page").removeClass('active');
            }

            
        });

        // Add event listener for material type filter changes
        $('#material_typeFilter').on('change', function() {
            const selectedType = $(this).val();
            handleMaterialTypeFilter(selectedType);
        });
    });

    // Date range picker configuration
    const start = moment().startOf('month');
    const end = moment();
    const thisMonth = new Date().getMonth();
    let thisFiscalYear = new Date().getFullYear();
    if (thisMonth < 9) thisFiscalYear -= 1;
    let lastFiscalYear1 = thisFiscalYear - 1;
    let lastFiscalYear2 = thisFiscalYear;

    function setDateRangePicker(id, callback) {
        $(id).daterangepicker({
            startDate: start,
            endDate: end,
            showDropdowns: true,
            minYear: 2021,
            maxYear: parseInt(moment().format('YYYY'), 10) + 1,
            alwaysShowCalendars: true,
            autoApply: true,
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                'Last Year': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')],
                'Last 1 Year': [moment().subtract(365, 'days'), moment()],
                'This Fiscal Year': [new Date(thisFiscalYear, 9, 1), moment()],
                'Last Fiscal Year': [new Date(lastFiscalYear1, 9, 1), new Date(lastFiscalYear2, 9, 1)]
            }
        }, callback);
        callback(start, end);
    }

    function cb(start, end) {
        $('#reportrange span').html(start.format("DD-MM-YYYY") + ' / ' + end.format("DD-MM-YYYY"));
    }

    function cb1(start, end) {
        $('#planned_month_reportrange span').html(start.format("DD-MM-YYYY") + ' / ' + end.format("DD-MM-YYYY"));
    }

    $(function () {
        setDateRangePicker('#reportrange', cb);
        setDateRangePicker('#planned_month_reportrange', cb1);
    });
});

// Add styles
const styles = `
    #material_typeFilter {
        min-width: 150px;
        padding: 6px;
        border: 1px solid #ddd;
        border-radius: 4px;
        margin-bottom: 10px;
    }

    .filter-container {
        margin: 10px 0;
    }

    .ui.table thead th {
        white-space: nowrap;
        background: #f9fafb;
    }

    .column-visibility-control {
        margin-top: 10px;
    }

    .ui.table {
        margin: 0;
        width: 100%;
    }

    .ta-c {
        text-align: center !important;
    }

    .ui.mini.buttons {
        display: flex;
        gap: 5px;
    }

    .mt-1 {
        margin-top: 5px !important;
    }

    #table_open_items_wrapper .dataTables_filter {
        margin-bottom: 15px;
    }

    #table_open_items_wrapper .dataTables_length {
        margin-bottom: 15px;
    }

    .ui.white.button {
        margin-left: 5px;
    }

    .dataTables_scrollBody {
        min-height: 300px;
    }

    .ui.table td {
        padding: 8px;
        vertical-align: middle;
    }

    .details-control {
        cursor: pointer;
    }

    #mai_spinner_page {
        z-index: 9999;
    }
`;

const styleSheet = document.createElement("style");
styleSheet.innerText = styles;
document.head.appendChild(styleSheet);