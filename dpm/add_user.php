<?php 
session_start();
$user = $_SESSION['username'];
include('shared/CommonManager.php');
$user_string = "";
$user_name ="";
$pass = "";

if(isset($_GET['user_id'])){
	$sql = "SELECT * FROM tbl_user_login where user_id=".$_GET['user_id'];
	$res = DbManager::fetchPDOQueryData('spectra_db', $sql)["data"];
	foreach ($res as $result)
	{
		$decrypted_qr_id = $decrypted_password = "";
		if ($result['user_string'] != "") {
			$decrypted_qr_id = SharedManager::decrypt_password(trim($result['user_string']));
		}
		$decrypted_password = SharedManager::decrypt_password(trim($result['password']));
		$user_string = $decrypted_qr_id;
		$user_name = trim($result['user_name']);
		$pass = $decrypted_password;
		$role_id = trim($result['role_id']);
	}
}
$sql_role = "select * from tbl_roles";
$query_role = DbManager::fetchPDOQueryData('spectra_db', $sql_role)["data"];
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
            <h5>Add User</h5>
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
    <form role="form"  name="myForm" method="POST" action="add_user_action.php" enctype="multipart/form-data" name="add-form" id="add-form">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-12">
            <!-- Default box -->
            <div class="framecontent">
            
			<div class="row">
				<div class="col-md-12">
					<div class="blockhead">
					<h3><i class="fa fa-map-marker"></i> Add User </h3>
					</div>
				</div>
			   </div>
			<div class="card">
			   <div class="card-body formarea smpadding">
			   <br></br>
               <input type="hidden" name="update_type" id="update_type" value="<?php echo (isset($_GET['user_id'])) ? "edit" : "add"; ?>">
                <input type="hidden" name="user_id" id="user_id" value="<?php echo (isset($_GET['user_id'])) ? $_GET['user_id'] : ""; ?>">
			      <div class="row">
                    <div class="col-md-2">
					     <label>Select  Role : <font color="red">*</font></label>
				    </div>
				    <div class="col-md-4">
                        <select name="role_id" class="form-control" id="role_id" required>
                            <option value="">Select Role</option>
                            <?php foreach ($query_role as $qr) { 
                                if($role_id == $qr['role_id']){
                             echo '<option value="'.$qr['role_id'].'" selected>'.$qr['role_name'].'</option>';
                                }
                                else{
                                    echo '<option value="'.$qr['role_id'].'">'.$qr['role_name'].'</option>';
 
                                }

                                 } ?>

                        </select>
				       
				    </div>
                    <div class="col-md-2">
					     <label> Qr ID String:</label>
				    </div>
				    <div class="col-md-4">
                    <input type="text" name="qr_id" id="qr_id" class="form-control" value="<?php echo $user_string; ?>" >
				    </div>
				
			      </div>
                  <div class="row">
                  <div class="col-md-2">
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
