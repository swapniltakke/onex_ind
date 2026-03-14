<?php
require_once $_SERVER["DOCUMENT_ROOT"] . '/users/auth.php';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>OneX User Management</title>

    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/users/components/headimports.php"; ?>
    <link rel="stylesheet" href="/users/css/management.css">
</head>
    <body>
        <div class="pace  pace-inactive">
            <div class="pace-progress" data-progress-text="100%" data-progress="99" style="transform: translate3d(100%, 0px, 0px);">
                <div class="pace-progress-inner"></div>
            </div>
            <div class="pace-activity"></div>
        </div>
        <div id="wrapper">
            <?php require_once $_SERVER["DOCUMENT_ROOT"]."/users/components/sidebar.php"; ?>
            <div id="page-wrapper" class="gray-bg">
                <div class="row border-bottom">
                    <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">
                        <div class="navbar-header" data-animation="fadeInLeft">
                            <a style="cursor:pointer;" class="navbar-minimalize minimalize-styl-2 btn btn-primary text-white"><i class="fa fa-bars"></i></a>
                        </div>
                    </nav>
                </div>
                <div>
                    <div class="ibox m-0" style="min-height: 80vh">
                        <div class="ibox-title">
                            <h5>User List</h5>
                        </div>
                        <div class="ibox-content table-container" >
                            <div class="d-block w-full loader" id="fullLoader" style="display: block !important;">
                                <h4 class="text-center">Loading...</h4>
                                <div class="spiner-example p-0">
                                    <div class="sk-spinner sk-spinner-cube-grid">
                                        <div class="sk-cube"></div>
                                        <div class="sk-cube"></div>
                                        <div class="sk-cube"></div>
                                        <div class="sk-cube"></div>
                                        <div class="sk-cube"></div>
                                        <div class="sk-cube"></div>
                                        <div class="sk-cube"></div>
                                        <div class="sk-cube"></div>
                                        <div class="sk-cube"></div>
                                    </div>
                                </div>
                            </div>
                            <div id="tableContainer"  style="display: none !important;">
                                <table id="usersTable" class="table  nowrap p-0" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="buttonsCol w-2 ta-c va-m p-0"></th>
                                            <th scope="col" class="idCol w-2 ta-c va-m p-0">ID</th>
                                            <th scope="col" class="emailCol w-15 ta-c va-m p-0">Email</th>
                                            <th scope="col" class=" w-13 ta-c va-m p-0">Group</th>
                                            <th scope="col" class=" w-5 ta-c va-m p-0">Country</th>
                                            <th scope="col" class=" w-10 ta-c va-m p-0">Last<br>Login</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    <script src="/users/js/management.js"></script>
</html>