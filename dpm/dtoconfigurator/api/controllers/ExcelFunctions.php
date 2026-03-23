<?php
ini_set('memory_limit', '512M');
require_once $_SERVER["DOCUMENT_ROOT"] .  '/shared/PHPExcel/Classes/PHPExcel.php';
require_once $_SERVER["DOCUMENT_ROOT"] .  '/shared/PHPExcel/Classes/PHPExcel/Writer/Excel2007.php';
require_once $_SERVER["DOCUMENT_ROOT"] .  '/shared/api/MtoolManager.php';
include_once '../../api/models/Journals.php';
include_once '../../api/controllers/KukoMatrixController.php';

try {
    $projectNo = $_POST['projectNo'];
    $nachbauNo = $_POST['nachbauNo'];
    $assemblyStart = $_POST['assemblyStart'] ?? '';
    $orderSummaryData = json_decode($_POST['orderSummaryData'], true);
    $excelOperation = $_POST['operation'] ?? 'downloadExcel'; // Default to download if not specified
    $isRevision = $_POST['isRevision'] === 'true';

    fillExcel($projectNo, $nachbauNo, $assemblyStart, $excelOperation, $orderSummaryData, $isRevision);
} catch (Exception $ex) {
    if (isset($_POST['operation']) && $_POST['operation'] === 'copyToFolder') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $ex->getMessage()
        ]);
    } else {
        echo "Error: " . $ex->getMessage();
    }
    return false;
}

function fillExcel($projectNo, $nachbauNo, $assemblyStart, $excelOperation, $orderSummaryData, $isRevision){
    $objPHPExcel = createPHPExcel();

    $kuko_matrix_sheet = createSheet($objPHPExcel, 'KUKO_MATRIX');
    $kukoMatrixData = (new KukoMatrixController)->getKukoMatrixDataForExcelSheet($projectNo, $nachbauNo);
    fillKukoMatrixExcelSheet($kuko_matrix_sheet, $kukoMatrixData,$projectNo, $nachbauNo);

    $bom_exchange = createSheet($objPHPExcel,'BOM_EXCHANGE');
    $mtool = MtoolManager::getProjectContacts([$projectNo])[0] ?? [];
    $query = "SELECT p.product_type, proj.working_user FROM projects proj LEFT JOIN products p ON p.id = proj.product_type WHERE proj.project_number=:pNo AND proj.nachbau_number=:nNo";
    $projProductData = DbManager::fetchPDOQueryData('dto_configurator', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo])['data'][0] ?? [];
    $panelType = $projProductData['product_type'];
    $mecEngineer = $projProductData['working_user'];

    $mergedNotes = showBomAndKukoNotesOfNachbau($projectNo, $nachbauNo, $bom_exchange);

    $projectCharacteristics = getProjectCharacteristics($projectNo, $panelType);
    $rated_short_circuit = ltrim($projectCharacteristics['rated_short_circuit'], '0'); //31,5 kA
    $rated_voltage = ltrim($projectCharacteristics['rated_voltage'], '0'); //17,5 kV
    $rated_current = ltrim($projectCharacteristics['rated_current'], '0'); //1250A

    $projectDetails = MtoolManager::getProjectDetailsByProjectNos([$projectNo])[0] ?? [];
    $data = [
        ['rowNumber'=>1,'cellName'=>'A','border'=>false, 'cellValue'=>'Tech Detail', 'cellFontColor'=>'FFFFFF', 'cellBgColor'=>'000000', 'cellWidth'=>40, 'cellHeight'=>20, 'cellFontSize'=>14, 'cellWrapText'=>true, 'cellFontBold'=>true, 'mergeTo'=>'D1', 'fontName'=>'Arial', 'cellFontItalic' => true, 'cellFontUnderline' => true],
        ['rowNumber'=>1,'cellName'=>'E','border'=>false, 'cellValue'=>'Keyword / Customer', 'cellFontColor'=>'FFFFFF', 'cellBgColor'=>'000000', 'cellWidth'=>80, 'cellHeight'=>20, 'cellFontSize'=>14, 'cellWrapText'=>true, 'cellFontBold'=>true, 'mergeTo'=>'F2', 'fontName'=>'Arial', 'cellFontItalic' => true, 'cellFontUnderline' => true],
        ['rowNumber'=>2,'cellName'=>'A','border'=>false, 'cellValue'=>'Factory.Nr. :', 'cellFontColor'=>'FFFFFF', 'cellBgColor'=>'000000', 'cellWidth'=>40, 'cellHeight'=>20, 'cellFontSize'=>12, 'cellWrapText'=>true, 'cellFontBold'=>true, 'mergeTo'=>'B2', 'fontName'=>'Arial'],
        ['rowNumber'=>2,'cellName'=>'C','border'=>false, 'cellValue'=>$projectNo, 'cellFontColor'=>'FF0000', 'cellBgColor'=>'FFFFFF', 'cellWidth'=>40, 'cellHeight'=>20, 'cellFontSize'=>14, 'cellWrapText'=>true, 'cellFontBold'=>true, 'mergeTo'=>'D2', 'fontName'=>'Arial'],
        ['rowNumber'=>3,'cellName'=>'A','border'=>false, 'cellValue'=>'Rated Voltage', 'cellFontColor'=>'FFFFFF', 'cellBgColor'=>'000000', 'cellWidth'=>40, 'cellHeight'=>20, 'cellFontSize'=>8, 'cellWrapText'=>true, 'cellFontBold'=>true, 'mergeTo'=>'B3', 'fontName'=>'Arial'],
        ['rowNumber'=>3,'cellName'=>'C','border'=>false, 'cellValue'=>$rated_voltage , 'cellFontColor'=>'FF0000', 'cellBgColor'=>'FFFFFF', 'cellWidth'=>40, 'cellHeight'=>20, 'cellFontSize'=>10, 'cellWrapText'=>true, 'cellFontBold'=>true, 'mergeTo'=>'', 'fontName'=>'Arial'],
        ['rowNumber'=>3,'cellName'=>'D','border'=>false, 'cellValue'=>'kV', 'cellFontColor'=>'000000', 'cellBgColor'=>'FFFFFF', 'cellWidth'=>40, 'cellHeight'=>20, 'cellFontSize'=>11, 'cellWrapText'=>true, 'cellFontBold'=>true, 'mergeTo'=>'', 'fontName'=>'Arial'],
        ['rowNumber'=>3,'cellName'=>'E','border'=>false, 'cellValue'=>$mtool['ProjectName'] ?? '', 'cellFontColor'=>'000000', 'cellBgColor'=>'FFFFFF', 'cellWidth'=>80, 'cellHeight'=>20, 'cellFontSize'=>20, 'cellWrapText'=>true, 'cellFontBold'=>true, 'mergeTo'=>'F5', 'fontName'=>'Arial'],
        ['rowNumber'=>3,'cellName'=>'G','border'=>false, 'cellValue'=>'Panel Type', 'cellFontColor'=>'FFFFFF', 'cellBgColor'=>'000000', 'cellWidth'=>120, 'cellHeight'=>20, 'cellFontSize'=>10, 'cellWrapText'=>true, 'cellFontBold'=>true, 'mergeTo'=>'', 'fontName'=>'Arial'],
        ['rowNumber'=>3,'cellName'=>'H','border'=>false, 'cellValue'=> $panelType, 'cellFontColor'=>'000000', 'cellBgColor'=>'FFFFFF', 'cellWidth'=>100, 'cellHeight'=>40, 'cellFontSize'=>10, 'cellWrapText'=>true, 'cellFontBold'=>true, 'mergeTo'=>'', 'fontName'=>'Arial'],
        ['rowNumber'=>3,'cellName'=>'I','border'=>false, 'cellValue'=>'Panel Qty.', 'cellFontColor'=>'FFFFFF', 'cellBgColor'=>'000000', 'cellWidth'=>120, 'cellHeight'=>20, 'cellFontSize'=>10, 'cellWrapText'=>true, 'cellFontBold'=>true, 'mergeTo'=>'', 'fontName'=>'Arial'],
        ['rowNumber'=>3,'cellName'=>'J','border'=>false, 'cellValue'=>$projectDetails['Qty'] . ' panels' ?? '', 'cellFontColor'=>'0000FF', 'cellBgColor'=>'FFFFFF', 'cellWidth'=>40, 'cellHeight'=>20, 'cellFontSize'=>10, 'cellWrapText'=>true, 'cellFontBold'=>true, 'mergeTo'=>'L3', 'fontName'=>'Arial'],
        ['rowNumber'=>4,'cellName'=>'A','border'=>false, 'cellValue'=>'Rated Short Circuit', 'cellFontColor'=>'FFFFFF', 'cellBgColor'=>'000000', 'cellWidth'=>40, 'cellHeight'=>40, 'cellFontSize'=>8, 'cellWrapText'=>true, 'cellFontBold'=>true, 'mergeTo'=>'B4', 'fontName'=>'Arial'],
        ['rowNumber'=>4,'cellName'=>'C','border'=>false, 'cellValue'=>$rated_short_circuit, 'cellFontColor'=>'FF0000', 'cellBgColor'=>'FFFFFF', 'cellWidth'=>40, 'cellHeight'=>40, 'cellFontSize'=>10, 'cellWrapText'=>true, 'cellFontBold'=>true, 'mergeTo'=>'', 'fontName'=>'Arial'],
        ['rowNumber'=>4,'cellName'=>'D','border'=>false, 'cellValue'=>'kA', 'cellFontColor'=>'000000', 'cellBgColor'=>'FFFFFF', 'cellWidth'=>40, 'cellHeight'=>40, 'cellFontSize'=>11, 'cellWrapText'=>true, 'cellFontBold'=>true, 'mergeTo'=>'', 'fontName'=>'Arial'],
        ['rowNumber'=>4,'cellName'=>'G','border'=>false, 'cellValue'=>'Name', 'cellFontColor'=>'FFFFFF', 'cellBgColor'=>'000000', 'cellWidth'=>120, 'cellHeight'=>40, 'cellFontSize'=>10, 'cellWrapText'=>true, 'cellFontBold'=>true, 'mergeTo'=>'', 'fontName'=>'Arial', 'cellFontItalic' => true, 'cellFontUnderline' => true],
        ['rowNumber'=>4,'cellName'=>'H','border'=>false, 'cellValue'=>$mecEngineer ?? $mtool['MechanicalEngineer'] ?? '', 'cellFontColor'=>'0000FF', 'cellBgColor'=>'FFFFFF', 'cellWidth'=>100, 'cellHeight'=>40, 'cellFontSize'=>10, 'cellWrapText'=>true, 'cellFontBold'=>true, 'mergeTo'=>'', 'fontName'=>'Arial'],
        ['rowNumber'=>4,'cellName'=>'I','border'=>false, 'cellValue'=>'Order Manager', 'cellFontColor'=>'FFFFFF', 'cellBgColor'=>'000000', 'cellWidth'=>120, 'cellHeight'=>40, 'cellFontSize'=>10, 'cellWrapText'=>true, 'cellFontBold'=>true, 'mergeTo'=>'', 'fontName'=>'Arial'],
        ['rowNumber'=>4,'cellName'=>'J','border'=>false, 'cellValue'=>$mtool['OrderManager'] ?? '', 'cellFontColor'=>'FF0000', 'cellBgColor'=>'FFFFFF', 'cellWidth'=>40, 'cellHeight'=>40, 'cellFontSize'=>10, 'cellWrapText'=>true, 'cellFontBold'=>true, 'mergeTo'=>'L4', 'fontName'=>'Arial'],
        ['rowNumber'=>5,'cellName'=>'A','border'=>false, 'cellValue'=>'Rated Current', 'cellFontColor'=>'FFFFFF', 'cellBgColor'=>'000000', 'cellWidth'=>40, 'cellHeight'=>20, 'cellFontSize'=>8, 'cellWrapText'=>true, 'cellFontBold'=>true, 'mergeTo'=>'B5', 'fontName'=>'Arial'],
        ['rowNumber'=>5,'cellName'=>'C','border'=>false, 'cellValue'=>$rated_current, 'cellFontColor'=>'FF0000', 'cellBgColor'=>'FFFFFF', 'cellWidth'=>40, 'cellHeight'=>20, 'cellFontSize'=>10, 'cellWrapText'=>true, 'cellFontBold'=>true, 'mergeTo'=>'', 'fontName'=>'Arial'],
        ['rowNumber'=>5,'cellName'=>'D','border'=>false, 'cellValue'=>'A', 'cellFontColor'=>'000000', 'cellBgColor'=>'FFFFFF', 'cellWidth'=>40, 'cellHeight'=>20, 'cellFontSize'=>10, 'cellWrapText'=>true, 'cellFontBold'=>true, 'mergeTo'=>'', 'fontName'=>'Arial'],
        ['rowNumber'=>5,'cellName'=>'G','border'=>false, 'cellValue'=>'Date', 'cellFontColor'=>'FFFFFF', 'cellBgColor'=>'000000', 'cellWidth'=>120, 'cellHeight'=>20, 'cellFontSize'=>10, 'cellWrapText'=>true, 'cellFontBold'=>true, 'mergeTo'=>'', 'fontName'=>'Arial'],
        ['rowNumber'=>5,'cellName'=>'H','border'=>false, 'cellValue'=>date('d.m.Y H:i'), 'cellFontColor'=>'0000FF', 'cellBgColor'=>'FFFFFF', 'cellWidth'=>100, 'cellHeight'=>20, 'cellFontSize'=>10, 'cellWrapText'=>true, 'cellFontBold'=>true, 'mergeTo'=>'', 'fontName'=>'Arial'],
        ['rowNumber'=>5,'cellName'=>'I','border'=>false, 'cellValue'=>'NXTools No.', 'cellFontColor'=>'FFFFFF', 'cellBgColor'=>'000000', 'cellWidth'=>120, 'cellHeight'=>20, 'cellFontSize'=>10, 'cellWrapText'=>true, 'cellFontBold'=>true, 'mergeTo'=>'', 'fontName'=>'Arial'],
        ['rowNumber'=>5,'cellName'=>'J','border'=>false, 'cellValue'=>$nachbauNo, 'cellFontColor'=>'FF0000', 'cellBgColor'=>'FFFFFF', 'cellWidth'=>40, 'cellHeight'=>20, 'cellFontSize'=>10, 'cellWrapText'=>true, 'cellFontBold'=>true, 'mergeTo'=>'L5', 'fontName'=>'Arial'],
        ['rowNumber'=>6,'cellName'=>'A','border'=>false, 'cellValue'=>'General Notes:', 'cellFontColor'=>'000000', 'cellBgColor'=>'FFFFFF', 'cellWidth'=>40, 'cellHeight'=>20, 'cellFontSize'=>9, 'cellWrapText'=>true, 'cellFontBold'=>true, 'mergeTo'=>'B6', 'fontName'=>'Arial'],
        ['rowNumber'=>6,'cellName'=>'C','border'=>false, 'cellValue'=>$mergedNotes, 'cellBgColor'=>'FFFFFF', 'cellWidth'=>40, 'cellHeight'=>100, 'cellFontSize'=>16, 'cellWrapText'=>true, 'cellFontBold'=>true, 'mergeTo'=>'L6', 'fontName'=>'Arial'],
        ['rowNumber'=>1,'cellName'=>'G','border'=>false, 'cellValue'=>'Name', 'cellFontColor'=>'FFFFFF', 'cellBgColor'=>'000000', 'cellWidth'=>80, 'cellHeight'=>20, 'cellFontSize'=>14, 'cellWrapText'=>true, 'cellFontBold'=>true, 'mergeTo'=>'L2', 'fontName'=>'Arial'],

    ];
    setCell($bom_exchange, $data);

    $i = 7;
    $last = '';
    foreach ($orderSummaryData as $item) {
        $typical =  str_replace('=', '', $item['typical_no']);
        $data = [];
        if($last != $typical.$item['panel_no'].$item['ortz_kz'] && intval($item['released_dto_type_id']) !== 3 && !$item['is_cable']){
            $bom_exchange->setCellValue("A$i", 'Position:');
            $bom_exchange->setCellValue("B$i", 'New Nr.');
            $bom_exchange->setCellValue("C$i", $item['panel_no'].'  Typical');
            $bom_exchange->setCellValue("D$i", $typical);
            $bom_exchange->setCellValue("E$i", '');
            $bom_exchange->setCellValue("F$i", 'Ortz-KZ:    '.$item['ortz_kz'].'            Feldname:          '.$item['feld_name']);
            $bom_exchange->setCellValue("G$i", $typical);
            $bom_exchange->setCellValue("H$i", 'Pano No');
            $bom_exchange->setCellValue("I$i", 'Aksesuar');
            $bom_exchange->setCellValue("J$i", 'Notlar');
            $bom_exchange->setCellValue("K$i", 'Rev. Tarihi');
            $bom_exchange->setCellValue("L$i", 'Rev. Yapan');

            $last = $typical.$item['panel_no'].$item['ortz_kz'];
            $cellRange = "A$i".':'."L$i";
            $bom_exchange
                ->getStyle($cellRange)
                ->getFont()
                ->setBold(true);

            $bom_exchange
                ->getStyle($cellRange)
                ->getFill()
                ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                ->getStartColor()
                ->setARGB('FFFF00');
            $i++;
        }

        $bom_exchange->setCellValue("A$i", trim($item['position']) . ' '); //boşluk koydum excel .1 yerine oto olarak 0.1 yazdığı için

        if ($item['operation'] === 'delete')
            $bom_exchange->setCellValue("B$i", 'SİL');
        else {
            if ($item['material_added_starts_by'] === 'A7ETKBL')
                $bom_exchange->setCellValue("B$i", $item['material_added_starts_by'] . $item['material_added_number']);
            else
                $bom_exchange->setCellValue("B$i", $item['material_added_number']);
        }

        if (intval($item['is_accessory']) === 1) {
            $starters = array('A7E00', 'A7ETKBL', 'A7ET0', 'A7ET', 'A7E');
            foreach ($starters as $starter) {
                if (str_starts_with($item['material_deleted_number'], $starter)) {
                    $item['material_deleted_number'] = substr($item['material_deleted_number'], strlen($starter));
                    break;
                }
            }
            $bom_exchange->setCellValue("C$i", preg_replace('/^00/', '', $item['material_deleted_number']));
            $bom_exchange->setCellValue("I$i", 'Aksesuar');
        }
        else {
            if ($item['type'] === 'nachbau_row') {
                $bom_exchange->setCellValue("C$i", preg_replace('/^00/', '', $item['kmat']));
            } else {
                $bom_exchange->setCellValue("C$i", preg_replace('/^00/', '', $item['material_deleted_number']));
            }
        }

        $bom_exchange->setCellValue("D$i", $item['release_quantity']);
        $bom_exchange->setCellValue("E$i", $item['release_unit']);
        $bom_exchange->setCellValue("F$i", $item['kmat_name']);

        if ($item['type'] === 'nachbau_description') {
            $bom_exchange->setCellValue("F$i", implode("\n", array_filter(explode("V:", $item['kmat_name']), 'trim')));
            $bom_exchange->getStyle("F$i")->getAlignment()->setWrapText(true);
        }

        $bom_exchange->setCellValue("G$i", $typical);

        if ($item['panel_no'] === '000020' || (intval($item['released_dto_type_id']) === 3 && intval($item['is_accessory']) === 1))
            $bom_exchange->setCellValue("H$i", 'AKSESUAR');
        else
            $bom_exchange->setCellValue("H$i", $item['ortz_kz'] . ' ' . $item['panel_no']);

        if (intval($item['released_dto_type_id']) === 5) { //NACHBAU ERROR
            $notesCell = 'NACHBAU ERROR';
        } else if (intval($item['released_dto_type_id']) === 3) { //EXTENSION
            $notesCell = (!empty($item['dto_number']) ? $item['dto_number'] . "\n" : '') .
                         (!empty($item['dto_description']) ? html_entity_decode($item['dto_description']) : '');

            if (!empty($item['is_acccessory']) && intval($item['is_accessory']) === 1) {
                $notesCell .= "\n" . $item['ortz_kz'] . "\n" . $item['note'];
                if (!empty($item['extension_extra_note'])) {
                    $notesCell .= "\n" . $item['extension_extra_note'];
                }
            }
            else {
                $notesCell .= "\n" . $item['note'];
            }
        } else if (intval($item['released_dto_type_id']) === 2) { //SPARE
            $notesCell = (!empty($item['dto_number']) ? $item['dto_number'] . "\n" : '') .
                        (!empty($item['dto_description']) ? html_entity_decode($item['dto_description']) : '');
        }
        else if (intval($item['released_dto_type_id']) === 4) { //MIN PRI
            $notesCell = (!empty($item['dto_number']) ? $item['dto_number'] . "\n" : '') .
                         (!empty($item['dto_description']) ? html_entity_decode($item['dto_description']) . "\n\n" : '') .
                         'ÜRETİLMEYECEK';
        }
        else if (intval($item['released_dto_type_id']) === 6) {
            $notesCell = (!empty($item['dto_number']) ? $item['dto_number'] . "\n" : '') .
                (!empty($item['dto_description']) ? html_entity_decode($item['dto_description']) : '') . "\n" .
                'TİPİK BAZLI DEĞİŞİKLİK';
        }
        else {
            if (!empty($item['dto_number']) && !empty($item['affected_dto_numbers'])) {
                $affectedDtoDescriptions = getDtoAndDescriptionOfAffectedDtoNumbersSync($projectNo, $nachbauNo, $item['affected_dto_numbers']);
                $releaseTypeText = $item['release_type'] === 'Typical' ? 'TİPİK BAZLI DEĞİŞİKLİK' :
                                   ($item['release_type'] === 'Panel' ? 'PANO BAZLI DEĞİŞİKLİK' : '');
                $notesCell = $affectedDtoDescriptions . ($releaseTypeText ? "\n\n" . $releaseTypeText : '');
            } else if (!empty($item['dto_number'])) {
                $releaseTypeText = $item['release_type'] === 'Typical' ? 'TİPİK BAZLI DEĞİŞİKLİK' :
                                   ($item['release_type'] === 'Panel' ? 'PANO BAZLI DEĞİŞİKLİK' : '');
                $notesCell = $item['dto_number'] . "\n" .
                             (!empty($item['dto_description']) ? html_entity_decode($item['dto_description']) : '') .
                             ($releaseTypeText ? "\n" . $releaseTypeText : '');
            } else {
                $notesCell = !empty($item['dto_description']) ? html_entity_decode($item['dto_description']) : '';
            }
        }

        $bom_exchange->setCellValue("J$i", $notesCell);
        $bom_exchange->getStyle("J$i")->getAlignment()->setWrapText(true);

        if ($item['is_revision_change'] === '1') {
            $bom_exchange->setCellValue("K$i", $item['send_to_review_by']);
            $formattedDate = (new DateTime($item['created_at']))->format('d.m.Y H:i:s');
            $bom_exchange->setCellValue("L$i", $formattedDate);

        }

        if($item['position'] == '.1' || $item['position'] == '0'){
            $cellRange = "A$i".':'."H$i";
            $bom_exchange
                ->getStyle($cellRange)
                ->getFont()
                ->setBold(true);
        }

        if(isset($item['operation']) && $item['operation'] === 'delete'){
            setColors($bom_exchange, "A$i".':'."L$i", 'FFFFFF', 'FF0000', $borders=true);
        }
        else if(isset($item['operation']) && $item['operation'] === 'add'){
            setColors($bom_exchange, "A$i".':'."L$i", 'FFFFFF', '00B050', $borders=true);
        }
        else if(isset($item['operation']) && $item['operation'] === 'replace'){
            setColors($bom_exchange, "A$i".':'."L$i", 'FFFFFF', '008B8B', $borders=true);
        }

        if($item['description'] != ''){
            $descriptions = explode('V:', $item['description']);
            foreach ($descriptions as $description) {
                if(!empty(trim($description))){
                    $bom_exchange->setCellValue("F$i", 'V: '.trim($description));
                }
                $i++;
            }
        }else{
            $i++;
        }
    }
    $bom_exchange->getColumnDimension('A')->setWidth(10);
    $bom_exchange->getColumnDimension('B')->setWidth(20);
    $bom_exchange->getColumnDimension('D')->setWidth(8);
    $bom_exchange->getColumnDimension('E')->setWidth(8);
    $bom_exchange->getColumnDimension('F')->setWidth(55);
    $bom_exchange->getColumnDimension('G')->setWidth(16);
    $bom_exchange->getColumnDimension('H')->setWidth(20);
    $bom_exchange->getColumnDimension('I')->setWidth(16);
    $bom_exchange->getColumnDimension('J')->setWidth(50);
    $bom_exchange->getColumnDimension('K')->setWidth(24);
    $bom_exchange->getColumnDimension('L')->setWidth(16);


    setRowHeight($bom_exchange,1, 20);
    setRowHeight($bom_exchange,3, 30);
    setRowHeight($bom_exchange,5, 20);

    $cellNames = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I','J', 'K', 'L'];
    foreach ($cellNames as $cellName) {

        for($i=1; $i<=6; $i++) {
            setBorders($bom_exchange, $cellName.$i);
        }
    }

    $cellRange = $bom_exchange->calculateWorksheetDimension();
    $bom_exchange->getStyle($cellRange)
        ->getAlignment()
        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);


    $bom_exchange->getStyle($cellRange)
        ->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);

    $bom_exchange->getStyle('A7')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $bom_exchange->getStyle('A7')->getAlignment()->setWrapText(true);

    $bom_exchange->setAutoFilter('A7:L7');

    $cellRange = 'C3:C5';

    $bom_exchange->getStyle($cellRange)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
    $bom_exchange->getStyle($cellRange)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);


    $bom_exchange->getStyle($cellRange)->getFont()->setSize(18); // set font size to 18

    if ($excelOperation === 'downloadExcel') {
        downloadExcel($projectNo, $nachbauNo, $assemblyStart, $objPHPExcel, $bom_exchange, $isRevision);
    } else if ($excelOperation === 'copyToFolder') {
        $result = copyExcelIntoFolder($projectNo, $nachbauNo, $assemblyStart, $objPHPExcel, $bom_exchange, $isRevision);

        header('Content-Type: application/json');
        echo json_encode($result);
    }
}

function setCellAlignment($sheet, $cell, $horizontalAlignment = PHPExcel_Style_Alignment::HORIZONTAL_LEFT, $verticalAlignment = PHPExcel_Style_Alignment::VERTICAL_TOP)
{
    $style = $sheet->getStyle($cell);
    $alignment = $style->getAlignment();
    $alignment->setHorizontal($horizontalAlignment);
    $alignment->setVertical($verticalAlignment);
    $alignment->setWrapText(true);
}

function setCell($sheet, $data){
    foreach ($data as $item) {

        setCellValue($sheet, $item['cellName'].$item['rowNumber'], $item['cellValue']);
        $sheet->getStyle($item['cellName'].$item['rowNumber'])->getAlignment()->setWrapText($item['cellWrapText']);
        setCellFontSize($sheet, $item['cellName'].$item['rowNumber'], $item['cellFontSize']);
        setCellWidth($sheet,$item['cellName'], $item['cellWidth']);
        setRowHeight($sheet,$item['rowNumber'], $item['cellHeight']);
        $sheet->getStyle($item['cellName'].$item['rowNumber'])->getFont()->setName($item['fontName']);

        if($item['cellFontBold'])
            setCellFontBold($sheet, $item['cellName'].$item['rowNumber']);
        if($item['mergeTo'] !== '')
            mergeCells($sheet, $item['cellName'].$item['rowNumber'].':'.$item['mergeTo']);
        setColors($sheet, $item['cellName'].$item['rowNumber'], $item['cellFontColor'], $item['cellBgColor'], $item['border']);

        if ($item['rowNumber'] == 1 && $item['cellName'] >= 'A' && $item['cellName'] <= 'L') {
            $sheet->getStyle($item['cellName'].$item['rowNumber'])->getFont()->setItalic(true);
            $sheet->getStyle($item['cellName'].$item['rowNumber'])->getFont()->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);
        }
    }

    setRowAsHeader($sheet, 7, 'L');
}

function setRowHeight($sheet, $row, $height = 15)
{
    $sheet->getRowDimension($row)->setRowHeight($height);
}

function createPHPExcel(): PHPExcel
{
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->removeSheetByIndex(0);
    return $objPHPExcel;
}

function createSheet($objPHPExcel, $title) {
    $sheet = $objPHPExcel->createSheet();
    $sheet->setTitle($title);
    return $sheet;
}

function setColors($sheet, $cell, $fontColor, $bgColor, $borders=true)
{
    $styleArray = [
        'font' => [
            'bold' => true,
            'color' => array('rgb' => $fontColor),
        ],

    ];

    if($borders){
        $styleArray['borders'] = [
            'top' => array('style' => 'thick'),
            'right' => array('style' => 'thick'),
            'bottom' => array('style' => 'thick'),
            'left' => array('style' => 'thick')
        ];
    }

    $fill = [
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'color' => array('rgb' => $bgColor)
    ];
    cellColor($sheet, $cell, $styleArray, $fill);
}

function setBorders($sheet, $cells)
{
    $styleArray = [
        'borders' => [
            'top' => array('style' => 'thin'),
            'right' => array('style' => 'thin'),
            'bottom' => array('style' => 'thin'),
            'left' => array('style' => 'thin')
        ]
    ];
    $sheet->getStyle($cells)->applyFromArray($styleArray);
}

function setCellFontSize($sheet, $cell, $fontSize)
{
    $style = $sheet->getStyle($cell);
    $font = $style->getFont();
    $font->setSize($fontSize);
}

function setCellFontBold($sheet, $cell)
{
    $style = $sheet->getStyle($cell);
    $font = $style->getFont();
    $font->setBold(true);
}

function mergeCells($sheet, $cells)
{
    // Merge cells A1 to C1
    $sheet->mergeCells($cells); //(A1:C1)
}

function cellColor($sheet, $cells, $style_array = [], $fill = [])
{
    $sheet->getStyle($cells)->getFill()->applyFromArray($fill);
    $sheet->getStyle($cells)->applyFromArray($style_array);
}

function setCellValue($sheet, $cellName, $cellValue) {
    $sheet->setCellValue($cellName, $cellValue);
    return $sheet;
}

function setCellWidth($sheet, $column, $width = 50)
{
    // Set the width of the specified column
    // Set the width of column A to 20
    //$sheet->getColumnDimension('A')->setWidth(20);
    $sheet->getColumnDimension($column)->setWidth($width);
}

function setRowAsHeader($sheet, $rowNumber, $lastColumn) {
    $sheet->freezePane('A'.($rowNumber + 1)); // Freeze everything above Row 10 (Row 9 becomes sticky)
//    $sheet->setAutoFilter("A$rowNumber:$lastColumn$rowNumber");
}

function downloadExcel($projectNo, $nachbauNo, $assemblyStart, $objPHPExcel, $bom_exchange, $isRevision)
{
    SharedManager::saveLog('log_dtoconfigurator', "PROCESSING | BOM Change Excel Download Request | " . implode(' | ', $_POST));
    Journals::saveJournal("PROCESSING | BOM Change Excel Download Request | " . implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_ORDER_SUMMARY, ACTION_PROCESSING, implode(' | ', $_POST), "BOM Change Excel Download");

    try {
        $current_time = date("YmdHi");
        $fileName = 'BOM_CHANGE_' . $projectNo . '_' . $nachbauNo . '_' . $current_time . '.xlsx';

        if ($isRevision) {
            $destinationFolderPath = SharedManager::getProjectFilePath($projectNo) . "\\03. Mekanik";

            // Determine the next revision number by checking existing files
            $revisionNumber = getNextRevisionNumber($destinationFolderPath, $projectNo, $nachbauNo);
            $fileName = 'REV' . str_pad($revisionNumber, 2, '0', STR_PAD_LEFT) . '_BOM_CHANGE_' . $projectNo . '_' . $nachbauNo . '_' . $current_time . '.xlsx';
        }

        // Define paths
        $tempFilePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $fileName;

        // Put a password if not in local
        if (!in_array(SharedManager::$user_ip, ['127.0.0.1', '::1'])) {
            $password = getenv("DTO_Configurator_XLSX_Password");
            $objPHPExcel->getSecurity()->setLockWindows(true);
            $objPHPExcel->getSecurity()->setLockStructure(true);
            $objPHPExcel->getSecurity()->setWorkbookPassword($password);
            $bom_exchange->getProtection()->setSheet(true);
            $bom_exchange->getProtection()->setPassword($password);
        }

        // Save file to temp directory
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save($tempFilePath);

        if (!in_array(SharedManager::$user_ip, ['127.0.0.1', '::1'])) {
            // Copy file to network folder with user's name and surname appended
            $userName = SharedManager::$name ?? 'Unknown';
            $userSurname = SharedManager::$surname ?? 'User';

            if (!empty($assemblyStart)) {
                $date = DateTime::createFromFormat('d.m.Y', $assemblyStart);
                $formattedAssemblyStartDate = $date->format('Y.m.d');
            } else {
                $formattedAssemblyStartDate = "";
            }

            // Modify the filename for network storage
            $networkFileName =  $formattedAssemblyStartDate . '_BOM_CHANGE_' . $projectNo . '_' . $nachbauNo . '_' . $current_time . '_' . $userName . '_' . $userSurname . '_APPROVED.xlsx';
            $backupFilePath = "\\\\ad001.siemens.net\\dfs001\\File\\TR\\SI_DS_TR_OP\\Digital_Transformation\\DTO_Configurator_BOM_Change_Excels\\" . $networkFileName;

            if (!copy($tempFilePath, $backupFilePath)) {
                throw new Exception("Failed to copy process: " . $backupFilePath);
            }

//            // **Set network copy as read-only**
//            if (file_exists($backupFilePath)) {
//                chmod($backupFilePath, 0444); // Read-only for all users (Unix-based)
//            }
        }

        // Set headers to force download the file (user's file remains editable)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        // Clear any output in the buffer
        ob_clean();
        flush();

        // Output the file content
        readfile($tempFilePath);

        // Delete the temporary file
        unlink($tempFilePath);

        SharedManager::saveLog('log_dtoconfigurator', "RETURNED | BOM Change Excel Downloaded Successfully | " . implode(' | ', $_POST));
        Journals::saveJournal("RETURNED | BOM Change Excel Downloaded Successfully | " . implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_ORDER_SUMMARY, ACTION_VIEWED, implode(' | ', $_POST), "BOM Change Excel Download");

        return true;
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
        return false;
    }
}

function copyExcelIntoFolder($projectNo, $nachbauNo, $assemblyStart, $objPHPExcel, $bom_exchange, $isRevision)
{
    try {
        SharedManager::saveLog('log_dtoconfigurator', "PROCESSING | BOM Change Excel Copy to Folder Request | " . implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | BOM Change Excel Copy to Folder Request | " . implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_ORDER_SUMMARY, ACTION_PROCESSING, implode(' | ', $_POST), "BOM Change Excel Copy to Folder");

        $destinationFolderPath = SharedManager::getProjectFilePath($projectNo) . "\\03. Mekanik";

        $current_time = date("YmdHi");
        $fileName = 'BOM_CHANGE_' . $projectNo . '_' . $nachbauNo . '_' . $current_time . '.xlsx';

        if ($isRevision) {
            // Determine the next revision number by checking existing files
            $revisionNumber = getNextRevisionNumber($destinationFolderPath, $projectNo, $nachbauNo);
            $fileName = 'REV' . str_pad($revisionNumber, 2, '0', STR_PAD_LEFT) . '_BOM_CHANGE_' . $projectNo . '_' . $nachbauNo . '_' . $current_time . '.xlsx';
        }

        // Define paths
        $tempFilePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $fileName;

        // Put a password if not in local
        if (!in_array(SharedManager::$user_ip, ['127.0.0.1', '::1'])) {
            $password = getenv("DTO_Configurator_XLSX_Password");
            $objPHPExcel->getSecurity()->setLockWindows(true);
            $objPHPExcel->getSecurity()->setLockStructure(true);
            $objPHPExcel->getSecurity()->setWorkbookPassword($password);
            $bom_exchange->getProtection()->setSheet(true);
            $bom_exchange->getProtection()->setPassword($password);
        }

        // Save file to temp directory
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save($tempFilePath);

        if (!in_array(SharedManager::$user_ip, ['127.0.0.1', '::1'])) {
            // Copy file to network folder with user's name and surname appended
            $userName = SharedManager::$name ?? 'Unknown';
            $userSurname = SharedManager::$surname ?? 'User';

            if (!empty($assemblyStart)) {
                $date = DateTime::createFromFormat('d.m.Y', $assemblyStart);
                $formattedAssemblyStartDate = $date->format('Y.m.d');
            } else {
                $formattedAssemblyStartDate = "";
            }

            // Modify the filename for network storage
            $networkFileName =  $formattedAssemblyStartDate . '_BOM_CHANGE_' . $projectNo . '_' . $nachbauNo . '_' . $current_time . '_' . $userName . '_' . $userSurname . '.xlsx';
            $backupFilePath = "\\\\ad001.siemens.net\\dfs001\\File\\TR\\SI_DS_TR_OP\\Digital_Transformation\\DTO_Configurator_BOM_Change_Excels\\" . $networkFileName;

            if (!copy($tempFilePath, $backupFilePath)) {
                throw new Exception("Failed to copy process: " . $backupFilePath);
            }

            // **Set network copy as read-only**
            if (file_exists($backupFilePath)) {
                chmod($backupFilePath, 0444); // Read-only for all users (Unix-based)
            }
        }

        // Ensure destination folder path ends with directory separator
        $destinationFolderPath = rtrim($destinationFolderPath, '/\\') . DIRECTORY_SEPARATOR;

        // Full destination path
        $destinationFilePath = $destinationFolderPath . $fileName;

        // Create destination directory if it doesn't exist
        if (!is_dir($destinationFolderPath)) {
            if (!mkdir($destinationFolderPath, 0755, true)) {
                throw new Exception("Failed to create destination directory: " . $destinationFolderPath);
            }
        }

        // Copy file to destination folder
        if (!copy($tempFilePath, $destinationFilePath)) {
            throw new Exception("Failed to copy file to destination: " . $destinationFilePath);
        }

//        // Set file as read-only (if not local)
//        if (!in_array(SharedManager::$user_ip, ['127.0.0.1', '::1'])) {
//            if (file_exists($destinationFilePath)) {
//                chmod($destinationFilePath, 0444); // Read-only for all users
//            }
//        }

        // Delete the temporary file
        unlink($tempFilePath);

        // Log success
        SharedManager::saveLog('log_dtoconfigurator', "RETURNED | BOM Change Excel Copied to Folder Successfully | " . implode(' | ', $_POST));
        Journals::saveJournal("RETURNED | BOM Change Excel Copied to Folder Successfully | " . implode(' | ', $_POST), PAGE_PROJECTS, DESIGN_DETAIL_ORDER_SUMMARY, ACTION_VIEWED, implode(' | ', $_POST), "BOM Change Excel Copy to Folder");

        // Return success with file path info
        return [
            'success' => true,
            'message' => 'Excel file successfully copied to folder',
            'file_path' => $destinationFilePath,
            'file_name' => $fileName
        ];

    } catch (Exception $e) {
        // Clean up temp file if it exists
        if (isset($tempFilePath) && file_exists($tempFilePath)) {
            unlink($tempFilePath);
        }

        return [
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ];
    }
}

function getNextRevisionNumber($folderPath, $projectNo, $nachbauNo)
{
    $maxRevision = 0;

    // Ensure folder path ends with directory separator
    $folderPath = rtrim($folderPath, '/\\') . DIRECTORY_SEPARATOR;

    // Check if directory exists
    if (!is_dir($folderPath)) {
        // If directory doesn't exist, start with REV01
        return 1;
    }

    // Scan directory for files
    $files = scandir($folderPath);

    if ($files === false) {
        // If unable to scan, start with REV01
        return 1;
    }

    // Pattern to match: REV{number}_BOM_CHANGE_{projectNo}_{nachbauNo}_*.xlsx
    $pattern = '/^REV(\d+)_BOM_CHANGE_' . preg_quote($projectNo, '/') . '_' . preg_quote($nachbauNo, '/') . '_.*\.xlsx$/i';

    foreach ($files as $file) {
        if (preg_match($pattern, $file, $matches)) {
            $revisionNumber = intval($matches[1]);
            if ($revisionNumber > $maxRevision) {
                $maxRevision = $revisionNumber;
            }
        }
    }

    // Return next revision number
    return $maxRevision + 1;
}

function getProjectCharacteristics($projectNo, $panelType): array {
    $query = "SELECT 
                RatedVoltage AS rated_voltage,
                SCCurTime AS rated_short_circuit,
                BusbarCurrent AS rated_current
            FROM OneX_ProjectTechDataEE 
            WHERE FactoryNumber = :pNo";

    $result = DbManager::fetchPDOQueryData('MTool_INKWA', $query, [
        ':pNo' => $projectNo
    ])['data'] ?? [];

    return !empty($result) ? [
        'rated_voltage' => $result[0]['rated_voltage'] ?? '',
        'rated_short_circuit' => $result[0]['rated_short_circuit'] ?? '',
        'rated_current' => $result[0]['rated_current'] ?? ''
    ] : [
        'rated_voltage' => '',
        'rated_short_circuit' => '',
        'rated_current' => ''
    ];
}

function showBomAndKukoNotesOfNachbau($projectNo, $nachbauNo, $bom_exchange) {
    // Fetch BOM Notes with File Names
    $bom_query = "SELECT note, file_name FROM bom_notes WHERE project_number=:pNo AND nachbau_number=:nNo AND deleted IS NULL";
    $bom_notes_data = DbManager::fetchPDOQueryData('dto_configurator', $bom_query, [':pNo' => $projectNo, ':nNo' => $nachbauNo])['data'] ?? [];

    // Fetch KUKO Notes with DTO Numbers
    $kuko_query = "SELECT dto_number, kuko_note FROM kuko_notes WHERE project_number=:pNo AND nachbau_number=:nNo AND deleted IS NULL";
    $kuko_notes_data = DbManager::fetchPDOQueryData('dto_configurator', $kuko_query, [':pNo' => $projectNo, ':nNo' => $nachbauNo])['data'] ?? [];

    // Prepare KUKO Notes with DTO Numbers
    $kuko_notes_array = array_map(function ($note) {
        return "KUKO Notu: " . formatDtoNumber($note['dto_number']) . " - " . $note['kuko_note'];
    }, $kuko_notes_data);

    // Prepare BOM Notes
    $bom_notes_array = array_map(fn($note) => "BOM Notu: " . $note['note'], $bom_notes_data);

    // Merge BOM Notes and KUKO Notes
    $mergedNotesArray = array_merge($bom_notes_array, $kuko_notes_array);

    // Combine Notes with Double Line Breaks
    $mergedNotes = PHP_EOL . PHP_EOL . implode(PHP_EOL, $mergedNotesArray);

    $rowIndex = 6; // Row 6
    $imageStartX = 5; // Horizontal offset for side-by-side placement
    $imageY = 5; // Vertical offset
    $imageSpacing = 60; // Spacing between images in pixels

    foreach ($bom_notes_data as $key => $bom_note) {
        $file_name = $bom_note['file_name'];
        $image_path = "\\\\ad001.siemens.net\\dfs001\\File\\TR\\SI_DS_TR_OP\\Product_Management\\04_Design_Documents\\99_DTO_Configurator_Files\\1_BOM_Note_Images\\" . $file_name;

        if (file_exists($image_path)) {
            $drawing = new PHPExcel_Worksheet_Drawing();
            $drawing->setName('BOM Note Image');
            $drawing->setDescription('BOM Note Image');
            $drawing->setPath($image_path); // Path to the image
            $drawing->setHeight(50); // Adjust image height
            $drawing->setCoordinates("C$rowIndex"); // Target cell
            $drawing->setOffsetX($imageStartX + ($key * $imageSpacing)); // Horizontal offset for side-by-side placement
            $drawing->setOffsetY($imageY); // Vertical offset
            $drawing->setWorksheet($bom_exchange);
        }
    }

    // Add merged notes below the images in the same cell
    $bom_exchange->setCellValue("C$rowIndex", $mergedNotes);
    $bom_exchange->getStyle("C$rowIndex")->getAlignment()->setWrapText(true); // Enable text wrapping
    $bom_exchange->getStyle("C$rowIndex")->getFont()->getColor()->setRGB('FF0000'); // Set font color to red
    $bom_exchange->getRowDimension($rowIndex)->setRowHeight(100); // Set row height
    $bom_exchange->getColumnDimension("C")->setWidth(60); // Set column width

    return $mergedNotes;
}


function getDtoAndDescriptionOfAffectedDtoNumbersSync($projectNo, $nachbauNo, $affectedDtoNumbers){
    $affectedDtoNumbersArr = array_unique(explode('|', $affectedDtoNumbers));
    $allNoteData = '';

    foreach ($affectedDtoNumbersArr as $affectedDto) {
        $query = "SELECT description FROM tkforms WHERE dto_number LIKE :dtoNumber";
        $affectedDtoDescription = DbManager::fetchPDOQueryData('dto_configurator', $query, [':dtoNumber' => '%'. $affectedDto .'%'])['data'][0]['description'] ?? [];

        if (empty($affectedDtoDescription)) {
            $query = "SELECT description FROM nachbau_datas WHERE project_no = :pNo AND nachbau_no = :nNo AND kmat_name LIKE :dtoNumber AND description != '' LIMIT 1";
            $affectedDtoDescription = DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':dtoNumber' => '%'.$affectedDto.'%'])['data'][0]['description'] ?? [];

            //Description Formatting starts
            $splitParts = explode('V:', $affectedDtoDescription);
            $extractedParts = array_slice($splitParts, 2, 3);
            $affectedDtoDescription = trim(str_replace('Description:', '', implode(' ', $extractedParts)));
            //Description Formatting ends
        }

        $allNoteData .= $affectedDto . " - " . $affectedDtoDescription . "\n\n";
    }

    return trim($allNoteData);
}


function formatDtoNumber($dtoNumber): string
{
    // Remove everything after the first dot, including the dot
    $dtoNumber = preg_replace('/\.\d+/', '', $dtoNumber);

    // Remove ":: " from the beginning of the string
    $dtoNumber = preg_replace('/^:: /', '', $dtoNumber);

    return rtrim($dtoNumber, '.');
}

function fillKukoMatrixExcelSheet($kuko_matrix_sheet, $kukoMatrixData, $projectNo, $nachbauNo){
    // Extract Columns, Rows, Colors, and Notes from response
    $columns = $kukoMatrixData['columns'];
    $rows = $kukoMatrixData['rows'];
    $colors = $kukoMatrixData['colors'];
    $notes = $kukoMatrixData['notes'];

    // Header Styling (Yellow Background, Bold, Centered)
    $headerStyle = [
        'font' => ['bold' => true],
        'fill' => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFF00']],
        'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, 'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER],
        'borders' => ['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => ['rgb' => '000000']]]
    ];

    // Red Font Styling for Project & Nachbau Numbers
    $redFontStyle = [
        'font' => ['color' => ['rgb' => 'FF0000'], 'size' => 14, 'bold' => true],
        'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, 'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER],
        'borders' => ['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => ['rgb' => '000000']]]
    ];

    // Odd Row Styling (Whitesmoke Background)
    $oddRowStyle = ['fill' => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => ['rgb' => 'F2F2F2']]];

    // Bold Style for DTO Numbers
    $boldStyle = [
        'font' => ['bold' => true],
        'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, 'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER],
        'borders' => ['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => ['rgb' => '000000']]]
    ];

    // Border Style (Thin Black Borders for all cells)
    $borderStyle = [
        'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, 'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER],
        'borders' => ['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => ['rgb' => '000000']]]
    ];

    // Define row background colors based on dto_exclude_option
    $excludeStyles = [
        'excludeMechanicalTeam' => 'FFFF99', // Yellow
        'excludeAllTeams' => 'FFC7CE' // Red
    ];

    // **Write Project and Nachbau Numbers**
    $kuko_matrix_sheet->setCellValue('A1', 'Project Number');
    $kuko_matrix_sheet->setCellValue('B1', $projectNo);
    $kuko_matrix_sheet->setCellValue('A2', 'Nachbau Number');
    $kuko_matrix_sheet->setCellValue('B2', $nachbauNo);

    // **Apply Styles for A1 and A2 (Yellow Background)**
    $kuko_matrix_sheet->getStyle('A1:A2')->applyFromArray($headerStyle);

    // **Apply Styles for B1 and B2 (Red Font)**
    $kuko_matrix_sheet->getStyle('B1:B2')->applyFromArray($redFontStyle);

    // **Apply Borders to A1:B2**
    $kuko_matrix_sheet->getStyle('A1:B2')->applyFromArray($borderStyle);

    // **Shift Headers to Row 4**
    $kuko_matrix_sheet->setCellValue('A4', 'DTO Number');
    $kuko_matrix_sheet->setCellValue('B4', 'DTO Description');
    $kuko_matrix_sheet->setCellValue('C4', 'TR Açıklama');

    $colIndex = 'D';
    foreach ($columns as $typicalNumber) {
        $kuko_matrix_sheet->setCellValueExplicit($colIndex . '4', $typicalNumber, PHPExcel_Cell_DataType::TYPE_STRING);
        $kuko_matrix_sheet->getStyle($colIndex . '4')->applyFromArray($headerStyle);
        $colIndex++;
    }

    // **Add "Note" Column as Last Column**
    $noteCol = $colIndex;
    $kuko_matrix_sheet->setCellValue($noteCol . '4', 'Note');
    $kuko_matrix_sheet->getStyle($noteCol . '4')->applyFromArray($headerStyle);

    // Apply header styles for first three columns
    $kuko_matrix_sheet->getStyle('A4')->applyFromArray($headerStyle);
    $kuko_matrix_sheet->getStyle('B4')->applyFromArray($headerStyle);
    $kuko_matrix_sheet->getStyle('C4')->applyFromArray($headerStyle);

    // Set column widths
    $kuko_matrix_sheet->getColumnDimension('A')->setWidth(20);
    $kuko_matrix_sheet->getColumnDimension('B')->setWidth(50);
    $kuko_matrix_sheet->getColumnDimension('C')->setWidth(30);
    $kuko_matrix_sheet->getColumnDimension($noteCol)->setWidth(50); // Fixed width for Note column

    // **Shift Data to Start from Row 5**
    $rowIndex = 5;
    setRowAsHeader($kuko_matrix_sheet, 4, 'A');

    foreach ($rows as $dtoNumber => $dtoData) {
        $colIndex = 'A';

        // DTO Number (Bold + Borders)
        $kuko_matrix_sheet->setCellValue($colIndex . $rowIndex, formatDtoNumber($dtoData['DtoNumber']));
        $kuko_matrix_sheet->getStyle($colIndex . $rowIndex)->applyFromArray($boldStyle);
        $colIndex++;

        // DTO Description with word wrap
        $formattedDescription = str_replace("V:", "\nV:", $dtoData['Description']); // Add line breaks
        $formattedDescription = str_replace("V:", "", $formattedDescription);
        $kuko_matrix_sheet->setCellValue($colIndex . $rowIndex, $formattedDescription);
        $kuko_matrix_sheet->getStyle($colIndex . $rowIndex)->getAlignment()->setWrapText(true);
        $kuko_matrix_sheet->getStyle($colIndex . $rowIndex)->applyFromArray($borderStyle);
        $colIndex++;

        // Set row height to 150 for DTO Description
        $kuko_matrix_sheet->getRowDimension($rowIndex)->setRowHeight(150);

        // DTO Description TR
        $kuko_matrix_sheet->setCellValue($colIndex . $rowIndex, $dtoData['DescriptionTr']);
        $kuko_matrix_sheet->getStyle($colIndex . $rowIndex)->getAlignment()->setWrapText(true);
        $kuko_matrix_sheet->getStyle($colIndex . $rowIndex)->applyFromArray($borderStyle);
        $colIndex++;

        // Apply row background color for odd rows (only to columns A and B)
        if ($rowIndex % 2 !== 0) {
            $rowRange = "A{$rowIndex}:B{$rowIndex}:C{$rowIndex}";
            $kuko_matrix_sheet->getStyle($rowRange)->applyFromArray($oddRowStyle);
        }

        // **Fill Note Column (Last Column)**
        $noteText = '';
        $excludeOption = null;

        foreach ($notes as $note) {
            if ($note['dto_number'] === $dtoData['DtoNumber']) {
                $noteText = $note['kuko_note'];
                $excludeOption = $note['dto_exclude_option'];
                break;
            }
        }


        // Fill dynamic typical numbers & apply coloring from `data.colors`
        $coloredCells = []; // Store cells that have been individually colored
        foreach ($columns as $typicalNumber) {
            $value = $dtoData[$typicalNumber] ?? '';

            $kuko_matrix_sheet->setCellValueExplicit($colIndex . $rowIndex, $value, PHPExcel_Cell_DataType::TYPE_STRING);
            $kuko_matrix_sheet->getStyle($colIndex . $rowIndex)->applyFromArray($borderStyle);

            // **Apply color based on `data.colors`**
            if ($value === 'X' && isset($colors[$typicalNumber][$dtoNumber])) {
                $cellColor = $colors[$typicalNumber][$dtoNumber];

                // Convert color name to HEX codes
                $colorMap = [
                    'green' => 'C6EFCE',
                    'red' => 'E83A42',
                    'yellow' => 'FFEB9C',
                    'blue' => '9FC5E8',
                    'default' => 'FFFFFF'
                ];
                $bgColor = $colorMap[$cellColor] ?? 'FFFFFF';

                // Apply cell color
                $kuko_matrix_sheet->getStyle($colIndex . $rowIndex)->applyFromArray([
                    'fill' => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => ['rgb' => $bgColor]],
                    'font' => ['bold' => true]
                ]);

                if ($bgColor !== 'FFFFFF')
                    $coloredCells[] = $colIndex . $rowIndex;
            }

            $colIndex++;
        }

        // **Apply row color if `dto_exclude_option` exists but skip already colored 'X' cells**
        if (isset($excludeStyles[$excludeOption])) {
            for ($c = 'A'; $c <= $colIndex; $c++) {
                $cell = $c . $rowIndex;
                if (!in_array($cell, $coloredCells)) { // Only color unmarked cells
                    $kuko_matrix_sheet->getStyle($cell)->applyFromArray([
                        'fill' => [
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => ['rgb' => $excludeStyles[$excludeOption]]
                        ]
                    ]);
                }
            }
        }

        // **Add Comment to the Note Column Instead of DTO Number**
        $commentText = null;
        if ($excludeOption === 'excludeAllTeams') {
            $commentText = "DTO'ya Çalışılmayacak.";
        } elseif ($excludeOption === 'excludeMechanicalTeam') {
            $commentText = "Mekanik Değişiklik Olmayacak.";
        }

        if ($commentText) {
            $commentCell = $kuko_matrix_sheet->getComment($noteCol . $rowIndex); // Attach comment to the Note column
            $commentCell->setAuthor('System');
            $commentCell->setText(new PHPExcel_RichText());
            $commentRun = $commentCell->getText()->createTextRun($commentText);
            $commentRun->getFont()->setBold(true);
        }

        $kuko_matrix_sheet->setCellValue($noteCol . $rowIndex, $noteText);
        $kuko_matrix_sheet->getStyle($noteCol . $rowIndex)->getAlignment()->setWrapText(true);
        $kuko_matrix_sheet->getStyle($noteCol . $rowIndex)->applyFromArray($borderStyle);

        $rowIndex++;
    }

    $headerRow = 4;

    // Determine the last column with data in the header row since the data can be dynamic
    $highestColumn = $kuko_matrix_sheet->getHighestColumn($headerRow); // for exp column 'G'

    // Set the auto filter on the header row range
    $kuko_matrix_sheet->setAutoFilter("A{$headerRow}:{$highestColumn}{$headerRow}");
}
