<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/shared.php";
header('Content-Type:application/json;charset=utf-8;');

$controller = new BreakerController();
switch ($_GET["action"]) {
    case "allList":
        $controller->listBreakers(0);
        break;
    case "plannedMonthAllList":
        $controller->plannedMonthlistBreakers(0);
        break;
    case "getBreakerData":
        $controller->getBreakerData();
        break;
    case "delete":
        $breaker_id = $_GET['breakerId'] ?? 0;
        if ($breaker_id < 1 || empty($breaker_id)) {
            http_response_code(500);
            exit();
        }
        $breaker_id_response = $controller->deleteBreaker($breaker_id);
        echo json_encode($breaker_id_response, JSON_PRETTY_PRINT);
        http_response_code(200);
    default:
        break;
}

switch ($_POST["action"]) {
    case "registration":
        $controller->registration();
        break;
    case "edit":
        $controller->update();
        break;
    case "getGroupNameSuggestions":
        $controller->getGroupNameSuggestions();
        break;
    case "getSalesOrderSuggestions":
        $controller->getSalesOrderSuggestions();
        break;
    case "getItemNoSuggestions":
        $controller->getItemNoSuggestions();
        break;
    case "getClientNameSuggestions":
        $controller->getClientNameSuggestions();
        break;
    case "getMlfbNoSuggestions":
        $controller->getMlfbNoSuggestions();
        break;
    case "getMlfbDetails":
        $controller->getMlfbDetails();
        break;
    case "getSerialNo":
        $controller->getSerialNo();
        break;
    case "getMlfbDetailsById":
        $controller->getMlfbDetailsById();
        break;
    case "getRatingSuggestions":
        $controller->getRatingSuggestions();
        break;   
    case "getProductNameSuggestions":
        $controller->getProductNameSuggestions();
        break;
    case "getWidthSuggestions":
        $controller->getWidthSuggestions();
        break;
    case "getTrolleyTypeSuggestions":
        $controller->getTrolleyTypeSuggestions();
        break;
    case "getTrolleyRefairSuggestions":
        $controller->getTrolleyRefairSuggestions();
        break;    
    case "getAddonSuggestions":
        $controller->getAddonSuggestions();
        break;
    default:
        break;
}
exit;

class BreakerController{
    private $currentUserGroupID;

    public function __construct(){
        $userGroupID = SharedManager::getUser()["GroupID"];
        if(is_int($userGroupID) || (is_string($userGroupID) && ctype_digit($userGroupID))) {    

        } else {
            returnHttpResponse(403, "Not Authorized");
        }
        $this->currentUserGroupID = $userGroupID;
    }

    public function registration()
    {
        $uniqueValue = $this->generateUniqueValue();
        $group_name = $_POST["group_name"];
        $cdd_date = $_POST["cdd_date"];
        $formatted_cdd_date = date('Y-m-d', strtotime($cdd_date));
        $plan_month_date = $_POST["plan_month_date"];
        $formatted_plan_month_date = date('Y-m-d', strtotime($plan_month_date));
        $sales_order_no = $_POST["sales_order_no"];
        $item_no = $_POST["item_no"];
        $item_no = str_pad($item_no, 6, "0", STR_PAD_LEFT);
        $client = $_POST["client"];
        $mlfb_no = $_POST["mlfb_no"];
        $rating = $_POST["rating"];
        $product_name = $_POST["product_name"];
        $width = $_POST["width"];
        $trolley_type = $_POST["trolley_type"];
        $trolley_refair = $_POST["trolley_refair"];
        $total_quantity = $_POST["total_quantity"];
        $production_order_quantity = $_POST["production_order_quantity"];
        $addon = $_POST["addon"];

        $serial_no = $_POST["serial_no"];
        $serial_no_int = intval($serial_no);

        $ptd_no = isset($_POST["ptd_no"]) && !empty($_POST["ptd_no"]) ? $_POST["ptd_no"] : "";
        $production_order_no = isset($_POST["production_order_no"]) && !empty($_POST["production_order_no"]) ? $_POST["production_order_no"] : "";
        $vi_type = $_POST["vi_type"];
        $formatted_c1_date = NULL;
        if (isset($_POST["c1_date"]) && !empty($_POST["c1_date"])) {
            $c1_date = $_POST["c1_date"];
            $formatted_c1_date = date('Y-m-d', strtotime($c1_date));
        }
        $remark = isset($_POST["remark"]) && !empty($_POST["remark"]) ? $_POST["remark"] : "";
        
        $insertbreakerQuery = "
            INSERT INTO tbl_breaker_details
            (unique_id, group_name, cdd_date, plan_month_date, sales_order_no, item_no, client, mlfb_no, rating, product_name, width, trolley_type, trolley_refair, total_quantity, production_order_quantity, addon, serial_no, ptd_no, production_order_no, vi_type, c1_date, remark, status)
            VALUES(:unique_id, :group_name, :formatted_cdd_date, :formatted_plan_month_date, :sales_order_no, :item_no, :client, :mlfb_no, :rating, :product_name, :width, :trolley_type, :trolley_refair, :total_quantity, :production_order_quantity, :addon, :serial_no, :ptd_no, :production_order_no, :vi_type, :formatted_c1_date, :remark, :status)
        ";

        for ($i = 0; $i < $production_order_quantity; $i++) {
            $new_serial_no = str_pad($serial_no_int, 8, "0", STR_PAD_LEFT);
            DbManager::fetchPDOQuery('spectra_db', $insertbreakerQuery, [
                ':unique_id' => $uniqueValue,
                ':group_name' => $group_name,
                ':formatted_cdd_date' => $formatted_cdd_date,
                ':formatted_plan_month_date' => $formatted_plan_month_date,
                ':sales_order_no' => $sales_order_no,
                ':item_no' => $item_no,
                ':client' => $client,
                ':mlfb_no' => $mlfb_no,
                ':rating' => $rating,
                ':product_name' => $product_name,
                ':width' => $width,
                ':trolley_type' => $trolley_type,
                ':trolley_refair' => $trolley_refair,
                ':total_quantity' => $total_quantity,
                ':production_order_quantity' => '1',
                ':addon' => $addon,
                ':serial_no' => $new_serial_no,
                ':ptd_no' => $ptd_no,
                ':production_order_no' => $production_order_no,
                ':vi_type' => $vi_type,
                ':formatted_c1_date' => $formatted_c1_date,
                ':remark' => $remark,
                ':status' => 'A'
            ]);
            $serial_no_int++;
        }

        $mlfb_query = "SELECT material_description FROM mlfb_details WHERE material_description=:material_description";
        $mlfb_response = DbManager::fetchPDOQueryData('spectra_db', $mlfb_query, [":material_description" => $mlfb_no])["data"];
        if (empty($mlfb_response[0]['material_description'])) {
            $insertmlfbQuery = "
            INSERT INTO mlfb_details
            (material_description, mlfb_no, product_name, rating, width, vi_type, series_no, ptd_no)
            VALUES(:material_description, :mlfb_no, :product_name, :rating, :width, :vi_type, :series_no, :ptd_no)";
            $new_mlfb_no = substr($mlfb_no, 0, 8);
            DbManager::fetchPDOQuery('spectra_db', $insertmlfbQuery, [
                ':material_description' => $mlfb_no,
                ':mlfb_no' => $new_mlfb_no,
                ':product_name' => $product_name,
                ':rating' => $rating,
                ':width' => $width,
                ':vi_type' => $vi_type,
                ':series_no' => $new_serial_no,
                ':ptd_no' => $ptd_no
            ]);
        }
        $this->insertGroupName($group_name);
        $this->insertRating($rating);
        $this->insertWidth($width);
        $this->insertTrolleyType($trolley_type);
        $this->insertAddon($addon);
        returnHttpResponse(200, "Registration Done Successfully");
    }

    public function generateUniqueValue() {
        $timestamp = microtime(true);
        $uniqueValue = bin2hex(random_bytes(10)) . substr(str_replace('.', '', (string)$timestamp), 0, 10);
        return substr($uniqueValue, 0, 20);
    }

    public function insertGroupName($group_name = "") {
        $select_query = "SELECT group_name FROM breaker_group_name WHERE group_name=:group_name";
        $select_response = DbManager::fetchPDOQueryData('spectra_db', $select_query, [":group_name" => $group_name])["data"];
        if (empty($select_response)) {
            $insertQuery = "INSERT INTO breaker_group_name (group_name) VALUES(:group_name)";
            DbManager::fetchPDOQuery('spectra_db', $insertQuery, [':group_name' => $group_name]);
        }
    }

    public function insertRating($rating = "") {
        $select_query = "SELECT rating FROM breaker_rating WHERE rating=:rating";
        $select_response = DbManager::fetchPDOQueryData('spectra_db', $select_query, [":rating" => $rating])["data"];
        if (empty($select_response)) {
            $insertQuery = "INSERT INTO breaker_rating (rating) VALUES(:rating)";
            DbManager::fetchPDOQuery('spectra_db', $insertQuery, [':rating' => $rating]);
        }
    }

    public function insertWidth($width = "") {
        $select_query = "SELECT width FROM breaker_width WHERE width=:width";
        $select_response = DbManager::fetchPDOQueryData('spectra_db', $select_query, [":width" => $width])["data"];
        if (empty($select_response)) {
            $insertQuery = "INSERT INTO breaker_width (width) VALUES(:width)";
            DbManager::fetchPDOQuery('spectra_db', $insertQuery, [':width' => $width]);
        }
    }

    public function insertTrolleyType($trolley_type = "") {
        $select_query = "SELECT trolley_type FROM breaker_trolley_type WHERE trolley_type=:trolley_type";
        $select_response = DbManager::fetchPDOQueryData('spectra_db', $select_query, [":trolley_type" => $trolley_type])["data"];
        if (empty($select_response)) {
            $insertQuery = "INSERT INTO breaker_trolley_type (trolley_type) VALUES(:trolley_type)";
            DbManager::fetchPDOQuery('spectra_db', $insertQuery, [':trolley_type' => $trolley_type]);
        }
    }

    public function insertAddon($addon = "") {
        $select_query = "SELECT addon FROM breaker_addon WHERE addon=:addon";
        $select_response = DbManager::fetchPDOQueryData('spectra_db', $select_query, [":addon" => $addon])["data"];
        if (empty($select_response)) {
            $insertQuery = "INSERT INTO breaker_addon (addon) VALUES(:addon)";
            DbManager::fetchPDOQuery('spectra_db', $insertQuery, [':addon' => $addon]);
        }
    }

    public function listBreakers($openCloseState)
    {
        global $replaceSubProductType;
        $start_date = $_GET['start_date'] ?? null;
        $finish_date = $_GET['finish_date'] ?? null;
        $firstOfMonth = date('Y-m-01 00:00:00');
        $firstOfMonth = date('Y-m-d H:i:s', strtotime($firstOfMonth . ' -1 second'));

        $date_query = "";
        if ($start_date != null && $finish_date != null) {
            $startdateObj = DateTime::createFromFormat('d-m-Y', $start_date);
            $formattedStartDate = $startdateObj->format('Y-m-d 00:00:00');
            $enddateObj = DateTime::createFromFormat('d-m-Y', $finish_date);
            $formattedEndDate = $enddateObj->format('Y-m-d 23:59:59');

            $date_query = " AND bd.status =:status AND bd.create_date BETWEEN :formattedStartDate AND :formattedEndDate ORDER BY bd.id DESC";
            $pdo_params = [":status" => 'A', ":formattedStartDate" => $formattedStartDate, ":formattedEndDate" => $formattedEndDate];
        } else {
            $date_query = " AND bd.status =:status AND bd.create_date >=:first_of_month ORDER BY bd.id DESC";
            $pdo_params = [":status" => 'A', ":first_of_month" => $firstOfMonth];
        }
        
        $query = "SELECT
                    bd.*,
                    DATE_FORMAT(bd.cdd_date, '%d-%m-%Y') AS cdd_date_formatted,
                    DATE_FORMAT(bd.plan_month_date, '%d-%m-%Y') AS plan_month_date_formatted,
                    DATE_FORMAT(bd.c1_date, '%d-%m-%Y') AS c1_date_formatted,
                    DATE_FORMAT(bd.cia_date, '%d-%m-%Y') AS cia_date_formatted,
                    CASE WHEN bd.id IN (SELECT parent_id FROM tbl_breaker_details) THEN 1 ELSE 0 END AS is_parent,
                    t.barcode,
                    CASE WHEN t.station_name = 'Stamping' AND t.status = '0' THEN 'Stamping Pending'
                        WHEN t.station_name = 'Stamping' AND t.status = '1' THEN 'Stamping Completed'
                        ELSE t.station_name
                    END AS station_name,
                    t.end_datetime,
                    t.user_id,
                    t.status AS transaction_status
                FROM tbl_breaker_details bd
                LEFT JOIN (
                    SELECT
                        barcode,
                        station_name,
                        user_id,
                        up_date,
                        end_datetime,
                        STATUS AS STATUS
                    FROM (
                        SELECT
                            barcode,
                            station_name,
                            user_id,
                            up_date,
                            ROW_NUMBER() OVER (PARTITION BY barcode ORDER BY up_date DESC) AS rn,
                            MAX(up_date) OVER (PARTITION BY barcode) AS end_datetime, STATUS
                        FROM tbl_transactions
                    ) t1
                    WHERE t1.rn = 1
                    ORDER BY up_date DESC
                ) t ON bd.serial_no = SUBSTRING(t.barcode, 1, 8)
                    AND bd.sales_order_no = SUBSTRING(t.barcode, -29, 10)
                    AND bd.item_no = SUBSTRING(t.barcode, -18, 6)
                WHERE 1 $date_query";

        $res = DbManager::fetchPDOQueryData('spectra_db', $query, $pdo_params)["data"];
        // SharedManager::print($res);
        $cnt = count($res);
        $product_info = array();
        if ($cnt > 0) {
            $sr = 1;
            $ProductionOrderQuantityCount = 0;
            foreach ($res as $data) {    
                $product_data['srNo'] = $sr;
                $sr++;
                $product_data['id'] = trim($data['id']);
                $product_data['parentId'] = trim($data['parent_id']);
                $product_data['isDisplay'] = trim($data['is_display']);
                $product_data['showCollapse'] = trim($data['show_collapse']);
                $product_data['uniqueId'] = trim($data['unique_id']);
                $product_data['groupName'] = $data['group_name'];
                $product_data['cddDate'] = $data['cdd_date_formatted'];
                $product_data['planMonthDate'] = $data['plan_month_date_formatted'];
                $product_data['salesOrderNo'] = trim($data['sales_order_no']);
                $product_data['itemNo'] = trim($data['item_no']);
                $product_data['client'] = $data['client'];
                $product_data['mlfbNo'] = $data['mlfb_no'];
                $product_data['rating'] = $data['rating'];
                $product_data['productName'] = $data['product_name'];
                $product_data['width'] = $data['width'];
                $product_data['trolleyType'] = $data['trolley_type'];
                $product_data['trolleyRefair'] = $data['trolley_refair'];
                $product_data['mlfbNoUniqueNumber'] = preg_replace('/[^a-zA-Z0-9 _-]/', '', $product_data['mlfbNo']);
                $product_data['totalQuantity'] = trim($data['total_quantity']);
                $product_data['productionOrderQuantity'] = trim($data['production_order_quantity']);
                $ProductionOrderQuantityCount = $ProductionOrderQuantityCount+$product_data['productionOrderQuantity'];
                $product_data['addon'] = trim($data['addon']);
                $product_data['serialNo'] = $data['serial_no'];
                $product_data['ptdNo'] = trim($data['ptd_no']);
                $product_data['productionOrderNo'] = trim($data['production_order_no']);
                $product_data['viType'] = $data['vi_type'];
                $product_data['c1Date'] = $data['c1_date_formatted'];
                $product_data['ciaDate'] = $data['cia_date_formatted'];
                $product_data['remark'] = $data['remark'];
                $product_data['createDate'] = $data['create_date'];
                $product_data['status'] = $data['status'];
                $product_data['currentStatus'] = $data['station_name'];
                $product_data['user'] = $data['user_id'];
                $product_info["data"][] = $product_data;
            }
        } else {
            $product_info["data"] = array();
        }
        $product_info["ProductionOrderQuantityCount"] = $ProductionOrderQuantityCount;
        echo json_encode($product_info, JSON_THROW_ON_ERROR);
        exit;
    }

    public function plannedMonthlistBreakers($openCloseState)
    {
        global $replaceSubProductType;
        $start_date = $_GET['start_date'] ?? null;
        $finish_date = $_GET['finish_date'] ?? null;
        $firstOfMonth = date('Y-m-01 00:00:00');
        $firstOfMonth = date('Y-m-d H:i:s', strtotime($firstOfMonth . ' -1 second'));

        $date_query = "";
        if ($start_date != null && $finish_date != null) {
            $startdateObj = DateTime::createFromFormat('d-m-Y', $start_date);
            $formattedStartDate = $startdateObj->format('Y-m-d 00:00:00');
            $enddateObj = DateTime::createFromFormat('d-m-Y', $finish_date);
            $formattedEndDate = $enddateObj->format('Y-m-d 23:59:59');

            $date_query = " AND bd.status =:status AND bd.plan_month_date BETWEEN :formattedStartDate AND :formattedEndDate ORDER BY bd.id DESC";
            $pdo_params = [":status" => 'A', ":tstatus" => '1', ":formattedStartDate" => $formattedStartDate, ":formattedEndDate" => $formattedEndDate];
        } else {
            $date_query = " AND bd.status =:status AND bd.plan_month_date >=:first_of_month ORDER BY bd.id DESC";
            $pdo_params = [":status" => 'A', ":tstatus" => '1', ":first_of_month" => $firstOfMonth];
        }
        
        $query = "SELECT
                    bd.*,
                    DATE_FORMAT(bd.cdd_date, '%d-%m-%Y') AS cdd_date_formatted,
                    DATE_FORMAT(bd.plan_month_date, '%d-%m-%Y') AS plan_month_date_formatted,
                    DATE_FORMAT(bd.c1_date, '%d-%m-%Y') AS c1_date_formatted,
                    DATE_FORMAT(bd.cia_date, '%d-%m-%Y') AS cia_date_formatted,
                    CASE WHEN bd.id IN (SELECT parent_id FROM tbl_breaker_details) THEN 1 ELSE 0 END AS is_parent,
                    t.barcode,
                    t.station_name,
                    t.end_datetime,
                    t.user_id
                FROM tbl_breaker_details bd
                LEFT JOIN (
                    SELECT
                        barcode,
                        station_name,
                        user_id,
                        up_date,
                        end_datetime
                    FROM (
                        SELECT
                            barcode,
                            station_name,
                            user_id,
                            up_date,
                            ROW_NUMBER() OVER (PARTITION BY barcode ORDER BY up_date DESC) AS rn,
                            MAX(up_date) OVER (PARTITION BY barcode) AS end_datetime
                        FROM tbl_transactions
                        WHERE STATUS =:tstatus
                    ) t1
                    WHERE t1.rn = 1
                    ORDER BY up_date DESC
                ) t ON bd.serial_no = SUBSTRING(t.barcode, 1, 8)
                    AND bd.sales_order_no = SUBSTRING(t.barcode, -29, 10)
                    AND bd.item_no = SUBSTRING(t.barcode, -18, 6)
                WHERE 1 $date_query";

        $res = DbManager::fetchPDOQueryData('spectra_db', $query, $pdo_params)["data"];
        // SharedManager::print($res);
        $cnt = count($res);
        $product_info = array();
        if ($cnt > 0) {
            $sr = 1;
            $ProductionOrderQuantityCount = 0;
            foreach ($res as $data) {    
                $product_data['srNo'] = $sr;
                $sr++;
                $product_data['id'] = trim($data['id']);
                $product_data['parentId'] = trim($data['parent_id']);
                $product_data['isDisplay'] = trim($data['is_display']);
                $product_data['showCollapse'] = trim($data['show_collapse']);
                $product_data['uniqueId'] = trim($data['unique_id']);
                $product_data['groupName'] = $data['group_name'];
                $product_data['cddDate'] = $data['cdd_date_formatted'];
                $product_data['planMonthDate'] = $data['plan_month_date_formatted'];
                $product_data['salesOrderNo'] = trim($data['sales_order_no']);
                $product_data['itemNo'] = trim($data['item_no']);
                $product_data['client'] = $data['client'];
                $product_data['mlfbNo'] = $data['mlfb_no'];
                $product_data['rating'] = $data['rating'];
                $product_data['productName'] = $data['product_name'];
                $product_data['width'] = $data['width'];
                $product_data['trolleyType'] = $data['trolley_type'];
                $product_data['trolleyRefair'] = $data['trolley_refair'];
                $product_data['mlfbNoUniqueNumber'] = preg_replace('/[^a-zA-Z0-9 _-]/', '', $product_data['mlfbNo']);
                $product_data['totalQuantity'] = trim($data['total_quantity']);
                $product_data['productionOrderQuantity'] = trim($data['production_order_quantity']);
                $ProductionOrderQuantityCount = $ProductionOrderQuantityCount+$product_data['productionOrderQuantity'];
                $product_data['addon'] = trim($data['addon']);
                $product_data['serialNo'] = $data['serial_no'];
                $product_data['ptdNo'] = trim($data['ptd_no']);
                $product_data['productionOrderNo'] = trim($data['production_order_no']);
                $product_data['viType'] = $data['vi_type'];
                $product_data['c1Date'] = $data['c1_date_formatted'];
                $product_data['ciaDate'] = $data['cia_date_formatted'];
                $product_data['remark'] = $data['remark'];
                $product_data['createDate'] = $data['create_date'];
                $product_data['status'] = $data['status'];
                $product_data['currentStatus'] = $data['station_name'];
                $product_data['user'] = $data['user_id'];
                $product_info["data"][] = $product_data;
            }
        } else {
            $product_info["data"] = array();
        }
        $product_info["ProductionOrderQuantityCount"] = $ProductionOrderQuantityCount;
        echo json_encode($product_info, JSON_THROW_ON_ERROR);
        exit;
    }

    public function getBreakerData($breaker_id = "", $return = "")
    {
        if (!empty($breaker_id)) {
            $id = $breaker_id;
        } else {
            $id = $_GET["id"];
        }
        if(!is_numeric($id))
            returnHttpResponse(400, "invalid id");

        $query = "SELECT *,DATE_FORMAT(cdd_date, '%d-%m-%Y') AS cdd_date_formatted,
                    DATE_FORMAT(plan_month_date, '%d-%m-%Y') AS plan_month_date_formatted,
                    DATE_FORMAT(c1_date, '%d-%m-%Y') AS c1_date_formatted,
                    DATE_FORMAT(cia_date, '%d-%m-%Y') AS cia_date_formatted
                  FROM tbl_breaker_details WHERE id = :p1";
        $response = DbManager::fetchPDOQuery('spectra_db', $query, [":p1" => $id])["data"];
        if (!empty($return)) {
            return $response[0];
        } else {
            echo json_encode($response[0]);
        }
        exit;
    }

    public function update()
    {
        $id = $_POST['id'];
        $uniqueValue = $this->generateUniqueValue();
        $group_name = $_POST["group_name"];
        $cdd_date = $_POST["cdd_date"];
        $formatted_cdd_date = date('Y-m-d', strtotime($cdd_date));
        $plan_month_date = $_POST["plan_month_date"];
        $formatted_plan_month_date = date('Y-m-d', strtotime($plan_month_date));
        $sales_order_no = $_POST["sales_order_no"];
        $item_no = $_POST["item_no"];
        $item_no = str_pad($item_no, 6, "0", STR_PAD_LEFT);
        $client = $_POST["client"];
        $mlfb_no = $_POST["mlfb_no"];
        $rating = $_POST["rating"];
        $product_name = $_POST["product_name"];
        $width = $_POST["width"];
        $trolley_type = $_POST["trolley_type"];
        $trolley_refair = $_POST["trolley_refair"];
        $total_quantity = $_POST["total_quantity"];
        $production_order_quantity = $_POST["production_order_quantity"];
        $addon = $_POST["addon"];
        
        $serial_no = $_POST["serial_no"];
        $serial_no_int = intval($serial_no);

        $ptd_no = isset($_POST["ptd_no"]) && !empty($_POST["ptd_no"]) ? $_POST["ptd_no"] : "";
        $production_order_no = isset($_POST["production_order_no"]) && !empty($_POST["production_order_no"]) ? $_POST["production_order_no"] : "";
        $vi_type = $_POST["vi_type"];
        $formatted_c1_date = NULL;
        if (isset($_POST["c1_date"]) && !empty($_POST["c1_date"])) {
            $c1_date = $_POST["c1_date"];
            $formatted_c1_date = date('Y-m-d', strtotime($c1_date));
        }
        $formatted_cia_date = NULL;
        if (isset($_POST["cia_date"]) && !empty($_POST["cia_date"])) {
            $cia_date = $_POST["cia_date"];
            $formatted_cia_date = date('Y-m-d', strtotime($cia_date));
        }
        $remark = isset($_POST["remark"]) && !empty($_POST["remark"]) ? $_POST["remark"] : "";
    
        $main_query = "SELECT * FROM tbl_breaker_details WHERE id=:id";
        $main_response = DbManager::fetchPDOQueryData('spectra_db', $main_query, [":id" => $id])["data"];

        if (empty($main_response[0]['cia_date']) && ($formatted_cia_date !== NULL)) {
            $query = "
                INSERT INTO tbl_breaker_details
                (unique_id, parent_id, group_name, cdd_date, plan_month_date, sales_order_no, item_no, client, mlfb_no, rating, product_name, width, trolley_type, trolley_refair, total_quantity, production_order_quantity, addon, serial_no, ptd_no, production_order_no, vi_type, create_date, c1_date, cia_date, remark, status, is_display, show_collapse)
                VALUES(:unique_id, :parent_id, :group_name, :formatted_cdd_date, :formatted_plan_month_date, :sales_order_no, :item_no, :client, :mlfb_no, :rating, :product_name, :width, :trolley_type, :trolley_refair, :total_quantity, :production_order_quantity, :addon, :serial_no, :ptd_no, :production_order_no, :vi_type, :create_date, :formatted_c1_date, :formatted_cia_date, :remark, :status, :is_display, :show_collapse)
            ";

            DbManager::fetchPDOQuery('spectra_db', $query, [
                ':parent_id' => $main_response[0]['id'],
                ':unique_id' => $main_response[0]['unique_id'],
                ':group_name' => $main_response[0]['group_name'],
                ':formatted_cdd_date' => $main_response[0]['cdd_date'],
                ':formatted_plan_month_date' => $main_response[0]['plan_month_date'],
                ':sales_order_no' => $main_response[0]['sales_order_no'],
                ':item_no' => $main_response[0]['item_no'],
                ':client' => $main_response[0]['client'],
                ':mlfb_no' => $main_response[0]['mlfb_no'],
                ':rating' => $main_response[0]['rating'],
                ':product_name' => $main_response[0]['product_name'],
                ':width' => $main_response[0]['width'],
                ':trolley_type' => $main_response[0]['trolley_type'],
                ':trolley_refair' => $main_response[0]['trolley_refair'],
                ':total_quantity' => $main_response[0]['total_quantity'],
                ':production_order_quantity' => $main_response[0]['production_order_quantity'],
                ':addon' => $main_response[0]['addon'],
                ':serial_no' => $main_response[0]['serial_no'],
                ':ptd_no' => $main_response[0]['ptd_no'],
                ':production_order_no' => $main_response[0]['production_order_no'],
                ':vi_type' => $main_response[0]['vi_type'],
                ':create_date' => $main_response[0]['create_date'],
                ':formatted_c1_date' => $main_response[0]['c1_date'],
                ':formatted_cia_date' => $main_response[0]['cia_date'],
                ':remark' => $main_response[0]['remark'],
                ':status' => 'I',
                ':is_display' => $main_response[0]['is_display'],
                ':show_collapse' => $main_response[0]['show_collapse']
            ]);

            $query_update = "
                UPDATE tbl_breaker_details SET
                group_name=:group_name, cdd_date=:formatted_cdd_date, plan_month_date=:formatted_plan_month_date, mlfb_no=:mlfb_no,
                rating=:rating, product_name=:product_name, width=:width, trolley_type=:trolley_type, trolley_refair=:trolley_refair,
                total_quantity=:total_quantity, addon=:addon, serial_no=:serial_no, ptd_no=:ptd_no, production_order_no=:production_order_no, 
                vi_type=:vi_type, c1_date=:formatted_c1_date, cia_date=:formatted_cia_date, remark=:remark
                WHERE id =:id
            ";
            $response_update_query = DbManager::fetchPDOQuery('spectra_db', $query_update, 
            [":group_name" => $group_name, ":formatted_cdd_date" => $formatted_cdd_date, ":formatted_plan_month_date" => $formatted_plan_month_date,
            ":mlfb_no" => $mlfb_no, ":rating" => $rating, ":product_name" => $product_name, ":width" => $width, 
            ":trolley_type" => $trolley_type, ":trolley_refair" => $trolley_refair, ":total_quantity" => $total_quantity, ":addon" => $addon, 
            ":serial_no" => $serial_no, ":ptd_no" => $ptd_no, ":production_order_no" => $production_order_no, ":vi_type" => $vi_type, 
            ":formatted_c1_date" => $formatted_c1_date, ":formatted_cia_date" => $formatted_cia_date, ":remark" => $remark, ":id" => $id]);
        } else {
            if (empty($main_response[0]['parent_id'])) {
                $query_update = "UPDATE tbl_breaker_details SET is_display=:is_display where id =:id";
                $response_update_query = DbManager::fetchPDOQuery('spectra_db', $query_update, [":is_display" => '0', ":id" => $id]);
                $parent_id = $id;
            } else {
                $query_update = "UPDATE tbl_breaker_details SET is_display=:is_display, show_collapse=:show_collapse where id =:id";
                $response_update_query = DbManager::fetchPDOQuery('spectra_db', $query_update, [":is_display" => '0', ":show_collapse" => '0', ":id" => $id]);
                $parent_id = $main_response[0]['parent_id'];
            }

            $query = "
                INSERT INTO tbl_breaker_details
                (unique_id, parent_id, group_name, cdd_date, plan_month_date, sales_order_no, item_no, client, mlfb_no, rating, product_name, width, trolley_type, trolley_refair, total_quantity, production_order_quantity, addon, serial_no, ptd_no, production_order_no, vi_type, c1_date, cia_date, remark, status, is_display, show_collapse)
                VALUES(:unique_id, :parent_id, :group_name, :formatted_cdd_date, :formatted_plan_month_date, :sales_order_no, :item_no, :client, :mlfb_no, :rating, :product_name, :width, :trolley_type, :trolley_refair, :total_quantity, :production_order_quantity, :addon, :serial_no, :ptd_no, :production_order_no, :vi_type, :formatted_c1_date, :formatted_cia_date, :remark, :status, :is_display, :show_collapse)
            ";

            DbManager::fetchPDOQuery('spectra_db', $query, [
                ':unique_id' => $uniqueValue,
                ':parent_id' => $parent_id,
                ':group_name' => $group_name,
                ':formatted_cdd_date' => $formatted_cdd_date,
                ':formatted_plan_month_date' => $formatted_plan_month_date,
                ':sales_order_no' => $sales_order_no,
                ':item_no' => $item_no,
                ':client' => $client,
                ':mlfb_no' => $mlfb_no,
                ':rating' => $rating,
                ':product_name' => $product_name,
                ':width' => $width,
                ':trolley_type' => $trolley_type,
                ':trolley_refair' => $trolley_refair,
                ':total_quantity' => $total_quantity,
                ':production_order_quantity' => '1',
                ':addon' => $addon,
                ':serial_no' => $serial_no,
                ':ptd_no' => $ptd_no,
                ':production_order_no' => $production_order_no,
                ':vi_type' => $vi_type,
                ':formatted_c1_date' => $formatted_c1_date,
                ':formatted_cia_date' => $formatted_cia_date,
                ':remark' => $remark,
                ':status' => 'A',
                ':is_display' => '1',
                ':show_collapse' => '1'
            ]);
        }

        $mlfb_query = "SELECT material_description FROM mlfb_details WHERE material_description=:material_description";
        $mlfb_response = DbManager::fetchPDOQueryData('spectra_db', $mlfb_query, [":material_description" => $mlfb_no])["data"];
        if (empty($mlfb_response[0]['material_description'])) {
            $add_serial_no = $serial_no;
            $insertmlfbQuery = "
            INSERT INTO mlfb_details
            (material_description, mlfb_no, product_name, rating, width, vi_type, series_no, ptd_no)
            VALUES(:material_description, :mlfb_no, :product_name, :rating, :width, :vi_type, :series_no, :ptd_no)";
            $new_mlfb_no = substr($mlfb_no, 0, 8);
            DbManager::fetchPDOQuery('spectra_db', $insertmlfbQuery, [
                ':material_description' => $mlfb_no,
                ':mlfb_no' => $new_mlfb_no,
                ':product_name' => $product_name,
                ':rating' => $rating,
                ':width' => $width,
                ':vi_type' => $vi_type,
                ':series_no' => $add_serial_no,
                ':ptd_no' => $ptd_no
            ]);
        }
        $this->insertGroupName($group_name);
        $this->insertRating($rating);
        $this->insertWidth($width);
        $this->insertTrolleyType($trolley_type);
        $this->insertAddon($addon);
        echo json_encode(["message" => "Successfully", "code" => 200], JSON_PRETTY_PRINT);
        exit;
    }

    public function deleteBreaker($breaker_id = 0)
    {
        $breakerEntity = $this->getBreakerData($breaker_id, "1");
        if (!isset($breakerEntity)) {
            $this->setDeleteBreakerResponseMessage('Data not found. Operation cancelled.');
        }
    
        $query_delete = "UPDATE tbl_breaker_details SET status=:status where id =:p1";
        $response_delete_query = DbManager::fetchPDOQuery('spectra_db', $query_delete, [":status" => 'D', ":p1" => $breaker_id]);
        http_response_code(200);
        return $breaker_id;
    }

    public function setDeleteBreakerResponseMessage($breakerMessage = ""): void
    {
        http_response_code(500);
        echo json_encode($breakerMessage, JSON_PRETTY_PRINT);
        exit();
    }

    public function getGroupNameSuggestions()
    {
        $searchTerm = $_POST['searchTerm'] ?? null;
        $query = "SELECT DISTINCT group_name
                  FROM breaker_group_name
                  WHERE group_name like :group_name
                  ORDER BY id DESC
                  LIMIT 200";
        $res = DbManager::fetchPDOQueryData('spectra_db', $query, [":group_name" => "%$searchTerm%"])["data"];
        $product_info = array();
        $product_info["data"] = $res;
        echo json_encode($product_info, JSON_THROW_ON_ERROR);
        exit;
    }

    public function getSalesOrderSuggestions()
    {
        $searchTerm = $_POST['searchTerm'] ?? null;
        $query = "SELECT DISTINCT sales_order_no
                  FROM tbl_breaker_details
                  WHERE sales_order_no like :sales_order_no
                  ORDER BY id DESC
                  LIMIT 200";
        $res = DbManager::fetchPDOQueryData('spectra_db', $query, [":sales_order_no" => "%$searchTerm%"])["data"];
        $product_info = array();
        $product_info["data"] = $res;
        echo json_encode($product_info, JSON_THROW_ON_ERROR);
        exit;
    }

    public function getItemNoSuggestions()
    {
        $searchTerm = $_POST['searchTerm'] ?? null;
        $query = "SELECT DISTINCT item_no
                  FROM tbl_breaker_details
                  WHERE item_no like :item_no
                  ORDER BY id DESC
                  LIMIT 200";
        $res = DbManager::fetchPDOQueryData('spectra_db', $query, [":item_no" => "%$searchTerm%"])["data"];
        $product_info = array();
        $product_info["data"] = $res;
        echo json_encode($product_info, JSON_THROW_ON_ERROR);
        exit;
    }

    public function getClientNameSuggestions()
    {
        $searchTerm = $_POST['searchTerm'] ?? null;
        $query = "SELECT DISTINCT client
                  FROM tbl_breaker_details
                  WHERE client like :client
                  ORDER BY id DESC
                  LIMIT 200";
        $res = DbManager::fetchPDOQueryData('spectra_db', $query, [":client" => "%$searchTerm%"])["data"];
        $product_info = array();
        $product_info["data"] = $res;
        echo json_encode($product_info, JSON_THROW_ON_ERROR);
        exit;
    }

    public function getMlfbNoSuggestions()
    {
        $searchTerm = $_POST['searchTerm'] ?? null;
        $query = "SELECT DISTINCT material_description as mlfb_no
                  FROM mlfb_details
                  WHERE material_description like :material_description
                  ORDER BY id DESC
                  LIMIT 200";
        $res = DbManager::fetchPDOQueryData('spectra_db', $query, [":material_description" => "%$searchTerm%"])["data"];
        $product_info = array();
        $product_info["data"] = $res;
        echo json_encode($product_info, JSON_THROW_ON_ERROR);
        exit;
    }

    public function getMlfbDetails()
    {
        $product_info = array();
        $mlfb_no = $_POST['mlfb_no'] ?? null;
        $new_mlfb_no = substr($mlfb_no, 0, 8);
        
        $query = "SELECT *
            FROM mlfb_details
            WHERE mlfb_no = :mlfb_no
            ORDER BY id DESC";
        $res = DbManager::fetchPDOQueryData('spectra_db', $query, [":mlfb_no" => "$new_mlfb_no"])["data"][0];
        
        if (empty($res)) {
            $sql_mlfb = "SELECT product_name FROM tbl_product WHERE mlfb_num like :mlfb_num";
            $result_mlfb = DbManager::fetchPDOQueryData('spectra_db', $sql_mlfb, [":mlfb_num" => "%$new_mlfb_no%"])["data"];
            if (empty($result_mlfb)) {
                $product_info["error"] = "The MLFB number you entered is not found in the system. Please map the MLFB number to a product in the master data.";
            } else {
                $sql = "SELECT MAX(serial_no) AS max_serial_no FROM tbl_breaker_details WHERE product_name =:product_name";
                $result = DbManager::fetchPDOQueryData('spectra_db', $sql, [":product_name" => $result_mlfb[0]['product_name']])["data"];
                
                $product_info["data"]['rating'] = '';
                $product_info["data"]['product_name'] = '';
                $product_info["data"]['width'] = '';
                $product_info["data"]['vi_type'] = '';
                if ($mlfb_no == "NA") {
                    $product_info["data"]['series_no'] = '';
                    $product_info["data"]['ptd_no'] = '';
                } else {
                    if ($result[0]["max_serial_no"] == "") {
                        $product_info["data"]['series_no'] = '';
                    } else {
                        $max_id = str_pad(($result[0]["max_serial_no"] + 1), 8, "0", STR_PAD_LEFT);
                        $product_info["data"]['series_no'] = $max_id;
                    }
                    $product_info["data"]['ptd_no'] = '';
                }
            }
        } else {
            $query_md = "SELECT *
                    FROM mlfb_details
                    WHERE material_description = :material_description
                    ORDER BY id DESC";
            $res_md = DbManager::fetchPDOQueryData('spectra_db', $query_md, [":material_description" => "$mlfb_no"])["data"][0];
            
            $product_name = $res['product_name'];
            $sql = "SELECT MAX(serial_no) AS max_serial_no FROM tbl_breaker_details WHERE product_name =:product_name";
            $result = DbManager::fetchPDOQueryData('spectra_db', $sql, [":product_name" => $product_name])["data"];

            if (empty($result) || $result[0]["max_serial_no"] === null) {
                $new_series_no = str_pad(($res['series_no'] + 1), 8, "0", STR_PAD_LEFT);
            } else {
                $max_id = $result[0]["max_serial_no"];
                $max_id = str_pad($max_id, 8, "0", STR_PAD_LEFT);
                $series_no = str_pad($res['series_no'], 8, "0", STR_PAD_LEFT);
            
                if ($max_id < $series_no) {
                    $new_series_no = $series_no + 1;
                } else {
                    $new_series_no = $max_id + 1;
                }
                $new_series_no = str_pad($new_series_no, 8, "0", STR_PAD_LEFT);
            }
            
            if ($mlfb_no == "NA") {
                $new_series_no = '';
                $res_md['ptd_no'] = '';
            }

            if (empty($res_md)) {
                $product_info["data"]['rating'] = '';
                $product_info["data"]['product_name'] = $product_name;
                $product_info["data"]['width'] = '';
                $product_info["data"]['vi_type'] = '';
                $product_info["data"]['series_no'] = $new_series_no;
                $product_info["data"]['ptd_no'] = '';
            } else {
                $product_info["data"] = $res_md;
                $product_info["data"]['series_no'] = $new_series_no;
            }
        }
        echo json_encode($product_info, JSON_THROW_ON_ERROR);
        exit;
    }

    public function getSerialNo()
    {
        $product_info = array();
        $mlfb_no = $_POST['mlfb_no'] ?? null;
        $new_mlfb_no = substr($mlfb_no, 0, 8);
        
        if ($mlfb_no == "NA") {
            $product_info["data"]['series_no'] = '';
        } else {
            $query = "SELECT *
                FROM mlfb_details
                WHERE mlfb_no = :mlfb_no
                ORDER BY id DESC";
            $res = DbManager::fetchPDOQueryData('spectra_db', $query, [":mlfb_no" => "$new_mlfb_no"])["data"][0];
            
            if (empty($res)) {
                $sql_mlfb = "SELECT product_name FROM tbl_product WHERE mlfb_num like :mlfb_num";
                $result_mlfb = DbManager::fetchPDOQueryData('spectra_db', $sql_mlfb, [":mlfb_num" => "%$new_mlfb_no%"])["data"];
                if (empty($result_mlfb)) {
                    $product_info["data"]['series_no'] = '';
                } else {
                    $sql = "SELECT MAX(serial_no) AS max_serial_no FROM tbl_breaker_details WHERE product_name =:product_name";
                    $result = DbManager::fetchPDOQueryData('spectra_db', $sql, [":product_name" => $result_mlfb[0]['product_name']])["data"];
                    if ($result[0]["max_serial_no"] == "") {
                        $product_info["data"]['series_no'] = '';
                    } else {
                        $max_id = str_pad(($result[0]["max_serial_no"] + 1), 8, "0", STR_PAD_LEFT);
                        $product_info["data"]['series_no'] = $max_id;
                    }
                }
            } else {
                $product_name = $res['product_name'];
                $sql = "SELECT MAX(serial_no) AS max_serial_no FROM tbl_breaker_details WHERE product_name =:product_name";
                $result = DbManager::fetchPDOQueryData('spectra_db', $sql, [":product_name" => $product_name])["data"];
                if (empty($result) || $result[0]["max_serial_no"] === null) {
                    $new_series_no = str_pad(($res['series_no'] + 1), 8, "0", STR_PAD_LEFT);
                } else {
                    $max_id = $result[0]["max_serial_no"];
                    $max_id = str_pad($max_id, 8, "0", STR_PAD_LEFT);
                    $series_no = str_pad($res['series_no'], 8, "0", STR_PAD_LEFT);
                    if ($max_id < $series_no) {
                        $new_series_no = $series_no + 1;
                    } else {
                        $new_series_no = $max_id + 1;
                    }
                    $new_series_no = str_pad($new_series_no, 8, "0", STR_PAD_LEFT);
                }
                $product_info["data"]['series_no'] = $new_series_no;
            }
        }
        echo json_encode($product_info, JSON_THROW_ON_ERROR);
        exit;
    }

    public function getMlfbDetailsById()
    {
        $id = $_POST['id'] ?? null;
        $query = "SELECT *
                  FROM tbl_breaker_details
                  WHERE id = :id
                  ORDER BY id DESC";
        $res = DbManager::fetchPDOQueryData('spectra_db', $query, [":id" => "$id"])["data"][0];
        $product_info = array();
        $product_info["data"] = $res;
        echo json_encode($product_info, JSON_THROW_ON_ERROR);
        exit;
    }

    public function getRatingSuggestions()
    {
        $searchTerm = $_POST['searchTerm'] ?? null;
        $query = "SELECT DISTINCT rating
                  FROM breaker_rating
                  WHERE rating like :rating
                  ORDER BY id DESC
                  LIMIT 200";
        $res = DbManager::fetchPDOQueryData('spectra_db', $query, [":rating" => "%$searchTerm%"])["data"];
        $product_info = array();
        $product_info["data"] = $res;
        echo json_encode($product_info, JSON_THROW_ON_ERROR);
        exit;
    }

    public function getProductNameSuggestions()
    {
        $searchTerm = $_POST['searchTerm'] ?? null;
        $query = "SELECT DISTINCT product_name
                  FROM tbl_product
                  WHERE product_name like :product_name
                  ORDER BY product_id DESC
                  LIMIT 200";
        $res = DbManager::fetchPDOQueryData('spectra_db', $query, [":product_name" => "%$searchTerm%"])["data"];
        $product_info = array();
        $product_info["data"] = $res;
        echo json_encode($product_info, JSON_THROW_ON_ERROR);
        exit;
    }

    public function getWidthSuggestions()
    {
        $searchTerm = $_POST['searchTerm'] ?? null;
        $query = "SELECT DISTINCT width
                  FROM breaker_width
                  WHERE width like :width
                  ORDER BY id DESC
                  LIMIT 200";
        $res = DbManager::fetchPDOQueryData('spectra_db', $query, [":width" => "%$searchTerm%"])["data"];
        $product_info = array();
        $product_info["data"] = $res;
        echo json_encode($product_info, JSON_THROW_ON_ERROR);
        exit;
    }

    public function getTrolleyTypeSuggestions()
    {
        $searchTerm = $_POST['searchTerm'] ?? null;
        $query = "SELECT DISTINCT trolley_type
                  FROM breaker_trolley_type
                  WHERE trolley_type like :trolley_type
                  ORDER BY id DESC
                  LIMIT 200";
        $res = DbManager::fetchPDOQueryData('spectra_db', $query, [":trolley_type" => "%$searchTerm%"])["data"];
        $product_info = array();
        $product_info["data"] = $res;
        echo json_encode($product_info, JSON_THROW_ON_ERROR);
        exit;
    }

    public function getTrolleyRefairSuggestions()
    {
        $searchTerm = $_POST['searchTerm'] ?? null;
        $query = "SELECT DISTINCT trolley_type as trolley_refair
                  FROM breaker_trolley_type
                  WHERE trolley_type like :trolley_type
                  ORDER BY id DESC
                  LIMIT 200";
        $res = DbManager::fetchPDOQueryData('spectra_db', $query, [":trolley_type" => "%$searchTerm%"])["data"];
        $product_info = array();
        $product_info["data"] = $res;
        echo json_encode($product_info, JSON_THROW_ON_ERROR);
        exit;
    }

    public function getAddonSuggestions()
    {
        $searchTerm = $_POST['searchTerm'] ?? null;
        $query = "SELECT DISTINCT addon
                  FROM breaker_addon
                  WHERE addon like :addon
                  ORDER BY id DESC
                  LIMIT 200";
        $res = DbManager::fetchPDOQueryData('spectra_db', $query, [":addon" => "%$searchTerm%"])["data"];
        $product_info = array();
        $product_info["data"] = $res;
        echo json_encode($product_info, JSON_THROW_ON_ERROR);
        exit;
    }
}