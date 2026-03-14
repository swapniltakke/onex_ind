<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
require_once $_SERVER["DOCUMENT_ROOT"] . "/cronjobs/CronDbManager.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/snowflake/SnowflakeQuery.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/cronjobs/jobs/snowflake/spiridon/rpa_common.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/SharedManager.php";
require_once $_SERVER["DOCUMENT_ROOT"] . '/shared/shared.php';

class NachbauJsonReader {
    private $project_list = [];
    private $allNachbauFiles = [];
    private $settings = [];
    private $nachbau_datas_table = 'nachbau_datas';
    private $nachbau_dtos_table = 'nachbau_dtos';
    private $log_nachbau_table = 'log_nachbau';
    private $mtoolProjectCache = [];

    public function __construct() {
        try {
            $this->settings = $this->getSettings();
            $this->setListAllNachbaus();
            // Uncomment to enable automatic project detection
            $this->setProjects();            
        } catch (Throwable $e) {
            error_log("Error in NachbauJsonReader constructor: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get settings from configuration
     */
    private function getSettings() {
        return [
            'NachbauFilesPath' => "C:\\Users\\z0051erm\\Downloads\\Nachbau Files\\Example",
            'planning' => 'planning',
            'logs' => 'logs'
        ];
    }

    /**
     * Automatically detect and process projects from database
     */
    public function setProjects() {
        try {
            // Get current date
            $currentDate = date('Y-m-d');
            // Calculate yesterday's date
            $yesterdayDate = date('Y-m-d', strtotime('-1 day', strtotime($currentDate)));
            
            $query = "SELECT FactoryNumber, FileName FROM " . $this->log_nachbau_table . " 
                      WHERE FactoryNumber IS NOT NULL AND FactoryNumber <> '' 
                      AND DATE(Created) = :yesterdayDate
                      GROUP BY FactoryNumber, FileName 
                      ORDER BY LastUpdated DESC";
            
            $params = [
                ':yesterdayDate' => $yesterdayDate
            ];
            
            $result = DbManager::fetchPDOQueryData('logs', $query, $params);
            $data = $result['data'] ?? [];
            if (!empty($data)) {
                foreach ($data as $row) {
                    $factoryNumber = $row['FactoryNumber'];
                    if (strpos($factoryNumber, '300') === 0) {
                        $projectNo = explode('-', $factoryNumber)[0];
                        $this->ReadJsonFile(trim($row['FileName']), trim($projectNo));
                    }
                }
            }
        } catch (Throwable $e) {
            error_log("Error in setProjects: " . $e->getMessage());
        }
    }

    /**
     * Check if file is already uploaded to database
     */
    public function isUploaded($filename, $projectno) {
        try {
            $query = "SELECT COUNT(Id) AS count FROM " . $this->nachbau_datas_table . " 
                      WHERE project_no = :projectno AND nachbau_no = :filename";
            
            $params = [
                ':projectno' => $projectno,
                ':filename' => $filename
            ];
            
            $result = DbManager::fetchPDOQueryData('planning', $query, $params);
            $data = $result['data'] ?? [];
            $count = 0;
            if (!empty($data)) {
                $count = $data[0]['count'];
            }
            
            if ((int)$count > 0) {
                error_log($filename . " already uploaded for project " . $projectno);
                return false;
            }
            
            return true;
        } catch (Throwable $e) {
            error_log("Error in isUploaded: " . $e->getMessage());
            return true;
        }
    }

    /**
     * Fetch and cache MTool project information
     */
    private function getMToolProjectInfo($projectno) {
        try {
            // Return cached data if already fetched
            if (isset($this->mtoolProjectCache[$projectno])) {
                return $this->mtoolProjectCache[$projectno];
            }
            
            $mtoolProjectInfoQuery = "
                SELECT 
                    SapPosData.*
                FROM(
                    SELECT 
                        ProjectNo,
                        PosNo,
                        LocationCode AS PanelName,
                        TypicalCode AS TypicalName
                    FROM 
                        dbo.OneX_SapPosData 
                    WHERE 
                        ProjectNo = :p1 AND
                        TypicalCode IS NOT NULL
                ) AS SapPosData
                JOIN
                    dbo.OneX_ProjectLeads
                ON
                    dbo.OneX_ProjectLeads.FactoryNumber = SapPosData.ProjectNo
                JOIN
                    dbo.OneX_ProjectTrackingEE
                ON
                    dbo.OneX_ProjectTrackingEE.FactoryNumber = SapPosData.ProjectNo
            ";
            
            $mtoolProjectInfoQueryData = DbManager::fetchPDOQueryData('MTool_INKWA', $mtoolProjectInfoQuery, [":p1" => $projectno])["data"] ?? [];
            
            // Cache the result
            $this->mtoolProjectCache[$projectno] = $mtoolProjectInfoQueryData;
            
            return $mtoolProjectInfoQueryData;
        } catch (Throwable $e) {
            error_log("Error in getMToolProjectInfo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get panel number from MTool data based on typical and panel name
     */
    private function getPanelNoFromMTool($projectno, $panel_typical, $orts_kz) {
        try {
            $mtoolData = $this->getMToolProjectInfo($projectno);
            
            if (empty($mtoolData)) {
                return null;
            }
            
            // Search for matching record based on TypicalName and PanelName
            foreach ($mtoolData as $record) {
                $typicalName = $record['TypicalName'] ?? '';
                $panelName = $record['PanelName'] ?? '';
                $posNo = $record['PosNo'] ?? '';
                
                // Compare with provided values
                if (trim($typicalName) === trim($panel_typical) && trim($panelName) === trim($orts_kz)) {
                    // Format PosNo to 6 digits with leading zeros
                    $formattedPosNo = str_pad($posNo, 6, '0', STR_PAD_LEFT);
                    return $formattedPosNo;
                }
            }
            
            // No match found
            return null;
        } catch (Throwable $e) {
            error_log("Error in getPanelNoFromMTool: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Read and parse Nachbau JSON file
     */
    public function ReadJsonFile($filename, $projectno) {
        try {
            SharedManager::print("========================================");
            SharedManager::print("Reading JSON file: " . $filename);
            SharedManager::print("Project No: " . $projectno);
            SharedManager::print("========================================");
            
            $isUpload = $this->isUploaded($filename, $projectno);
            
            if (!$isUpload) {
                SharedManager::print("File already uploaded, skipping: " . $filename);
                return;
            }
            
            $filepath = $this->getFilePath($filename);
            
            if (empty($filepath) || !file_exists($filepath)) {
                error_log("JSON File not found: " . $filename);
                SharedManager::print("✗ File not found: " . $filename);
                return;
            }
            
            SharedManager::print("File path: " . $filepath);
            
            // Read JSON file
            $jsonContent = file_get_contents($filepath);
            if (empty($jsonContent)) {
                error_log("JSON file is empty: " . $filename);
                SharedManager::print("✗ JSON file is empty: " . $filename);
                return;
            }
            
            // Parse JSON
            $jsonData = json_decode($jsonContent, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("Invalid JSON format in file " . $filename . ": " . json_last_error_msg());
                SharedManager::print("✗ Invalid JSON format: " . json_last_error_msg());
                return;
            }
            
            SharedManager::print("✓ JSON parsed successfully");
            
            // Extract panels from JSON
            if (!isset($jsonData['panels']) || !is_array($jsonData['panels'])) {
                error_log("No panels found in JSON file: " . $filename);
                SharedManager::print("✗ No panels found in JSON structure");
                return;
            }
            
            SharedManager::print("Found " . count($jsonData['panels']) . " panels in JSON");
            
            // Process each panel
            foreach ($jsonData['panels'] as $panelIndex => $panel) {
                try {
                    $this->processJsonPanel($panel, $filename, $projectno, $panelIndex);
                } catch (Throwable $e) {
                    error_log("Error processing panel $panelIndex: " . $e->getMessage());
                    SharedManager::print("✗ Error processing panel $panelIndex: " . $e->getMessage());
                }
            }
            
            SharedManager::print("✓ JSON file processing completed: " . $filename);
            SharedManager::print("========================================");
            
        } catch (Throwable $e) {
            error_log("Error in ReadJsonFile: " . $e->getMessage());
            SharedManager::print("✗ Critical error in ReadJsonFile: " . $e->getMessage());
        }
    }

    /**
     * Process individual panel data from JSON
     */
    private function processJsonPanel($panel, $filename, $projectno, $panelIndex) {
        try {
            $insertValues = [];
            $dtoValues = [];
            
            // Extract panel information from JSON structure
            $panel_number = $panel['position'] ?? '';
            $panel_typical = $panel['typical'] ?? '';
            $orts_kz = $panel['ortsKz'] ?? '';
            $feld_name = $panel['feldName'] ?? '';
            
            SharedManager::print("  Panel $panelIndex: Position=$panel_number, Typical=$panel_typical, FeldName=$feld_name");
            
            // Extract materials/items from panelInformation.children (hierarchical structure)
            if (!isset($panel['panelInformation']) || !is_array($panel['panelInformation'])) {
                SharedManager::print("    ⚠ No panelInformation found in panel $panelIndex");
                return;
            }
            
            $panelInfo = $panel['panelInformation'];
            
            // Recursively flatten the hierarchical children structure
            $flattenedItems = $this->flattenPanelChildren($panelInfo);
            
            if (empty($flattenedItems)) {
                SharedManager::print("    ⚠ No items found in panel $panelIndex");
                return;
            }
            
            $itemCount = count($flattenedItems);
            SharedManager::print("    Found $itemCount items in panel");
            
            // Get panel_no from MTool or use fallback value
            $panel_no = $this->getPanelNoFromMTool($projectno, $panel_typical, $orts_kz);
            
            if ($panel_no === null) {
                // Fallback to original logic if no match found in MTool
                $panel_no = substr(trim($panel_number), 0, 2) . '00';
                SharedManager::print("    ℹ No MTool match found, using fallback panel_no: " . $panel_no);
            } else {
                SharedManager::print("    ✓ Panel_no retrieved from MTool: " . $panel_no);
            }
            
            // Process each flattened item
            foreach ($flattenedItems as $itemIndex => $item) {
                try {
                    // Extract item information
                    $position = $item['position'] ?? '';
                    $position_ext = $item['positionExt'] ?? '';
                    $kmat_no = $item['kmat'] ?? '';
                    $kmat_no_ext = $item['kmatExt'] ?? '';
                    $qty = $item['qty'] ?? '';
                    $unit = $item['unit'] ?? '';
                    $kmat_name = $item['kmatName'] ?? '';
                    $description = $item['description'] ?? '';
                    $parent = $item['parent'] ?? '';
                    
                    // Format position with dots based on depth level
                    $position = $this->formatPosition($position);
                    
                    // Check if this is a special material that needs separate handling
                    if ((strpos($kmat_name, 'NX') !== false || strpos($kmat_name, '8BT') !== false) 
                        && strpos($kmat_name, ':') !== false 
                        && trim($kmat_no_ext) === '' 
                        && trim($description) !== '') {
                        
                        $unique = $projectno . "_" . $kmat_name;
                        $dtoValues[] = [
                            'ProjectNo' => $projectno,
                            'DtoNumber' => $kmat_name,
                            'Unq' => $unique
                        ];
                    }
                    
                    // Build main insert statement
                    $insertValues[] = [
                        'project_no' => $projectno,
                        'nachbau_no' => $filename,
                        'typical_no' => $panel_typical,
                        'ortz_kz' => $orts_kz,
                        'panel_no' => $panel_no,
                        'feld_name' => $feld_name,
                        'position' => $position,
                        'position_ext' => $position_ext,
                        'kmat' => $kmat_no,
                        'kmat_ext' => $kmat_no_ext,
                        'qty' => $qty,
                        'unit' => $unit,
                        'kmat_name' => $kmat_name,
                        'parent_kmat' => $parent,
                        'description' => $description
                    ];
                    
                } catch (Throwable $e) {
                    error_log("Error processing item $itemIndex in panel $panelIndex: " . $e->getMessage());
                    SharedManager::print("    ✗ Error processing item $itemIndex: " . $e->getMessage());
                }
            }
            
            // Execute inserts
            if (!empty($insertValues)) {
                SharedManager::print("    Inserting " . count($insertValues) . " material records");
                $this->insertNachbauDatas($insertValues);
            }
            
            // Execute DTO inserts
            if (!empty($dtoValues)) {
                SharedManager::print("    Inserting " . count($dtoValues) . " DTO records");
                $this->insertNachbauDtos($dtoValues);
            }
            
        } catch (Throwable $e) {
            error_log("Error in processJsonPanel: " . $e->getMessage());
            SharedManager::print("  ✗ Error processing panel: " . $e->getMessage());
        }
    }

    /**
     * Format position with dots based on depth level
     * 0 => 0
     * 1 => .1
     * 2 => ..2
     * 3 => ...3
     * 4 => ....4
     */
    private function formatPosition($position) {
        if (empty($position) || !is_numeric($position)) {
            return $position;
        }
        
        $posValue = (int)$position;
        
        if ($posValue === 0) {
            return '0';
        }
        
        $dots = str_repeat('.', $posValue);
        return $dots . $posValue;
    }

    /**
     * Recursively flatten the hierarchical children structure
     */
    private function flattenPanelChildren($panelInfo, $parentKmat = '', $depth = 0) {
        $flattenedItems = [];
        
        if (!isset($panelInfo['children']) || !is_array($panelInfo['children'])) {
            return $flattenedItems;
        }
        
        foreach ($panelInfo['children'] as $childIndex => $child) {
            // Add current item with depth information
            $item = [
                'position' => $depth,
                'positionExt' => $child['positionExt'] ?? '',
                'kmat' => $child['kmat'] ?? '',
                'kmatExt' => $child['kmatExt'] ?? '',
                'qty' => $child['qty'] ?? '',
                'unit' => $child['unit'] ?? '',
                'kmatName' => $child['kmatName'] ?? '',
                'description' => $child['description'] ?? '',
                'parent' => $parentKmat,
                'deviceName' => $child['deviceName'] ?? ''
            ];
            
            $flattenedItems[] = $item;
            
            // Recursively process children if they exist
            if (isset($child['children']) && is_array($child['children']) && !empty($child['children'])) {
                $currentKmat = $child['kmat'] ?? '';
                $childItems = $this->flattenPanelChildren($child, $currentKmat, $depth + 1);
                $flattenedItems = array_merge($flattenedItems, $childItems);
            }
        }
        
        return $flattenedItems;
    }

    /**
     * Insert nachbau data using batch insert
     */
    private function insertNachbauDatas($values) {
        try {
            if (empty($values)) {
                return;
            }
            
            $statement = '';
            foreach ($values as $row) {
                $statement .= "('" 
                    . addslashes($row['project_no']) . "','" 
                    . addslashes($row['nachbau_no']) . "','" 
                    . addslashes($row['typical_no']) . "','" 
                    . addslashes($row['ortz_kz']) . "','" 
                    . addslashes($row['panel_no']) . "','" 
                    . addslashes($row['feld_name']) . "','" 
                    . addslashes($row['position']) . "','" 
                    . addslashes($row['position_ext']) . "','" 
                    . addslashes($row['kmat']) . "','" 
                    . addslashes($row['kmat_ext']) . "','" 
                    . addslashes($row['qty']) . "','" 
                    . addslashes($row['unit']) . "','" 
                    . addslashes($row['kmat_name']) . "','" 
                    . addslashes($row['parent_kmat']) . "','" 
                    . addslashes($row['description']) . "'),";
            }
            
            $query = "INSERT INTO " . $this->nachbau_datas_table . 
                "(project_no, nachbau_no, typical_no, ortz_kz, panel_no, feld_name, position, 
                 position_ext, kmat, kmat_ext, qty, unit, kmat_name, parent_kmat, description) 
                VALUES " . rtrim($statement, ',');
            
            DbManager::fetchPDOQueryData('planning', $query);
            error_log("Inserted " . count($values) . " nachbau records for project");
            SharedManager::print("      ✓ Successfully inserted " . count($values) . " records");
            
        } catch (Throwable $e) {
            error_log("Error in insertNachbauDatas: " . $e->getMessage());
            SharedManager::print("      ✗ Error inserting records: " . $e->getMessage());
        }
    }

    /**
     * Insert nachbau DTOs using batch insert
     */
    private function insertNachbauDtos($values) {
        try {
            if (empty($values)) {
                return;
            }
            
            $statement = '';
            foreach ($values as $row) {
                $statement .= "('" 
                    . addslashes($row['ProjectNo']) . "','" 
                    . addslashes($row['DtoNumber']) . "','" 
                    . addslashes($row['Unq']) . "'),";
            }
            
            $query = "INSERT IGNORE INTO " . $this->nachbau_dtos_table . 
                "(ProjectNo, DtoNumber, Unq) VALUES " . rtrim($statement, ',');
            
            DbManager::fetchPDOQueryData('planning', $query);
            error_log("Inserted " . count($values) . " DTO records");
            SharedManager::print("      ✓ Successfully inserted " . count($values) . " DTO records");
            
        } catch (Throwable $e) {
            error_log("Error in insertNachbauDtos: " . $e->getMessage());
            SharedManager::print("      ✗ Error inserting DTO records: " . $e->getMessage());
        }
    }

    /**
     * Scan directory and list all JSON Nachbau files
     */
    private function setListAllNachbaus() {
        try {
            $path = $this->settings['NachbauFilesPath'];
            
            if (!is_dir($path)) {
                error_log("Nachbau files path does not exist: " . $path);
                SharedManager::print("✗ Path does not exist: " . $path);
                return;
            }
            
            $iterator = new RecursiveDirectoryIterator($path);
            $recursiveIterator = new RecursiveIteratorIterator($iterator);
            
            foreach ($recursiveIterator as $file) {
                if ($file->isFile()) {
                    $extension = strtolower($file->getExtension());
                    // Accept only JSON files
                    if ($extension === 'json') {
                        $this->allNachbauFiles[] = $file->getPathname();
                    }
                }
            }
            
            error_log("Found " . count($this->allNachbauFiles) . " JSON Nachbau files");
            SharedManager::print("Found " . count($this->allNachbauFiles) . " JSON files");
        } catch (Throwable $e) {
            error_log("Error in setListAllNachbaus: " . $e->getMessage());
            SharedManager::print("✗ Error scanning directory: " . $e->getMessage());
        }
    }

    /**
     * Get full file path from filename
     */
    private function getFilePath($scan) {
        foreach ($this->allNachbauFiles as $filepath) {
            if (strpos($filepath, $scan) !== false) {
                return $filepath;
            }
        }
        return '';
    }
}

// Instantiate the class
try {
    SharedManager::print("========================================");
    SharedManager::print("Initializing NachbauJsonReader");
    SharedManager::print("========================================");
    new NachbauJsonReader();
    SharedManager::print("✓ NachbauJsonReader initialized successfully");
    error_log("NachbauJsonReader initialized successfully");
} catch (Throwable $e) {
    SharedManager::print("✗ Failed to initialize NachbauJsonReader: " . $e->getMessage());
    error_log("Failed to initialize NachbauJsonReader: " . $e->getMessage());
}
?>