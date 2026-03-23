<?php

//Pages
const PAGE_GENERAL = 1;
const PAGE_TKFORM = 2;
const PAGE_PROJECTS = 3;
const PAGE_NC = 4;
const PAGE_MATERIAL_SEARCH = 5;
const PAGE_BANFOMAT = 6;
const PAGE_ADMIN = 7;
const PAGE_CHECKLIST = 8;
const PAGE_ORDERS_PLAN = 9;
const PAGE_DTO_ASSEMBLY_HOURS = 10;
const PAGE_DTO_CABLE_CODES = 11;

//Actions
const ACTION_CREATED = 1;
const ACTION_MODIFIED = 2;
const ACTION_DELETED = 3;
const ACTION_VIEWED = 4;
const ACTION_WARNING = 5;
const ACTION_ERROR = 6;
const ACTION_PROCESSING = 7;


//Subpages
const TKFORM_MAIN = 1;
const TKFORM_CREATE_FORM = 2;
const TKFORM_MODAL_INFO = 3;
const TKFORM_MODAL_MATERIAL_LIST = 4;
const TKFORM_MODAL_ADD_MATERIAL_FORM = 5;
const TKFORM_NC_LIST = 6;
const TKFORM_TK_ORDERS = 7;
const TKFORM_TK_NOTES = 8;
const GENERAL_HOME_PAGE = 9;
const GENERAL_MATERIAL_DEFINE_MODAL = 10;
const GENERAL_MATERIAL_SEARCH_MODAL = 11;
const DESIGN_LAST_WORKED_PROJECTS_LIST = 12;
const DESIGN_PROJECT_INFO = 13;
const DESIGN_DETAIL_NACHBAU_FILTER = 14;
const DESIGN_DETAIL_TYPE_LIST_FILTER = 15;
const DESIGN_DETAIL_TYPICAL_NUMBER_FILTER = 16;
const DESIGN_DETAIL_DTO_NUMBER_FILTER = 17;
const DESIGN_DETAIL_KUKO_MATRIX = 18;
const DESIGN_DETAIL_PROJECT_WORK = 19;
const DESIGN_DETAIL_JT_COLLECTION = 20;
const DESIGN_DETAIL_NACHBAU_OPERATIONS = 21;
const DESIGN_DETAIL_ORDER_SUMMARY = 22;
const DESIGN_DETAIL_BOM_NOTES = 23;
const DESIGN_DETAIL_NC_MAIN = 24;
const DESIGN_DETAIL_EXTENSION_DTO = 25;
const MATERIAL_SEARCH = 26;
const BANFOMAT_INDEX = 27;
const BANFOMAT_POOL = 28;
const BANFOMAT_HISTORY = 29;
const PROJECT_BANFOMAT_INFO = 30;
const TKFORM_JT_COLLECTION = 31;
const ADMIN_INDEX = 32;
const ADMIN_ORDER_CHANGES = 33;
const CHECKLIST_INDEX = 34;
const CHECKLIST_ADD_CHECKLIST_ITEM = 35;
const CHECKLIST_EDIT_CHECKLIST_ITEM = 36;
const ORDERS_PLAN_INDEX = 37;
const DTO_ASSEMBLY_HOURS_PANEL_BY = 38;
const DTO_ASSEMBLY_HOURS_STATION_BY = 39;
const DTO_CABLE_CODES_INDEX = 40;
const DTO_CABLE_CODES_ADD_CABLE_ITEM = 41;


class Journals
{
    public static $whom = null;
    public static $what = null;
    public static $page = "";
    public static $subpage = "";
    public static $action = "";
    public static $name = "";
    public static $surname = "";
    public static $gid = "";
    public static $user_os = "";
    public static $user_browser = "";
    public static $user_ip = "";
    public static $registryNo = "";
    public static $modulesStr = "";
    public static $functionsStr = "";
    public static $userGroupId = 0;
    public static $userOrgCode = "";

    public static function init()
    {
        if (session_status() === PHP_SESSION_NONE)
            session_start();

        $jwt = $_COOKIE["accessToken"];
        self::$user_os = SharedManager::getOSForUser();
        self::$user_browser = SharedManager::getBrowserForUser();
        self::$user_ip = SharedManager::getUserIP();
        if ($jwt) {
            $payload = SharedManager::getAccessTokenPayload();
            self::$whom = $payload->mail;
            self::$gid = $payload->gid;
            self::$name = $payload->name;
            self::$surname = $payload->surname;
            self::$registryNo = $payload->registry_no;
            self::setUserPermissions();
        }
    }

    public static function setUserPermissions($newAccessToken = null)
    {
        $payload = SharedManager::getAccessTokenPayload($newAccessToken);
        self::$modulesStr = $payload->modulesStr;
        self::$functionsStr = $payload->functionsStr;
        self::$userGroupId = $payload->group_id;
        self::$userOrgCode = $payload->org_code;
    }

    public static function saveJournal($what, $page, $subpage, $action, $parameters, $data_model): void
    {
        self::init();
        $whom = self::$whom;
        $page_url = self::getPageURLPath();
        $name = self::$name;
        $surname = self::$surname;
        $gid = self::$gid;
        $user_os = self::$user_os;
        $user_browser = self::$user_browser;
        $user_ip = self::$user_ip;

        $query_journal = "INSERT INTO journals (whom, what, page, subpage, page_url, action, parameters, data_model, name, surname, gid, operating_system, browser, ip) 
                                 VALUES (:p1, :p2, :p3, :p4, :p5, :p6, :p7, :p8, :p9, :p10, :p11, :p12, :p13, :p14)";
        $pdo_params = [
            ":p1" => $whom,
            ":p2" => $what,
            ":p3" => $page,
            ":p4" => $subpage,
            ":p5" => $page_url,
            ":p6" => $action,
            ":p7" => $parameters,
            ":p8" => $data_model,
            ":p9" => $name,
            ":p10" => $surname,
            ":p11" => $gid,
            ":p12" => $user_os,
            ":p13" => $user_browser,
            ":p14" => $user_ip
        ];
        DbManager::fetchPDOQuery('dto_configurator', $query_journal, $pdo_params);

//        //Local için yazıldı
//        if (in_array(self::$user_ip,['127.0.0.1', '::1'])) {
//            DbManager::fetchPDOQuery('dto_configurator', $query_journal, $pdo_params);
//        }

    }

    public static function getPageURLPath(): string
    {
        // Get the current URI
        $uri = $_SERVER['REQUEST_URI'];

        // Parse the URL to extract the path and query string
        $urlComponents = parse_url($uri);

        // Extract the path and query string and return it
        return $urlComponents['path'] . (isset($urlComponents['query']) ? '?' . $urlComponents['query'] : '');
    }
}


