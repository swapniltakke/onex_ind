<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dynamic Character Display</title>
    <style>
        .label {
            display: block;
            margin: 5px 0;
            font-weight: bold;
        }
    </style>
</head>
<body>

<h2>Enter a String</h2>
<form method="POST">
    <input type="text" name="inputString" placeholder="Enter your string" required>
    <button type="submit">Submit</button>
</form>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $inputString = $_POST['inputString']; // Get the input string
    
    // Get the first 8 characters
    $firstEight = substr($inputString, 0, 8); 
    
    // Get the last 12 characters
    $lastTwelve = substr($inputString, -12); 

    echo '<h3>First 8 Characters:</h3>';
    echo '<div class="label">' . htmlspecialchars($firstEight) . '</div>'; // Display as label

    echo '<h3>Last 12 Characters:</h3>';
    echo '<div class="label">' . htmlspecialchars($lastTwelve) . '</div>'; // Display as label
}
?>

</body>
</html>
