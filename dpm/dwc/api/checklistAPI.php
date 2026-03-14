<?php
// checklistAPI.php - COMPLETE FILE
require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/api/MToolManager.php";
ini_set('memory_limit', '8192M');
ini_set('max_execution_time', 0);
$type = $_GET["type"] ?? $_POST["type"];
switch ($type) {
    case "insert_line":
        insertLine();
        break;
    case "view_line":
        viewLine();
        break;
    case "update_line":
        updateLine();
        break;
    case "check_line_name":
        checkLineNameExists();
        break;
    case "insert_product":
        insertProduct();
        break;
    case "view_product":
        viewProduct();
        break;
    case "update_product":
        updateProduct();
        break;
    case "check_product_name":
        checkProductNameExists();
        break;
    case "insert_station":
        insertStation();
        break;
    case "view_station":
        viewStation();
        break;
    case "update_station":
        updateStation();
        break;
    case "insert_checklist":
        insertChecklist();
        break;
    case "view_checklist":
        viewChecklist();
        break;
    case "update_checklist":
        updateChecklist();
        break;
    case "check_form_fields":
        checkFormFieldsExist();
        break;
    case "check_checklist_name":
        checkChecklistNameExists();
        break;
    case "get_checklist_names":
        getChecklistNames();
        break;
    case "delete_checklist":
        deleteChecklist();
        break;
    case "load_checklist":
        loadChecklist();
        break;
    case "save_entire_checklist":
        saveChecklistData();
        break;
    case "delete_punch_list_row":
        deletePunchListRow();
        break;
    case "get_checklist_results":
        getChecklistResults();
        break;
    case "get_punch_list_detail":
        getPunchListDetail();
        break;
    case "update_punch_list":
        updatePunchList();
        break;
    case "get_suggestions":
        getSuggestions();
        break;   
    case "upload_images":
        uploadImages();     
        break;
    case "get_images":
        getImages();    
        break;
    case "delete_image":
        deleteImage();    
        break; 
    case "get_all_data":
        exportAll();    
        break;
    case "get_footer_info":
        getFooterInfo();
        break;
    case "get_checklist_details":
        getChecklistDetails();
        break;
    case "get_all_details":
        exportAllDetails();    
        break;
    default:
        break;
}
exit;

function insertLine()
{
    $lineName = $_POST['lineName'];
    $lineType = "panel";
    $sql = "INSERT INTO tbl_line (line_name, line_type) VALUES (:lineName, :lineType)";
    $result = DbManager::fetchPDOQuery('spectra_db', $sql, [':lineName' => $lineName, ':lineType' => $lineType]);
    if ($result) {
        echo 'Line saved successfully';
    } else {
        echo 'Error while saving line';
    }
}

function viewLine()
{
    $page = isset($_POST['page']) ? $_POST['page'] : 1;
    $limit = 10; // Number of lines to display per page
    $offset = ($page - 1) * $limit;

    $sql = "SELECT * FROM tbl_line ORDER BY id DESC LIMIT :limit OFFSET :offset";
    $lines = DbManager::fetchPDOQueryData('spectra_db', $sql, [":limit" => $limit, ":offset" => $offset])["data"];
    if (empty($lines)) {
        ?>
        <div class="text-center">
            <h4>No lines found.</h4>
        </div>
        <?php
    } else {
        ?>
        <table class="table table-striped table-bordered table-hover dataTables-example">
            <tbody>
                <?php foreach ($lines as $line) { ?>
                <tr>
                    <td><?php echo $line['line_name']; ?></td>
                    <td>
                        <button class="btn btn-primary btn-sm" onclick="editLine(<?php echo $line['id']; ?>, '<?php echo $line['line_name']; ?>')">Edit</button>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="text-center">
            <ul class="pagination">
                <?php
                $totalLines = DbManager::fetchPDOQueryData('spectra_db', "SELECT COUNT(*) AS total FROM tbl_line")["data"][0]["total"];
                $totalPages = ceil($totalLines / $limit);

                for ($i = 1; $i <= $totalPages; $i++) {
                    $active = ($i == $page) ? 'active' : '';
                    echo "<li class='page-item $active'><a class='page-link' href='#' onclick='fetchAndDisplayLines($i)'>$i</a></li>";
                }
                ?>
            </ul>
        </div>
        <?php
    }
}

function updateLine()
{
    $lineId = $_POST['lineId'];
    $lineName = $_POST['lineName'];
    $lineType = "panel";
    $sql = "UPDATE tbl_line SET line_name = :lineName, line_type = :lineType WHERE id = :lineId";
    $result = DbManager::fetchPDOQuery('spectra_db', $sql, [':lineName' => $lineName, ':lineType' => $lineType, ':lineId' => $lineId]);
    if ($result) {
        echo 'success';
    } else {
        echo 'Error while updating line';
    }
}

function checkLineNameExists()
{
    $lineName = $_POST['lineName'];
    $lineType = "panel";
    $sql = "SELECT id, line_name FROM tbl_line WHERE line_name = :lineName AND line_type = :lineType";
    $result = DbManager::fetchPDOQueryData('spectra_db', $sql, [':lineName' => $lineName, ':lineType' => $lineType]);
    if (!empty($result['data'])) {
        echo json_encode($result['data'][0]);
    } else {
        echo 'false';
    }
}

function insertProduct()
{
    $productName = $_POST['productName'];
    $productType = "panel";
    $sql = "INSERT INTO tbl_chk_product (product_name, product_type) VALUES (:productName, :productType)";
    $result = DbManager::fetchPDOQuery('spectra_db', $sql, [':productName' => $productName, ':productType' => $productType]);
    if ($result) {
        echo 'Product saved successfully';
    } else {
        echo 'Error while saving product';
    }
}

function viewProduct()
{
    $page = isset($_POST['page']) ? $_POST['page'] : 1;
    $limit = 10; // Number of products to display per page
    $offset = ($page - 1) * $limit;

    $sql = "SELECT * FROM tbl_chk_product ORDER BY id DESC LIMIT :limit OFFSET :offset";
    $products = DbManager::fetchPDOQueryData('spectra_db', $sql, [":limit" => $limit, ":offset" => $offset])["data"];
    if (empty($products)) {
        ?>
        <div class="text-center">
            <h4>No products found.</h4>
        </div>
        <?php
    } else {
        ?>
        <table class="table table-striped table-bordered table-hover dataTables-example">
            <tbody>
                <?php foreach ($products as $product) { ?>
                <tr>
                    <td><?php echo $product['product_name']; ?></td>
                    <td>
                        <button class="btn btn-primary btn-sm" onclick="editProduct(<?php echo $product['id']; ?>, '<?php echo $product['product_name']; ?>')">Edit</button>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="text-center">
            <ul class="pagination">
                <?php
                $totalProducts = DbManager::fetchPDOQueryData('spectra_db', "SELECT COUNT(*) AS total FROM tbl_chk_product")["data"][0]["total"];
                $totalPages = ceil($totalProducts / $limit);

                for ($i = 1; $i <= $totalPages; $i++) {
                    $active = ($i == $page) ? 'active' : '';
                    echo "<li class='page-item $active'><a class='page-link' href='#' onclick='fetchAndDisplayProducts($i)'>$i</a></li>";
                }
                ?>
            </ul>
        </div>
        <?php
    }
}

function updateProduct()
{
    $productId = $_POST['productId'];
    $productName = $_POST['productName'];
    $productType = "panel";
    $sql = "UPDATE tbl_chk_product SET product_name = :productName, product_type = :productType WHERE id = :productId";
    $result = DbManager::fetchPDOQuery('spectra_db', $sql, [':productName' => $productName, ':productType' => $productType, ':productId' => $productId]);
    if ($result) {
        echo 'success';
    } else {
        echo 'Error while updating product';
    }
}

function checkProductNameExists()
{
    $productName = $_POST['productName'];
    $productType = "panel";
    $sql = "SELECT id, product_name FROM tbl_chk_product WHERE product_name = :productName AND product_type = :productType";
    $result = DbManager::fetchPDOQueryData('spectra_db', $sql, [':productName' => $productName, ':productType' => $productType]);
    if (!empty($result['data'])) {
        echo json_encode($result['data'][0]);
    } else {
        echo 'false';
    }
}

function insertStation()
{
    $stationName = $_POST['stationName'];
    $stationType = "panel";
    $sql = "INSERT INTO tbl_chk_station (station_name, station_type) VALUES (:stationName, :stationType)";
    $result = DbManager::fetchPDOQuery('spectra_db', $sql, [':stationName' => $stationName, ':stationType' => $stationType]);
    if ($result) {
        echo 'Station saved successfully';
    } else {
        echo 'Error while saving station';
    }
}

function viewStation()
{
    $page = isset($_POST['page']) ? $_POST['page'] : 1;
    $limit = 10; // Number of stations to display per page
    $offset = ($page - 1) * $limit;

    $sql = "SELECT * FROM tbl_chk_station ORDER BY id DESC LIMIT :limit OFFSET :offset";
    $stations = DbManager::fetchPDOQueryData('spectra_db', $sql, [":limit" => $limit, ":offset" => $offset])["data"];
    if (empty($stations)) {
        ?>
        <div class="text-center">
            <h4>No stations found.</h4>
        </div>
        <?php
    } else {
        ?>
        <table class="table table-striped table-bordered table-hover dataTables-example">
            <tbody>
                <?php foreach ($stations as $station) { ?>
                <tr>
                    <td><?php echo $station['station_name']; ?></td>
                    <td>
                        <button class="btn btn-primary btn-sm" onclick="editStation(<?php echo $station['id']; ?>, '<?php echo $station['station_name']; ?>')">Edit</button>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="text-center">
            <ul class="pagination">
                <?php
                $totalStations = DbManager::fetchPDOQueryData('spectra_db', "SELECT COUNT(*) AS total FROM tbl_chk_station")["data"][0]["total"];
                $totalPages = ceil($totalStations / $limit);

                for ($i = 1; $i <= $totalPages; $i++) {
                    $active = ($i == $page) ? 'active' : '';
                    echo "<li class='page-item $active'><a class='page-link' href='#' onclick='fetchAndDisplayStations($i)'>$i</a></li>";
                }
                ?>
            </ul>
        </div>
        <?php
    }
}

function updateStation()
{
    $stationId = $_POST['stationId'];
    $stationName = $_POST['stationName'];
    $stationType = "panel";
    $sql = "UPDATE tbl_chk_station SET station_name = :stationName, station_type = :stationType WHERE id = :stationId";
    $result = DbManager::fetchPDOQuery('spectra_db', $sql, [':stationName' => $stationName, ':stationType' => $stationType, ':stationId' => $stationId]);
    if ($result) {
        echo 'success';
    } else {
        echo 'Error while updating station';
    }
}

function checkStationNameExists()
{
    $stationName = $_POST['stationName'];
    $stationType = "panel";
    $sql = "SELECT id, station_name FROM tbl_chk_station WHERE station_name = :stationName AND station_type = :stationType";
    $result = DbManager::fetchPDOQueryData('spectra_db', $sql, [':stationName' => $stationName, ':stationType' => $stationType]);
    if (!empty($result['data'])) {
        echo json_encode($result['data'][0]);
    } else {
        echo 'false';
    }
}

function insertChecklist()
{
    $checklistName = $_POST['checklistName'];

    $checkExistingSql = "SELECT checklist_id FROM tbl_chk_checklist WHERE checklist_name = :checklistName LIMIT 1";
    $existingResult = DbManager::fetchPDOQuery('spectra_db', $checkExistingSql, [
        ':checklistName' => $checklistName
    ])["data"];
    
    if ($existingResult && !empty($existingResult)) {
        $checklistId = $existingResult[0]['checklist_id'];
    } else {
        $sqlMaxId = "SELECT MAX(CAST(checklist_id AS UNSIGNED)) as max_id FROM tbl_chk_checklist";
        $maxResult = DbManager::fetchPDOQuery('spectra_db', $sqlMaxId, [])["data"];
        $checklistId = (!$maxResult || $maxResult[0]['max_id'] === null) ? '1' : (string)($maxResult[0]['max_id'] + 1);
    }

    $lineName = $_POST['lineName'];
    $productName = $_POST['productName'];
    $stationName = $_POST['stationName'];
    $documentDescription = isset($_POST['documentDescription']) ? $_POST['documentDescription'] : '';
    $revision = isset($_POST['revisionDate']) ? $_POST['revisionDate'] : '';
    $checklistReferences = $_POST['checklistReferences'];
    $checklistItems = $_POST['checklistItems'];
    $addOns = $_POST['addOns'];
    $checklistType = "panel";
    
    $success = true; // Flag to track if all insertions are successful
    
    // First item will contain the document and revision data
    $firstItemInserted = false;
    
    for ($i = 0; $i < count($checklistItems); $i++) {
        $item = $checklistItems[$i];
        $reference = $checklistReferences[$i];
        $add_on = $addOns[$i];
        
        // For the first item, include document_description and revision
        if (!$firstItemInserted) {
            $sql = "INSERT INTO tbl_chk_checklist (checklist_id, checklist_name, checklist_type, line_id, product_id, station_id, checklist_reference, checklist_item, add_on, document_description, revision) 
            VALUES (:checklistId, :checklistName, :checklistType, :lineName, :productName, :stationName, :checklistReference, :checklistItem, :addOns, :documentDescription, :revision)";
            $result = DbManager::fetchPDOQuery('spectra_db', $sql, [
                ':checklistId' => $checklistId,
                ':checklistName' => $checklistName,
                ':checklistType' => $checklistType, 
                ':lineName' => $lineName,
                ':productName' => $productName,
                ':stationName' => $stationName,
                ':checklistReference' => $reference, 
                ':checklistItem' => $item,
                ':addOns' => $add_on,
                ':documentDescription' => $documentDescription,
                ':revision' => $revision
            ]);
            $firstItemInserted = true;
        } else {
            // For subsequent items, don't include document_description and revision
            $sql = "INSERT INTO tbl_chk_checklist (checklist_id, checklist_name, checklist_type, line_id, product_id, station_id, checklist_reference, checklist_item, add_on) 
            VALUES (:checklistId, :checklistName, :checklistType, :lineName, :productName, :stationName, :checklistReference, :checklistItem, :addOns)";
            $result = DbManager::fetchPDOQuery('spectra_db', $sql, [
                ':checklistId' => $checklistId,
                ':checklistName' => $checklistName,
                ':checklistType' => $checklistType, 
                ':lineName' => $lineName,
                ':productName' => $productName,
                ':stationName' => $stationName,
                ':checklistReference' => $reference, 
                ':checklistItem' => $item,
                ':addOns' => $add_on
            ]);
        }
        
        if (!$result) {
            $success = false;
            break;
        }
    }
    
    if ($success) {
        echo 'Checklist saved successfully';
    } else {
        echo 'Error while saving checklist';
    }
}

function viewChecklist()
{
    $page = isset($_POST['page']) ? $_POST['page'] : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;

    $where = [];
    $params = [":limit" => $limit, ":offset" => $offset];

    if (!empty($_POST['checklistName'])) {
        $where[] = "c.checklist_name = :checklistName";

        $sql = "SELECT checklist_name FROM tbl_chk_checklist WHERE checklist_id = :checklistName AND status = 'A' GROUP BY checklist_id";
        $result = DbManager::fetchPDOQueryData('spectra_db', $sql, [':checklistName' => $_POST['checklistName']]);
        $checklistName = "";
        if (!empty($result['data'])) {
            $checklistName = $result['data'][0]['checklist_name'];
        }
        $params[':checklistName'] = $checklistName;
    }
    if (!empty($_POST['lineName'])) {
        $where[] = "c.line_id = :lineId";
        $params[':lineId'] = $_POST['lineName'];
    }
    if (!empty($_POST['productName'])) {
        $where[] = "c.product_id = :productId";
        $params[':productId'] = $_POST['productName'];
    }
    if (!empty($_POST['stationName'])) {
        $where[] = "c.station_id = :stationId";
        $params[':stationId'] = $_POST['stationName'];
    }
    if (!empty($_POST['reference'])) {
        $where[] = "c.checklist_reference LIKE :reference";
        $params[':reference'] = '%' . $_POST['reference'] . '%';
    }
    if (!empty($_POST['item'])) {
        $where[] = "c.checklist_item LIKE :item";
        $params[':item'] = '%' . $_POST['item'] . '%';
    }

    $whereClause = !empty($where) 
    ? 'WHERE ' . implode(' AND ', $where) . ' AND c.status = \'A\'' 
    : 'WHERE c.status = \'A\'';

    // First, get the total count of filtered results
    $countSql = "SELECT 
            COUNT(*) as total
        FROM 
            tbl_chk_checklist c
        LEFT JOIN 
            tbl_line l ON c.line_id = l.id
        LEFT JOIN 
            tbl_chk_product p ON c.product_id = p.id
        LEFT JOIN 
            tbl_chk_station s ON c.station_id = s.id
        $whereClause";
    
    // Remove limit and offset from params for count query
    $countParams = $params;
    unset($countParams[':limit']);
    unset($countParams[':offset']);
    
    $totalFilteredChecklists = DbManager::fetchPDOQueryData('spectra_db', $countSql, $countParams)["data"][0]["total"];

    // Main query for data
    $sql = "SELECT 
            c.*,
            l.line_name,
            p.product_name,
            s.station_name
        FROM 
            tbl_chk_checklist c
        LEFT JOIN 
            tbl_line l ON c.line_id = l.id
        LEFT JOIN 
            tbl_chk_product p ON c.product_id = p.id
        LEFT JOIN 
            tbl_chk_station s ON c.station_id = s.id
        $whereClause
        ORDER BY 
            c.id DESC 
        LIMIT 
            :limit 
        OFFSET 
            :offset";
    $checklists = DbManager::fetchPDOQueryData('spectra_db', $sql, $params)["data"];

    if (empty($checklists)) {
        ?>
        <div class="text-center">
            <h4>No checklists found.</h4>
        </div>
        <?php
    } else {
        ?>
        <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover dataTables-example" style="min-width: 1000px;">
            <thead>
                <tr>
                    <th><b>Checklist Name</b></th>
                    <th><b>Department Name</b></th>
                    <th><b>Product Name</b></th>
                    <th><b>Station Name</b></th>
                    <th><b>Document Description</b></th>
                    <th><b>Revision Date</b></th>
                    <th><b>Checklist Reference</b></th>
                    <th><b>Checklist Item</b></th>
                    <th style="min-width: 120px;"><b>Action</b></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($checklists as $checklist) { ?>
                <tr>
                    <td><?php echo $checklist['checklist_name']; ?></td>
                    <td><?php echo $checklist['line_name']; ?></td>
                    <td><?php echo $checklist['product_name']; ?></td>
                    <td><?php echo ($checklist['station_name']) ? $checklist['station_name'] : '-'; ?></td>
                    <td><?php echo $checklist['document_description']; ?></td>
                    <td><?php echo $checklist['revision']; ?></td>
                    <td><?php echo $checklist['checklist_reference']; ?></td>
                    <td><?php echo $checklist['checklist_item']; ?></td>
                    <td class="action-column">
                        <div class="action-buttons">
                            <button class="ui tiny teal button" onclick="editChecklist(<?php echo $checklist['id']; ?>, '<?php echo addslashes($checklist['checklist_name']); ?>', '<?php echo $checklist['line_id']; ?>', '<?php echo $checklist['product_id']; ?>', '<?php echo $checklist['station_id']; ?>', '<?php echo addslashes($checklist['checklist_reference']); ?>', '<?php echo addslashes($checklist['checklist_item']); ?>', '<?php echo $checklist['add_on']; ?>', '<?php echo addslashes($checklist['document_description']); ?>', '<?php echo addslashes($checklist['revision']); ?>')">Edit</button>
                            <button class="ui tiny button delete-btn" onclick="deleteChecklist(<?php echo $checklist['id']; ?>)">Delete</button>
                        </div>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

        <!-- Pagination -->
        <div class="text-center">
            <ul class="pagination">
                <?php
                    $totalPages = ceil($totalFilteredChecklists / $limit);

                    // Calculate range of pages to show
                    $startPage = max(1, $page - 10);
                    $endPage = min($totalPages, $page + 10);

                    // Show first page if not in range
                    if ($page > 1) {
                        echo "<li class='page-item'><a class='page-link' href='javascript:void(0)' onclick='fetchAndDisplayChecklists(" . ($page - 1) . ", false)'>&laquo;</a></li>";
                    }

                    // Page numbers
                    for ($i = $startPage; $i <= $endPage; $i++) {
                        $active = ($i == $page) ? 'active' : '';
                        echo "<li class='page-item $active'><a class='page-link' href='javascript:void(0)' onclick='fetchAndDisplayChecklists($i, false)'>$i</a></li>";
                    }

                    // Next button
                    if ($page < $totalPages) {
                        echo "<li class='page-item'><a class='page-link' href='javascript:void(0)' onclick='fetchAndDisplayChecklists(" . ($page + 1) . ", false)'>&raquo;</a></li>";
                    }
                ?>
            </ul>
        </div>
        <?php
    }
}

function updateChecklist()
{
    $checklistId = $_POST['checklistId'];
    $checklistName = $_POST['checklistName'];
    $lineName = $_POST['lineName'];
    $productName = $_POST['productName'];
    $stationName = $_POST['stationName'];
    $documentDescription = isset($_POST['documentDescription']) ? $_POST['documentDescription'] : '';
    $revisionDate = isset($_POST['revisionDate']) ? $_POST['revisionDate'] : '';
    $checklistReference = $_POST['checklistReference'];
    $checklistItem = $_POST['checklistItem'];
    $addOns = $_POST['addOn'];
    $checklistType = "panel";
    
    // Validation
    if (empty($documentDescription)) {
        echo 'Document Description/No cannot be blank.';
        return;
    }
    if (empty($revisionDate)) {
        echo 'Revision Date/No cannot be blank.';
        return;
    }
    
    $sql = "UPDATE tbl_chk_checklist SET checklist_name = :checklistName, line_id = :lineName, product_id = :productName, station_id = :stationName, document_description = :documentDescription, revision = :revisionDate, checklist_reference = :checklistReference, checklist_item = :checklistItem, checklist_type = :checklistType, add_on = :addOns WHERE id = :checklistId";
    
    $result = DbManager::fetchPDOQuery('spectra_db', $sql, [
        ':checklistName' => $checklistName, 
        ':lineName' => $lineName, 
        ':productName' => $productName, 
        ':stationName' => $stationName,
        ':documentDescription' => $documentDescription,
        ':revisionDate' => $revisionDate,
        ':checklistReference' => $checklistReference, 
        ':checklistItem' => $checklistItem, 
        ':checklistType' => $checklistType, 
        ':addOns' => $addOns, 
        ':checklistId' => $checklistId
    ]);
    
    if ($result) {
        echo 'success';
    } else {
        echo 'Error while updating checklist';
    }
}

function checkFormFieldsExist()
{
    $checklistName = $_POST['checklistName'];
    $lineName = $_POST['lineName'];
    $productName = $_POST['productName'];
    $stationName = $_POST['stationName'];
    $checklistReferences = $_POST['checklistReferences'];
    $checklistItems = $_POST['checklistItems'];

    $sql = "SELECT 
        c.checklist_name, 
        l.line_name, 
        p.product_name, 
        s.station_name, 
        c.checklist_reference, 
        c.checklist_item
    FROM tbl_chk_checklist c
    JOIN tbl_line l ON c.line_id = l.id
    JOIN tbl_chk_product p ON c.product_id = p.id
    JOIN tbl_chk_station s ON c.station_id = s.id
    WHERE c.checklist_name = :checklistName 
        AND c.line_id = :lineName
        AND c.product_id = :productName
        AND c.station_id = :stationName
        AND c.checklist_reference IN (:checklistReferences)
        AND c.checklist_item IN (:checklistItems)";

    $result = DbManager::fetchPDOQueryData('spectra_db', $sql, [
        ':checklistName' => $checklistName,
        ':lineName' => $lineName,
        ':productName' => $productName,
        ':stationName' => $stationName,
        ':checklistReferences' => $checklistReferences,
        ':checklistItems' => $checklistItems
    ]);

    echo json_encode($result['data']);
}

function checkChecklistNameExists()
{
    $checklistName = $_POST['checklistName'];
    $lineId = $_POST['lineName'];
    $productId = $_POST['productName'];
    $stationId = $_POST['stationName'];
    $checklistReference = $_POST['checklistReference'];
    $checklistItem = $_POST['checklistItem'];
    $addOn = $_POST['addOn'];
    $documentDescription = isset($_POST['documentDescription']) ? $_POST['documentDescription'] : '';
    $revisionDate = isset($_POST['revisionDate']) ? $_POST['revisionDate'] : '';
    $checklistType = "panel";
    
    $sql = "SELECT id, checklist_name, document_description, revision FROM tbl_chk_checklist WHERE checklist_name = :checklistName AND line_id = :lineId AND product_id = :productId AND station_id = :stationId AND checklist_reference = :checklistReference AND checklist_item = :checklistItem AND add_on = :addOn AND document_description = :documentDescription AND revision = :revisionDate AND checklist_type = :checklistType";
    
    $result = DbManager::fetchPDOQueryData('spectra_db', $sql, [
        ':checklistName' => $checklistName, 
        ':lineId' => $lineId, 
        ':productId' => $productId, 
        ':stationId' => $stationId, 
        ':checklistReference' => $checklistReference, 
        ':checklistItem' => $checklistItem, 
        ':addOn' => $addOn,
        ':documentDescription' => $documentDescription,
        ':revisionDate' => $revisionDate,
        ':checklistType' => $checklistType
    ]);
    
    if (!empty($result['data'])) {
        echo json_encode($result['data'][0]);
    } else {
        echo 'false';
    }
}

function getChecklistNames()
{
    $checklistName = $_POST['search'];
    $sql = "SELECT DISTINCT checklist_name FROM tbl_chk_checklist WHERE checklist_name LIKE :searchName";
    $result = DbManager::fetchPDOQueryData('spectra_db', $sql, [":searchName" => "%$checklistName%"]);
    if (!empty($result['data'])) {
        echo json_encode($result['data']);
        exit;
    } else {
        echo 'false';
        exit;
    }
}

function deleteChecklist()
{
    $checklistId = $_POST['checklistId'];
    $sql = "UPDATE tbl_chk_checklist SET status = :status WHERE id = :checklistId";
    $result = DbManager::fetchPDOQuery('spectra_db', $sql, [':status' => 'D', ':checklistId' => $checklistId]);
    if ($result) {
        echo 'success';
    } else {
        echo 'Error while deleting checklist';
    }
}

function loadChecklist()
{
    // Get POST parameters
    $salesOrderNo = $_POST['sales_order_no'] ?? '';
    $panelNoRaw = $_POST['panel_no'] ?? '';
    $panelNo = explode('|', $panelNoRaw)[0];
    $panelNoDB = str_replace(',', '', commaFormat($panelNo));
    $checklistId = $_POST['checklist_id'] ?? '';
    $lineId = $_POST['line_id'] ?? '';
    $productId = $_POST['product_id'] ?? '';
    $stationId = isset($_POST['station_id']) ? $_POST['station_id'] : null;
    $subItem = isset($_POST['sub_item']) ? trim($_POST['sub_item']) : '';
    $actionFromSave = isset($_POST['action_from_save']) ? $_POST['action_from_save'] : '';

    // ===== STEP 1: Get Product Name =====
    $getProduct = "SELECT DISTINCT product_name FROM tbl_chk_product WHERE id = :product_id LIMIT 1";
    $productResult = DbManager::fetchPDOQueryData('spectra_db', $getProduct, [':product_id' => $productId])['data'];
    $getProductName = !empty($productResult) ? $productResult[0]['product_name'] : '';
    $isComponentProduct = ($getProductName == "Component") ? 1 : 0;

    // ===== STEP 2: Get MTool Project Info =====
    $mtoolProjectInfoQueryData = [];
    
    if ($getProductName == "Component") {
        $mtoolProjectInfoQuery = "
            SELECT 
                FactoryNumber,
                ProjectName,
                OrderManager
            FROM dbo.OneX_ProjectLeads
            WHERE FactoryNumber = :p1
        ";
        
        $mtoolProjectInfoQueryData = DbManager::fetchPDOQueryData('MTool_INKWA', $mtoolProjectInfoQuery, [
            ":p1" => $salesOrderNo
        ])["data"] ?? [];
    } else {
        $mtoolProjectInfoQuery = "
            SELECT 
                SapPosData.*,
                pl.ProjectName,
                pl.OrderManager,
                pt.Product,
                ptd.RatedVoltage,
                ptd.SCCurTime,
                ptd.BusbarCurrent
            FROM (
                SELECT 
                    ProjectNo,
                    PosNo,
                    LocationCode AS PanelName,
                    TypicalCode AS TypicalName
                FROM dbo.OneX_SapPosData 
                WHERE ProjectNo = :p1 
                AND CAST(PosNo AS NVARCHAR(10)) = CAST(:panelNo AS NVARCHAR(10)) 
                AND TypicalCode IS NOT NULL
            ) AS SapPosData
            JOIN dbo.OneX_ProjectLeads pl 
                ON pl.FactoryNumber = SapPosData.ProjectNo
            JOIN dbo.OneX_ProjectTrackingEE pt 
                ON pt.FactoryNumber = SapPosData.ProjectNo
            JOIN dbo.OneX_ProjectTechDataEE ptd 
                ON ptd.FactoryNumber = SapPosData.ProjectNo
        ";
        
        $mtoolProjectInfoQueryData = DbManager::fetchPDOQueryData('MTool_INKWA', $mtoolProjectInfoQuery, [
            ":p1" => $salesOrderNo, 
            ":panelNo" => $panelNoDB
        ])["data"] ?? [];
    }

    // ===== STEP 3: Check for Saved Checklist Data =====
    $shouldLoadItems = true;
    $savedData = [];
    $punchListData = [];
    $finalStatus = "0"; // Default to not submitted
    
    if ($isComponentProduct && empty($subItem)) {
        // CASE 1: Component product without subItem selected
        // Don't load any saved data yet, just show header
        $shouldLoadItems = false;
        $savedData = [];
        $punchListData = [];
        $finalStatus = "0";
    } else {
        // CASE 2: Either non-component product OR component product with subItem selected
        // Load saved data from database
        $savedDataQuery = "
            SELECT 
                cd.*,
                GROUP_CONCAT(DISTINCT TRIM(ud.work_person) ORDER BY ud.create_date DESC SEPARATOR ', ') as previous_user
            FROM tbl_checklist_data cd
            LEFT JOIN tbl_checklist_user_details ud 
                ON ud.cl_id = cd.id 
                AND TRIM(ud.work_person) IS NOT NULL 
                AND TRIM(ud.work_person) != ''
            WHERE cd.order_no = :order_no 
            AND cd.item_no = :item_no 
            AND cd.checklist_id = :checklist_id 
            AND cd.line_id = :line_id 
            AND cd.product_id = :product_id
        ";

        $params = [
            ':order_no' => $salesOrderNo,
            ':item_no' => $panelNo,
            ':checklist_id' => $checklistId,
            ':line_id' => $lineId,
            ':product_id' => $productId
        ];

        // If sub_item is provided (component product with subItem selected), filter by it
        if (!empty($subItem)) {
            $savedDataQuery .= " AND cd.sub_item = :sub_item";
            $params[':sub_item'] = $subItem;
        }

        $savedDataQuery .= " GROUP BY cd.id ORDER BY cd.id ASC";

        $savedData = DbManager::fetchPDOQueryData('spectra_db', $savedDataQuery, $params)['data'] ?? [];

        // ===== STEP 4: Get Punch List Data =====
        $punchListQuery = "
            SELECT 
                pld.*,
                pld.chk_data_id as checklist_data_id,
                cd.item_id as checklist_item_id,
                plud.id as user_detail_id,
                plud.created_at as user_created_at,
                plud.user_id as user_detail_user_id,
                plud.user_name as user_detail_user_name,
                plud.role_id as user_detail_role_id,
                plud.role_name as user_detail_role_name
            FROM tbl_punch_list_data pld
            LEFT JOIN tbl_checklist_data cd 
                ON pld.chk_data_id = cd.id
            LEFT JOIN (
                SELECT 
                    pl_id, 
                    id, 
                    created_at, 
                    user_id,
                    user_name,
                    role_id,
                    role_name,
                    ROW_NUMBER() OVER (PARTITION BY pl_id ORDER BY created_at DESC) as rn
                FROM tbl_punch_list_user_details
            ) plud 
                ON pld.id = plud.pl_id AND plud.rn = 1
            WHERE pld.order_no = :order_no 
            AND pld.item_no = :item_no 
            AND pld.checklist_id = :checklist_id 
            AND pld.line_id = :line_id 
            AND pld.product_id = :product_id
        ";

        $punchParams = [
            ':order_no' => $salesOrderNo,
            ':item_no' => $panelNo,
            ':checklist_id' => $checklistId,
            ':line_id' => $lineId,
            ':product_id' => $productId
        ];

        // If sub_item is provided, filter punch list by it
        if (!empty($subItem)) {
            $punchListQuery .= " AND pld.sub_item = :sub_item";
            $punchParams[':sub_item'] = $subItem;
        }

        $punchListQuery .= " ORDER BY pld.reference, pld.id ASC";

        $punchListDataRaw = DbManager::fetchPDOQueryData('spectra_db', $punchListQuery, $punchParams)['data'] ?? [];

        // ===== STEP 5: Format Punch List Data by Reference =====
        $punchListData = [];
        
        if (!empty($punchListDataRaw)) {
            foreach ($punchListDataRaw as $item) {
                $reference = $item['reference'];
                
                if (!isset($punchListData[$reference])) {
                    $punchListData[$reference] = [];
                }

                // Format dates
                $findingDate = !empty($item['finding_date']) ? date('Y-m-d H:i:s', strtotime($item['finding_date'])) : '';
                $resolutionDate = !empty($item['resolution_date']) ? date('Y-m-d H:i:s', strtotime($item['resolution_date'])) : '';
                $recheckingDate = !empty($item['rechecking_date']) ? date('Y-m-d H:i:s', strtotime($item['rechecking_date'])) : '';

                $punchListData[$reference][] = [
                    'id' => $item['id'],
                    'description' => $item['description'],
                    'checklist_data_id' => $item['checklist_data_id'],
                    'checklist_item_id' => $item['checklist_item_id'],
                    'finding' => [
                        'by' => $item['finding_by'],
                        'date' => $findingDate
                    ],
                    'resolution' => [
                        'by' => $item['resolution_by'],
                        'date' => $resolutionDate,
                        'remark' => $item['resolution_remark']
                    ],
                    'rechecking' => [
                        'by' => $item['rechecking_by'],
                        'date' => $recheckingDate,
                        'remark' => $item['rechecking_remark']
                    ],
                    'work_hrs' => $item['work_hrs'],
                    'code' => $item['code'],
                    'user_details' => !empty($item['user_detail_id']) ? [
                        'id' => $item['user_detail_id'],
                        'created_at' => $item['user_created_at'],
                        'user_id' => $item['user_detail_user_id'],
                        'user_name' => $item['user_detail_user_name'],
                        'role_id' => $item['user_detail_role_id'],
                        'role_name' => $item['user_detail_role_name']
                    ] : null
                ];
            }
        }
    }

    if (!empty($savedData)) {
        // CRITICAL FIX: Determine status based on actionFromSave parameter
        // This parameter is passed ONLY when reloading after save/submit
        if (!empty($actionFromSave)) {
            // If we have an explicit action from save, use it
            if ($actionFromSave === 'submit') {
                $finalStatus = "1"; // Submitted - not editable
            } else {
                $finalStatus = "0"; // Saved - editable
            }
        } else {
            // If no action from save, check the database status
            // Only set to 1 if ALL items have status = 1 AND we're not coming from a save
            $allStatusOne = true;
            foreach ($savedData as $item) {
                if ($item['status'] != 1) {
                    $allStatusOne = false;
                    break;
                }
            }
            $finalStatus = $allStatusOne ? "1" : "0";
        }

        $headerInfo = [
            'checklistName' => $savedData[0]['checklist_name'] ?? '',
            'projectName' => $savedData[0]['project_name'] ?? '',
            'orderProcessor' => $savedData[0]['order_processor'] ?? '',
            'panelType' => $savedData[0]['panel_type'] ?? '',
            'ur_value' => !empty($savedData[0]['ur_value']) 
                ? $savedData[0]['ur_value'] 
                : (!empty($mtoolProjectInfoQueryData) ? $mtoolProjectInfoQueryData[0]['RatedVoltage'] ?? '' : ''),
            'ik_value' => !empty($savedData[0]['ik_value']) 
                ? $savedData[0]['ik_value'] 
                : (!empty($mtoolProjectInfoQueryData) ? $mtoolProjectInfoQueryData[0]['SCCurTime'] ?? '' : ''),
            'ir_value' => !empty($savedData[0]['ir_value']) 
                ? $savedData[0]['ir_value'] 
                : (!empty($mtoolProjectInfoQueryData) ? $mtoolProjectInfoQueryData[0]['BusbarCurrent'] ?? '' : ''),
            'production_order_no' => $savedData[0]['production_order_no'] ?? '',
            'location_name' => $savedData[0]['location_name'] ?? '',
            'typical_name' => $savedData[0]['typical_name'] ?? '',
            'sub_item' => $savedData[0]['sub_item'] ?? '',
            'is_component' => $isComponentProduct
        ];

        // Format saved items
        $items = array_map(function($data) {
            $serialData = null;
            if ($data['add_on_type'] == '2' && !empty($data['add_on_value'])) {
                $serialData = json_decode($data['add_on_value'], true);
            }
            
            return [
                'id' => $data['item_id'],
                'checklist_data_id' => $data['id'],
                'checklist_id' => $data['checklist_id'],
                'line_id' => $data['line_id'],
                'product_id' => $data['product_id'],
                'station_id' => $data['station_id'],
                'previous_user' => $data['previous_user'] ?? '',
                'checklist_reference' => $data['reference_to_traning_document'] ?? '',
                'checklist_item' => $data['checklist_item'] ?? '',
                'add_on' => $data['add_on_type'] ?? '',
                'add_on_value' => $data['add_on_value'] ?? '',
                'passed' => $data['worker_status'] == 1 ? 'ok' : 
                          ($data['worker_status'] == 2 ? 'notok' : 
                          ($data['worker_status'] == 3 ? 'na' : '')),
                'remark' => $data['remark'] ?? '',
                'serial_l1' => $serialData ? ($serialData['serial_l1'] ?? '') : '',
                'serial_l2' => $serialData ? ($serialData['serial_l2'] ?? '') : '',
                'serial_l3' => $serialData ? ($serialData['serial_l3'] ?? '') : ''
            ];
        }, $savedData);
    } else {
        // Load default checklist template
        $query = "
            SELECT * FROM tbl_chk_checklist 
            WHERE checklist_id = :checklistId
            AND line_id = :lineId
            AND product_id = :productId
            AND status = 'A'
            ORDER BY id ASC
        ";
              
        $checklistItems = DbManager::fetchPDOQueryData('spectra_db', $query, [
            ':checklistId' => htmlspecialchars($checklistId), 
            ':lineId' => $lineId, 
            ':productId' => $productId
        ])['data'] ?? [];

        $location_name = !empty($mtoolProjectInfoQueryData) ? ($mtoolProjectInfoQueryData[0]['PanelName'] ?? '') : '';
        $typical_name = !empty($mtoolProjectInfoQueryData) ? ($mtoolProjectInfoQueryData[0]['TypicalName'] ?? '') : '';

        // For Component products, panelType should be "Component"
        $panelTypeValue = $isComponentProduct ? "Component" : (!empty($mtoolProjectInfoQueryData) ? ($mtoolProjectInfoQueryData[0]['Product'] ?? '') : '');

        $headerInfo = [
            'checklistName' => !empty($checklistItems) ? ($checklistItems[0]['checklist_name'] ?? '') : "",
            'projectName' => !empty($mtoolProjectInfoQueryData) ? ($mtoolProjectInfoQueryData[0]['ProjectName'] ?? '') : "",
            'orderProcessor' => !empty($mtoolProjectInfoQueryData) ? ($mtoolProjectInfoQueryData[0]['OrderManager'] ?? '') : "",
            'panelType' => $panelTypeValue,
            'ur_value' => !empty($mtoolProjectInfoQueryData) ? ($mtoolProjectInfoQueryData[0]['RatedVoltage'] ?? '') : '',
            'ik_value' => !empty($mtoolProjectInfoQueryData) ? ($mtoolProjectInfoQueryData[0]['SCCurTime'] ?? '') : '',
            'ir_value' => !empty($mtoolProjectInfoQueryData) ? ($mtoolProjectInfoQueryData[0]['BusbarCurrent'] ?? '') : '',
            'production_order_no' => '',
            'location_name' => $location_name,
            'typical_name' => $typical_name,
            'sub_item' => $subItem,
            'is_component' => $isComponentProduct
        ];
        
        $items = $shouldLoadItems ? $checklistItems : [];
        $finalStatus = "0";
    }
    
    // Determine if data loading is allowed
    $isAllow = true;
    if ($getProductName != "Component" && empty($panelNo)) {
        $isAllow = false;
    }

    // Build final response
    $response = [
        'headerInfo' => $headerInfo,
        'items' => ($salesOrderNo && $isAllow && $shouldLoadItems) ? $items : "",
        'status' => $finalStatus,
        'autoSelected' => [
            'line_id' => $lineId,
            'product_id' => $productId,
            'station_id' => $stationId
        ],
        'punchList' => $punchListData
    ];
    
    echo json_encode($response);
}

function commaFormat($number) {
    // Remove leading zeros first
    $number = ltrim($number, '0');
    
    // If number is greater than 999, add comma
    if ($number > 999) {
        return number_format($number, 0, '', ',');
    }
    
    return $number;
}

function saveChecklistData()
{
    set_time_limit(300); // 5 minutes
    ini_set('max_execution_time', 300);
    
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    
    $items = $input['items'];
    $headerInfo = $input['headerInfo'];
    $success = true;
    
    $user_id = $_SESSION['user_id'];
    $user_name = $_SESSION['username'];
    $role_id = isset($_SESSION['role_id']) ? $_SESSION['role_id'] : "";
    $role_name = $_SESSION['role_name'];
    $checklist_name = $headerInfo['checklist_name'];
    $project_name = $headerInfo['project_name'];
    $order_processor = $headerInfo['order_processor'];
    $panel_type = $headerInfo['panel_type'];
    $production_order_no = $headerInfo['production_order_no'];
    $location_name = $headerInfo['location_name'] ?? '';
    $typical_name = $headerInfo['typical_name'] ?? '';
    $sub_item = isset($headerInfo['sub_item']) ? trim($headerInfo['sub_item']) : '';
    $is_component_product = $headerInfo['is_component_product'] ?? 0;
    $ur_value = $headerInfo['ur_value'];
    $ik_value = $headerInfo['ik_value'];
    $ir_value = $headerInfo['ir_value'];

    $sql_item_id = "SELECT id, checklist_item FROM tbl_chk_checklist WHERE status = 'A'";        
    $checklist_item_list = DbManager::fetchPDOQuery('spectra_db', $sql_item_id)['data'];
    $checklist_item_array = array();
    foreach ($checklist_item_list as $chk_key => $chk_value) {
        $checklist_item_array[$chk_value['id']] = $chk_value['checklist_item'];
    }

    // Create an array to store item_id to checklist_data_id mapping
    $checklistDataIds = [];

    foreach ($items as $item) {
        $order_no = $item['order_no'];
        $item_no = $item['panel_no'];
        $checklist_id = $item['checklist_id'];
        $line_id = $item['line_id'];
        $product_id = $item['product_id'];
        $station_id = $item['station_id'];
        $item_id = $item['item_id'];
        
        // SKIP ITEMS THAT ARE NOT IN THE ACTIVE CHECKLIST ITEMS LIST
        if (!isset($checklist_item_array[$item_id])) {
            error_log("WARNING: Item ID $item_id is not an active checklist item. Skipping...");
            continue;
        }
        
        $reference_to_traning_document = $item['reference'];
        $checklist_item = $checklist_item_array[$item_id];

        $add_on_type = $item['addon_type'];
        $add_on_value = '';
        $valid_add_on_value = '';
        if ($add_on_type == '0') {
            $add_on_value = '';
        } else if ($add_on_type == '1') {
            $add_on_value = $item['addon_value'];
            $valid_add_on_value = 1;
        } else if ($add_on_type == '2') {
            $serial_data = [
                'serial_l1' => $item['serial_l1'] ?? '',
                'serial_l2' => $item['serial_l2'] ?? '',
                'serial_l3' => $item['serial_l3'] ?? ''
            ];
            $add_on_value = json_encode($serial_data);
            if (($item['serial_l1'] == "") && ($item['serial_l2'] == "") && ($item['serial_l3'] == "")) {
                $valid_add_on_value = 0;
            } else {
                $valid_add_on_value = 1;
            }
        }

        $worker_status = 0;
        $itemStatus = 0;
        if (strtolower($item['passed']) === 'ok') {
            $worker_status = 1;
            $itemStatus = 1;
        } elseif (strtolower($item['passed']) === 'notok') {
            $worker_status = 2;
            $itemStatus = 0;
        } elseif (strtolower($item['passed']) === 'na') {
            $worker_status = 3;
            $itemStatus = 1;
        }

        $status = ($input['action'] == 'submit') ? 1 : $itemStatus;
        $remark = $item['remark'];
        
        // FIX: Check if record exists - Include sub_item in the check
        $check_sql = "SELECT id FROM tbl_checklist_data 
                     WHERE order_no = :order_no 
                     AND item_no = :item_no 
                     AND checklist_id = :checklist_id 
                     AND line_id = :line_id 
                     AND product_id = :product_id 
                     AND station_id = :station_id 
                     AND item_id = :item_id
                     AND sub_item = :sub_item";
        
        $check_params = [
            ':order_no' => $order_no,
            ':item_no' => $item_no,
            ':checklist_id' => $checklist_id,
            ':line_id' => $line_id,
            ':product_id' => $product_id,
            ':station_id' => $station_id,
            ':item_id' => $item_id,
            ':sub_item' => $sub_item
        ];

        $check_result = DbManager::fetchPDOQuery('spectra_db', $check_sql, $check_params);

        if ($check_result && !empty($check_result['data'])) {
            // Get the previous data for comparison
            $prev_data_sql = "SELECT add_on_value, worker_status, remark, work_person 
                            FROM tbl_checklist_data 
                            WHERE id = :id";
            
            $prev_data = DbManager::fetchPDOQuery('spectra_db', $prev_data_sql, [
                ':id' => $check_result['data'][0]['id']
            ]);

            $prev_values = $prev_data['data'][0];

            // Compare current values with previous values
            $has_changes = false;
            
            // Check add_on_value changes
            if ($add_on_type == '0') {
                if ($prev_values['add_on_value'] != '') {
                    $has_changes = true;
                }
            } else if ($add_on_type == '1') {
                if ($prev_values['add_on_value'] != $add_on_value) {
                    $has_changes = true;
                }
            } else if ($add_on_type == '2') {
                $prev_serial_data = json_decode($prev_values['add_on_value'], true);
                $current_serial_data = json_decode($add_on_value, true);
                
                if ($prev_serial_data != $current_serial_data) {
                    $has_changes = true;
                }
            }

            // Check worker_status changes
            if ($prev_values['worker_status'] != $worker_status) {
                $has_changes = true;
            }

            // Check remark changes
            if ($prev_values['remark'] != $remark) {
                $has_changes = true;
            }

            // Only set work_person if there are changes
            if ($has_changes) {
                $work_person = $user_name;
            } else {
                // Keep the previous work_person
                $work_person = $prev_values['work_person'];
            }

            // Update existing record
            $update_sql = "UPDATE tbl_checklist_data 
                          SET checklist_name = :checklist_name,
                              project_name = :project_name,
                              order_processor = :order_processor,
                              panel_type = :panel_type,
                              production_order_no = :production_order_no,
                              location_name = :location_name,
                              typical_name = :typical_name,
                              sub_item = :sub_item,
                              is_component_product = :is_component_product,
                              ur_value = :ur_value,
                              ik_value = :ik_value,
                              ir_value = :ir_value,
                              reference_to_traning_document = :reference_to_traning_document,
                              checklist_item = :checklist_item,
                              add_on_type = :add_on_type,
                              add_on_value = :add_on_value,
                              worker_status = :worker_status,
                              work_person = :work_person,
                              remark = :remark,
                              user_id = :user_id,
                              user_name = :user_name,
                              role_id = :role_id,
                              role_name = :role_name,
                              status = :status
                          WHERE order_no = :order_no 
                          AND item_no = :item_no 
                          AND checklist_id = :checklist_id 
                          AND line_id = :line_id 
                          AND product_id = :product_id 
                          AND station_id = :station_id 
                          AND item_id = :item_id
                          AND sub_item = :sub_item";

            $update_params = [
                ':order_no' => $order_no,
                ':item_no' => $item_no,
                ':checklist_id' => $checklist_id,
                ':line_id' => $line_id,
                ':product_id' => $product_id,
                ':station_id' => $station_id,
                ':checklist_name' => $checklist_name,
                ':project_name' => $project_name,
                ':order_processor' => $order_processor,
                ':panel_type' => $panel_type,
                ':production_order_no' => $production_order_no,
                ':location_name' => $location_name,
                ':typical_name' => $typical_name,
                ':sub_item' => $sub_item,
                ':is_component_product' => $is_component_product,
                ':ur_value' => $ur_value,
                ':ik_value' => $ik_value,
                ':ir_value' => $ir_value,
                ':reference_to_traning_document' => $reference_to_traning_document,
                ':item_id' => $item_id,
                ':checklist_item' => $checklist_item,
                ':add_on_type' => $add_on_type,
                ':add_on_value' => $add_on_value,
                ':worker_status' => $worker_status,
                ':work_person' => $work_person,
                ':remark' => $remark,
                ':user_id' => $user_id,
                ':user_name' => $user_name,
                ':role_id' => $role_id,
                ':role_name' => $role_name,
                ':status' => $status
            ];

            $result = DbManager::fetchPDOQuery('spectra_db', $update_sql, $update_params);

            $lastInsertId = $check_result['data'][0]['id'];
            // Store the mapping
            $checklistDataIds[$item_id] = $lastInsertId;
        } else {
            $work_person = "";
            if (($add_on_type == 0) && ($add_on_value == "" && $worker_status != 0) || ($remark != "")) {
                $work_person = $user_name;
            } else if (($add_on_type == 1) && (($add_on_value != "" && $valid_add_on_value == 1) || ($worker_status != 0) || ($remark != ""))) {
                $work_person = $user_name;
            } else if (($add_on_type == 2) && (($add_on_value != "" && $valid_add_on_value == 1) || ($worker_status != 0)) || ($remark != "")) {
                $work_person = $user_name;
            }
            // Insert new record
            $insert_sql = "INSERT INTO tbl_checklist_data (order_no, item_no, checklist_id, line_id, product_id, station_id, 
                          checklist_name, project_name, order_processor, panel_type, production_order_no, location_name, 
                          typical_name, sub_item, is_component_product, ur_value, ik_value, ir_value, reference_to_traning_document, item_id, checklist_item, 
                          add_on_type, add_on_value, worker_status, work_person, remark, user_id, user_name, role_id, role_name, status) 
                          VALUES (:order_no, :item_no, :checklist_id, :line_id, :product_id, :station_id, :checklist_name, 
                          :project_name, :order_processor, :panel_type, :production_order_no, :location_name, :typical_name, 
                          :sub_item, :is_component_product, :ur_value, :ik_value, :ir_value, :reference_to_traning_document, :item_id, :checklist_item, 
                          :add_on_type, :add_on_value, :worker_status, :work_person, :remark, :user_id, :user_name, :role_id, :role_name, :status)";

            $insert_params = [
                ':order_no' => $order_no,
                ':item_no' => $item_no,
                ':checklist_id' => $checklist_id,
                ':line_id' => $line_id,
                ':product_id' => $product_id,
                ':station_id' => $station_id,
                ':checklist_name' => $checklist_name,
                ':project_name' => $project_name,
                ':order_processor' => $order_processor,
                ':panel_type' => $panel_type,
                ':production_order_no' => $production_order_no,
                ':location_name' => $location_name,
                ':typical_name' => $typical_name,
                ':sub_item' => $sub_item,
                ':is_component_product' => $is_component_product,
                ':ur_value' => $ur_value,
                ':ik_value' => $ik_value,
                ':ir_value' => $ir_value,
                ':reference_to_traning_document' => $reference_to_traning_document,
                ':item_id' => $item_id,
                ':checklist_item' => $checklist_item,
                ':add_on_type' => $add_on_type,
                ':add_on_value' => $add_on_value,
                ':worker_status' => $worker_status,
                ':work_person' => $work_person,
                ':remark' => $remark,
                ':user_id' => $user_id,
                ':user_name' => $user_name,
                ':role_id' => $role_id,
                ':role_name' => $role_name,
                ':status' => $status
            ];

            $result = DbManager::fetchPDOQuery('spectra_db', $insert_sql, $insert_params);

            if ($result) {
                $lastInsertId = $result['pdoConnection']->lastInsertId();
                // Store the mapping
                $checklistDataIds[$item_id] = $lastInsertId;
            }
        }

        if ($result) {
            // Always insert into user details table
            $sql_user_details = "INSERT INTO tbl_checklist_user_details (cl_id, order_no, item_no, checklist_id, line_id, 
                               product_id, station_id, checklist_name, project_name, order_processor, panel_type, 
                               production_order_no, location_name, typical_name, sub_item, is_component_product, ur_value, ik_value, ir_value, 
                               reference_to_traning_document, item_id, checklist_item, add_on_type, add_on_value, 
                               worker_status, work_person, remark, user_id, user_name, role_id, role_name, status) 
                               VALUES (:cl_id, :order_no, :item_no, :checklist_id, :line_id, :product_id, :station_id, 
                               :checklist_name, :project_name, :order_processor, :panel_type, :production_order_no, 
                               :location_name, :typical_name, :sub_item, :is_component_product, :ur_value, :ik_value, :ir_value, :reference_to_traning_document, 
                               :item_id, :checklist_item, :add_on_type, :add_on_value, :worker_status, :work_person, :remark, :user_id, 
                               :user_name, :role_id, :role_name, :status)";

            $user_details_params = [
                ':cl_id' => $lastInsertId,
                ':order_no' => $order_no,
                ':item_no' => $item_no,
                ':checklist_id' => $checklist_id,
                ':line_id' => $line_id,
                ':product_id' => $product_id,
                ':station_id' => $station_id,
                ':checklist_name' => $checklist_name,
                ':project_name' => $project_name,
                ':order_processor' => $order_processor,
                ':panel_type' => $panel_type,
                ':production_order_no' => $production_order_no,
                ':location_name' => $location_name,
                ':typical_name' => $typical_name,
                ':sub_item' => $sub_item,
                ':is_component_product' => $is_component_product,
                ':ur_value' => $ur_value,
                ':ik_value' => $ik_value,
                ':ir_value' => $ir_value,
                ':reference_to_traning_document' => $reference_to_traning_document,
                ':item_id' => $item_id,
                ':checklist_item' => $checklist_item,
                ':add_on_type' => $add_on_type,
                ':add_on_value' => $add_on_value,
                ':worker_status' => $worker_status,
                ':work_person' => $work_person,
                ':remark' => $remark,
                ':user_id' => $user_id,
                ':user_name' => $user_name,
                ':role_id' => $role_id,
                ':role_name' => $role_name,
                ':status' => $status
            ];

            $result_user_details = DbManager::fetchPDOQuery('spectra_db', $sql_user_details, $user_details_params);

            if (!$result_user_details) {
                $success = false;
                break;
            }
        } else {
            $success = false;
            break;
        }
    }
    
    // Add this new section for compliance data
    if (isset($input['complianceData']) && !empty($input['complianceData'])) {
        $complianceData = $input['complianceData'];
        $success = savePunchListData($complianceData, $checklistDataIds, $input['action']);
        
        if (!$success) {
            echo json_encode(['success' => false, 'message' => 'Error while saving punch list data']);
            return;
        }
    }

    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Checklist data saved successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error while saving checklist data']);
    }
}

function savePunchListData($complianceData, $checklistDataIds, $inputAction) {
    $success = true;
    $user_id = $_SESSION['user_id'];
    $user_name = $_SESSION['username'];
    $role_id = isset($_SESSION['role_id']) ? $_SESSION['role_id'] : "";
    $role_name = $_SESSION['role_name'];
    
    $sub_item = isset($complianceData['sub_item']) ? trim($complianceData['sub_item']) : '';
    $is_component_product = $complianceData['is_component_product'] ?? 0;

    $formatDate = function($date) {
        if (empty($date) || $date === '0000-00-00 00:00:00' || $date === '-0001-11-30 00:00:00') {
            return null;
        }
        return $date;
    };

    // Process each reference group
    foreach ($complianceData['reference'] as $reference => $items) {
        foreach ($items as $item) {
            // Get the checklist_data_id
            $chk_data_id = null;
            
            if (isset($item['checklist_data_id']) && !empty($item['checklist_data_id'])) {
                $chk_data_id = $item['checklist_data_id'];
            } else if (isset($item['checklist_item_id']) && isset($checklistDataIds[$item['checklist_item_id']])) {
                $chk_data_id = $checklistDataIds[$item['checklist_item_id']];
            }

            // Validate chk_data_id
            if (!$chk_data_id || $chk_data_id == 0) {
                error_log("WARNING: Invalid chk_data_id for item: " . json_encode($item));
                continue; // Skip this item
            }

            // Check status in tbl_checklist_data
            $checklistDataStatus = null;
            if ($chk_data_id) {
                $checklistStatusSql = "SELECT status FROM tbl_checklist_data WHERE id = :id LIMIT 1";
                $checklistStatusResult = DbManager::fetchPDOQuery('spectra_db', $checklistStatusSql, [':id' => $chk_data_id]);
                
                if ($checklistStatusResult && !empty($checklistStatusResult['data'])) {
                    $checklistDataStatus = $checklistStatusResult['data'][0]['status'];
                }
            }

            // Check existing punch list record - FIX: Include sub_item in the check
            $existingRecord = null;
            $punchListStatus = null;
            
            if (isset($item['db_id'])) {
                $check_by_id_sql = "SELECT id, status FROM tbl_punch_list_data WHERE id = :id";
                $existingRecord = DbManager::fetchPDOQuery('spectra_db', $check_by_id_sql, [':id' => $item['db_id']]);
            }

            if (!$existingRecord || empty($existingRecord['data'])) {
                $check_sql = "SELECT id, status FROM tbl_punch_list_data 
                             WHERE order_no = :order_no 
                             AND item_no = :item_no 
                             AND reference = :reference
                             AND chk_data_id = :chk_data_id
                             AND sub_item = :sub_item";
                
                $check_params = [
                    ':order_no' => $complianceData['order_no'],
                    ':item_no' => $complianceData['item_no'],
                    ':reference' => $reference,
                    ':chk_data_id' => $chk_data_id,
                    ':sub_item' => $sub_item
                ];

                $existingRecord = DbManager::fetchPDOQuery('spectra_db', $check_sql, $check_params);
            }

            // Get punch list status if record exists
            if ($existingRecord && !empty($existingRecord['data'])) {
                $punchListStatus = $existingRecord['data'][0]['status'];
            }

            // Handle blank remarks
            if (empty(trim($item['resolution']['remark']))) {
                $item['resolution']['by'] = '';
                $item['resolution']['date'] = null;
                $item['resolution']['remark'] = '';
            }

            if (empty(trim($item['rechecking']['remark']))) {
                $item['rechecking']['by'] = '';
                $item['rechecking']['date'] = null;
                $item['rechecking']['remark'] = '';
            }

            $work_hrs = !empty($item['work_hrs']) ? $item['work_hrs'] : null;

            // Determine status - CHECK BOTH TABLES
            // If either tbl_checklist_data OR tbl_punch_list_data has status = 1, keep it as 1
            $punchStatus = 0;
            
            // Check if either table has status = 1
            if (($checklistDataStatus == 1) || ($punchListStatus == 1)) {
                // If either is already submitted, keep punch list as submitted
                $punchStatus = 1;
                
                error_log("Status preserved as 1 - Checklist Data Status: $checklistDataStatus, Punch List Status: $punchListStatus for reference: $reference, chk_data_id: $chk_data_id");
            } else {
                // If neither is submitted, set based on action
                $punchStatus = ($inputAction == 'submit') ? 1 : 0;
            }

            $params = [
                ':checklist_id' => $complianceData['checklist_id'],
                ':line_id' => $complianceData['line_id'],
                ':product_id' => $complianceData['product_id'],
                ':station_id' => $complianceData['station_id'],
                ':description' => $item['description'],
                ':finding_by' => $item['finding']['by'],
                ':finding_date' => $formatDate($item['finding']['date']),
                ':resolution_by' => $item['resolution']['by'],
                ':resolution_date' => $formatDate($item['resolution']['date']),
                ':resolution_remark' => $item['resolution']['remark'],
                ':rechecking_by' => $item['rechecking']['by'],
                ':rechecking_date' => $formatDate($item['rechecking']['date']),
                ':rechecking_remark' => $item['rechecking']['remark'],
                ':work_hrs' => $work_hrs,
                ':code' => $item['code'],
                ':chk_data_id' => $chk_data_id,
                ':sub_item' => $sub_item,
                ':is_component_product' => $is_component_product,
                ':status' => $punchStatus
            ];

            $addon_params = array_merge($params, [
                ':order_no' => $complianceData['order_no'],
                ':item_no' => $complianceData['item_no'],
                ':reference' => $reference
            ]);

            if ($existingRecord && !empty($existingRecord['data'])) {
                // Update existing record
                $update_sql = "UPDATE tbl_punch_list_data SET 
                             checklist_id = :checklist_id,
                             line_id = :line_id,
                             product_id = :product_id,
                             station_id = :station_id,
                             description = :description,
                             finding_by = :finding_by,
                             finding_date = :finding_date,
                             resolution_by = :resolution_by,
                             resolution_date = :resolution_date,
                             resolution_remark = :resolution_remark,
                             rechecking_by = :rechecking_by,
                             rechecking_date = :rechecking_date,
                             rechecking_remark = :rechecking_remark,
                             work_hrs = :work_hrs,
                             code = :code,
                             chk_data_id = :chk_data_id,
                             sub_item = :sub_item,
                             is_component_product = :is_component_product,
                             status = :status,
                             updated_at = NOW()
                             WHERE id = :id";

                $params[':id'] = $existingRecord['data'][0]['id'];
                $result = DbManager::fetchPDOQuery('spectra_db', $update_sql, $params);
                $lastInsertId = $existingRecord['data'][0]['id'];
            } else {
                // Insert new record
                $insert_sql = "INSERT INTO tbl_punch_list_data 
                             (order_no, item_no, checklist_id, line_id, product_id, station_id,
                              reference, description, finding_by, finding_date,
                              resolution_by, resolution_date, resolution_remark,
                              rechecking_by, rechecking_date, rechecking_remark,
                              work_hrs, code, chk_data_id, sub_item, is_component_product, status)
                             VALUES 
                             (:order_no, :item_no, :checklist_id, :line_id, :product_id, :station_id,
                              :reference, :description, :finding_by, :finding_date,
                              :resolution_by, :resolution_date, :resolution_remark,
                              :rechecking_by, :rechecking_date, :rechecking_remark,
                              :work_hrs, :code, :chk_data_id, :sub_item, :is_component_product, :status)";

                $result = DbManager::fetchPDOQuery('spectra_db', $insert_sql, $addon_params);
                
                if ($result) {
                    $lastInsertId = $result['pdoConnection']->lastInsertId();
                }
            }

            if ($result) {
                // Insert into user details table
                $user_params = array_merge($addon_params, [
                    ':pl_id' => $lastInsertId,
                    ':user_id' => $user_id,
                    ':user_name' => $user_name,
                    ':role_id' => $role_id,
                    ':role_name' => $role_name
                ]);

                $sql_user_details = "INSERT INTO tbl_punch_list_user_details 
                                   (pl_id, order_no, item_no, checklist_id, line_id, product_id, station_id,
                                    reference, description, finding_by, finding_date,
                                    resolution_by, resolution_date, resolution_remark,
                                    rechecking_by, rechecking_date, rechecking_remark,
                                    work_hrs, code, chk_data_id, sub_item, is_component_product, user_id, user_name, role_id, role_name, status)
                                   VALUES 
                                   (:pl_id, :order_no, :item_no, :checklist_id, :line_id, :product_id, :station_id,
                                    :reference, :description, :finding_by, :finding_date,
                                    :resolution_by, :resolution_date, :resolution_remark,
                                    :rechecking_by, :rechecking_date, :rechecking_remark,
                                    :work_hrs, :code, :chk_data_id, :sub_item, :is_component_product, :user_id, :user_name, :role_id, :role_name, :status)";

                $result_user_details = DbManager::fetchPDOQuery('spectra_db', $sql_user_details, $user_params);

                if (!$result_user_details) {
                    $success = false;
                    error_log("Failed to insert user details for punch list item");
                    break;
                }
            } else {
                $success = false;
                error_log("Failed to insert/update punch list data");
                break;
            }
        }
    }

    return $success;
}

function isPunchListItemComplete($item) {
    // Check if any required field is empty
    return !(
        empty(trim($item['description'])) ||
        empty(trim($item['resolution']['remark'])) ||
        empty(trim($item['rechecking']['remark'])) ||
        empty(trim($item['work_hrs'])) ||
        empty(trim($item['code']))
    );
}

function deletePunchListRow() {
    $id = $_POST['id'] ?? null;
    $order_no = $_POST['order_no'] ?? null;
    $item_no = $_POST['item_no'] ?? null;
    $reference = $_POST['reference'] ?? null;
    $description = $_POST['description'] ?? null;
    SharedManager::saveLog("log_punch_list_data", "Deleted punch list row for sales order no : $order_no, panel no : $item_no, reference : $reference, description : $description");

    if ($id && $order_no && $item_no && $reference && $description) {
        $delete_sql = "DELETE FROM tbl_punch_list_data 
                      WHERE id = :id 
                      AND order_no = :order_no 
                      AND item_no = :item_no 
                      AND reference = :reference 
                      AND description = :description";
        
        $delete_params = [
            ':id' => $id,
            ':order_no' => $order_no,
            ':item_no' => $item_no,
            ':reference' => $reference,
            ':description' => $description
        ];
        
        $result = DbManager::fetchPDOQuery('spectra_db', $delete_sql, $delete_params);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Row deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete row']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    }
}

function getChecklistResults() {
    $draw = $_POST['draw'];
    $start = $_POST['start'];
    $length = $_POST['length'];
    $search = $_POST['search']['value'];
    $orderColumn = $_POST['order'][0]['column'];
    $orderDir = $_POST['order'][0]['dir'];

    // Get filter values
    $orderFilter = $_POST['orderFilter'] ?? '';
    $itemFilter = $_POST['itemFilter'] ?? '';
    $checklistFilter = $_POST['checklistFilter'] ?? '';
    $findingByFilter = $_POST['findingByFilter'] ?? '';
    $dateRangeFilter = $_POST['dateRangeFilter'] ?? '';
    $stationFilter = $_POST['stationFilter'] ?? '';

    // Initialize parameters array
    $params = [];

    // OPTIMIZED: Use a subquery approach to reduce JOIN complexity
    // First, get the punch list data with status filter
    $sql = "SELECT 
            p.id,
            p.order_no,
            p.item_no,
            p.checklist_id,
            p.line_id,
            p.product_id,
            p.station_id,
            p.reference,
            p.description,
            p.finding_by,
            p.finding_date,
            p.resolution_by,
            p.resolution_date,
            p.resolution_remark,
            p.rechecking_by,
            p.rechecking_date,
            p.rechecking_remark,
            p.work_hrs,
            p.code,
            p.chk_data_id,
            p.sub_item,
            p.is_component_product,
            p.status,
            p.created_at,
            p.updated_at,
            COALESCE(cd.panel_type, '') as panel_type,
            COALESCE(cd.location_name, '') as location_name,
            COALESCE(cd.typical_name, '') as typical_name,
            COALESCE(s.station_name, '') as station_name,
            COALESCE(chk.checklist_name, '') as checklist_name
            FROM tbl_punch_list_data p 
            LEFT JOIN tbl_checklist_data cd ON (
                cd.id = p.chk_data_id
            )
            LEFT JOIN tbl_chk_station s ON p.station_id = s.id 
            LEFT JOIN (
                SELECT DISTINCT checklist_id, checklist_name 
                FROM tbl_chk_checklist 
                WHERE status = 'A'
            ) chk ON p.checklist_id = chk.checklist_id
            WHERE p.status = 0";

    // Apply filters
    if (isset($_POST['applyingFilters']) && $_POST['applyingFilters'] === 'true') {
        if (!empty($orderFilter)) {
            $sql .= " AND p.order_no = :orderFilter";
            $params[':orderFilter'] = $orderFilter;
        }
        if (!empty($itemFilter)) {
            $sql .= " AND p.item_no = :itemFilter";
            $params[':itemFilter'] = $itemFilter;
        }
        if (!empty($checklistFilter)) {
            $sql .= " AND chk.checklist_name = :checklistFilter";
            $params[':checklistFilter'] = $checklistFilter;
        }
        if (!empty($findingByFilter)) {
            $sql .= " AND p.finding_by = :findingByFilter";
            $params[':findingByFilter'] = $findingByFilter;
        }
        if (!empty($dateRangeFilter)) {
            $dates = explode(' - ', $dateRangeFilter);
            if (count($dates) === 2) {
                $sql .= " AND DATE(p.finding_date) BETWEEN :startDate AND :endDate";
                $params[':startDate'] = trim($dates[0]);
                $params[':endDate'] = trim($dates[1]);
            }
        }
        if (!empty($stationFilter)) {
            $sql .= " AND p.station_id = :stationFilter";
            $params[':stationFilter'] = $stationFilter;
        }
    }

    // Add search if it exists
    if (!empty($search)) {
        $sql .= " AND (p.order_no LIKE :search 
                 OR cd.panel_type LIKE :search 
                 OR p.item_no LIKE :search 
                 OR cd.location_name LIKE :search
                 OR cd.typical_name LIKE :search
                 OR p.sub_item LIKE :search
                 OR s.station_name LIKE :search 
                 OR chk.checklist_name LIKE :search
                 OR p.description LIKE :search 
                 OR p.finding_by LIKE :search)";
        $params[':search'] = "%$search%";
    }

    // Get total records count (before filtering)
    $totalSql = "SELECT COUNT(*) as count FROM tbl_punch_list_data WHERE status = 0";
    $totalRecords = DbManager::fetchPDOQuery('spectra_db', $totalSql)['data'][0]['count'];

    // Create a count query with same structure but optimized
    $countSql = "SELECT COUNT(DISTINCT p.id) as count FROM tbl_punch_list_data p
                 LEFT JOIN tbl_checklist_data cd ON cd.id = p.chk_data_id
                 LEFT JOIN tbl_chk_station s ON p.station_id = s.id 
                 LEFT JOIN (
                    SELECT DISTINCT checklist_id, checklist_name 
                    FROM tbl_chk_checklist 
                    WHERE status = 'A'
                 ) chk ON p.checklist_id = chk.checklist_id
                 WHERE p.status = 0";
    
    // Add the same filters to count query
    $countParams = [];
    if (isset($_POST['applyingFilters']) && $_POST['applyingFilters'] === 'true') {
        if (!empty($orderFilter)) {
            $countSql .= " AND p.order_no = :orderFilter";
            $countParams[':orderFilter'] = $orderFilter;
        }
        if (!empty($itemFilter)) {
            $countSql .= " AND p.item_no = :itemFilter";
            $countParams[':itemFilter'] = $itemFilter;
        }
        if (!empty($checklistFilter)) {
            $countSql .= " AND chk.checklist_name = :checklistFilter";
            $countParams[':checklistFilter'] = $checklistFilter;
        }
        if (!empty($findingByFilter)) {
            $countSql .= " AND p.finding_by = :findingByFilter";
            $countParams[':findingByFilter'] = $findingByFilter;
        }
        if (!empty($dateRangeFilter)) {
            $dates = explode(' - ', $dateRangeFilter);
            if (count($dates) === 2) {
                $countSql .= " AND DATE(p.finding_date) BETWEEN :startDate AND :endDate";
                $countParams[':startDate'] = trim($dates[0]);
                $countParams[':endDate'] = trim($dates[1]);
            }
        }
        if (!empty($stationFilter)) {
            $countSql .= " AND p.station_id = :stationFilter";
            $countParams[':stationFilter'] = $stationFilter;
        }
    }

    if (!empty($search)) {
        $countSql .= " AND (p.order_no LIKE :search 
                 OR cd.panel_type LIKE :search 
                 OR p.item_no LIKE :search 
                 OR cd.location_name LIKE :search
                 OR cd.typical_name LIKE :search
                 OR p.sub_item LIKE :search
                 OR s.station_name LIKE :search 
                 OR chk.checklist_name LIKE :search
                 OR p.description LIKE :search 
                 OR p.finding_by LIKE :search)";
        $countParams[':search'] = "%$search%";
    }

    $filteredRecords = DbManager::fetchPDOQuery('spectra_db', $countSql, $countParams)['data'][0]['count'];

    // Add ordering
    $columns = [
        0 => 'p.order_no',
        1 => 'cd.panel_type',
        2 => 'p.item_no',
        3 => 'cd.location_name',
        4 => 'cd.typical_name',
        5 => 'p.sub_item',
        6 => 's.station_name',
        7 => 'chk.checklist_name',
        8 => 'p.reference',
        9 => 'p.description',
        10 => 'p.finding_by',
        11 => 'p.finding_date'
    ];
    
    $orderColumn = $columns[$orderColumn] ?? 'p.finding_date';
    $orderDir = strtoupper($orderDir) === 'ASC' ? 'ASC' : 'DESC';
    $sql .= " ORDER BY $orderColumn $orderDir";

    // Add pagination
    if ($length > 0) {
        $sql .= " LIMIT :start, :length";
        $params[':start'] = (int)$start;
        $params[':length'] = (int)$length;
    }

    // Get filtered data
    $result = DbManager::fetchPDOQuery('spectra_db', $sql, $params);

    // Format the response for DataTables
    $response = [
        'draw' => (int)$draw,
        'recordsTotal' => (int)$totalRecords,
        'recordsFiltered' => (int)$filteredRecords,
        'data' => $result['data'] ?? []
    ];

    echo json_encode($response);
}

function getOrderColumn($columnIndex) {
    $columns = [
        0 => 'p.order_no',
        1 => 'c.panel_type',
        2 => 'p.item_no',
        3 => 's.station_name',
        4 => 'p.reference',
        5 => 'p.description',
        6 => 'p.finding_by',
        7 => 'p.finding_date'
    ];
    return $columns[$columnIndex] ?? 'p.finding_date';
}

function getPunchListDetail() {
    $id = $_POST['id'];
    
    $sql = "SELECT 
        p.*,
        c.panel_type,
        c.location_name,
        c.typical_name,
        cl.checklist_name,
        l.line_name,
        pr.product_name,
        s.station_name
    FROM tbl_punch_list_data p
    LEFT JOIN tbl_checklist_data c ON (
        p.order_no = c.order_no 
        AND p.item_no = c.item_no 
        AND p.checklist_id = c.checklist_id
        AND p.line_id = c.line_id
        AND p.product_id = c.product_id
        AND p.station_id = c.station_id
        AND p.sub_item = c.sub_item
    )
    LEFT JOIN tbl_chk_checklist cl ON p.checklist_id = cl.checklist_id
    LEFT JOIN tbl_line l ON p.line_id = l.id
    LEFT JOIN tbl_chk_product pr ON p.product_id = pr.id
    LEFT JOIN tbl_chk_station s ON p.station_id = s.id
    WHERE p.id = :id
    LIMIT 1";

    $result = DbManager::fetchPDOQuery('spectra_db', $sql, [':id' => $id]);
    
    echo json_encode($result['data'][0] ?? null);
}

function updatePunchList() {
    $input = $_POST;
    $action = $input['action']; // 'save' or 'submit'

    // Validate required fields
    if (!isset($input['id'])) {
        echo json_encode(['success' => false, 'message' => 'Missing ID']);
        return;
    }

    $user_id = $_SESSION['user_id'];
    $user_name = $_SESSION['username'];
    $role_id = isset($_SESSION['role_id']) ? $_SESSION['role_id'] : "";
    $role_name = $_SESSION['role_name'];

    // Get the current data and chk_data_id
    $getCurrentDataSql = "SELECT *, chk_data_id FROM tbl_punch_list_data WHERE id = :id";
    $currentData = DbManager::fetchPDOQueryData('spectra_db', $getCurrentDataSql, [':id' => $input['id']]);

    if (empty($currentData['data'])) {
        echo json_encode(['success' => false, 'message' => 'Record not found']);
        return;
    }

    $current = $currentData['data'][0];
    $chk_data_id = $current['chk_data_id'];
    $sub_item = isset($current['sub_item']) ? trim($current['sub_item']) : '';

    // Set status based on action
    $status = ($action === 'submit') ? 1 : 0;

    // Update punch list data
    $sql = "UPDATE tbl_punch_list_data 
            SET resolution_by = :resolution_by,
            resolution_date = :resolution_date,
            resolution_remark = :resolution_remark,
            rechecking_by = :rechecking_by,
            rechecking_date = :rechecking_date,
            rechecking_remark = :rechecking_remark,
            work_hrs = :work_hrs,
            code = :code,
            sub_item = :sub_item,
            status = :status
            WHERE id = :id";

    $params = [
            ':id' => $input['id'],
            ':resolution_by' => $input['resolution_by'],
            ':resolution_date' => $input['resolution_date'],
            ':resolution_remark' => $input['resolution_remark'],
            ':rechecking_by' => $input['rechecking_by'],
            ':rechecking_date' => $input['rechecking_date'],
            ':rechecking_remark' => $input['rechecking_remark'],
            ':work_hrs' => empty($input['work_hrs']) ? null : $input['work_hrs'],
            ':code' => empty($input['code']) ? null : $input['code'],
            ':sub_item' => $sub_item,
            ':status' => $status
    ];

    $result = DbManager::fetchPDOQuery('spectra_db', $sql, $params);

    if ($result) {
        // Insert into punch list user details
        $sql_user_details = "INSERT INTO tbl_punch_list_user_details 
                            (pl_id, order_no, item_no, checklist_id, line_id, product_id, station_id,
                            reference, chk_data_id, description, finding_by, finding_date,
                            resolution_by, resolution_date, resolution_remark,
                            rechecking_by, rechecking_date, rechecking_remark,
                            work_hrs, code, sub_item, user_id, user_name, role_id, role_name, status)
                            VALUES 
                            (:pl_id, :order_no, :item_no, :checklist_id, :line_id, :product_id, :station_id,
                            :reference, :chk_data_id, :description, :finding_by, :finding_date,
                            :resolution_by, :resolution_date, :resolution_remark,
                            :rechecking_by, :rechecking_date, :rechecking_remark,
                            :work_hrs, :code, :sub_item, :user_id, :user_name, :role_id, :role_name, :status)";

        // Insert into user details table
        $user_params = [
                ':pl_id' => $current['id'],
                ':order_no' => $current['order_no'],
                ':item_no' => $current['item_no'],
                ':checklist_id' => $current['checklist_id'],
                ':line_id' => $current['line_id'],
                ':product_id' => $current['product_id'],
                ':station_id' => $current['station_id'],
                ':reference' => $current['reference'],
                ':chk_data_id' => $chk_data_id,
                ':description' => $input['description'],
                ':finding_by' => $input['finding_by'],
                ':finding_date' => $input['finding_date'],
                ':resolution_by' => $input['resolution_by'],
                ':resolution_date' => $input['resolution_date'],
                ':resolution_remark' => $input['resolution_remark'],
                ':rechecking_by' => $input['rechecking_by'],
                ':rechecking_date' => $input['rechecking_date'],
                ':rechecking_remark' => $input['rechecking_remark'],
                ':work_hrs' => empty($input['work_hrs']) ? null : $input['work_hrs'],
                ':code' => empty($input['code']) ? null : $input['code'],
                ':sub_item' => $sub_item,
                ':user_id' => $user_id,
                ':user_name' => $user_name,
                ':role_id' => $role_id,
                ':role_name' => $role_name,
                ':status' => $status
            ];
        
        $user_details_result = DbManager::fetchPDOQuery('spectra_db', $sql_user_details, $user_params);

        // If submit action, update checklist data status
        if ($action === 'submit' && $chk_data_id) {
            // Update checklist data
            $update_checklist = "UPDATE tbl_checklist_data SET status = 1 WHERE id = :chk_data_id";
            DbManager::fetchPDOQuery('spectra_db', $update_checklist, [':chk_data_id' => $chk_data_id]);

            // Update checklist user details
            $update_checklist_user = "INSERT INTO tbl_checklist_user_details (cl_id, order_no, item_no, checklist_id, line_id, 
                               product_id, station_id, checklist_name, project_name, order_processor, panel_type, 
                               production_order_no, location_name, typical_name, sub_item, is_component_product, ur_value, ik_value, ir_value, 
                               reference_to_traning_document, item_id, checklist_item, add_on_type, add_on_value, 
                               worker_status, work_person, remark, user_id, user_name, role_id, role_name, status) 
                               VALUES (:cl_id, :order_no, :item_no, :checklist_id, :line_id, :product_id, :station_id, 
                               :checklist_name, :project_name, :order_processor, :panel_type, :production_order_no, 
                               :location_name, :typical_name, :sub_item, :is_component_product, :ur_value, :ik_value, :ir_value, :reference_to_traning_document, 
                               :item_id, :checklist_item, :add_on_type, :add_on_value, :worker_status, :work_person, :remark, :user_id, 
                               :user_name, :role_id, :role_name, :status)";

            DbManager::fetchPDOQuery('spectra_db', $update_checklist_user, [
                ':cl_id' => $chk_data_id,
                ':order_no' => $current['order_no'],
                ':item_no' => $current['item_no'],
                ':checklist_id' => $current['checklist_id'],
                ':line_id' => $current['line_id'],
                ':product_id' => $current['product_id'],
                ':station_id' => $current['station_id'],
                ':checklist_name' => $current['checklist_name'],
                ':project_name' => $current['project_name'],
                ':order_processor' => $current['order_processor'],
                ':panel_type' => $current['panel_type'],
                ':production_order_no' => $current['production_order_no'],
                ':location_name' => $current['location_name'],
                ':typical_name' => $current['typical_name'],
                ':sub_item' => $sub_item,
                ':is_component_product' => $current['is_component_product'],
                ':ur_value' => $current['ur_value'],
                ':ik_value' => $current['ik_value'],
                ':ir_value' => $current['ir_value'],
                ':reference_to_traning_document' => $current['reference_to_traning_document'],
                ':item_id' => $current['item_id'],
                ':checklist_item' => $current['checklist_item'],
                ':add_on_type' => $current['add_on_type'],
                ':add_on_value' => $current['add_on_value'],
                ':worker_status' => $current['worker_status'],
                ':work_person' => $current['work_person'],
                ':remark' => $current['remark'],
                ':user_id' => $user_id,
                ':user_name' => $user_name,
                ':role_id' => $role_id,
                ':role_name' => $role_name,
                ':status' => $status
            ]);
        }

        echo json_encode([
            'success' => true,
            'message' => 'Punch list updated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update punch list'
        ]);
    }
}

function getSuggestions() {
    $search = $_POST['search'] ?? '';
    $field = $_POST['field'] ?? '';
    $params = [':search' => "%$search%"];
    
    switch ($field) {
        case 'order':
            $sql = "SELECT DISTINCT order_no as value 
                   FROM tbl_checklist_data 
                   WHERE order_no LIKE :search 
                   ORDER BY order_no 
                   LIMIT 10";
            break;
            
        case 'item':
            $sql = "SELECT DISTINCT item_no as value 
                   FROM tbl_checklist_data 
                   WHERE item_no LIKE :search 
                   ORDER BY item_no 
                   LIMIT 10";
            break;
            
        case 'checklist':
            $sql = "SELECT DISTINCT checklist_name as value 
                   FROM tbl_chk_checklist 
                   WHERE checklist_name LIKE :search 
                   ORDER BY checklist_name 
                   LIMIT 10";
            break;
            
        case 'finding_by':
            $sql = "SELECT DISTINCT finding_by as value 
                   FROM tbl_punch_list_data 
                   WHERE finding_by LIKE :search 
                   ORDER BY finding_by 
                   LIMIT 10";
            break;

        case 'product':
            $sql = "SELECT DISTINCT product_name as value 
                   FROM tbl_chk_product 
                   WHERE product_name LIKE :search 
                   ORDER BY product_name 
                   LIMIT 10";
            break;

        case 'user':
            $sql = "SELECT DISTINCT user_name as value 
                   FROM tbl_checklist_data 
                   WHERE user_name LIKE :search 
                   ORDER BY user_name 
                   LIMIT 10";
            break;    
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid field type']);
            return;
    }

    $result = DbManager::fetchPDOQuery('spectra_db', $sql, $params);
    echo json_encode(['success' => true, 'data' => $result['data']]);
}

function uploadImages() {
    $id = $_POST['id'];
    $section = $_POST['section'];
    $images = $_FILES['images'];

    if (count($images['name']) > 3) {
        echo json_encode(['success' => false, 'message' => 'Maximum 3 images allowed']);
        return;
    }

    $uploadedImages = [];
    foreach ($images['tmp_name'] as $key => $tmp_name) {
        $filename = uniqid() . '_' . $images['name'][$key];
        $path = '../uploads/' . $filename;
        
        if (move_uploaded_file($tmp_name, $path)) {
            // Create thumbnail
            $thumbnail = createThumbnail($path, '../uploads/thumbnails/' . $filename);
            
            // Save to database
            $sql = "INSERT INTO tbl_punch_list_images 
                    (punch_list_id, section, image_path, thumbnail_path) 
                    VALUES (:id, :section, :path, :thumbnail)";
            
            $params = [
                ':id' => $id,
                ':section' => $section,
                ':path' => $path,
                ':thumbnail' => $thumbnail
            ];
            
            DbManager::fetchPDOQuery('spectra_db', $sql, $params);
            $uploadedImages[] = ['full' => $path, 'thumbnail' => $thumbnail];
        }
    }

    echo json_encode(['success' => true, 'images' => $uploadedImages]);
}

function getImages() {
    $id = $_POST['id'];
    $section = $_POST['section'];
    
    $sql = "SELECT * FROM tbl_punch_list_images 
            WHERE punch_list_id = :id AND section = :section";
    
    $result = DbManager::fetchPDOQueryData('spectra_db', $sql, [
        ':id' => $id,
        ':section' => $section
    ]);

    if (!empty($result['data'])) {
        foreach ($result['data'] as &$image) {
            // Just store the filename, not the full path
            $image['filename'] = basename($image['image_path']);
        }
    }

    echo json_encode([
        'success' => true,
        'images' => $result['data'] ?? []
    ]);
}

function createThumbnail($source, $destination) {
    $info = getimagesize($source);
    $width = 50;
    $height = 50;

    switch ($info[2]) {
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($source);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($source);
            break;
        default:
            return false;
    }

    $thumb = imagecreatetruecolor($width, $height);
    imagecopyresampled($thumb, $image, 0, 0, 0, 0, $width, $height, $info[0], $info[1]);
    
    imagejpeg($thumb, $destination);
    return $destination;
}

function deleteImage() {
    try {
        // Get the image ID from the POST request
        $imageId = isset($_POST['id']) ? $_POST['id'] : null;

        if (!$imageId) {
            throw new Exception('Image ID is required');
        }

        // First, get the image details from the database
        $sql = "SELECT image_path, thumbnail_path FROM tbl_punch_list_images WHERE id = :id";
        $params = [':id' => $imageId];
        
        $result = DbManager::fetchPDOQueryData('spectra_db', $sql, $params);

        if (empty($result['data'])) {
            throw new Exception('Image not found');
        }

        $imagePath = $result['data'][0]['image_path'];
        $thumbnailPath = $result['data'][0]['thumbnail_path'];

        // Delete the physical files
        if (file_exists($imagePath)) {
            if (!unlink($imagePath)) {
                throw new Exception('Failed to delete original image file');
            }
        }

        if (file_exists($thumbnailPath)) {
            if (!unlink($thumbnailPath)) {
                throw new Exception('Failed to delete thumbnail file');
            }
        }

        // Delete the database record
        $deleteSql = "DELETE FROM tbl_punch_list_images WHERE id = :id";
        $deleteParams = [':id' => $imageId];
        
        $deleteResult = DbManager::fetchPDOQuery('spectra_db', $deleteSql, $deleteParams);

        if ($deleteResult === false) {
            throw new Exception('Failed to delete image record from database');
        }

        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Image deleted successfully'
        ]);

    } catch (Exception $e) {
        // Log the error (you should implement proper error logging)
        error_log('Error deleting image: ' . $e->getMessage());

        // Return error response
        echo json_encode([
            'success' => false,
            'message' => 'Failed to delete image: ' . $e->getMessage()
        ]);
    }
}

function exportAll() {
    // Get filter parameters
    $orderFilter = $_POST['orderFilter'] ?? '';
    $itemFilter = $_POST['itemFilter'] ?? '';
    $checklistFilter = $_POST['checklistFilter'] ?? '';
    $findingByFilter = $_POST['findingByFilter'] ?? '';
    $dateRangeFilter = $_POST['dateRangeFilter'] ?? '';
    $stationFilter = $_POST['stationFilter'] ?? '';
    $applyingFilters = $_POST['applyingFilters'] ?? 'false';

    // Initialize parameters array
    $params = [];

    // Base query with proper JOIN to tbl_checklist_data
    $sql = "SELECT DISTINCT p.id,
            p.order_no,
            p.item_no,
            p.checklist_id,
            p.line_id,
            p.product_id,
            p.station_id,
            p.reference,
            p.description,
            p.finding_by,
            p.finding_date,
            p.resolution_by,
            p.resolution_date,
            p.resolution_remark,
            p.rechecking_by,
            p.rechecking_date,
            p.rechecking_remark,
            p.work_hrs,
            p.code,
            p.chk_data_id,
            p.sub_item,
            p.is_component_product,
            p.status,
            p.created_at,
            p.updated_at,
            cd.panel_type,
            cd.location_name,
            cd.typical_name,
            s.station_name,
            chk.checklist_name
            FROM tbl_punch_list_data p 
            LEFT JOIN tbl_checklist_data cd ON (
                cd.order_no = p.order_no 
                AND cd.item_no = p.item_no 
                AND cd.checklist_id = p.checklist_id
                AND cd.line_id = p.line_id
                AND cd.product_id = p.product_id
                AND cd.station_id = p.station_id
                AND cd.sub_item = p.sub_item
            )
            LEFT JOIN tbl_chk_station s ON p.station_id = s.id 
            LEFT JOIN tbl_chk_checklist chk ON p.checklist_id = chk.checklist_id
            WHERE 1=1 AND p.status = '0'";

    // Only apply filters if they're being submitted
    if ($applyingFilters === 'true') {
        if (!empty($orderFilter)) {
            $sql .= " AND p.order_no = :orderFilter";
            $params[':orderFilter'] = $orderFilter;
        }
        if (!empty($itemFilter)) {
            $sql .= " AND p.item_no = :itemFilter";
            $params[':itemFilter'] = $itemFilter;
        }
        if (!empty($checklistFilter)) {
            $sql .= " AND chk.checklist_name = :checklistFilter";
            $params[':checklistFilter'] = $checklistFilter;
        }
        if (!empty($findingByFilter)) {
            $sql .= " AND p.finding_by = :findingByFilter";
            $params[':findingByFilter'] = $findingByFilter;
        }
        if (!empty($dateRangeFilter)) {
            $dates = explode(' - ', $dateRangeFilter);
            if (count($dates) === 2) {
                $sql .= " AND DATE(p.finding_date) BETWEEN :startDate AND :endDate";
                $params[':startDate'] = trim($dates[0]);
                $params[':endDate'] = trim($dates[1]);
            }
        }
        if (!empty($stationFilter)) {
            $sql .= " AND p.station_id = :stationFilter";
            $params[':stationFilter'] = $stationFilter;
        }
    }

    // Add ordering
    $sql .= " ORDER BY p.finding_date DESC";

    try {
        // Get filtered data
        $result = DbManager::fetchPDOQuery('spectra_db', $sql, $params);
        
        // For exact matching of order_no if that's what's needed
        if (!empty($orderFilter) && is_numeric($orderFilter)) {
            // Filter results to match exact order number
            $exactMatches = array_filter($result['data'] ?? [], function($item) use ($orderFilter) {
                return $item['order_no'] == $orderFilter;
            });
            
            // If we found exact matches, replace the result data
            if (!empty($exactMatches)) {
                $result['data'] = array_values($exactMatches);
            }
        }

        echo json_encode([
            'success' => true,
            'data' => $result['data'] ?? []
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error fetching data: ' . $e->getMessage()
        ]);
    }
    exit;
}

function getFooterInfo() {
    $checklist_id = isset($_POST['checklist_id']) ? trim($_POST['checklist_id']) : '';
    $line_id = isset($_POST['line_id']) ? trim($_POST['line_id']) : '';
    $product_id = isset($_POST['product_id']) ? trim($_POST['product_id']) : '';
    
    // Default footer values
    $defaultDocumentDescription = 'Annex 7 to Documented Procedure No. 2-00-00-2-74-529';
    $defaultRevision = 'Revision: 00';
    
    if (empty($checklist_id) || empty($line_id) || empty($product_id)) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing required parameters',
            'document_description' => $defaultDocumentDescription,
            'revision' => 'Revision: '.$defaultRevision
        ]);
        exit;
    }
    
    try {
        // Query to get footer information from tbl_checklist_data
        $query = "SELECT 
                    document_description,
                    revision
                  FROM tbl_chk_checklist
                  WHERE checklist_id = :checklist_id
                    AND line_id = :line_id
                    AND product_id = :product_id
                  ORDER BY id DESC
                  LIMIT 1";
        
        $check_params = [
                    ':checklist_id' => $checklist_id,
                    ':line_id' => $line_id,
                    ':product_id' => $product_id
        ];

        $result = DbManager::fetchPDOQuery('spectra_db', $query, $check_params)["data"][0];

        if ($result) {
            // Use database values, fallback to defaults if empty
            $documentDescription = !empty($result['document_description']) 
                ? $result['document_description'] 
                : $defaultDocumentDescription;
            $revision = !empty($result['revision']) 
                ? $result['revision'] 
                : $defaultRevision;
            
            echo json_encode([
                'success' => true,
                'document_description' => $documentDescription,
                'revision' => 'Revision: '.$revision
            ]);
        } else {
            // No record found, return defaults
            echo json_encode([
                'success' => false,
                'message' => 'No record found, using defaults',
                'document_description' => $defaultDocumentDescription,
                'revision' => 'Revision: '.$defaultRevision
            ]);
        }
    } catch (Exception $e) {
        // Error occurred, return defaults
        error_log('Error in get_footer_info: ' . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage(),
            'document_description' => $defaultDocumentDescription,
            'revision' => 'Revision: '.$defaultRevision
        ]);
    }
    exit;
}

function getChecklistDetails() {
    $draw = $_POST['draw'];
    $start = $_POST['start'];
    $length = $_POST['length'];
    $search = $_POST['search']['value'];
    $orderColumn = $_POST['order'][0]['column'];
    $orderDir = $_POST['order'][0]['dir'];

    // Get filter values
    $orderFilter = $_POST['orderFilter'] ?? '';
    $itemFilter = $_POST['itemFilter'] ?? '';
    $checklistFilter = $_POST['checklistFilter'] ?? '';
    $userFilter = $_POST['userFilter'] ?? '';
    $dateRangeFilter = $_POST['dateRangeFilter'] ?? '';
    $productFilter = $_POST['productFilter'] ?? '';
    $stationFilter = $_POST['stationFilter'] ?? '';

    // Initialize parameters array
    $params = [];

    // Base query with JOINs to get names instead of IDs
    $sql = "SELECT 
            c.id,
            c.order_no,
            c.item_no,
            chk.checklist_name,
            ln.line_name,
            prod.product_name,
            st.station_name,
            c.panel_type,
            c.location_name,
            c.typical_name,
            c.sub_item,
            c.project_name,
            c.order_processor,
            c.production_order_no,
            c.user_name,
            c.create_date,
            c.updated_date,
            CASE WHEN MIN(c.status) = 1 THEN 1 ELSE 0 END AS status
            FROM tbl_checklist_data c 
            LEFT JOIN (
                SELECT checklist_id, checklist_name, 
                    ROW_NUMBER() OVER (PARTITION BY checklist_id ORDER BY id) AS rn
                FROM tbl_chk_checklist
            ) chk ON c.checklist_id = chk.checklist_id AND chk.rn = 1
            LEFT JOIN tbl_line ln ON c.line_id = ln.id
            LEFT JOIN tbl_chk_product prod ON c.product_id = prod.id
            LEFT JOIN tbl_chk_station st ON c.station_id = st.id
            WHERE 1=1";

    // Only apply filters if they're being submitted
    if (isset($_POST['applyingFilters']) && $_POST['applyingFilters'] === 'true') {
        if (!empty($orderFilter)) {
            $sql .= " AND c.order_no = :orderFilter";
            $params[':orderFilter'] = $orderFilter;
        }
        if (!empty($itemFilter)) {
            $sql .= " AND c.item_no = :itemFilter";
            $params[':itemFilter'] = $itemFilter;
        }
        if (!empty($checklistFilter)) {
            $sql .= " AND chk.checklist_name = :checklistFilter";
            $params[':checklistFilter'] = $checklistFilter;
        }
        if (!empty($userFilter)) {
            $sql .= " AND c.user_name = :userFilter";
            $params[':userFilter'] = $userFilter;
        }
        if (!empty($productFilter)) {
            $sql .= " AND prod.product_name = :productFilter";
            $params[':productFilter'] = $productFilter;
        }
        if (!empty($dateRangeFilter)) {
            $dates = explode(' - ', $dateRangeFilter);
            if (count($dates) === 2) {
                $sql .= " AND DATE(c.create_date) BETWEEN :startDate AND :endDate";
                $params[':startDate'] = trim($dates[0]);
                $params[':endDate'] = trim($dates[1]);
            }
        }
        if (!empty($stationFilter)) {
            $sql .= " AND st.id = :stationFilter";
            $params[':stationFilter'] = $stationFilter;
        }
    }

    // Add search if it exists
    if (!empty($search)) {
        $sql .= " AND (c.order_no LIKE :search 
                 OR c.panel_type LIKE :search 
                 OR c.item_no LIKE :search 
                 OR c.location_name LIKE :search
                 OR c.typical_name LIKE :search
                 OR c.sub_item LIKE :search
                 OR chk.checklist_name LIKE :search 
                 OR ln.line_name LIKE :search
                 OR prod.product_name LIKE :search
                 OR st.station_name LIKE :search
                 OR c.project_name LIKE :search 
                 OR c.user_name LIKE :search
                 OR DATE_FORMAT(c.create_date, '%Y-%m-%d %H:%i:%s') LIKE :search
                 OR DATE_FORMAT(c.updated_date, '%Y-%m-%d %H:%i:%s') LIKE :search
                 OR c.remark LIKE :search)";
        $params[':search'] = "%$search%";
    }

    // Add GROUP BY clause - NOW INCLUDING sub_item
    $sql .= " GROUP BY 
            c.order_no,
            c.item_no,
            c.checklist_id,
            c.line_id,
            c.product_id,
            c.station_id,
            c.sub_item";

    // Get total records count (before filtering)
    $totalSql = "SELECT COUNT(DISTINCT CONCAT(order_no, '-', item_no, '-', checklist_id, '-', line_id, '-', product_id, '-', station_id, '-', COALESCE(sub_item, ''))) as count FROM tbl_checklist_data";
    $totalRecords = DbManager::fetchPDOQuery('spectra_db', $totalSql)['data'][0]['count'];

    // Get filtered records count using subquery
    $countSql = "SELECT COUNT(*) as count FROM ($sql) as counted";
    $filteredRecords = DbManager::fetchPDOQuery('spectra_db', $countSql, $params)['data'][0]['count'] ?? 0;

    // Add ordering - Default to create_date DESC if no order specified
    $columns = [
        0 => 'c.order_no',
        1 => 'c.panel_type',
        2 => 'c.item_no',
        3 => 'c.location_name',
        4 => 'chk.checklist_name',
        5 => 'ln.line_name',
        6 => 'prod.product_name',
        7 => 'st.station_name',
        8 => 'c.user_name',
        9 => 'c.create_date',
        10 => 'c.typical_name',
        11 => 'c.sub_item',
        12 => 'c.updated_date'
    ];
    
    // Use create_date DESC as default ordering (latest first)
    $orderColumn = $columns[$orderColumn] ?? 'c.create_date';
    
    // If no specific order direction is provided, use DESC for create_date
    if ($orderColumn === 'c.create_date' && empty($orderDir)) {
        $orderDir = 'DESC';
    } else {
        $orderDir = strtoupper($orderDir) === 'ASC' ? 'ASC' : 'DESC';
    }
    
    $sql .= " ORDER BY $orderColumn $orderDir";

    // Add pagination
    if ($length > 0) {
        $sql .= " LIMIT :start, :length";
        $params[':start'] = (int)$start;
        $params[':length'] = (int)$length;
    }

    // Get filtered data
    $result = DbManager::fetchPDOQuery('spectra_db', $sql, $params);

    // Format the response for DataTables
    $response = [
        'draw' => (int)$draw,
        'recordsTotal' => (int)$totalRecords,
        'recordsFiltered' => (int)$filteredRecords,
        'data' => $result['data'] ?? []
    ];

    echo json_encode($response);
}

function exportAllDetails() {
    // Get filter parameters
    $orderFilter = $_POST['orderFilter'] ?? '';
    $itemFilter = $_POST['itemFilter'] ?? '';
    $checklistFilter = $_POST['checklistFilter'] ?? '';
    $userFilter = $_POST['userFilter'] ?? '';
    $dateRangeFilter = $_POST['dateRangeFilter'] ?? '';
    $productFilter = $_POST['productFilter'] ?? '';
    $stationFilter = $_POST['stationFilter'] ?? '';
    $applyingFilters = $_POST['applyingFilters'] ?? 'false';

    // Initialize parameters array
    $params = [];

    // Base query
    $sql = "SELECT 
            c.id,
            c.order_no,
            c.item_no,
            chk.checklist_name,
            ln.line_name,
            prod.product_name,
            st.station_name,
            c.panel_type,
            c.location_name,
            c.typical_name,
            c.sub_item,
            c.project_name,
            c.order_processor,
            c.production_order_no,
            c.user_name,
            c.create_date,
            c.updated_date,
            c.status
            FROM tbl_checklist_data c 
            LEFT JOIN tbl_chk_checklist chk ON c.checklist_id = chk.checklist_id
            LEFT JOIN tbl_line ln ON c.line_id = ln.id
            LEFT JOIN tbl_chk_product prod ON c.product_id = prod.id
            LEFT JOIN tbl_chk_station st ON c.station_id = st.id
            WHERE 1=1";

    // Only apply filters if they're being submitted
    if ($applyingFilters === 'true') {
        if (!empty($orderFilter)) {
            $sql .= " AND c.order_no = :orderFilter";
            $params[':orderFilter'] = $orderFilter;
        }
        if (!empty($itemFilter)) {
            $sql .= " AND c.item_no = :itemFilter";
            $params[':itemFilter'] = $itemFilter;
        }
        if (!empty($checklistFilter)) {
            $sql .= " AND chk.checklist_name = :checklistFilter";
            $params[':checklistFilter'] = $checklistFilter;
        }
        if (!empty($userFilter)) {
            $sql .= " AND c.user_name = :userFilter";
            $params[':userFilter'] = $userFilter;
        }
        if (!empty($productFilter)) {
            $sql .= " AND prod.product_name = :productFilter";
            $params[':productFilter'] = $productFilter;
        }
        if (!empty($dateRangeFilter)) {
            $dates = explode(' - ', $dateRangeFilter);
            if (count($dates) === 2) {
                $sql .= " AND DATE(c.create_date) BETWEEN :startDate AND :endDate";
                $params[':startDate'] = trim($dates[0]);
                $params[':endDate'] = trim($dates[1]);
            }
        }
        if (!empty($stationFilter)) {
            $sql .= " AND st.id = :stationFilter";
            $params[':stationFilter'] = $stationFilter;
        }
    }

    $sql .= " GROUP BY 
            c.order_no,
            c.item_no,
            c.checklist_id,
            c.line_id,
            c.product_id,
            c.station_id,
            c.sub_item";            
    // Add ordering
    $sql .= " ORDER BY c.create_date DESC";

    try {
        // Get filtered data
        $result = DbManager::fetchPDOQuery('spectra_db', $sql, $params);
        
        echo json_encode([
            'success' => true,
            'data' => $result['data'] ?? []
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error fetching data: ' . $e->getMessage()
        ]);
    }
    exit;
}
?>