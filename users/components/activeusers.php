<?php 
$css = "<style>
  /* Define the hover effect */
  img.enlarge-on-hover {
    transition: transform 0.1s ease;
  }

  /* Apply the effect on hover */
  img.enlarge-on-hover:hover {
    transform: scale(2); /* Increase the scale to make it larger */
  }
</style>
";
error_reporting(E_ERROR | E_PARSE);
include_once $_SERVER["DOCUMENT_ROOT"] . "/checklogin.php";
$usercount = 50;
echo getActiveUsers($usercount, $css);
 
function getActiveUsers($usercount, $css) {
	
    $query = "SELECT
				u.email AS UserEmail,
				u.gid AS GID,
				u.name AS Name,
				u.surname AS Surname,
				u.title AS Title,
				u.country AS Country,
				u.office AS Office,
				u.current_org_code AS OrgCode,
				ut.updated AS LastLogin
			FROM
				users u
			INNER JOIN
				user_tokens ut ON u.email = ut.email
			WHERE
				ut.updated >= NOW() - INTERVAL 1 DAY
			ORDER BY
				ut.updated DESC
			";
		
		
	$result = DbManager::fetchPDOQueryData('php_auth', $query)["data"];
	$users = [];
	$style = "border-radius: 50%;width: auto;max-width: 65px;max-height: 60px;height: auto;margin: 0.2rem;border: 2px solid #1a9599;box-shadow: 3px 1px 3px -1px #023638; ";
	$link = "src='users/?gid=";
	$html = "";
	$userInfo = SharedManager::getUser();
	
	// Get current user's GID safely
	$currentUserGid = null;
	if (isset($userInfo['GID'])) {
		$currentUserGid = $userInfo['GID'];
	} elseif (method_exists('SharedManager', 'getUser')) {
		$userInfo = SharedManager::getUser();
		$currentUserGid = $userInfo['GID'] ?? null;
	}
	
	// Add current user's image first
	if ($currentUserGid) {
		$html .= "<img class='enlarge-on-hover' style='" . $style . "' " . $link . $currentUserGid . "'></a>";
	}
	
	$i = 0;
	$j = 0;
    foreach ($result as $row) {
		$users[] = array(
			"email" => $row["UserEmail"],
			"gid" => $row["GID"],
			"name" => $row["Name"],
			"surname" => $row["Surname"],
			"country" => $row["Country"],
			"office" => $row["Office"],
			"title" => $row["Title"],
			"orgcode" => $row["OrgCode"],
			"lastlogin" => $row["LastLogin"]
			);
			
		$title = $row["Name"] . " " . $row["Surname"]. "\nTitle: " . $row["Title"] ."\nGID: " . $row["GID"] ."\nOrg Code: " . $row["OrgCode"] ."\nCountry: " . $row["Country"] ."\nLocation: " . $row["Office"] ."\nLogin Time: " . $row["LastLogin"];
		
		$email = $row["UserEmail"];
		$gid = $row["GID"];
		$file =  $_SERVER["DOCUMENT_ROOT"] . "/users/pics/" . $gid . ".png";
		
		if(empty($gid)){
			$html .= "";
		}
		else if (!file_exists($file)){
			$html .= "";
		}
		else if (filesize($file) < 13000) {
			$html .= "";
		}
		else {			
			if($i < $usercount && $gid != $currentUserGid) {
				$html .= "<a target='_blank' href='https://teams.microsoft.com/l/chat/0/0?users=" . $email . "'><img class='enlarge-on-hover' title='" . $title . "' style='" . $style . "' " . $link . $gid . "'></a>";					
				$i++;
			}
		}
		$j++;
    }
	
	if(!sizeof($users)) 
		die("no active user for the last 24 hours");
		 
	$html =" <b>$j</b> Active users in last 24 hours, showing last $i users.<br>" . $html;
	
	return $css.$html;
}