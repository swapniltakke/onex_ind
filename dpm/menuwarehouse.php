
 <!-- Main Sidebar Container -->
 <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="warehousedashboard.php" class="brand-link">
      <img src="img/sie-favicon_internet_1024px.png" alt="DPM Logo" class="brand-image img-thumbnail elevation-1" >
      <span class="brand-text font-weight-light"><br/>
	  <small style="font-size:0.6em;"></small></span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
	
	<?php // $str1 = "http://localhost:8080/home?employeeid=$user " ?> 
	<?php // echo $str1; ?>
	<ul id="accordion" class="accordion nav nav-pills nav-sidebar flex-column">
	<!--<li><a href="Admindash.php"><div class="link"><i class="fa fa-dashboard"></i><span>Dashboard</span></div></a></li>-->
  <li><div class="link"><a href="generatewarehouse.php"><i class="fa fa-hourglass-start"></i><span>Scan</span></a></div></li>
  <li><div class="link"><a href="warehousedetails.php"><i class="fas fa-layer-group"></i><span>List</span></a></div></li>
  <!-- <li><div class="link"><a href= "<?php echo $str1; ?>" target="_blank"><i class="fa fa-hourglass-start"></i><span>Core Drive Label</span></a></div></li> -->

<!--<li><div class="link"><a href="checkproductsteps.php"><i class="fa fa-dashboard"></i><span>Assembly Test</span></a></div></li>
<li><div class="link"><a href="checkproducttesting.php"><i class="fa fa-dashboard"></i><span>Testing Test</span></a></div></li>
<li><div class="link"><a href="checksubassembly.php"><i class="fa fa-dashboard"></i><span>Subassembly Test</span></a></div></li>

  
-->
  
    
   <li>
   <div class="link"><A href="logout.php"> <i class="fa fa-sign-out nav-icon"></i><span>Logout</span></a></div>
   
  </li>
  
  
  
</ul>
	
	
	
	
	
     
      <!-- Sidebar Menu -->
      
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>