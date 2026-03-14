<?php
    $scriptName = strtolower(parse_url($_SERVER["SCRIPT_NAME"], PHP_URL_PATH));
?>

<nav class="navbar-default navbar-static-side navbar-static-side-alternate" style="position: fixed;" role="navigation">
    <div class="sidebar-collapse">
        <ul class="nav metismenu" id="side-menu">
            <li class="nav-header">
                <div class="dropdown profile-element">
                    <img alt="image" style="width:10rem;" class="rounded-circle"
                         src="/users/?gid=<?= SharedManager::getUser()["GID"] ?>">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <span class="block font-bold" style="text-align: center"><?= SharedManager::getUser()["FullName"] ?></span>
                    </a>
                </div>
                <div class="logo-element">
                    <a href="/">
                        <img style="width:50px;padding:10px;" src="/images/onex_icon.png" alt="">
                    </a>
                </div>
            </li>
            <li class="active">
                <a href="/users/management.php" aria-expanded="false">
                    <i class="fa fa-th-large"></i>
                    <span class="nav-label">User Management</span>
                    <span class="fa arrow"></span>
                </a>
                <ul class="nav nav-second-level collapse" aria-expanded="false">
                    <li class="<?= ($scriptName === "/users/management.php") ? 'landing_link' : '' ?>" style="padding: 0;">
                        <a href="/users/management.php">Users</a>
                    </li>
                    <li class="<?= ($scriptName === "/users/createuser.php") ? 'landing_link' : '' ?>" style="padding: 0;">
                        <a href="/users/createuser.php">Create User</a>
                    </li>
                    <li class="<?= ($scriptName === "/users/groups.php") ? 'landing_link' : '' ?>" style="padding: 0;">
                        <a href="/users/groups.php">Groups</a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>