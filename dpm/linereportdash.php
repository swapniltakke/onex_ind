<?php
session_start();
$user = $_SESSION['username'];
include('shared/CommonManager.php');
include('header.php');
include('menu_reportdash.php');

$ah_qry = "SELECT COUNT(*) AS cnt FROM tbl_DailyUpload WHERE LEFT(Product, 4) =:Product AND Progress_Status =:Progress_Status AND DATE(uploaddate) = CURDATE()";
$rs_ah = DbManager::fetchPDOQueryData('spectra_db', $ah_qry, [":Product" => "3AH0", ":Progress_Status" => "4"])["data"];
$cnt_ah = $rs_ah[0]['cnt'];

$ah_qry1 = "SELECT COUNT(*) AS cnt FROM tbl_DailyUpload WHERE LEFT(Product, 4) =:Product AND Progress_Status =:Progress_Status AND DATE(uploaddate) = CURDATE()";
$rs_ah1 = DbManager::fetchPDOQueryData('spectra_db', $ah_qry1, [":Product" => "3AH5", ":Progress_Status" => "4"])["data"];
$cnt_ah1 = $rs_ah1[0]['cnt'];

$ah_qry2 = "SELECT COUNT(*) AS cnt FROM tbl_DailyUpload WHERE LEFT(Product, 4) =:Product AND Progress_Status =:Progress_Status AND DATE(uploaddate) = CURDATE()";
$rs_ah2 = DbManager::fetchPDOQueryData('spectra_db', $ah_qry2, [":Product" => "3AK6", ":Progress_Status" => "4"])["data"];
$cnt_ah2 = $rs_ah2[0]['cnt'];
//Sion M25
$ah_qry3 = "SELECT COUNT(*) AS cnt FROM tbl_DailyUpload WHERE LEFT(Product, 8) IN (:Product) AND Progress_Status =:Progress_Status AND DATE(uploaddate) = CURDATE()";
$rs_ah3 = DbManager::fetchPDOQueryData('spectra_db', $ah_qry3, [":Product" => "'3AE55541', '3AE55542', '3AE55642', '3AE55643'", ":Progress_Status" => "4"])["data"];
$cnt_ah3 = $rs_ah3[0]['cnt'];
//Sion M31
$ah_qry4 = "SELECT COUNT(*) AS cnt FROM tbl_DailyUpload WHERE LEFT(Product, 8) IN (:Product) AND Progress_Status =:Progress_Status AND DATE(uploaddate) = CURDATE()";
$rs_ah4 = DbManager::fetchPDOQueryData('spectra_db', $ah_qry4, [":Product" => "'3AE55551','3AE55552','3AE55652','3AE55653'", ":Progress_Status" => "4"])["data"];
$cnt_ah4 = $rs_ah4[0]['cnt'];
//Sion M31+
$ah_qry5 = "SELECT COUNT(*) AS cnt FROM tbl_DailyUpload WHERE LEFT(Product, 8) IN (:Product) AND Progress_Status =:Progress_Status AND DATE(uploaddate) = CURDATE()";
$rs_ah5 = DbManager::fetchPDOQueryData('spectra_db', $ah_qry5, [":Product" => "'3AE55656','3AE56241','3AE56242','3AE56251','3AE56252','3AE56642','3AE56643','3AE56652','3AE56653','3AE56656'", ":Progress_Status" => "4"])["data"];
$cnt_ah5 = $rs_ah5[0]['cnt'];
//Sion M40
$ah_qry6 = "SELECT COUNT(*) AS cnt FROM tbl_DailyUpload WHERE LEFT(Product, 8) IN (:Product) AND Progress_Status =:Progress_Status AND DATE(uploaddate) = CURDATE()";
$rs_ah6 = DbManager::fetchPDOQueryData('spectra_db', $ah_qry6, [":Product" => "'3AE55662','3AE55666','3AE55667','3AE55668','3AE56662','3AE56666','3AE56667'", ":Progress_Status" => "4"])["data"];
$cnt_ah6 = $rs_ah6[0]['cnt'];

//$plan_qry = "select count(*) as cnt_total from tbl_checklistdetails c1 inner join tbl_product p on p.product_id=c1.product_id and p.product_name='3AH0'";
$plan_qry = "SELECT COUNT(*) AS cnt_total FROM tbl_DailyUpload WHERE LEFT(Product, 4) =:Product AND DATE(uploaddate) BETWEEN DATE_FORMAT(CURDATE(), '%Y-%m-01') AND LAST_DAY(CURDATE())";
$rs = DbManager::fetchPDOQueryData('spectra_db', $plan_qry, [":Product" => "3AH0"])["data"];
$cnt_plan = $rs[0]['cnt_total'];

$plan_qry1 = "SELECT COUNT(*) AS cnt_total FROM tbl_DailyUpload WHERE LEFT(Product, 4) =:Product AND DATE(uploaddate) BETWEEN DATE_FORMAT(CURDATE(), '%Y-%m-01') AND LAST_DAY(CURDATE())";
$rs1 = DbManager::fetchPDOQueryData('spectra_db', $plan_qry1, [":Product" => "3AH5"])["data"];
$cnt_plan1 = $rs1[0]['cnt_total'];

$plan_qry2 = "SELECT COUNT(*) AS cnt_total FROM tbl_DailyUpload WHERE LEFT(Product, 4) =:Product AND DATE(uploaddate) BETWEEN DATE_FORMAT(CURDATE(), '%Y-%m-01') AND LAST_DAY(CURDATE())";
$rs2 = DbManager::fetchPDOQueryData('spectra_db', $plan_qry2, [":Product" => "3AK6"])["data"];
$cnt_plan2 = $rs2[0]['cnt_total'];

$plan_qry3 = "SELECT COUNT(*) AS cnt_total FROM tbl_DailyUpload WHERE LEFT(Product, 8) IN (:Product) AND DATE(uploaddate) BETWEEN DATE_FORMAT(CURDATE(), '%Y-%m-01') AND LAST_DAY(CURDATE())";
$rs3 = DbManager::fetchPDOQueryData('spectra_db', $plan_qry3, [":Product" => "'3AE55541', '3AE55542', '3AE55642', '3AE55643'"])["data"];
$cnt_plan3 = $rs3[0]['cnt_total'];

$plan_qry4 = "SELECT COUNT(*) AS cnt_total FROM tbl_DailyUpload WHERE LEFT(Product, 8) IN (:Product) AND DATE(uploaddate) BETWEEN DATE_FORMAT(CURDATE(), '%Y-%m-01') AND LAST_DAY(CURDATE())";
$rs4 = DbManager::fetchPDOQueryData('spectra_db', $plan_qry4, [":Product" => "'3AE55551','3AE55552','3AE55652','3AE55653'"])["data"];
$cnt_plan4 = $rs4[0]['cnt_total'];

$plan_qry5 = "SELECT COUNT(*) AS cnt_total FROM tbl_DailyUpload WHERE LEFT(Product, 8) IN (:Product) AND DATE(uploaddate) BETWEEN DATE_FORMAT(CURDATE(), '%Y-%m-01') AND LAST_DAY(CURDATE())";
$rs5 = DbManager::fetchPDOQueryData('spectra_db', $plan_qry5, [":Product" => "'3AE55656','3AE56241','3AE56242','3AE56251','3AE56252','3AE56642','3AE56643','3AE56652','3AE56653','3AE56656'"])["data"];
$cnt_plan5 = $rs5[0]['cnt_total'];

$plan_qry6 = "SELECT COUNT(*) AS cnt_total FROM tbl_DailyUpload WHERE LEFT(Product, 8) IN (:Product) AND DATE(uploaddate) BETWEEN DATE_FORMAT(CURDATE(), '%Y-%m-01') AND LAST_DAY(CURDATE())";
$rs6 = DbManager::fetchPDOQueryData('spectra_db', $plan_qry6, [":Product" => "'3AE55662','3AE55666','3AE55667','3AE55668','3AE56662','3AE56666','3AE56667'"])["data"];
$cnt_plan6 = $rs6[0]['cnt_total'];

$act_qry = "SELECT COUNT(*) AS cnt_total FROM tbl_DailyUpload WHERE LEFT(Product, 4) =:Product AND Progress_Status =:Progress_Status AND DATE(uploaddate) BETWEEN DATE_FORMAT(CURDATE(), '%Y-%m-01') AND LAST_DAY(CURDATE())";
$rs_act = DbManager::fetchPDOQueryData('spectra_db', $act_qry, [":Product" => "3AH0", ":Progress_Status" => "4"])["data"];
$cnt_act = $rs_act[0]['cnt_total'];

$act_qry1 = "SELECT COUNT(*) AS cnt_total FROM tbl_DailyUpload WHERE LEFT(Product, 4) =:Product AND Progress_Status =:Progress_Status AND DATE(uploaddate) BETWEEN DATE_FORMAT(CURDATE(), '%Y-%m-01') AND LAST_DAY(CURDATE())";
$rs_act1 = DbManager::fetchPDOQueryData('spectra_db', $act_qry1, [":Product" => "3AH5", ":Progress_Status" => "4"])["data"];
$cnt_act1 = $rs_act1[0]['cnt_total'];

$act_qry2 = "SELECT COUNT(*) AS cnt_total FROM tbl_DailyUpload WHERE LEFT(Product, 4) =:Product AND Progress_Status =:Progress_Status AND DATE(uploaddate) BETWEEN DATE_FORMAT(CURDATE(), '%Y-%m-01') AND LAST_DAY(CURDATE())";
$rs_act2 = DbManager::fetchPDOQueryData('spectra_db', $act_qry2, [":Product" => "3AK6", ":Progress_Status" => "4"])["data"];
$cnt_act2 = $rs_act2[0]['cnt_total'];

$act_qry3 = "SELECT COUNT(*) AS cnt_total FROM tbl_DailyUpload WHERE LEFT(Product, 8) IN (:Product) AND Progress_Status =:Progress_Status AND DATE(uploaddate) BETWEEN DATE_FORMAT(CURDATE(), '%Y-%m-01') AND LAST_DAY(CURDATE())";
$rs_act3 = DbManager::fetchPDOQueryData('spectra_db', $act_qry3, [":Product" => "'3AE55541','3AE55542','3AE55642','3AE55643'", ":Progress_Status" => "4"])["data"];
$cnt_act3 = $rs_act3[0]['cnt_total'];

$act_qry4 = "SELECT COUNT(*) AS cnt_total FROM tbl_DailyUpload WHERE LEFT(Product, 8) IN (:Product) AND Progress_Status =:Progress_Status AND DATE(uploaddate) BETWEEN DATE_FORMAT(CURDATE(), '%Y-%m-01') AND LAST_DAY(CURDATE())";
$rs_act4 = DbManager::fetchPDOQueryData('spectra_db', $act_qry4, [":Product" => "'3AE55551','3AE55552','3AE55652','3AE55653'", ":Progress_Status" => "4"])["data"];
$cnt_act4 = $rs_act4[0]['cnt_total'];

$act_qry5 = "SELECT COUNT(*) AS cnt_total FROM tbl_DailyUpload WHERE LEFT(Product, 8) IN (:Product) AND Progress_Status =:Progress_Status AND DATE(uploaddate) BETWEEN DATE_FORMAT(CURDATE(), '%Y-%m-01') AND LAST_DAY(CURDATE())";
$rs_act5 = DbManager::fetchPDOQueryData('spectra_db', $act_qry5, [":Product" => "'3AE55656','3AE56241','3AE56242','3AE56251','3AE56252','3AE56642','3AE56643','3AE56652','3AE56653','3AE56656'", ":Progress_Status" => "4"])["data"];
$cnt_act5 = $rs_act5[0]['cnt_total'];

$act_qr6 = "SELECT COUNT(*) AS cnt_total FROM tbl_DailyUpload WHERE LEFT(Product, 8) IN (:Product) AND Progress_Status =:Progress_Status AND DATE(uploaddate) BETWEEN DATE_FORMAT(CURDATE(), '%Y-%m-01') AND LAST_DAY(CURDATE())";
$rs_act6 = DbManager::fetchPDOQueryData('spectra_db', $act_qr6, [":Product" => "'3AE55662','3AE55666','3AE55667','3AE55668','3AE56662','3AE56666','3AE56667'", ":Progress_Status" => "4"])["data"];
$cnt_act6 = $rs_act6[0]['cnt_total'];

?>
<style>
    .small-box > .small-box-footer {
    background-color: rgba(12, 60, 247, 0.81);
	background-image: url('Capture.png');	
    color: rgba(255, 255, 255, 0.8);
    display: block;
    padding: 3px 0;
    position: relative;
    text-align: center;
    text-decoration: none;
	height: 70px;
    z-index: 10;
	font-size:30px;
}
.bg-aqua {
    background-color: #6de264 !important;
    background: repeating-linear-gradient( -55deg, #2882c0, #a9b1b1 2px, #2882c0 4px, #a9b1b1 0px );
}
table.example-table 
{ background: url("Capture1.png"); 
/* image courtesy of subtlepatterns.com */ 
	color: rgba(255, 255, 255, 0.8);	
    
	border: 1px solid #dee2e6;
	font-size:28px;
	 
}

    </style>
  <!-- Content Wrapper. Contains page content  class="content-wrapper"-->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header mb-2">
      <div class="container-fluid">
       
		<div class="pagehead"  style=" background-image: url('Capture.png'); ">
		 <div class="row">
          <div class=" col-md-4 col-sm-4">
            <h5>Breaker Line Output</h5>
          </div>
          <div class="col-sm-8">
		  <div class="tab float-sm-right">
 
</div>
           
          </div>
		  </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
	  
        <div class="row">
          <div class="col-md-12 text-center">
            <!-- Default box -->
			<div class="card" style="background: url('img/background.png'); background-size: 1900px 720px; background-position: center; ">
			   <div class="card-body formarea smpadding">

               <div class="row">
        <div class="col-lg-4 col-xs-4">
          <!-- small box -->
        <div class="small-box bg-aqua">
           <a  class="small-box-footer">3AHO</a>
            <table  class="example-table"  width="100%" style="border: 1px solid rgba(0, 0, 0, 0.5);" border="1" >
              <thead><tr><th>Daily</th><th colspan="2">Monthly</th></tr></thead>
              <tbody>
                  <tr>
                       <td>Actual</td>
                       <td>Planned</td>
                       <td>Actual</td>
                </tr>
                <tr>
                       <td><?php echo $cnt_ah; ?></td>
                       <td><?php echo $cnt_plan; ?></td>
                       <td><?php echo $cnt_act; ?></td>
                </tr>
            
             </tbody>
              </table>
            </div>
        </div>
        <div class="col-lg-4 col-xs-4">
          <!-- small box -->
        <div class="small-box bg-aqua">
           <a  class="small-box-footer">3AH5</a>
            <table  class="example-table"  width="100%" style="border: 1px solid rgba(0, 0, 0, 0.5);" border="1">
              <thead><tr><th>Daily</th><th colspan="2">Monthly</th></tr></thead>
              <tbody>
                  <tr>
                       <td>Actual</td>
                       <td>Planned</td>
                       <td>Actual</td>
                </tr>
                <tr>
                       <td><?php echo $cnt_ah1; ?></td>
                       <td><?php echo $cnt_plan1; ?></td>
                       <td><?php echo $cnt_act1; ?></td>
                </tr>
            
             </tbody>
              </table>
            </div>
        </div>
        <div class="col-lg-4 col-xs-4">
          <!-- small box -->
        <div class="small-box bg-aqua">
           <a  class="small-box-footer">3AK6</a>
            <table  class="example-table"  width="100%" style="border: 1px solid rgba(0, 0, 0, 0.5);" border="1">
              <thead><tr><th>Daily</th><th colspan="2">Monthly</th></tr></thead>
              <tbody>
                  <tr>
                       <td>Actual</td>
                       <td>Planned</td>
                       <td>Actual</td>
                </tr>
                <tr>
                       <td><?php echo $cnt_ah2; ?></td>
                       <td><?php echo $cnt_plan2; ?></td>
                       <td><?php echo $cnt_act2; ?></td>
                </tr>
            
             </tbody>
              </table>
            </div>
        </div>

</div>
<div class="row">
        <div class="col-lg-4 col-xs-4">
          <!-- small box -->
        <div class="small-box bg-aqua">
           <a  class="small-box-footer">SION M25</a>
            <table  class="example-table"  width="100%" style="border: 1px solid rgba(0, 0, 0, 0.5);" border="1">
              <thead><tr><th>Daily</th><th colspan="2">Monthly</th></tr></thead>
              <tbody>
                  <tr>
                       <td>Actual</td>
                       <td>Planned</td>
                       <td>Actual</td>
                </tr>
                <tr>
                       <td><?php echo $cnt_ah3; ?></td>
                       <td><?php echo $cnt_plan3; ?></td>
                       <td><?php echo $cnt_act3; ?> </td>
                </tr>
            
             </tbody>
              </table>
            </div>
        </div>
        <div class="col-lg-4 col-xs-4">
          <!-- small box -->
        <div class="small-box bg-aqua">
           <a  class="small-box-footer">SION M31</a>
            <table  class="example-table"  width="100%" style="border: 1px solid rgba(0, 0, 0, 0.5);" border="1">
              <thead><tr><th>Daily</th><th colspan="2">Monthly</th></tr></thead>
              <tbody>
                  <tr>
                       <td>Actual</td>
                       <td>Planned</td>
                       <td>Actual</td>
                </tr>
                <tr>
                       <td><?php echo $cnt_ah4; ?></td>
                       <td><?php echo $cnt_plan4; ?></td>
                       <td><?php echo $cnt_act4; ?></td>
                </tr>
            
             </tbody>
              </table>
            </div>
        </div>
        <div class="col-lg-4 col-xs-4">
          <!-- small box -->
        <div class="small-box bg-aqua">
           <a  class="small-box-footer">SION M31+</a>
            <table  class="example-table"  width="100%" style="border: 1px solid rgba(0, 0, 0, 0.5);" border="1">
              <thead><tr><th>Daily</th><th colspan="2">Monthly</th></tr></thead>
              <tbody>
                  <tr>
                       <td>Actual</td>
                       <td>Planned</td>
                       <td>Actual</td>
                </tr>
                <tr>
                       <td><?php echo $cnt_ah5; ?></td>
                       <td><?php echo $cnt_plan5; ?></td>
                       <td><?php echo $cnt_act5; ?></td>
                </tr>
            
             </tbody>
              </table>
            </div>
        </div>

</div>
<div class="row">
		
		<div class="col-lg-4 col-xs-4">
          <!-- small box -->
        <div class="small-box ">
           <!--<a  class="small-box-footer">3AHk</a> 
            <table  class="table-bordered" width="100%">
              <thead><tr><th>Daily</th><th colspan="2">Monthly</th></tr></thead>
              <tbody>
                  <tr>
                       <td>Actual</td>
                       <td>Planned</td>
                       <td>Actual</td>
                </tr>
                <tr>
                       <td><?php echo $cnt_ah2; ?></td>
                       <td><?php echo $cnt_plan2; ?></td>
                       <td><?php echo $cnt_act2; ?></td>
                </tr>
            
             </tbody>
              </table>
			  -->
            </div>
        </div>


        <div class="col-lg-4 col-xs-4">
          <!-- small box -->
        <div class="small-box bg-aqua">
           <a  class="small-box-footer">SION M40</a> 
            <table  class="example-table"  width="100%" style="border: 1px solid rgba(0, 0, 0, 0.5);" border="1">
              <thead><tr><th height = 50px; >Daily</th><th colspan="2">Monthly</th></tr></thead>
              <tbody>
                  <tr height = 50px;>
                       <td>Actual</td>
                       <td>Planned</td>
                       <td>Actual</td>
                </tr>
                <tr height = 50px;>
                       <td><?php echo $cnt_ah6; ?></td>
                       <td><?php echo $cnt_plan6; ?></td>
                       <td><?php echo $cnt_act6; ?></td>
                </tr>
            
             </tbody>
              </table>
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
  </div>
  <!-- /.content-wrapper -->

<?php include('footer.php');?>
<!-- ./wrapper -->

<!-- jQuery -->
<script>
$(document).ready(function(){
	$(".icon-input-btn").each(function(){
        var btnFont = $(this).find(".btn").css("font-size");
        var btnColor = $(this).find(".btn").css("color");
      	$(this).find(".fa").css({'font-size': btnFont, 'color': btnColor});
	}); 
});
</script>

<script src="plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="dist/js/adminlte.min.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="dist/js/demo.js"></script>
<script src="js/fontawesome4.js"></script>
<script>
$(function() {
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
    // Handler for .ready() called.
    window.setTimeout(function () {
        location.href = "Reportdashboard.php";
    }, 5000);
});
</script>

</body>
</html>