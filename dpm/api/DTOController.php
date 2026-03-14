<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/shared.php";
header('Content-Type:application/json;charset=utf-8;');
// Enable all error reporting
// ini_set('display_errors', 1);  // Displays errors on the page
// ini_set('display_startup_errors', 1);  // Displays startup errors
// error_reporting(E_ALL);  // Reports all errors (notices, warnings, and errors)

 //SharedManager::print($_POST);
 //SharedManager::print($_GET);
 
// exit();
$controller = new DTOController();
switch ($_GET["action"]) {
    case "allList":
        $controller->listDto(0);
        break;
    case "allmat":
        $controller->listMat(0);
        break;
    case "allNum":
        $controller->listNum(0);
        break;    
    case "plannedMonthAllList":
        $controller->plannedMonthlistBreakers(0);
        break;
    case "getDtoData":
        $controller->getDtoData($_GET["id"]);
        break;
    case "generateDocNo":
        $controller->generateDocNo();
        break;
    case "getUpdateData":
        $controller->getUpdateData($_GET["id"]);
        break;
    case "getUpdateMatRegister":
        $controller->getUpdateMatRegister($_GET["id"]);
        break;
    case "getUpdateNumberData":
        $controller->getUpdateNumberData($_GET["id"]);
        break;
    // case "getOfferData":
    //     $controller->getOfferData($_GET["id"]);
    //     break;
    case "getImage":
        $controller->getImage($_GET["docNo"]);
        break;
    case "getDocs":
        $controller->getDocs($_GET["docNo"]);
        break;
    case "delete":
        $dto_id = $_GET['dto_id'] ?? 0;

    if ($dto_id < 1 || empty($dto_id)) {
        http_response_code(400); // Use 400 Bad Request for invalid input
        echo json_encode(["message" => "Invalid DTO ID"]);
        exit();
    }

    $breaker_id_response = $controller->deleteBreaker($dto_id);

    http_response_code(200);
    echo json_encode([
        "success" => true,
        "deleted_id" => $breaker_id_response
    ], JSON_PRETTY_PRINT);
    exit();
    default:
        break;
}

switch ($_POST["action"]) {
    case "registration":
        $controller->registration();
        break;
    case "numregister":
        $controller->numregister();
        break;
    case "matregister":
        $controller->matregister();
        break;
    case "edit":
        $controller->update();
        break;
    case "editnumber":
        $controller->updateNumber();
        break;
    case "updateoffer":
        $controller->updateOffer();
        break;
    case "editimage":
        $controller->updateImage();
        break;
    case "generateDrwNo":
        $controller->generateDrwNo();
        break;
    case "generateOrderDrawingNo":
        $controller->generateOrderDrawingNo();
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
    case "updateMatRegister":
        $controller->updateMatRegister();
        break;
    default:
        break;
}
exit;

class DTOController{
    private $currentUserGroupID;

    public function __construct(){
        $userGroupID = SharedManager::getUser()["GroupID"];
        if($userGroupID == 2 || $userGroupID == 8) {

        } else {
            returnHttpResponse(403, "Not Authorized");
        }
        $this->currentUserGroupID = $userGroupID;
    }

    public function registration()
    {
            $error = 0;
            $tableData = json_decode($_POST['tableData'], true);
            $stageName = $_POST['stageName'];
            $productName = $_POST['productName'];
            $iacRating = $_POST['iacRating'];
            $docNo = $_POST['docNo'];
            $shortDescription = $_POST['shortDescription'];
            $woundCTs = $_POST['woundCTs'];
            $windowCTs = $_POST['windowCTs'];
            $cablesBus = $_POST['cablesBus'];
            $cabCore = $_POST['cabCore'];
            $cabEntry = $_POST['cabEntry'];
            $ratedVol = $_POST['ratedVol'];
            $ratedCir = $_POST['ratedCir'];
            $ratedCurrent = $_POST['ratedCurrent'];
            $width = $_POST['width'];
            $rearBoxDepth = $_POST['rearBoxDepth'];
            $feederMat = $_POST['feederMat'];
            $realBy = $_POST['realBy'];
            $info = $_POST['info'];
            $orderNo = $_POST['orderNo'];
            $DrawNo = $_POST['DrawNo'];
            $eartSwitch = $_POST['eartSwitch'];
            $doVt = $_POST['doVt'];
            $toolSel = $_POST['toolSel'];
            $addOn = $_POST['addOn'];
            $solenoid = $_POST['solenoid'];
            $limSwi = $_POST['limSwi'];
            $meshAss = $_POST['meshAss'];
            $lampRearCover = $_POST['lampRearCover'];
            $glandPlate = $_POST['glandPlate'];
            $rearCover = $_POST['rearCover'];
            $DispUser = $_POST['DispUser'];

            $image_name = '';

            // OPTIONAL FILE UPLOAD
            if (isset($_FILES['exampleInputFile']) && $_FILES['exampleInputFile']['error'] !== UPLOAD_ERR_NO_FILE) {
                if ($_FILES['exampleInputFile']['error'] == 0) {
                    $image_name = $_FILES['exampleInputFile']['name'];
                    $image_tmp_name = $_FILES['exampleInputFile']['tmp_name'];
                    $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/images/DTO/';
                    $image_path = $upload_dir . basename($image_name);
                    $image_type = strtolower(pathinfo($image_path, PATHINFO_EXTENSION));

                    if (in_array($image_type, ['jpg', 'jpeg', 'png', 'gif', 'pdf'])) {
                        if (!move_uploaded_file($image_tmp_name, $image_path)) {
                            $error = 1;
                            $error_msg = "Error moving file";
                        }
                    } else {
                        $error = 1;
                        $error_msg = "Invalid file type. Only JPG, JPEG, PNG, GIF, and PDF are allowed.";
                    }
                } else {
                    $error = 1;
                    $error_msg = "File upload error.";
                }
            }

            $select_query = "SELECT docNo FROM order_db WHERE docNo = :docNo";
            $select_response = DbManager::fetchPDOQueryData('spectra_db', $select_query, [":docNo" => $docNo])["data"];

            if ($error == 0) {
                if (!empty($select_response)) {
                    $docNo = $select_response[0]['docNo'];
                    $parts = explode('_', $docNo);
                    $number = (int)array_pop($parts);
                    $docNo = implode('_', $parts) . '_' . ($number + 1);
                }

                $sqlA = "INSERT INTO order_db (stageName, productName, iacRating, docNo, shortDescription, woundCTs, windowCTs, cablesBus, cabCore, cabEntry, ratedVol, ratedCir, ratedCurrent,
                    width, rearBoxDepth, feederMat, realBy, info, orderNo, DrawNo, eartSwitch, doVt, toolSel, exampleInputFile, addOn, solenoid, limSwi, meshAss, lampRearCover,
                    glandPlate, rearCover, DispUser) 
                    VALUES (:stageName, :productName, :iacRating, :docNo, :shortDescription, :woundCTs, :windowCTs, :cablesBus, :cabCore, :cabEntry, :ratedVol, :ratedCir, :ratedCurrent,
                    :width, :rearBoxDepth, :feederMat, :realBy, :info, :orderNo, :DrawNo, :eartSwitch, :doVt, :toolSel, :exampleInputFile, :addOn, :solenoid, :limSwi, :meshAss, :lampRearCover,
                    :glandPlate, :rearCover, :DispUser)";

                $queryParamsA = [
                    ":stageName" => $stageName,
                    ":productName" => $productName,
                    ":iacRating" => $iacRating,
                    ":docNo" => $docNo,
                    ":shortDescription" => $shortDescription,
                    ":woundCTs" => $woundCTs,
                    ":windowCTs" => $windowCTs,
                    ":cablesBus" => $cablesBus,
                    ":cabCore" => $cabCore,
                    ":cabEntry" => $cabEntry,
                    ":ratedVol" => $ratedVol,
                    ":ratedCir" => $ratedCir,
                    ":ratedCurrent" => $ratedCurrent,
                    ":width" => $width,
                    ":rearBoxDepth" => $rearBoxDepth,
                    ":feederMat" => $feederMat,
                    ":realBy" => $realBy,
                    ":info" => $info,
                    ":orderNo" => $orderNo,
                    ":DrawNo" => $DrawNo,
                    ":eartSwitch" => $eartSwitch,
                    ":doVt" => $doVt,
                    ":toolSel" => $toolSel,
                    ":exampleInputFile" => $image_name, // always set, can be empty
                    ":addOn" => $addOn,
                    ":solenoid" => $solenoid,
                    ":limSwi" => $limSwi,
                    ":meshAss" => $meshAss,
                    ":lampRearCover" => $lampRearCover,
                    ":glandPlate" => $glandPlate,
                    ":rearCover" => $rearCover,
                    ":DispUser" => $DispUser
                ];

                $queryA = DbManager::fetchPDOQuery('spectra_db', $sqlA, $queryParamsA);

                $sqlB = "INSERT INTO order_release (docNo2, matNo, dMat, quanTt, descRp, kMat)
                        VALUES (:docNo2, :matNo, :dMat, :quanTt, :descRp, :kMat)";

                foreach ($tableData as $row) {
                    $queryParamsB = [
                        ":docNo2" => $docNo,
                        ":matNo" => $row['material_no'],
                        ":dMat" => $row['deleted_material_no'],
                        ":quanTt" => (int)$row['quantity'],
                        ":descRp" => $row['description'],
                        ":kMat" => $row['k_mat']
                    ];

                    try {
                        $queryB = DbManager::fetchPDOQuery('spectra_db', $sqlB, $queryParamsB);
                    } catch (Exception $e) {
                        $error = 1;
                    }
                }

                // OPTIONAL MULTIPLE LAYOUT FILES
                if (isset($_FILES['layoutFiles'])) {
                    $file_count = count($_FILES['layoutFiles']['name']);
                    $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/images/DTO/ADD/';

                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }

                    for ($i = 0; $i < $file_count; $i++) {
                        $tmp_name = $_FILES['layoutFiles']['tmp_name'][$i];
                        $file_name = $_FILES['layoutFiles']['name'][$i];
                        $file_error = $_FILES['layoutFiles']['error'][$i];

                        $destination = $upload_dir . $file_name;
                        $file_type = mime_content_type($tmp_name);
                        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];

                        if ($file_error === UPLOAD_ERR_OK && in_array($file_type, $allowed_types)) {
                            if (move_uploaded_file($tmp_name, $destination)) {
                                $sqlC = "INSERT INTO order_add_doc (docNo, file_name) VALUES (:docNo, :file_name)";
                                $queryParamsC = [
                                    ":docNo" => $docNo,
                                    ":file_name" => $file_name
                                ];
                                $queryC = DbManager::fetchPDOQuery('spectra_db', $sqlC, $queryParamsC);
                            } else {
                                error_log("Failed to move uploaded file: $file_name");
                            }
                        } else {
                            error_log("Invalid file or error: $file_name");
                        }
                    }
                }
            }

            returnHttpResponse(200, "Registration Done Successfully");
        }


        public function numregister()
        {   
            // SharedManager::print($_POST);
            // exit('aaaaaaaaaaaaaaaaa');
            $error = 0;
            $prdName = $_POST['prdName'];
            $drwName = $_POST['drwName'];
            $matName = $_POST['matName'];
            $mainNumber = $_POST['mainNumber'];
            $startNumber = $_POST['startNumber'];
            $endNumber = $_POST['endNumber'];
            $user = SharedManager::getUser()["Name"] . ' ' . SharedManager::getUser()["Surname"] ?? '';
            
            if ($error == 0) {
                $sqlnum = "INSERT INTO tbl_drawing_number (prdName, drwName, matName, mainNumber, startNumber, endNumber , user) 
                VALUES (:prdName, :drwName, :matName, :mainNumber, :startNumber, :endNumber , :user)";
            
                $queryParamsnum = [
                ":prdName" => $prdName,
                ":drwName" => $drwName,
                ":matName" => $matName,
                ":mainNumber" => $mainNumber,
                ":startNumber" => $startNumber,
                ":endNumber" => $endNumber,
                ":user" => $user
                ];
                $query = DbManager::fetchPDOQuery('spectra_db', $sqlnum, $queryParamsnum);
                
            }

            returnHttpResponse(200, "Registration Done Successfully");
        }

public function matregister()
{
    // SharedManager::print($_SESSION);
     //exit('Goggya');

    $error = 0;
    $product_name = $_POST['product_name'] ?? '';
    $drawing_name = $_POST['drawing_name'] ?? '';
    $drawing_number = $_POST['drawing_number'] ?? '';
    $material_type = $_POST['material_type'] ?? '';
    $ka_rating = $_POST['ka_rating'] ?? '';
    $width = $_POST['width'] ?? '';
    $description = $_POST['description'] ?? '';
    $thickness = $_POST['thickness'] ?? '';
    $rear_box = $_POST['rear_box'] ?? '';
    $end_cover_location = $_POST['end_cover_location'] ?? '';
    $ebb_cutout = $_POST['ebb_cutout'] ?? '';
    $ebb_size = $_POST['ebb_size'] ?? '';
    $cable_entry = $_POST['cable_entry'] ?? '';
    $gp_thickness = $_POST['gp_thickness'] ?? '';
    $gp_material = $_POST['gp_material'] ?? '';
    $interlock = $_POST['interlock'] ?? '';
    $ir_window = $_POST['ir_window'] ?? '';
    $nameplate = $_POST['nameplate'] ?? '';
    $viewing_window = $_POST['viewing_window'] ?? '';
    $lhs_panel_rb = $_POST['lhs_panel_rb'] ?? '';
    $rhs_panel_rb = $_POST['rhs_panel_rb'] ?? '';
    $rear_box_type = $_POST['rear_box_type'] ?? '';
    $ct_type = $_POST['ct_type'] ?? '';
    $cable_number = $_POST['cable_number'] ?? '';
    $cbct = $_POST['cbct'] ?? '';
    $panel_width = $_POST['panel_width'] ?? '';
    $feeder_bar_size = $_POST['feeder_bar_size'] ?? '';
    $mbb_size = $_POST['mbb_size'] ?? '';
    $sizeofbusbar = $_POST['sizeofbusbar'] ?? '';
    $material = $_POST['material'] ?? '';
    $ag_plating = $_POST['ag_plating'] ?? '';
    $mbb_run = $_POST['mbb_run'] ?? '';
    $feeder_run = $_POST['feeder_run'] ?? '';
    $feeder_size = $_POST['feeder_size'] ?? '';
    $short_text = $_POST['short_text'] ?? '';
    $remarks = $_POST['remarks'] ?? '';
    $user = SharedManager::getUser()["Name"] . ' ' . SharedManager::getUser()["Surname"] ?? '';

    if ($error == 0) {
        $sql = "INSERT INTO tbl_material_registration (
            product_name, drawing_name, drawing_number, material_type, ka_rating, width,
            description, thickness, rear_box, end_cover_location, ebb_cutout,ebb_size,
            cable_entry, gp_thickness, gp_material, interlock,
            ir_window, nameplate, viewing_window, lhs_panel_rb, rhs_panel_rb,
            rear_box_type, ct_type, cable_number,
            cbct, panel_width, feeder_bar_size,
            mbb_size, sizeofbusbar, material, ag_plating, mbb_run, feeder_run, feeder_size, short_text, remarks, user
        ) VALUES (
            :product_name, :drawing_name, :drawing_number, :material_type, :ka_rating, :width,
            :description, :thickness, :rear_box, :end_cover_location, :ebb_cutout, :ebb_size,
            :cable_entry, :gp_thickness, :gp_material, :interlock,
            :ir_window, :nameplate, :viewing_window, :lhs_panel_rb, :rhs_panel_rb,
            :rear_box_type, :ct_type, :cable_number,
            :cbct, :panel_width, :feeder_bar_size,
            :mbb_size, :sizeofbusbar, :material, :ag_plating,
            :mbb_run, :feeder_run, :feeder_size, 
            :short_text, :remarks, :user
            
        )";

        $params = [
            ":product_name" => $product_name,
            ":drawing_name" => $drawing_name,
            ":drawing_number" => $drawing_number,
            ":material_type" => $material_type,
            ":ka_rating" => $ka_rating,
            ":width" => $width,
            ":description" => $description,
            ":thickness" => $thickness,
            ":rear_box" => $rear_box,
            ":end_cover_location" => $end_cover_location,
            ":ebb_cutout" => $ebb_cutout,
            ":ebb_size" => $ebb_size,
            ":cable_entry" => $cable_entry,
            ":gp_thickness" => $gp_thickness,
            ":gp_material" => $gp_material,
            ":interlock" => $interlock,
            ":ir_window" => $ir_window,
            ":nameplate" => $nameplate,
            ":viewing_window" => $viewing_window,
            ":lhs_panel_rb" => $lhs_panel_rb,
            ":rhs_panel_rb" => $rhs_panel_rb,
            ":rear_box_type" => $rear_box_type,
            ":ct_type" => $ct_type,
            ":cable_number" => $cable_number,
            ":cbct" => $cbct,
            ":panel_width" => $panel_width,
            ":feeder_bar_size" => $feeder_bar_size,
            ":mbb_size" => $mbb_size,
            ":sizeofbusbar" => $sizeofbusbar,
            ":material" => $material,
            ":ag_plating" => $ag_plating,
            ":mbb_run" => $mbb_run,
            ":feeder_run" => $feeder_run,
            ":feeder_size" => $feeder_size,
            ":short_text" => $short_text,
            ":remarks" => $remarks,
            ":user" => $user
        ];

        //DbManager::fetchPDOQuery('spectra_db', $sql, $params);
         DbManager::fetchPDOQuery('spectra_db', $sql, $params);

        // Instead of returnHttpResponse, do this:
        header('Content-Type: application/json');
        echo json_encode([
            "message" => "Registration Done Successfully",
            "drawing_number" => $drawing_number,
            "short_text" => $short_text
        ]);
        exit;
    }

    //returnHttpResponse(200, "Registration Done Successfully");
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

    // public function listBreakers($openCloseState)
    // {
    //     global $replaceSubProductType;
    //     $start_date = $_GET['start_date'] ?? null;
    //     $finish_date = $_GET['finish_date'] ?? null;
    //     $firstOfMonth = date('Y-m-01 00:00:00');
    //     $firstOfMonth = date('Y-m-d H:i:s', strtotime($firstOfMonth . ' -1 second'));

    //     $date_query = "";
    //     if ($start_date != null && $finish_date != null) {
    //         $startdateObj = DateTime::createFromFormat('d-m-Y', $start_date);
    //         $formattedStartDate = $startdateObj->format('Y-m-d 00:00:00');
    //         $enddateObj = DateTime::createFromFormat('d-m-Y', $finish_date);
    //         $formattedEndDate = $enddateObj->format('Y-m-d 23:59:59');

    //         $date_query = " AND bd.status =:status AND bd.create_date BETWEEN :formattedStartDate AND :formattedEndDate ORDER BY bd.id DESC";
    //         $pdo_params = [":status" => 'A', ":formattedStartDate" => $formattedStartDate, ":formattedEndDate" => $formattedEndDate];
    //     } else {
    //         $date_query = " AND bd.status =:status AND bd.create_date >=:first_of_month ORDER BY bd.id DESC";
    //         $pdo_params = [":status" => 'A', ":first_of_month" => $firstOfMonth];
    //     }
        
    //     $query = "SELECT
    //                 bd.*,
    //                 DATE_FORMAT(bd.cdd_date, '%d-%m-%Y') AS cdd_date_formatted,
    //                 DATE_FORMAT(bd.plan_month_date, '%d-%m-%Y') AS plan_month_date_formatted,
    //                 DATE_FORMAT(bd.c1_date, '%d-%m-%Y') AS c1_date_formatted,
    //                 DATE_FORMAT(bd.cia_date, '%d-%m-%Y') AS cia_date_formatted,
    //                 CASE WHEN bd.id IN (SELECT parent_id FROM tbl_breaker_details) THEN 1 ELSE 0 END AS is_parent,
    //                 t.barcode,
    //                 CASE WHEN t.station_name = 'Stamping' AND t.status = '0' THEN 'Stamping Pending'
    //                     WHEN t.station_name = 'Stamping' AND t.status = '1' THEN 'Stamping Completed'
    //                     ELSE t.station_name
    //                 END AS station_name,
    //                 t.end_datetime,
    //                 t.user_id,
    //                 t.status AS transaction_status
    //             FROM tbl_breaker_details bd
    //             LEFT JOIN (
    //                 SELECT
    //                     barcode,
    //                     station_name,
    //                     user_id,
    //                     up_date,
    //                     end_datetime,
    //                     STATUS AS STATUS
    //                 FROM (
    //                     SELECT
    //                         barcode,
    //                         station_name,
    //                         user_id,
    //                         up_date,
    //                         ROW_NUMBER() OVER (PARTITION BY barcode ORDER BY up_date DESC) AS rn,
    //                         MAX(up_date) OVER (PARTITION BY barcode) AS end_datetime, STATUS
    //                     FROM tbl_transactions
    //                 ) t1
    //                 WHERE t1.rn = 1
    //                 ORDER BY up_date DESC
    //             ) t ON bd.serial_no = SUBSTRING(t.barcode, 1, 8)
    //                 AND bd.sales_order_no = SUBSTRING(t.barcode, -29, 10)
    //                 AND bd.item_no = SUBSTRING(t.barcode, -18, 6)
    //             WHERE 1 $date_query";

    //     $res = DbManager::fetchPDOQueryData('spectra_db', $query, $pdo_params)["data"];
    //     // SharedManager::print($res);
    //     $cnt = count($res);
    //     $product_info = array();
    //     if ($cnt > 0) {
    //         $sr = 1;
    //         $ProductionOrderQuantityCount = 0;
    //         foreach ($res as $data) {    
    //             $product_data['srNo'] = $sr;
    //             $sr++;
    //             $product_data['id'] = trim($data['id']);
    //             $product_data['parentId'] = trim($data['parent_id']);
    //             $product_data['isDisplay'] = trim($data['is_display']);
    //             $product_data['showCollapse'] = trim($data['show_collapse']);
    //             $product_data['uniqueId'] = trim($data['unique_id']);
    //             $product_data['groupName'] = $data['group_name'];
    //             $product_data['cddDate'] = $data['cdd_date_formatted'];
    //             $product_data['planMonthDate'] = $data['plan_month_date_formatted'];
    //             $product_data['salesOrderNo'] = trim($data['sales_order_no']);
    //             $product_data['itemNo'] = trim($data['item_no']);
    //             $product_data['client'] = $data['client'];
    //             $product_data['mlfbNo'] = $data['mlfb_no'];
    //             $product_data['rating'] = $data['rating'];
    //             $product_data['productName'] = $data['product_name'];
    //             $product_data['width'] = $data['width'];
    //             $product_data['trolleyType'] = $data['trolley_type'];
    //             $product_data['trolleyRefair'] = $data['trolley_refair'];
    //             $product_data['mlfbNoUniqueNumber'] = preg_replace('/[^a-zA-Z0-9 _-]/', '', $product_data['mlfbNo']);
    //             $product_data['totalQuantity'] = trim($data['total_quantity']);
    //             $product_data['productionOrderQuantity'] = trim($data['production_order_quantity']);
    //             $ProductionOrderQuantityCount = $ProductionOrderQuantityCount+$product_data['productionOrderQuantity'];
    //             $product_data['addon'] = trim($data['addon']);
    //             $product_data['serialNo'] = $data['serial_no'];
    //             $product_data['ptdNo'] = trim($data['ptd_no']);
    //             $product_data['productionOrderNo'] = trim($data['production_order_no']);
    //             $product_data['viType'] = $data['vi_type'];
    //             $product_data['c1Date'] = $data['c1_date_formatted'];
    //             $product_data['ciaDate'] = $data['cia_date_formatted'];
    //             $product_data['remark'] = $data['remark'];
    //             $product_data['createDate'] = $data['create_date'];
    //             $product_data['status'] = $data['status'];
    //             $product_data['currentStatus'] = $data['station_name'];
    //             $product_data['user'] = $data['user_id'];
    //             $product_info["data"][] = $product_data;
    //         }
    //     } else {
    //         $product_info["data"] = array();
    //     }
    //     $product_info["ProductionOrderQuantityCount"] = $ProductionOrderQuantityCount;
    //     echo json_encode($product_info, JSON_THROW_ON_ERROR);
    //     exit;
    // }

    public function listDto($openCloseState)
    {
        $start_date = $_GET['start_date'] ?? null;
        $finish_date = $_GET['finish_date'] ?? null;
        if ($start_date && $finish_date) {
            // Add time for the start_date to begin at 00:00:00 and finish_date to end at 23:59:59.
            $start_date = date('Y-m-d 00:00:00', strtotime($start_date));
            $finish_date = date('Y-m-d 23:59:59', strtotime($finish_date));
        }

        $start_date = $_GET['start_date'] ?? null;
        $finish_date = $_GET['finish_date'] ?? null;
        $date_query = "";
        $pdo_params = array();
        if ($start_date != null && $finish_date != null) {
            $startdateObj = DateTime::createFromFormat('d-m-Y', $start_date);
            $formattedStartDate = $startdateObj->format('Y-m-d 00:00:00');
            $enddateObj = DateTime::createFromFormat('d-m-Y', $finish_date);
            $formattedEndDate = $enddateObj->format('Y-m-d 23:59:59');

            $date_query = " WHERE createDate BETWEEN :formattedStartDate AND :formattedEndDate";
            $pdo_params = [":formattedStartDate" => $formattedStartDate, ":formattedEndDate" => $formattedEndDate];
        }
        
        $sql = "SELECT * FROM order_db $date_query ORDER BY id DESC";
        $res = DbManager::fetchPDOQueryData('spectra_db', $sql, $pdo_params)["data"];
        
        $cnt = count($res);
        $product_info = array();
        if ($cnt > 0) {
            $sr = 1;
            foreach ($res as $data) {    
                $product_data['id'] = $sr;
                $sr++;
                $product_data['dto_id'] = trim($data['id']);
                $product_data['stageName'] = trim($data['stageName']);
                $product_data['productName'] = trim($data['productName']);
                $product_data['iacRating'] = trim($data['iacRating']);
                $product_data['docNo'] = trim($data['docNo']);
                $product_data['shortDescription'] = trim($data['shortDescription']);
                $product_data['woundCTs'] = trim($data['woundCTs']);
                $product_data['windowCTs'] = trim($data['windowCTs']);
                $product_data['cablesBus'] = trim($data['cablesBus']);
                $product_data['cabCore'] = trim($data['cabCore']);
                $product_data['cabEntry'] = trim($data['cabEntry']);
                $product_data['ratedVol'] = trim($data['ratedVol']);
                $product_data['ratedCir'] = trim($data['ratedCir']);
                $product_data['ratedCurrent'] = trim($data['ratedCurrent']);
                $product_data['width'] = trim($data['width']);
                $product_data['rearBoxDepth'] = trim($data['rearBoxDepth']);
                $product_data['feederMat'] = trim($data['feederMat']);
                $product_data['realBy'] = trim($data['realBy']);
                $product_data['info'] = trim($data['info']);
                $product_data['orderNo'] = trim($data['orderNo']);
                $product_data['DrawNo'] = trim($data['DrawNo']);
                $product_data['eartSwitch'] = trim($data['eartSwitch']);
                $product_data['doVt'] = trim($data['doVt']);
                $product_data['toolSel'] = trim($data['toolSel']);
                $product_data['exampleInputFile'] = trim($data['exampleInputFile']);
                $product_data['addOn'] = trim($data['addOn']);
                $product_data['solenoid'] = trim($data['solenoid']);
                $product_data['limSwi'] = trim($data['limSwi']);
                $product_data['meshAss'] = trim($data['meshAss']);
                $product_data['lampRearCover'] = trim($data['lampRearCover']);
                $product_data['glandPlate'] = trim($data['glandPlate']);
                $product_data['rearCover'] = trim($data['rearCover']);
                $product_data['DispUser'] = trim($data['DispUser']);
                $product_data['createDate'] = trim($data['createDate']);
                $product_info["data"][] = $product_data;
            }
        }else {
            $product_info["data"] = array();
        }
         //SharedManager::print($product_info);
         //  exit();
        echo json_encode($product_info, JSON_THROW_ON_ERROR);
        exit;
    }   
    
     public function listMat($openCloseState)
    {
        
        $sql = "SELECT * FROM tbl_material_registration ORDER BY id DESC";
        $res = DbManager::fetchPDOQueryData('spectra_db', $sql)["data"];
        
        $cnt = count($res);
        $product_info = array();
        if ($cnt > 0) {
            $sr = 1;
            foreach ($res as $data) {    
                $product_data['id'] = $sr;
                $sr++;
                $product_data['id'] = trim($data['id']);
                $product_data['product_name'] = trim($data['product_name']);
                $product_data['drawing_name'] = trim($data['drawing_name']);
                $product_data['drawing_number'] = trim($data['drawing_number']);
                $product_data['material_type'] = trim($data['material_type']);
                $product_data['ka_rating'] = trim($data['ka_rating']);
                $product_data['width'] = trim($data['width']);
                $product_data['rear_box'] = trim($data['rear_box']);
                $product_data['material'] = trim($data['material']);
                $product_data['thickness'] = trim($data['thickness']);
                $product_data['description'] = trim($data['description']);
                $product_data['panel_width'] = trim($data['panel_width']);
                $product_data['gp_material'] = trim($data['gp_material']);
                $product_data['gp_thickness'] = trim($data['gp_thickness']);
                $product_data['sizeofbusbar'] = trim($data['sizeofbusbar']);
                $product_data['ag_plating'] = trim($data['ag_plating']);
                $product_data['feeder_bar_size'] = trim($data['feeder_bar_size']);
                $product_data['mbb_size'] = trim($data['mbb_size']);
                $product_data['end_cover_location'] = trim($data['end_cover_location']);
                $product_data['ebb_cutout'] = trim($data['ebb_cutout']);
                $product_data['ebb_size'] = trim($data['ebb_size']);
                $product_data['cable_entry'] = trim($data['cable_entry']);
                $product_data['interlock'] = trim($data['interlock']);
                $product_data['ir_window'] = trim($data['ir_window']);
                $product_data['nameplate'] = trim($data['nameplate']);
                $product_data['viewing_window'] = trim($data['viewing_window']);
                $product_data['rear_box_type'] = trim($data['rear_box_type']);
                $product_data['lhs_panel_rb'] = trim($data['lhs_panel_rb']);
                $product_data['rhs_panel_rb'] = trim($data['rhs_panel_rb']);
                $product_data['ct_type'] = trim($data['ct_type']);
                $product_data['cable_number'] = trim($data['cable_number']);
                $product_data['cbct'] = trim($data['cbct']);
                $product_data['mbb_run'] = trim($data['mbb_run']);
                $product_data['feeder_run'] = trim($data['feeder_run']);
                $product_data['feeder_size'] = trim($data['feeder_size']);
                $product_data['short_text'] = trim($data['short_text']);
                $product_data['remarks'] = trim($data['remarks']);
                $product_data['user'] = trim($data['user']);
                $product_data['created_at'] = trim($data['created_at']);
                $product_data['updated_at'] = trim($data['updated_at']);
                $product_info["data"][] = $product_data;
            }
        }else {
            $product_info["data"] = array();
        }
         //SharedManager::print($product_info);
         //  exit();
        echo json_encode($product_info, JSON_THROW_ON_ERROR);
        exit;
    }
    
    public function listNum($openCloseState = null)
        {
            $sql = "SELECT id, prdName, drwName, matName, mainNumber, startNumber, endNumber 
                    FROM tbl_drawing_number 
                    ORDER BY id DESC";

            // Execute and fetch result using the same pattern as listDto
            $res = DbManager::fetchPDOQueryData('spectra_db', $sql)["data"];

            $cnt = count($res);
            $product_info = array();

            if ($cnt > 0) {
                $sr = 1;
                foreach ($res as $data) {
                    $product_data['id'] = $sr++;
                    $product_data['prdName'] = trim($data['prdName']);
                    $product_data['drwName'] = trim($data['drwName']);
                    $product_data['matName'] = trim($data['matName']);
                    $product_data['mainNumber'] = trim($data['mainNumber']);
                    $product_data['startNumber'] = trim($data['startNumber']);
                    $product_data['endNumber'] = trim($data['endNumber']);

                    $product_info["data"][] = $product_data;
                }
            } else {
                $product_info["data"] = array();
            }

            echo json_encode($product_info, JSON_THROW_ON_ERROR);
            exit;
        }



    public function getDtoData($breaker_id = "", $return = "")
{
    if (!empty($breaker_id)) {
        $id = $breaker_id;
    } else {
        $id = $_GET["id"];
    }

    $query = "SELECT b.* FROM order_db a JOIN order_release b ON a.docNo = b.docNo2 WHERE a.docNo = :p1 ORDER BY b.id DESC";
    $response = DbManager::fetchPDOQuery('spectra_db', $query, [":p1" => $id])["data"];

    // If no matching records, return an empty dataset with docNo2 only
    if (empty($response)) {
        echo json_encode([
            [
                "docNo2" => $id,
                "matNo" => "",
                "dMat" => "",
                "quanTt" => "",
                "descRp" => "",
                "kMat" => ""
            ]
        ]);
    } else {
        echo json_encode($response);
    }

    exit;
}
    // public function generateDocNo()
    // {
    //     $prefix = "DTO_S_";
    //     $start_number = 202;

    //     //$query = "SELECT docNo FROM order_db ORDER BY docNo DESC LIMIT 1";
    //       $query = "SELECT docNo FROM order_db ORDER BY CAST(SUBSTRING_INDEX(docNo, '_', -1) AS UNSIGNED) DESC LIMIT 1";

    //     $response = DbManager::fetchPDOQuery('spectra_db', $query)["data"];
    //     // SharedManager::print($response);
    //     // exit();
    //     // Step 1: Check if there is any docNo returned
    //     if (empty($response)) {
    //         // If no docNo exists, start with the initial number
    //         $new_number = $start_number;
    //     } else {
    //         // Step 2: Extract the latest docNo
    //         $latest_doc = $response[0]['docNo'];  // Assuming the first result is the latest one

    //         // Step 3: Extract the numeric part using regex
    //         preg_match('/\d+/', $latest_doc, $matches);
            
    //         if (isset($matches[0])) {
    //             // Increment the number by 1
    //              $new_number = intval($matches[0]) + 1;
    //         } else {
    //             // If no numeric part exists (which shouldn't happen), use the starting number
    //           $new_number = $start_number;
    //         }
    //     }

    //     // Step 4: Format new docNo with leading zeros (ensuring at least 4 digits)
    //     $new_doc_no = $prefix . str_pad($new_number, 3, '0', STR_PAD_LEFT);

    //     // Output or use the new docNo
    //     echo json_encode(['docNo' => $new_doc_no]);
    // }

public function generateDocNo()
{
    $stageName = isset($_GET['stageName']) ? $_GET['stageName'] : 'Execution Stage';
    
    if ($stageName === 'Offer Stage') {
        $prefix = "DTO_O_";
        $start_number = 1;
    } else {
        $prefix = "DTO_S_";
        $start_number = 202;
    }

    $query = "SELECT docNo FROM order_db WHERE docNo LIKE ? ORDER BY CAST(SUBSTRING_INDEX(docNo, '_', -1) AS UNSIGNED) DESC LIMIT 1";

    $prefixPattern = $prefix . '%';
    $response = DbManager::fetchPDOQuery('spectra_db', $query, [$prefixPattern])["data"];
    
    if (empty($response)) {
        $new_number = $start_number;
    } else {
        $latest_doc = $response[0]['docNo'];
        preg_match('/\d+/', $latest_doc, $matches);
        
        if (isset($matches[0])) {
            $new_number = intval($matches[0]) + 1;
        } else {
            $new_number = $start_number;
        }
    }

    $new_doc_no = $prefix . str_pad($new_number, 3, '0', STR_PAD_LEFT);
    echo json_encode(['docNo' => $new_doc_no]);
}

    public function getImage($docNo = "", $return = "")
{
    // If docNo is provided, use it; otherwise, get it from the GET request
    if (!empty($docNo)) {
        $docNo = $docNo;
    } else {
        $docNo = $_GET["docNo"];  // Get docNo from the GET request
    }

    // Check if docNo is provided, if not, return an error
    if (empty($docNo)) {
        returnHttpResponse(400, "docNo is required");
    }

    // Query to fetch the image path from the order_db table based on docNo
    $query = "SELECT exampleInputFile FROM order_db WHERE docNo = :p1 LIMIT 1";

    // Fetch the image path from the database
    $response = DbManager::fetchPDOQuery('spectra_db', $query, [":p1" => $docNo])["data"];

    // Check if the image path exists in the response
    if (!empty($response)) {
        // Assuming the image path is in the 'exampleInputFile' field
        $imagePath = $response[0]['exampleInputFile'];

        // Construct the image URL, adjust as needed to match your server configuration
        // Assuming the image is stored in the '/images' directory on your server
        $imageUrl = "/images/DTO/" . $imagePath;

        // Return the image URL as a JSON response
        echo json_encode(['imageUrl' => $imageUrl]);
    } else {
        // If no image is found, return null
        echo json_encode(['imageUrl' => null]);
    }

    exit;
}

public function getDocs($docNo = "", $return = "")
{
    // If docNo is not passed, get it from the GET request
    if (empty($docNo)) {
        $docNo = $_GET["docNo"] ?? null;
    }

    if (empty($docNo)) {
        returnHttpResponse(400, "docNo is required");
    }

    // SQL query to fetch all file names for the given docNo
    $query = "SELECT file_name FROM order_add_doc WHERE docNo = :p1";

    // Fetch the file names from the database
    $response = DbManager::fetchPDOQuery('spectra_db', $query, [":p1" => $docNo])["data"];

    // Check if files are returned
    if (!empty($response)) {
        // Construct array of file info
        $files = array_map(function($row) {
            return [
                'name' => $row['file_name'],
                'type' => 'file',
                'url'  => "/images/DTO/ADD/" . $row['file_name']  // adjust path as needed
            ];
        }, $response);

        // Return files as JSON
        echo json_encode(['files' => $files]);
    } else {
        echo json_encode(['files' => []]); // return empty list
    }

    exit;
}



public function getUpdateData($breaker_id = "", $return = "")
    {
        if (!empty($breaker_id)) {
            $id = $breaker_id;
        } else {
            $id = $_GET["id"];
        }
        // SharedManager::print($id);
        // exit();
        // if(!is_numeric($id))
        //     returnHttpResponse(400, "invalid id");

        //$query = "SELECT * FROM order_release WHERE id = :p1";
         $query = "SELECT * FROM order_db WHERE id = :p1 ORDER BY id DESC";
        $response = DbManager::fetchPDOQuery('spectra_db', $query, [":p1" => $id])["data"];
        
            echo json_encode($response);
        
        exit;
    }

    // Add this method to your DTOController.php class

public function updateMatRegister()
{
    try {
        // Get form data
        $id = $_POST['id'] ?? null;
        
        if (!$id) {
            throw new Exception("Material ID is missing");
        }
        
        // Prepare fields for update
        $fields = [
            'product_name', 'drawing_name', 'drawing_number', 'material_type', 
            'ka_rating', 'width', 'description', 'thickness', 'rear_box', 
            'end_cover_location', 'ebb_cutout', 'ebb_size', 'cable_entry', 
            'gp_thickness', 'gp_material', 'interlock', 'ir_window', 'nameplate', 
            'viewing_window', 'lhs_panel_rb', 'rhs_panel_rb', 'rear_box_type', 
            'ct_type', 'cable_number', 'cbct', 'panel_width', 'feeder_bar_size', 
            'mbb_size', 'sizeofbusbar', 'material', 'ag_plating', 'mbb_run', 
            'feeder_run', 'feeder_size', 'short_text', 'remarks', 'user'
        ];
        
        // Build SQL update query
        $sql = "UPDATE tbl_material_registration SET ";
        $params = [':id' => $id];
        
        $updateParts = [];
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                $updateParts[] = "$field = :$field";
                $params[":$field"] = $_POST[$field];
            }
        }
        
        // Add updated_at timestamp
        $updateParts[] = "updated_at = NOW()";
        
        // Complete the SQL query
        $sql .= implode(', ', $updateParts) . " WHERE id = :id";
        
        // Execute the update query
        DbManager::fetchPDOQuery('spectra_db', $sql, $params);
        
        // Return success response
        echo json_encode([
            "message" => "Successfully Updated",
            "code" => 200
        ], JSON_PRETTY_PRINT);
        
    } catch (Exception $e) {
        // Return error response
        http_response_code(500);
        echo json_encode([
            "message" => "Error: " . $e->getMessage(),
            "code" => 500
        ], JSON_PRETTY_PRINT);
    }
    exit;
}

    public function getUpdateMatRegister($id = "", $return = "")
    {
        
        if (!empty($id)) {
            $id = $id;
        } else {
            $id = $_GET["id"];
        }
        // SharedManager::print($id);
        // exit();
        
         $query = "SELECT * FROM tbl_material_registration WHERE id = :p1 ORDER BY id DESC";
        $response = DbManager::fetchPDOQuery('spectra_db', $query, [":p1" => $id])["data"];
        
            echo json_encode($response);
        
        exit;
    }

   public function getUpdateNumberData($id = "", $return = "")
    {
        if (!empty($id)) {
            $id = $id;
        } else {
            $id = $_GET["id"];
        }
        // SharedManager::print($id);
        // exit();
        // if(!is_numeric($id))
        //     returnHttpResponse(400, "invalid id");

        //$query = "SELECT * FROM order_release WHERE id = :p1";
         $query = "SELECT * FROM tbl_drawing_number WHERE id = :p1 ORDER BY id DESC";
         //$query = "SELECT * FROM tbl_drawing_number WHERE id = :p1";
        $response = DbManager::fetchPDOQuery('spectra_db', $query, [":p1" => $id])["data"];
        
            echo json_encode($response);
        
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

    public function getDataDto($id = "", $return = "")
    {
        if (!empty($id)) {
            $id = $id;
        } else {
            $id = $_GET["id"];
        }
        if(!is_numeric($id))
            returnHttpResponse(400, "invalid id");

        //$query = "SELECT  b.* FROM order_db AS a INNER JOIN order_release AS b ON a.docNo = b.docNo2 Where a.docNo=:id";
        $query = "SELECT * from order_release where docNo = :p1";
        $response = DbManager::fetchPDOQuery('spectra_db', $query, [":p1" => $id])["data"];
        if (!empty($return)) {
            return $response[0];
        } else {
            echo json_encode($response[0]);
        }
        exit;
    }

    // public function update()
    // {
    //     $error = 0;
    //     $productName = $_POST['productName'];
    //     $iacRating = $_POST['iacRating'];
    //     $docNo = $_POST['docNo'];
    //     $shortDescription = $_POST['shortDescription'];
    //     $woundCTs = $_POST['woundCTs'];
    //     $windowCTs = $_POST['windowCTs'];
    //     $cablesBus = $_POST['cablesBus'];
    //     $cabCore = $_POST['cabCore'];
    //     $cabEntry = $_POST['cabEntry'];
    //     $ratedVol = $_POST['ratedVol'];
    //     $ratedCir = $_POST['ratedCir'];
    //     $ratedCurrent = $_POST['ratedCurrent'];
    //     $width = $_POST['width'];
    //     $rearBoxDepth = $_POST['rearBoxDepth'];
    //     $feederMat = $_POST['feederMat'];
    //     $realBy = $_POST['realBy'];
    //     $info = $_POST['info'];
    //     $orderNo = $_POST['orderNo'];
    //     $DrawNo = $_POST['DrawNo'];
    //     $eartSwitch = $_POST['eartSwitch'];
    //     $doVt = $_POST['doVt'];
    //     $toolSel = $_POST['toolSel'];
    //     $addOn = $_POST['addOn'];
    //     $solenoid = $_POST['solenoid'];
    //     $limSwi = $_POST['limSwi'];
    //     $meshAss = $_POST['meshAss'];
    //     $lampRearCover = $_POST['lampRearCover'];
    //     $glandPlate = $_POST['glandPlate'];
    //     $rearCover = $_POST['rearCover'];

    //     if ($error == 0) {
    //         $sqlA = "UPDATE order_db SET productName = :productName,iacRating = :iacRating,docNo = :docNo,shortDescription = :shortDescription,woundCTs = :woundCTs,windowCTs = :windowCTs,
    //                  cablesBus = :cablesBus,cabCore = :cabCore,cabEntry = :cabEntry,ratedVol = :ratedVol,ratedCir = :ratedCir,ratedCurrent = :ratedCurrent,width = :width,rearBoxDepth = :rearBoxDepth,
    //                  feederMat = :feederMat,realBy = :realBy,info = :info,DrawNo = :DrawNo,eartSwitch = :eartSwitch,doVt = :doVt,toolSel = :toolSel,addOn = :addOn,solenoid = :solenoid,
    //                  limSwi = :limSwi, meshAss = :meshAss,lampRearCover = :lampRearCover,glandPlate = :glandPlate, rearCover = :rearCover WHERE docNo = :docNo";

        
    //             DbManager::fetchPDOQuery('spectra_db', $sqlA, [
                
    //             ":productName" => $productName,
    //             ":iacRating" => $iacRating,
    //             ":docNo" => $docNo,
    //             ":shortDescription" => $shortDescription,
    //             ":woundCTs" => $woundCTs,
    //             ":windowCTs" => $windowCTs,
    //             ":cablesBus" => $cablesBus,
    //             ":cabCore" => $cabCore,
    //             ":cabEntry" => $cabEntry,
    //             ":ratedVol" => $ratedVol,
    //             ":ratedCir" => $ratedCir,
    //             ":ratedCurrent" => $ratedCurrent,
    //             ":width" => $width,
    //             ":rearBoxDepth" => $rearBoxDepth,
    //             ":feederMat" => $feederMat,
    //             ":realBy" => $realBy,
    //             ":info" => $info,
    //             ":orderNo" => $orderNo,
    //             ":DrawNo" => $DrawNo,
    //             ":eartSwitch" => $eartSwitch,
    //             ":doVt" => $doVt,
    //             ":toolSel" => $toolSel,
    //             ":exampleInputFile" => isset($image_name) ? $image_name : '',
    //             ":addOn" => $addOn,
    //             ":solenoid" => $solenoid,
    //             ":limSwi" => $limSwi,
    //             ":meshAss" => $meshAss,
    //             ":lampRearCover" => $lampRearCover,
    //             ":glandPlate" => $glandPlate,
    //             ":rearCover" => $rearCover
        
    //         ]);
    //         // SharedManager::print($_POST);
    //         //  exit('aaaaaaaaaaaaaaaaa');
            
            
    //         //returnHttpResponse(200, "Registration Done Successfully");
    //     }

       
    //     echo json_encode(["message" => "Successfully", "code" => 200], JSON_PRETTY_PRINT);
    //     exit;
    // }
public function update()
{
    try {
        // Get POST data
        $fields = [
            'stageName','productName', 'iacRating', 'docNo', 'shortDescription', 'woundCTs', 'windowCTs',
            'cablesBus', 'cabCore', 'cabEntry', 'ratedVol', 'ratedCir', 'ratedCurrent',
            'width', 'rearBoxDepth', 'feederMat', 'realBy', 'info', 'orderNo', 'DrawNo',
            'eartSwitch', 'doVt', 'toolSel', 'addOn', 'solenoid', 'limSwi', 'meshAss',
            'lampRearCover', 'glandPlate', 'rearCover','DispUser'
        ];

        $data = [];
        foreach ($fields as $field) {
            $data[$field] = $_POST[$field] ?? null;
        }

        // SQL Update Query
        $sql = "UPDATE order_db SET 
                    stageName = :stageName,
                    productName = :productName,
                    iacRating = :iacRating,
                    shortDescription = :shortDescription,
                    woundCTs = :woundCTs,
                    windowCTs = :windowCTs,
                    cablesBus = :cablesBus,
                    cabCore = :cabCore,
                    cabEntry = :cabEntry,
                    ratedVol = :ratedVol,
                    ratedCir = :ratedCir,
                    ratedCurrent = :ratedCurrent,
                    width = :width,
                    rearBoxDepth = :rearBoxDepth,
                    feederMat = :feederMat,
                    realBy = :realBy,
                    info = :info,
                    orderNo = :orderNo,
                    DrawNo = :DrawNo,
                    eartSwitch = :eartSwitch,
                    doVt = :doVt,
                    toolSel = :toolSel,
                    addOn = :addOn,
                    solenoid = :solenoid,
                    limSwi = :limSwi,
                    meshAss = :meshAss,
                    lampRearCover = :lampRearCover,
                    glandPlate = :glandPlate,
                    rearCover = :rearCover,
                    DispUser = :DispUser
                WHERE docNo = :docNo";

        // Execute update
        $params = array_combine(
    array_map(fn($f) => ":$f", array_keys($data)),
    $data
);

$stmt = DbManager::fetchPDOQuery('spectra_db', $sql, $params);

        echo json_encode(["message" => "Successfully Updated", "code" => 200], JSON_PRETTY_PRINT);
    } catch (Exception $e) {
        echo json_encode(["message" => "Error: " . $e->getMessage(), "code" => 500], JSON_PRETTY_PRINT);
    }
    exit;
}

public function updateNumber()
{
    try {
        // Get POST data
        $fields = [
            'id','prdName', 'drwName', 'matName', 'mainNumber', 'startNumber',
            'endNumber'
        ];

        $data = [];
        foreach ($fields as $field) {
            $data[$field] = $_POST[$field] ?? null;
        }

        // SQL Update Query
        $sql = "UPDATE tbl_drawing_number SET 
                    prdName = :prdName,
                    drwName = :drwName,
                    matName = :matName,
                    mainNumber = :mainNumber,
                    startNumber = :startNumber,
                    endNumber = :endNumber
                WHERE id = :id";

        // Execute update
        DbManager::fetchPDOQuery('spectra_db', $sql, array_combine(
            array_map(fn($f) => ":$f", array_keys($data)),
            $data
        ));

        echo json_encode(["message" => "Successfully Updated", "code" => 200], JSON_PRETTY_PRINT);
    } catch (Exception $e) {
        echo json_encode(["message" => "Error: " . $e->getMessage(), "code" => 500], JSON_PRETTY_PRINT);
    }
    exit;
}

public function updateOffer()
{
    try {
        $offerData = json_decode($_POST['offerData'], true);

        if (empty($offerData)) {
            throw new Exception("No offer data provided.");
        }

        // Get docNo2 from the first row (assuming all have the same docNo2)
        $docNo2 = $offerData[0]['docNo2'] ?? null;

        if (!$docNo2) {
            throw new Exception("Document number is missing.");
        }

       
        DbManager::fetchPDOQuery('spectra_db',
            "DELETE FROM order_release WHERE docNo2 = :docNo2",
            [':docNo2' => $docNo2]
        );

      
        $sql = "INSERT INTO order_release (docNo2, matNo, dMat, quanTt, descRp, kMat)
                VALUES (:docNo2, :matNo, :dMat, :quanTt, :descRp, :kMat)";

        foreach ($offerData as $data) {
            DbManager::fetchPDOQuery('spectra_db', $sql, [
                ':docNo2' => $data['docNo2'],
                ':matNo' => $data['matNo'],
                ':dMat' => $data['dMat'],
                ':quanTt' => $data['quanTt'],
                ':descRp' => $data['descRp'],
                ':kMat' => $data['kMat'],
            ]);
        }

        echo json_encode(["message" => "Successfully updated all rows", "code" => 200], JSON_PRETTY_PRINT);
    } catch (Exception $e) {
        echo json_encode(["message" => "Error: " . $e->getMessage(), "code" => 500], JSON_PRETTY_PRINT);
    }
    exit;
}

public function updateImage()
{
    try {
        $docNo = $_POST['docNo'] ?? null;

        if (!$docNo) {
            throw new Exception("Document number (docNo) is missing.");
        }

        if (isset($_FILES['exampleInputFile']) && $_FILES['exampleInputFile']['error'] === 0) {
            $image_name = basename($_FILES['exampleInputFile']['name']);
            $image_tmp_name = $_FILES['exampleInputFile']['tmp_name'];
            $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/images/DTO/';
            $target_file = $upload_dir . $image_name;

            $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
            $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            if (!in_array($file_type, $allowed_types)) {
                throw new Exception("Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.");
            }

            // Check if an image already exists for this docNo
            $existingImage = $this->getImageByDocNo($docNo);

            if ($existingImage && !empty($existingImage['exampleInputFile'])) {
                $oldFilePath = $upload_dir . $existingImage['exampleInputFile'];
                if (file_exists($oldFilePath)) {
                    unlink($oldFilePath); // Delete old image
                }
            }
            $this->updateImageInDB($docNo, $image_name); // Update with new file name

            // Move the uploaded file to the target directory
            if (move_uploaded_file($image_tmp_name, $target_file)) {
                echo json_encode(["message" => "Image uploaded and database updated.", "code" => 200]);
            } else {
                throw new Exception("Failed to move uploaded file.");
            }

        } else {
            throw new Exception("No valid file uploaded.");
        }

    } catch (Exception $e) {
        echo json_encode(["message" => "Error: " . $e->getMessage(), "code" => 500]);
    }

    exit;
}

private function getImageByDocNo($docNo)
{
    $query = "SELECT exampleInputFile FROM order_db WHERE docNo = :docNo LIMIT 1";
    $result = DbManager::fetchPDOQuery('spectra_db', $query, [':docNo' => $docNo]);
    return $result['data'][0] ?? null;
}

private function updateImageInDB($docNo, $fileName)
{
    $query = "UPDATE order_db SET exampleInputFile = :fileName WHERE docNo = :docNo";
    DbManager::fetchPDOQuery('spectra_db', $query, [
        ':fileName' => $fileName,
        ':docNo' => $docNo
    ]);
}

private function insertImageInDB($docNo, $fileName)
{
    $query = "INSERT INTO order_db (docNo, exampleInputFile) VALUES (:docNo, :fileName)";
    DbManager::fetchPDOQuery('spectra_db', $query, [
        ':docNo' => $docNo,
        ':fileName' => $fileName
    ]);
}

public function generateOrderDrawingNo() {
    try {
        $stageName = $_POST['stage_name'] ?? '';
        $productName = $_POST['product_name'] ?? '';

        error_log("Received Stage Name: " . $stageName); // Debug log

        // Validate inputs
        if (empty($stageName) || empty($productName)) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing required parameters"
            ]);
            return;
        }

        // Properly determine if it's Execution or Offer
        $isExecution = (strpos($stageName, 'Execution') !== false);
        $startRange = $isExecution ? 1 : 9999;
        $endRange = $isExecution ? 10000 : 19999;

        error_log("Stage Type: " . ($isExecution ? 'Execution' : 'Offer')); // Debug log
        error_log("Range: " . $startRange . " to " . $endRange); // Debug log

        // Get the last used drawing number for this stage type
        $usedSql = "SELECT DrawNo 
                    FROM order_db 
                    WHERE DrawNo LIKE '122_%_9'
                    AND DrawNo REGEXP '^122_[0-9]{4}_9$'
                    AND CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(DrawNo, '_', 2), '_', -1) AS UNSIGNED) 
                    BETWEEN :start_range AND :end_range
                    ORDER BY CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(DrawNo, '_', 2), '_', -1) AS UNSIGNED) DESC 
                    LIMIT 1";

        $usedParams = [
            ':start_range' => $startRange,
            ':end_range' => $endRange
        ];

        error_log("SQL Query: " . $usedSql);
        error_log("Parameters: " . json_encode($usedParams));

        $usedResult = DbManager::fetchPDOQuery('spectra_db', $usedSql, $usedParams);

        // Determine next sequence number
        if (empty($usedResult['data'])) {
            // No numbers used yet in this range, start with range start
            $nextSequence = $startRange;
            error_log("No existing numbers found, starting with: " . $nextSequence);
        } else {
            $lastUsed = $usedResult['data'][0]['DrawNo'];
            error_log("Last used number: " . $lastUsed);
            
            $parts = explode('_', $lastUsed);
            if (count($parts) === 3) {
                $sequence = (int)$parts[1];
                $nextSequence = $sequence + 1;
                
                // Check if we've reached the end of the range
                if ($nextSequence > $endRange) {
                    echo json_encode([
                        "status" => "error",
                        "message" => ($isExecution ? "Execution" : "Offer") . 
                                   " number range (" . $startRange . "-" . $endRange . ") is exhausted"
                    ]);
                    return;
                }
            } else {
                $nextSequence = $startRange;
            }
        }

        error_log("Next sequence number: " . $nextSequence);

        $nextNumber = "122_" . sprintf("%04d", $nextSequence) . "_9";

        // Double check for duplicates
        $checkSql = "SELECT COUNT(*) as count 
                    FROM order_db 
                    WHERE DrawNo = :drawing_no";

        $checkResult = DbManager::fetchPDOQuery('spectra_db', $checkSql, [
            ':drawing_no' => $nextNumber
        ]);

        if ($checkResult['data'][0]['count'] > 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Generated number already exists. Please try again."
            ]);
            return;
        }

        $response = [
            "status" => "success",
            "drawing_number" => $nextNumber,
            "message" => "Drawing number generated successfully"
        ];

        error_log("Generated drawing number: " . $nextNumber);
        
        header('Content-Type: application/json');
        echo json_encode($response);

    } catch (Exception $e) {
        error_log("Error in generateOrderDrawingNo: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode([
            "status" => "error",
            "message" => "Error generating drawing number: " . $e->getMessage()
        ]);
    }
}
 // SharedManager::print($_POST);
  // SharedManager::print($_POST);
public function generateDrwNo()
{
    try {

        $productName  = $_POST['product_name'] ?? '';
        $drawingName  = $_POST['drawing_name'] ?? '';
        $materialType = $_POST['material_type'] ?? '';
        $material     = $_POST['material'] ?? '';

        if (!$productName || $drawingName === '' || !$materialType) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing required parameters"
            ]);
            return;
        }

        // Normalize material type for lookup
        $lookupMaterialType = $materialType;
        if ($materialType === 'busbar') {
            $lookupMaterialType = 'busbar_' . strtolower($material);
        }

        // Get ALL ranges for this configuration
        $configSql = "
            SELECT mainNumber, startNumber, endNumber
            FROM tbl_drawing_number
            WHERE prdName = :prdName
            AND drwName = :drwName
            AND matName = :matName
            ORDER BY startNumber ASC
        ";

        $config = DbManager::fetchPDOQuery(
            'spectra_db',
            $configSql,
            [
                ':prdName' => $productName,
                ':drwName' => $drawingName,
                ':matName' => $lookupMaterialType
            ]
        );

        if (empty($config['data'])) {
            echo json_encode([
                "status" => "error",
                "message" => "No configuration found"
            ]);
            return;
        }

        // Get already used drawing numbers
        $usedSql = "
            SELECT drawing_number
            FROM tbl_material_registration
            WHERE product_name = :prdName
            AND drawing_name = :drwName
            AND material_type = :matType
        ";

        $params = [
            ':prdName' => $productName,
            ':drwName' => $drawingName,
            ':matType' => $materialType
        ];

        if ($materialType === 'busbar') {
            $usedSql .= " AND material = :material";
            $params[':material'] = $material;
        }

        $used = DbManager::fetchPDOQuery('spectra_db', $usedSql, $params);
        $usedNumbers = $used['data'] ?? [];

        $nextSeq = null;
        $main = null;
        $start = null;
        $end = null;

        // Loop through all ranges
        foreach ($config['data'] as $cfg) {

            $main  = (int)$cfg['mainNumber'];
            $start = (int)$cfg['startNumber'];
            $end   = (int)$cfg['endNumber'];

            $highestUsed = $start - 1;

            foreach ($usedNumbers as $row) {

                $parts = explode('_', $row['drawing_number']);

                if (
                    count($parts) === 3 &&
                    (int)$parts[0] === $main &&
                    (int)$parts[2] === (int)$drawingName
                ) {
                    $seq = (int)$parts[1];

                    if ($seq >= $start && $seq <= $end) {
                        $highestUsed = max($highestUsed, $seq);
                    }
                }
            }

            $candidate = $highestUsed + 1;

            if ($candidate <= $end) {
                $nextSeq = $candidate;
                break;
            }
        }

        if ($nextSeq === null) {
            echo json_encode([
                "status" => "error",
                "message" => "Number range exhausted"
            ]);
            return;
        }

        $drawingNumber = sprintf(
            "%d_%04d_%d",
            $main,
            $nextSeq,
            $drawingName
        );

        // Remaining numbers in this range
        $remainingNumbers = $end - $nextSeq;

        echo json_encode([
            "status" => "success",
            "drawing_number" => $drawingNumber,
            "remaining_numbers" => $remainingNumbers
        ]);

    } catch (Exception $e) {
        echo json_encode([
            "status" => "error",
            "message" => $e->getMessage()
        ]);
    }
}



// public function generateDrwNo()
// {
//     try {

//         $productName  = $_POST['product_name'] ?? '';
//         $drawingName  = $_POST['drawing_name'] ?? ''; // 0 or 3
//         $materialType = $_POST['material_type'] ?? '';
//         $material     = $_POST['material'] ?? '';

//         if (!$productName || $drawingName === '' || !$materialType) {
//             echo json_encode([
//                 "status" => "error",
//                 "message" => "Missing required parameters"
//             ]);
//             return;
//         }

//         // Normalize material type for lookup
//         $lookupMaterialType = $materialType;
//         if ($materialType === 'busbar') {
//             $lookupMaterialType = 'busbar_' . strtolower($material);
//         }

//         $configSql = "
//             SELECT mainNumber, startNumber, endNumber
//             FROM tbl_drawing_number
//             WHERE prdName = :prdName
//             AND drwName = :drwName
//             AND matName = :matName
//             LIMIT 1
//         ";

//         $config = DbManager::fetchPDOQuery(
//             'spectra_db',
//             $configSql,
//             [
//                 ':prdName' => $productName,
//                 ':drwName' => $drawingName,
//                 ':matName' => $lookupMaterialType
//             ]
//         );

//         if (empty($config['data'])) {
//             echo json_encode([
//                 "status" => "error",
//                 "message" => "No configuration found"
//             ]);
//             return;
//         }

//         $main  = (int)$config['data'][0]['mainNumber'];
//         $start = (int)$config['data'][0]['startNumber'];
//         $end   = (int)$config['data'][0]['endNumber'];

//         $usedSql = "
//             SELECT drawing_number
//             FROM tbl_material_registration
//             WHERE product_name = :prdName
//             AND drawing_name = :drwName
//             AND material_type = :matType
//         ";

//         $params = [
//             ':prdName' => $productName,
//             ':drwName' => $drawingName,
//             ':matType' => $materialType
//         ];
//         if ($materialType === 'busbar') {
//             $usedSql .= " AND material = :material";
//             $params[':material'] = $material;
//         }

//         $used = DbManager::fetchPDOQuery('spectra_db', $usedSql, $params);
//         $usedNumbers = $used['data'] ?? [];

//         $highestUsed = $start - 1;

//         foreach ($usedNumbers as $row) {

//             $parts = explode('_', $row['drawing_number']);

            
//             if (
//                 count($parts) === 3 &&
//                 (int)$parts[0] === $main &&
//                 (int)$parts[2] === (int)$drawingName
//             ) {
//                 $seq = (int)$parts[1];
//                 if ($seq >= $start && $seq <= $end) {
//                     $highestUsed = max($highestUsed, $seq);
//                 }
//             }
//         }

//         $nextSeq = $highestUsed + 1;

//         if ($nextSeq > $end) {
//             echo json_encode([
//                 "status" => "error",
//                 "message" => "Number range exhausted"
//             ]);
//             return;
//         }

        
//         $drawingNumber = sprintf(
//             "%d_%04d_%d",
//             $main,
//             $nextSeq,
//             $drawingName
//         );

//         // ========== NEW CODE START ==========
//         // Calculate remaining numbers
//         $remainingNumbers = $end - $nextSeq;
//         // ========== NEW CODE END ==========

//         echo json_encode([
//             "status" => "success",
//             "drawing_number" => $drawingNumber,
//             // ========== NEW CODE START ==========
//             "remaining_numbers" => $remainingNumbers
//             // ========== NEW CODE END ==========
//         ]);

//     } catch (Exception $e) {
//         echo json_encode([
//             "status" => "error",
//             "message" => $e->getMessage()
//         ]);
//     }
// }

// public function generateDrwNo() {
//     try {
//         $productName = $_POST['product_name'] ?? '';
//         $drawingName = $_POST['drawing_name'] ?? '';
//         $materialType = $_POST['material_type'] ?? '';
//         $material = $_POST['material'] ?? '';

//         // Validate inputs
//         if (empty($productName) || empty($drawingName) || empty($materialType)) {
//             echo json_encode([
//                 "status" => "error",
//                 "message" => "Missing required parameters"
//             ]);
//             return;
//         }

//         // Create lookup material type
//         $lookupMaterialType = $materialType;
//         if ($materialType === 'busbar') {
//             $lookupMaterialType = "busbar_" . strtolower($material);
//         }

//         // Get configuration for this specific material type
//         $configSql = "SELECT startNumber, endNumber, mainNumber 
//                      FROM tbl_drawing_number 
//                      WHERE prdName = :product_name 
//                      AND drwName = :drawing_name
//                      AND matName = :material_type
//                      LIMIT 1";

//         $configParams = [
//             ':product_name' => $productName,
//             ':drawing_name' => $drawingName,
//             ':material_type' => $lookupMaterialType
//         ];

//         error_log("Config Query Params: " . json_encode($configParams)); // Debug log

//         $config = DbManager::fetchPDOQuery('spectra_db', $configSql, $configParams);

//         if (empty($config['data'])) {
//             echo json_encode([
//                 "status" => "error",
//                 "message" => "No configuration found for " . $lookupMaterialType
//             ]);
//             return;
//         }

//         $main = (int) $config['data'][0]['mainNumber'];
//         $start = (int) $config['data'][0]['startNumber'];
//         $end = (int) $config['data'][0]['endNumber'];

//         // Get used numbers for this specific material type
//         $usedSql = "SELECT drawing_number 
//                     FROM tbl_material_registration 
//                     WHERE product_name = :product_name 
//                     AND drawing_name = :drawing_name 
//                     AND material_type = :material_type";

//         $usedParams = [
//             ':product_name' => $productName,
//             ':drawing_name' => $drawingName,
//             ':material_type' => $materialType
//         ];

//         // Add material parameter for busbar
//         if ($materialType === 'busbar') {
//             $usedSql .= " AND material = :material";
//             $usedParams[':material'] = $material;
//         }

//         error_log("Used Numbers Query Params: " . json_encode($usedParams)); // Debug log

//         $usedResult = DbManager::fetchPDOQuery('spectra_db', $usedSql, $usedParams);
//         $usedNumbers = !empty($usedResult['data']) ? array_column($usedResult['data'], 'drawing_number') : [];

//         error_log("Used Numbers: " . json_encode($usedNumbers)); // Debug log

//         // Get drawing name number
//         $drawingNameNumber = preg_replace('/[^0-9]/', '', $drawingName);

//         // Find next available sequence
//         if (empty($usedNumbers)) {
//             // No numbers used yet, start from beginning
//             $nextSequence = $start;
//         } else {
//             // Find highest used sequence
//             $highestUsed = $start - 1; // Initialize to one less than start
//             foreach ($usedNumbers as $used) {
//                 $parts = explode('_', $used);
//                 if (count($parts) === 3 && (int)$parts[0] === $main) {
//                     $sequence = (int)$parts[1];
//                     if ($sequence >= $start && $sequence <= $end) {
//                         $highestUsed = max($highestUsed, $sequence);
//                     }
//                 }
//             }
//             $nextSequence = $highestUsed + 1;
//         }

//         error_log("Next Sequence: " . $nextSequence); // Debug log

//         // Check if we've reached the end
//         if ($nextSequence > $end) {
//             echo json_encode([
//                 "status" => "error",
//                 "message" => "Number range for " . $lookupMaterialType . " (" . $start . "-" . $end . ") is exhausted"
//             ]);
//             return;
//         }

//         // Generate the drawing number
//         $nextNumber = $main . "_" . sprintf("%04d", $nextSequence) . "_" . $drawingNameNumber;

//         error_log("Generated Number: " . $nextNumber); // Debug log

//         // Check for duplicates
//         if (in_array($nextNumber, $usedNumbers)) {
//             echo json_encode([
//                 "status" => "error",
//                 "message" => "Generated number already exists"
//             ]);
//             return;
//         }

//         // Return the generated number
//         echo json_encode([
//             "status" => "success",
//             "drawing_number" => $nextNumber,
//             "message" => "Drawing number generated successfully"
//         ]);

//     } catch (Exception $e) {
//         error_log("Error generating drawing number: " . $e->getMessage());
//         echo json_encode([
//             "status" => "error",
//             "message" => "Error generating drawing number: " . $e->getMessage()
//         ]);
//     }
// }
    
    public function deleteBreaker($dto_id = 0)
{
    $breakerEntity = $this->getDtoData($dto_id, "1");

    if (!$breakerEntity) {
        $this->setDeleteBreakerResponseMessage('Data not found. Operation cancelled.');
        return false; // Stop here, don't delete
    }

    $query_delete = "DELETE FROM order_db WHERE id = :id";
    DbManager::fetchPDOQuery('spectra_db', $query_delete, [":id" => $dto_id]);

    return $dto_id;
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
