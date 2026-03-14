<?php 
session_start();
$user=$_SESSION['username'];
include 'DatabaseConfig.php';
if(isset($_GET['uploadid'])){
$sql = "SELECT * FROM tbl_DailyUpload where uploadid=".$_GET['uploadid'];
//echo $sql;exit;	
$res = odbc_exec($conn, $sql);
while(odbc_fetch_row($res))
{
  $uploaddate=trim(odbc_result($res, 'uploaddate'));
  $ProductinOrder=trim(odbc_result($res, 'ProductinOrder'));
  //echo $ProductinOrder
  $SAPNo=trim(odbc_result($res, 'SAPNo'));
  $Client =trim(odbc_result($res,'Client'));
  $Product=trim(odbc_result($res, 'Product'));
  $rating=trim(odbc_result($res, 'rating'));
  $Qty=trim(odbc_result($res, 'Qty'));
  $WB =trim(odbc_result($res,'WB'));
  $ois=trim(odbc_result($res, 'ois'));
  $ODS_MSN=trim(odbc_result($res, 'ODS_MSN'));
  $FAT=trim(odbc_result($res, 'FAT'));
  $FirstShiftDrive =trim(odbc_result($res,'FirstShiftDrive'));
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
            <h5>Update Production Plan</h5>
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
    <form role="form"  name="myForm" method="POST" action="update_production_action.php" enctype="multipart/form-data" name="add-form" id="add-form">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-12">
            <!-- Default box -->
            <div class="framecontent">
            
			<div class="row">
				<div class="col-md-12">
					<div class="blockhead">
					<h3><i class="fa fa-map-marker"></i> Update Production Plan </h3>
					</div>
				</div>
			   </div>
			<div class="card">
			   <div class="card-body formarea smpadding">
			   <br></br>
               <input type="hidden" name="update_type" id="update_type" value="<?php echo (isset($_GET['uploadid'])) ? "edit" : "add"; ?>">
                <input type="hidden" name="uploadid" id="uploadid" value="<?php echo (isset($_GET['uploadid'])) ? $_GET['uploadid'] : ""; ?>">
			      <div class="row">
                    <div class="col-md-2">
					     <label>Production Order</label>
				    </div>
				    <div class="col-md-4">
				       	<input type="text" class="form-control" name="production_order" value="<?php echo (isset($ProductinOrder)) ? $ProductinOrder : ""; ?>" required/>
				    </div>
                    <div class="col-md-2">
					     <label>SAP Number</label>
				    </div>
				    <div class="col-md-4">
                    
                    <input type="text" class="form-control" name="sap_no" value="<?php echo (isset($SAPNo)) ? $SAPNo : ""; ?>" required/>

				    </div>
				
			      </div>
				  <div class="row">
				  <div class="col-md-2">
					     <label>Client</label>
				    </div>
				    <div class="col-md-4">
                    <input type="text" class="form-control" name="client" value="<?php echo (isset($Client)) ? $Client : ""; ?>" required/>

				    </div>
                                      
                                      <div class="col-md-2">
                                        <label>Product</label>  
                                          
                                          </div>
                                      <div class="col-md-4">
                                      <input type="text" class="form-control" name="product" value="<?php echo (isset($Product)) ? $Product : ""; ?>" />

                                      </div>
				  </div>
                  <div class="row">
				  <div class="col-md-2">
					     <label>Rating</label>
				    </div>
				    <div class="col-md-4">
                    <input type="text" class="form-control" name="rating" value="<?php echo (isset($rating)) ? $rating : ""; ?>" />

				    </div>
                                      
                                      <div class="col-md-2">
                                        <label>Qty</label>  
                                          
                                          </div>
                                      <div class="col-md-4">
                                      <input type="text" class="form-control" name="qty" value="<?php echo (isset($Qty)) ? $Qty : ""; ?>" />

                                      </div>
				  </div>
                  <div class="row">
				  <div class="col-md-2">
					     <label>WB</label>
				    </div>
				    <div class="col-md-4">
                    <input type="text" class="form-control" name="wb" value="<?php echo (isset($WB)) ? $WB : ""; ?>" />

				    </div>
                                      
                                      <div class="col-md-2">
                                        <label>OIS</label>  
                                          
                                          </div>
                                      <div class="col-md-4">
                                      <input type="text" class="form-control" name="ois" value="<?php echo (isset($ois)) ? $ois : ""; ?>" />

                                      </div>
				  </div>
                  <div class="row">
				  <div class="col-md-2">
					     <label>ODS_MSN</label>
				    </div>
				    <div class="col-md-4">
                    <input type="text" class="form-control" name="ods_msn" value="<?php echo (isset($ODS_MSN)) ? $ODS_MSN : ""; ?>" />

				    </div>
                                      
                                      <div class="col-md-2">
                                        <label>FAT</label>  
                                          
                                          </div>
                                      <div class="col-md-4">
                                      <input type="text" class="form-control" name="fat" value="<?php echo (isset($FAT)) ? $FAT : ""; ?>" />

                                      </div>
				  </div>
                  

			   </div>
              
            </div>
			<div class="card">
			   <div class="card-body formarea smpadding">
               <button type="submit" class="btn btn-primary">Submit</button>
                   <span class="pull-right">                   
<span class="pull-right"><button type="reset" class="btn btn-sm btn-primary">Reset</button>	&nbsp;&nbsp;<a class="btn btn-sm btn-primary" href="Uploaddailyworkshit.php" >	<i class="fa fa-chevron-left"></i> Back</a> &nbsp;&nbsp;</span> 
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
