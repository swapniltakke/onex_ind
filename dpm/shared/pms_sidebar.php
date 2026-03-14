<?php 
include_once $_SERVER["DOCUMENT_ROOT"] . "/assemblynotes/shared/SidebarHelperMAI.php";
session_start();

// Get current page for active state tracking
$activePage = $_SERVER['PHP_SELF'];
$fileName = basename($_SERVER['SCRIPT_FILENAME']);
?>

<style>
/* Ensure sidebar extends full height with proper background */
.navbar-default.navbar-static-side {
    position: fixed;
    height: 100%;
    min-height: 100vh;
    width: 220px;
    background-color: #2f4050; /* Standard dark blue sidebar background */
    z-index: 2001;
    overflow-y: auto;
}

.sidebar-collapse {
    height: 100%;
    min-height: 100vh;
    background-color: inherit;
    padding-bottom: 100px; /* Additional padding to ensure last items are fully visible */
}

/* Ensure page content adjusts to sidebar */
#page-wrapper {
    margin-left: 220px;
    min-height: 100vh;
}

/* Mini navbar state */
body.mini-navbar .navbar-static-side {
    width: 70px;
}

body.mini-navbar #page-wrapper {
    margin-left: 70px;
}

/* Scrollbar styling for sidebar */
.navbar-static-side::-webkit-scrollbar {
    width: 10px; /* Slightly wider scrollbar */
}

.navbar-static-side::-webkit-scrollbar-track {
    background: #2f4050;
}

.navbar-static-side::-webkit-scrollbar-thumb {
    background: #4A5462;
    border-radius: 4px;
}

/* Profile element styling */
.dropdown.profile-element {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100%;
}

.header-right-profile {
    margin: 10px 0;
}

.header-right-profile img {
    border-radius: 50%;
    width: 65px;
    height: 65px;
    object-fit: cover;
}

/* Ensure last menu items are fully visible */
.nav.metismenu {
    padding-bottom: 100px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .navbar-default.navbar-static-side {
        width: 70px;
    }
    
    #page-wrapper {
        margin-left: 70px;
    }
}
</style>

<nav class="navbar-default navbar-static-side" role="navigation">
    <div class="sidebar-collapse">
        <ul class="nav metismenu" id="side-menu">
            <li class="nav-header" style="padding-top: 15px !important;">
                <div class="dropdown profile-element" style="display: flex; flex-direction: column; align-items: center; width: 100%;">
                    <div class="df jcc">
                        <a href="/index.php">
                            <img style="max-width: 100px;transition: all 0.5s ease;cursor: pointer;padding-bottom: 5px;"
                                alt="<?php echo SharedManager::getUser()["Name"] . ' ' . SharedManager::getUser()["Surname"] ?>"
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
                                    echo SharedManager::getUser()["Name"] . ' ' . SharedManager::getUser()["Surname"];
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

            <?php 
            // Check if user has access to either PMS Admin (20) or PMS Supervisor (21) module
            $userModules = SharedManager::getUser()["Modules"];
            $hasPMSAccess = in_array(20, $userModules) || in_array(21, $userModules);
            $isPMSAdmin = in_array(20, $userModules);
            $isPMSSupervisor = in_array(21, $userModules);
            
            // ✅ NEW: Check if user is Module 19 user (Regular User with Attendance Record access)
            $isModule19User = in_array(19, $userModules);
            ?>

            <?php if($hasPMSAccess): ?>
            <!-- 1. User Management Section - Requires Module 20 or 21 -->
            <li id="user-management" class="<?php echo (strpos($activePage, 'pms_userform') !== false || strpos($activePage, 'Shift_Allocation') !== false || 
                strpos($activePage, 'user_transfer') !== false || strpos($activePage, 'user_reports') !== false) ? 'active' : ''; ?>">
                <a href="#" class="menu-toggle" data-menu="user-management">
                    <i class="fa fa-users"></i>
                    <span class="nav-label" data-translate="user-management">User Management</span>
                    <span class="fa arrow"></span>
                </a>
                <ul class="nav nav-second-level collapse <?php echo (strpos($activePage, 'pms_userform') !== false || strpos($activePage, 'Shift_Allocation') !== false || 
                    strpos($activePage, 'user_transfer') !== false || strpos($activePage, 'user_reports') !== false) ? 'in' : ''; ?>">
                    
                    <?php if($isPMSAdmin): ?>
                    <!-- 1.a. Add New User - Only for Admin (Module 20) -->
                    <li class="<?php echo (strpos($activePage, 'pms_userform.php') !== false) ? 'active' : ''; ?>">
                        <a href="/dpm/pms_userform.php" class="menu-link" data-parent="user-management">
                            <i class="fa fa-user-plus"></i>
                            <span class="nav-label" data-translate="add-user">Add New User</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if($hasPMSAccess): ?>
                    <!-- 1.b. Shift Allocation - Requires Module 20 or 21 -->
                    <li class="<?php echo (strpos($activePage, 'Shift_Allocation.php') !== false) ? 'active' : ''; ?>">
                        <a href="/dpm/Shift_Allocation.php" class="menu-link" data-parent="user-management">
                            <i class="fa fa-clock-o"></i>
                            <span class="nav-label" data-translate="add-shift">Shift Allocation</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if($hasPMSAccess): ?>
                    <!-- 1.c. Transfer Allocation - Requires Module 20 or 21 -->
                    <li class="<?php echo (strpos($activePage, 'user_transfer.php') !== false) ? 'active' : ''; ?>">
                        <a href="/dpm/user_transfer.php" class="menu-link" data-parent="user-management">
                            <i class="fa fa-exchange"></i>
                            <span class="nav-label" data-translate="transfer-user">Transfer User</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if($hasPMSAccess): ?>
                    <!-- 1.d. Reports - Requires Module 20 or 21 -->
                    <li id="user-reports" class="<?php echo (strpos($activePage, 'user_reports') !== false || strpos($activePage, 'user_graph_reports') !== false) ? 'active' : ''; ?>">
                        <a href="#" class="menu-toggle" data-menu="user-reports">
                            <i class="fa fa-file-text-o"></i>
                            <span class="nav-label">Reports</span>
                            <span class="fa arrow"></span>
                        </a>
                        <ul class="nav nav-third-level collapse <?php echo (strpos($activePage, 'user_reports') !== false || strpos($activePage, 'user_graph_reports') !== false) ? 'in' : ''; ?>">
                            
                            <?php if($hasPMSAccess): ?>
                            <!-- Detailed Report - Requires Module 20 or 21 -->
                            <li class="<?php echo (strpos($activePage, 'user_reports.php') !== false) ? 'active' : ''; ?>" style="padding-left: 15px;">
                                <a href="/dpm/user_reports.php" class="menu-link" data-parent="user-reports">
                                    <i class="fa fa-table"></i>
                                    <span class="nav-label">Detailed Report (Tabular)</span>
                                </a>
                            </li>
                            <?php endif; ?>
                            
                            <?php if($hasPMSAccess): ?>
                            <!-- Graph Report - Requires Module 20 or 21 -->
                            <li class="<?php echo (strpos($activePage, 'user_graph_reports.php') !== false) ? 'active' : ''; ?>" style="padding-left: 15px;">
                                <a href="/dpm/user_graph_reports.php" class="menu-link" data-parent="user-reports">
                                    <i class="fa fa-bar-chart"></i>
                                    <span class="nav-label">Graph Report (Visualization)</span>
                                </a>
                            </li>
                            <?php endif; ?>
                            
                        </ul>
                    </li>
                    <?php endif; ?>
                </ul>
            </li>
            <?php endif; ?>

            <?php if($hasPMSAccess): ?>
            <!-- 2. Leave Management Section - Requires Module 20 or 21 -->
            <li id="leave-management" class="<?php echo (strpos($activePage, 'pms_leaveform') !== false || strpos($activePage, 'pms_attendance') !== false || 
                strpos($activePage, 'leave_reports') !== false || strpos($activePage, 'attendance_reports') !== false) ? 'active' : ''; ?>">
                <a href="#" class="menu-toggle" data-menu="leave-management">
                    <i class="fa fa-calendar"></i>
                    <span class="nav-label" data-translate="leave-management">Leave Management</span>
                    <span class="fa arrow"></span>
                </a>
                <ul class="nav nav-second-level collapse <?php echo (strpos($activePage, 'pms_leaveform') !== false || strpos($activePage, 'pms_attendance') !== false || 
                    strpos($activePage, 'leave_reports') !== false || strpos($activePage, 'attendance_reports') !== false) ? 'in' : ''; ?>">
                    
                    <?php if($hasPMSAccess): ?>
                    <!-- 2.a. Add Leave Entry - Requires Module 20 or 21 -->
                    <li class="<?php echo (strpos($activePage, 'pms_leaveform.php') !== false) ? 'active' : ''; ?>">
                        <a href="/dpm/pms_leaveform.php" class="menu-link" data-parent="leave-management">
                            <i class="fa fa-calendar-plus-o"></i>
                            <span class="nav-label" data-translate="add-leave">Add Leave Entry</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if($hasPMSAccess): ?>
                    <!-- 2.b. Attendance Record - Requires Module 20 or 21 -->
                    <li class="<?php echo (strpos($activePage, 'pms_attendance.php') !== false) ? 'active' : ''; ?>">
                        <a href="/dpm/pms_attendance.php" class="menu-link" data-parent="leave-management">
                            <i class="fa fa-calendar-check-o"></i>
                            <span class="nav-label" data-translate="attendance-record">Attendance Record</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if($hasPMSAccess): ?>
                    <!-- 2.c. Reports Section - Requires Module 20 or 21 -->
                    <li id="leave-reports" class="<?php echo (strpos($activePage, 'leave_reports.php') !== false || strpos($activePage, 'attendance_reports.php') !== false) ? 'active' : ''; ?>">
                        <a href="#" class="menu-toggle" data-menu="leave-reports">
                            <i class="fa fa-file-text-o"></i>
                            <span class="nav-label">Reports</span>
                            <span class="fa arrow"></span>
                        </a>
                        <ul class="nav nav-third-level collapse <?php echo (strpos($activePage, 'leave_reports.php') !== false || strpos($activePage, 'attendance_reports.php') !== false) ? 'in' : ''; ?>">
                            
                            <?php if($hasPMSAccess): ?>
                            <!-- Leave Reports - Requires Module 20 or 21 -->
                            <li class="<?php echo (strpos($activePage, 'leave_reports.php') !== false) ? 'active' : ''; ?>" style="padding-left: 15px;">
                                <a href="/dpm/leave_reports.php" class="menu-link" data-parent="leave-reports">
                                    <i class="fa fa-calendar-minus-o"></i>
                                    <span class="nav-label">Leave Reports</span>
                                </a>
                            </li>
                            <?php endif; ?>
                            
                            <?php if($hasPMSAccess): ?>
                            <!-- Attendance Reports - Requires Module 20 or 21 -->
                            <li class="<?php echo (strpos($activePage, 'attendance_reports.php') !== false) ? 'active' : ''; ?>" style="padding-left: 15px;">
                                <a href="/dpm/attendance_reports.php" class="menu-link" data-parent="leave-reports">
                                    <i class="fa fa-calendar-check-o"></i>
                                    <span class="nav-label">Attendance Reports</span>
                                </a>
                            </li>
                            <?php endif; ?>
                            
                        </ul>
                    </li>
                    <?php endif; ?>
                </ul>
            </li>
            <?php endif; ?>

            <!-- ✅ NEW: Module 19 User - Attendance Record Only -->
            <?php if($isModule19User && !$hasPMSAccess): ?>
            <li id="attendance-record" class="<?php echo (strpos($activePage, 'pms_attendance.php') !== false) ? 'active' : ''; ?>">
                <a href="/dpm/pms_attendance.php" class="menu-link">
                    <i class="fa fa-calendar-check-o"></i>
                    <span class="nav-label" data-translate="attendance-record">Attendance Record</span>
                </a>
            </li>
            <?php endif; ?>

        </ul>
    </div>
</nav>

<!-- Script to ensure sidebar height adjusts dynamically and maintain menu state -->
<script>
// Wait for jQuery to be available
function initSidebarAfterJQuery() {
    if (typeof jQuery === 'undefined') {
        // jQuery not ready yet, try again in 100ms
        setTimeout(initSidebarAfterJQuery, 100);
        return;
    }

    var $ = jQuery;

    $(document).ready(function() {
        // Function to adjust sidebar height
        function adjustSidebarHeight() {
            var pageHeight = Math.max(
                $(document).height(),
                $(window).height(),
                $('#page-wrapper').height()
            );
            $('.navbar-static-side, .sidebar-collapse').css('min-height', pageHeight + 'px');
        }
        
        // Run on page load
        adjustSidebarHeight();
        
        // Run on window resize
        $(window).resize(function() {
            adjustSidebarHeight();
        });
        
        // Run when menu items expand/collapse
        if ($.fn.metisMenu) {
            $('#side-menu').metisMenu();
            
            $('#side-menu').on('shown.metisMenu hidden.metisMenu', function() {
                adjustSidebarHeight();
            });
        }
        
        // Run after AJAX content loads
        $(document).ajaxComplete(function() {
            setTimeout(adjustSidebarHeight, 200);
        });
        
        // Store current page path to identify active menu on page load
        var currentPath = window.location.pathname;
        
        // Menu toggle functionality with state persistence
        $(document).on('click', '.menu-toggle', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var menuId = $(this).data('menu');
            var $submenu = $(this).next('ul');
            
            if ($submenu.hasClass('in')) {
                // Menu is open, close it
                $submenu.removeClass('in');
                $(this).parent('li').removeClass('active');
                localStorage.removeItem('sidebar_' + menuId);
            } else {
                // Menu is closed, open it
                $submenu.addClass('in');
                $(this).parent('li').addClass('active');
                localStorage.setItem('sidebar_' + menuId, 'open');
            }
            
            adjustSidebarHeight();
        });
        
        // Handle menu link clicks to store the parent menu state
        $(document).on('click', '.menu-link', function() {
            var parentMenu = $(this).data('parent');
            if (parentMenu) {
                localStorage.setItem('sidebar_' + parentMenu, 'open');
                
                // Also store any grandparent menu if this is a third-level item
                var $grandparent = $(this).closest('.nav-second-level').prev('.menu-toggle');
                if ($grandparent.length) {
                    var grandparentMenu = $grandparent.data('menu');
                    if (grandparentMenu) {
                        localStorage.setItem('sidebar_' + grandparentMenu, 'open');
                    }
                }
            }
        });
        
        // Restore menu state from localStorage on page load
        function restoreMenuState() {
            // First check if the current page has an active menu item
            var hasActiveItem = false;
            
            // Check if any menu item is active based on URL
            $('.menu-link').each(function() {
                var linkPath = $(this).attr('href');
                if (linkPath && currentPath.indexOf(linkPath) !== -1) {
                    $(this).parent('li').addClass('active');
                    hasActiveItem = true;
                    
                    // Get parent menu ID and ensure it's open
                    var parentMenu = $(this).data('parent');
                    if (parentMenu) {
                        $('#' + parentMenu).addClass('active');
                        $('#' + parentMenu).find('> ul').addClass('in');
                        localStorage.setItem('sidebar_' + parentMenu, 'open');
                        
                        // If this is in a third level, ensure grandparent is open too
                        var $grandparentUl = $(this).closest('.nav-third-level');
                        if ($grandparentUl.length) {
                            $grandparentUl.addClass('in');
                            $grandparentUl.parent('li').addClass('active');
                            
                            // Also ensure the top-level parent is open
                            var $topParent = $grandparentUl.closest('.nav-second-level');
                            if ($topParent.length) {
                                $topParent.addClass('in');
                                $topParent.parent('li').addClass('active');
                            }
                        }
                    }
                }
            });
            
            // If no active item found by URL, restore from localStorage
            if (!hasActiveItem) {
                $('.menu-toggle').each(function() {
                    var menuId = $(this).data('menu');
                    var menuState = localStorage.getItem('sidebar_' + menuId);
                    
                    if (menuState === 'open') {
                        var $submenu = $(this).next('ul');
                        $submenu.addClass('in');
                        $(this).parent('li').addClass('active');
                    }
                });
            }
        }
        
        // Call restore function on page load
        restoreMenuState();
        
        // Initialize metisMenu plugin if available
        if ($.fn.metisMenu) {
            setTimeout(function() {
                restoreMenuState();
                adjustSidebarHeight();
            }, 100);
        }
    });
}

// Start initialization
initSidebarAfterJQuery();
</script>