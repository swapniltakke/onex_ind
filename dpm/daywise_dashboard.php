<?php
include 'DatabaseConfig.php';
session_start();
$user=$_SESSION['username'];
$pass = $_SESSION['pass'];
include('header.php');
include('menu_spectra.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Today's Product Count Report</title>
</head>

<body>
    <div><center>
    <h2>Product-wise Count for Today (<?php echo date('d-m-Y'); ?>)</h2>
    <table border='1' align='center'>
        <tr>
            <th>Product Name</th>
            <th>Total Count</th>
        </tr>

        <?php
        // Step 1: Set the current date
        $startDate = date('Y-m-d') . ' 00:00:00'; // Start of today
        $endDate = date('Y-m-d') . ' 23:59:59'; // End of today

        // Step 3: Fetching Product-wise Count for Today
        $sql = "SELECT product_name, COUNT(*) AS total_count 
                FROM tbl_transactions 
                WHERE up_date BETWEEN ? AND ? and stage_id = '3'
                GROUP BY product_name";
        $stmt = odbc_prepare($conn, $sql);

        if ($stmt) {
            // Execute the prepared statement with bound parameters
            odbc_execute($stmt, array($startDate, $endDate));

            // Step 4: Displaying the Report
            while ($row = odbc_fetch_array($stmt)) {
                echo "<tr>
                        <td>{$row['product_name']}</td>
                        <td>{$row['total_count']}</td>
                    </tr>";
            }

            // if (odbc_num_rows($stmt) == 0) {
            //     echo "<tr><td colspan='2'>No records found for today.</td></tr>";
            // }
        } else {
            echo "Failed to prepare the SQL statement.";
        }

        // Close the ODBC connection
        odbc_close($conn);
        ?>
    </table>

    </center></div>
    <div>


    <div>
</body>
</html>
