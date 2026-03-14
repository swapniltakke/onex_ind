<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/snowflake/Snowflake.php";

class SnowflakeResult extends Snowflake{
    public int $numRows = 0;
    public array $columnData = [];
    public array $columnNames = [];
    public int $currentPartition = 0;
    public array $partitionInfo = [];
    public string $statementHandle = "";
    private array $partitionData = [];
    private int $totalUncompressedPartitionSize = 0;
    private bool $isLocalhost = false;

    public function __construct(SnowflakeQuery $snowflakeQueryInstance, $statementResult){
        //set all properties of SnowflakeQuery instance equal to this instance
        foreach (get_object_vars($snowflakeQueryInstance) as $property => $value) {
            $this->$property = $value;
        }

        $this->isLocalhost = in_array(getUserIP(), ["127.0.0.1", "::1"]);

        $this->numRows = $statementResult["resultSetMetaData"]["numRows"];
        $this->partitionInfo = $statementResult["resultSetMetaData"]["partitionInfo"];
        $this->columnData = $statementResult["resultSetMetaData"]["rowType"];
        $this->statementHandle = $statementResult["statementHandle"];
        $this->partitionData = $statementResult["data"];

        foreach($this->columnData as $columnDatum){
            $this->columnNames[] = $columnDatum["name"];
        }
        foreach ($this->partitionInfo as $partitionData){
            $this->totalUncompressedPartitionSize += $partitionData["uncompressedSize"];
        }

    }

    public function getPartitionData(){
        $data = [];
        foreach ($this->partitionData as $row){
            $tempRow = [];
            foreach ($row as $valueIndex => $value){
                $columnName = $this->columnNames[$valueIndex];
                $tempRow[$columnName] = $value;
            }
            $data[] = $tempRow;
        }
        return $data;
    }

    public function nextPartition(){
        $totalPartitionCount = count($this->partitionInfo)-1;
        if($totalPartitionCount === $this->currentPartition)
            return false;

        $this->currentPartition++;

        $cacheKey = "$this->STATEMENT_CACHE_KEY|$this->currentPartition";
        if($this->CACHE_ENABLED){
            //if cached in apcu, return
            $cachedData = apcu_fetch($cacheKey);
            if($cachedData){
                $this->partitionData = $cachedData;
                return $this;
            }

            //if cached as file, return
            if(file_exists($cacheKey)){
                $this->partitionData = json_decode(file_get_contents($cacheKey), true);
                return $this;
            }
        }

        $statementResult = $this->getStatementResult($this->currentPartition)["data"];
        //if is stored in STATEMENT_RESULTS
        if($statementResult){
            $this->partitionData = $statementResult;
            return $this;
        }

        $this->setStatementResults($this->PARAMETERS, $totalPartitionCount, $this->currentPartition, $this->statementHandle);
        $this->partitionData = $this->getStatementResult($this->currentPartition)["data"];

        if($this->CACHE_ENABLED){
            $oneMegabyte = 1048576;
            //if total data size is less then 128MB, store in cache
            if($this->totalUncompressedPartitionSize/$oneMegabyte < 128)
                apcu_store($cacheKey, $this->partitionData, $this->CACHE_TTL);
            //else, store as file
            else if($this->isLocalhost)
                file_put_contents($cacheKey, json_encode($this->partitionData));
        }

        return $this;
    }

    public function getAllPartitionData(){
        $allData = [...$this->getPartitionData()];
        $totalPartitionCount = count($this->partitionInfo)-1;
        for(;$this->currentPartition < $totalPartitionCount;){
            $this->nextPartition();
            $allData = [...$allData, ...$this->getPartitionData()];
        }
        return $allData;
    }
}
