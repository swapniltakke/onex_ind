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

//$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie.
// Note: This will destroy the session, and not just the session data!
//if (isset($_COOKIE[session_name()])) 
//{
   //setcookie(session_name(), '', time()-42000, '/');
//}

// Finally, destroy the session.
//session_destroy();
echo("<script language='javascript'> go();</script>");

echo "<a href='frm_lastpage.htm'>click here</a>";
?>
</html>