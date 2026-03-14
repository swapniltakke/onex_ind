<?php
                              
include 'DatabaseConfig.php';
$sql = 'SELECT * FROM tbl_roles';
		
  $rs = odbc_exec($conn, $sql) or  die ('ERROR...Qry');


                              ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Responsive Login Form</title>
    <style>
        body {
            font-family: Siemens Sans Black;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            /* background:url("../img/Xceleratoge.jpg"); */
          /*   background: linear-gradient(to right, #009999, #000028); Gradient background */
            background: linear-gradient(to right, #F3F3F0, #F3F3F0);
           color: #fff;  /* Text color to contrast with the gradient */
        }
        header, footer {
            background-color: #000028; 
           /* background-color: #000028;  Darker color for header and footer */
            color: #fff;
            padding: 40px 80px;
            text-align: left;
        }
        .container {
            max-width: 400px;
            width: 100%;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin: auto;
            margin-top: 20px;
            margin-bottom: 20px;
        }
        h2 {
            margin-top: 0;
            text-align: center;
            color: #009999;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #009999;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #009999;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-group button {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 4px;
            background-color: #009999;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
        }
        .form-group button:hover {
            background-color: #0056b3;
        }
        .img {
            display: block;
            margin-left: auto;
            margin-right: auto;
           
        }
        .option1{
            ackground-color: #009999;
        }
        @media (max-width: 600px) {
            .container {
                padding: 15px;
            }
            .form-group input, .form-group select, .form-group button {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <header>
        <!--<h1>SIEMENS</h1>-->
         <img src="img/siemens-logo2.svg" class="logo" style="float: left"; width="200"> 
    </header>
    
    <div class="container">
     <h2>Welcome to DPM</h2>
      </div>
    <div class="container">
    <span><img src="img/login_fav1.png" class="img" width="250";></span> 
        <h2>LOGIN</h2>
        <form action="loginAuth.php" name="form1" method="POST">
            <div class="form-group">
                <label for="role_id">Role</label>
                <select class="option1" id="role_id" name="role_id" required>
                <option class="option1" value="" selected>Select Role</option>
                       <?php
                                  while(odbc_fetch_row($rs))
                                  { 
                                   echo "<option value='".trim(odbc_result($rs, 'role_name'))."'>".trim(odbc_result($rs, 'role_name'))."</option>";
 } ?>
                </select>
            </div>
            <!-- <div class="form-group">
                <label for="qr_id">QR ID</label>
                <input type="text" id="qr_id" name="qr_id" required>
            </div> -->
            <div class="form-group">
                <label for="username">User ID</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <button type="submit">Login</button>
            </div>


        </form>
    </div>
    <!-- <footer>
        <p>&copy; 2024 My Website. All rights reserved.</p>
    </footer> -->
</body>
</html>
