<?php
session_start();
$user = $_SESSION['username'];
include('shared/CommonManager.php');
include('header.php');
include('menustamping.php');

// Retrieve the data based on the ID passed in the URL
$id = $_GET['id'];
$product_id = $_GET['product_id'];
$station_id = $_GET['station_id'];
$results = getStampingChecklistData($id,$product_id,$station_id);

function getStampingChecklistData($id,$product_id,$station_id) {
  $sql = "SELECT c2.checklist_item,c2.id,c1.check_req,c1.text_req,c1.text_lable_names
        FROM tbl_checklistdetails c1
        INNER JOIN tbl_checklist c2 ON c1.checklist_id=c2.id
        INNER JOIN tbl_stage c3 ON c1.stage_id=c3.stage_id
        INNER JOIN tbl_product p ON c1.product_id =p.product_id
        WHERE  c3.stage_id=:stage_id AND p.product_id=:product_id
        AND concat(',', c1.station_id, ',') LIKE :station_id ORDER BY c2.id ASC";	
  $query = DbManager::fetchPDOQueryData('spectra_db', $sql, [":stage_id" => "8", ":product_id" => "$product_id", ":station_id" => "%,$station_id,%"])["data"];
  // SharedManager::print($query);

  $main_sql = "SELECT * FROM tbl_transactions WHERE stage_id=:stage_id AND tr_id=:id GROUP BY barcode ORDER BY tr_id DESC";
  $main_res = DbManager::fetchPDOQueryData('spectra_db', $main_sql, [":stage_id" => "8", ":id" => "$id"])["data"][0];
  // SharedManager::print($main_res);

      $actual_output_merge = explode("||",$main_res['actual_output']);
      $actual_output = explode(",",$actual_output_merge[0]);
      // SharedManager::print($actual_output);
      $actual_output_manufacturing = explode(",",$actual_output_merge[1]);
      // SharedManager::print($actual_output_manufacturing);
      $remark_merge = explode("||",$main_res['remark_comp']);
      $remark = trim($remark_merge[0],"'");
      $remark = explode("','",$remark);
      $remark_manufacturing = trim($remark_merge[1],"'");
      $remark_manufacturing = explode("','",$remark_manufacturing);
 

  $sr = 1;
  foreach ($query as $key => $data) {
      $stage_data['sr_no']= $sr;
      $sr++;
      $stage_data['check_item'] = $data['checklist_item'];
      $stage_data['actual_opt'] = $data['check_req'];
      $stage_data['actual_opt_manufacturing'] = $data['check_req'];
      $display_remark = "";
      $display_remark_manufacturing = "";
      $stage_data['actual_opt_checked'] = "2";
      if ($actual_output[$key] == "1") {
          $stage_data['actual_opt_checked'] = "1";
      } 
      if ($actual_output[$key] == "0") {
          $stage_data['actual_opt_checked'] = "0";
      }
      if ($remark[$key] != "") {
          $display_remark = ($remark[$key] == '""') ? '' : $remark[$key];
      }
      $stage_data['actual_opt_manufacturing_checked'] = "2";
      if ($actual_output_manufacturing[$key] == "1") {
          $stage_data['actual_opt_manufacturing_checked'] = "1";
      } 
      if ($actual_output_manufacturing[$key] == "0") {
          $stage_data['actual_opt_manufacturing_checked'] = "0";
      }
      if ($remark_manufacturing[$key] != "") {
          $display_remark_manufacturing = ($remark_manufacturing[$key] == '""') ? '' : $remark_manufacturing[$key];
      }
      $stage_data['remark'] = $display_remark;
      $stage_data['remark_manufacturing'] = $display_remark_manufacturing;
      $stage_info[] = $stage_data;
  }
  return $stage_info;
}
?>

<!-- Content Wrapper. Contains page content -->
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="pagehead">
        <div class="row">
          <div class="col-sm-4">
            <h5>Stamping View Details</h5>
          </div>
          <div class="col-sm-8">
            <div class="tab float-sm-right">
            <a href="javascript:history.back()" class="btn btn-primary">
              <i class="fas fa-arrow-left"></i> Back
            </a>
            </div>
          </div>
        </div>
      </div>
    </div><!-- /.container-fluid -->
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-12">
          <!-- Default box -->
          <div class="framecontent">
            <div class="card">
              <div class="card-body formarea smpadding">
              <table class="table table-bordered table-striped">
                  <thead>
                      <tr>
                        <th>Sr. No.</th>
                        <th colspan="2" style="text-align: center;">Checklist Item</th>
                        <th>Stamper Initial</th>
                        <th>Stamper Initial Remarks</th>
                        <th>Stamper Final</th>
                        <th>Stamper Final Remarks</th>
                      </tr>
                  </thead>
                  <tbody>
                    <?php
                    $rowspans = array(6, 6, 4, 6, 9, 6, 3, 1);
                    $equipmentList = array(
                      "Equipment List", 
                      "Wiring Testing", 
                      "PT Testing", 
                      "Name Plate", 
                      "General Checking", 
                      "Contact Arms", 
                      "After cover fixing", 
                      ""
                    );
                    
                    $currentRow = 1;
                    $rowspanIndex = 0;
                    $nextRowStart = $currentRow + $rowspans[$rowspanIndex];
                    
                    foreach ($results as $row) {
                      $rowspan = $rowspans[$rowspanIndex];
                      ?>
                      <tr>
                        <td><?php echo $row['sr_no']; ?></td>
                        <?php if ($currentRow == 1 || $currentRow == 7 || $currentRow == 13 || $currentRow == 17 || $currentRow == 23 || $currentRow == 32 || $currentRow == 38 || $currentRow == 41) { ?>
                          <td rowspan="<?php echo $rowspan; ?>"><?php echo $equipmentList[$rowspanIndex]; ?></td>
                          <td><?php echo $row['check_item']; ?></td>
                        <?php } else { ?>
                          <td><?php echo $row['check_item']; ?></td>
                        <?php } ?>
                        <td>
                          <?php if ($row['actual_opt_checked'] == '1') {
                            echo 'Yes';
                          } elseif ($row['actual_opt_checked'] == '0') {
                            echo 'No';
                          } else {
                            echo 'N/A';
                          } ?>
                        </td>
                        <td><?php echo $row['remark']; ?></td>
                        <td>
                          <?php if ($row['actual_opt_manufacturing_checked'] == '1') {
                            echo 'Yes';
                          } elseif ($row['actual_opt_manufacturing_checked'] == '0') {
                            echo 'No';
                          } else {
                            echo 'N/A';
                          } ?>
                        </td>
                        <td><?php echo $row['remark_manufacturing']; ?></td>
                      </tr>
                    <?php
                      $currentRow++;
                    
                      if ($currentRow == $nextRowStart) {
                        $rowspanIndex++;
                        if (isset($rowspans[$rowspanIndex])) {
                          $nextRowStart += $rowspans[$rowspanIndex];
                        }
                      }
                    }
                    ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php include('footer.php'); ?>