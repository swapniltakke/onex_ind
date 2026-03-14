<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/snowflake/Snowflake.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/snowflake/SnowflakeResult.php";

class SnowflakeQuery extends Snowflake {
    private array $STATEMENT_RESULT = [];
    private object $SNOWFLAKE_QUERY_RESULT;
    public function __construct($userKeyword, $database, $schema, $query, $parameters = [], $cache = true, $curlOptions = []){
        parent::__construct();

        $this->USER_KEYWORD = $userKeyword;
        $this->DATABASE = $database;
        $this->SCHEMA = $schema;
        $this->CURL_TIMEOUT = ($curlOptions["timeout"]) ? (int) $curlOptions["timeout"] : 300;
        $this->CURL_BATCH_SIZE = ($curlOptions["batchSize"]) ? (int) $curlOptions["batchSize"] : 5;
        $this->STATEMENT = $query;
        $this->CACHE_ENABLED = $cache;
        $this->CACHE_TTL = 60*60*2;//120 minutes

        $CACHE_KEY = md5($query);
        if(count($parameters) > 0){
            $parameterValues = [];
            foreach ($parameters as $index => $parameterData){
                $parameterValues[] = $parameterData["value"];
            }
            sort($parameterValues);
            $CACHE_KEY .= md5(join('', $parameterValues));
        }
        $this->STATEMENT_CACHE_KEY = $CACHE_KEY;
        $this->PARAMETERS = $parameters;

        $cachedStatementResult = apcu_fetch($this->STATEMENT_CACHE_KEY);
        if($cache && $cachedStatementResult){
            $this->STATEMENT_RESULT = $cachedStatementResult;
        }
        else{
            $this->setStatementResults($parameters, 0);
            $this->STATEMENT_RESULT = $this->getStatementResult(0);
            apcu_store($this->STATEMENT_CACHE_KEY, $this->STATEMENT_RESULT, $this->CACHE_TTL);
        }

        $this->SNOWFLAKE_QUERY_RESULT = new SnowflakeResult($this, $this->STATEMENT_RESULT);
    }

    public function getAllData(){
        return $this->SNOWFLAKE_QUERY_RESULT->getAllPartitionData();
    }

    public function getCurrentPartitionData()
    {
        return $this->SNOWFLAKE_QUERY_RESULT->getPartitionData();
    }
    
    public function nextPartition()
    {
        return $this->SNOWFLAKE_QUERY_RESULT->nextPartition();
    }

    public function getCurrentPartitionNumber()
    {
        return $this->SNOWFLAKE_QUERY_RESULT->currentPartition;
    }

    public function getPartitionInfo()
    {
        return $this->SNOWFLAKE_QUERY_RESULT->partitionInfo;
    }


}

