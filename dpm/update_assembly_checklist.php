<?php 
session_start();
$user=$_SESSION['username'];
include 'DatabaseConfig.php';
if(isset($_GET['id'])){
$sql = "SELECT * FROM tbl_master_checklist where check_id=".$_GET['id'];
//echo $sql;exit;	
$res = odbc_exec($conn, $sql);
while(odbc_fetch_row($res))
{
  $check_type=trim(odbc_result($res, 'check_type'));
  $activity=trim(odbc_result($res, 'check_item'));
  $chk_box=trim(odbc_result($res, 'check_req'));
  $text_req =trim(odbc_result($res,'text_req'));
  $label_text=trim(odbc_result($res,'label_text'));
  $product =trim(odbc_result($res,'product'));
  $new_station_no =trim(odbc_result($res,'new_station'));
}
}
include('header.php');
include('menu_supervisor.php');
?>



  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
	  <div class="pagehead">
        <div class="row">
          <div class="col-sm-4">
            <h5>Assembly Checklist</h5>
          </div>
          <div class="col-sm-8">
		  <div class="tab float-sm-right">
  <a href="#"  class="tablinks" ><i class="fa fa-info-circle"></i> </a>
  <a href="#"  class="tablinks" ><i class="fa fa-money"></i> </a>
  	<a href="#"  class="tablinks gpf" ><i class="fa fa-database"></i> </a>
    <a href="#"  class="tablinks income" ><i class="fa fa-edit"></i></a>
	  <a href="#" class="tablinks" ><i class="fa fa-line-chart"></i> </a>
</div>
            
          </div>
        </div>
		</div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
    <form role="form"  name="myForm" method="POST" action="add_edit_assembly_checklist.php" enctype="multipart/form-data" name="add-form" id="add-form">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-12">
            <!-- Default box -->
            <div class="framecontent">
            
			<div class="row">
				<div class="col-md-12">
					<div class="blockhead">
					<h3><i class="fa fa-map-marker"></i> Update Checklist Record </h3>
					</div>
				</div>
			   </div>
			<div class="card">
			   <div class="card-body formarea smpadding">
			   <br></br>
               <input type="hidden" name="update_type" id="update_type" value="<?php echo (isset($_GET['id'])) ? "edit" : "add"; ?>">
                <input type="hidden" name="id" id="id" value="<?php echo (isset($_GET['id'])) ? $_GET['id'] : ""; ?>">
			      <div class="row">
                    <div class="col-md-2">
					     <label>Checklist Type</label>
				    </div>
				    <div class="col-md-4">
				   
						   <select name="check_type" class="form-control" id="check_type">
                            <option value="">Select Checklist Type</option>
                            <option value="Subassembly" <?php if($check_type == 'Subassembly'){ echo "selected";} ?>>Subassembly</option>
                            <option value="Assembly" <?php if($check_type == 'Assembly'){ echo "selected";} ?>>Assembly</option> 
							<option value="Testing" <?php if($check_type == 'Testing'){ echo "selected";} ?>>Testing</option> 

                        </select>
					</div>
                    <div class="col-md-2">
					     <label>Activity</label>
				    </div>
				    <div class="col-md-4">
				       	<input type="text" class="form-control" name="activity" value="<?php echo (isset($activity)) ? $activity : ""; ?>" required/>
				    </div>
				
			      </div>
				  <div class="row">
				  <div class="col-md-2">
					     <label>Checkbox</label>
				    </div>
				    <div class="col-md-4">
					<select name="chk_box" class="form-control" id="chk_box">
                            <option value="">Select</option>
                            <option value="Y" <?php if($chk_box == 'Y'){ echo "selected";} ?>>Yes</option>
                            <option value="N" <?php if($chk_box == 'N'){ echo "selected";} ?>>No</option> 

                        </select>
				    </div>
                                      
                                      <div class="col-md-2">
                                        <label>Text Req</label>  
                                          
                                          </div>
                                      <div class="col-md-4">
				       	<input type="text" class="form-control" name="text_req" value="<?php echo (isset($text_req)) ? $text_req : ""; ?>" />
				    </div>
                    
                     <div class="col-md-2">
                                        <label>Product</label>  
                                          
                                          </div>
                                      <div class="col-md-4">
				       	<input type="text" class="form-control" name="product" value="<?php echo (isset($product)) ? $product : ""; ?>" required/>
				    </div>
                    
                      <div class="col-md-2">
                                        <label>New Station No.</label>  
                                          
                                          </div>
                                      <div class="col-md-4">
				       	<input type="text" class="form-control" name="new_station_no" value="<?php echo (isset($new_station_no)) ? $new_station_no : ""; ?>" required/>
				    </div>
                    
				  </div>
			   </div>
              
            </div>
			<div class="card">
			   <div class="card-body formarea smpadding">
               <button type="submit" class="btn btn-primary">Submit</button>
                   <span class="pull-right">                   
<span class="pull-right"><button type="reset" class="btn btn-sm btn-primary">Reset</button>	&nbsp;&nbsp;<a class="btn btn-sm btn-primary" href="load_assembly_checklist.php" >	<i class="fa fa-chevron-left"></i> Back</a> &nbsp;&nbsp;</span> 
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
