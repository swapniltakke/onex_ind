<?php 
session_start();
$user = $_SESSION['username'];
include('shared/CommonManager.php');
$sql = "SELECT * FROM tbl_workbench";
$res = DbManager::fetchPDOQueryData('spectra_db', $sql)["data"];

if(isset($_REQUEST['w_id']))
{
//$sql_check ="SELECT m.w_id,w_type,m.workbench_id FROM tbl_manageworkbench m inner join tbl_workbench w on w.id=m.workbench_id where w_id='".$_REQUEST['w_id']."'";
$sql_check ="SELECT m.w_id,w_type,m.workbench_id FROM tbl_manageworkbench m inner join tbl_workbench w on w.id=m.workbench_id where w_id=:w_id";

//echo $sql_check;
$res_check =  DbManager::fetchPDOQuery('spectra_db', $sql_check, [":w_id" => $_REQUEST['w_id']])["data"];
// echo "<pre>";
// print_r($res_check);
// echo "</pre>";

foreach ($res_check as $result)
{		
    $w_id = trim($result['w_id']);
	$w_type = trim($result['w_type']);
	$workbench_id = trim($result['workbench_id']);

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
            <h5>Add Stage</h5>
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
    <form role="form"  name="myForm" method="POST" action="add_workbenchtype_action.php" enctype="multipart/form-data" name="add-form" id="add-form">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-12">
            <!-- Default box -->
            <div class="framecontent">
            
			<div class="row">
				<div class="col-md-12">
					<div class="blockhead">
					<h3><i class="fa fa-map-marker"></i> Add Workbench Types </h3>
					</div>
				</div>
			   </div>
			<div class="card">
			   <div class="card-body formarea smpadding">
			   <br></br>
               <input type="hidden" name="update_type" id="update_type" value="<?php echo (isset($_GET['w_id'])) ? "edit" : "add"; ?>">
                <input type="hidden" name="w_id" id="w_id" value="<?php echo (isset($_GET['w_id'])) ? $_GET['w_id'] : ""; ?>">
			      <div class="row">
				    <div class="col-md-2">
					     <label>Select Workbench: <font color="red">*</font></label>
				    </div>
				    <div class="col-md-4">
                    <select name="workbench_id" class="select2 form-control" id="workbench_id">
                            <option value="">Select Workbench</option>
                            <?php foreach ($res as $data) { 
                                if($workbench_id == $data['id']){
                             echo '<option value="'.trim($data['id']).'" selected>'.trim($data['title']).'</option>';
                                }
                                else{
                                    echo '<option value="'.trim($data['id']).'">'.trim($data['title']).'</option>';
 
                                }

                                 } ?>

                        </select>				    </div>
						
						
						    <div class="col-md-2">
					     <label>Type of Workbench: <font color="red">*</font></label>
				    </div>
				    <div class="col-md-4">
                   	 <input type="text" name="w_type" id="w_type" value="<?php echo (isset($w_type)) ? $w_type : ""; ?>" required class="form-control">
                    </div>
				  
                
                   
				
			      </div>
            <br>
           
		
                
			   </div>
              
            </div>
			<div class="card">
			   <div class="card-body formarea smpadding">
               <button type="submit" class="btn btn-primary">Submit</button>
                   <span class="pull-right">                   
<span class="pull-right"><button type="reset" class="btn btn-sm btn-primary">Reset</button>	&nbsp;&nbsp;<a class="btn btn-sm btn-primary" href="Manageworkbentchlist.php" >	<i class="fa fa-chevron-left"></i> Back</a> &nbsp;&nbsp;</span> 
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
  
</script>
<script>
$(function() {
//Date range picker
    $('#reservationdate,#reservationdate1').datetimepicker({
       format: "DD/MM/YYYY"
    });
    //Date range picker
    $('.select2').select2();
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
