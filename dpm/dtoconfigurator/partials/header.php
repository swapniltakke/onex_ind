<div id="pageHeader" class="ui stackable menu" style="margin-left:-35px;background-color:#f3f3f0;height:50px;">
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
