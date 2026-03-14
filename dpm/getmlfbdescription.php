<?php
include('shared/CommonManager.php');
$scanqrcode = $_POST['scanqrcode'];
$scanqrcode = substr($scanqrcode, 8);
$scanqrcode = strtok($scanqrcode, '/');
$scanqrcode = substr($scanqrcode, 0, -10);

$sql = "SELECT * FROM mlfb_details WHERE material_description=:material_description";
$result = DbManager::fetchPDOQueryData('spectra_db', $sql, [":material_description" => $scanqrcode])["data"][0];
$html = "";
if (is_array($result) && $result['po_text'] != "") {
	$mlfb_description = $result['po_text'];
	// SharedManager::print($mlfb_description);
	// $data = "SIEMENS MAKE TROLLEY MOUNTING VCB SION;;RATED VOLTAGE        :12kV<(>,<)>;BREAKING CAPACITY    :26.3 kA<(>,<)>;RATED CURRENT        :1250 A<(>,<)>;HV/ IMPULSE LEVEL    :28/75 kV<(>,<)>;MOTOR VOLTAGE        :230V AC<(>,<)>;CLOSING  COIL Y9     :110V DC<(>,<)>;OPENING COIL Y1      :110V DC<(>,<)>;2ND TRIP COIL 3AX1101 Y2       :NA;CURENT OPP. RELEASE 3AX1102 Y4 :NA;CURENT OPP. RELEASE 3AX1102Y Y4:NA;U/V RELEASE 3AX1103 Y7         :NA;SOCKET TERMINAL                :64 PIN,12NO+12NC<(>,<)>;OPERATING FREQUENCY            :50 Hz<(>,<)>;;;With Additional Z nos as below:;W69 - For Channel partner product;E46 - 26.3kV instead of 25kV;D91 - Shorter cover on contact arm side;F38 - Rated Operating Sequence 0-0.3s-CO-3min-CO.;C25 - M25 Pole Shell instead of M31 Pole shell";
	// SharedManager::print($data);
	$data = str_replace('&lt;(&gt;,&lt;)&gt;', '<(>,<)>', $mlfb_description);
	$data = trim($data, '" ;');
	$data = preg_replace('/,+/', ',', $data);
	$data = preg_replace('/;+/', ';', $data);
	$data = str_replace(',:', ':', $data);
	// SharedManager::print($data);
	
	$sections = explode(';', $data);
	$html = "<b>MLFB DESCRIPTION</b><div class='popup-content'><br>";
	foreach ($sections as $index => $section) {
		$section = trim($section);
		// Check if the section is meant for key-value pairs
		if (strpos($section, ':') !== false) {
			list($key, $value) = explode(':', $section);

			// Remove anything after <(>,<)> in the value string
			if (strpos($value, '<(>,<)>') !== false) {
				$value = trim(explode('<(>,<)>', $value)[0]);
			}

			if (strpos($section, "With Additional") !== false) {
				$html .= "<br><p><strong>" . trim($key) . ":</strong> " . trim($value) . "</p>";
			} else {
				$html .= "<p><strong>" . trim($key) . ":</strong> " . trim($value) . "</p>\n";
			}
		} else if (!empty($section)) {
			if ($index > 0 && strpos($sections[$index - 1], ':') === false) {
				// $html .= "<p></p>\n";
			}
			if (strpos($section, "SIEMENS") !== false) {
				$html .= "<p>" . trim($section) . "</p><br>";
			} else {
				$html .= "<p>" . trim($section) . "</p>\n";
			}
		}
	}

	$html .= "<p></p>\n";
	$html .= "</div>";
} else {
	$html = "There is no MLFB description available";
}
echo $html;
?>