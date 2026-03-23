<?php
SharedManager::checkAuthToModule(35);

require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/shared.php";

class AKDImportController {
    private array $xmlData = [];
    private DOMDocument $XMLDocument;

    //Example data
    /*
     * $this->xmlData = [
            "SalesOrder" => "7024050638",
            "Panels" => [
                [
                    "PanelPosNo" => "002010",
                    "PanelTypicalName" => "",
                    "PanelName" => "",
                    "Changes" => [
                        [
                            "KMAT" => "A7E0030009580",
                            "REMOVE" => [
                                "POS" => "0020",
                                "MATNR" => "A7ET8PQ91307AA23",
                                "MENGE" => 1
                            ],
                            "ADD" => [
                                "MATNR" => "A7ET8PQ81182TA67",
                                "MENGE" => 1
                            ]
                        ],
                        [
                            "KMAT" => "A7E0030009580",
                            "REMOVE" => [],
                            "ADD" => [
                                "MATNR" => "A7ET8PQ81182TA67_S",
                                "MENGE" => 1
                            ]
                        ]
                    ]
                ]
            ]
        ];
    */
    public function __construct($data)
    {
        $this->xmlData = $data;
        $this->XMLDocument = new DOMDocument('1.0', 'UTF-8');
        $this->createXml();

        return $this;
    }

    private function isArrayOfArrays($array): bool
    {
        if (!is_array($array)) return false;

        foreach ($array as $element)
            if (!is_array($element))
                return false;
        return true;
    }

    /**
     * @throws DOMException
     */
    private function createXml(): void
    {
        $this->XMLDocument->formatOutput = true;

        $abapTag = $this->XMLDocument->createElement('abap');
        $this->XMLDocument->appendChild($abapTag);

        $abapTag->setAttribute('xmlns:asx','http://www.sap.com/abapxml');
        $abapTag->setAttribute('version','1.0');

        $valuesTag = $this->XMLDocument->createElement('values');
        $abapTag->appendChild($valuesTag);

        $STUECKLISTENDATENTag = $this->XMLDocument->createElement('STUECKLISTENDATEN');
        $valuesTag->appendChild($STUECKLISTENDATENTag);

        $salesOrder = $this->xmlData["SalesOrder"];
        if(!$salesOrder || !is_numeric($salesOrder) || strlen($salesOrder) !== 10 || !str_starts_with($salesOrder, "7024"))
            returnHttpResponse(500, "Invalid sales order number");
        $AUFTRAGTag = $this->XMLDocument->createElement('AUFTRAG', $salesOrder);
        $HPOSTag = $this->XMLDocument->createElement('HPOS', '000000');
        $LIEFERPOSITIONTag = $this->XMLDocument->createElement('LIEFERPOSITION');

        $STUECKLISTENDATENTag->appendChild($AUFTRAGTag);
        $STUECKLISTENDATENTag->appendChild($HPOSTag);
        $STUECKLISTENDATENTag->appendChild($LIEFERPOSITIONTag);

        $panels = $this->xmlData["Panels"];
        if(!$this->isArrayOfArrays($panels))
            returnHttpResponse(500, "Panels must be array of arrays");

        foreach ($panels as $panelData){
            $panelPosNo = $panelData["PanelPosNo"];
            $panelTypicalName = $panelData["PanelTypicalName"] ?? "";
            $panelName = $panelData["PanelName"] ?? "";
            $changes = $panelData["Changes"];

            if(!$panelPosNo || strlen($panelPosNo) !== 6 || !is_numeric($panelPosNo))
                returnHttpResponse(500, "Invalid panel pos no");

            if(!$this->isArrayOfArrays($changes))
                returnHttpResponse(500, "Changes must be array of arrays");

            $lieferPositionItemTag = $this->XMLDocument->createElement('item');

            $NRTag = $this->XMLDocument->createElement('NR', $panelPosNo);
            $TYPICALKZTag = $this->XMLDocument->createElement('TYPICALKZ');
            $TYPICALKZTagText = $this->XMLDocument->createTextNode($panelTypicalName);
            $TYPICALKZTag->appendChild($TYPICALKZTagText);

            $FELDKZTag = $this->XMLDocument->createElement('FELDKZ');
            $FELDKZTagText = $this->XMLDocument->createTextNode($panelName);
            $FELDKZTag->appendChild($FELDKZTagText);

            $lieferPositionItemTag->appendChild($NRTag);
            $lieferPositionItemTag->appendChild($TYPICALKZTag);
            $lieferPositionItemTag->appendChild($FELDKZTag);

            $AENDERUNGTag = $this->XMLDocument->createElement('AENDERUNG');
            foreach ($changes as $changeData){
                $kmat = $changeData["KMAT"];
                $removeData = $changeData["REMOVE"];
                $addData = $changeData["ADD"];

                if(!$kmat)
                    returnHttpResponse(500, "KMAT can not be empty");
                if(count($removeData) === 0 && count($addData) === 0)
                    returnHttpResponse(500, "Remove or Add must be defined");

                $AENDERUNGItemTag = $this->XMLDocument->createElement('item');
                $AENDERUNGItemKmatTag = $this->XMLDocument->createElement('KMAT', $kmat);
                $AENDERUNGItemTag->appendChild($AENDERUNGItemKmatTag);

                $ALT_POS = $removeData["POS"];
                $ALT_MATNR = $removeData["MATNR"];
                $ALT_MENGE = $removeData["MENGE"];

                $ALTTag = $this->XMLDocument->createElement('ALT');

                if(count($removeData) !== 0){
                    if(!$ALT_POS || !is_numeric($ALT_POS) || strlen("$ALT_POS") !== 4)
                        returnHttpResponse(500, "Invalid ALT-POS");
                    if(!str_starts_with($ALT_MATNR, "A7E"))
                        returnHttpResponse(500, "Invalid ALT-MATNR");
                    if(!is_numeric($ALT_MENGE))
                        returnHttpResponse(500, "Invalid ALT-MENGE");

                    $ALTPOSTag = $this->XMLDocument->createElement('POS', $ALT_POS);
                    $ALTMATNRTag = $this->XMLDocument->createElement('MATNR', $ALT_MATNR);
                    $ALTMENGETag = $this->XMLDocument->createElement('MENGE', $ALT_MENGE);

                    $ALTTag->appendChild($ALTPOSTag);
                    $ALTTag->appendChild($ALTMATNRTag);
                    $ALTTag->appendChild($ALTMENGETag);
                }
                else{
                    $emptyText = $this->XMLDocument->createTextNode('');
                    $ALTTag->appendChild($emptyText);
                }
                $AENDERUNGItemTag->appendChild($ALTTag);

                $NEU_MATNR = $addData["MATNR"];
                $NEU_MENGE = $addData["MENGE"];

                $NEUTag = $this->XMLDocument->createElement('NEU');
                if(count($addData) !== 0){
                    if(!str_starts_with($NEU_MATNR, "A7E"))
                        returnHttpResponse(500, "Invalid NEU-MATNR");
                    if(!is_numeric($NEU_MENGE))
                        returnHttpResponse(500, "Invalid NEU-MENGE");

                    $NEUPOSTag = $this->XMLDocument->createElement('POS');
                    $emptyText = $this->XMLDocument->createTextNode('');
                    $NEUPOSTag->appendChild($emptyText);

                    $NEUMATNRTag = $this->XMLDocument->createElement('MATNR', $NEU_MATNR);
                    $NEUMENGETag = $this->XMLDocument->createElement('MENGE', $NEU_MENGE);

                    $NEUTag->appendChild($NEUPOSTag);
                    $NEUTag->appendChild($NEUMATNRTag);
                    $NEUTag->appendChild($NEUMENGETag);
                }
                else{
                    $emptyText = $this->XMLDocument->createTextNode('');
                    $NEUTag->appendChild($emptyText);
                }
                $AENDERUNGItemTag->appendChild($NEUTag);
                $AENDERUNGTag->appendChild($AENDERUNGItemTag);
            }

            $lieferPositionItemTag->appendChild($AENDERUNGTag);
            $LIEFERPOSITIONTag->appendChild($lieferPositionItemTag);
        }
    }

    public function save($savePath): void
    {
        $isSaved = $this->XMLDocument->save($savePath);
        if($isSaved === false){
            returnHttpResponse(500, "Document could not be written");
        }
    }

    public function download($fileName): void
    {
        Journals::saveJournal("AKD Import XML Download Started | $fileName", PAGE_PROJECTS, DESIGN_DETAIL_ORDER_SUMMARY, ACTION_VIEWED, $fileName, "AKD Import");
        SharedManager::saveLog("log_dtoconfigurator", "Downloaded AKD Xml: $fileName");
        header_remove();

        header('Content-Type: application/xml');
        header("Content-Disposition: attachment; filename=$fileName.xml");
        header('Content-Length: ' . strlen($this->XMLDocument->saveXML()));

        echo $this->XMLDocument->saveXML();
        exit;
    }

    public function copyToFolder($folderPath, $fileName): array
    {
        try {
            $folderPath = rtrim($folderPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

            if (!is_dir($folderPath)) {
                if (!mkdir($folderPath, 0777, true)) {
                    throw new Exception("Failed to create destination directory: " . $folderPath);
                }
            }

            if (!is_writable($folderPath)) {
                throw new Exception("Destination directory is not writable: " . $folderPath);
            }

            $fullFilePath = $folderPath . $fileName . '.xml';

            $isSaved = $this->XMLDocument->save($fullFilePath);

            if ($isSaved === false) {
                throw new Exception("Failed to save XML file to: " . $fullFilePath);
            }

            if (file_exists($fullFilePath)) {
                chmod($fullFilePath, 0444);
            }

            // Log success
            Journals::saveJournal("AKD Import XML Copied to Folder | $fileName", PAGE_PROJECTS, DESIGN_DETAIL_ORDER_SUMMARY, ACTION_VIEWED, $fileName, "AKD Import Copy");
            SharedManager::saveLog("log_dtoconfigurator", "Copied AKD XML to folder: $fileName");

            return [
                'success' => true,
                'message' => 'AKD Import XML file successfully copied to folder',
                'file_path' => $fullFilePath,
                'file_name' => $fileName . '.xml'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

}