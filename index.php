<?php
    SharedManager::saveLog('log_portal', 'Portal Page Access');
    $userInfo = SharedManager::getUser();
    
    // Generate modules array in PHP and convert to JSON for JavaScript
    $modulesArray = array();
    
    if(in_array(1, SharedManager::getUser()["Modules"])) {
        $modulesArray[] = array(
            "href" => "/ordersinplan",
            "added" => "2025-01-01",
            "keywords" => "orders in plan order date information master planning",
            "img" => "/images/mainmenu/1.svg",
            "label" => "ORDERS IN PLAN",
            "description" => "Order Date Information"
        );
    }
    
    if(in_array(2, SharedManager::getUser()["Modules"])) {
        $modulesArray[] = array(
            "href" => "/layout",
            "added" => "2025-01-01",
            "keywords" => "layout daily weekly production plan",
            "img" => "/images/mainmenu/2-2(2).svg",
            "label" => "LAYOUT",
            "description" => "Daily/Weekly Production Plan"
        );
    }
    
    if(in_array(3, SharedManager::getUser()["Modules"])) {
        $modulesArray[] = array(
            "href" => "/materialsearch",
            "added" => "2025-01-01",
            "keywords" => "material search materials in projects",
            "img" => "/images/mainmenu/51.svg",
            "label" => "MATERIAL SEARCH",
            "description" => "Search for Materials in Projects"
        );
    }
    
    if(in_array(13, SharedManager::getUser()["Modules"])) {
        $modulesArray[] = array(
            "href" => "/dpm/dwc/material_tracking.php?search=1",
            "added" => "2025-08-25",
            "keywords" => "material tracking material master data production information inventory logistics",
            "img" => "/images/mainmenu/material_tracking.svg",
            "label" => "MATERIAL TRACKING",
            "description" => "Material Master Data & Production Information"
        );
    }
    
    if(in_array(5, SharedManager::getUser()["Modules"])) {
        $modulesArray[] = array(
            "href" => "/opt/typicals",
            "added" => "2025-01-01",
            "keywords" => "typical quantities panel quantities project typicals",
            "img" => "/images/mainmenu/56.svg",
            "label" => "TYPICAL QUANTITIES",
            "description" => "Panel Quantities of Project Typicals"
        );
    }
    
    if(in_array(6, SharedManager::getUser()["Modules"])) {
        $modulesArray[] = array(
            "href" => "/assemblynotes/notes/detail.php",
            "added" => "2025-01-01",
            "keywords" => "missing assembly items assembly items",
            "img" => "/images/mainmenu/36.svg",
            "label" => "MISSING ASSEMBLY ITEMS",
            "description" => "Missing Assembly Items"
        );
        
        $modulesArray[] = array(
            "href" => "/assemblynotes/orderIssues/",
            "added" => "2025-01-01",
            "keywords" => "open issues project issues",
            "img" => "/images/mainmenu/63.svg",
            "label" => "OPEN ISSUES",
            "description" => "Project Issues"
        );
    }
    
    if(in_array(7, SharedManager::getUser()["Modules"])) {
        $modulesArray[] = array(
            "href" => "/opt",
            "added" => "2025-01-01",
            "keywords" => "panels lots qr project line informations",
            "img" => "/images/mainmenu/57.svg",
            "label" => "PANELS & LOTS (QR)",
            "description" => "Project Line Informations"
        );
    }
    
    if(SharedManager::getUser()["GroupID"] === 2) {
        $modulesArray[] = array(
            "href" => "/users/management.php",
            "added" => "2025-01-01",
            "keywords" => "user management users module",
            "img" => "/images/mainmenu/3-1.svg",
            "label" => "USER MANAGEMENT",
            "description" => "User Management Module"
        );
    }
    
    if(in_array(11, SharedManager::getUser()["Modules"])) {
        $modulesArray[] = array(
            "href" => "/dpm/index.php",
            "added" => "2025-01-01",
            "keywords" => "digital work center breaker manufacturing",
            "img" => "/images/mainmenu/22.svg",
            "label" => "DIGITAL WORK CENTER - BREAKER",
            "description" => "Breaker Manufacturing"
        );
    }
    
    if(in_array(14, SharedManager::getUser()["Modules"])) {
        $modulesArray[] = array(
            "href" => "/dpm/breaker_registration_form.php",
            "added" => "2025-01-01",
            "keywords" => "breaker status breaker tracking system",
            "img" => "/images/mainmenu/27.svg",
            "label" => "BREAKER STATUS",
            "description" => "Breaker Tracking system"
        );
    }
    
    if(in_array(15, SharedManager::getUser()["Modules"])) {
        $modulesArray[] = array(
            "href" => "/dpm/new_layout.php",
            "added" => "2025-01-01",
            "keywords" => "digital twin vcb line",
            "img" => "/images/mainmenu/58.svg",
            "label" => "DIGITAL TWIN",
            "description" => "VCB Line"
        );
    }
    
    if(in_array(16, SharedManager::getUser()["Modules"])) {
        $modulesArray[] = array(
            "href" => "/dpm/day_shift_wise_output.php",
            "added" => "2025-01-01",
            "keywords" => "day shift wise output sion m breaker output",
            "img" => "/images/mainmenu/26.svg",
            "label" => "DAY & SHIFT WISE OUTPUT",
            "description" => "SION M Breaker Output"
        );
    }
    
    if(in_array(17, SharedManager::getUser()["Modules"])) {
        $modulesArray[] = array(
            "href" => "/dpm/add_dto_data.php?mode=database",
            "added" => "2025-01-01",
            "keywords" => "dto database dto",
            "img" => "/images/mainmenu/dto_db.svg",
            "label" => "DTO DATABASE",
            "description" => "DTO Database"
        );
    }
    
    if(in_array(12, SharedManager::getUser()["Modules"])) {
        $modulesArray[] = array(
            "href" => "/dpm/dwc/index.php",
            "added" => "2025-01-01",
            "keywords" => "digital work center panel manufacturing",
            "img" => "/images/mainmenu/22.svg",
            "label" => "DIGITAL WORK CENTER - PANEL",
            "description" => "Panel Manufacturing"
        );
    }
    
    if(in_array(18, SharedManager::getUser()["Modules"])) {
        $modulesArray[] = array(
            "href" => "/dpm/panel_status.php",
            "added" => "2025-01-01",
            "keywords" => "order tracking system project panel tracking system",
            "img" => "/images/mainmenu/79.svg",
            "label" => "ORDER TRACKING SYSTEM",
            "description" => "Project/Panel Tracking System"
        );
    }
    
    if(in_array(19, SharedManager::getUser()["Modules"])) {
        $modulesArray[] = array(
            "href" => "/dpm/pms_attendance.php",
            "added" => "2025-01-01",
            "keywords" => "personnel management system personnel management statistics",
            "img" => "/images/mainmenu/pmslogo.svg",
            "label" => "PERSONNEL MANAGEMENT SYSTEM",
            "description" => "Personnel Management and Statistics"
        );
    }
    
    if(in_array(22, SharedManager::getUser()["Modules"])) {
        $modulesArray[] = array(
            "href" => "/dpm/document/document_details.php",
            "added" => "2025-01-01",
            "keywords" => "document management system document",
            "img" => "/images/mainmenu/document.svg",
            "label" => "DOCUMENT MANAGEMENT SYSTEM",
            "description" => "Document Management System"
        );
    }

    if(in_array(24, SharedManager::getUser()["Modules"])) {
        $modulesArray[] = array(
            "href" => "/dpm/add_dto_data.php?mode=configurator",
            "added" => "2025-01-01",
            "keywords" => "dto configurator",
            "img" => "/images/mainmenu/dto_config.svg",
            "label" => "DTO CONFIGURATOR",
            "description" => "Web system for configuring DTOs"
        );
    }
    
    // Convert to JSON
    $modulesJSON = json_encode($modulesArray);
?>
<html>
    <head>
        <title>OneX Home Page</title>
        <meta name="viewport" content="width=device-width, initial-scale=0.85, maximum-scale=1, user-scalable=yes"/>
        <link rel="shortcut icon" href="/images/onex_icon.png" type="image/x-icon">
        <link rel="icon" href="/images/onex_icon.png" type="image/x-icon">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
        <meta charset="utf-8">

        <script src="/js/jquery.min.js"></script>
        <script src="/js/semantic.min.js"></script>
        <script src="/js/jquery.toast.min.js"></script>

        <link href="/css/semantic.min.css" rel="stylesheet"/>
        <link href="/css/main-page.css" rel="stylesheet"/>
        <link href="/shared/inspia_gh_assets/font-awesome/css/font-awesome.css" rel="stylesheet">
        
        <style>
            :root {
                --gap-desktop: 18px;
                --cols-desktop: 4;
                --gap-mobile: 12px;
                --cols-mobile: 2;
                --card-height: 212.66px;
                --neon-size: 2px;
                --neon-radius: 27px;
                --neon-speed: 6s;
                --neon-grad: linear-gradient(90deg, #ff00cc, #6633ff, #00ffe6, #ffcc00, #ff00cc);
                --safe-top: 64px;
            }

            html {
                -webkit-text-size-adjust: 100%;
            }

            body {
                background: linear-gradient(180deg, #000028 0%, #009999 100%) fixed !important;
                margin: 0;
                padding: 0;
            }

            /* Improved styling for consistent alignment */
            .module-item {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: flex-start;
                background: rgba(255, 255, 255, 0.2) !important;
                border-radius: 16px;
                box-shadow: 0 4px 30px rgb(0 0 0 / 10%) !important;
                backdrop-filter: blur(5px) !important;
                -webkit-backdrop-filter: blur(5px);
                border: 1px solid rgba(255, 255, 255, 0.3) !important;
                color: #fff;
                text-decoration: none;
                text-align: center;
                padding: 0 16px;
                margin: 0 !important;
                height: var(--card-height);
                min-height: var(--card-height) !important;
                transition: background .2s ease, transform .08s ease;
                position: relative;
                overflow: hidden;
                cursor: pointer;
            }

            .module-item:hover {
                background: #009999bf !important;
                transform: translateY(-5px);
            }

            .module-item.hidden {
                display: none !important;
            }

            .module-item img {
                margin-top: 18px;
                margin-bottom: 14px;
                height: 72px;
                width: auto;
                display: block;
                object-fit: contain;
            }

            .module-item .content {
                width: 100%;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: flex-end;
                gap: 8px;
                padding-bottom: 12px;
                flex: 1 1 auto;
            }

            .ui.large.black.label, .description {
                max-width: 92%;
                overflow: hidden;
                text-overflow: ellipsis;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
            }

            .ui.large.black.label {
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 8px 12px;
                line-height: 1.1;
                min-height: 40px;
                box-sizing: border-box;
            }

            .description {
                padding-top: 0 !important;
                min-height: 42px;
            }

            #moduleGrid {
                display: grid !important;
                grid-template-columns: repeat(var(--cols-desktop), 1fr);
                gap: var(--gap-desktop);
                padding-bottom: 2rem;
            }

            #favoritesStrip {
                display: grid;
                grid-template-columns: repeat(var(--cols-desktop), 1fr);
                gap: var(--gap-desktop);
                width: 100%;
                padding: 8px 8px 10px 8px;
            }

            @media (max-width: 767px) {
                :root {
                    --card-height: 200px;
                }

                #moduleGrid, #favoritesStrip {
                    grid-template-columns: repeat(var(--cols-mobile), 1fr);
                    gap: var(--gap-mobile);
                }

                .module-item img {
                    height: 64px;
                }

                .ui.large.black.label {
                    min-height: 36px;
                }

                .description {
                    min-height: 38px;
                }
            }

            /* Search Bar Styling */
            .search-bar {
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .search-container {
                margin: auto;
                max-width: 900px;
                position: relative;
            }

            .search-input-wrap {
                position: relative;
                flex: 1 1 auto;
            }

            .search-input-wrap::before {
                content: "";
                position: absolute;
                inset: calc(-1 * var(--neon-size));
                border-radius: var(--neon-radius);
                background: var(--neon-grad);
                background-size: 300% 300%;
                filter: drop-shadow(0 0 6px rgba(0, 255, 200, .55));
                z-index: 0;
                pointer-events: none;
                animation: neonFlow var(--neon-speed) linear infinite;
            }

            @keyframes neonFlow {
                0% {
                    background-position: 0% 50%
                }
                50% {
                    background-position: 100% 50%
                }
                100% {
                    background-position: 0% 50%
                }
            }

            .search-input {
                position: relative;
                z-index: 1;
                width: 100%;
                padding: 15px 20px 15px 50px;
                line-height: 1px;
                font-family: 'Open Sans', 'Helvetica Neue', Arial, Helvetica, sans-serif;
                font-size: 16px;
                border: 2px solid transparent;
                border-radius: 25px;
                background: rgba(255, 255, 255, 0.1);
                color: white;
                backdrop-filter: blur(10px);
                -webkit-backdrop-filter: blur(10px);
                outline: none;
                transition: all .3s ease;
                background-color: #001330 !important;
            }

            @supports (-webkit-touch-callout: none) {
                .search-input {
                    font-size: 17px;
                }
            }

            @media (max-width: 767px) {
                .search-input {
                    font-size: 17px;
                    line-height: 1.2;
                }
            }

            .search-input::placeholder {
                color: rgba(255, 255, 255, 0.7);
            }

            .search-input:focus {
                background: rgba(255, 255, 255, 0.15);
                box-shadow: 0 0 20px rgba(0, 153, 153, 0.35);
            }

            .search-icon {
                position: absolute;
                left: 18px;
                top: 50%;
                transform: translateY(-50%);
                color: rgba(255, 255, 255, 0.7);
                font-size: 18px;
                z-index: 2;
            }

            .clear-search {
                position: absolute;
                right: 15px;
                top: 50%;
                transform: translateY(-50%);
                background: none;
                border: none;
                color: rgba(255, 255, 255, 0.7);
                font-size: 20px;
                cursor: pointer;
                padding: 5px;
                border-radius: 50%;
                transition: all .2s;
                display: none;
                z-index: 2;
            }

            .clear-search:hover {
                background: rgba(255, 255, 255, 0.2);
                color: white;
            }

            .no-results {
                text-align: center;
                color: rgba(255, 255, 255, 0.8);
                font-size: 18px;
                margin: 2rem 0;
                display: none;
            }

            .pill-btn {
                appearance: none;
                border: 0;
                padding: 10px 14px;
                border-radius: 999px;
                font-family: 'Open Sans', 'Helvetica Neue', 'Arial', 'Helvetica';
                font-size: 13px;
                font-weight: 800;
                letter-spacing: .2px;
                cursor: pointer;
                box-shadow: 0 8px 20px rgba(0, 0, 0, .15);
                transition: transform .08s, filter .2s, box-shadow .2s;
                white-space: nowrap;
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }

            #resetOrderBtn {
                color: #033;
                background: linear-gradient(135deg, #c7f9e6, #86f3cb);
                display: none;
            }

            .fav-star {
                position: absolute;
                top: 8px;
                right: 8px;
                width: 34px;
                height: 34px;
                border-radius: 999px;
                border: 1px solid rgba(255, 255, 255, 0.5);
                background: rgba(255, 255, 255, 0.85);
                display: inline-flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                z-index: 2;
                transition: transform .08s, background .2s;
            }

            .fav-star:hover {
                transform: scale(1.03);
                background: #fff;
            }

            .fav-star svg {
                width: 18px;
                height: 18px;
                stroke: #6b7280;
                fill: #335c69;
                stroke-width: 1.5;
            }

            .fav-star.active svg {
                fill: #F59E0B;
                stroke: #B45309;
            }

            .new-badge {
                position: absolute;
                top: 8px;
                left: 8px;
                height: 24px;
                padding: 0 10px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 999px;
                border: 1px solid rgba(255, 255, 255, 0.5);
                background: rgba(255, 255, 255, 0.9);
                color: #0f766e;
                font-weight: 900;
                font-size: 12px;
                letter-spacing: .4px;
                z-index: 2;
                user-select: none;
                pointer-events: none;
                box-shadow: 0 2px 10px rgba(0, 0, 0, .08);
            }

            #favoritesSection {
                margin-top: 8px;
                margin-bottom: 18px;
                padding: 8px 4px 0 4px;
                border: 1px dashed rgba(255, 255, 255, 0.35);
                border-radius: 14px;
                background: rgba(255, 255, 255, 0.08);
                display: none;
            }

            #favoritesHeader {
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 8px 8px 4px 8px;
                color: #fff;
            }

            #favoritesHeader h3 {
                margin: 0;
                font-size: 16px;
                font-weight: 800;
                letter-spacing: .2px;
            }

            #favCount {
                opacity: .85;
                font-size: 16px;
                font-weight: 800;
            }

            #clearFavs {
                margin-left: auto;
                color: #033;
                background: linear-gradient(135deg, #c7f9e6, #86f3cb);
            }

            #mainbg {
                background: radial-gradient(black, transparent) !important;
                background-size: cover !important;
            }

            .drag-ghost {
                opacity: .75;
            }

            .sortable-chosen {
                opacity: .95;
            }

            /* Loading Overlay */
            #loadingOverlay {
                position: fixed;
                top: var(--safe-top);
                left: 0;
                width: 100%;
                height: calc(100% - var(--safe-top));
                background: linear-gradient(180deg, #000028 0%, #009999 100%);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 9999;
                transition: opacity .4s ease;
            }

            .spinner {
                width: 64px;
                height: 64px;
                border: 6px solid rgba(255, 255, 255, 0.25);
                border-top-color: #00c2ff;
                border-radius: 50%;
                animation: spin 1s linear infinite;
                box-shadow: 0 0 20px rgba(0, 194, 255, 0.6);
            }

            @keyframes spin {
                to {
                    transform: rotate(360deg);
                }
            }

            /* Fixed Search Bar */
            #searchFixed {
                position: fixed;
                top: var(--safe-top);
                left: 0;
                right: 0;
                z-index: 1100;
                backdrop-filter: blur(6px);
                -webkit-backdrop-filter: blur(6px);
                will-change: transform;
                transform: translateZ(0);
            }

            .search-shell {
                position: relative;
                max-width: 980px;
                margin: 0 auto;
                padding: 12px 12px;
            }

            #searchSpacer {
                height: 0px;
            }

            /* Header styling */
            .onex-header {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                z-index: 1000;
                background-color: #1B1C1D;
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0.5rem 1rem;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                overflow: visible;
            }

            .header-right-profile {
                display: flex;
                align-items: center;
                position: relative;
            }

            /* Dropdown styling - FIXED */
            #logout {
                position: fixed;
                z-index: 10000;
                display: none;
            }

            .arrowDropdowns {
                position: fixed;
                background-color: #fff;
                border: 1px solid #ddd;
                border-radius: 4px;
                padding: 15px 20px;
                box-shadow: 0 8px 24px rgba(0,0,0,0.3);
                display: flex;
                align-items: center;
                gap: 10px;
                z-index: 10000;
                min-width: 180px;
            }

            .logoutAnchors {
                color: #333;
                text-decoration: none;
                font-weight: 600;
                font-size: 14px;
                transition: all 0.2s ease;
            }

            .logoutAnchors:hover {
                color: #000;
                text-decoration: underline;
            }

            /* Arrow SVG styling */
            #arrowSvg {
                transition: transform 0.3s ease;
                cursor: pointer;
            }

            /* Color classes */
            .tealx { background-color: #00B5AD !important; }
            .brownx { background-color: #A5673F !important; }
            .bluex { background-color: #2185D0 !important; }
            .yellowx { background-color: #FBBD08 !important; }

            /* Footer styling */
            .ui.attached.segment {
                margin-top: 2rem;
                padding: 1.5rem;
                font-size: 0.9rem;
            }

            /* Row spacing */
            .ui.grid .row {
                padding-bottom: 0 !important;
            }
        </style>
    </head>
    <body>
        <!-- Header -->
        <div class="onex-header">
            <div class="header-left-logo">
                <svg xmlns="http://www.w3.org/2000/svg" xml:space="preserve" width="180px" height="40px"
                    style="shape-rendering:geometricPrecision; text-rendering:geometricPrecision; image-rendering:optimizeQuality; fill-rule:evenodd; clip-rule:evenodd"
                    viewBox="0 0 210 50">
                    <g id="Ebene_x0020_1">
                        <metadata id="CorelCorpID_0Corel-Layer"></metadata>
                        <path class="fil0"
                            d="M200.121 10.3466l0 5.8289c-3.0198,-1.14 -5.7084,-1.7164 -8.0615,-1.7164 -1.3938,0 -2.5037,0.2581 -3.3382,0.7571 -0.8346,0.5033 -1.2605,1.1228 -1.2605,1.8541 0,0.9722 0.9421,1.8368 2.8392,2.6112l5.4805 2.6671c4.4309,2.1122 6.6291,4.9169 6.6291,8.4401 0,2.9295 -1.1658,5.2654 -3.5189,6.9947 -2.3359,1.7466 -5.4805,2.6112 -9.3951,2.6112 -1.8068,0 -3.4285,-0.0774 -4.8696,-0.2409 -1.4411,-0.1548 -3.0973,-0.4732 -4.9342,-0.9292l0 -6.0999c3.3683,1.14 6.4355,1.7164 9.1972,1.7164 3.2952,0 4.9342,-0.955 4.9342,-2.8822 0,-0.9593 -0.6711,-1.7336 -2.0347,-2.3402l-6.0871 -2.594c-2.2455,-1.0152 -3.9146,-2.2455 -5.0073,-3.7038 -1.0754,-1.4712 -1.6218,-3.1575 -1.6218,-5.0847 0,-2.6973 1.1357,-4.8697 3.3812,-6.5216 2.2628,-1.639 5.2655,-2.4606 8.9994,-2.4606 1.2131,0 2.6112,0.1075 4.1599,0.3054 1.5615,0.2108 3.0628,0.4689 4.5082,0.7873z"></path>
                        <path class="fil0"
                            d="M27.7222 10.3466l0 5.8289c-3.0199,-1.14 -5.7042,-1.7164 -8.0573,-1.7164 -1.3981,0 -2.5036,0.2581 -3.3382,0.7571 -0.8345,0.5033 -1.2604,1.1228 -1.2604,1.8541 0,0.9722 0.955,1.8368 2.8521,2.6112l5.4805 2.6671c4.4136,2.1122 6.6162,4.9169 6.6162,8.4401 0,2.9295 -1.1701,5.2654 -3.506,6.9947 -2.3531,1.7466 -5.4805,2.6112 -9.408,2.6112 -1.8068,0 -3.4329,-0.0774 -4.874,-0.2409 -1.4411,-0.1548 -3.0801,-0.4732 -4.9298,-0.9292l0 -6.0999c3.3812,1.14 6.4484,1.7164 9.1929,1.7164 3.2952,0 4.9342,-0.955 4.9342,-2.8822 0,-0.9593 -0.6668,-1.7336 -2.0176,-2.3402l-6.087 -2.594c-2.2628,-1.0152 -3.9319,-2.2455 -5.0073,-3.7038 -1.0927,-1.4712 -1.6261,-3.1575 -1.6261,-5.0847 0,-2.6973 1.1271,-4.8697 3.3855,-6.5216 2.2456,-1.639 5.2525,-2.4606 8.9865,-2.4606 1.226,0 2.6069,0.1075 4.1727,0.3054 1.5487,0.2108 3.05,0.4689 4.4911,0.7873z"></path>
                        <polygon class="fil0" points="34.0028,9.8002 42.9291,9.8002 42.9291,39.8483 34.0028,39.8483 "></polygon>
                        <polygon class="fil0"
                                points="71.6866,9.8002 71.6866,15.3539 58.4241,15.3539 58.4241,22.0173 69.9272,22.0173 69.9272,27.0246 58.4241,27.0246 58.4241,34.0194 71.9576,34.0194 71.9576,39.8483 49.8335,39.8483 49.8335,9.8002 "></polygon>
                        <polygon class="fil0"
                                points="113.358,9.8002 113.358,39.8483 105.025,39.8483 105.025,20.0299 96.3789,40.1236 91.234,40.1236 82.9186,20.0299 82.9186,39.8483 76.8918,39.8483 76.8918,9.8002 87.7882,9.8002 95.226,28.1947 103.008,9.8002 "></polygon>
                        <polygon class="fil0"
                                points="142.103,9.8002 142.103,15.3539 128.913,15.3539 128.913,22.0173 140.416,22.0173 140.416,27.0246 128.913,27.0246 128.913,34.0194 142.374,34.0194 142.374,39.8483 120.25,39.8483 120.25,9.8002 "></polygon>
                        <polygon class="fil0"
                                points="173.424,9.8002 173.424,39.8483 163.956,39.8483 153.331,20.5762 153.331,39.8483 147.308,39.8483 147.308,9.8002 157.052,9.8002 167.402,28.7411 167.402,9.8002 "></polygon>
                    </g>
                </svg>
            </div>
            <div class="header-center">
                <img class="logo" src="images/onex.png" style="height: 60px;">
            </div>
            <div class="header-right-profile">
                <div>
                    <img alt="image" style="border-radius: 50%;width: auto;max-width: 65px;max-height: 60px;height: auto;" src="/users/?gid=<?= $userInfo["GID"]; ?>">
                </div>
                <div style="padding-left: 8px;">
                    <div style="padding-left: 5px; color: white;"> <?= $userInfo["Name"] ?> <?= $userInfo["Surname"] ?></div>
                    <div style="padding-left: 5px; font-size: 11px; color: rgba(255,255,255,0.7);"> <?= $userInfo["OrgCode"] ?></div>
                </div>
                <div style="padding-left: 8px; cursor: pointer;" id="profileDropdownBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" fill="#FFF" height="19px" width="19px" version="1.1" id="arrowSvg" viewBox="0 0 330 330" xml:space="preserve">
                        <path id="XMLID_225_" d="M325.607,79.393c-5.857-5.857-15.355-5.858-21.213,0.001l-139.39,139.393L25.607,79.393  c-5.857-5.857-15.355-5.858-21.213,0.001c-5.858,5.858-5.858,15.355,0,21.213l150.004,150c2.813,2.813,6.628,4.393,10.606,4.393  s7.794-1.581,10.606-4.394l149.996-150C331.465,94.749,331.465,85.251,325.607,79.393z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Logout Dropdown - FIXED POSITIONING -->
        <div id="logout">
            <div id="logout2" class="arrowDropdowns">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="20px" height="20px"
                     version="1.1" viewBox="0 0 700 700" fill="#333">
                    <g>
                        <path d="m359.61 478.8h-162.11c-3.0625 0-5.5547-2.5156-5.5547-5.6016v-386.4c0-3.0859 2.4922-5.6016 5.5547-5.6016h162.11c6.1836 0 11.199-5.0195 11.199-11.199 0-6.1836-5.0195-11.199-11.199-11.199h-162.11c-15.418 0-27.957 12.562-27.957 28v386.4c0 15.438 12.539 28 27.957 28h162.11c6.1836 0 11.199-5.0195 11.199-11.199 0.003906-6.1836-5.0156-11.199-11.199-11.199zm167.57-206.72l-96.035-96.027c-4.375-4.375-11.465-4.375-15.836 0-4.375 4.375-4.375 11.465 0 15.836l76.91 76.91h-218.38c-6.1836 0-11.199 5.0195-11.199 11.199 0 6.1836 5.0195 11.199 11.199 11.199h218.38l-76.91 76.906c-4.375 4.375-4.375 11.465 0 15.836 2.1914 2.1914 5.0508 3.2812 7.918 3.2812 2.8672 0 5.7305-1.0938 7.918-3.2812l96.027-96.023c2.1016-2.1016 3.2812-4.9492 3.2812-7.918s-1.1758-5.8203-3.2734-7.918z"></path>
                    </g>
                </svg>
                <a class="logoutAnchors" href="/shared/screens/logout.php">Logout</a>
            </div>
        </div>

        <!-- Loading Overlay -->
        <div id="loadingOverlay" aria-hidden="true">
            <div class="spinner" role="status" aria-label="Loading"></div>
        </div>

        <div id="mainbg">
            <!-- Fixed Search Bar -->
            <div id="searchFixed">
                <div class="search-shell">
                    <div class="search-container">
                        <div class="search-bar">
                            <div class="search-input-wrap">
                                <div class="search-icon">🔍</div>
                                <input type="text" id="searchInput" autofocus class="search-input" placeholder="">
                                <button class="clear-search" id="clearSearch" title="Clear filter">✕</button>
                            </div>
                            <button id="resetOrderBtn" class="pill-btn" title="Reset to default order">Default Order</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Spacer to maintain content position -->
            <div id="searchSpacer" aria-hidden="true"></div>

            <div class="ui container" id="mainContainer" style="padding-top:6em;padding-bottom:15px;">

                <!-- Favorites Section -->
                <div id="favoritesSection" aria-live="polite">
                    <div id="favoritesHeader">
                        <h3>Favorites</h3>
                        <span id="favCount"></span>
                        <button id="clearFavs" class="pill-btn">Clear Favorites and Reset Order</button>
                    </div>
                    <div id="favoritesStrip"></div>
                </div>

                <div class="no-results" id="noResults" aria-live="polite" role="status">
                    <h3>No results found 😕</h3>
                    <p>No modules match your search criteria. Please try different keywords.</p>
                </div>

                <div class="four column doubling center aligned padded ui grid" id="moduleGrid"></div>

                <div class="ui four column doubling attached center aligned padded bottom attached inverted grey segment"
                     style="border-radius:5px;">
                    <div id="users"></div>
                </div>
                
                <div class="ui four column doubling attached center aligned padded bottom attached inverted grey segment">
                    <b><?= date('Y') ?> OneX (Version: THA)
                        <span>
                            <img class="flag-icon" src="https://flagcdn.com/in.svg" alt="IN"
                            style="width:22px; height:15px; border:1px solid #ccc; margin-right:5px; vertical-align:middle;">
                        </span>
                        </b>
                    <br>
                    💡OneX empowers our t<b>EA</b>ms with unified access to Snowflake, SAP, MTool, QR-Code Tracking and more,
                    turning data into clarity, streamlining operations and strengthening our competitive edge.<br><br>
                    👨‍💻 Designed & Developed for SI EA Operations by Digital Transformation Group (SI EA O AIS THA DGT) 🌟
                    <span style="display:flex;justify-content:center;align-items:flex-end;">
                        Send us an email
                        <a href="mailto:<?= SharedManager::getFromSharedEnv("CONTACT_EMAIL"); ?>" style="color:#FFF;padding-left:5px;">
                            <svg width="20" height="20" viewBox="0 0 710 564" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M582.4 182.28C575.681 173.322 562.988 172.572 554.025 179.296L354.665 341.322L155.305 180.042C146.347 173.323 134.399 174.068 126.931 183.027C120.212 191.985 120.957 203.933 129.915 211.401L254.608 312.204L129.92 418.231C121.707 425.699 120.212 438.392 127.681 446.605C131.415 451.085 137.389 453.324 142.613 453.324C147.092 453.324 151.571 451.829 155.305 448.845L285.972 337.59L341.227 382.393C344.961 385.377 349.44 386.872 353.919 386.872C358.399 386.872 362.878 385.377 366.612 382.393L421.867 337.59L552.533 448.845C556.268 451.829 560.747 453.324 565.226 453.324C571.2 453.324 576.424 451.085 580.158 446.605C587.627 438.392 586.132 425.699 577.919 418.231L454.716 311.455L579.409 210.652C587.623 203.933 589.117 191.24 582.399 182.283Z"
                                      fill="white"/>
                                <path d="M651.093 65.0533H507.733C496.535 65.0533 487.572 74.0117 487.572 85.2146C487.572 96.4125 496.53 105.376 507.733 105.376H651.093C660.802 105.376 669.015 113.59 669.015 123.297V504.844C669.015 514.552 660.801 522.765 651.093 522.765L58.24 522.76C48.5317 522.76 40.3187 514.546 40.3187 504.839V123.292C40.3187 113.584 48.5323 105.371 58.24 105.371H201.6C212.798 105.371 221.761 96.4124 221.761 85.2094C221.761 74.0116 212.803 65.0481 201.6 65.0481L58.24 65.0533C26.136 65.0533 0 91.1893 0 123.293V504.84C0 536.944 26.136 563.08 58.24 563.08H651.093C683.197 563.08 709.333 536.944 709.333 504.84V123.293C709.333 91.1893 683.197 65.0533 651.093 65.0533V65.0533Z"
                                      fill="white"/>
                                <path d="M274.027 141.96C277.761 150.918 282.985 158.387 290.453 165.105C297.922 171.824 306.136 177.053 316.589 180.788C327.042 185.267 338.991 186.762 352.428 186.762C363.626 186.762 372.589 186.017 380.803 183.778C389.016 182.283 396.485 179.298 402.453 176.309C409.922 173.324 409.922 162.871 402.453 159.137L400.959 158.392C394.985 155.408 388.266 155.408 382.292 157.647C379.307 158.392 374.823 159.887 370.344 160.632C365.115 161.376 359.146 161.376 351.677 161.376C342.719 161.376 334.505 160.632 327.037 158.392C320.319 156.152 314.345 153.163 309.865 148.684C304.636 144.205 300.907 138.975 298.667 133.001C295.683 127.772 293.438 120.308 293.438 113.589C292.693 111.349 292.693 108.36 292.693 105.375C292.693 102.391 291.949 98.6567 291.949 94.9224C291.949 91.938 292.693 88.2036 292.693 85.2141C292.693 82.2297 292.693 79.2401 293.438 77.0005C294.183 69.5317 296.423 63.5632 298.667 57.5885C301.652 51.6145 305.386 46.3907 309.865 41.9059C315.095 38.1715 321.063 34.4371 327.787 32.1976C335.255 29.958 343.469 28.4632 353.172 28.4632C365.12 28.4632 374.823 29.208 383.036 31.4476C390.505 32.9424 396.473 36.6768 401.703 40.406C406.932 44.8852 410.661 49.3644 412.901 56.0887C415.14 62.8075 416.635 69.526 418.13 77.7393C418.875 82.2185 418.875 85.9529 418.875 88.9372C418.875 92.6716 418.875 96.406 418.13 100.885C418.13 106.114 417.385 109.099 415.145 111.338C413.651 112.833 410.666 114.323 407.677 114.323Z"
                                      fill="white"/>
                            </svg>
                        </a>
                    </span>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

        <script>
            /* ====== GLOBAL VARIABLES ====== */
            const ORDER_KEY_NAME = 'portal_module_order_v1';
            const FAV_KEY_NAME = 'portal_favorites_v1';
            const moduleGrid = document.getElementById('moduleGrid');
            const favoritesSection = document.getElementById('favoritesSection');
            const favoritesStrip = document.getElementById('favoritesStrip');
            const favCount = document.getElementById('favCount');
            const resetOrderBtn = document.getElementById('resetOrderBtn');
            const clearFavsBtn = document.getElementById('clearFavs');

            const SAVE_ENDPOINT = '/users/components/save_user_fav.php';

            function hasFilter() {
                const si = document.getElementById('searchInput');
                return !!(si && si.value.trim());
            }

            function isOrderModified() {
                return localStorage.getItem(ORDER_KEY_NAME) !== null;
            }

            function getFavs() {
                try {
                    return JSON.parse(localStorage.getItem(FAV_KEY_NAME) || '[]');
                } catch {
                    return [];
                }
            }

            function saveFavs(arr) {
                localStorage.setItem(FAV_KEY_NAME, JSON.stringify(Array.from(new Set(arr))));
            }

            function updateTopButtonsVisibility() {
                const clearXBtn = document.getElementById('clearSearch');
                const filterOn = hasFilter();
                const modified = isOrderModified();
                const favOn = getFavs().length > 0;
                clearXBtn.style.display = filterOn ? 'block' : 'none';
                resetOrderBtn.style.display = (!filterOn && !favOn && modified) ? 'inline-flex' : 'none';
            }

            function sendFavState(reason) {
                const moduleOrder = localStorage.getItem(ORDER_KEY_NAME) || '[]';
                const favModules = localStorage.getItem(FAV_KEY_NAME) || '[]';
                fetch(SAVE_ENDPOINT, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'},
                    body: new URLSearchParams({module_order: moduleOrder, fav_modules: favModules, reason})
                }).catch(() => {
                });
            }

            /* ====== MODULE DEFINITIONS - FROM PHP ====== */
            const MODULES = <?= $modulesJSON; ?>;

            /* ====== HELPER FUNCTIONS ====== */
            function pickRandom(arr, n) {
                if (!Array.isArray(arr)) return [];
                const len = arr.length >>> 0;
                const take = Math.max(0, Math.min(len, n | 0));
                if (len <= 1 || take <= 0) return len ? [arr[0]] : [];
                const a = arr.slice();
                for (let i = len - 1; i > 0; i--) {
                    const j = Math.floor(Math.random() * (i + 1));
                    [a[i], a[j]] = [a[j], a[i]];
                }
                return a.slice(0, take);
            }

            function stamp(iso) {
                if (!iso) return null;
                const d = new Date(String(iso).replace(/-/g, '/'));
                return isNaN(d) ? null : d.getTime();
            }

            function isFresh(mod) {
                if (!mod || !mod.added) return false;
                const t = stamp(mod.added);
                if (t === null) return false;
                return ((Date.now() - t) / 86400000) < 30;
            }

            /* Placeholder rotation */
            let __phIntervalId = null;

            function updatePlaceholder() {
                const el = document.getElementById("searchInput");
                if (!el) return;
                const labels = MODULES.map(m => m.label || '').filter(Boolean);
                const r = pickRandom(labels, 2);
                el.setAttribute("placeholder", r.length ? `Which module are you looking for? (e.g: ${r.join(", ")})` : "Which module are you looking for?");
                queueMicrotask(updateSearchSpacerHeight);
            }

            document.addEventListener('DOMContentLoaded', () => {
                updatePlaceholder();
                if (!__phIntervalId) __phIntervalId = setInterval(updatePlaceholder, 2000);
            });

            /* ====== RENDER ====== */
            const itemsMap = new Map();
            const freshMap = new Map();
            const addedTimeMap = new Map();
            let defaultOrder = [], activeOrder = [];
            const slugify = (txt) => (txt || '').toString().trim().toLowerCase().replace(/[^\p{L}\p{N}]+/gu, '-').replace(/^-+|-+$/g, '') || null;

            function buildModuleCard(mod, id) {
                const a = document.createElement('a');
                a.className = 'hoverable module-item';
                a.href = mod.href || '#';
                a.dataset.id = id;
                a.setAttribute('data-keywords', mod.keywords || '');
                a.style.cursor = 'pointer';

                if (freshMap.get(id) === true) {
                    const badge = document.createElement('span');
                    badge.className = 'new-badge';
                    badge.textContent = 'NEW';
                    a.appendChild(badge);
                }

                const img = document.createElement('img');
                img.src = mod.img || '';
                const content = document.createElement('div');
                content.className = 'content';
                const title = document.createElement('div');
                title.className = 'ui large black label';
                title.textContent = mod.label.toUpperCase() || '';
                const desc = document.createElement('div');
                desc.className = 'description';
                desc.textContent = mod.description || '';
                content.appendChild(title);
                content.appendChild(desc);
                a.appendChild(img);
                a.appendChild(content);

                const btn = document.createElement('button');
                btn.className = 'fav-star';
                btn.type = 'button';
                btn.setAttribute('aria-label', 'Favorite');
                btn.innerHTML = `<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.62L12 2 9.19 8.62 2 9.24l5.46 4.73L5.82 21z"/></svg>`;
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    toggleFavorite(a.dataset.id);
                });
                a.appendChild(btn);

                return a;
            }

            function renderModules(modules) {
                moduleGrid.innerHTML = '';
                const frag = document.createDocumentFragment();
                modules.forEach((m, i) => {
                    const id = slugify(m.label || `module-${i}`) || `module-${i}`;
                    const isN = isFresh(m);
                    freshMap.set(id, isN);
                    addedTimeMap.set(id, stamp(m.added));
                    const el = buildModuleCard(m, id);
                    itemsMap.set(id, el);
                    frag.appendChild(el);
                });
                moduleGrid.appendChild(frag);

                defaultOrder = Array.from(itemsMap.keys());

                try {
                    const saved = JSON.parse(localStorage.getItem(ORDER_KEY_NAME) || 'null');
                    const base = Array.isArray(saved) && saved.length ? saved.filter(id => itemsMap.has(id)) : [];
                    const missing = defaultOrder.filter(id => !base.includes(id));
                    activeOrder = [...base, ...missing];
                } catch {
                    activeOrder = [...defaultOrder];
                }

                applyActiveOrderToDOM();
            }

            function applyActiveOrderToDOM() {
                const favs = getFavs();
                favoritesSection.style.display = favs.length ? 'block' : 'none';
                favCount.textContent = favs.length ? `(${favs.length})` : '';
                favoritesStrip.innerHTML = '';
                favs.forEach(id => {
                    const el = itemsMap.get(id);
                    if (el) favoritesStrip.appendChild(el);
                });

                const notFav = activeOrder.filter(id => !favs.includes(id));
                const fresh = notFav.filter(id => freshMap.get(id) === true);
                const rest = notFav.filter(id => freshMap.get(id) !== true);

                fresh.sort((a, b) => {
                    const ta = addedTimeMap.get(a) ?? 0;
                    const tb = addedTimeMap.get(b) ?? 0;
                    return tb - ta;
                });

                moduleGrid.innerHTML = '';
                [...fresh, ...rest].forEach(id => {
                    const el = itemsMap.get(id);
                    if (el) moduleGrid.appendChild(el);
                });

                itemsMap.forEach((el, id) => {
                    const btn = el.querySelector('.fav-star');
                    if (btn) btn.classList.toggle('active', favs.includes(id));
                });
            }

            function persistOrderFromDOM() {
                const favIds = Array.from(favoritesStrip.querySelectorAll('.module-item')).map(el => el.dataset.id);
                saveFavs(favIds);
                const gridIds = Array.from(moduleGrid.querySelectorAll('.module-item')).map(el => el.dataset.id);
                const full = [...favIds, ...gridIds];
                itemsMap.forEach((_, id) => {
                    if (!full.includes(id)) full.push(id);
                });
                activeOrder = full;
                localStorage.setItem(ORDER_KEY_NAME, JSON.stringify(activeOrder));
                sendFavState('persist_from_dom');
            }

            function toggleFavorite(id) {
                const favs = getFavs();
                const i = favs.indexOf(id);
                if (i >= 0) favs.splice(i, 1); else favs.push(id);
                saveFavs(favs);
                applyActiveOrderToDOM();
                persistOrderFromDOM();
                updateTopButtonsVisibility();
                sendFavState('favorite_toggled');
            }

            /* ====== SORTABLE ====== */
            let gridSortable = null, favoritesSortable = null;

            function initSortables() {
                if (gridSortable) {
                    try {
                        gridSortable.destroy();
                    } catch (_) {
                    }
                }
                if (favoritesSortable) {
                    try {
                        favoritesSortable.destroy();
                    } catch (_) {
                    }
                }
                const sortingEnabled = !hasFilter();
                gridSortable = new Sortable(moduleGrid, {
                    group: {name: 'mods', pull: true, put: true}, animation: 150,
                    ghostClass: 'drag-ghost', chosenClass: 'sortable-chosen', dragClass: 'drag-ghost',
                    sort: sortingEnabled,
                    onAdd: () => {
                        persistOrderFromDOM();
                        updateTopButtonsVisibility();
                        sendFavState('dnd_add');
                    },
                    onUpdate: () => {
                        persistOrderFromDOM();
                        updateTopButtonsVisibility();
                        sendFavState('dnd_update');
                    },
                    onEnd: () => {
                        persistOrderFromDOM();
                        updateTopButtonsVisibility();
                        sendFavState('dnd_end');
                    }
                });
                favoritesSortable = new Sortable(favoritesStrip, {
                    group: {name: 'mods', pull: true, put: true}, animation: 150,
                    ghostClass: 'drag-ghost', chosenClass: 'sortable-chosen', dragClass: 'drag-ghost',
                    sort: sortingEnabled,
                    onAdd: (evt) => {
                        const id = evt.item?.dataset?.id;
                        if (id) {
                            const favs = getFavs();
                            if (!favs.includes(id)) {
                                favs.push(id);
                                saveFavs(favs);
                            }
                        }
                        persistOrderFromDOM();
                        applyActiveOrderToDOM();
                        updateTopButtonsVisibility();
                        sendFavState('fav_onadd');
                    },
                    onUpdate: () => {
                        persistOrderFromDOM();
                        updateTopButtonsVisibility();
                        sendFavState('fav_onupdate');
                    },
                    onEnd: () => {
                        persistOrderFromDOM();
                        updateTopButtonsVisibility();
                        sendFavState('fav_onend');
                    }
                });
            }

            /* MOBILE RESPONSIVE */
            const MOBILE_BP = 767;

            function isMobileView() {
                return window.matchMedia(`(max-width: ${MOBILE_BP}px)`).matches;
            }

            function setupDnDByViewport() {
                const mobile = isMobileView();
                if (mobile) {
                    if (gridSortable) {
                        try {
                            gridSortable.destroy();
                        } catch (_) {
                        }
                        gridSortable = null;
                    }
                    if (favoritesSortable) {
                        try {
                            favoritesSortable.destroy();
                        } catch (_) {
                        }
                        favoritesSortable = null;
                    }
                } else {
                    if (!gridSortable || !favoritesSortable) initSortables();
                }
            }

            window.addEventListener('resize', setupDnDByViewport);

            /* RESET BUTTONS */
            resetOrderBtn.addEventListener('click', () => {
                localStorage.removeItem(ORDER_KEY_NAME);
                activeOrder = [...defaultOrder];
                applyActiveOrderToDOM();
                updateTopButtonsVisibility();
                try {
                    navigator.vibrate && navigator.vibrate(15);
                } catch (_) {
                }
                sendFavState('reset_order_btn');
            });
            clearFavsBtn.addEventListener('click', () => {
                if (!confirm('Clear all favorites and custom module order?\nPage will return to default layout.')) return;
                localStorage.setItem(FAV_KEY_NAME, JSON.stringify([]));
                localStorage.removeItem(ORDER_KEY_NAME);
                activeOrder = [...defaultOrder];
                applyActiveOrderToDOM();
                updateTopButtonsVisibility();
                setupDnDByViewport();
                sendFavState('clear_favs_btn');
            });

            /* INITIALIZE */
            renderModules(MODULES);
            setupDnDByViewport();
            updateTopButtonsVisibility();

            document.addEventListener('DOMContentLoaded', () => {
                sendFavState('page_load');
            });

            /* ====== SEARCH + FILTERING ====== */
            document.addEventListener('DOMContentLoaded', function () {
                const searchInput = document.getElementById('searchInput');
                const clearButton = document.getElementById('clearSearch');
                const noResults = document.getElementById('noResults');

                function setSortingEnabled(enabled) {
                    if (gridSortable) gridSortable.option('sort', enabled);
                    if (favoritesSortable) favoritesSortable.option('sort', enabled);
                }

                function performSearch() {
                    const term = (searchInput.value || '').trim().toLowerCase();
                    const items = Array.from(document.querySelectorAll('.module-item'));
                    let visible = 0;
                    const filtering = !!term;
                    setSortingEnabled(!filtering);

                    if (!term) {
                        items.forEach(it => it.classList.remove('hidden'));
                        if (noResults) noResults.style.display = 'none';
                        updateTopButtonsVisibility();
                        return;
                    }

                    items.forEach(item => {
                        const keywords = (item.getAttribute('data-keywords') || '').toLowerCase();
                        const title = (item.querySelector('.ui.large.black.label')?.textContent || '').toLowerCase();
                        const description = (item.querySelector('.description')?.textContent || '').toLowerCase();
                        const href = (item.getAttribute('href') || '').toLowerCase();
                        const match = keywords.includes(term) || title.includes(term) || description.includes(term) || href.includes(term);
                        item.classList.toggle('hidden', !match);
                        if (match) visible++;
                    });

                    if (noResults) noResults.style.display = (visible === 0 ? 'block' : 'none');
                    updateTopButtonsVisibility();
                }

                searchInput.addEventListener('input', performSearch);
                searchInput.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        performSearch();
                    }
                    if (e.key === 'Escape') {
                        searchInput.value = '';
                        performSearch();
                    }
                });
                clearButton.addEventListener('click', () => {
                    searchInput.value = '';
                    performSearch();
                    searchInput.focus();
                });
            });

            /* LAZY LOAD USERS */
            function loadUsers() {
                $.ajax({
                    url: '/users/components/activeusers.php', type: 'GET', dataType: 'html', success: (r) => {
                        $('#users').html(r);
                        observer.unobserve(usersPlaceholder);
                    }
                });
            }

            function handleIntersection(entries, observer) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        loadUsers();
                    }
                });
            }

            const options = {root: null, rootMargin: '0px', threshold: 0.01};
            const observer = new IntersectionObserver(handleIntersection, options);
            const usersPlaceholder = document.getElementById('users');
            observer.observe(usersPlaceholder);

            /* BEFOREUNLOAD BEACON */
            window.addEventListener('beforeunload', () => {
                const moduleOrder = localStorage.getItem(ORDER_KEY_NAME) || '[]';
                const favModules = localStorage.getItem(FAV_KEY_NAME) || '[]';
                if (navigator.sendBeacon) {
                    const body = new URLSearchParams({
                        module_order: moduleOrder,
                        fav_modules: favModules,
                        reason: 'beforeunload'
                    });
                    navigator.sendBeacon(SAVE_ENDPOINT, new Blob([body], {type: 'application/x-www-form-urlencoded'}));
                }
            });

            /* iOS FIX */
            (function () {
                const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) ||
                    (navigator.userAgent.includes('Mac') && navigator.maxTouchPoints > 1);

                if (isIOS) {
                    let vp = document.querySelector('meta[name="viewport"]');
                    if (!vp) {
                        vp = document.createElement('meta');
                        vp.name = 'viewport';
                        document.head.appendChild(vp);
                    }
                    const content = vp.getAttribute('content') || 'width=device-width, initial-scale=1';
                    const parts = new Set(content.split(',').map(s => s.trim()).filter(Boolean));
                    parts.add('maximum-scale=1');
                    parts.add('user-scalable=0');
                    parts.add('viewport-fit=cover');
                    vp.setAttribute('content', Array.from(parts).join(', '));
                }

                function computeSafeTop() {
                    let safe = 0;

                    const known = document.querySelector(
                        '#topbar, nav.topbar, .ui.menu.fixed, .ui.fixed.menu, header .fixed.menu, header .ui.menu, nav.fixed, header, nav'
                    );
                    if (known) {
                        const r = known.getBoundingClientRect();
                        if (r.bottom > safe) safe = Math.ceil(r.bottom);
                    }

                    const all = document.body.getElementsByTagName('*');
                    for (let i = 0; i < all.length; i++) {
                        const el = all[i];
                        if (el.id === 'searchFixed') continue;
                        const cs = window.getComputedStyle(el);
                        if (cs.position === 'fixed' || cs.position === 'sticky') {
                            const r = el.getBoundingClientRect();
                            if (r.top <= 0 && r.bottom > 0 && r.height > 32 && r.width > 120) {
                                if (r.bottom > safe) safe = Math.ceil(r.bottom);
                            }
                        }
                    }

                    document.documentElement.style.setProperty('--safe-top', safe + 'px');
                    updateSearchSpacerHeight();
                }

                document.addEventListener('DOMContentLoaded', computeSafeTop);
                window.addEventListener('load', computeSafeTop);
                window.addEventListener('resize', computeSafeTop);
                window.addEventListener('orientationchange', computeSafeTop);
                if (!isIOS) {
                    window.addEventListener('scroll', computeSafeTop);
                }
                setTimeout(computeSafeTop, 120);
                setTimeout(computeSafeTop, 450);
                setTimeout(computeSafeTop, 900);

                window.updateSearchSpacerHeight = function () {
                    const fixed = document.getElementById('searchFixed');
                    const sp = document.getElementById('searchSpacer');
                    if (fixed && sp) {
                        sp.style.height = (fixed.offsetHeight + 6) + 'px';
                    }
                };
            })();

            window.addEventListener("load", function () {
                const overlay = document.getElementById("loadingOverlay");
                if (overlay) {
                    overlay.style.opacity = "0";
                    setTimeout(() => overlay.remove(), 400);
                }
                if (typeof updateSearchSpacerHeight === 'function') updateSearchSpacerHeight();
            });
        </script>

        <script>
            $(document).ready(function() {
                // Dropdown functionality - FIXED
                const profileBtn = document.getElementById('profileDropdownBtn');
                const logoutDiv = document.getElementById('logout');
                const logoutDropdown = document.getElementById('logout2');
                const arrowSvg = document.getElementById('arrowSvg');

                // Toggle dropdown on arrow click
                profileBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    if (logoutDiv.style.display === 'none' || logoutDiv.style.display === '') {
                        // Show dropdown
                        const headerRight = document.querySelector('.header-right-profile');
                        const rect = headerRight.getBoundingClientRect();
                        
                        logoutDropdown.style.top = (rect.bottom + 10) + 'px';
                        logoutDropdown.style.left = (rect.right - 200) + 'px';
                        
                        logoutDiv.style.display = 'block';
                        arrowSvg.style.transform = 'rotate(180deg)';
                    } else {
                        // Hide dropdown
                        logoutDiv.style.display = 'none';
                        arrowSvg.style.transform = 'rotate(0deg)';
                    }
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!profileBtn.contains(e.target) && !logoutDiv.contains(e.target)) {
                        logoutDiv.style.display = 'none';
                        arrowSvg.style.transform = 'rotate(0deg)';
                    }
                });

                // Close on scroll
                window.addEventListener('scroll', function() {
                    if (logoutDiv.style.display !== 'none') {
                        logoutDiv.style.display = 'none';
                        arrowSvg.style.transform = 'rotate(0deg)';
                    }
                });

                // Stop AJAX and observer before logout
                const logoutLink = document.querySelector('.logoutAnchors');
                if (logoutLink) {
                    logoutLink.addEventListener('click', function(e) {
                        if (typeof observer !== 'undefined') {
                            observer.disconnect();
                        }
                        $.ajaxStop();
                    });
                }
            });
        </script>
    </body>
</html>
