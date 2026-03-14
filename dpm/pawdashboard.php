<?php
session_start();
$user = $_SESSION['username'];
$pass = $_SESSION['pass'];
include('shared/CommonManager.php');
include('header.php');
include('menupaw.php');
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header mb-2">
    <div class="container-fluid">

		<div class="pagehead">
		<div class="row">
        <div class=" col-md-4 col-sm-4">
            <h5 >Welcome to Digital Production Management</h5>
        </div>
        <div class="col-sm-8">
		<div class="tab float-sm-right">
		<!-- <a href="Admindash.php"  class="tablinks" ><i class='fas fa-home'></i> </a>
            <a href="#"  class="tablinks paybill" ><i class="fa fa-money"></i> </a>
            <a href="#"  class="tablinks gpf" ><i class="fa fa-database"></i> </a>
            <a href="#"  class="tablinks income" ><i class="fa fa-edit"></i></a>
            <a href="#" class="tablinks" ><i class="fa fa-line-chart"></i> </a>-->
</div>
        
        </div>
		</div>
        </div>
    </div><!-- /.container-fluid -->
    </section>

    
</div>
<!-- /.content-wrapper -->
<?php include('footer.php'); ?>
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
<script>
$(document).ready(function(){
	$(".icon-input-btn").each(function(){
        var btnFont = $(this).find(".btn").css("font-size");
        var btnColor = $(this).find(".btn").css("color");
      	$(this).find(".fa").css({'font-size': btnFont, 'color': btnColor});
	}); 
});
</script>
</body>
</html>
