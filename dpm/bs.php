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
include('menu_spectra.php')
?>
   <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f0f4f8;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #007bff;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .card-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin: 15px;
            flex: 1 1 calc(25% - 30px); /* 4 cards in one row */
            text-align: center;
            transition: transform 0.3s;
            max-width: 280px; /* Limit width for larger screens */
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .icon {
            font-size: 48px;
            color: #007bff;
            margin-bottom: 10px;
        }
        .title {
            font-size: 20px;
            color: #333;
            margin: 10px 0;
            font-weight: bold;
        }
        .description {
            font-size: 14px;
            color: #777;
            margin: 5px 0;
        }
        .counts {
            display: flex;
            justify-content: space-around;
            margin-top: 10px;
        }
        .count {
            background-color: #007bff;
            color: white;
            border-radius: 12px;
            padding: 5px 10px;
            font-size: 14px;
            display: inline-block;
        }
        @media (max-width: 768px) {
            .card {
                flex: 1 1 calc(50% - 30px); /* 2 cards in one row on smaller screens */
            }
        }
        @media (max-width: 480px) {
            .card {
                flex: 1 1 100%; /* 1 card in one row on very small screens */
            }
        }
    </style>   

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header mb-2">
    
        
    <div class="card-container">
        <div class="card" onclick="fetchValue('Email Support')">
            <span class="icon"></span>
            <div class="title">Email Support</div>
            <div class="description">Get assistance via email.</div>
            <div class="counts">
                <span class="count">Open: 12</span>
                <span class="count">Resolved: 8</span>
            </div>
        </div>
        <div class="card" onclick="fetchValue('Call Support')">
            <span class="icon">📞</span>
            <div class="title">Call Support</div>
            <div class="description">Speak to our support team.</div>
            <div class="counts">
                <span class="count">Calls: 5</span>
                <span class="count">Answered: 4</span>
            </div>
        </div>
        <div class="card" onclick="fetchValue('Live Chat')">
            <span class="icon">💬</span>
            <div class="title">Live Chat</div>
            <div class="description">Chat with us in real-time.</div>
            <div class="counts">
                <span class="count">Active: 8</span>
                <span class="count">Closed: 6</span>
            </div>
        </div>
        <div class="card" onclick="fetchValue('FAQs')">
            <span class="icon">❓</span>
            <div class="title">FAQs</div>
            <div class="description">Find answers to common questions.</div>
            <div class="counts">
                <span class="count">Total: 20</span>
                <span class="count">Updated: 5</span>
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
