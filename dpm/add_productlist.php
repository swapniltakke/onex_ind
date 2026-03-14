<?php 
session_start();
$user = $_SESSION['username'];
include('shared/CommonManager.php');
if (isset($_GET['product_id'])) {
	$sql = "SELECT * FROM tbl_product where product_id=:product_id";
	$res = DbManager::fetchPDOQuery('spectra_db', $sql, [":product_id" => $_GET['product_id']])["data"];
	foreach ($res as $result) {
		$product_name = trim($result['product_name']);
		$description = trim($result['description']);
		$subass_req = trim($result['subassembly_req']);
		$mlfb_num = trim($result['mlfb_num']);
	}
}
include('header.php');
include('menu_spectra.php');
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
		<div class="pagehead">
			<div class="row">
				<div class="col-sm-4">
					<h5>Product List</h5>
				</div>
				<div class="col-sm-8">
					<div class="tab float-sm-right">
						<!-- <a href="#"  class="tablinks" ><i class="fa fa-info-circle"></i> </a>
						<a href="#"  class="tablinks" ><i class="fa fa-money"></i> </a>
						<a href="#"  class="tablinks gpf" ><i class="fa fa-database"></i> </a>
						<a href="#"  class="tablinks income" ><i class="fa fa-edit"></i></a>
						<a href="#" class="tablinks" ><i class="fa fa-line-chart"></i> </a>-->
					</div>            
				</div>
			</div>
		</div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
    <form role="form"  name="myForm" method="POST" action="add_product_action.php" enctype="multipart/form-data" name="add-form" id="add-form">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-12">
            <!-- Default box -->
            <div class="framecontent">
            
			<div class="row">
				<div class="col-md-12">
					<div class="blockhead">
					<h3><i class="fa fa-map-marker"></i> Add Product </h3>
					</div>
				</div>
			   </div>
			<div class="card">
			   <div class="card-body formarea smpadding">
			   <br></br>
               <input type="hidden" name="update_type" id="update_type" value="<?php echo (isset($_GET['product_id'])) ? "edit" : "add"; ?>">
                <input type="hidden" name="product_id" id="product_id" value="<?php echo (isset($_GET['product_id'])) ? $_GET['product_id'] : ""; ?>">
			      <div class="row">
                    <div class="col-md-2">
					     <label>Product Name</label>
				    </div>
				    <div class="col-md-4">
				       	<input type="text" class="form-control" name="product_name" value="<?php echo (isset($product_name)) ? $product_name : ""; ?>" required/>
				    </div>
                    <div class="col-md-2">
					     <label>Description</label>
				    </div>
				    <div class="col-md-4">
					    <textarea class="form-control" name="product_desc" id="product_desc"> <?php if (!empty($description)) {
                                                echo $description;
                                            } ?></textarea>
				    </div>
				
			      </div>
				  <div class="row">
				  <div class="col-md-2">
					     <label>SubAssembly Required</label>
				    </div>
				    <div class="col-md-4">
					<select name="subassembly_req" class="form-control" id="subassembly_req">
                            <option value="">Select Subassembly</option>
                            <option value="Y" <?php if($subass_req == 'Y'){ echo "selected";} ?>>Yes</option>
                            <option value="N" <?php if($subass_req == 'N'){ echo "selected";} ?>>No</option> 

                        </select>
				    </div>
                                      
                                      <div class="col-md-2">
                                        <label>MLFB Number</label>  
                                          
                                          </div>
                                      <div class="col-md-4">
                                          <textarea class="form-control" name="mlfb_num" id="mlfb_num"> <?php if (!empty($mlfb_num)) {
                                                echo $mlfb_num;
                                            } ?></textarea>
                                      </div>
				  </div>
			   </div>
              
            </div>
			<div class="card">
			   <div class="card-body formarea smpadding">
               <button type="submit" class="btn btn-primary">Submit</button>
                   <span class="pull-right">                   
<span class="pull-right"><button type="reset" class="btn btn-sm btn-primary">Reset</button>	&nbsp;&nbsp;<a class="btn btn-sm btn-primary" href="Productlist.php" >	<i class="fa fa-chevron-left"></i> Back</a> &nbsp;&nbsp;</span> 
               </div>
            </div>
			
			
			
			</div>

          </div>
        </div>
      </div>
    </section>
</form>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
<?php include('footer.php'); ?>
 
<script>
$(document).ready(function(){
	$(".icon-input-btn").each(function(){
        var btnFont = $(this).find(".btn").css("font-size");
        var btnColor = $(this).find(".btn").css("color");
      	$(this).find(".fa").css({'font-size': btnFont, 'color': btnColor});
	}); 
});
</script>
<script>
  $(function () {
var table=  $('#example1').DataTable({
    'paging'      : true,
	 'lengthChange': true,
      'searching'   : true,
      'ordering'    : false,
      'info'        : true,
	  'stateSave' : true,
      'autoWidth'   : false,
	  'serverSide' : false,
	  'language': {
        "lengthMenu": "_MENU_ per page",
        "zeroRecords": "No records found",
        "info": "Showing <b>_START_ to _END_</b> (of _TOTAL_)",
        "infoFiltered": "",
        "infoEmpty": "No records found",
        "processing": '<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i>'
    },
	  'destroy': true,
	    "sAjaxSource": "load_productlist.php",
         "aoColumns": [
                    {mData: 'sr_no'},
					{mData: 'procuct_name'},
					{mData: 'description'},
                 
                   
				
					{mData: 'action'}
                ]
			
			 
    
    })
	;
  });
</script>
<script>
$(function() {
//Date range picker
    $('#reservationdate,#reservationdate1').datetimepicker({
       format: "DD/MM/YYYY"
    });
    //Date range picker
   
	var Accordion = function(el, multiple) {
		this.el = el || {};
		this.multiple = multiple || false;

		// Variables privadas
		var links = this.el.find('.link');
		var links1 = this.el.find('.link1');
		// Evento
		links.on('click', {el: this.el, multiple: this.multiple}, this.dropdown)
		links1.on('click', {el: this.el, multiple: this.multiple}, this.dropdown1)
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