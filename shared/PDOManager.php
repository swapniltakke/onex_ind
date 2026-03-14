<?php
require_once $_SERVER["DOCUMENT_ROOT"] . '/shared/BaseManager.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/shared/shared.php';

/**
 *
 */
class PDOManager extends BaseManager
{

    private mixed $pdoConnection = null;
    private mixed $credentials;
    private array $connectionAttributes;
    private array $parameters;
    private string $query;

    /**
     * @param string $dbname
     */
    public function __construct(string $dbname)
    {
        try {
            $this->credentials = self::getCredentials($dbname);
            $assign_db = $this->credentials['DB'];
            if ($assign_db == "spectra_db_local") {
                $this->credentials['dbName'] = $assign_db;
            } else {
                $this->credentials['dbName'] = $dbname;
            }
            $this->connectionAttributes = [
                PDO::ATTR_CASE => PDO::CASE_NATURAL,
                PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, //turn on errors in the form of exceptions
                PDO::ATTR_STRINGIFY_FETCHES => false,
                PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
                PDO::ATTR_EMULATE_PREPARES => false, // turn off emulation mode for "real" prepared statements
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, //make the default fetch be an associative array
            ];
            if (!$this->credentials) {
                error_reporting(E_ERROR | E_PARSE);
                throw new Error("DBCredentials could not for DB: $dbname");
            }
        } catch (Exception $e) {
            error_reporting(E_ERROR | E_PARSE);
            throw new Error("DbManager __construct Error {$e->getMessage()}");
        }
    }

    /**
     * @param string $Query
     * @param array $parameters
     * @param array $options
     * @return array
     */
    public function fetchQueryData(string $Query, array $parameters = [], array $options = []): array
    {
        try {
            return [
                'data' => $this->fetchQuery($Query, $parameters, $options)['result'] ?? []
            ];
        } catch (Exception $e) {
            error_reporting(E_ERROR | E_PARSE);
            throw new Error("DbManager fetchQueryData Error {$e->getMessage()}");
        }
    }

    /**
     * @param string $Query
     * @param array $parameters
     * @param array $options
     * @return array
     */
    public function fetchQuery(string $Query, array $parameters = [], array $options = []): array
    {
        try {
            $this->connectionAttributes += $options;
            $this->parameters = $parameters;
            $this->query = $Query;
            $this->setParameters();
            $this->setPDOConnectionDb();
            $stmt = $this->pdoConnection->prepare($this->query);
            $status = $stmt->execute($this->parameters);
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
                $result = $this->pdoConnection->lastInsertId();
            }
            return [
                'data' => $result,
                'result' => $result,
                'stmt' => $stmt,
                'status' => $status,
                'pdoConnection' => $this->pdoConnection
            ];
        } catch (Exception $e) {
            error_reporting(E_ERROR | E_PARSE);
            throw new Error("DbManager fetchQuery Error {$e->getMessage()}");
        }
    }

    /**
     * @return void
     */
    private function setParameters(): void
    {
        try {
            $this->buildParameterArray();
            foreach ($this->parameters as $key => $parameter) {
                if (is_array($parameter)) {
                    $keys = [];
                    foreach ($parameter as $i => $iValue) {
                        $this->parameters[$key . $i] = $iValue;
                        $keys[] = $key . $i;
                    }
                    $this->query = str_replace($key, implode(',', $keys), $this->query);
                    unset($this->parameters[$key]);
                }
            }

        } catch (Exception $e) {
            error_reporting(E_ERROR | E_PARSE);
            throw new Error("DbManager setParameters Error {$e->getMessage()}");
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    private function buildParameterArray(): void
    {
        $duplicate_keys = [];
        foreach ($this->parameters as $key => $parameter) {
            $count = substr_count($this->query, $key);
            if ($count > 1) {
                $duplicate_keys[$key] = $parameter;
                $this->changeParameterArrayAndQuery($key);
            }
        }
        foreach ($duplicate_keys as $key => $parameter) {
            $hex = ':' . bin2hex(random_bytes(10));
            $this->parameters[$hex] = $parameter;
            $this->query = preg_replace("/$key/", $hex, $this->query);
            unset($this->parameters[$key]);
        }

        foreach ($this->parameters as $key => $parameter) {
            $count = substr_count($this->query, $key);
            if ($count) {
                for ($i = 0; $i < $count; $i++) {
                    $hex = ':' . bin2hex(random_bytes(10));
                    $this->parameters[$hex] = $parameter;
                    $this->query = preg_replace("/$key/", $hex, $this->query, 1);
                }
                unset($this->parameters[$key]);
            }
        }

    }

    /**
     * @param string $str
     * @return void
     * @throws Exception
     */
    private function changeParameterArrayAndQuery(string $str): void
    {
        foreach ($this->parameters as $key => $parameter) {
            if ($key !== $str && str_contains($key, $str)) {
                $hex = ':' . bin2hex(random_bytes(10));
                $this->parameters[$hex] = $parameter;
                $this->query = preg_replace("/$key/", $hex, $this->query, 1);
                unset($this->parameters[$key]);
            }
        }
    }

    /**
     * @return void
     */
    private function setPDOConnectionDb(): void
    {
        try {
            if (strtolower($this->credentials['DB']) == 'mssql') {
                $this->pdoConnection = new PDO("sqlsrv:Server={$this->credentials['IP']};Database={$this->credentials['dbName']}", $this->credentials['UID'], $this->credentials['pwd']);
                $this->pdoConnection->setAttribute(PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE, true);
            } else {
                $this->pdoConnection = new PDO("mysql:host={$this->credentials['IP']};dbname={$this->credentials['dbName']};port={$this->credentials['port']}", $this->credentials['UID'], $this->credentials['pwd']);
                foreach ($this->connectionAttributes as $prop => $value) {
                    $this->pdoConnection->setAttribute($prop, $value);
                }
            }

        } catch (Exception $e) {
            error_reporting(E_ERROR | E_PARSE);
            throw new Error("DbManager setPDOConnectionDb Error {$e->getMessage()}");
        }
    }


    /**
     * @param string $Query
     * @param array $parameters
     * @param array $options
     * @return array
     */
    public function fetchInsert(string $Query, array $parameters = [], array $options = []): array
    {
        try {
            $this->parameters = $parameters;
            $this->setPDOConnectionDb();
            $i = 0;
            $rawQuery = [];
            $count = count($this->parameters);
            while ($i < $count) {
                $keys = [];
                $j = 0;
                foreach ($this->parameters[$i] as $value) {
                    $hex = ':' . bin2hex(random_bytes(10)) . $i . $j;
                    $keys[] = $hex;
                    $this->parameters[$hex] = $value;
                    $j++;
                }
                if (!empty($keys)) $rawQuery[] = '(' . implode(',', $keys) . ')';
                unset($this->parameters[$i]);
                $i++;
            }
            $Query .= ' VALUES ' . implode(',', $rawQuery);
            $stmt = $this->pdoConnection->prepare($Query);
            $status = $stmt->execute($this->parameters);
            return [
                'stmt' => $stmt,
                'status' => $status,
                'pdoConnection' => $this->pdoConnection
            ];
        } catch (Exception $e) {
            error_reporting(E_ERROR | E_PARSE);
            throw new Error("DbManager fetchQuery Error {$e->getMessage()}");
        }
    }

}