<?php
include_once '../../api/controllers/BaseController.php';
include_once '../../api/models/Journals.php';
header('Content-Type: application/json; charset=utf-8');

class CableController extends BaseController {

    public function getRALColors() {
        try {
            $query = "SELECT id, ral_code, hex_code, color_name, tr_color FROM dto_cable_ral_codes ORDER BY ral_code ASC";
            $data = DbManager::fetchPDOQueryData('dto_configurator', $query)['data'];
            echo json_encode($data);
            exit();
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to load RAL codes']);
            exit();
        }
    }

    public function getCableProducts() {
        try {
            $query = "SELECT id, product_type FROM products WHERE id <= 7 ORDER BY product_type ASC";
            $data = DbManager::fetchPDOQueryData('dto_configurator', $query)['data'];
            echo json_encode($data);
            exit();
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to load products']);
            exit();
        }
    }

    public function createCable() {
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Insert Cable Code Request");
        Journals::saveJournal("PROCESSING | Insert Cable Code Request", PAGE_DTO_CABLE_CODES, DTO_CABLE_CODES_ADD_CABLE_ITEM, ACTION_PROCESSING, '', "Add DTO Cable Item");

        $cableTypeCategory = $_POST['cableTypeCategory'];
        $definition = trim($_POST['definition']);
        $notes = trim($_POST['notes']);
        $numberHarness = trim($_POST['numberHarness']);
        $vthCode = trim($_POST['vthCode']);
        $numberDrawing = trim($_POST['numberDrawing']);
        $cableType = trim($_POST['cableType']);
        $cableCrossSection = trim($_POST['cableCrossSection']);
        $cableLengthType = trim($_POST['cableLengthType']);
        $placeToUse = trim($_POST['placeToUse']);
        $panelWidth = $_POST['panelWidth'] ?: null;
        $core = $_POST['core'] ?: null;
        $orderNo = trim($_POST['orderNo']);
        $additionalInfo = trim($_POST['additionalInfo']);

        // Decode JSON strings
        $productTypes = json_decode($_POST['productTypes'] ?? '[]', true);
        $colors = json_decode($_POST['colors'] ?? '{}', true);
        $cthSpecific = json_decode($_POST['cthSpecific'] ?? '{}', true);

        SharedManager::saveLog('log_dtoconfigurator', "PROCESSING | Create Cable Code: " . $_POST['vthCode']);
        Journals::saveJournal("PROCESSING | Create Cable Code", PAGE_DTO_CABLE_CODES, DTO_CABLE_CODES_ADD_CABLE_ITEM, ACTION_PROCESSING, '', "Add Cable Code");

        if (!SharedManager::hasAccessRight(35, 49)) {
            SharedManager::saveLog('log_dtoconfigurator', "ERROR | Unauthorized User for Create Cable Code");
            Journals::saveJournal("ERROR | Unauthorized User for Create Cable Code", PAGE_DTO_CABLE_CODES, DTO_CABLE_CODES_ADD_CABLE_ITEM, ACTION_ERROR, '', "Add Cable Code");
            returnHttpResponse(400, 'Unauthorized User for Create Cable Code');
            exit();
        }

        try {
            // Validate required fields
            if (empty($cableTypeCategory) || empty($definition) || empty($numberHarness) || empty($vthCode) || empty($cableType)) {
                throw new Exception('Required fields are missing');
            }

            // Validate cable type category
            if (!in_array($cableTypeCategory, ['VTH', 'CTH'])) {
                throw new Exception('Invalid cable type category');
            }

            // Check for duplicate cable code
            $checkQuery = "SELECT id FROM dto_cable_codes WHERE cable_code = :cable_code AND is_active = 1";
            $existingCable = DbManager::fetchPDOQueryData('dto_configurator', $checkQuery, [':cable_code' => $vthCode])['data'];

            if (!empty($existingCable)) {
                echo json_encode(['success' => false, 'message' => 'Cable code already exists']);
                exit();
            }

            // Insert main cable record
            $cableId = $this->insertCableCode($cableTypeCategory, $definition, $notes, $numberHarness, $vthCode,
                $numberDrawing, $cableType, $cableCrossSection, $cableLengthType, $colors, $cthSpecific,
                $placeToUse, $panelWidth, $core, $orderNo, $additionalInfo);

            // Insert product relationships
            if (!empty($productTypes)) {
                $this->insertCableProducts($cableId, $productTypes);
            }

            SharedManager::saveLog('log_dtoconfigurator', "CREATED | Cable Code Created Successfully with ID: " . $cableId);
            Journals::saveJournal("CREATED | Cable Code Created Successfully", PAGE_DTO_CABLE_CODES, DTO_CABLE_CODES_ADD_CABLE_ITEM, ACTION_CREATED, '', "Add Cable Code Item");

            echo json_encode([
                'success' => true,
                'message' => 'Cable code created successfully',
                'cable_id' => $cableId
            ]);
            exit();

        } catch (Exception $e) {
            SharedManager::saveLog('log_dtoconfigurator', "ERROR | Create Cable Code Error: " . $e->getMessage());
            Journals::saveJournal("ERROR | Create Cable Code Error", PAGE_DTO_CABLE_CODES, DTO_CABLE_CODES_ADD_CABLE_ITEM, ACTION_ERROR, '', "Add Cable Code");
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit();
        }
    }

    private function insertCableCode($cableTypeCategory, $definition, $notes, $numberHarness, $vthCode,
                                     $numberDrawing, $cableType, $cableCrossSection, $cableLengthType, $colors, $cthSpecific,
                                     $placeToUse, $panelWidth, $core, $orderNo, $additionalInfo) {

        $query = "INSERT INTO dto_cable_codes (
        cable_type_category, definition, tr_notes, number_harness, cable_code,
        number_drawing, cable_type, cable_cross_section, cable_length_type,
        t5l1_color_a, t5l1_color_n, t5l2_color_a1, t5l2_color_n1, t5l2_color_a2, t5l2_color_n2,
        t1l1_1s1, t1l1_1s2, t1l1_1s3, t1l2_1s1, t1l2_1s2, t1l2_1s3, t1l3_1s1, t1l3_1s2, t1l3_1s3,
        cable_length_total, cable_length_groups, total_cable_length, ct_in_rear_box,
        place_to_use, panel_width, core, order_no, additional_info, created_by
    )";

        // Helper function to convert empty strings to null
        $toNullIfEmpty = function($value) {
            return (empty($value)) ? null : $value;
        };

        $params = [];
        $params[] = [
            $cableTypeCategory,
            $definition,
            $notes,
            $numberHarness,
            $vthCode,
            $numberDrawing,
            $cableType,
            $cableCrossSection,
            $cableLengthType,
            // VTH colors - convert empty strings to null
            $cableTypeCategory === 'VTH' ? $toNullIfEmpty($colors['t5l1ColorA'] ?? null) : null,
            $cableTypeCategory === 'VTH' ? $toNullIfEmpty($colors['t5l1ColorN'] ?? null) : null,
            $cableTypeCategory === 'VTH' ? $toNullIfEmpty($colors['t5l2ColorA1'] ?? null) : null,
            $cableTypeCategory === 'VTH' ? $toNullIfEmpty($colors['t5l2ColorN1'] ?? null) : null,
            $cableTypeCategory === 'VTH' ? $toNullIfEmpty($colors['t5l2ColorA2'] ?? null) : null,
            $cableTypeCategory === 'VTH' ? $toNullIfEmpty($colors['t5l2ColorN2'] ?? null) : null,
            // CTH colors - convert empty strings to null
            $cableTypeCategory === 'CTH' ? $toNullIfEmpty($colors['t1l1_1s1'] ?? null) : null,
            $cableTypeCategory === 'CTH' ? $toNullIfEmpty($colors['t1l1_1s2'] ?? null) : null,
            $cableTypeCategory === 'CTH' ? $toNullIfEmpty($colors['t1l1_1s3'] ?? null) : null,
            $cableTypeCategory === 'CTH' ? $toNullIfEmpty($colors['t1l2_1s1'] ?? null) : null,
            $cableTypeCategory === 'CTH' ? $toNullIfEmpty($colors['t1l2_1s2'] ?? null) : null,
            $cableTypeCategory === 'CTH' ? $toNullIfEmpty($colors['t1l2_1s3'] ?? null) : null,
            $cableTypeCategory === 'CTH' ? $toNullIfEmpty($colors['t1l3_1s1'] ?? null) : null,
            $cableTypeCategory === 'CTH' ? $toNullIfEmpty($colors['t1l3_1s2'] ?? null) : null,
            $cableTypeCategory === 'CTH' ? $toNullIfEmpty($colors['t1l3_1s3'] ?? null) : null,
            // CTH specific fields
            $cableTypeCategory === 'CTH' ? ($cthSpecific['cableLengthTotal'] ?? null) : null,
            $cableTypeCategory === 'CTH' ? ($cthSpecific['cableLengthGroups'] ?? null) : null,
            $cableTypeCategory === 'CTH' ? ($cthSpecific['totalCableLength'] ?? null) : null,
            $cableTypeCategory === 'CTH' ? ($cthSpecific['ctInRearBox'] ?? null) : null,
            // Common fields
            $placeToUse,
            $panelWidth,
            $core,
            $orderNo,
            $additionalInfo,
            SharedManager::$fullname ?? 'system'
        ];

        $response = DbManager::fetchInsert('dto_configurator', $query, $params);
        return $response["pdoConnection"]->lastInsertId();
    }

    private function insertCableProducts($cableId, $productIds) {
        if (!is_array($productIds)) {
            return;
        }

        foreach ($productIds as $productId) {
            if (!empty($productId)) {
                $query = "INSERT INTO dto_cable_codes_products (cable_code_id, product_id)";
                $params = [];
                $params[] = [$cableId, $productId];
                DbManager::fetchInsert('dto_configurator', $query, $params);
            }
        }
    }


    public function getCableItems() {
        try {
            SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Get CTH/VTH Cable Items for Index Page");
            Journals::saveJournal("PROCESSING | Get CTH/VTH Cable Items for Index Page", PAGE_DTO_CABLE_CODES, DTO_CABLE_CODES_INDEX, ACTION_PROCESSING, '', "DTO Cable Codes Index");

            $query = "SELECT *
                      FROM view_dto_cable_codes 
                      ORDER BY cable_code_id DESC";

            $rawData = DbManager::fetchPDOQueryData('dto_configurator', $query)['data'] ?? [];

            // Group data by cable_code_id
            $groupedData = [];

            foreach ($rawData as $row) {
                $itemId = $row['cable_code_id'];

                // If this cable item hasn't been added yet
                if (!isset($groupedData[$itemId])) {
                    $groupedData[$itemId] = [
                        'cable_code_id' => $row['cable_code_id'],
                        'cable_type_category' => $row['cable_type_category'],
                        'definition' => $row['definition'],
                        'tr_notes' => $row['tr_notes'],
                        'number_harness' => $row['number_harness'],
                        'cable_code' => $row['cable_code'],
                        'number_drawing' => $row['number_drawing'],
                        'cable_type' => $row['cable_type'],
                        'cable_cross_section' => $row['cable_cross_section'],
                        'cable_length_type' => $row['cable_length_type'],
                        // VTH Colors - ADD MISSING MAPPINGS
                        'vth_colors' => [
                            't5l1_a' => ['ral' => $row['t5l1_color_a_ral'], 'hex' => $row['t5l1_color_a_hex'], 'tr' => $row['t5l1_color_a_tr']],
                            't5l1_n' => ['ral' => $row['t5l1_color_n_ral'], 'hex' => $row['t5l1_color_n_hex'], 'tr' => $row['t5l1_color_n_tr']], // MISSING
                            't5l2_a1' => ['ral' => $row['t5l2_color_a1_ral'], 'hex' => $row['t5l2_color_a1_hex'], 'tr' => $row['t5l2_color_a1_tr']],
                            't5l2_n1' => ['ral' => $row['t5l2_color_n1_ral'], 'hex' => $row['t5l2_color_n1_hex'], 'tr' => $row['t5l2_color_n1_tr']], // MISSING
                            't5l2_a2' => ['ral' => $row['t5l2_color_a2_ral'], 'hex' => $row['t5l2_color_a2_hex'], 'tr' => $row['t5l2_color_a2_tr']],
                            't5l2_n2' => ['ral' => $row['t5l2_color_n2_ral'], 'hex' => $row['t5l2_color_n2_hex'], 'tr' => $row['t5l2_color_n2_tr']], // MISSING
                        ],
                        // CTH Colors - FIX PROPERTY NAMES AND ADD MISSING MAPPINGS
                        'cth_colors' => [
                            't1l1_s1' => ['ral' => $row['t1l1_1s1_ral'], 'hex' => $row['t1l1_1s1_hex'], 'tr' => $row['t1l1_1s1_tr']],
                            't1l1_s2' => ['ral' => $row['t1l1_1s2_ral'], 'hex' => $row['t1l1_1s2_hex'], 'tr' => $row['t1l1_1s2_tr']], // MISSING
                            't1l1_s3' => ['ral' => $row['t1l1_1s3_ral'], 'hex' => $row['t1l1_1s3_hex'], 'tr' => $row['t1l1_1s3_tr']], // MISSING
                            't1l2_s1' => ['ral' => $row['t1l2_1s1_ral'], 'hex' => $row['t1l2_1s1_hex'], 'tr' => $row['t1l2_1s1_tr']],
                            't1l2_s2' => ['ral' => $row['t1l2_1s2_ral'], 'hex' => $row['t1l2_1s2_hex'], 'tr' => $row['t1l2_1s2_tr']], // MISSING
                            't1l2_s3' => ['ral' => $row['t1l2_1s3_ral'], 'hex' => $row['t1l2_1s3_hex'], 'tr' => $row['t1l2_1s3_tr']], // MISSING
                            't1l3_s1' => ['ral' => $row['t1l3_1s1_ral'], 'hex' => $row['t1l3_1s1_hex'], 'tr' => $row['t1l3_1s1_tr']],
                            't1l3_s2' => ['ral' => $row['t1l3_1s2_ral'], 'hex' => $row['t1l3_1s2_hex'], 'tr' => $row['t1l3_1s2_tr']], // MISSING
                            't1l3_s3' => ['ral' => $row['t1l3_1s3_ral'], 'hex' => $row['t1l3_1s3_hex'], 'tr' => $row['t1l3_1s3_tr']], // MISSING
                        ],
                        'cable_length_total' => $row['cable_length_total'],
                        'cable_length_groups' => $row['cable_length_groups'],
                        'total_cable_length' => $row['total_cable_length'],
                        'ct_in_rear_box' => $row['ct_in_rear_box'],
                        'place_to_use' => $row['place_to_use'],
                        'panel_width' => $row['panel_width'],
                        'core' => $row['core'],
                        'order_no' => $row['order_no'],
                        'additional_info' => $row['additional_info'],
                        'created_by' => $row['created_by'],
                        'cable_created' => $row['cable_created'],
                        'cable_updated' => $row['cable_updated'],
                        'is_active' => $row['is_active'],
                        'products' => []
                    ];
                }

                // Add product to the products array (only if product_id exists)
                if (!empty($row['product_id'])) {
                    $groupedData[$itemId]['products'][] = [
                        'product_id' => $row['product_id'],
                        'product_type' => $row['product_type']
                    ];
                }
            }

            SharedManager::saveLog('log_dtoconfigurator',"RETURNED | Get CTH/VTH Cable Code Items for Index Page Successfully Returned.");
            Journals::saveJournal("RETURNED | Get CTH/VTH Cable Code Items for Index Page Successfully Returned.", PAGE_DTO_CABLE_CODES, DTO_CABLE_CODES_INDEX, ACTION_VIEWED, '', "DTO Cable Codes Index");

            echo json_encode(array_values($groupedData));
            exit();
        } catch (Exception $e) {
            SharedManager::saveLog('log_dtoconfigurator',"ERROR | Failed to load cable items.");
            Journals::saveJournal("ERROR | Failed to load cable items.", PAGE_DTO_CABLE_CODES, DTO_CABLE_CODES_INDEX, ACTION_ERROR, '', "DTO Cable Codes Index");

            echo json_encode(['success' => false, 'message' => 'Failed to load cable items']);
            exit();
        }
    }

    public function deleteCableItem()
    {
        $id = $_POST['id'];

        if (!SharedManager::hasAccessRight(35, 49)) {
            SharedManager::saveLog('log_dtoconfigurator', "ERROR | Unauthorized User for Delete Cable Code");
            Journals::saveJournal("ERROR | Unauthorized User for Delete Cable Code", PAGE_DTO_CABLE_CODES, DTO_CABLE_CODES_INDEX, ACTION_ERROR, '', "Delete Cable Code");
            echo json_encode(['success' => false, 'message' => 'Unauthorized User for Delete Cable Code']);
            exit();
        }

        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Delete Cable Item With ID : " . $id);
        Journals::saveJournal("PROCESSING | Delete Cable Item With ID : " . $id, PAGE_DTO_CABLE_CODES, DTO_CABLE_CODES_INDEX, ACTION_PROCESSING, '', "Delete DTO Cable Code");

        // Safe Delete
        $query = "UPDATE dto_cable_codes SET is_active = 0 WHERE id = :id";
        DbManager::fetchPDOQueryData('dto_configurator', $query, [':id' => $id])['data'];

        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Cable Item With ID : " . $id . " Successfully Deleted.");
        Journals::saveJournal("PROCESSING | Cable Item With ID : " . $id . " Successfully Deleted.", PAGE_DTO_CABLE_CODES, DTO_CABLE_CODES_INDEX, ACTION_VIEWED, '', "Delete DTO Cable Code");

    }
}

$controller = new CableController($_POST);

$response = match ($_GET['action']) {
    'getRALColors' => $controller->getRALColors(),
    'getCableProducts' => $controller->getCableProducts(),
    'getCableItems' => $controller->getCableItems(),
    default => ['status' => 400, 'message' => 'Invalid action'],
};

$response = match ($_POST['action']) {
    'createCable' => $controller->createCable(),
    'deleteCableItem' => $controller->deleteCableItem(),
    default => ['status' => 400, 'message' => 'Invalid action'],
};
?>