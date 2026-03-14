<?php 
session_start();
$user = $_SESSION['username'];
include('shared/CommonManager.php');
$error_id = "";
$error_name = "";
$description = "";

if (isset($_GET['error_id'])) {
	$sql = "SELECT * FROM tbl_error where error_id=:error_id";
	$res = DbManager::fetchPDOQuery('spectra_db', $sql, [":error_id" => $_GET['error_id']])["data"];
	foreach ($res as $result) {
		$error_name = trim($result['error_name']);
		$description = trim($result['description']);
		$error_type = trim($result['error_type']);
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
            <h5>Add Basic Error Description</h5>
          </div>
          <div class="col-sm-8">
		  <div class="tab float-sm-right">
  <!--<a href="#"  class="tablinks" ><i class="fa fa-info-circle"></i> </a>
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
    <form role="form"  name="myForm" method="POST" action="add_error_action.php" enctype="multipart/form-data" name="add-form" id="add-form">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-12">
            <!-- Default box -->
            <div class="framecontent">
            
			<div class="row">
				<div class="col-md-12">
					<div class="blockhead">
					<h3><i class="fa fa-map-marker"></i> Add Error </h3>
					</div>
				</div>
			   </div>
			<div class="card">
			   <div class="card-body formarea smpadding">
			   <br></br>
               <input type="hidden" name="update_type" id="update_type" value="<?php echo (isset($_GET['error_id'])) ? "edit" : "add"; ?>">
                <input type="hidden" name="error_id" id="error_id" value="<?php echo (isset($_GET['error_id'])) ? $_GET['error_id'] : ""; ?>">
			      <div class="row">
                  <div class="col-md-2">    
                  <label>Error Name</label>
				    </div>
				    <div class="col-md-4">
                    <input type="text" name="error_name" id="error_name" class="form-control" value="<?php echo $error_name; ?>" required >
				    </div>
                    <div class="col-md-2">
					     <label> Description </label>
				    </div>
				    <div class="col-md-4">
                    <input type="text" name="description" id="description" class="form-control" value="<?php echo $description; ?>" >
				    </div>
                    <div class="col-md-2">
					     <label>Select  Role : <font color="red">*</font></label>
				    </div>
				    <div class="col-md-4">
                        <select name="error_type" class="form-control" id="error_type" required>
                            <option value="">Select Role</option>
                            <option <?php if(trim($error_type) == "Material Shortage") { ?> selected="selected" <?php } ?> value="Material Shortage">Material Shortage</option>
                            <option <?php if(trim($error_type) == "Quality") { ?> selected="selected" <?php } ?> value="Quality">Quality</option>
                            <option <?php if(trim($error_type) == "R&D/BOM Error") { ?> selected="selected" <?php } ?> value="R&D/BOM Error">R&D/BOM Error</option>
                            <option <?php if(trim($error_type) == "Manufacture & Infrastructure") { ?> selected="selected" <?php } ?> value="Manufacture & Infrastructure">Manufacture & Infrastructure</option>
                            <option <?php if(trim($error_type) == "EHS") { ?> selected="selected" <?php } ?> value="EHS">EHS</option>
                            <option <?php if(trim($error_type) == "Fixture/Timing") { ?> selected="selected" <?php } ?> value="Fixture/Timing">Fixture/Timing</option>
                            <option <?php if(trim($error_type) == "Other") { ?> selected="selected" <?php } ?> value="Other">Other</option>
                        </select>				       
				    </div>
			      </div>
                  <div class="row">
                  <!-- <div class="col-md-2">
					     <label> Username:</label>
				    </div>
				    <div class="col-md-4">
                    <input type="text" name="username" id="username" class="form-control" value="<?php echo $user_name; ?>" required >
				    </div>
                    <div class="col-md-2">
					     <label> Password:</label>
				    </div>
				    <div class="col-md-4">
                    <input type="text" name="password" id="password" class="form-control" value="<?php echo $pass; ?>" required>
				    </div> -->
                </div>                  
			   </div>
              
            </div>
			<div class="card">
			   <div class="card-body formarea smpadding">
               <button type="submit" class="btn btn-primary">Submit</button>
                   <span class="pull-right">                   
<span class="pull-right"><button type="reset" class="btn btn-sm btn-primary">Reset</button>	&nbsp;&nbsp;<a class="btn btn-sm btn-primary" href="Errorlist.php" >	<i class="fa fa-chevron-left"></i> Back</a> &nbsp;&nbsp;</span> 
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
