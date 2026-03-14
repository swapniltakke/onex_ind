<?php
session_start();
$user = $_SESSION['username'];
$pass = $_SESSION['pass'];

include('shared/CommonManager.php');

$res11 = [
    [
        'barcode' => '12345678901234567890123456789012345678901234567890',
        'station_name' => 'Station A',
        'user_id' => 'Samet Durak',
    ]
];

$stepCounter = 1; // Initialize the step counter

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workflow Example with Icons</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #1a1a1a;
            color: #00d1b2;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            flex-direction: column;
        }

        .hover-div {
            background-color: #2b2b2b;
            width: 200px;
            height: 100px;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            margin-bottom: 20px;
            color: #00d1b2;
            font-size: 18px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .card {
            background-color: #2b2b2b;
            border-radius: 10px;
            width: 400px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            display: none; /* Initially hidden */
            position: absolute;
            top: 50px; /* Adjust position to be above the hover div */
            z-index: 10;
        }

        .hover-div:hover + .card {
            display: block; /* Show the card when the hover div is hovered */
        }

        .title {
            font-size: 24px;
            margin-bottom: 10px;
            text-align: center;
        }

        .time {
            font-size: 12px;
            color: #ccc;
            margin-bottom: 5px;
            text-align: center;
        }

        .step {
            position: relative;
            padding-left: 40px;
            margin-left: 10px;
            border-left: 2px solid #00d1b2;
            margin-bottom: 15px;
            cursor: pointer;
        }

        .step:last-child {
            border-left: none;
        }

        .step:hover .tooltip {
            display: block;
        }

        .tooltip {
            display: none;
            position: absolute;
            left: 45px;
            top: 5px;
            background-color: #00d1b2;
            color: #fff;
            padding: 5px;
            border-radius: 5px;
            font-size: 12px;
            z-index: 10;
        }

        .operator {
            margin-top: 10px;
            font-size: 14px;
        }

        .divider {
            border-top: 1px solid #ccc;
            margin: 10px 0;
        }

        .success-icon {
            text-align: center;
            font-size: 40px;
            color: #00d1b2;
        }

        .icon {
            position: absolute;
            left: 5px;
            top: 5px;
            font-size: 18px;
            color: #00d1b2;
        }

        .step-content {
            margin-left: 45px;
        }
    </style>
</head>

<body>

    <!-- Div to hover over to show the card -->
    <div class="hover-div">
        Hover over me to see the workflow card
    </div>

    <div class="card">
        <div class="title">Rutin Test</div>
        <div class="time">Giriş Tarihi (3 gün önce)<br>14:08:28 - 24.12.2024</div>

        <?php
        foreach ($res11 as $row) {
            // Process the barcode details
            $barcode = trim($row['barcode']);
            $row['serial_no'] = substr($barcode, 0, 8);
            $row['sales_no'] = substr($barcode, -29, 10);
            $row['item_no'] = substr($barcode, -18, 6);
            $row['prod_no'] = substr($barcode, -12);

            // Line connecting this step to the next one (optional)
            echo "<div class='divider'></div>";

            // Step 1: Station Information
            echo "<div class='step'>";
            echo "<i class='fas fa-warehouse icon'></i>"; // Icon for Step 1
            echo "<div class='step-content'><strong>Step {$stepCounter}: Station Information</strong></div>";
            echo "</div>";
            $stepCounter++;

            // Line connecting this step to the next one (optional)
            echo "<div class='divider'></div>";

            // Step 2: Barcode Details
            echo "<div class='step'>";
            echo "<i class='fas fa-cogs icon'></i>"; // Icon for Step 2
            echo "<div class='step-content'><strong>" . htmlspecialchars($row['station_name']) . "</strong></div>";
            echo "</div>";
            $stepCounter++;

            // Line connecting this step to the next one (optional)
            echo "<div class='divider'></div>";

            // Step 3: Barcode details output
            echo "<div class='step'>";
            echo "<i class='fas fa-barcode icon'></i>"; // Icon for Step 3
            echo "<div class='step-content'><strong>Barcode Details</strong>";
            echo "<p><strong>Serial No:</strong> " . htmlspecialchars($row['serial_no']) . "</p>";
            echo "<p><strong>Sales No:</strong> " . htmlspecialchars($row['sales_no']) . "</p>";
            echo "<p><strong>Item No:</strong> " . htmlspecialchars($row['item_no']) . "</p>";
            echo "<p><strong>Prod No:</strong> " . htmlspecialchars($row['prod_no']) . "</p>";
            echo "</div>";
            echo "</div>";
            
            // Line connecting this step to the next one (optional)
            echo "<div class='divider'></div>";

            // Step 4: User Information (User ID)
            echo "<div class='step'>";
            echo "<div class='icon'><i class='fas fa-user'></i></div>"; // Icon for User ID
            echo "<div class='step-content'><strong>User: " . htmlspecialchars($row['user_id']) . "</strong></div>";
            echo "</div>";
            
            // Optional: Divider after the last step
            echo "<div class='divider'></div>";
        }

        ?>

        <div class="success-icon"><i class="fas fa-check"></i></div>
    </div>

</body>

</html>
