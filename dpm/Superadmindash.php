<?php
session_start();
$user = $_SESSION['username'];
SharedManager::checkAuthToModule(9);
include('shared/CommonManager.php');
include('header.php');
include('menu_spectra.php')
?>
<head>
<!-- <meta http-equiv="refresh" content="10;url=day_shift_wise_dash.php"> -->
<style>
	.space_tablink {
    border: 1px solid #fff;
    background: #fff;
    float: left;
    margin-left: 6px;
    min-width: 100px;
    margin-bottom: 5px;
    outline: none;
    cursor: pointer;
    padding: 3px 10px;
    transition: 0.3s;
    font-size: 1.15rem;
    letter-spacing: 1pt;
    color: #fff;
    text-align: center;
}
.space_tablink1 {
    border: 1px solid #fff;
    background: #fff;
    float: left;
    margin-left: 6px;
    min-width: 50px;
    margin-bottom: 5px;
    outline: none;
    cursor: pointer;
    padding: 3px 10px;
    transition: 0.3s;
    font-size: 1.15rem;
    letter-spacing: 1pt;
    color: #fff;
    text-align: center;
}
	.tablinks1 {
    border: 1px solid #92eafe;
    background: #129bba;
    float: left;
    margin-left: 6px;
   /* border-radius: 0 0em 2em 2em;*/
    min-width: 50px;
    margin-bottom: 5px;
    outline: none;
    cursor: pointer;
    padding: 3px 10px;
    transition: 0.3s;
    font-size: 1.15rem;
    letter-spacing: 1pt;
    color: #fff;
    text-align: center;
    box-shadow: 0px 4px 0px #ccc;
}
.tablinks2 {
	border: 1px solid #5ec656;
background: #1d6830;
    float: left;
    margin-left: 6px;
   /* border-radius: 0 0em 2em 2em;*/
    min-width: 55px;
    margin-bottom: 5px;
    outline: none;
    cursor: pointer;
    padding: 3px 10px;
    transition: 0.3s;
    font-size: 1.15rem;
    letter-spacing: 1pt;
    color: #fff;
    text-align: center;
    box-shadow: 0px 4px 0px #ccc;
	writing-mode: vertical-rl; 
}


.tablinks_3ah0 {
    border: 1px solid #f2d909;
    background: #eeab0c;
    float: left;
    margin-left: 6px;
    min-width: 215px;
    margin-bottom: 5px;
    outline: none;
    cursor: pointer;
    padding: 3px 10px;
    transition: 0.3s;
    font-size: 1.15rem;
    letter-spacing: 1pt;
    color: #fff;
    text-align: center;
    box-shadow: 0px 4px 0px #ccc;
}
.tablinks_end {
    border: 1px solid #f2d909;
    background: #eeab0c;
    float: left;
    margin-left: 6px;
    min-width: 235px;
    margin-bottom: 5px;
    outline: none;
    cursor: pointer;
    padding: 3px 10px;
    transition: 0.3s;
    font-size: 1.15rem;
    letter-spacing: 1pt;
    color: #fff;
    text-align: center;
    box-shadow: 0px 4px 0px #ccc;
}
.tablinks_drive {
    border: 1px solid #f2d909;
    background: #eeab0c;
    float: left;
    margin-left: 6px;
    min-width: 180px;
    margin-bottom: 5px;
    outline: none;
    cursor: pointer;
    padding: 3px 10px;
    transition: 0.3s;
   
    letter-spacing: 1pt;
    color: #fff;
    text-align: center;
    box-shadow: 0px 4px 0px #ccc;
}
.tablinks_rework{
	border: 1px solid #5ec656;
background: #1d6830;
    float: left;
    margin-left: 6px;
    min-width: 180px;
    margin-bottom: 5px;
    outline: none;
    cursor: pointer;
    padding: 3px 10px;
    transition: 0.3s;
   
    letter-spacing: 1pt;
    color: #fff;
    text-align: center;
    box-shadow: 0px 4px 0px #ccc;

}
.tablinks_3ak{
	border: 1px solid #f2d909;
    background: #eeab0c;
    float: left;
    margin-left: 6px;
    min-width: 105px;
    margin-bottom: 5px;
    outline: none;
    cursor: pointer;
    padding: 3px 10px;
    transition: 0.3s;
    font-size: 1.15rem;
    letter-spacing: 1pt;
    color: #fff;
    text-align: center;
    box-shadow: 0px 4px 0px #ccc;
}
.tablinks_buffer{
	border: 1px solid #5ec656;
    background: #1d6830;
    float: left;
    margin-left: 6px;
    min-width: 105px;
    margin-bottom: 5px;
    outline: none;
    cursor: pointer;
    padding: 3px 10px;
    transition: 0.3s;
    font-size: 1.15rem;
    letter-spacing: 1pt;
    color: #fff;
    text-align: center;
    box-shadow: 0px 4px 0px #ccc;


}
.tablinks_fun {
	border: 1px solid #f2d909;
    background: #eeab0c;
    float: left;
    margin-left: 6px;
    min-width: 180px;
    margin-bottom: 5px;
    outline: none;
    cursor: pointer;
    padding: 3px 10px;
    transition: 0.3s;
    font-size: 1.15rem;
    letter-spacing: 1pt;
    color: #fff;
    text-align: center;
    box-shadow: 0px 4px 0px #ccc;


}
.tablinks_sion {
    border: 1px solid #f2d909;
    background: #eeab0c;
    float: left;
    margin-left: 6px;
    min-width: 150px;
    margin-bottom: 5px;
    outline: none;
    cursor: pointer;
    padding: 3px 10px;
    transition: 0.3s;
    font-size: 1.15rem;
    letter-spacing: 1pt;
    color: #fff;
    text-align: center;
    box-shadow: 0px 4px 0px #ccc;
}

.tooltip {
  position: relative;
  border-bottom: 1px dotted black;
}
.tooltip span {
  visibility: hidden;
  width: 10em;
  background-color: #000;
  color: #fff;
  text-align: center;
  border-radius: 6px;
  padding: 5px 0;
  position: absolute;
  z-index: 9;
  top: -1em;
  left:  100%;
  margin-left:1em;
  opacity: 0;
  transition: opacity 1s;
}
.tooltip span::after {
  content: "";
  position: absolute;
  top: 1.5em;
  right: 100%;
  margin-top: -5px;
  border-width: 5px;
  border-style: solid;
  border-color: transparent black transparent transparent;
}
.tooltip input {
  display:none;
}
.tooltip input:checked+span {
  visibility: visible;
  opacity: 1;
}
	</style>
</head>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header mb-2">
      <div class="container-fluid">
       
		<div class="pagehead">
		 <div class="row">
          <div class=" col-md-4 col-sm-4">
            <h5 >Line Status</h5>
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
	  <!--<div class="row">
        <div class="col-lg-2 col-xs-2">
          <!-- small box 
        <div class="small-box bg-aqua">
            <div class="inner">
              <h3><?php //echo $enquiry_opencount; ?></h3>

              <p>Open Enquiry</p>
            </div>
           
            <a href="manage-open-enquiry.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>

</div>-->

        <div class="row">
          <div class="col-md-12 text-center">
            <!-- Default box -->
			<div class="card">
			   <div class="card-body formarea smpadding">

			   <div class="row">
			   <a href="#"  class="tablinks_3ah0">SION M25/31</a>
			   <a class="space_tablink1">&nbsp;</a>
			   <a href="#"  class="tablinks_sion">SION M31+/40</a>
			   <a class="space_tablink1">&nbsp;</a>
			   <a href="#"  class="tablinks_end">Endurance Test</a>
			   <a href="#"  class="tablinks_rework">Rework </a>
			   <a href="#"  class="tablinks2" >Buffer</a>
			   <a href="#"  class="tablinks_fun">Functional Test</a>
		   <a href="#"  class="tablinks_fun">HV TEST</a>

               </div>
			   <br>

			   <div class="row">
         <table class="table table-striped table-bordered">              
          <thead>
          <tr>
          
          
          </tr>
          </thead>              
          <tbody>           
          <?php
         // $sql = "SELECT id, employee_name, employee_salary, employee_age FROM employee LIMIT 5";
          //$resultset = mysqli_query($conn, $sql) or die("database error:". mysqli_error($conn));
         // while( $rows = mysqli_fetch_assoc($resultset) ) { 
          ?>
          <tr>
          
          <?php
          
          ?>
          </tbody>
          </table>








        
			   <a href="#" class="tablinks1" >B2</a>
			   <a class="space_tablink">&nbsp;</a>
			   <a href=""  class="tablinks1" >B3</a>
			   <a class="space_tablink1">&nbsp;</a>
			   <a href="#"  class="tablinks1" >B4</a>
			   <a class="space_tablink1">&nbsp;</a>
			   <a href="#"  class="tablinks1" >B5</a>
			   <a class="space_tablink1">&nbsp;</a>
			   <a href="#"  class="tablinks1" >B7</a>
			   <a href="#"  class="tablinks1" >B8</a>
			   <a href="#"  class="tablinks1" >B9</a>
			   <a href="#"  class="tablinks1" >B10</a>
			   <a href="#"  class="tablinks1" >B11</a>
			   <a href="#"  class="tablinks1" >B12</a>
			   <a href="#"  class="tablinks1" >B13</a>
			   <a href="#"  class="tablinks1" >B14</a>
			   <a href="#"  class="tablinks1" >B16</a>
			   <a href="#"  class="tablinks1" >B17</a>
			   <a href="#"  class="tablinks1" >B18</a>
			   <a class="space_tablink">&nbsp;</a>
			   <a href="#"  class="tablinks1" >B19</a>
			   
                </div>
			   <div class="card">
			          <div class="card-body formarea smpadding">
                      </div>

                </div>
                
				<div class="card">
			          <div class="card-body formarea smpadding">
                      </div>

                </div>

			   
				<div class="row">
			<a href="#"  class="tablinks1" >A2</a>
			<a href="#"  class="tablinks1" >A3</a>
			<a href="#"  class="tablinks1" >A4</a>
			<a href="#"  class="tablinks1" >A5</a>
			<a href="#"  class="tablinks1" >A6</a>
			<a href="#"  class="tablinks1" >A7</a>
			<a href="#"  class="tablinks1" >A8</a>
			<a href="#"  class="tablinks1" >A9</a>
			<a href="#"  class="tablinks1" >A10</a>
			<a href="#"  class="tablinks1" >A11</a>
			<a href="#"  class="tablinks1" >A12</a>
			<a href="#"  class="tablinks1" >A13</a>
			<a href="#"  class="tablinks1" >A14</a>
			<a href="#"  class="tablinks1" >A15</a>
			<a href="#"  class="tablinks1" >A16</a>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<a href="#"  class="tablinks1" >A17</a>
			<a href="#"  class="tablinks1" >A18</a>
			<a href="#"  class="tablinks1" >A19</a>
			<a href="#"  class="tablinks1" >A20</a>
			<a href="#"  class="tablinks1" >A21</a>
			<a href="#"  class="tablinks1" >A22</a>
			<a href="#"  class="tablinks1" >A23</a>
           </div>
		   <br>
		   <div class="row">
		   <a href="#"  class="tablinks_3ah0">3AHO/5</a>
		   <a href="#"  class="tablinks_3ak">3AK</a>
		   <a href="#"  class="tablinks_buffer">Buffer</a>
		   <a href="#"  class="tablinks_end">Endurance Test</a>
		   <a href="#"  class="tablinks_drive">Drive Setting<br> +mV Drop </a>
		   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		   <a href="#"  class="tablinks2" >Buffer</a>
		   <a href="#"  class="tablinks_fun">Functional Test</a>
		   <a href="#"  class="tablinks_fun">Dropping</a>
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
