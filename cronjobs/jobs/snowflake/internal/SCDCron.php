<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
ini_set('memory_limit', '8192M');
ini_set('max_execution_time', 0);
require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/SharedManager.php";

$SCD_API = SharedManager::getFromSharedEnv('SCD_API');
$localities = ["5655"];
$allData = [];

// Rate limiting parameters
$requestsPerMinute = 50;
$timeWindow = 60; // 1 minute in seconds
$requestCount = 0;
$batchStartTime = time();

$currentMonth = date('M');
$currentYear = date('Y');

// Array of allowed cost locations
$allowedCostLocations = [
    '62050997', '62050998', '62057101', '62057102', '62057107', '62057108', '62057109', 
    '62057110', '62057111', '62057112', '62057113', '62057116', '62057117', '62057118', 
    '62057119', '62057120', '62057121', '62057122', '62057123', '62057126', '62057127', 
    '62057128', '62057129', '62057130', '62057131', '62057132', '62057133', '62057134', 
    '62057135', '62057136', '62057137', '62057138', '62057139', '62057140', '62057141', 
    '62057143', '62057144', '62057145', '62057149', '62057150', '62057151', '62057153', 
    '62057154', '62057156', '62057160', '62057161', '62057162', '62057196', '62057197', 
    '62057199', '62100495', '62100895', '62107241', '62107295', '62107363', '62107365', 
    '62327863', '62327867', '62327869', '62327870', '62327871', '62327873', '62327874', 
    '62327877', '62327878', '62327879', '62327880', '62327881', '62327882', '62327883', 
    '62327884', '62327885', '62327888', '62327889', '62327890', '62327891', '62327892', 
    '62327893', '62327894', '62327895', '62327896', '62327897', '62327898', '62327899', 
    '62327900', '62327901', '62327902', '62327903', '62327904', '62327905'
];

foreach ($localities as $locality) {
    $url = $SCD_API."people?page[limit]=100&filter[costLocationUnit]=" . urlencode($locality);
    $SCD_API_KEY = SharedManager::getFromSharedEnv('SCD_API_KEY');

    $headers = array(
        "Accept: application/vnd.api+json",
        "apikey: " . $SCD_API_KEY
    );

    do {
        // Check if we need to pause for rate limiting
        $requestCount++;
        if ($requestCount >= $requestsPerMinute) {
            $elapsedTime = time() - $batchStartTime;
            if ($elapsedTime < $timeWindow) {
                $sleepTime = $timeWindow - $elapsedTime;
                echo "Rate limit reached. Sleeping for {$sleepTime} seconds...\n";
                sleep($sleepTime);
            }
            $requestCount = 0;
            $batchStartTime = time();
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        $response = curl_exec($ch);
        
        if ($response === false) {
            echo "Curl Error: " . curl_error($ch);
            break;
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode !== 200) {
            echo "HTTP Error: " . $httpCode . "\n";
            echo "Response: " . $response . "\n";
            break;
        }

        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "JSON Decode Error: " . json_last_error_msg() . "\n";
            echo "Raw Response: " . $response . "\n";
            break;
        }

        if (!isset($data['data']) || !is_array($data['data'])) {
            echo "Invalid API Response Structure. Response: \n";
            print_r($data);
            break;
        }

        // Filter data based on allowed cost locations
        foreach ($data['data'] as $item) {
            if (isset($item['attributes']['costLocation']) && 
                in_array($item['attributes']['costLocation'], $allowedCostLocations)) {
                $allData[] = $item;
            }
        }

        echo "Processed batch. Current total records: " . count($allData) . "\n";

        if (isset($data['meta']['page']['nextCursor'])) {
            $url = $SCD_API."people?page[limit]=100&filter[costLocationUnit]=" . urlencode($locality) . 
                  "&page[cursor]=" . urlencode($data['meta']['page']['nextCursor']);
        } else {
            $url = null;
        }
        curl_close($ch);

    } while ($url !== null);
}

// Check if we have any data before proceeding
if (empty($allData)) {
    die("No data received from API or no records match the allowed cost locations");
}

echo "Total records to process: " . count($allData) . "\n";

$insertscdQuery = "INSERT INTO scd_details (
        type, gid, countryCode, companyName, organization, organizationId, organizationalUnit, altOrganizationalUnit, 
        locality, city, commonName, surname, givenName, preferredSurname, gender, nickname, email, department, departmentNumber, 
        departmentText, costLocation, costLocationUnit, costLocationUnitName, timezone, telephoneNumber, mobileNumber, validDate, 
        clientId, userType, recordType, status, contractStatus, sponsor, representative, secretary, inCompanyManager, lineManager, 
        building, room, leaveDate, expireDate, restStartDate, restEndDate, locationId, modifyDate, distinguishedName, graduateTitle, 
        personalTitle, mainFunction, month, year
    ) VALUES (
        :type, :gid, :countryCode, :companyName, :organization, :organizationId, :organizationalUnit, :altOrganizationalUnit,
        :locality, :city, :commonName, :surname, :givenName, :preferredSurname, :gender, :nickname, :email, :department, :departmentNumber,
        :departmentText, :costLocation, :costLocationUnit, :costLocationUnitName, :timezone, :telephoneNumber, :mobileNumber, :validDate,
        :clientId, :userType, :recordType, :status, :contractStatus, :sponsor, :representative, :secretary, :inCompanyManager, :lineManager,
        :building, :room, :leaveDate, :expireDate, :restStartDate, :restEndDate, :locationId, :modifyDate, :distinguishedName, :graduateTitle,
        :personalTitle, :mainFunction, :month, :year
    )";

$insertedCount = 0;

foreach ($allData as $value) {
    $insertResult = DbManager::fetchPDOQuery('spectra_db', $insertscdQuery, [
        ':type' => $value['type'],
        ':gid' => $value['attributes']['gid'],
        ':countryCode' => $value['attributes']['countryCode'],
        ':companyName' => $value['attributes']['companyName'],
        ':organization' => $value['attributes']['organization'],
        ':organizationId' => $value['attributes']['organizationId'],
        ':organizationalUnit' => $value['attributes']['organizationalUnit'],
        ':altOrganizationalUnit' => $value['attributes']['altOrganizationalUnit'],
        ':locality' => $value['attributes']['locality'],
        ':city' => $value['attributes']['city'],
        ':commonName' => $value['attributes']['commonName'],
        ':surname' => $value['attributes']['surname'],
        ':givenName' => $value['attributes']['givenName'],
        ':preferredSurname' => $value['attributes']['preferredSurname'],
        ':gender' => $value['attributes']['gender'],
        ':nickname' => $value['attributes']['nickname'],
        ':email' => $value['attributes']['email'],
        ':department' => $value['attributes']['department'],
        ':departmentNumber' => $value['attributes']['departmentNumber'],
        ':departmentText' => $value['attributes']['departmentText'],
        ':costLocation' => $value['attributes']['costLocation'],
        ':costLocationUnit' => $value['attributes']['costLocationUnit'],
        ':costLocationUnitName' => $value['attributes']['costLocationUnitName'],
        ':timezone' => $value['attributes']['timezone'],
        ':telephoneNumber' => $value['attributes']['telephoneNumber'],
        ':mobileNumber' => $value['attributes']['mobileNumber'],
        ':validDate' => $value['attributes']['validDate'],
        ':clientId' => $value['attributes']['clientId'],
        ':userType' => $value['attributes']['userType'],
        ':recordType' => $value['attributes']['recordType'],
        ':status' => $value['attributes']['status'],
        ':contractStatus' => $value['attributes']['contractStatus'],
        ':sponsor' => $value['attributes']['sponsor'],
        ':representative' => $value['attributes']['representative'],
        ':secretary' => $value['attributes']['secretary'],
        ':inCompanyManager' => $value['attributes']['inCompanyManager'],
        ':lineManager' => $value['attributes']['lineManager'],
        ':building' => $value['attributes']['building'],
        ':room' => $value['attributes']['room'],
        ':leaveDate' => $value['attributes']['leaveDate'],
        ':expireDate' => $value['attributes']['expireDate'],
        ':restStartDate' => $value['attributes']['restStartDate'],
        ':restEndDate' => $value['attributes']['restEndDate'],
        ':locationId' => $value['attributes']['locationId'],
        ':modifyDate' => $value['attributes']['modifyDate'],
        ':distinguishedName' => $value['attributes']['distinguishedName'],
        ':graduateTitle' => $value['attributes']['graduateTitle'],
        ':personalTitle' => $value['attributes']['personalTitle'],
        ':mainFunction' => $value['attributes']['mainFunction'],
        ':month' => $currentMonth,
        ':year' => $currentYear
    ]);
    
    if ($insertResult) {
        $insertedCount++;
    }
}

$totalDataCount = count($allData);
echo "Success: $insertedCount records inserted out of $totalDataCount total records.";
?>