<?php 
session_start();
$user = $_SESSION['username'];
include('shared/CommonManager.php');
ini_set('display_errors', 0);
error_reporting(E_ERROR | E_WARNING | E_PARSE); 
$product_id=array();
$stage_id_arr = array();
if(isset($_GET['station_id'])){
$sql = "SELECT * FROM tbl_station where station_id=:station_id";
//echo $sql;exit;	
$res = DbManager::fetchPDOQuery('spectra_db', $sql, [":station_id" =>$_GET['station_id']])["data"];
// echo "<pre>";
// print_r($res);
// echo "<pre>";
foreach ($res as $result)
{
  $station_name=trim($result['station_name']);
  $stage_id=trim($result['stage_id']);
  $product_id=explode(",",($result['product_id']));
  $machine_name=trim($result['Machine_name']);
   if($stage_id != ""){
    $stage_id_arr= explode(",",$stage_id);

   }
}
}
//print_r($product_id); print_r($stage_id_arr);
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
            <h5>Station List</h5>
          </div>
          <div class="col-sm-8">
		  <div class="tab float-sm-right">
  <!-- <a href="#"  class="tablinks" ><i class="fa fa-info-circle"></i> </a>
  <a href="#"  class="tablinks" ><i class="fa fa-money"></i> </a>
  	<a href="#"  class="tablinks gpf" ><i class="fa fa-database"></i> </a>
    <a href="#"  class="tablinks income" ><i class="fa fa-edit"></i></a>
	  <a href="#" class="tablinks" ><i class="fa fa-line-chart"></i> </a> -->
</div>
            
          </div>
        </div>
		</div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
    <form role="form"  name="myForm" method="POST" action="add_station_action.php" enctype="multipart/form-data" name="add-form" id="add-form">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-12">
            <!-- Default box -->
            <div class="framecontent">
            
			<div class="row">
				<div class="col-md-12">
					<div class="blockhead">
					<h3><i class="fa fa-map-marker"></i> Add Station </h3>
					</div>
				</div>
			   </div>
			<div class="card">
			   <div class="card-body formarea smpadding">
			   <br></br>
               <input type="hidden" name="update_type" id="update_type" value="<?php echo (isset($_GET['station_id'])) ? "edit" : "add"; ?>">
                <input type="hidden" name="station_id" id="station_id" value="<?php echo (isset($_GET['station_id'])) ? $_GET['station_id'] : ""; ?>">
			      <div class="row">
                    <div class="col-md-2">
					     <label>Station Name</label>
				    </div>
				    <div class="col-md-4">
				       	<input type="text" class="form-control" name="station_name" value="<?php echo (isset($station_name)) ? $station_name : ""; ?>" required/>
				    </div>
            <div class="col-md-2">
					     <label>Select Product</label>
				    </div>
				    <div class="col-md-4 mulitjs">
             <select name="product_id[]"  id="multiple-checkboxes" multiple="multiple"  class="form-control">
                
				    <?php  $product_query="select product_id,product_name from tbl_product";  
//echo $product_query;exit;					
            $rs= DbManager::fetchPDOQueryData('spectra_db', $product_query)["data"];
            foreach ($rs as $res1){
              if(in_array(trim($res1['product_id']),$product_id)){
                echo "<option value='".trim($res1['product_id'])."' selected>".trim($res1['product_name'])."</option>";

              }
              else{
               echo "<option value='".trim($res1['product_id'])."'>".trim($res1['product_name'])."</option>";

              }
            }
            
            ?>
            </select>
            </div>
                    
				
			      </div>
                  <div class="row">
                  <div class="col-md-2">
					     <label>Stages</label>
				    </div>
				    <div class="col-md-4">
					   <?php
                          $sql_stage = "SELECT * FROM tbl_stage";
                         
                          $query_stage = DbManager::fetchPDOQueryData('spectra_db', $sql_stage)["data"];
  // print_r($stage_id_arr);
   //echo trim(odbc_result($query_stage, 'stage_name'));
                          echo "<ul>";
                          $i=0;
                          foreach ($query_stage as $qs)
                           {  
                              
							 if(count($stage_id_arr) > 0){
								 if(in_array(trim($qs['stage_id']), $stage_id_arr))
                               {
                                $checked ="checked";
						
                               }
                               else{
                                $checked =""; 
                               }
								   }	else{
									  
									   $checked =""; 
								  } 
									  ?>
                         <li> <input type='checkbox' name='stages[]'  value="<?php echo trim($qs['stage_id']); ?>" <?php echo $checked;?>><?php echo trim($qs['stage_name'])."(".trim($qs['stage_type']).")";?></li>
                          <?php  $i++;  
                            }//end while
                          
                          echo"</ul>";
                       
                       ?>
				    </div>
            <div class="col-md-2">
            <label>Machine Name</label>
            </div>
            <div class="col-md-4">
             <input type="text" name="machine_name" class="form-control" id="machine_name" value="<?php echo $machine_name;?>">

            </div>
                  </div>
				  <div class="row">
                  
				   </div>
                </div>
              
            </div>
			<div class="card">
			   <div class="card-body formarea smpadding">
               <button type="submit" class="btn btn-primary">Submit</button>
                   <span class="pull-right">                   
<span class="pull-right"><button type="reset" class="btn btn-sm btn-primary">Reset</button>	&nbsp;&nbsp;<a class="btn btn-sm btn-primary" href="Stationlist.php" >	<i class="fa fa-chevron-left"></i> Back</a> &nbsp;&nbsp;</span> 
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
  $(document).ready(function() {
        $('#multiple-checkboxes').multiselect();
    });
//Date range picker
$('#multiple-checkboxes').multiselect();
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
<script src="js/bootstrap-multiselect.js"></script>
  <link rel="stylesheet" href="css/bootstrap-multiselect.css">

</body>
</html>
