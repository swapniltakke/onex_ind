<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/checklogin.php";
SharedManager::checkAuthToModule(35);

include_once './api/models/Journals.php';
Journals::saveJournal('Opening DTO Configurator Home Page', PAGE_GENERAL, GENERAL_HOME_PAGE, ACTION_PROCESSING, null, 'Home');
SharedManager::saveLog('log_dtoconfigurator', 'DTO Configurator Home Page Access');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DTO Configurator</title>
    <?php include_once '../dpm/dtoconfigurator/partials/libraries.php'; ?>
    <link href="/dpm/dtoconfigurator/assets/css/style.css" rel="stylesheet" type="text/css"/>
</head>
<body>

<!-- Main Content Area -->
<div class="pusher" id="dtoIndexPageContainer">
    <div id="pageHeader" class="ui stackable menu" style="background-color:#f3f3f0;height:50px;">
        <!-- Left Menu: Title -->
        <div class="header item">
            <a href="/dtoconfigurator">
                    DTO Configurator
            </a>
        </div>

        <div id="dtoHeaderDesc" class="item">
        <span style="color: grey;">
            Web system for configuring DTOs and handling customized orders
        </span>
        </div>

        <!-- Right-Aligned User Menu -->
        <div class="right menu">
            <div id="userInfoHeader" class="ui item">
                <img src="/users/?gid=<?php echo SharedManager::$gid ?>" alt="image">
                <div>
                    <div id="userNameSurname"><?= SharedManager::$name ?> <?= SharedManager::$surname ?></div>
                    <div id="userOrgCode"><?= SharedManager::$userOrgCode ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="ui container-fluid" style="margin-top: 50px;padding-right: 10%;padding-left: 10%;">
        <div class="ui stackable four column grid" style="margin:0 auto;">
            <div class="ui attached message dto-index-message">
                <div class="header dto-index-header">
                    Welcome to DTO Configurator Home Page
                </div>
                <p style="margin-top:1.2rem;"><b>DTO Configurator</b> is a web system developed for configuring Design to Orders (DTO) and handling customized orders.</p>
                <p> It streamlines team workflows, resolves ordering process challenges, and enhances efficiency within the organization.</p>
                <p> With a user-friendly interface, it empowers engineering teams to handle configured orders seamlessly, ensuring accuracy and timely product delivery.</p>
            </div>

            <div class="container-baba" style="display: flex;flex-direction: row;gap: 8px;padding:0;">
                <div class="column" style="padding:0!important;">
                    <a href="/dpm/dtoconfigurator/core/tkform/index.php" class="ui link fluid card">
                        <div class="image">
                            <img src="./assets/images/index-tkforms.png" alt="TK Forms">
                        </div>
                        <div class="content" style="height: 96px;">
                            <div class="header">TK Forms</div>
                            <div class="description">View and manage material lists in TK Forms of DTO numbers.</div>
                        </div>
                    </a>
                </div>

                <div class="column" style="padding:0!important;">
                    <a href="/dpm/dtoconfigurator/core/projects/index.php" class="ui link fluid card">
                        <div class="image">
                            <img src="./assets/images/index-projects.png" alt="Material Search">
                        </div>
                        <div class="content" style="height: 96px;">
                            <div class="header">Projects</div>
                            <div class="description">Manage DTO configurations and handle customized orders for panel design.</div>
                        </div>
                    </a>
                </div>

                <div class="column" style="padding:0!important;">
                    <a href="/dpm/dtoconfigurator/core/material-search/index.php" class="ui link fluid card">
                        <div class="image">
                            <img src="./assets/images/index-materialsearch.png" alt="Material Search">
                        </div>
                        <div class="content" style="height: 96px;">
                            <div class="header">Material Search</div>
                            <div class="description">Search for material lists and their usage inside the TK Forms.</div>
                        </div>
                    </a>
                </div>

                <div class="column" style="padding:0!important;">
                    <a href="/dpm/dtoconfigurator/core/banfomat/index.php" class="ui link fluid card">
                        <div class="image">
                            <img src="./assets/images/index-banfomat.png" alt="Banfomat">
                        </div>
                        <div class="content" style="height: 96px;">
                            <div class="header">Banfomat</div>
                            <div class="description"> Generate excel of coating details of coppers, including metals and coverage details.</div>
                        </div>
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="/dpm/dtoconfigurator/assets/js/main.js?<?=uniqid()?>"></script>
</body>
</html>
