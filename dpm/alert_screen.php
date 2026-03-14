<?php
include 'DatabaseConfig.php';
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

$ah_qry = "select s.station_name,w.title, m.w_type
from tbl_transactiondetails t inner join tbl_workbench w on w.id = t.workbench_id
inner join tbl_station s on s.station_id = t.station_id INNER JOIN tbl_manageworkbench m ON t.w_type = m.w_id
where FORMAT(end_time,'dd/MM/yyyy') = '01/01/1900'";
//echo $ah_qry; exit;
$rs_ah = odbc_exec($conn, $ah_qry);

include('header.php');
include('menu_reportdash.php')
?>
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
	</style>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper" style="background-color:red;">
    <!-- Content Header (Page header) -->
    <section class="content-header mb-2">
      <div class="container-fluid">
       
		
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content" >
      <div class="container-fluid">
        <?php while(odbc_fetch_row($rs_ah)){ ?>
          <br></br> <br></br>
          <div class="mySlides w3-container w3-red" style="text-align:center;">
           
           <h1 style="font-size:80px;"><i><?php echo odbc_result($rs_ah,'title');?></i></h1>
           <h1 style="font-size:80px;"><b><?php echo odbc_result($rs_ah,'station_name');?> </b></h1>
         </div>
         <?php } ?>
  
       
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
var slideIndex = 0;
carousel();

function carousel() {
  var i;
  var x = document.getElementsByClassName("mySlides");
  for (i = 0; i < x.length; i++) {
    x[i].style.display = "none"; 
  }
  slideIndex++;
  if (slideIndex > x.length) {slideIndex = 1} 
  x[slideIndex-1].style.display = "block"; 
  setTimeout(carousel, 5000); 
}
$(document).ready(function () {
    // Handler for .ready() called.
    window.setTimeout(function () {
        location.href = "Reportdashboard.php";
    }, 5000);
});

</script>

</body>
</html>
