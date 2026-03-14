<?php
SharedManager::checkAuthToModule(11);
$sql = 'SELECT * FROM tbl_roles where role_name!=:role_name';
$rs = DbManager::fetchPDOQueryData('spectra_db', $sql, [":role_name" => "Manufacturing"])["data"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>DPM</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400">
  <link rel="stylesheet" href="font-awesome-4.6.3/css/font-awesome.min.css">
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="css/templatemo-style.css">
  <style>
    @media (max-width:1024px) {
      .tm-main-content {
        height: auto !important;
      }

    }
    @media (max-width:1196px) {
      .aboutText {
        padding: 10px !important;
      }

    }
    @media(max-width:1400px) {
      .tm-main-nav {
        height: auto !Important;
      }
    }
  </style>
</head>
<body>
  <div class="container-fluid">
    <div class="tm-body" style="background: rgba(255,255,255);">
      <div class="tm-sidebar" style="height:auto; min-height:100vh;">
        <nav class="tm-main-nav" style="min-height:100% !Important; height:auto !important;">
          <ul class="tm-main-nav-ul">
            <li class="tm-nav-item">
              <div id="welcome" class="tm-content-box tm-banner margin-b-10">
                <div class="tm-banner-inner">
                  <!--<h1 class="tm-banner-title"><img src="img/logo.jpg" alt = "" /> <br/>-->
                  <h1 class="tm-banner-title">
                    <a href="../index.php" style="cursor:pointer;">
                      <img src="img/fav_logo1.png" alt="" /><br />
                    </a>
                    <div>
                      <center><b>DPM</b></center>
                    </div>
                    <!--<small> <img src="img/emb.gif" style="width:40px !Important" alt = "" /></small>-->
                  </h1>
                </div> <br>
                <div class="aboutText">
                  <div class="container">
                    <div class="row">
                      <div class="login-box">
                        <div class="col-lg-12 login-key"></div>
                        <div class="col-lg-12 login-form">
                          <form action="loginAuth.php" name="form1" method="POST">
                            <div class="form-group">
                              <div class="input_row">
                                <label class="form-control-label"><i class="fa fa-server"></i> Role</label>
                                <select class="form-control" name="role_id" id="role_id" required>
                                  <option value="" selected>Select Role</option>
                                  <?php
                                  foreach ($rs as $role) {
                                    echo "<option value='" . trim($role["role_name"]) . "'>" . trim($role["role_name"]) . "</option>";
                                  }
                                  ?>
                                </select>
                              </div>
                            </div>
                            <!-- <div class="form-group" id="qr_id_string">
                              <div class="input_row">
                                <label class="form-control-label"><i class="fa fa-user"></i> QR ID</label>
                                <input type="text" name="qr_id" id="qr_id" class="form-control">
                              </div>
                            </div> -->
                            <div class="form-group">
                              <div class="input_row">
                                <label class="form-control-label"><i class="fa fa-user"></i> User ID</label>
                                <input type="text" name="username" id="username" class="form-control">
                              </div>
                            </div>
                            <div class="form-group">
                              <div class="input_row">
                                <label class="form-control-label"><i class="fa fa-lock"></i> Password</label>
                                <input type="password" name="password" id="password" class="form-control" />
                              </div>
                            </div>
                            <!--	<div class="form-group">
							
							 <div class="input_row" >
							 <div class="cap_cont">
							 <img src="CaptchaSecurityImages.php?width=115&height=33&characters=5" alt="captcha image" id="capt" /><img src="img/reload.png" class="cap" alt="" onClick="reload();"/>
							 </div>
							 
							 </div>
							</div>
							<div class="form-group">
							 <div class="input_row">
                                <label class="form-control-label"><i class="fa fa-edit"></i> Captcha </label>
                                <input type="text" id="security_code" name="security_code" class="form-control" />
								</div>
                            </div>
-->
                            <div class="col-lg-12 login-button">
                              <div class="input_row">
                                <input type="submit" id="check" class="btn btn-outline-primary" value="Login">
                                <input type="reset" class="btn btn-outline-primary" value="Reset">
                              </div>
                            </div>
                            <div class="col-lg-12 login-text text-center">
                              <!--<A href="#">Forgot Password !!</A> -->
                            </div>
                        </div>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </li>
          </ul>
        </nav>
      </div>
      <div class="tm-main-content" style="height:auto; min-height:100vh;">
        <div class="main_area">
          <div class="logincontentarea">
            <!-- <div class="pageheading"></i>
					  <i class="fa fa-pencil-square"></i> Income Tax 
					 <i class="fa fa-database"></i> GPF
					 <i class="fa fa-group"></i> Employee 
					</div>-->
            <div class="tab">
              <button class="tablinks active" onclick="openCity(event, 'Info')"><i class="fa fa-info-circle"></i> Information</button>
              <button class="tablinks" onclick="openCity(event, 'Manual')"><i class="fa fa-file-pdf-o"></i> User Manual</button>
              <button class="tablinks" onclick="openCity(event, 'FAQ')"><i class="fa fa-question-circle"></i> FAQ</button>
              <button class="tablinks" onclick="openCity(event, 'Contact')"><i class="fa fa-phone"></i> Contact Us</button>
            </div>
            <div id="Info" class="tabcontent" style="display:block;">
              <h3>Information </h3>
              <p style="font-family: SiemensSans, Helvetica Neue, Helvetica, Arial, sans-serif;">This DPM software is developed to implement Andon, increase productivity and minimize paperwork of our factory. Digitalizing the checklists and making workbench query’s directly available to the worker, makes the production of the breakers efficient and solving of issues like breakdown, rework etc. faster on the assembly line.
                The main purpose of developing this software is to track products, gives real time position of products, to create a database of product history to improve quality, material availability and infrastructure
                This software is developed under the guidance of a shop engineers and operators, keeping the design and functionality minimalistic so as the operators have no issue in using the software to the fullest of its potential.
              </p>
            </div>
            <div id="Manual" class="tabcontent">
              <h3>User Manual</h3>
              <p style="font-family: SiemensSans, Helvetica Neue, Helvetica, Arial, sans-serif;">Some Text Here for user manual info or the download link for the manual.</p>
            </div>
            <div id="FAQ" class="tabcontent">
              <h3>FAQ's</h3>
              <p style="font-family: SiemensSans, Helvetica Neue, Helvetica, Arial, sans-serif;">Some Text Here for FAQs or the download link for the FAQs.</p>
            </div>
            <div id="Contact" class="tabcontent">
              <h3>Contact Us</h3>
              <p style="font-family: SiemensSans, Helvetica Neue, Helvetica, Arial, sans-serif;">Contact List Related to the Department.</p>
            </div>
            <div class="toplink">
              <!--<A href="index.html" class="backlink"><i class="fa fa-home"></i> Back To Home</A> -->
            </div>
          </div>
          <!-- slider -->
        </div>
      </div>
    </div>
  </div>
  <footer class="tm-footer" style="position:static; width:100%;">
    2025 DPM<br />Designed and Developed for SI EA O AIS THA
  </footer>
  <!-- load JS files -->
  <script src="js/jquery-1.11.3.min.js"></script> <!-- jQuery (https://jquery.com/download/) -->
  <script>
    function openCity(evt, cityName) {
      var i, tabcontent, tablinks;
      tabcontent = document.getElementsByClassName("tabcontent");
      for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
      }
      tablinks = document.getElementsByClassName("tablinks");
      for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
      }
      document.getElementById(cityName).style.display = "block";
      evt.currentTarget.className += " active";
    }

    $(document).ready(function() {
      $('#check').click(function() {
        var role_id = $('#role_id').val();
        var username = $('#username').val();
        var password = $('#password').val();
        if (role_id == "") {
          alert('Please Select Roles');
          return false;
        }
        if (username == "") {
          alert('Please enter username');
          return false;
        }
        if (password == "") {
          alert('Please enter password');
          return false;
        }
      });
    });

    function reload()
    {
      img = document.getElementById("capt");
      img.src = "CaptchaSecurityImages.php";
    }

    $(function() {
      var ajaxRequestMade = false;
      $('#qr_id').keyup(function() {
        //   alert('Hello');
        var qr_string = $('#qr_id').val();
        var username = qr_string.substr(0, 8);
        var passward = qr_string.substr(8);
        //alert('vendor_no'+qr_string.length);
        $('#username').val(username);
        $('#password').val(passward);

      });
    });

    $(function() {
      $("#role_id").change(function() {
        // var selectedText = $(this).find("option:selected").text();
        var selectedValue = $(this).val();
        if (selectedValue == "Admin") {
          //alert('hello');
          $("#qr_id_string").hide();
        } else {
          $("#qr_id_string").show();
        }
        //alert("Selected Text: " + selectedText + " Value: " + selectedValue);
      });
    });
  </script>
</body>
</html>