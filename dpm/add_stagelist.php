<?php 
session_start();
$user = $_SESSION['username'];
include('shared/CommonManager.php');
$stage_type = "";
if (isset($_GET['stage_id'])) {
	$sql = "SELECT * FROM tbl_stage where stage_id=:stage_id";
	$res = DbManager::fetchPDOQuery('spectra_db', $sql, [":stage_id" => $_GET['stage_id']])["data"];
	foreach ($res as $data) {
		$stage_name = trim($data['stage_name']);
		$description = trim($data['description']);
		$stage_type = trim($data['stage_type']);
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
    <form role="form"  name="myForm" method="POST" action="add_stage_action.php" enctype="multipart/form-data" name="add-form" id="add-form">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-12">
            <!-- Default box -->
            <div class="framecontent">
            
			<div class="row">
				<div class="col-md-12">
					<div class="blockhead">
					<h3><i class="fa fa-map-marker"></i> Add Stage </h3>
					</div>
				</div>
			   </div>
			<div class="card">
			   <div class="card-body formarea smpadding">
			   <br></br>
               <input type="hidden" name="update_type" id="update_type" value="<?php echo (isset($_GET['stage_id'])) ? "edit" : "add"; ?>">
                <input type="hidden" name="stage_id" id="stage_id" value="<?php echo (isset($_GET['stage_id'])) ? $_GET['stage_id'] : ""; ?>">
			      <div class="row">
                    <div class="col-md-2">
					     <label>Stage Name : <font color="red">*</font></label>
				    </div>
				    <div class="col-md-4">
				       	<input type="text" class="form-control" name="stage_name" value="<?php echo (isset($stage_name)) ? $stage_name : ""; ?>" required/>
				    </div>
                    <div class="col-md-2">
					     <label>Description :</label>
				    </div>
				    <div class="col-md-4">
					    <textarea class="form-control" name="stage_desc" id="stage_desc"> <?php if (!empty($description)) {
                                                echo $description;
                                            } ?></textarea>
				    </div>
				
			      </div>
                  <div class="row">
                    <div class="col-md-2">
					     <label>Stage Type : <font color="red">*</font></label>
				    </div>
                    <div class="col-md-4">
                    <select name="stage_type" class="form-control" id="stage_type" >
						  <option value="">Select Stage Type</option>
						  <option <?php if(trim($stage_type) == "Subassembly") { ?> selected="selected" <?php } ?>  value="Subassembly">Subassembly</option>
                          <option <?php if(trim($stage_type) == "Assembly") { ?> selected="selected" <?php } ?>  value="Assembly">Assembly</option>
                          <option <?php if(trim($stage_type) == "Post Assembly") { ?> selected="selected" <?php } ?> value="Post Assembly">Post Assembly</option>
						</select>
                    </div>
                  </div>
			   </div>
              
            </div>
			<div class="card">
			   <div class="card-body formarea smpadding">
               <button type="submit" class="btn btn-primary">Submit</button>
                   <span class="pull-right">                   
<span class="pull-right"><button type="reset" class="btn btn-sm btn-primary">Reset</button>	&nbsp;&nbsp;<a class="btn btn-sm btn-primary" href="Stagelist.php" >	<i class="fa fa-chevron-left"></i> Back</a> &nbsp;&nbsp;</span> 
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
