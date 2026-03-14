<?php
session_start();
if (!isset($_SESSION['session_id'])) {
    $_SESSION['session_id'] = bin2hex(random_bytes(32)); 
}
session_regenerate_id(true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$action = $_POST['action'] ?? '';
	$ip_address = SharedManager::get_ip_address();
	$login_time = date('Y-m-d H:i:s'); 
	$session_expiry = date('Y-m-d H:i:s'); 
	$is_active = 1;

	if ($action === 'auto_login') {
		// Check if user is already logged in
		$email_id = SharedManager::getUser()["Email"];
		$not_allowed_emails = ['mfsd.in@siemens.com', 'rginslpanelmanufacturing.in@siemens.com'];
		
		if (!in_array($email_id, $not_allowed_emails)) {
			$user = SharedManager::getUser()["Name"];
			$username = SharedManager::getUser()["FullName"];
			$user_id = SharedManager::getUser()["GID"];
			$_SESSION['username'] = $username;
			$_SESSION['user_id'] = $user_id;
			$_SESSION['role_name'] = "Entra Id";

			$sql1 = "insert into tbl_user_sessions(username,session_id,ip_address,login_time,session_expiry,is_active) values (:username,:session_id,:ip_address,:login_time,:session_expiry,:is_active)" ;
			$query = DbManager::fetchPDOQueryData('spectra_db', $sql1, [":username" => $username,":session_id" => $_SESSION['session_id'],":ip_address" => $ip_address,":login_time" => $login_time,":session_expiry" => $session_expiry,":is_active" => $is_active]);

			echo json_encode([
				'success' => true,
				'redirect_url' => '/dpm/dwc/material_search.php',
				'message' => 'Login successful',
				'user_data' => [
					'username' => $username,
					'role' => $results['role_name']
				]
			]);
			exit;
		} else {
			echo json_encode([
				'success' => false,
				'redirect_url' => '/dpm/dwc/index.php',
				'message' => "Manual Authentication Required.\n You are not authorized to log in via Entra ID. Please use manual login credentials."
			]);
			exit;
		}
	} else if ($action === 'manual_login') {
		$role_id = trim($_POST['role_id']);
		$username = $_POST['username'];
		$password = $_POST['password'];
		$encrypted_password = SharedManager::encrypt_password($password);
		
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

		if ($username == $user && $password == $pass && $role_id == 'Panel') {
			echo json_encode([
				'success' => true,
				'redirect_url' => '/dpm/dwc/material_search.php',
				'message' => 'Login successful',
				'user_data' => [
					'username' => $user,
					'role' => $results['role_name']
				]
			]);
			exit;
		} else {
			echo json_encode([
				'success' => false,
				'redirect_url' => '/dpm/dwc/index.php',
				'message' => 'Please Enter Correct Username and Password'
			]);
			exit;
		}
	} else {
		echo json_encode([
			'success' => false,
			'redirect_url' => '/dpm/dwc/index.php',
			'message' => 'An error occurred. Please try again.'
		]);
		exit;
	}
}
?>