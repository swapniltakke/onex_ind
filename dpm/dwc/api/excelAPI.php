<?php
// download_excel.php
require '../../dpm/vendor/autoload.php';
require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/api/MToolManager.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/shared.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\RichText\RichText;

function listNotes($openCloseState,$filterParameter) {
    global $replaceSubProductType;
    $pdo_params = [];
    // SharedManager::print($filterParameter);exit;
    $data = json_decode($filterParameter, true);
    //SharedManager::print($data);

    // Method 1: Using foreach loop
    foreach ($data['projectNo'] as $projectNumber) {
        if($projectNumber !== "all" && !is_numeric($projectNumber)) {
            returnHttpResponse(400, "Incorrect project number");
        }
    }
    
    $pdo_params = [":noteStatus" => $openCloseState];
    $query = "SELECT tn.*, if(tn.createdby=:param_created_by,1,0) as hasAccessToDelete 
                FROM vw_assembly_notes as tn 
                WHERE tn.notestatus= :noteStatus";
    $pdo_params = array_merge($pdo_params, [":param_created_by" => SharedManager::getUser()["Email"]]);
    
    if ($openCloseState == "-1") {
        $pdo_params = [];
        $query = "SELECT * FROM vw_assembly_notes as tn WHERE tn.notestatus in (0,1)";
    }
    
    $start_date = $_GET['start_date'] ?? null;
    $finish_date = $_GET['finish_date'] ?? null;
    $firstOfMonth = date('Y-m-01');
    
    $date_query = "";
    if ($data['startDate'] != null && $data['finishDate'] != null) {
        $start_date = date("Y-m-d", strtotime($data['startDate']));
        $finish_date = date("Y-m-d", strtotime($data['finishDate']));
        $date_query = " and DATE_FORMAT(tn.created_raw, '%Y-%m-%d') >= :start_date 
                        and DATE_FORMAT(tn.created_raw, '%Y-%m-%d') <= :finish_date";
        $pdo_params = array_merge($pdo_params, [
            ":start_date" => $start_date, 
            ":finish_date" => $finish_date
        ]);
    } else {
        $date_query = " and DATE_FORMAT(tn.created_raw, '%Y-%m-%d') >= :first_of_month";
        $pdo_params = array_merge($pdo_params, [":first_of_month" => $firstOfMonth]);
    }
    
    // Corrected project number handling
    if (isset($data['projectNo']) && !empty($data['projectNo'])) {
        $in_params = [];
        foreach ($data['projectNo'] as $key => $projectNo) {
            $param_name = ":projectNo_" . $key;
            $in_params[] = $param_name;
            $pdo_params[$param_name] = $projectNo;
        }
        $date_query .= " and tn.projectNo IN (" . implode(',', $in_params) . ")";
    }

    if (isset($data['category']) && !empty($data['category'])) {
        $cat_params = [];
        foreach ($data['category'] as $key => $categoryName) {
            $param_name = ":category_" . $key;
            $cat_params[] = $param_name;
            $pdo_params[$param_name] = $categoryName;
        }
        $date_query .= " and tn.category IN (" . implode(',', $cat_params) . ")";
    }

    if (isset($data['mainCategory']) && !empty($data['mainCategory'])) {
        $mcat_params = [];
        foreach ($data['mainCategory'] as $key => $mainCategory) {
            $param_name = ":mainCategory_" . $key;
            $mcat_params[] = $param_name;
            $pdo_params[$param_name] = $mainCategory;
        }
        $date_query .= " and tn.mainCategory IN (" . implode(',', $mcat_params) . ")";
    }

    if (isset($data['subCategory']) && !empty($data['subCategory'])) {
        $scat_params = [];
        foreach ($data['subCategory'] as $key => $subCategory) {
            $param_name = ":subCategory_" . $key;
            $scat_params[] = $param_name;
            $pdo_params[$param_name] = $subCategory;
        }
        $date_query .= " and tn.subCategory IN (" . implode(',', $scat_params) . ")";
    }

    if (isset($data['missingCategory']) && !empty($data['missingCategory'])) {
        $miscat_params = [];
        foreach ($data['missingCategory'] as $key => $missingCategory) {
            $param_name = ":missingCategory_" . $key;
            $miscat_params[] = $param_name;
            $pdo_params[$param_name] = $missingCategory;
        }
        $date_query .= " and tn.missingCategory IN (" . implode(',', $miscat_params) . ")";
    }
    
    $query .= $date_query;
    
    $projectNosForQuery = "";
    $data["data"] = [];
    $response["data"] = DbManager::fetchPDOQueryData('assembly_items', $query, $pdo_params)["data"];
    // SharedManager::print($response["data"]);
    $openNoteStatusCount = 0;
    $closeNoteStatusCount = 0;
    foreach ($response["data"] as $key => $value) {
        $data["data"][$value["projectNo"]][$value["panelno"]][] = $value;
        $projectNosForQuery .= $value["projectNo"] . ",";
        if ($value["notestatus"] == 1) {
            $closeNoteStatusCount = $closeNoteStatusCount + 1;
        } else {
            $openNoteStatusCount = $openNoteStatusCount + 1;
        }
    }
    if (count($response["data"]) === 0) {
        echo json_encode($response, JSON_THROW_ON_ERROR);
        exit;
    }
    $projectNosForQuerySubstr = substr($projectNosForQuery, 0, -1);
    $projectNosForQuery = array_unique(explode(',', $projectNosForQuerySubstr));

    $responseForProjectDetails = MToolManager::searchMultipleProjects($projectNosForQuery);
    foreach ($responseForProjectDetails as $keys => $values) {
        foreach ($data["data"][$values["FactoryNumber"]] as $key => $value) {
            foreach ($data["data"][$values["FactoryNumber"]][$key] as $k => $v) {
                $data["data"][$values["FactoryNumber"]][$key][$k]["projectName"] = $values["ProjectName"];
                $data["data"][$values["FactoryNumber"]][$key][$k]["product"] = $values["Product"];
                $data["data"][$values["FactoryNumber"]][$key][$k]["productType"] = $replaceSubProductType[$values["Product"]] ?? "-";
            }
        }
    }

    $response["data"] = [];
    foreach ($data["data"] as $keys => $values) {
        foreach ($values as $key => $value) {
            foreach ($value as $k => $v) {
                $v['note'] = html_entity_decode($v['note']);
                $response["data"][] = $v;
            }
        }
    }

    if (!empty($data['searchValue'])) {
        $searchValue = strtolower(trim($data['searchValue']));
        $response["data"] = array_filter($response["data"], function($note) use ($searchValue) {
            // Convert all searchable fields to lowercase for case-insensitive search
            return 
                // Project Details
                strpos(strtolower($note['projectNo'] ?? ''), $searchValue) !== false ||
                strpos(strtolower($note['projectName'] ?? ''), $searchValue) !== false ||
                strpos(strtolower($note['panelno'] ?? ''), $searchValue) !== false ||
                strpos(strtolower($note['product'] ?? ''), $searchValue) !== false ||
                
                // Categories
                strpos(strtolower($note['category'] ?? ''), $searchValue) !== false ||
                strpos(strtolower($note['mainCategory'] ?? ''), $searchValue) !== false ||
                strpos(strtolower($note['subCategory'] ?? ''), $searchValue) !== false ||
                strpos(strtolower($note['missingCategory'] ?? ''), $searchValue) !== false ||
                
                // Other Details
                strpos(strtolower($note['note'] ?? ''), $searchValue) !== false ||
                strpos(strtolower($note['materialnolist'] ?? ''), $searchValue) !== false ||
                strpos(strtolower($note['createdby'] ?? ''), $searchValue) !== false ||
                strpos(strtolower($note['updatedby'] ?? ''), $searchValue) !== false;
        });
    }
    
    return $response;
}

function generateExcelFromNotes($openCloseState, $filterParams) {
    try {
        $notesData = listNotes($openCloseState, $filterParams);
        // exit;
        if (empty($notesData['data'])) {
            echo "No data available to export";
            return;
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Assembly Notes');

        // Set up headers
        $headers = ['Status', 'Project No', 'Project Name', 'Panel No', 'Product Type', 'Category', 'Main Category', 'Sub Category', 'Missing Category', 'Rework Note', 'Materials', 'Created By', 'Created On' , 'Updated By', 'Updated On', 'ECR', 'Idle'];
        foreach (range('A', 'Q') as $key => $column) {
            $sheet->setCellValue($column . '1', $headers[$key]);
            $sheet->getColumnDimension($column)->setWidth(20);
        }

        // Make Note column wider
        $sheet->getColumnDimension('J')->setWidth(80);

        // Style headers
        $headerStyle = $sheet->getStyle('A1:Q1');
        $headerStyle->getFont()->setBold(true);
        $headerStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $headerStyle->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('CCCCCC');

        // Add data rows
        $row = 2;
        foreach ($notesData['data'] as $note) {
            $sheet->setCellValue('A' . $row, $note['notestatus'] ? 'Closed' : 'Open');
            $sheet->setCellValue('B' . $row, $note['projectNo']);
            $sheet->setCellValue('C' . $row, $note['projectName']);
            $sheet->setCellValue('D' . $row, $note['panelno']);
            $sheet->setCellValue('E' . $row, $note['product']);
            $sheet->setCellValue('F' . $row, $note['category']);
            $sheet->setCellValue('G' . $row, $note['mainCategory']);
            $sheet->setCellValue('H' . $row, $note['subCategory']);
            $sheet->setCellValue('I' . $row, $note['missingCategory']);            

            // Create RichText object for the note
            $richText = new RichText();
            
            // Get the note content and decode HTML entities
            $noteContent = html_entity_decode($note['note'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            
            // Split content by HTML tags while preserving the tags
            $parts = preg_split('/<([^>]+)>/', $noteContent, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

            $isStrikethrough = false;
            $isBold = false;
            $isNewLine = false;
            $currentText = '';
            $listBuffer = '';
            $inOrderedList = false;
            $inUnorderedList = false;
            $listCounter = 1;
            $inParagraph = false;
            $paragraphText = '';
            $listItems = [];
            $listItemsFormat = [];

            foreach ($parts as $part) {
                // Handle paragraph
                if ($part === 'p') {
                    $inParagraph = true;
                    continue;
                }
                if ($part === '/p') {
                    $inParagraph = false;
                    if (!empty($paragraphText)) {
                        $richText->createTextRun($paragraphText . "\n\n");
                        $paragraphText = '';
                    }
                    continue;
                }

                // Handle ordered list
                if ($part === 'ol') {
                    $inOrderedList = true;
                    $listCounter = 1;
                    $listItems = [];
                    $listItemsFormat = [];
                    continue;
                }
                if ($part === '/ol') {
                    $inOrderedList = false;
                    // Process list items
                    foreach ($listItems as $index => $item) {
                        $run = $richText->createTextRun(($index + 1) . ". " . $item . "\n");
                        
                        // Apply strikethrough if needed
                        if (isset($listItemsFormat[$index]['strikethrough']) && $listItemsFormat[$index]['strikethrough']) {
                            $run->getFont()->setStrikethrough(true);
                        }
                        
                        // Apply bold if needed
                        if (isset($listItemsFormat[$index]['bold']) && $listItemsFormat[$index]['bold']) {
                            $run->getFont()->setBold(true);
                        }
                    }
                    $listItems = [];
                    $listItemsFormat = [];
                    $richText->createTextRun("\n");
                    continue;
                }

                // Handle unordered list
                if ($part === 'ul') {
                    $inUnorderedList = true;
                    $listItems = [];
                    $listItemsFormat = [];
                    continue;
                }
                if ($part === '/ul') {
                    $inUnorderedList = false;
                    // Process list items
                    foreach ($listItems as $index => $item) {
                        $run = $richText->createTextRun("• " . $item . "\n");
                        
                        // Apply strikethrough if needed
                        if (isset($listItemsFormat[$index]['strikethrough']) && $listItemsFormat[$index]['strikethrough']) {
                            $run->getFont()->setStrikethrough(true);
                        }
                        
                        // Apply bold if needed
                        if (isset($listItemsFormat[$index]['bold']) && $listItemsFormat[$index]['bold']) {
                            $run->getFont()->setBold(true);
                        }
                    }
                    $listItems = [];
                    $listItemsFormat = [];
                    $richText->createTextRun("\n");
                    continue;
                }

                // Handle list items
                if ($part === 'li') {
                    $listBuffer = '';
                    continue;
                }
                if ($part === '/li') {
                    if (!empty($listBuffer)) {
                        $listItems[] = strip_tags($listBuffer);
                        $listItemsFormat[] = [
                            'strikethrough' => strpos($listBuffer, '<strike>') !== false,
                            'bold' => strpos($listBuffer, '<b>') !== false
                        ];
                        $listBuffer = '';
                    }
                    continue;
                }

                // Handle strikethrough
                if ($part === 's' || $part === 'strike') {
                    $isStrikethrough = true;
                    continue;
                }
                if ($part === '/s' || $part === '/strike') {
                    $isStrikethrough = false;
                    continue;
                }

                // Handle bold
                if ($part === 'strong' || $part === 'b') {
                    $isBold = true;
                    continue;
                }
                if ($part === '/strong' || $part === '/b') {
                    $isBold = false;
                    continue;
                }

                // Handle line break
                if ($part === 'br') {
                    $isNewLine = true;
                    continue;
                }
                if ($part === '/br') {
                    $isNewLine = true;
                    continue;
                }
                // SharedManager::print($part);
                // Handle content
                if (!preg_match('/^[\/]?(?:div|span|p|br|ul|ol|li|strike|s|strong|b)$/i', $part) && trim($part) !== '') {
                    // echo __LINE__."<br>";
                    $text = str_replace(['<br>', '<br/>', '<br />', '</li>'], "\n", $part);
                    // SharedManager::print($text);
                    if ($inParagraph) {
                        // echo __LINE__."<br>";
                        if ($isNewLine) {
                            // echo __LINE__."<br>";
                            $richText->createTextRun($text . "\n");
                        } else {
                            // echo __LINE__."<br>";
                            $paragraphText .= $text;
                        }
                    } elseif ($inOrderedList || $inUnorderedList) {
                        // echo __LINE__."<br>";
                        // Modify list buffer handling to preserve formatting
                        if ($isStrikethrough) {
                            // echo __LINE__."<br>";
                            $listBuffer .= '<strike>' . $text . '</strike>';
                        } elseif ($isBold) {
                            // echo __LINE__."<br>";
                            $listBuffer .= '<b>' . $text . '</b>';
                        } elseif ($isNewLine) {
                            // echo __LINE__."<br>";
                            $richText->createTextRun($text . "\n");
                        } else {
                            // echo __LINE__."<br>";
                            $listBuffer .= $text;
                        }
                    } else {
                        // echo __LINE__."<br>";
                        if ($isStrikethrough) {
                            // echo __LINE__."<br>";
                            $run = $richText->createTextRun($text);
                            $run->getFont()->setStrikethrough(true);
                        } elseif ($isBold) {
                            // echo __LINE__."<br>";
                            $run = $richText->createTextRun($text);
                            $run->getFont()->setBold(true);
                        } elseif ($isNewLine) {
                            // echo __LINE__."<br>";
                            $richText->createTextRun($text . "\n");
                        } else {
                            // echo __LINE__."<br>";
                            $richText->createTextRun($text);
                        }
                    }
                }
            }

            $debugText = $richText->getPlainText();
            // echo "RichText Content: " . $debugText."<br>";
            // Handle any remaining content
            if (!empty($paragraphText)) {
                $richText->createTextRun($paragraphText . "\n");
            }

            $sheet->getCell('J' . $row)->setValue($richText);

            $sheet->setCellValue('K' . $row, $note['materialnolist']);
            $sheet->setCellValue('L' . $row, $note['createdby']);
            $sheet->setCellValue('M' . $row, date('Y-m-d H:i:s', strtotime($note['created'])));
            $sheet->setCellValue('N' . $row, $note['updatedby']);
            $sheet->setCellValue('O' . $row, $note['updated']);
            $sheet->setCellValue('P' . $row, $note['ecrTime']);
            $sheet->setCellValue('Q' . $row, $note['idleTime']);
            
            // Set row styling
            $sheet->getRowDimension($row)->setRowHeight(-1);
            $sheet->getStyle('J' . $row)->getAlignment()
                ->setWrapText(true)
                ->setVertical(Alignment::VERTICAL_TOP);

            $row++;
        }
        // exit("sheru");
        // Apply borders and alignment to all cells
        $styleArray = [
            'alignment' => [
                'vertical' => Alignment::VERTICAL_TOP,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A1:Q' . ($row - 1))->applyFromArray($styleArray);

        // Clear any previous output
        if (ob_get_length()) ob_end_clean();

        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="OneX_Assembly_Notes_' . date('Y-m-d_H-i-s') . '.xlsx"');
        header('Cache-Control: max-age=0');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');

        // Create Excel file
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;

    } catch (Exception $e) {
        die('Error generating Excel file: ' . $e->getMessage());
    }
}

// Handle the export action
if (isset($_GET['action']) && $_GET['action'] === 'export_notes') {
    $filterParams = $_POST['filterParams'] ?? '{}';
    generateExcelFromNotes($_GET['openCloseState'], $filterParams);
    exit;
}
?>