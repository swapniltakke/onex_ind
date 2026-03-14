<?php
session_start();
if (!isset($_SESSION['session_id'])) {
    $_SESSION['session_id'] = bin2hex(random_bytes(32)); 
}
session_regenerate_id(true);

$role_id = trim($_POST['role_id']);
$username = $_POST['username'];
$password = $_POST['password'];
$encrypted_password = SharedManager::encrypt_password($password);
$ip_address = SharedManager::get_ip_address();
$login_time = date('Y-m-d H:i:s'); 
$session_expiry = date('Y-m-d H:i:s'); 
$is_active = 1;

$sql1 = "SELECT role_id FROM tbl_roles where role_name=:role_id";
$result1 = DbManager::fetchPDOQueryData('spectra_db', $sql1, [":role_id" => $role_id])["data"];

$sql = "SELECT u.*, r.role_name FROM tbl_user_login u JOIN tbl_roles r ON u.role_id = r.role_id WHERE u.user_name = :username AND u.password = :password AND u.role_id = :role_id";
$result = DbManager::fetchPDOQueryData('spectra_db', $sql, [":username" => $username, ":password" => $encrypted_password, ":role_id" => $result1[0]['role_id']])["data"];
foreach ($result as $results) {
	$user = $results['user_name'];
	$pass = $results['password'];
	$pass = SharedManager::decrypt_password($pass);
	$user_id = $results['user_id'];
	$_SESSION['username'] = $user;
	$_SESSION['user_id'] = $user_id;
	$_SESSION['pass'] = $pass;
	$_SESSION['role_id'] = $results['role_id'];
	$_SESSION['role_name'] = $results['role_name'];
}
$sql1 = "insert into tbl_user_sessions(username,password,session_id,ip_address,login_time,session_expiry,is_active) values (:username,:password,:session_id,:ip_address,:login_time,:session_expiry,:is_active)" ;
$query = DbManager::fetchPDOQueryData('spectra_db', $sql1, [":username" => $username,":password" => $encrypted_password,":session_id" => $_SESSION['session_id'],":ip_address" => $ip_address,":login_time" => $login_time,":session_expiry" => $session_expiry,":is_active" => $is_active]);


if ($username == $user && $password == $pass && $role_id == 'Admin') {
	header("Location: Superadmindash.php");
} else if ($username == $user && $password == $pass && $role_id == 'User') {
	header("Location: Admindash.php");
} else if ($username == $user && $password == $pass && $role_id == 'Supervisor') {
	header("Location: Supervisordash.php");
} elseif ($username == $user && $password == $pass && $role_id == 'Report') {
	header("Location: Reportdashboard.php");
} elseif ($username == $user && $password == $pass && $role_id == 'Warehouse') {
	header("Location: warehousedashboard.php");
} elseif ($username == $user && $password == $pass && $role_id == 'PAW') {
	header("Location: pawdashboard.php");
}elseif ($username == $user && $password == $pass && $role_id == 'Shaft') {
	header("Location: shaftdashboard.php");
} elseif ($username == $user && $password == $pass && $role_id == 'Stamping') {
	header("Location: Stampingdashboard.php");
} elseif ($username == $user && $password == $pass && $role_id == 'Manufacturing') {
	header("Location: Stampingdashboard.php");
}
else {
	echo '<script type="text/javascript"> alert("Please Enter Correct Username and Password ...");
				document.location.href="index.php"</script>';
	exit;
}
?>