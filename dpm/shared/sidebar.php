<?php 
include_once $_SERVER["DOCUMENT_ROOT"] . "/assemblynotes/shared/SidebarHelperMAI.php";
session_start();
$email_id = SharedManager::getUser()["Email"];
$getModulesQuery = "SELECT id FROM users WHERE email=:email AND FIND_IN_SET('10', REPLACE(modules, '|', ',')) > 0";
$modulesData = DbManager::fetchPDOQueryData('php_auth', $getModulesQuery, [":email" => $email_id])["data"];
$hide_admin_panel = 1;
if (empty($modulesData)) {
    $hide_admin_panel = 0;
}
$getDocumentQuery = "SELECT id FROM users WHERE email=:email AND FIND_IN_SET('23', REPLACE(modules, '|', ',')) > 0";
$documentData = DbManager::fetchPDOQueryData('php_auth', $getDocumentQuery, [":email" => $email_id])["data"];
$hide_document_panel = 1;
if (empty($documentData)) {
    $hide_document_panel = 0;
}
// Retrieve saved values
$selectedProject = $_SESSION['selected_project'] ?? '';
$selectedPanel = $_SESSION['selected_panel'] ?? '';
$selectedStation = $_SESSION['selected_station'] ?? '';
$fileName = basename($_SERVER['SCRIPT_FILENAME']);

// Determine the sidebar type based on the active page and type parameter
$sidebarType = isset($_GET['type']) ? $_GET['type'] : 'default';
$isChecklistDetailsType2 = ($activePage == "/dpm/dwc/checklist_details.php" || 
                            (strpos($_SERVER['REQUEST_URI'], '/dpm/dwc/checklist_details.php') !== false)) && 
                           ($sidebarType === 'type2' || strpos($_SERVER['REQUEST_URI'], 'type=type2') !== false);
?>
<nav class="navbar-default navbar-static-side" role="navigation">
    <div class="sidebar-collapse">
        <ul class="nav metismenu" id="side-menu">
            <li class="nav-header" style="padding-top: 15px !important;">
                <div class="dropdown profile-element" style="display: flex; flex-direction: column; align-items: center; width: 100%;">
                    <div class="df jcc">
                        <a href="/index.php">
                            <img style="max-width: 100px;transition: all 0.5s ease;cursor: pointer;padding-bottom: 5px;"
                                alt="<?php echo $_SESSION['username']; ?>"
                                src="/images/onex.png"/>
                        </a>
                    </div>
                    <div class="header-right-profile" style="margin: 10px 0;">
                        <?php
                        $gid = SharedManager::getUser()["GID"] ?? null;
                        if ($gid != "") {
                            $profile_image = "/users/?gid=" . $gid;   
                        } else {
                            $profile_image = "/users/?gid=null";   
                        }
                        ?>
                        <img alt="image" 
                            style="border-radius: 50%;width: 65px;height: 65px;object-fit: cover;" 
                            src="<?= htmlspecialchars($profile_image) ?>"
                        >
                    </div>
                    <div class="df jcc">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                            <span class="block m-t-xs ta-c font-bold">
                                <?php 
                                if ($gid != "") {
                                    echo SharedManager::getUser()["Name"] . ' ' . SharedManager::getUser()["Surname"]. ' ('. SharedManager::getUser()["OrgCode"] . ')';
                                } else {
                                    echo strtoupper($_SESSION['username']). ' ('. $_SESSION['role_name'] . ')';
                                }
                                ?></span>
                            <span class="text-muted ta-c text-xs block"><?php echo date("d-m-Y H:i"); ?></span>
                        </a>
                    </div>
                </div>
                <a href="/index.php" style="padding: 0 !important;">
                    <div class="logo-element">
                        OneX
                    </div>
                </a>
            </li>
            <?php if ($activePage == "/dpm/day_shift_wise_viewer.php") { ?>
                <li <?php echo SidebarHelperMAI::getActiveClassDefinitionBylike($activePage, "day_shift_wise_viewer") ?>>
                    <a href="" aria-expanded="<?php echo SidebarHelperMAI::hasExpandedAria($activePage, 'day_shift_wise_viewer') ?>">
                    <i class="fa fa-file-text"></i>
                    <span class="nav-label" data-translate="assembly-notes">Dashboard</span><span class="fa arrow"></span></a>
                    <ul class="nav nav-second-level collapse <?php echo SidebarHelperMAI::getColapseClassTerm($activePage, 'day_shift_wise_viewer') ?>"
                        aria-expanded="<?php echo SidebarHelperMAI::hasExpandedAria($activePage, 'day_shift_wise_viewer') ?>" style="">
                        <li <?php echo SidebarHelperMAI::getActiveClassDefinition($activePage, "/dpm/day_shift_wise_viewer.php") ?>>
                            <a href="/dpm/day_shift_wise_viewer.php">
                                <i class="fa fa-file"></i> 
                                <span class="nav-label" data-translate="all-notes">Day and Shift Wise Output</span>
                            </a>
                        </li>
                    </ul>
                </li>
            <?php } else if ($activePage == "/order_technical_information.php" || $activePage == "/panel_technical_information.php" || $activePage == "/single_line_diagram.php"
                    || $activePage == "/material_search.php" || $activePage == "/material_tracking.php" || $activePage == "/add_checklist_line.php" || $activePage == "/add_checklist_product.php" 
                    || $activePage == "/add_checklist_station.php" || $activePage == "/add_checklist_item.php" || $activePage == "/view_checklist_item.php" 
                    || $activePage == "/checklist_form.php" || ($activePage == "/checklist_details.php" && !$isChecklistDetailsType2) || $activePage == "/checklist_results.php" || $activePage == "/3d_panel.php" || $activePage == "/files_of_projects.php"
                    || $activePage == "/missing_material_entry.php" || $activePage == "/show_missing_material.php" || $activePage == "/logout.php") { ?>
                <style>
                    .navbar-static-side {
                        position: fixed;
                        top: 0;
                        bottom: 0;
                        width: 220px;
                        overflow-y: auto;
                        z-index: 1000;
                        background-color: #2f4050;
                    }

                    .sidebar-collapse {
                        height: 100%;
                        overflow-y: auto;
                        padding-bottom: 100px;
                    }

                    .footer {
                        position: fixed;
                        bottom: 0;
                        width: 100%;
                        z-index: 1000;
                        background-color: #fff;
                    }

                    .navbar-static-side::-webkit-scrollbar {
                        width: 10px;
                    }

                    .navbar-static-side::-webkit-scrollbar-track {
                        background: #2f4050;
                    }

                    .navbar-static-side::-webkit-scrollbar-thumb {
                        background: #4A5462;
                        border-radius: 4px;
                    }

                    .nav.metismenu {
                        padding-bottom: 100px;
                    }
                </style>
                <!-- <li <?php echo SidebarHelperMAI::getActiveClassDefinitionBylike($activePage, "order_technical_information") ?>>
                    <a href="" aria-expanded="<?php echo SidebarHelperMAI::hasExpandedAria($activePage, 'order_technical_information') ?>">
                    <i class="fa fa-cogs"></i>
                    <span class="nav-label" data-translate="assembly-notes">Order Technical Information</span><span class="fa arrow"></span></a>
                    <ul class="nav nav-second-level collapse <?php echo SidebarHelperMAI::getColapseClassTerm($activePage, 'order_technical_information') ?>"
                        aria-expanded="<?php echo SidebarHelperMAI::hasExpandedAria($activePage, 'order_technical_information') ?>" style="">
                    </ul>
                </li>
                <li <?php echo SidebarHelperMAI::getActiveClassDefinitionBylike($activePage, "panel_technical_information") ?>>
                    <a href="" aria-expanded="<?php echo SidebarHelperMAI::hasExpandedAria($activePage, 'panel_technical_information') ?>">
                    <i class="fa fa-tint"></i>
                    <span class="nav-label" data-translate="assembly-notes">Panel Technical Search</span><span class="fa arrow"></span></a>
                    <ul class="nav nav-second-level collapse <?php echo SidebarHelperMAI::getColapseClassTerm($activePage, 'panel_technical_information') ?>"
                        aria-expanded="<?php echo SidebarHelperMAI::hasExpandedAria($activePage, 'panel_technical_information') ?>" style="">
                    </ul>
                </li>
                <li <?php echo SidebarHelperMAI::getActiveClassDefinitionBylike($activePage, "single_line_diagram") ?>>
                    <a href="" aria-expanded="<?php echo SidebarHelperMAI::hasExpandedAria($activePage, 'single_line_diagram') ?>">
                    <i class="fa fa-list"></i>
                    <span class="nav-label" data-translate="assembly-notes">Single Line Diagram</span><span class="fa arrow"></span></a>
                    <ul class="nav nav-second-level collapse <?php echo SidebarHelperMAI::getColapseClassTerm($activePage, 'single_line_diagram') ?>"
                        aria-expanded="<?php echo SidebarHelperMAI::hasExpandedAria($activePage, 'single_line_diagram') ?>" style="">
                    </ul>
                </li> -->
                <li <?php echo SidebarHelperMAI::getActiveClassDefinitionBylike($activePage, "material_search") ?>>
                    <a href="material_search.php?project=<?php echo urlencode($selectedProject); ?>" aria-expanded="<?php echo SidebarHelperMAI::hasExpandedAria($activePage, 'material_search') ?>" 
                        onclick="window.location.href='material_search.php?project=<?php echo urlencode($selectedProject); ?>'; return false;">
                        <i class="fa fa-search"></i>
                        <span class="nav-label" data-translate="assembly-notes">Material Search</span>
                    </a>
                    <ul class="nav nav-second-level collapse <?php echo SidebarHelperMAI::getColapseClassTerm($activePage, 'material_search') ?>"
                        aria-expanded="<?php echo SidebarHelperMAI::hasExpandedAria($activePage, 'material_search') ?>" style="">
                    </ul>
                </li>
                <li <?php echo SidebarHelperMAI::getActiveClassDefinitionBylike($activePage, "material_tracking") ?>>
                    <a href="material_tracking.php?project=<?php echo urlencode($selectedProject); ?>" aria-expanded="<?php echo SidebarHelperMAI::hasExpandedAria($activePage, 'material_tracking') ?>" 
                        onclick="window.location.href='material_tracking.php?project=<?php echo urlencode($selectedProject); ?>'; return false;">
                        <i class="fa fa-road"></i>
                        <span class="nav-label" data-translate="assembly-notes">Material Tracking</span>
                    </a>
                    <ul class="nav nav-second-level collapse <?php echo SidebarHelperMAI::getColapseClassTerm($activePage, 'material_search') ?>"
                        aria-expanded="<?php echo SidebarHelperMAI::hasExpandedAria($activePage, 'material_search') ?>" style="">
                    </ul>
                </li>
                <li <?php echo SidebarHelperMAI::getActiveClassDefinitionBylike($activePage, "checklist") ?>>
                    <a href="" aria-expanded="<?php echo SidebarHelperMAI::hasExpandedAria($activePage, 'checklist') ?>">
                        <i class="fa fa-check"></i>
                        <span class="nav-label" data-translate="assembly-notes">Checklist</span><span class="fa arrow"></span>
                    </a>
                    <ul class="nav nav-second-level collapse <?php echo SidebarHelperMAI::getColapseClassTerm($activePage, 'checklist') ?>"
                        aria-expanded="<?php echo SidebarHelperMAI::hasExpandedAria($activePage, 'checklist') ?>" style="">
                        <?php
                        if ($hide_admin_panel == 1) {
                        ?>
                            <li <?php echo SidebarHelperMAI::getActiveClassDefinitionBylike($activePage, "_checklist_") ?>>
                                <a href="#" aria-expanded="<?php echo SidebarHelperMAI::hasExpandedAria($activePage, '_checklist_') ?>">
                                    <i class="fa fa-lock"></i>
                                    <span class="nav-label">Admin Panel</span><span class="fa arrow"></span>
                                </a>
                                <ul class="nav nav-third-level collapse <?php echo SidebarHelperMAI::getColapseClassTerm($activePage, 'checklist-admin') ?>">
                                    <li <?php echo SidebarHelperMAI::getActiveClassDefinitionBylike($activePage, "add_checklist_line") ?>>
                                        <a href="add_checklist_line.php"><i class="fa fa-plus"></i>Add Department</a>
                                    </li>
                                    <li <?php echo SidebarHelperMAI::getActiveClassDefinitionBylike($activePage, "add_checklist_product") ?>>
                                        <a href="add_checklist_product.php"><i class="fa fa-plus"></i>Add Product</a>
                                    </li>
                                    <li <?php echo SidebarHelperMAI::getActiveClassDefinitionBylike($activePage, "add_checklist_station") ?>>
                                        <a href="add_checklist_station.php"><i class="fa fa-plus"></i>Add Station</a>
                                    </li>
                                    <li <?php echo SidebarHelperMAI::getActiveClassDefinitionBylike($activePage, "add_checklist_item") ?>>
                                        <a href="add_checklist_item.php"><i class="fa fa-plus"></i>Add Checklist</a>
                                    </li>
                                    <li <?php echo SidebarHelperMAI::getActiveClassDefinitionBylike($activePage, "view_checklist_item") ?>>
                                        <a href="view_checklist_item.php"><i class="fa fa-eye"></i>View Checklist</a>
                                    </li>
                                </ul>
                            </li>
                        <?php
                        }
                        ?>
                        <li <?php echo SidebarHelperMAI::getActiveClassDefinitionBylike($activePage, "checklist_form") ?>>
                            <a href="checklist_form.php?project=<?php echo urlencode($selectedProject); ?>" aria-expanded="<?php echo SidebarHelperMAI::hasExpandedAria($activePage, 'checklist_form') ?>" 
                                onclick="window.location.href='checklist_form.php?project=<?php echo urlencode($selectedProject); ?>'; return false;">
                                <i class="fa fa-list-alt"></i>
                                <span class="nav-label" data-translate="assembly-notes">Checklist Form</span>
                            </a>
                        </li>
                        <li <?php echo SidebarHelperMAI::getActiveClassDefinitionBylike($activePage, "checklist_details") ?>>
                            <a href="checklist_details.php" aria-expanded="<?php echo SidebarHelperMAI::hasExpandedAria($activePage, 'checklist_details') ?>" 
                                onclick="window.location.href='checklist_details.php'; return false;">
                                <i class="fa fa-table"></i>
                                <span class="nav-label" data-translate="assembly-notes">Checklist Details</span>
                            </a>
                        </li>
                        <!-- <li <?php echo SidebarHelperMAI::getActiveClassDefinitionBylike($activePage, "checklist-progress") ?>>
                            <a href="/checklist/progress"><i class="fa fa-line-chart"></i>Checklist Progress</a>
                        </li> -->
                        <li <?php echo SidebarHelperMAI::getActiveClassDefinitionBylike($activePage, "checklist_results") ?>>
                            <a href="checklist_results.php" aria-expanded="<?php echo SidebarHelperMAI::hasExpandedAria($activePage, 'checklist_results') ?>" 
                                onclick="window.location.href='checklist_results.php'; return false;">
                                <i class="fa fa-signal"></i>
                                <span class="nav-label" data-translate="assembly-notes">Checklist Results</span>
                            </a>
                        </li>
                        <!-- <li <?php echo SidebarHelperMAI::getActiveClassDefinitionBylike($activePage, "checklist-progress") ?>>
                            <a href="/checklist/progress"><i class="fa fa-table"></i>Door Checklist Form</a>
                        </li> -->
                    </ul>
                </li>
                <!-- <li <?php echo SidebarHelperMAI::getActiveClassDefinitionBylike($activePage, "3d_panel") ?>>
                    <a href="" aria-expanded="<?php echo SidebarHelperMAI::hasExpandedAria($activePage, '3d_panel') ?>">
                    <i class="fa fa-file-image-o"></i>
                    <span class="nav-label" data-translate="assembly-notes">3D Panel</span><span class="fa arrow"></span></a>
                    <ul class="nav nav-second-level collapse <?php echo SidebarHelperMAI::getColapseClassTerm($activePage, '3d_panel') ?>"
                        aria-expanded="<?php echo SidebarHelperMAI::hasExpandedAria($activePage, '3d_panel') ?>" style="">
                    </ul>
                </li> -->
                <li <?php echo SidebarHelperMAI::getActiveClassDefinitionBylike($activePage, "files_of_projects") ?>>
                    <a href="files_of_projects.php?project=<?php echo urlencode($selectedProject); ?>" aria-expanded="<?php echo SidebarHelperMAI::hasExpandedAria($activePage, 'files_of_projects') ?>" 
                        onclick="window.location.href='files_of_projects.php?project=<?php echo urlencode($selectedProject); ?>'; return false;">
                        <i class="fa fa-file-pdf-o"></i>
                        <span class="nav-label" data-translate="assembly-notes">Files Of Projects</span>
                    </a>
                    <ul class="nav nav-second-level collapse <?php echo SidebarHelperMAI::getColapseClassTerm($activePage, 'files_of_projects') ?>"
                        aria-expanded="<?php echo SidebarHelperMAI::hasExpandedAria($activePage, 'files_of_projects') ?>" style="">
                    </ul>
                </li>
                <li <?php echo SidebarHelperMAI::getActiveClassDefinitionBylike($activePage, "missing_material") ?>>
                    <a href="" aria-expanded="<?php echo SidebarHelperMAI::hasExpandedAria($activePage, 'missing_material') ?>">
                    <i class="fa fa-list"></i>
                    <span class="nav-label" data-translate="assembly-notes">Missing List Form</span><span class="fa arrow"></span></a>
                    <ul class="nav nav-second-level collapse <?php echo SidebarHelperMAI::getColapseClassTerm($activePage, 'missing_material') ?>"
                        aria-expanded="<?php echo SidebarHelperMAI::hasExpandedAria($activePage, 'missing_material') ?>" style="">
                        <li <?php echo SidebarHelperMAI::getActiveClassDefinition($fileName, "missing_material_entry") ?>>
                            <a href="missing_material_entry.php?project=<?php echo urlencode($selectedProject);?>">
                                <i class="fa fa-wpforms"></i>
                                <span class="nav-label" data-translate="all-notes">Missing Materials Entry</span>
                            </a>
                        </li>
                        <li <?php echo SidebarHelperMAI::getActiveClassDefinition($fileName, "show_missing_material") ?>>
                            <a href="show_missing_material.php?project=<?php echo urlencode($selectedProject);?>">
                                <i class="fa fa-ban"></i>
                                <span class="nav-label" data-translate="all-notes">Show Missing Materials</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li <?php echo SidebarHelperMAI::getActiveClassDefinitionBylike($activePage, "logout") ?>>
                    <a href="logout.php" aria-expanded="<?php echo SidebarHelperMAI::hasExpandedAria($activePage, 'logout') ?>"
                    onclick="window.location.href='logout.php'; return false;">
                    <i class="fa fa-power-off"></i>
                    <span class="nav-label" data-translate="assembly-notes">Logout</span></a>
                    <ul class="nav nav-second-level collapse <?php echo SidebarHelperMAI::getColapseClassTerm($activePage, 'logout') ?>"
                        aria-expanded="<?php echo SidebarHelperMAI::hasExpandedAria($activePage, 'logout') ?>" style="">
                    </ul>
                </li>
            <?php } else if ($activePage == "/panel_status.php" || $activePage == "/dpm/report_viewer.php" || $isChecklistDetailsType2) { ?>
                <style>
                    .navbar-static-side {
                        position: fixed;
                        top: 0;
                        bottom: 0;
                        width: 220px;
                        overflow-y: auto;
                        z-index: 1000;
                        background-color: #2f4050;
                    }

                    .sidebar-collapse {
                        height: 100%;
                        overflow-y: auto;
                        padding-bottom: 100px;
                    }

                    .footer {
                        position: fixed;
                        bottom: 0;
                        width: 100%;
                        z-index: 1000;
                        background-color: #fff;
                    }

                    .navbar-static-side::-webkit-scrollbar {
                        width: 10px;
                    }

                    .navbar-static-side::-webkit-scrollbar-track {
                        background: #2f4050;
                    }

                    .navbar-static-side::-webkit-scrollbar-thumb {
                        background: #4A5462;
                        border-radius: 4px;
                    }

                    .nav.metismenu {
                        padding-bottom: 100px;
                    }
                </style>
                <li <?php echo SidebarHelperMAI::getActiveClassDefinitionBylike($activePage, "panel_status") ?>>
                    <a href="/dpm/panel_status.php" aria-expanded="<?php echo SidebarHelperMAI::hasExpandedAria($activePage, 'panel_status') ?>"
                    onclick="window.location.href='/dpm/panel_status.php'; return false;">
                    <i class="fa fa-flag-checkered"></i>
                    <span class="nav-label" data-translate="assembly-notes">Order Status</span></a>
                    <ul class="nav nav-second-level collapse <?php echo SidebarHelperMAI::getColapseClassTerm($activePage, 'panel_status') ?>"
                        aria-expanded="<?php echo SidebarHelperMAI::hasExpandedAria($activePage, 'panel_status') ?>" style="">
                    </ul>
                </li>
                <li <?php echo SidebarHelperMAI::getActiveClassDefinitionBylike($activePage, "report_viewer") ?>>
                    <a href="/dpm/report_viewer.php" aria-expanded="<?php echo SidebarHelperMAI::hasExpandedAria($activePage, 'report_viewer') ?>"
                    onclick="window.location.href='/dpm/report_viewer.php'; return false;">
                    <i class="fa fa-file-text"></i>
                    <span class="nav-label" data-translate="assembly-notes">Breaker Status</span></a>
                    <ul class="nav nav-second-level collapse <?php echo SidebarHelperMAI::getColapseClassTerm($activePage, 'report_viewer') ?>"
                        aria-expanded="<?php echo SidebarHelperMAI::hasExpandedAria($activePage, 'report_viewer') ?>" style="">
                    </ul>
                </li>
                <li <?php echo SidebarHelperMAI::getActiveClassDefinitionBylike($activePage, "checklist_details") ?>>
                    <a href="/dpm/dwc/checklist_details.php?type=type2" aria-expanded="<?php echo SidebarHelperMAI::hasExpandedAria($activePage, 'checklist_details') ?>"
                    onclick="window.location.href='/dpm/dwc/checklist_details.php?type=type2'; return false;">
                    <i class="fa fa-tasks"></i>
                    <span class="nav-label" data-translate="assembly-notes">Panel Status</span></a>
                    <ul class="nav nav-second-level collapse <?php echo SidebarHelperMAI::getColapseClassTerm($activePage, 'checklist_details') ?>"
                        aria-expanded="<?php echo SidebarHelperMAI::hasExpandedAria($activePage, 'checklist_details') ?>" style="">
                    </ul>
                </li>    
            <?php } else if ($activePage == "/document_master.php" || $activePage == "/document_details.php" || $activePage == "/certificate_details.php" || $activePage == "/policy_details.php" || $activePage == "/manual_details.php" || $activePage == "/fac_details.php") { ?>

            <?php 
            // ✅ CHECK IF USER HAS DOCUMENT MANAGEMENT SYSTEM ADMIN MODULE (Module 23)
            $hasDocumentMasterAccess = in_array(23, SharedManager::getUser()["Modules"]);
            ?>

            <?php if ($hasDocumentMasterAccess) { ?>
                <li <?php echo SidebarHelperMAI::getActiveClassDefinitionBylike($activePage, "document") ?>>
                    <a href="" aria-expanded="<?php echo SidebarHelperMAI::hasExpandedAria($activePage, 'document') ?>">
                        <i class="fa fa-file-pdf-o"></i>
                        <span class="nav-label">Document Master</span>
                        <span class="fa arrow"></span>
                    </a>
                    <ul class="nav nav-second-level collapse <?php echo SidebarHelperMAI::getColapseClassTerm($activePage, 'document') ?>"
                        aria-expanded="<?php echo SidebarHelperMAI::hasExpandedAria($activePage, 'document') ?>">
                        
                        <?php if ($hide_document_panel == 1) { ?>
                            <li <?php echo SidebarHelperMAI::getActiveClassDefinitionBylike($activePage, "document_master") ?>>
                                <a href="document_master.php">
                                    <i class="fa fa-plus-circle"></i>
                                    <span class="nav-label">Add New Document</span>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                </li>
            <?php } ?>
            
            <li <?php echo SidebarHelperMAI::getActiveClassDefinitionBylike($activePage, "policy_details") ?>>
                <a href="policy_details.php">
                    <i class="fa fa-file-text"></i>
                    <span class="nav-label">Policies</span>
                </a>
            </li>

            <li <?php echo SidebarHelperMAI::getActiveClassDefinitionBylike($activePage, "certificate_details") ?>>
                <a href="certificate_details.php">
                    <i class="fa fa-certificate"></i>
                    <span class="nav-label">Certificates</span>
                </a>
            </li>

            <li <?php echo SidebarHelperMAI::getActiveClassDefinitionBylike($activePage, "fac_details") ?>>
                <a href="fac_details.php">
                    <i class="fa fa-sitemap"></i>
                    <span class="nav-label">FACs</span>
                </a>
            </li>

            <li <?php echo SidebarHelperMAI::getActiveClassDefinitionBylike($activePage, "manual_details") ?>>
                <a href="manual_details.php">
                    <i class="fa fa-book"></i>
                    <span class="nav-label">Manuals</span>
                </a>
            </li>

            <li <?php echo SidebarHelperMAI::getActiveClassDefinitionBylike($activePage, "document_details") ?>>
                <a href="document_details.php">
                    <i class="fa fa-file-pdf-o"></i>
                    <span class="nav-label">Documents</span>
                </a>
            </li>

        <?php } else { ?>
            <li <?php echo SidebarHelperMAI::getActiveClassDefinitionBylike($activePage, "breaker") ?>>
                <a href="" aria-expanded="<?php echo SidebarHelperMAI::hasExpandedAria($activePage, 'breaker_registration_form') ?>">
                <i class="fa fa-database"></i>
                <span class="nav-label" data-translate="assembly-notes">Breaker</span><span class="fa arrow"></span></a>
                <ul class="nav nav-second-level collapse <?php echo SidebarHelperMAI::getColapseClassTerm($activePage, 'breaker_registration_form') ?>"
                    aria-expanded="<?php echo SidebarHelperMAI::hasExpandedAria($activePage, 'breaker_registration_form') ?>" style="">
                    <li <?php echo SidebarHelperMAI::getActiveClassDefinition($activePage, "/dpm/breaker_registration_form.php") ?>>
                        <a href="/dpm/breaker_registration_form.php">
                            <i class="fa fa-registered"></i>
                            <span class="nav-label" data-translate="all-notes">Registration Form</span>
                        </a>
                    </li>
                    <li <?php echo SidebarHelperMAI::getActiveClassDefinition($activePage, "/dpm/breaker_details.php") ?>>
                        <a href="/dpm/breaker_details.php">
                            <i class="fa fa-edit"></i>
                            <span class="nav-label" data-translate="all-notes">Details</span>
                        </a>
                    </li>
                </ul>
            </li>
            <?php } ?>
        </ul>
    </div>
</nav>