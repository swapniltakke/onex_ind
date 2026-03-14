 
<?php
/* $DSN = 'Spectratest';
$username = 'SpectraTech';
$password = 'Spectra';
$dbname = 'spectra_db';

$conn=odbc_connect("Driver={SQL Server};Server=MD3ZE0XC;Database=$dbname;", $username, $password ) or die ( "Connection failed: " . $conn );

if ($conn >= 1) {

} else {
    print("connection Not established");
    $sqlerror = odbc_errormsg($conn);
    print($sqlerror);
}

$host = '132.186.79.48';
$port = 6201; 
$username = 'root';
$password = 'x7FA3pTL*nmoCS7f$hJT1Pm6(CRlIb';
$database = 'spectra_db';

try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully"; 

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
} */
exit('Warning: DatabaseConfig.php file has been called unexpectedly. Please review the code to ensure this call is correct and remove it if unnecessary.');
?>