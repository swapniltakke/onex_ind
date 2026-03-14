<?php
error_reporting(E_ERROR | E_PARSE);
require_once $_SERVER["DOCUMENT_ROOT"] . '/shared/BaseManager.php';
require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/shared.php";
class CronDbManager extends BaseManager
{
    private static mixed $conn = null;
    private static ?array $DBCredentials = null;
    private static string $query = "";
    private static array $parameters = [];
    private static array $connectionAttributes = [
        PDO::ATTR_CASE => PDO::CASE_NATURAL,
        PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, //turn on errors in the form of exceptions
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
        PDO::ATTR_EMULATE_PREPARES => false, // turn off emulation mode for "real" prepared statements
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, //make the default fetch be an associative array
    ];

    /**
     * @param string $dbname
     * @param string $query
     * @param array $parameters
     * @param array $options
     * @return array
     */
    public static function fetchQuery(string $dbname, string $query, array $parameters = [], array $options = [], bool $manualCatch = false, bool $setUniqueParameters = true): array
    {
        try {
            self::$query = $query;
            self::$parameters = $parameters;

            if($setUniqueParameters) self::setParameters();
            else self::$parameters = array_merge(...$parameters);

            self::setPDOConnectionDb($dbname, $options);

            $stmt = self::$conn->prepare(self::$query);
            $status = $stmt->execute(self::$parameters);
            try {
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $data = [];
                foreach ($result as $key => $value) {
                    foreach ($value as $key1 => $value1) {
                        $data[$key][$key1] = self::Filter($value1);
                    }
                }
                $result = $data;
            } catch (Exception $e) {
                $result = self::$conn->lastInsertId();
            }
            return [
                'result' => $result,
                'stmt' => $stmt,
                'status' => $status,
                'pdoConnection' => self::$conn
            ];
        } catch (Exception $e) {
            error_reporting(E_ERROR | E_PARSE);
            if($manualCatch){
                // don`t forget to do 'throw new Error' after catching error
                return [
                    'result' => null,
                    'status' => false,
                    'exception' => $e,
                    'db' => [
                        'query' => self::$query,
                        'param' => self::$parameters,
                    ]
                ];
            }else{
                throw new Error("DbManager fetchQuery Error {$e->getMessage()}");
            }
        }
    }


    /**
     * @param string $dbname
     * @param string $Query
     * @param array $parameters
     * @param array $options
     * @return array
     */
    public static function fetchInsert(string $dbname, string $Query, array $parameters = [], array $options = []): array
    {
        try {
            self::$parameters = $parameters;
            self::setPDOConnectionDb($dbname);
            $i = 0;
            $rawQuery = [];
            $count = count(self::$parameters);
            while ($i < $count) {
                $keys = [];
                $j = 0;
                foreach (self::$parameters[$i] as $value) {
                    $hex = ':' . bin2hex(random_bytes(10)) . $i . $j;
                    $keys[] = $hex;
                    self::$parameters[$hex] = $value;
                    $j++;
                }
                if (!empty($keys)) $rawQuery[] = '(' . implode(',', $keys) . ')';
                unset(self::$parameters[$i]);
                $i++;
            }
            $Query .= ' VALUES ' . implode(',', $rawQuery);
            $stmt = self::$conn->prepare($Query);
            $status = $stmt->execute(self::$parameters);
            return [
                'stmt' => $stmt,
                'status' => $status,
                'pdoConnection' => self::$conn
            ];
        } catch (Exception $e) {
            error_reporting(E_ERROR | E_PARSE);
            throw new Error("DbManager fetchQuery Error {$e->getMessage()}");
        }
    }


    /**
     * @param string $dbname
     * @param string $query
     * @param array $parameters
     * @param array $options
     * @return array
     */
    public static function fetchQueryData(string $dbname, string $query, array $parameters = [], array $options = []): array
    {
        try {
            return [
                'data' => self::fetchQuery($dbname, $query, $parameters, $options)['result'] ?? []
            ];
        } catch (Exception $e) {
            error_reporting(E_ERROR | E_PARSE);
            throw new Error("DbManager fetchQueryData Error {$e->getMessage()}");
        }
    }

    public static function getPDOConnectionDb($dbname, array $extraPdoOptions = [])
    {
        self::setPDOConnectionDb($dbname, $extraPdoOptions);
        return self::$conn;
    }

    private static function setPDOConnectionDb($dbname, $extraPdoOptions = []): void
    {
        try {
            self::$DBCredentials = self::getCredentials($dbname);
            $DB = self::$DBCredentials['DB'];
            $IP = self::$DBCredentials['IP'];
            $user = self::$DBCredentials['UID'];
            $password = self::$DBCredentials['pwd'];
            $port = self::$DBCredentials['port'];

            if (strtolower($DB) == 'mssql') {
                $pdoConnection = new PDO("sqlsrv:Server={$IP};Database={$dbname}", $user, $password);
            } else {
                $pdoConnection = new PDO("mysql:host={$IP};dbname={$dbname};port=$port", $user, $password, self::$connectionAttributes + $extraPdoOptions);
                $pdoConnection->exec('set names utf8');
            }
            self::$conn = $pdoConnection;
        } catch (Exception $e) {
            error_reporting(E_ERROR | E_PARSE);
            throw new Error("DbManager setPDOConnectionDb Error {$e->getMessage()}");
        }
    }

    /**
     * @return array
     */
    public static function runTransaction($dbName, array $queryArray)
    {
        self::setPDOConnectionDb($dbName);
        self::$conn->beginTransaction();
        $returnArray = [];
        try{
            foreach ($queryArray as $queryData){
                $query = $queryData["query"];
                if(!$query) throw new Error("'query' keyword and its value must be present");

                $parameters = $queryData["parameters"] ?? [];

                 self::$query = $query;
                 self::$parameters = $parameters;
                self::setParameters();
                $stmt = self::$conn->prepare(self::$query);
                $status = $stmt->execute(self::$parameters);
                $returnArray[] = [
                    "status" => $status,
                    "stmt" => $stmt
                ];
            }
            self::$conn->commit();
            return $returnArray;
        } catch (Exception $e){
            self::$conn->rollback();
            throw new Error("Error on running transaction: " . $e->getMessage());
        }
    }

    private static function setParameters(): void
    {
        try {
            self::buildParameterArray();
            foreach (self::$parameters as $key => $parameter) {
                if (is_array($parameter)) {
                    $keys = [];
                    foreach ($parameter as $i => $iValue) {
                        self::$parameters[$key . $i] = $iValue;
                        $keys[] = $key . $i;
                    }
                    self::$query = str_replace($key, implode(',', $keys), self::$query);
                    unset(self::$parameters[$key]);
                }
            }

        } catch (Exception $e) {
            error_reporting(E_ERROR | E_PARSE);
            throw new Error("DbManager setParameters Error {$e->getMessage()}");
        }
    }

    private static function changeParameterArrayAndQuery(string $str): void
    {
        foreach (self::$parameters as $key => $parameter) {
            if ($key !== $str && str_contains($key, $str)) {
                $hex = ':' . bin2hex(random_bytes(10));
                self::$parameters[$hex] = $parameter;
                self::$query = preg_replace("/$key/", $hex, self::$query, 1);
                unset(self::$parameters[$key]);
            }
        }
    }

    private static function buildParameterArray(): void
    {
        $duplicate_keys = [];
        foreach (self::$parameters as $key => $parameter) {
            $count = substr_count(self::$query, $key);
            if ($count > 1) {
                $duplicate_keys[$key] = $parameter;
                self::changeParameterArrayAndQuery($key);
            }
        }
        foreach ($duplicate_keys as $key => $parameter) {
            $hex = ':' . bin2hex(random_bytes(10));
            self::$parameters[$hex] = $parameter;
            self::$query = preg_replace("/$key/", $hex, self::$query);
            unset(self::$parameters[$key]);
        }

        foreach (self::$parameters as $key => $parameter) {
            $count = substr_count(self::$query, $key);
            if ($count) {
                for ($i = 0; $i < $count; $i++) {
                    $hex = ':' . bin2hex(random_bytes(10));
                    self::$parameters[$hex] = $parameter;
                    self::$query = preg_replace("/$key/", $hex, self::$query, 1);
                }
                unset(self::$parameters[$key]);
            }
        }
    }
}