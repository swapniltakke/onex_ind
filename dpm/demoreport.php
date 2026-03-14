<?php
include 'DatabaseConfig.php';
?>

<table border="1">
  <caption>Details</caption>
  <tr>
    <th>Product Count
    <th>Product Name
    <th>Station Name
  </tr>

  <?php

    //$sql = "SELECT * FROM tbl_transactions where station_name='B2'   ";
    $sql = "SELECT 
    product_name, 
    COUNT(product_id) AS product_id,  -- or MAX(id) or any other aggregation function
    station_name
FROM 
    tbl_transactions
where station_name='B2' and product_name='Sion M25'
GROUP BY 
    product_name, station_name";
    $res = odbc_exec($conn, $sql);

    if ($res == FALSE) die ("could not execute statement $sql<br />");

    while (odbc_fetch_row($res)) // while there are rows
    {
       echo "<tr>\n";   
        echo "  <td>" . odbc_result($res, "product_id") . "\n";
       echo "  <td>" . odbc_result($res, "product_name") . "\n";
       echo "  <td>" . odbc_result($res, "station_name") . "\n";
       echo "</tr>\n";
    }
  ?>
  </table>


  <table border="1">
  <caption>Details</caption>
  <tr>
    <th>Product ID
    <th>Product Name
    <th>Station Name
  </tr>

  <?php

$sql = "SELECT 
product_name, 
COUNT(product_id) AS product_id,  -- or MAX(id) or any other aggregation function
station_name
FROM 
tbl_transactions
where station_name='No Load - A' and product_name='3AH3'
GROUP BY 
product_name, station_name";
    $res = odbc_exec($conn, $sql);

    if ($res == FALSE) die ("could not execute statement $sql<br />");

    while (odbc_fetch_row($res)) // while there are rows
    {
       echo "<tr>\n";
       echo "  <td>" . odbc_result($res, "product_id") . "\n";
       echo "  <td>" . odbc_result($res, "product_name") . "\n";
       echo "  <td>" . odbc_result($res, "station_name") . "\n";
       echo "</tr>\n";
    }
  ?>
  </table>