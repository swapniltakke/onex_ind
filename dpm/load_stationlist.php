<?php
include('shared/CommonManager.php');
$sql = "SELECT
    st.station_id,
    st.station_name,
    st.stage_id,
    GROUP_CONCAT(DISTINCT sg.stage_name) AS stage_names,
    st.product_id,
    GROUP_CONCAT(DISTINCT pd.product_name) AS product_names,
    st.Machine_name
FROM
    tbl_station st
    LEFT JOIN tbl_stage sg ON FIND_IN_SET(sg.stage_id, st.stage_id) > 0
    LEFT JOIN tbl_product pd ON FIND_IN_SET(pd.product_id, st.product_id) > 0
GROUP BY
    st.station_id, st.station_name, st.Machine_name
ORDER BY st.station_id DESC";
$res =  DbManager::fetchPDOQuery('spectra_db', $sql)["data"];
$cnt = count($res);
$product_info =array();
if($cnt > 0) {
    $sr=1;
	foreach ($res as $result) {
        $product_data['sr_no']= $sr;$sr++;
        $product_data['station_id'] = trim($result['station_id']);
        $product_data['station_name'] = trim($result['station_name']);
        $product_data['stages'] = $result['stage_names'];
        $product_data['product_name'] = $result['product_names'];
        $product_data['machine_name'] = trim($result['Machine_name']);
        $product_data['action'] ='<a class="btn btn-default btn-circle" title="Modify" href="add_stationlist.php?station_id='.trim($result['station_id']).'"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;<a class="btn btn-danger btn-circle"  data-placement="top" data-original-title=""  onclick=\'return confirm("Are you sure you want to delete this Station?");\' title="" href="delete_station.php?station_id='.trim($result['station_id']).'" ><i class="fa fa-trash"></i></a>'; 
        $product_info[] = $product_data;
    }
}
$results = array(
    "sEcho" => 1,
    "iTotalRecords" => count($product_info),
    "iTotalDisplayRecords" => count($product_info),
    "aaData" => $product_info);

echo json_encode($results);
?>