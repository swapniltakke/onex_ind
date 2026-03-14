<?php

include('shared/SharedManager.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

$logFile = __DIR__ . '/kafka_consumer_log.txt';

function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] $message" . PHP_EOL;
    
    echo $logEntry;
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

logMessage("=== KAFKA CONSUMER STARTED ===");

$bootstrapServer = 'pkc-7xoy1.eu-central-1.aws.confluent.cloud:9092';
$apiKey = '3FDNHCNGTZY7JCZE';
$apiSecret = 'cflt9uQpq7KiZNhatne8qbNqGftLPSo2zGAYDYi105PkbLOOYHq9a38ylWc9YkKQ';
$topic = 'central-mdp-dev-ais-tha-raw';

logMessage("Configuration loaded. Using topic: $topic");

$messageCount = 0;
$errorCount = 0;

// ===== TEST DATABASE CONNECTION =====
try {
    $testQuery = "SELECT 1";
    DbManager::fetchPDOQuery('spectra_db', $testQuery, []);
    logMessage("✓ Successfully connected to database");
} catch (Exception $e) {
    logMessage("CRITICAL ERROR: Database connection failed: " . $e->getMessage());
    die();
}

// ===== CONFIGURE KAFKA CONNECTION =====
try {
    $conf = new RdKafka\Conf();
    $conf->set('bootstrap.servers', $bootstrapServer);
    $conf->set('sasl.username', $apiKey);
    $conf->set('sasl.password', $apiSecret);
    $conf->set('security.protocol', 'SASL_SSL');
    $conf->set('sasl.mechanisms', 'PLAIN');
    $conf->set('group.id', 'central-mdp-dev-ais-tha-raw-connector'); 
    $conf->set('auto.offset.reset', 'earliest');
    $conf->set('enable.auto.commit', 'true');
    
    logMessage("Kafka configuration prepared");
} catch (Exception $e) {
    logMessage("CRITICAL ERROR: Failed to create Kafka configuration: " . $e->getMessage());
    die();
}

// ===== CREATE KAFKA CONSUMER =====
try {
    $consumer = new RdKafka\KafkaConsumer($conf);
    $consumer->subscribe([$topic]);
    logMessage("✓ Successfully connected to Kafka and subscribed to topic: $topic");
} catch (Exception $e) {
    logMessage("CRITICAL ERROR: Failed to connect to Kafka: " . $e->getMessage());
    die();
}

// ===== CONSUME MESSAGES =====
logMessage("Starting message consumption...");

$runtime = 300; // 5 minutes
$endTime = time() + $runtime;

while (time() < $endTime) {

    $message = $consumer->consume(10000);

    if ($message->err === RD_KAFKA_RESP_ERR__TIMED_OUT) {
        logMessage("No new messages (waiting...)");
        continue;
    }

    if ($message->err !== RD_KAFKA_RESP_ERR_NO_ERROR) {
        logMessage("Kafka error: " . $message->errstr());
        continue;
    }

    // ================= PARSE MESSAGE =================
    $jsonData = json_decode($message->payload, true);

    if (!is_array($jsonData)) {
        logMessage("WARNING: Invalid JSON received");
        logMessage("RAW: " . substr($message->payload, 0, 200));
        continue;
    }

    // ================= EXTRACT DATA FROM CONSOLIDATED FORMAT =================
    // Handle both formats: with and without payload wrapper
    
    $payload = null;
    $topicName = null;

    // Format 1: {topic: "...", payload: {...}}
    if (isset($jsonData['payload']) && isset($jsonData['topic'])) {
        $payload = $jsonData['payload'];
        $topicName = $jsonData['topic'];
    }
    // Format 2: Direct payload format
    else if (isset($jsonData['machine']) && isset($jsonData['datapoints'])) {
        $payload = $jsonData;
        $topicName = $message->topic_name;
    }

    if (!$payload) {
        logMessage("WARNING: Could not extract payload from message");
        continue;
    }

    // ================= EXTRACT FIELDS =================
    $machine = $payload['machine'] ?? 'UNKNOWN_MACHINE';
    $timestamp = $payload['timestamp'] ?? (int)(microtime(true) * 1000);
    $datapoints = $payload['datapoints'] ?? [];

    // Validate we have datapoints
    if (!is_array($datapoints) || count($datapoints) === 0) {
        logMessage("WARNING: No datapoints found for machine: $machine");
        logMessage("Payload: " . json_encode($payload));
        continue;
    }

    // ================= CONVERT DATAPOINTS TO STRING =================
    // Format: "Temperature: 45.2, Pressure: 102.5, Humidity: 65"
    $datapointsString = '';
    $datapointsList = [];
    
    foreach ($datapoints as $name => $value) {
        $datapointsList[] = "$name: $value";
    }
    $datapointsString = implode(', ', $datapointsList);

    // Also keep JSON for detailed queries
    $datapointsJson = json_encode($datapoints);

    // ================= GENERATE JOB ID =================
    $job_id = 'JOB_' . $machine . '_' . date('YmdHis') . '_' . $message->offset;

    // ================= INSERT INTO DATABASE =================
    $sql = "
        INSERT INTO machine_snapshot_update
        (
            job_id,
            machine,
            datapoints,
            snapshot_ts,
            tag_count,
            expected_tag_count,
            missing_tag_count,
            is_complete,
            kafka_topic,
            kafka_partition,
            kafka_offset
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";

    $datapointCount = count($datapoints);
    $params = [
        $job_id,
        $machine,
        $datapointsString,  // <-- STORE AS STRING: "Temperature: 45.2, Pressure: 102.5"
        $timestamp,
        $datapointCount,
        $datapointCount,
        0,
        1,
        $topicName ?? $message->topic_name,
        $message->partition,
        $message->offset
    ];

    try {
        DbManager::fetchPDOQuery('spectra_db', $sql, $params);
        $messageCount++;
        logMessage("✓ Job=$job_id | Machine=$machine | Datapoints=$datapointCount | Data=$datapointsString");
        
        // Optional: Also store JSON version in separate column if you have it
        // $updateSql = "UPDATE machine_snapshot_update SET datapoints_json = ? WHERE job_id = ?";
        // DbManager::fetchPDOQuery('spectra_db', $updateSql, [$datapointsJson, $job_id]);

    } catch (Exception $e) {
        $errorCount++;
        logMessage("DB ERROR: " . $e->getMessage());
        logMessage("SQL: $sql");
        logMessage("Params: " . json_encode($params));
    }
}

// Close consumer
try {
    $consumer->close();
    logMessage("✓ Kafka consumer closed");
} catch (Exception $e) {
    logMessage("WARNING: Error closing consumer: " . $e->getMessage());
}

logMessage("=== KAFKA CONSUMER FINISHED ===");
logMessage("Messages processed: $messageCount");
logMessage("Errors: $errorCount");

?>