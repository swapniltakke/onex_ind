<?php 
session_start();
$user=$_SESSION['username'];

/*if (!isset($_SESSION['username']) || $_SESSION['username'] == '')
{

			  
        echo '<script type="text/JavaScript">';
					//echo 'alert("Number of parameters not matched");';
					echo 'top.window.document.location="logout.php"';
					echo '</script>';
					exit();
}*/
include('header.php');
include('menu_spectra.php');
include 'DatabaseConfig.php';

if(isset($_REQUEST['save_product']))
{
	//echo $_FILES["file_product"].PATHINFO_DIRNAME;
	
  //$_FILES["file_product"]["name"];
	
	//exit;

$file = $_FILES['file_product']['tmp_name'];

 $handle = fopen($file, "r");
 $c = 0;

 
while(($filesop = fgetcsv($handle, 1000, ",")) !== false)
           {
 //$sr_no = $filesop[0];
 $checklist_type = $filesop[0];
 $activity=$filesop[1];
 $chk_box=$filesop[2];
 $text_req=$filesop[3];
 $product=$filesop[5];
 $new_station_no=$filesop[6];
 $label_text = $filesop[4];
 $StageName = $filesop[7]; 
 if($c != 0)
 {
 
 $sql = "insert into tbl_master_checklist(check_type,check_item,check_req,text_req,product,new_station,label_text,StageName) values ('$checklist_type','$activity','$chk_box','$text_req','$product','$new_station_no','$label_text','$StageName')";
 //echo $sql; exit;
 
 $stmt = odbc_exec($conn,$sql);
 }
 
 //mysqli_stmt_execute($stmt);

$c = $c + 1;
  }

   if($sql){
      echo "sucess";
    } 
else
{
   echo "Sorry! Unable to impo.";
 }




}


?>
<style>
.text-wrap{
    white-space:normal;
}
.width-200{
    width:200px;
}

</style>



<!--<span class="icon-input-btn">
     <div class="modal-body">
                  
                <label>Upload CSV File</label>

<input type="file" id="file_product" name="file_product" class="form-control">
                </div>

                <div class="modal-footer">

                    

                    <input type="submit" id="save_product"  name="save_product"value="Save" class="btn btn-primary">

                </div>
                </span>-->
                
                 
                

  <!--<div class="modal fade" id="SampleModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">

        <div class="modal-dialog">

            <div class="modal-content">

                <div class="modal-header">
 <h4 class="modal-title" id="myModalLabel"><center>Add Assembly Checklist</center></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>

                   

                </div>

                <div class="modal-body">
                  
                <label>Upload CSV File</label>

<input type="file" id="file_product" name="file_product" class="form-control">
                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>

                    <input type="submit" id="save_product"  name="save_product"value="Save" class="btn btn-primary">

                </div>

            </div>

        </div>

    </div>-->

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

<form id="formid" enctype="multipart/form-data" method="post">
    <!-- Main content -->
    
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-12">
            <!-- Default box -->
            <div class="framecontent">
               <div class="new_buttons float-sm-right">
			   
	
              <!-- <a href="add_productlist.php"><span class="icon-input-btn">
		<i class="fa fa-plus text-success"></i> 
		<input type="submit"  class="btn btn-default" value="New">
	</span></a>-->
    
    
	
  <!--  <span class="icon-input-btn">
		<i class="fa fa-retweet text-warning"></i> 
		<input type="submit"  id="btnShow" class="btn btn-default" value="Import">
	</span>-->
	<!--<span class="icon-input-btn">
		<i class="fa fa-remove text-danger"></i> 
		<input type="submit" class="btn btn-default" value="Close">
	</span>-->

			   
			   
				
</div>
              
			
<br>	</br>		
			
		
			
			
			
			
			<div class="row">
				<div class="col-md-12">
					<div class="blockhead">
					<h3><i class="fa fa-map-marker"></i> Master Checklist Details</h3>
					</div>
				</div>
			   </div>
			<div class="card">
			   <div class="card-body formarea smpadding">


         <div class="row">
               <div class=" col-md-3">
					     <label>Upload CSV File: <font color="red">*</font></label>
				    </div>
				    <div class="col-md-4">
                    <!--<input type="file" name="upload_file" > -->
                    <input type="file" id="file_product" name="file_product" class="">
                    
                    </div>
				<div class="col-md-3">
                <!--<input type="submit" name="submit" > -->
                <input type="submit" id="save_product"  name="save_product" value="Save" class="btn btn-primary">
                </div>
			   </div>
    
			   
			   <div class="row">
				<div class="col-md-12">
					<div class="display_table">
					<table id="example1" class="table table-bordered table-striped">
                  <thead>
                  <tr>
                    <th>Sr. No.</th>
                    <th>Check Type</th>
                    <th>Activity</th>
		            <th>Checkbox</th>
                    <th>Text Req.</th>
					<th>Text Lable</th>
                    <th>Product</th>
                    <th>New Station</th>
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
    </form>
  </div>
  <!-- /.content-wrapper -->

  <?php include('footer.php') ?>
<script>
$(document).ready(function(){
	$(".icon-input-btn").each(function(){
        var btnFont = $(this).find(".btn").css("font-size");
        var btnColor = $(this).find(".btn").css("color");
      	$(this).find(".fa").css({'font-size': btnFont, 'color': btnColor});
	}); 
});

$("#save_product5").click(function () {
 
  var file_product= $('#file_product').val();
  
  
 // var file_product= document.getElementById("file_product").files[0].name;
  


//alert(file_product);
//exit;

  $.ajax({
            url: 'file_upload_assembly_checklist.php',
            type: "POST",
            data: {
              file_product: file_product  
            },
            success: function (data) {
                // does some stuff here...
				alert(data);
				
            }
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
	    "sAjaxSource": "get_assembly_checklist.php",
         "aoColumns": [
                    {mData: 'sr_no'},
					{mData: 'check_type'},
					{mData: 'activity'},
                    {mData: 'chk_box'},
                    {mData: 'text_req'},
					{mData: 'label_text'},
                    {mData: 'product'},
                    {mData: 'new_station_no'},
	
					{mData: 'action'}
                ],
                columnDefs: [
                {
                    render: function (data, type, full, meta) {
                        return "<div class='text-wrap width-200'>" + data + "</div>";
                    },
                    targets: 4
                }
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
 $(document).ready(function () {

            $("#btnShow").click(function () {

                $('#SampleModal').modal('show');

            });

        });
</script>
</body>
</html>
