<?php 
    include_once $_SERVER["DOCUMENT_ROOT"] . "/assemblynotes/shared/SidebarHelperMAI.php";
    
    // Get the mode from the parent page
    $mode = isset($_GET['mode']) ? $_GET['mode'] : 'database';
    $isConfigurator = ($mode === 'configurator');
    
    // Get current page for active state
    $currentPage = basename($_SERVER['PHP_SELF']);
    $currentPath = $_SERVER['PHP_SELF'];
    
    // Check if we're on project_detail.php and set configurator mode
    if ($currentPage === 'project_detail.php' || $currentPage === 'index.php') {
        $isConfigurator = true;
        $mode = 'configurator';
    }
?>
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

            <!-- DTO Database Section - Show for both modes -->
            <?php 
                $isDtoActive = (strpos($currentPath, 'add_dto_data.php') !== false || strpos($currentPath, 'dto_details.php') !== false);
                $dtoActiveClass = $isDtoActive ? 'active' : '';
                $dtoExpandedAria = $isDtoActive ? 'true' : 'false';
                $dtoCollapseClass = $isDtoActive ? 'in' : '';
            ?>
            <li class="<?php echo $dtoActiveClass; ?>">
                <a href="/dpm/dto_details.php?mode=<?php echo $mode; ?>" aria-expanded="<?php echo $dtoExpandedAria; ?>">
                    <i class="fa fa-clipboard"></i>
                    <span class="nav-label" data-translate="assembly-notes">DTO Database</span>
                    <span class="fa arrow"></span>
                </a>
                <ul class="nav nav-second-level collapse <?php echo $dtoCollapseClass; ?>" aria-expanded="<?php echo $dtoExpandedAria; ?>">
                    
                    <?php 
                        $isAddDtoActive = (strpos($currentPath, 'add_dto_data.php') !== false);
                    ?>
                    <li class="<?php echo $isAddDtoActive ? 'active' : ''; ?>">
                        <a href="/dpm/add_dto_data.php?mode=<?php echo $mode; ?>" class="<?php echo $isAddDtoActive ? 'active' : ''; ?>">
                            <i class="fa fa-registered"></i>
                            <span class="nav-label" data-translate="all-notes">Registration Form</span>
                        </a>
                    </li>
                    
                    <?php 
                        $isDtoDetailsActive = (strpos($currentPath, 'dto_details.php') !== false);
                    ?>
                    <li class="<?php echo $isDtoDetailsActive ? 'active' : ''; ?>">
                        <a href="/dpm/dto_details.php?mode=<?php echo $mode; ?>" class="<?php echo $isDtoDetailsActive ? 'active' : ''; ?>">
                            <i class="fa fa-edit"></i>
                            <span class="nav-label" data-translate="all-notes">Details</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Additional Sections - Only show for Database mode -->
            <?php if (!$isConfigurator): ?>
                
                <!-- Series Allocation Section -->
                <?php 
                    $isSeriesActive = (strpos($currentPath, 'draw_reg.php') !== false || strpos($currentPath, 'numreg_details.php') !== false);
                    $seriesActiveClass = $isSeriesActive ? 'active' : '';
                    $seriesExpandedAria = $isSeriesActive ? 'true' : 'false';
                    $seriesCollapseClass = $isSeriesActive ? 'in' : '';
                ?>
                <li class="<?php echo $seriesActiveClass; ?>">
                    <a href="/dpm/numreg_details.php?mode=<?php echo $mode; ?>" aria-expanded="<?php echo $seriesExpandedAria; ?>">
                        <i class="fa fa-calendar"></i>
                        <span class="nav-label" data-translate="all-notes">Series Allocation</span>
                        <span class="fa arrow"></span>
                    </a>
                    <ul class="nav nav-second-level collapse <?php echo $seriesCollapseClass; ?>" aria-expanded="<?php echo $seriesExpandedAria; ?>">
                        
                        <?php 
                            $isDrawRegActive = (strpos($currentPath, 'draw_reg.php') !== false);
                        ?>
                        <li class="<?php echo $isDrawRegActive ? 'active' : ''; ?>">
                            <a href="/dpm/draw_reg.php?mode=<?php echo $mode; ?>" class="<?php echo $isDrawRegActive ? 'active' : ''; ?>">
                                <i class="fa fa-calendar-plus-o"></i>
                                <span class="nav-label" data-translate="all-notes">Series Number Allocation</span>
                            </a>
                        </li>
                        
                        <?php 
                            $isNumregDetailsActive = (strpos($currentPath, 'numreg_details.php') !== false);
                        ?>
                        <li class="<?php echo $isNumregDetailsActive ? 'active' : ''; ?>">
                            <a href="/dpm/numreg_details.php?mode=<?php echo $mode; ?>" class="<?php echo $isNumregDetailsActive ? 'active' : ''; ?>">
                                <i class="fa fa-calendar-check-o"></i>
                                <span class="nav-label" data-translate="all-notes">Series Details</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Number Master Section -->
                <?php 
                    $isMatActive = (strpos($currentPath, 'mat_reg.php') !== false || strpos($currentPath, 'mat_details.php') !== false);
                    $matActiveClass = $isMatActive ? 'active' : '';
                    $matExpandedAria = $isMatActive ? 'true' : 'false';
                    $matCollapseClass = $isMatActive ? 'in' : '';
                ?>
                <li class="<?php echo $matActiveClass; ?>">
                    <a href="/dpm/mat_details.php?mode=<?php echo $mode; ?>" aria-expanded="<?php echo $matExpandedAria; ?>">
                        <i class="fa fa-calendar"></i>
                        <span class="nav-label" data-translate="all-notes">Number Master</span>
                        <span class="fa arrow"></span>
                    </a>
                    <ul class="nav nav-second-level collapse <?php echo $matCollapseClass; ?>" aria-expanded="<?php echo $matExpandedAria; ?>">
                        
                        <?php 
                            $isMatRegActive = (strpos($currentPath, 'mat_reg.php') !== false);
                        ?>
                        <li class="<?php echo $isMatRegActive ? 'active' : ''; ?>">
                            <a href="/dpm/mat_reg.php?mode=<?php echo $mode; ?>" class="<?php echo $isMatRegActive ? 'active' : ''; ?>">
                                <i class="fa fa-calendar-plus-o"></i>
                                <span class="nav-label" data-translate="all-notes">New Number Registration</span>
                            </a>
                        </li>
                        
                        <?php 
                            $isMatDetailsActive = (strpos($currentPath, 'mat_details.php') !== false);
                        ?>
                        <li class="<?php echo $isMatDetailsActive ? 'active' : ''; ?>">
                            <a href="/dpm/mat_details.php?mode=<?php echo $mode; ?>" class="<?php echo $isMatDetailsActive ? 'active' : ''; ?>">
                                <i class="fa fa-calendar-check-o"></i>
                                <span class="nav-label" data-translate="all-notes">Numbers Details</span>
                            </a>
                        </li>
                    </ul>
                </li>

            <?php else: ?>
                
                <!-- Configurator Mode -->
                <?php 
                    // Check if we're on index.php or project_detail.php
                    $isProjectActive = (strpos($currentPath, 'index.php') !== false || strpos($currentPath, 'project_detail.php') !== false);
                    $projectActiveClass = $isProjectActive ? 'active' : '';
                    $projectExpandedAria = $isProjectActive ? 'true' : 'false';
                    $projectCollapseClass = $isProjectActive ? 'in' : '';
                ?>
                <li class="<?php echo $projectActiveClass; ?>">
                    <a href="/dpm/dtoconfigurator/core/projects/index.php?mode=configurator" aria-expanded="<?php echo $projectExpandedAria; ?>">
                        <i class="fa fa-list"></i>
                        <span class="nav-label" data-translate="all-notes">Projects</span>
                        <span class="fa arrow"></span>
                    </a>
                    <ul class="nav nav-second-level collapse <?php echo $projectCollapseClass; ?>" aria-expanded="<?php echo $projectExpandedAria; ?>">
                        <li class="<?php echo $isProjectActive ? 'active' : ''; ?>">
                            <a href="/dpm/dtoconfigurator/core/projects/index.php?mode=configurator" class="<?php echo $isProjectActive ? 'active' : ''; ?>">
                                <i class="fa fa-info-circle"></i>
                                <span class="nav-label" data-translate="all-notes">Project Information</span>
                            </a>
                        </li>
                    </ul>
                </li>    
                
            <?php endif; ?>
        </ul>
    </div>
</nav>