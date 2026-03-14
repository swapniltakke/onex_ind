<?php
require_once $_SERVER["DOCUMENT_ROOT"] . '/shared/BaseManager.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/shared/shared.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/shared/PDOManager.php';

/**
 *
 */
class DbManager extends BaseManager
{
    /**
     * @param string $dbname
     * @param string $Query
     * @param array $parameters
     * @param array $options
     * @return array
     */
    public static function fetchPDOQuery(string $dbname, string $Query, array $parameters = [], array $options = []): array
    {
        return (new PDOManager($dbname))->fetchQuery($Query, $parameters, $options);
    }

    /**
     * @param string $dbname
     * @param string $Query
     * @param array $parameters
     * @param array $options
     * @return array
     */
    public static function fetchPDOQueryData(string $dbname, string $Query, array $parameters = [], array $options = []): array
    {
        return (new PDOManager($dbname))->fetchQueryData($Query, $parameters, $options);
    }
}