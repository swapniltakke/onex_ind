<?php
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=warehousedetails.csv');
session_start();

include('shared/CommonManager.php');
// Open output stream
$output = fopen('php://output', 'w');

// Sanitize and validate input
$fromDate = isset($_GET['fromDate']) ? filter_var($_GET['fromDate'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
$toDate = isset($_GET['toDate']) ? filter_var($_GET['toDate'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';

// Validate date format (YYYY-MM-DD)
if (!DateTime::createFromFormat('Y-m-d', $fromDate) || !DateTime::createFromFormat('Y-m-d', $toDate)) {
    die("Invalid date format.");
}

// Prepare your query with placeholders
$query = "SELECT tr_id, product_name, barcode, remark, up_date 
          FROM tbl_warehousebarcode 
          WHERE up_date BETWEEN :fromDate AND :toDate";
$stmt = DbManager::fetchPDOQueryData('spectra_db',$query,[":fromDate" => "$fromDate", ":toDate" => "$toDate"])["data"];

// Fetch the first row for the header
$firstRow = true;
foreach ($stmt as $row) {
    // Split the barcode into four segments of 8 characters
    $barcode = $row['barcode'];
    $barcodes = [
        substr($barcode, 0, 8),  
        substr($barcode, 8, -29),  
        substr($barcode, -29, 10), 
        substr($barcode, -18, 6), 
        substr($barcode, -12)
    ];
    
    // Create a new row with the split barcode columns
    $outputRow = [
        $row['tr_id'],
        $row['product_name'],
        ...$barcodes,  // Spread operator to add barcode segments
        $row['remark'],
        $row['up_date']
    ];
    
    if ($firstRow) {
        // Adjust header to include new barcode columns
        fputcsv($output, array_merge(['tr_id', 'product_name', 'serial_no', 'MFLB_no', 'sales_order_no', 'item_no','production_no', 'remark', 'up_date'], array_keys($row)));
        $firstRow = false;
    }
    
    // Write data rows
    fputcsv($output, $outputRow);
}

// Close output stream
fclose($output);
exit();
?>