<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/composer/vendor/autoload.php";

use Firebase\JWT\JWT;

//Siemens Wiki: https://wiki.siemens.com/display/en/Snowflake+SQL+API
//Snowflake Documentation: https://docs.snowflake.com/en/developer-guide/sql-api/index
//for accepted data types: https://docs.snowflake.com/en/sql-reference/intro-summary-data-types
class Snowflake{
    protected string $CREDENTIALS_PATH = "";
    protected string $USER_KEYWORD = "";
    protected string $ROLE = "";
    protected string $WAREHOUSE = "";
    protected string $API_URL = "https://sdc-prd.snowflakecomputing.com/api/v2/statements";
    protected string $DATABASE = "";
    protected string $SCHEMA = "";
    protected int $CURL_TIMEOUT;
    protected int $CURL_BATCH_SIZE;
    protected string $STATEMENT = "";
    protected string $STATEMENT_CACHE_KEY = "";
    protected bool $CACHE_ENABLED;
    protected int|float $CACHE_TTL = 60*60*6;//6 hours
    protected array $STATEMENT_RESULTS = [];

    protected array $PARAMETERS;

    public function __construct($credentialsPath = null)
    {
        //default credentials folder path
        if(!$credentialsPath)
            $credentialsPath = $_SERVER["DOCUMENT_ROOT"] . "/../../snowflake";
        $this->CREDENTIALS_PATH = $credentialsPath;
    }

    private function generateFingerprint($publicKey) {
        // Convert the public key from PEM to DER format
        $pem = str_replace(["-----BEGIN PUBLIC KEY-----", "-----END PUBLIC KEY-----", "\n", "\r"], '', $publicKey);
        // Decode the base64 encoded string to get the DER formatted key
        $derKey = base64_decode($pem);

        // Generate the SHA-256 hash of the DER-formatted key
        $hash = hash('sha256', $derKey, true);

        // Encode the hash in Base64
        return base64_encode($hash);
    }

    private function getUserCredentials($userKey)
    {
        $getTokenQuery = "SELECT * FROM snowflake WHERE `user_keyword` = :userKeyword LIMIT 1";
        $getTokenQueryParameters = [":userKeyword" => $userKey];

        if(class_exists('DbManager'))
            return DbManager::fetchPDOQueryData('php_auth', $getTokenQuery, $getTokenQueryParameters)["data"][0];
        else
            return CronDbManager::fetchQueryData('php_auth', $getTokenQuery, $getTokenQueryParameters)["data"][0];
    }

    public function getJWTToken($userKey){
        $tokenData = $this->getUserCredentials($userKey);

        $user = $tokenData["user"];
        $token = $tokenData["token"];
        $this->ROLE = $tokenData["role"];
        $this->WAREHOUSE = $tokenData["warehouse"];
        if(!$token){
            $tokenData = $this->createJWTToken($user);
            $token = $tokenData["token"];
        }

        return $token;
    }

    public function createJWTToken($user){
        $fileNamePrefix = ucfirst(strtolower($user));
        $publicKeyFilePath = "$this->CREDENTIALS_PATH\\$fileNamePrefix"."_public_key.pub";
        $privateKeyFilePath = "$this->CREDENTIALS_PATH\\$fileNamePrefix"."_private_key.pem";
        $privateKeyPasswordPath = "$this->CREDENTIALS_PATH\\$fileNamePrefix"."_password.txt";

        $publicKeyContent = file_get_contents($publicKeyFilePath);
        $privateKeyContent = file_get_contents($privateKeyFilePath);
        $privateKeyPassword = file_get_contents($privateKeyPasswordPath);

        if($publicKeyContent === false)
            returnHttpResponse(500, "Snowflake getJWTToken(), Could not read file: $publicKeyFilePath");
        if($privateKeyContent === false)
            returnHttpResponse(500, "Snowflake getJWTToken(), Could not read file: $privateKeyFilePath");
        if($privateKeyPassword === false)
            returnHttpResponse(500, "Snowflake getJWTToken(), Could not read file: $privateKeyPasswordPath");

        $privateKeyResource = openssl_pkey_get_private($privateKeyContent, $privateKeyPassword);
        $publicKeyFingerprint = $this->generateFingerprint($publicKeyContent);

        // Snowflake specific JWT claims
        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT'
        ];

        $tokenCreatedTime = time();// Issued at time
        $tokenExpirationTime = $tokenCreatedTime + 3600;// Expiry time (1 hour from now)

        $payload = [
            'iss' => "sdc-prd.$user.SHA256:$publicKeyFingerprint",
            'sub' => "sdc-prd.$user",
            'iat' => $tokenCreatedTime,
            'exp' => $tokenExpirationTime
        ];

        $token = JWT::encode($payload, $privateKeyResource, 'RS256', null, $header);

        return [
            "token" => $token,
            "payload" => $payload
        ];
    }

    protected function getHttpHeaders(): array
    {
        $token = $this->getJWTToken($this->USER_KEYWORD);

        return [
            "Authorization: Bearer $token",
            "Content-Type: application/json",
            "Accept: */*",
            "X-Snowflake-Authorization-Token-Type: KEYPAIR_JWT"
        ];
    }

    /*{
        "statement": "CALL DEV_TEST.TRANSFORM.CRM_PROJECT_SALES_P(?);",
        "timeout": 60,
        "database": "TEST_DEV_DISTRIBUTE",
        "schema": "TRANSFORM",
        "warehouse": "DEV_ETL",
        "role": "ETL_RESTRICTED",
        "bindings" : {
            "1" : {
                "type" : "TEXT",
                "value" :"70240XXXXX"
            }
        },
        "resultSetMetaData": {
            "format": "json"
        }
    }*/
    protected function getBody($parameters = []): array
    {
        $body = [
            "statement" => $this->STATEMENT,
            "timeout" => $this->CURL_TIMEOUT,
            "database" => $this->DATABASE,
            "schema" => $this->SCHEMA,
            "warehouse" => $this->WAREHOUSE,
            "role" => $this->ROLE,
            "resultSetMetaData" => [
                "format" => "json"
            ]
        ];
        if(count($parameters) > 0){
            $bindings = [];
            foreach ($parameters as $index => $parameterInfo){
                $parameterType = $parameterInfo["type"];
                $parameterValue = $parameterInfo["value"];
                $bindings["$index"] = [
                    "type" => $parameterType,
                    "value" => $parameterValue
                ];
            }
            $body["bindings"] = $bindings;
        }

        return $body;
    }

    protected function setStatementResults($parameters, int $totalPartitionCount, int $currentPartition = 0, string $handle = ""){
        $header = $this->getHttpHeaders();

        $remainingPartitionCount = $totalPartitionCount - $currentPartition;
        $multiCurlCount = ($remainingPartitionCount >= $this->CURL_BATCH_SIZE) ? $this->CURL_BATCH_SIZE : $remainingPartitionCount + 1;

        // Array to hold individual cURL handles
        $handles = [];

        $mh = curl_multi_init();
        for($partitionIndex = 0; $partitionIndex < $multiCurlCount; $partitionIndex++){
            $nextPartitionNumber = $currentPartition + $partitionIndex;
            $API_V2_URL = ($nextPartitionNumber === 0) ? $this->API_URL : $this->API_URL . "/$handle?partition=$nextPartitionNumber";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $API_V2_URL);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->CURL_TIMEOUT); // Set a timeout for the request
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');

            if($nextPartitionNumber === 0){//if partition = 0, request is POST
                $body = $this->getBody($parameters);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
                $httpMethod = "POST";
            }
            else{//else GET
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                $httpMethod = "GET";
            }

            curl_multi_add_handle($mh, $ch);
            $handles[] = [
                'handle' => $ch,
                'method' => $httpMethod,
                'partitionNumber' => $nextPartitionNumber
            ];
        }

        // Execute all queries simultaneously, and continue when all are complete
        do {
            $status = curl_multi_exec($mh, $active);
            if($active)
                curl_multi_select($mh); // Wait for activity on any cURL connection
        } while ($active && $status === CURLM_OK);

        // Loop through the handles and get the content and response status
        foreach ($handles as $handleData) {
            $ch = $handleData['handle'];
            $method = $handleData['method'];
            $partitionNumber = $handleData['partitionNumber'];

            $response = curl_multi_getcontent($ch); // Get the response
            if($method === "GET")
                $response = gzdecode($response);

            if (curl_errno($ch)) {
                returnHttpResponse(500, 'Curl error: ' . curl_error($ch));
                exit;
            }

            $response = json_decode($response, true);
            if(!$response["resultSetMetaData"] && !$response["data"])
                returnHttpResponse(500, json_encode($response));

            $this->STATEMENT_RESULTS[$partitionNumber] = $response;

            // Remove and close the handle
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
        }
    }

    protected function getStatementResult($partitionNumber){
        $statementResult = $this->STATEMENT_RESULTS[$partitionNumber];
        if($statementResult){
            unset($this->STATEMENT_RESULTS[$partitionNumber]);
        }
        return $statementResult;
    }
}