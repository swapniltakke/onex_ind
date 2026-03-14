<?php 
session_start();
$user = $_SESSION['username'];
include('shared/CommonManager.php');
include('header.php');
include('menustamping.php');
$login_type = $_SESSION['role_name'];
if ($login_type == "Stamping") {
    $heading_msg =  "Stamping All Checklist";
    $heading_details_msg = "Stamping Checklist Details";
    $role_name = "Manufacturing";
} else if ($login_type == "Manufacturing") {
    $heading_msg =  "Manufacturing All Checklist";
    $heading_details_msg = "Manufacturing Checklist Details";
    $role_name = "Stamping";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Stamping Checklist</title>
    <link href="https://code.jqueryui.com/1.12.1/themes/base/jquery-ui.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <!-- Include jsPDF library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <!-- Include jsPDF autoTable plugin -->
    <script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.5.23/dist/jspdf.plugin.autotable.min.js"></script>
</head>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="pagehead">
                <div class="row">
                    <div class="col-sm-4">
                        <h5><?php echo $heading_msg; ?></h5>
                    </div>
                    <div class="col-sm-8">
                        <div class="tab float-sm-right">
                        </div>

                    </div>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="framecontent">
                        <div class="card">
                            <div class="card-body formarea smpadding">
                              <div class="row">
                                  <div class="col-md-1">
                                    <label>Product Name: </label>
                                  </div>
                                  <div class="col-md-2">
                                    <input type="text" class="form-control" id="product_name" name="product_name" value="<?php echo (isset($product_name)) ? $product_name : ""; ?>" required />
                                  </div>
                                  <div class="col-md-1">
                                    <label>Serial No: </label>
                                  </div>
                                  <div class="col-md-2">
                                    <input type="text" class="form-control" id="serial_no" name="serial_no" value="<?php echo (isset($serial_no)) ? $serial_no : ""; ?>" required />
                                  </div>&nbsp;&nbsp;
                                  <div class="col-md-1.5">
                                    <label>Sales Order No: </label>
                                  </div>
                                  <div class="col-md-2">
                                    <input type="text" class="form-control" id="sales_order_no" name="sales_order_no" value="<?php echo (isset($sales_order_no)) ? $sales_order_no : ""; ?>" required />
                                  </div>
                                  <div class="col-md-1">
                                    <label>Item No: </label>
                                  </div>
                                  <div class="col-md-2">
                                    <input type="text" class="form-control" id="item_no" name="item_no" value="<?php echo (isset($item_no)) ? $item_no : ""; ?>" required />
                                  </div>
                                  <div class="col-md-1">
                                    <label>Production Order No: </label>
                                  </div>
                                  <div class="col-md-2">
                                    <input type="text" class="form-control" id="production_order_no" name="production_order_no" value="<?php echo (isset($production_order_no)) ? $production_order_no : ""; ?>" required />
                                  </div>
                                  <div class="col-md-1">
                                      <label>Date Range: </label>
                                  </div>
                                  <div class="col-md-2">
                                      <input type="date" class="form-control" id="start_date" placeholder="Start Date" />
                                  </div>
                                  <div class="col-md-0">To
                                  </div>
                                  <div class="col-md-2">
                                      <input type="date" class="form-control" id="end_date" placeholder="End Date" />
                                  </div>
                                  <div class="col-md-1">
                                    <label>Status: </label>
                                  </div>
                                  <div class="col-md-2">
                                    <select class="form-control" id="status" name="status" id="subassembly_req">
                                      <option value="">Select Status</option>
                                      <option value="Completed" <?php if($status == '1'){ echo "selected";} ?>>Completed</option>
                                      <option value="Pending" <?php if($status == '0'){ echo "selected";} ?>>Pending</option>
                                    </select>
                                  </div>
                              </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body formarea smpadding">
                                <button id="searchButton" type="submit" class="btn btn-primary">Submit</button>
                                <button id="clearButton" type="button" class="btn btn-secondary">Clear</button>
                                <span class="pull-right">
                                <button id="exportExcel" class="btn btn-primary">Export to Excel</button>
                                <button id="exportPDF" class="btn btn-primary">Export to PDF</button>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body formarea smpadding">
                                  <div class="row">
                                    <div class="col-md-12">
                                        <div class="display_table">
                                            <table id="example1" class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Sr. No.</th>
                                                        <th>Product Name</th>
                                                        <th>Serial No</th>
                                                        <th>Sales Order No</th>
                                                        <th>Item No</th>
                                                        <th>Production Order No</th>
                                                        <th>User</th>
                                                        <th>Created Date</th>
                                                        <th>Status</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php include('footer.php'); ?>
<script>
var table;
$(document).ready(function() {
    $(".icon-input-btn").each(function() {
        var btnFont = $(this).find(".btn").css("font-size");
        var btnColor = $(this).find(".btn").css("color");
        $(this).find(".fa").css({
            'font-size': btnFont,
            'color': btnColor
        });
    });
    // Clear button click event
    $('#clearButton').on('click', function() {
        // Clear the input fields
        $('#product_name').val('');
        $('#serial_no').val('');
        $('#sales_order_no').val('');
        $('#item_no').val('');
        $('#production_order_no').val('');
        $('#start_date').val('');
        $('#end_date').val('');
        $('#status').val('');

        // Remove all table filters and redraw
        $.fn.dataTable.ext.search = []; // Clear custom search functions
        if (table) {
            table.search('').columns().search('').draw(); // Clear filters and redraw only if table is defined
        } else {
            console.error('DataTable is not defined');
        }
    });
});
</script>
<script>
$(function() {
    var table = $('#example1').DataTable({
        'paging': true,
        'lengthChange': true,
        'searching': true,
        'ordering': true,
        'info': true,
        'stateSave': true,
        'autoWidth': false,
        'serverSide': false,
        'language': {
            "lengthMenu": "_MENU_ per page",
            "zeroRecords": "No records found",
            "info": "Showing <b>_START_ to _END_</b> (of _TOTAL_)",
            "infoFiltered": "",
            "infoEmpty": "No records found",
            "processing": '<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i>'
        },
        'destroy': true,
        "sAjaxSource": "load_stampingchecklist.php",
        "aoColumns": [{
                mData: 'sr_no'
            },
            {
                mData: 'product_name'
            },
            {
                mData: 'serial_no'
            },
            {
                mData: 'sales_order_no'
            },
            {
                mData: 'item_no'
            },
            {
                mData: 'production_order_no'
            },
            {
                mData: 'user'
            },
            {
                mData: 'create_date'
            },
            {
                mData: 'status'
            },
            {
                mData: 'action',
                "render": function(data, type, row) {
                    return '<a href="view_stampingchecklist.php?type=view&id=' + row.id +
                        '&product_id=' + row.product_id + '&station_id=' + row.station_id +
                        '" class="view-btn">View</a>';
                }
            }
        ],
        'order': [
            [0, 'asc']
        ]
    });
    table.order([0, 'asc']).draw();
    // Handle search button click
    $('#searchButton').on('click', function() {
        var product_name = $('#product_name').val();
        var serial_no = $('#serial_no').val();
        var sales_order_no = $('#sales_order_no').val();
        var item_no = $('#item_no').val();
        var production_order_no = $('#production_order_no').val();
        var start_date = $('#start_date').val();
        var end_date = $('#end_date').val();
        var status = $('#status').val();

        // Clear any previously set custom search functions
        $.fn.dataTable.ext.search = [];

        // Apply date range filtering for create_date
        if (start_date && end_date) {
            $.fn.dataTable.ext.search.push(
                function(settings, data, dataIndex) {
                    var create_date = new Date(data[6]); // Assume data[6] contains the date in 'YYYY-MM-DD' format or similar
                    var start = new Date(start_date);
                    var end = new Date(end_date);

                    // Adjust date comparisons to cover the entire day for end_date
                    start.setHours(0, 0, 0, 0);
                    end.setHours(23, 59, 59, 999);

                    return (create_date >= start && create_date <= end);
                }
            );
        }

        // Create an array of search criteria
        var searchCriteria = [];

        if (product_name) {
            searchCriteria.push({ column: 1, value: product_name });
        }
        if (serial_no) {
            searchCriteria.push({ column: 2, value: serial_no });
        }
        if (sales_order_no) {
            searchCriteria.push({ column: 3, value: sales_order_no });
        }
        if (item_no) {
            searchCriteria.push({ column: 4, value: item_no });
        }
        if (production_order_no) {
            searchCriteria.push({ column: 5, value: production_order_no });
        }
        if (status) {
            searchCriteria.push({ column: 7, value: status });
        }

        // Apply the search criteria to the table
        table.columns().every(function(index) {
            var searchValue = '';
            
            for (var i = 0; i < searchCriteria.length; i++) {
                if (searchCriteria[i].column === index) {
                    searchValue = searchCriteria[i].value;
                    break;
                }
            }
            
            this.search(searchValue).draw();
        });
    });

    // Export to Excel
    $('#exportExcel').on('click', function() {
      exportTableToCSV('export.csv');
    });


    // Export to PDF
    // Export to PDF with filtered data
$('#exportPDF').on('click', function() {
    const { jsPDF } = window.jspdf;
    const pdf = new jsPDF('landscape', 'mm', 'a4');
    var columns = ["Sr. No.", "Product Name", "Serial No", "Sales Order No", "Item No", "Production Order No", "Created Date", "Status"];
    var rows = [];

    // Get the filtered data
    table.rows({ search: 'applied' }).every(function(rowIdx, tableLoop, rowLoop) {
        var data = this.data();
        var row = [
            data.sr_no,
            data.product_name,
            data.serial_no,
            data.sales_order_no,
            data.item_no,
            data.production_order_no,
            data.create_date,
            data.status
        ];
        rows.push(row);
    });

    // Use autoTable to add the table to PDF
    pdf.autoTable({
        head: [columns],
        body: rows,
        styles: {
          fontSize: 8
        },
        columnStyles: {
          0: { cellWidth: 20 },
          1: { cellWidth: 40 },
          2: { cellWidth: 30 },
          3: { cellWidth: 30 },
          4: { cellWidth: 30 },
          5: { cellWidth: 40 },
          6: { cellWidth: 30 },
          7: { cellWidth: 30 }
        },
        margin: { top: 20, bottom: 20, left: 20, right: 20 }
    });

    pdf.save("filtered-stamping-checklist.pdf");
});

    
});

// Function to export Table to CSV
function exportTableToCSV(filename) {
  // Make an AJAX request to fetch all the data
  $.ajax({
    url: "load_stampingchecklist.php",
    type: "GET",
    dataType: "json",
    success: function(data) {
      var csv = [];
      var headers = ["Sr. No.", "Product Name", "Serial No", "Sales Order No", "Item No", "Production Order No", "Created Date", "Status"];

      // Add the headers to the CSV
      csv.push(headers.join(","));

      // Add the data rows to the CSV
      for (var i = 0; i < data.aaData.length; i++) {
        var row = [
          data.aaData[i].sr_no,
          data.aaData[i].product_name,
          data.aaData[i].serial_no,
          data.aaData[i].sales_order_no,
          data.aaData[i].item_no,
          data.aaData[i].production_order_no,
          data.aaData[i].create_date,
          data.aaData[i].status
        ];
        csv.push(row.join(","));
      }

      downloadCSV(csv.join("\n"), filename);
    },
    error: function(xhr, status, error) {
      console.error("Error fetching data: " + error);
    }
  });
}

function downloadCSV(csv, filename) {
  var csvFile;
  var downloadLink;

  csvFile = new Blob([csv], {type: "text/csv"});
  downloadLink = document.createElement("a");

  downloadLink.download = filename;
  downloadLink.href = window.URL.createObjectURL(csvFile);
  downloadLink.style.display = "none";
  document.body.appendChild(downloadLink);
  downloadLink.click();
}

// Function to export Table to PDF
function exportTableToPDF() {
  var pdf = new jsPDF('landscape');
  pdf.autoTable({ html: '#example1' });
  pdf.save('export.pdf');
}

</script>
<script>
$(function() {
    //Date range picker
    $('#start_date,#end_date').datetimepicker({
        format: "yy-mm-dd"
    });
    //Date range picker

    var Accordion = function(el, multiple) {
        this.el = el || {};
        this.multiple = multiple || false;

        // Variables privadas
        var links = this.el.find('.link');
        var links1 = this.el.find('.link1');
        // Evento
        links.on('click', {
            el: this.el,
            multiple: this.multiple
        }, this.dropdown)
        links1.on('click', {
            el: this.el,
            multiple: this.multiple
        }, this.dropdown1)
    }

    Accordion.prototype.dropdown = function(e) {
        var $el = e.data.el;
        $this = $(this),
            $next = $this.next();

        $next.slideToggle();
        $this.parent().toggleClass('open');

        if (!e.data.multiple) {
            $el.find('.submenu').not($next).slideUp().parent().removeClass('open');
        };
    }

    Accordion.prototype.dropdown1 = function(e) {
        var $el = e.data.el;
        $this = $(this),
            $next = $this.next();

        $next.slideToggle();
        $this.parent().toggleClass('open');

        if (!e.data.multiple) {
            $el.find('.submenu1').not($next).slideUp().parent().removeClass('open');
        };
    }

    var accordion = new Accordion($('#accordion'), false);
});
</script>
</body>

</html>