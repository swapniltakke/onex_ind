<?php include_once $_SERVER["DOCUMENT_ROOT"] . "/assemblynotes/shared/SidebarHelperMAI.php"; ?>
<nav class="navbar-default navbar-static-side" role="navigation">
    <div class="sidebar-collapse">
        <ul class="nav metismenu" id="side-menu">
            <li class="nav-header" style="padding-top: 15px !important;">
                <div class="dropdown profile-element">
                    <div class="df jcc">
                        <a href="/index.php">
                            <img style="max-width: 100px;transition: all 0.5s ease;cursor: pointer;padding-bottom: 5px;"
                                 alt="<?php echo SharedManager::getUser()["Name"] . ' ' . SharedManager::getUser()["Surname"] ?>"
                                 src="/images/onex.png"/>
                        </a>
                    </div>
                    <div class="df jcc">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                            <span class="block m-t-xs ta-c font-bold"><?php echo SharedManager::getUser()["Name"] . ' ' . SharedManager::getUser()["Surname"]?></span>
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
            <li <?php echo SidebarHelperMAI::getActiveClassDefinition($activePage, "orderIssues") ?>>
                <a href=""
                   aria-expanded="<?php echo SidebarHelperMAI::hasExpandedAria($activePage, "orderIssues") ?>"><i
                            class="fa fa-bug"></i>
                    <span class="nav-label" data-translate="open-issues">Open Issues</span> <span class="fa arrow"></span></a>
                <ul class="nav nav-second-level collapse <?php echo SidebarHelperMAI::getColapseClassTerm($activePage, 'orderIssues') ?>"
                    aria-expanded="<?php echo SidebarHelperMAI::hasExpandedAria($activePage, "orderIssues") ?>">
                    <li <?php echo SidebarHelperMAI::getActiveClassDefinition($activePage, "/orderIssues/index.php") ?>>
                        <a href="/assemblynotes/orderIssues/index.php">
                            <span class="nav-label" data-translate="ask-question">Ask Question</span>
                        </a></li>
                    <li <?php echo SidebarHelperMAI::getActiveClassDefinition($activePage, "/orderIssues/listTable.php") ?>>
                        <a href="/assemblynotes/orderIssues/listTable.php">
                            <span class="nav-label" data-translate="list-questions">List Questions</span>
                        </a></li>
                    <li <?php echo SidebarHelperMAI::getActiveClassDefinition($activePage, "queryManagements/index.php") ?>>
                        <a href="/assemblynotes/questionManagement/index.php">
                            <span class="nav-label" data-translate="management-question">Question Management</span>
                        </a></li>
                </ul>
            </li>
            <!--<li <?php /*echo SidebarHelperMAI::getActiveClassDefinition($activePage, 'report|trends') */?>>
                <a href=""
                   aria-expanded="<?php /*echo SidebarHelperMAI::hasExpandedAria($activePage, 'report|trends') */?>"><i
                            class="fa fa-line-chart"></i>
                    <span class="nav-label" data-translate="assembly-reports">Assembly Reports</span> <span
                            class="fa arrow"></span></a>
                <ul class="nav nav-second-level collapse <?php /*echo SidebarHelperMAI::getColapseClassTerm($activePage, 'report|trends') */?>"
                    aria-expanded="<?php /*echo SidebarHelperMAI::hasExpandedAria($activePage, 'report|trends') */?>"
                    style="">
                    <li <?php /*echo SidebarHelperMAI::getActiveClassDefinition($activePage, "report|trends/index.php") */?>>
                        <a href="/assemblynotes/report/index.php">
                            <span class="nav-label" data-translate="main-sub-category-report">Main and Sub Category Report</span>
                        </a></li>
                    <li <?php /*echo SidebarHelperMAI::getActiveClassDefinition($activePage, "report|trends/trendReports") */?>>
                        <a href="/assemblynotes/trendReports/index.php">
                            <span class="nav-label" data-translate='additional-labor-report'>Additional Labor Report</span>
                        </a></li>
                    <li <?php /*echo SidebarHelperMAI::getActiveClassDefinition($activePage, "report|trends/panelTrend") */?>>
                        <a href="/assemblynotes/panelTrend/index.php">
                            <span class="nav-label" data-translate='additional-labor-report-per-panel'>Additional Labor Report Per Panel</span>
                        </a></li>
                </ul>
            </li>-->

            <li <?php echo SidebarHelperMAI::getActiveClassDefinitionBylike($activePage, "notes") ?>>
                <a href="" aria-expanded="<?php echo SidebarHelperMAI::hasExpandedAria($activePage, 'notes') ?>"><i
                            class="fa fa-edit"></i>
                    <span class="nav-label" data-translate="assembly-notes">Assembly Notes</span><span class="fa arrow"></span></a>
                <ul class="nav nav-second-level collapse <?php echo SidebarHelperMAI::getColapseClassTerm($activePage, 'notes') ?>"
                    aria-expanded="<?php echo SidebarHelperMAI::hasExpandedAria($activePage, 'notes') ?>" style="">
                    <li <?php echo SidebarHelperMAI::getActiveClassDefinition($activePage, "/notes/allNotes.php") ?>>
                        <a href="/assemblynotes/notes/allNotes.php">
                            <i class="fa fa-list"></i>
                            <span class="nav-label" data-translate="all-notes">All Notes</span>
                        </a>
                    </li>
                    <li <?php echo SidebarHelperMAI::getActiveClassDefinition($activePage, "/notes/openList.php") ?>>
                        <a href="/assemblynotes/notes/openList.php">
                            <i class="fa fa-file"></i>
                            <span class="nav-label" data-translate="open-notes">Open Notes</span>
                        </a>
                    </li>
                    <li <?php echo SidebarHelperMAI::getActiveClassDefinition($activePage, '/notes/closeList.php') ?>>
                        <a href="/assemblynotes/notes/closeList.php">
                            <i class="fa fa-window-close"></i>
                            <span class="nav-label" data-translate="close-notes">Closed Notes</span>
                        </a>
                    </li>
                    <li <?php echo SidebarHelperMAI::getActiveClassDefinition($activePage, '/notes/detail.php') ?>>
                        <a href="/assemblynotes/notes/detail.php">
                            <i class="fa fa-list"></i>
                            <span class="nav-label" data-translate="panel-select-screen">Panel Selection Screen</span>
                        </a>
                    </li>
                </ul>
            </li>

            <li <?php echo SidebarHelperMAI::getActiveClassDefinition($activePage, 'mainPage') ?>>
                <a href="/assemblynotes/index.php" target="_blank">
                    <i class="fa fa-barcode"></i>
                    <span class="nav-label" data-translate="panel-barcode-read">Scan Barcode</span>
                </a>
            </li>
        </ul>
    </div>
</nav>