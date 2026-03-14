<?php
session_start();
$user=$_SESSION['username'];
if (!isset($_SESSION['username']) || $_SESSION['username'] == '')
{

			  
        echo '<script type="text/JavaScript">';
					//echo 'alert("Number of parameters not matched");';
					echo 'top.window.document.location="logout.php"';
					echo '</script>';
					exit();
}
include('header.php');
include('menu_reportdash.php')

$ah_qry = "select count(*) as cnt from tbl_DailyUpload where SUBSTRING(Product,1,4)= '3AH0' and Progress_Status = 4 and FORMAT(uploaddate,'dd/MM/yyyy') = FORMAT (getdate(), 'dd/MM/yyyy') ";
//echo $ah_qry; exit;
$rs_ah = odbc_exec($conn, $ah_qry);
$cnt_ah = odbc_result($rs_ah,'cnt');
//echo $cnt_ah; exit;
$ah_qry1 = "select count(*) as cnt from tbl_DailyUpload where SUBSTRING(Product,1,4)= '3AH5' and Progress_Status = 4 and FORMAT(uploaddate,'dd/MM/yyyy') = FORMAT (getdate(), 'dd/MM/yyyy') ";
$rs_ah1 = odbc_exec($conn, $ah_qry1);
$cnt_ah1 = odbc_result($rs_ah1,'cnt');

$ah_qry2 = "select count(*) as cnt from tbl_DailyUpload where SUBSTRING(Product,1,4)= '3AHK' and Progress_Status = 4 and FORMAT(uploaddate,'dd/MM/yyyy') = FORMAT (getdate(), 'dd/MM/yyyy') ";
$rs_ah2 = odbc_exec($conn, $ah_qry2);
$cnt_ah2 = odbc_result($rs_ah2,'cnt');

$ah_qry3 = "select count(*) as cnt from tbl_DailyUpload where SUBSTRING(Product,1,8)= 'Sion M25' and Progress_Status = 4 and FORMAT(uploaddate,'dd/MM/yyyy') = FORMAT (getdate(), 'dd/MM/yyyy') ";
$rs_ah3 = odbc_exec($conn, $ah_qry3);
$cnt_ah3 = odbc_result($rs_ah3,'cnt');

$ah_qry4 = "select count(*) as cnt from tbl_DailyUpload where SUBSTRING(Product,1,4)= '3AH1' and Progress_Status = 4 and FORMAT(uploaddate,'dd/MM/yyyy') = FORMAT (getdate(), 'dd/MM/yyyy') ";
$rs_ah4 = odbc_exec($conn, $ah_qry4);
$cnt_ah4 = odbc_result($rs_ah4,'cnt');

$ah_qry5 = "select count(*) as cnt from tbl_DailyUpload where SUBSTRING(Product,1,8)= 'Sion M25' and Progress_Status = 4 and FORMAT(uploaddate,'dd/MM/yyyy') = FORMAT (getdate(), 'dd/MM/yyyy') ";
$rs_ah5 = odbc_exec($conn, $ah_qry5);
$cnt_ah5 = odbc_result($rs_ah5,'cnt');

$ah_qry6 = "select count(*) as cnt from tbl_DailyUpload where SUBSTRING(Product,1,4)= '3AH1' and Progress_Status = 4 and FORMAT(uploaddate,'dd/MM/yyyy') = FORMAT (getdate(), 'dd/MM/yyyy') ";
$rs_ah6 = odbc_exec($conn, $ah_qry6);
$cnt_ah6 = odbc_result($rs_ah6,'cnt');


//$plan_qry = "select count(*) as cnt_total from tbl_checklistdetails c1 inner join tbl_product p on p.product_id=c1.product_id and p.product_name='3AH0'";
$plan_qry = "select count(*) as cnt_total from tbl_DailyUpload where SUBSTRING(Product,1,4)= '3AH0' and FORMAT(uploaddate,'dd/MM/yyyy') between FORMAT(DATEADD(month, DATEDIFF(month, 0, GETDATE()), 0),'dd/MM/yyyy')  and FORMAT(eomonth(getdate()),'dd/MM/yyyy') ";
$rs = odbc_exec($conn, $plan_qry);
$cnt_plan = odbc_result($rs,'cnt_total');
//echo $plan_qry; exit;

$plan_qry1 = "select count(*) as cnt_total from tbl_DailyUpload where SUBSTRING(Product,1,4)= '3AH5' and FORMAT(uploaddate,'dd/MM/yyyy') between FORMAT(DATEADD(month, DATEDIFF(month, 0, GETDATE()), 0),'dd/MM/yyyy')  and FORMAT(eomonth(getdate()),'dd/MM/yyyy') ";
$rs1 = odbc_exec($conn, $plan_qry1);
$cnt_plan1 = odbc_result($rs1,'cnt_total');

$plan_qry2 = "select count(*) as cnt_total from tbl_DailyUpload where SUBSTRING(Product,1,4)= '3AHK' and FORMAT(uploaddate,'dd/MM/yyyy') between FORMAT(DATEADD(month, DATEDIFF(month, 0, GETDATE()), 0),'dd/MM/yyyy')  and FORMAT(eomonth(getdate()),'dd/MM/yyyy') ";
$rs2 = odbc_exec($conn, $plan_qry2);
$cnt_plan2 = odbc_result($rs2,'cnt_total');

$plan_qry3 = "select count(*) as cnt_total from tbl_DailyUpload where SUBSTRING(Product,1,8)= 'Sion M25' and FORMAT(uploaddate,'dd/MM/yyyy') between FORMAT(DATEADD(month, DATEDIFF(month, 0, GETDATE()), 0),'dd/MM/yyyy')  and FORMAT(eomonth(getdate()),'dd/MM/yyyy') ";
$rs3 = odbc_exec($conn, $plan_qry3);
$cnt_plan3 = odbc_result($rs3,'cnt_total');

$plan_qry4 = "select count(*) as cnt_total from tbl_DailyUpload where SUBSTRING(Product,1,4)= '3AH1' and FORMAT(uploaddate,'dd/MM/yyyy') between FORMAT(DATEADD(month, DATEDIFF(month, 0, GETDATE()), 0),'dd/MM/yyyy')  and FORMAT(eomonth(getdate()),'dd/MM/yyyy')  ";
$rs4 = odbc_exec($conn, $plan_qry4);
$cnt_plan4 = odbc_result($rs4,'cnt_total');

$plan_qry5 = "select count(*) as cnt_total from tbl_DailyUpload where SUBSTRING(Product,1,8)= 'Sion M25' and FORMAT(uploaddate,'dd/MM/yyyy') between FORMAT(DATEADD(month, DATEDIFF(month, 0, GETDATE()), 0),'dd/MM/yyyy')  and FORMAT(eomonth(getdate()),'dd/MM/yyyy') ";
$rs5 = odbc_exec($conn, $plan_qry5);
$cnt_plan5 = odbc_result($rs5,'cnt_total');

$plan_qry6 = "select count(*) as cnt_total from tbl_DailyUpload where SUBSTRING(Product,1,4)= '3AH1' and FORMAT(uploaddate,'dd/MM/yyyy') between FORMAT(DATEADD(month, DATEDIFF(month, 0, GETDATE()), 0),'dd/MM/yyyy')  and FORMAT(eomonth(getdate()),'dd/MM/yyyy')  ";
$rs6 = odbc_exec($conn, $plan_qry6);
$cnt_plan6 = odbc_result($rs6,'cnt_total');

//$act_qry = "select count(*) as cnt_total from tbl_checklistdetails c1 inner join tbl_product p on p.product_id=c1.product_id and p.product_name='3AH0'";
$act_qry = "select count(*) as cnt_total from tbl_DailyUpload where SUBSTRING(Product,1,4)= '3AH0' and Progress_Status = 4 and FORMAT(uploaddate,'dd/MM/yyyy') between FORMAT(DATEADD(month, DATEDIFF(month, 0, GETDATE()), 0),'dd/MM/yyyy')  and FORMAT(eomonth(getdate()),'dd/MM/yyyy') ";
$rs_act = odbc_exec($conn, $act_qry);
$cnt_act = odbc_result($rs_act,'cnt_total');

$act_qry1 = "select count(*) as cnt_total from tbl_DailyUpload where SUBSTRING(Product,1,4)= '3AH5' and Progress_Status = 4 and FORMAT(uploaddate,'dd/MM/yyyy') between FORMAT(DATEADD(month, DATEDIFF(month, 0, GETDATE()), 0),'dd/MM/yyyy')  and FORMAT(eomonth(getdate()),'dd/MM/yyyy') ";
$rs_act1 = odbc_exec($conn, $act_qry1);
$cnt_act1 = odbc_result($rs_act1,'cnt_total');

$act_qry2 = "select count(*) as cnt_total from tbl_DailyUpload where SUBSTRING(Product,1,4)= '3AHK' and Progress_Status = 4 and FORMAT(uploaddate,'dd/MM/yyyy') between FORMAT(DATEADD(month, DATEDIFF(month, 0, GETDATE()), 0),'dd/MM/yyyy')  and FORMAT(eomonth(getdate()),'dd/MM/yyyy') ";
$rs_act2 = odbc_exec($conn, $act_qry2);
$cnt_act2 = odbc_result($rs_act2,'cnt_total');

$act_qry3 = "select count(*) as cnt_total from tbl_DailyUpload where SUBSTRING(Product,1,8)= 'Sion M25' and Progress_Status = 4 and FORMAT(uploaddate,'dd/MM/yyyy') between FORMAT(DATEADD(month, DATEDIFF(month, 0, GETDATE()), 0),'dd/MM/yyyy')  and FORMAT(eomonth(getdate()),'dd/MM/yyyy') ";
$rs_act3 = odbc_exec($conn, $act_qry3);
$cnt_act3 = odbc_result($rs_act3,'cnt_total');

$act_qry4 = "select count(*) as cnt_total from tbl_DailyUpload where SUBSTRING(Product,1,4)= '3AH1' and Progress_Status = 4 and FORMAT(uploaddate,'dd/MM/yyyy') between FORMAT(DATEADD(month, DATEDIFF(month, 0, GETDATE()), 0),'dd/MM/yyyy')  and FORMAT(eomonth(getdate()),'dd/MM/yyyy') ";
$rs_act4 = odbc_exec($conn, $act_qry4);
$cnt_act4 = odbc_result($rs_act4,'cnt_total');

$act_qry5 = "select count(*) as cnt_total from tbl_DailyUpload where SUBSTRING(Product,1,8)= 'Sion M25' and Progress_Status = 4 and FORMAT(uploaddate,'dd/MM/yyyy') between FORMAT(DATEADD(month, DATEDIFF(month, 0, GETDATE()), 0),'dd/MM/yyyy')  and FORMAT(eomonth(getdate()),'dd/MM/yyyy') ";
$rs_act5 = odbc_exec($conn, $act_qry5);
$cnt_act5 = odbc_result($rs_act5,'cnt_total');

$act_qry6 = "select count(*) as cnt_total from tbl_DailyUpload where SUBSTRING(Product,1,4)= '3AH1' and Progress_Status = 4 and FORMAT(uploaddate,'dd/MM/yyyy') between FORMAT(DATEADD(month, DATEDIFF(month, 0, GETDATE()), 0),'dd/MM/yyyy')  and FORMAT(eomonth(getdate()),'dd/MM/yyyy') ";
$rs_act6 = odbc_exec($conn, $act_qry6);
$cnt_act6 = odbc_result($rs_act6,'cnt_total');

?>
<style>
    .small-box > .small-box-footer {
    background-color: rgba(12, 60, 247, 0.81);
    color: rgba(255, 255, 255, 0.8);
    display: block;
    padding: 3px 0;
    position: relative;
    text-align: center;
    text-decoration: none;
    z-index: 10;
}

 
    </style>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header mb-2">
      <div class="container-fluid">
       
		<div class="pagehead">
		 <div class="row">
          <div class=" col-md-4 col-sm-4">
            <h5 >Breaker Line Output</h5>
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
			<div class="card">
			   <div class="card-body formarea smpadding">

               <div class="row">
        <div class="col-lg-4 col-xs-4">
          <!-- small box -->
        <div class="small-box bg-aqua">
           <a  class="small-box-footer">3AHO</a>
            <table  class="table-bordered" width="100%">
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
           <a  class="small-box-footer">SION M25</a>
            <table  class="table-bordered" width="100%">
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
                       <td><?php echo $cnt_act3; ?></td>
                </tr>
            
             </tbody>
              </table>
            </div>
        </div>
        <div class="col-lg-4 col-xs-4">
          <!-- small box -->
        <div class="small-box bg-aqua">
           <a  class="small-box-footer">3AH1</a>
            <table  class="table-bordered" width="100%">
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

</div>
<div class="row">
        <div class="col-lg-4 col-xs-4">
          <!-- small box -->
        <div class="small-box bg-aqua">
           <a  class="small-box-footer">3AH5</a>
            <table  class="table-bordered" width="100%">
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
                       <td><?php echo $cnt_act1; ?> </td>
                </tr>
            
             </tbody>
              </table>
            </div>
        </div>
        <div class="col-lg-4 col-xs-4">
          <!-- small box -->
        <div class="small-box bg-aqua">
           <a  class="small-box-footer">SION M31</a>
            <table  class="table-bordered" width="100%">
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
           <a  class="small-box-footer">SION M40</a>
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
           <a  class="small-box-footer">3AHk</a> 
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
</script>

</body>
</html>
