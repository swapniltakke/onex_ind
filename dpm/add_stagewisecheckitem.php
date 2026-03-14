<?php 
session_start();
$user = $_SESSION['username'];
include('shared/CommonManager.php');
$sql = "SELECT * FROM tbl_stage";
$res = DbManager::fetchPDOQueryData('spectra_db', $sql)["data"];

$sql1 = "SELECT * FROM tbl_checklist";
$res1 = DbManager::fetchPDOQueryData('spectra_db', $sql1)["data"];

$sql2 = "SELECT * FROM tbl_product";
$res2 = DbManager::fetchPDOQueryData('spectra_db', $sql2)["data"];

$sql3 = "SELECT * FROM tbl_station";
$res3 = DbManager::fetchPDOQueryData('spectra_db', $sql3)["data"];

$check_req = '';
$check_req = '';
$station_id = array();
$product_id = array();

if (isset($_REQUEST['id'])) {
  $sql_check ="SELECT s.stage_id,s.stage_name,c.id,c.checklist_item,c1.text_req,c1.check_req,c1.product_id,c1.station_id,c1.text_lable_names FROM tbl_checklistdetails c1 inner join tbl_stage s on s.stage_id=c1.stage_id inner join tbl_checklist c on c.id=c1.checklist_id where check_id=:check_id";
  $res_check = DbManager::fetchPDOQuery('spectra_db', $sql_check, [":check_id" => $_REQUEST['id']])["data"];
  foreach ($res_check as $data) {
    $item_id = $data['id'];
    $stage_id = $data['stage_id'];
    $text_req = $dat['text_req'];
    $check_req = $data['check_req'];
    $product_id = $data['product_id'];
    $station_id = explode(',',$data['station_id']);
    $text_lable_name = $data['text_lable_names'];
  }
}
include('header.php');
include('menu_spectra.php');
?>
<style>
  .select2-container--default .select2-selection--multiple .select2-selection__choice {
    background-color: #129bba;
    border: 1px solid #aaa;
    border-radius: 4px;
    cursor: default;
    float: left;
    margin-right: 5px;
    margin-top: 5px;
    padding: 0 5px;
}
</style>

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
    <form role="form"  name="myForm" method="POST" action="stagewisecheck_action.php" enctype="multipart/form-data" name="add-form" id="add-form">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-12">
            <!-- Default box -->
            <div class="framecontent">
            
			<div class="row">
				<div class="col-md-12">
					<div class="blockhead">
					<h3><i class="fa fa-map-marker"></i> Add Stagewise Checklist </h3>
					</div>
				</div>
			   </div>
			<div class="card">
			   <div class="card-body formarea smpadding">
			   <br></br>
               <input type="hidden" name="update_type" id="update_type" value="<?php echo (isset($_GET['id'])) ? "edit" : "add"; ?>">
                <input type="hidden" name="check_id" id="check_id" value="<?php echo (isset($_GET['id'])) ? $_GET['id'] : ""; ?>">
			      <div class="row">
				    <div class="col-md-2">
					     <label>Select Product: <font color="red">*</font></label>
				    </div>
				    <div class="col-md-4">
              <select name="product_id" class="select2 form-control" id="product_id">
                      <option value="">Select Product</option>
                      <?php foreach ($res2 as $product) { 
                          if ($product_id == $product['product_id']) {
                            echo '<option value="'.$product['product_id'].'" selected>'.$product['product_name'].'</option>';
                          } else{
                              echo '<option value="'.$product['product_id'].'">'.$product['product_name'].'</option>';
                          }
                      } ?>
              </select>
            </div>
						
						
						    <div class="col-md-2">
					     <label>Select station: <font color="red">*</font></label>
				    </div>
				    <div class="col-md-4">
                    <select name="station_id[]" multiple class="select2 form-control" id="station_id">
                            <option value="">Select station</option>
                            <?php foreach ($res3 as $station) { 
                                if (in_array(trim($station['station_id']),$station_id)) {
                                  echo '<option value="'.$station['station_id'].'" selected>'.$station['station_name'].'</option>';
                                } else{
                                    echo '<option value="'.$station['station_id'].'">'.$station['station_name'].'</option>';
                                }
                            } ?>
                        </select>
                      </div>
			      </div>
            <br>
            <div class="row">
			    <div class="col-md-2">
					     <label>Enter Stage : <font color="red">*</font></label>
				    </div>
				    <div class="col-md-4">
                    <select name="stage_id" class="select2 form-control" id="stage_id">
                            <option value="">Select Stage</option>
                            <?php foreach ($res as $stage) { 
                                if ($stage_id == $stage['stage_id']) {
                                  echo '<option value="'.$stage['stage_id'].'" selected>'.$stage['stage_name'].($stage['stage_type']).'</option>';
                                } else{
                                    echo '<option value="'.$stage['stage_id'].'">'.$stage['stage_name'].($stage['stage_type']).'</option>';
                                }
                            } ?>
                        </select>
                      </div>
			 <div class="col-md-2">
					     <label>Checklist Item :</label>
				    </div>
				    <div class="col-md-4">
                    <select name="checklist_id" class="select2 form-control" id="checklist_id">
                            <option value="">Select Checklist</option>
                            <?php foreach ($res1 as $checklist) { 
                                if ($item_id == $checklist['id']) {
                                  echo '<option value="'.$checklist['id'].'" selected>'.$checklist['checklist_item'].'</option>';
                                } else{
                                    echo '<option value="'.$checklist['id'].'">'.$checklist['checklist_item'].'</option>';
                                }
                            } ?>
                        </select>	
				    </div>
        
         

            </div>
            <br>
			<div class="row">
			    <div class="col-md-2">
					     <label>How Many Text is Required :</label>
				    </div>
            <div class="col-md-4">
					     <input type="text" name="text_req" class="form-control" value="<?php echo (isset($text_req)) ? $text_req : "0"; ?>">
				    </div>
            <div class="col-md-2">
					     <label>Text Lables :</label>
				    </div>
            <div class="col-md-4">
					     <input type="text" name="text_lable_name" class="form-control" value="<?php echo (isset($text_lable_name)) ? $text_lable_name : " "; ?>">
				    </div>
			  
			</div>
      <br>
      <div class="row">
      <div class="col-md-2">
					     <label>Checkbox Required Yes/No :</label>
				    </div>
            <div class="col-md-4">
               <input type="radio"  id="radio1" name="check_req" value="Y" <?php if($check_req == 'Y'){ echo "checked"; } ?> >Yes
               <input type="radio"  id="radio2" name="check_req" value="N" <?php if($check_req == 'N'){ echo "checked"; } ?>>No

              </div>

      </div>
                
			   </div>
              
            </div>
			<div class="card">
			   <div class="card-body formarea smpadding">
               <button type="submit" class="btn btn-primary">Submit</button>
                   <span class="pull-right">                   
<span class="pull-right"><button type="reset" class="btn btn-sm btn-primary">Reset</button>	&nbsp;&nbsp;<a class="btn btn-sm btn-primary" href="stagewisechecklistdet.php" >	<i class="fa fa-chevron-left"></i> Back</a> &nbsp;&nbsp;</span> 
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