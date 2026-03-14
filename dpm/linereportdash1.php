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
                       <td>7</td>
                       <td>845</td>
                       <td>900</td>
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
                       <td>7</td>
                       <td>845</td>
                       <td>900</td>
                </tr>
            
             </tbody>
              </table>
            </div>
        </div>
        <div class="col-lg-4 col-xs-4">
          <!-- small box -->
        <div class="small-box bg-aqua">
           <a  class="small-box-footer">SION M31+</a>
            <table  class="table-bordered" width="100%">
              <thead><tr><th>Daily</th><th colspan="2">Monthly</th></tr></thead>
              <tbody>
                  <tr>
                       <td>Actual</td>
                       <td>Planned</td>
                       <td>Actual</td>
                </tr>
                <tr>
                       <td>7</td>
                       <td>845</td>
                       <td>900</td>
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
                       <td>7</td>
                       <td>845</td>
                       <td>900</td>
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
                       <td>7</td>
                       <td>845</td>
                       <td>900</td>
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
                       <td>7</td>
                       <td>845</td>
                       <td>900</td>
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
                       <td>7</td>
                       <td>845</td>
                       <td>900</td>
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
