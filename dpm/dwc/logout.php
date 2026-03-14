<?php

session_start();
$session_id = $_SESSION['session_id'];
$sql2 = "UPDATE tbl_user_sessions 
         SET is_active = 0 
         WHERE session_id = :session_id";

$query = DbManager::fetchPDOQueryData(
    'spectra_db', 
    $sql2, 
    [":session_id" => $session_id]
);

session_unset();    
session_destroy();
// Remove the 'selected_project' item from localStorage
echo '<script>localStorage.removeItem("selected_project");</script>';
echo '<script>localStorage.removeItem("selected_panel");</script>';
echo '<script>localStorage.removeItem("selected_station");</script>';
?>
<html>
<head>
<title>
</title>
<script language="javascript">
function go()
{
	document.logout.method = "POST";
	document.logout.action="index.php";
	document.logout.submit();
}
</script>
</head>
<form name="logout" id="logout" method="POST">
<?php
echo("<script language='javascript'> go();</script>");
?>
</form>
</html>