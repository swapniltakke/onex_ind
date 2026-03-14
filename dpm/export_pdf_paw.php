<?php
session_start();
$user = $_SESSION['username'];
$pass = $_SESSION['pass'];
include('shared/CommonManager.php');

$query = "SELECT tr_id,product_id,product_name,barcode,remark,up_date FROM tbl_pawbarcode"; 
$result = DbManager::fetchPDOQueryData('spectra_db', $query)["data"];
ob_start();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Warehouse Data PDF Export</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

<h1>Data Export</h1>
<table>
    <thead>
        <tr>
            <th>SR No</th>
            <th>Product ID</th>
            <th>Product Name</th>
            <th>Barcode</th>
            <th>Remark</th>
            <th>Update Date</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($result as $row) { ?>
            <tr>
                <td><?php echo htmlspecialchars($row['tr_no']); ?></td>
                <td><?php echo htmlspecialchars($row['procuct_id']); ?></td>
                <td><?php echo htmlspecialchars($row['procuct_name']); ?></td>
                <td><?php echo htmlspecialchars($row['barcode']); ?></td>
                <td><?php echo htmlspecialchars($row['remark']); ?></td>
                <td><?php echo htmlspecialchars($row['up_date']); ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>

</body>
</html>

<?php
// Get the contents of the buffer
$html = ob_get_clean();
// Print the page using JavaScript
echo "<script>window.onload = function() { window.print(); }</script>";
echo $html;
exit();
?>