function detectZoomLevel() {
    const zoomLevel = Math.round(window.devicePixelRatio * 100);
    return zoomLevel;
}

/**
 * Apply Zoom-Specific Adjustments
 */
function applyZoomAdjustments() {
    const zoom = detectZoomLevel();
    const root = document.documentElement;
    
    root.classList.remove('zoom-67', 'zoom-80', 'zoom-90', 'zoom-100', 'zoom-110', 'zoom-120');
    
    if (zoom <= 70) {
        root.classList.add('zoom-67');
        root.style.setProperty('--base-font-size', '11px');
        root.style.setProperty('--base-padding', '8px');
        root.style.setProperty('--base-column-width', '100px');
        root.style.setProperty('--base-name-width', '160px');
        root.style.setProperty('--base-id-width', '90px');
        root.style.setProperty('--base-hours-width', '70px');
    } else if (zoom <= 85) {
        root.classList.add('zoom-80');
        root.style.setProperty('--base-font-size', '12px');
        root.style.setProperty('--base-padding', '9px');
        root.style.setProperty('--base-column-width', '110px');
        root.style.setProperty('--base-name-width', '180px');
        root.style.setProperty('--base-id-width', '100px');
        root.style.setProperty('--base-hours-width', '75px');
    } else if (zoom <= 95) {
        root.classList.add('zoom-90');
        root.style.setProperty('--base-font-size', '12.5px');
        root.style.setProperty('--base-padding', '9px');
        root.style.setProperty('--base-column-width', '115px');
        root.style.setProperty('--base-name-width', '190px');
        root.style.setProperty('--base-id-width', '105px');
        root.style.setProperty('--base-hours-width', '80px');
    } else if (zoom <= 105) {
        root.classList.add('zoom-100');
        root.style.setProperty('--base-font-size', '13px');
        root.style.setProperty('--base-padding', '10px');
        root.style.setProperty('--base-column-width', '120px');
        root.style.setProperty('--base-name-width', '200px');
        root.style.setProperty('--base-id-width', '110px');
        root.style.setProperty('--base-hours-width', '77px');
    } else if (zoom <= 115) {
        root.classList.add('zoom-110');
        root.style.setProperty('--base-font-size', '12px');
        root.style.setProperty('--base-padding', '8px');
        root.style.setProperty('--base-column-width', '105px');
        root.style.setProperty('--base-name-width', '175px');
        root.style.setProperty('--base-id-width', '95px');
        root.style.setProperty('--base-hours-width', '70px');
    } else {
        root.classList.add('zoom-120');
        root.style.setProperty('--base-font-size', '11px');
        root.style.setProperty('--base-padding', '7px');
        root.style.setProperty('--base-column-width', '95px');
        root.style.setProperty('--base-name-width', '160px');
        root.style.setProperty('--base-id-width', '85px');
        root.style.setProperty('--base-hours-width', '65px');
    }
}

/**
 * Enhanced Responsive Table Layout Initialization
 */
function initializeResponsiveTableLayout() {
    applyZoomAdjustments();
    
    if (!$('#responsiveTableStyles').length) {
        $('head').append(`
            <style id="responsiveTableStyles">
                :root {
                    --siemens-teal: #00a9a9;
                    --siemens-text: #333;
                    --siemens-white: #fff;
                    --siemens-gray-light: #f5f5f5;
                    --siemens-gray-dark: #666;
                }
                
                .table-responsive-container {
                    width: 100%;
                    height: auto;
                    min-height: 400px;
                    max-height: calc(100vh - 320px);
                    overflow: hidden;
                    display: flex;
                    flex-direction: column;
                    background: #fff;
                    border-radius: 4px;
                    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                    position: relative;
                }
                
                .table-responsive-container .dataTables_wrapper {
                    height: 100%;
                    display: flex;
                    flex-direction: column;
                    position: relative;
                    overflow: visible;
                }
                
                .table-responsive-container .dataTables_top {
                    padding: calc(var(--base-padding) * 1.2) calc(var(--base-padding) * 1.5);
                    background: #f8f9fa;
                    border-bottom: 1px solid #e0e0e0;
                    flex-shrink: 0;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: calc(var(--base-padding) * 1.5);
                    flex-wrap: wrap;
                }
                
                .table-responsive-container .dataTables_length {
                    display: flex;
                    align-items: center;
                    gap: calc(var(--base-padding) * 0.8);
                    font-size: var(--base-font-size);
                    color: var(--siemens-text);
                    order: 1;
                }
                
                .table-responsive-container .dataTables_length label {
                    display: flex;
                    align-items: center;
                    gap: calc(var(--base-padding) * 0.8);
                    margin: 0;
                }
                
                .table-responsive-container .dataTables_length select {
                    padding: calc(var(--base-padding) * 0.6) var(--base-padding);
                    border: 1px solid #ddd;
                    border-radius: 4px;
                    background: #fff;
                    color: var(--siemens-text);
                    font-size: var(--base-font-size);
                    cursor: pointer;
                    transition: all 0.2s ease;
                    min-width: 60px;
                }
                
                .table-responsive-container .dataTables_filter {
                    display: flex;
                    align-items: center;
                    gap: calc(var(--base-padding) * 0.8);
                    order: 2;
                }
                
                .table-responsive-container .dataTables_filter label {
                    display: flex;
                    align-items: center;
                    gap: calc(var(--base-padding) * 0.8);
                    margin: 0;
                    font-size: var(--base-font-size);
                    color: var(--siemens-text);
                }
                
                .table-responsive-container .dataTables_filter input {
                    padding: calc(var(--base-padding) * 0.8) calc(var(--base-padding) * 1.2);
                    border: 1px solid #ddd;
                    border-radius: 4px;
                    font-size: var(--base-font-size);
                    min-width: calc(var(--base-name-width) - 20px);
                    transition: all 0.2s ease;
                }
                
                .table-responsive-container .dataTables_scroll {
                    flex: 1;
                    overflow: hidden;
                    position: relative;
                }
                
                .table-responsive-container .dataTables_scrollBody {
                    overflow-y: auto;
                    overflow-x: hidden;
                    height: 100%;
                    max-height: calc(100vh - 450px);
                }
                
                .table-responsive-container table {
                    width: 100%;
                    margin: 0;
                    border-collapse: collapse;
                    table-layout: fixed;
                    background: #fff;
                }
                
                .table-responsive-container thead {
                    position: sticky;
                    top: 0;
                    z-index: 100;
                    background: #f5f5f5;
                    display: table;
                    width: 100%;
                    table-layout: fixed;
                }
                
                .table-responsive-container thead tr {
                    display: table;
                    width: 100%;
                    table-layout: fixed;
                }
                
                .table-responsive-container thead th {
                    font-weight: 700;
                    font-size: 10px;
                    color: #333;
                    padding: 10px 8px !important;
                    text-align: center;
                    vertical-align: middle;
                    white-space: normal;
                    border: none !important;
                    border-right: 1px solid #ddd !important;
                    background: #f5f5f5 !important;
                    text-transform: uppercase;
                    letter-spacing: 0.3px;
                    line-height: 1.4;
                }
                
                .table-responsive-container thead th:last-child {
                    border-right: none !important;
                }
                
                .table-responsive-container tbody {
                    display: table;
                    width: 100%;
                    table-layout: fixed;
                }
                
                .table-responsive-container tbody tr {
                    display: table;
                    width: 100%;
                    table-layout: fixed;
                    border-bottom: 1px solid #e0e0e0;
                }
                
                .table-responsive-container tbody tr:hover {
                    background-color: #f9f9f9;
                }
                
                .table-responsive-container tbody tr.has-temp-transfer {
                    background-color: #f0f8f8;
                }
                
                .table-responsive-container tbody tr.transfer-last-day {
                    background-color: #fff3cd;
                }
                
                .table-responsive-container tbody td {
                    padding: var(--base-padding) calc(var(--base-padding) * 0.8);
                    font-size: var(--base-font-size);
                    color: #555;
                    border: 1px solid #e0e0e0;
                    text-align: center;
                    word-wrap: break-word;
                    overflow-wrap: break-word;
                    vertical-align: middle;
                    min-height: calc(var(--base-padding) * 4);
                    display: table-cell;
                }
                
                .col-employee-name {
                    width: var(--base-name-width);
                    min-width: var(--base-name-width);
                    max-width: var(--base-name-width);
                }
                
                .col-employee-id {
                    width: var(--base-id-width);
                    min-width: var(--base-id-width);
                    max-width: var(--base-id-width);
                }
                
                .col-man-hours {
                    width: var(--base-hours-width);
                    min-width: var(--base-hours-width);
                    max-width: var(--base-hours-width);
                }
                
                .col-date {
                    width: var(--base-column-width);
                    min-width: var(--base-column-width);
                    max-width: var(--base-column-width);
                }
                
                .table-responsive-container .dataTables_paginate {
                    padding: calc(var(--base-padding) * 1.2) calc(var(--base-padding) * 1.5);
                    background: #f8f9fa;
                    border-top: 1px solid #e0e0e0;
                    text-align: center;
                    flex-shrink: 0;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 5px;
                    flex-wrap: wrap;
                }
                
                .table-responsive-container .paginate_button {
                    padding: calc(var(--base-padding) * 0.8) calc(var(--base-padding) * 1.2);
                    margin: 0 2px;
                    border: 1px solid #ddd;
                    background: #fff;
                    color: #333;
                    cursor: pointer;
                    border-radius: 4px;
                    font-size: var(--base-font-size);
                    font-weight: 500;
                    transition: all 0.2s ease;
                    min-width: calc(var(--base-padding) * 4);
                    text-align: center;
                }
                
                .table-responsive-container .paginate_button.current {
                    background: var(--siemens-teal);
                    color: #fff;
                    border-color: var(--siemens-teal);
                    font-weight: 700;
                    box-shadow: 0 2px 8px rgba(0, 168, 168, 0.3);
                }
                
                .table-responsive-container .paginate_button:hover:not(.disabled) {
                    background: var(--siemens-teal);
                    color: #fff;
                    border-color: var(--siemens-teal);
                }
                
                .table-responsive-container .paginate_button.disabled {
                    opacity: 0.5;
                    cursor: not-allowed;
                }
                
                .table-responsive-container .dataTables_scrollBody::-webkit-scrollbar {
                    width: 8px;
                    height: 8px;
                }
                
                .table-responsive-container .dataTables_scrollBody::-webkit-scrollbar-track {
                    background: #f1f1f1;
                }
                
                .table-responsive-container .dataTables_scrollBody::-webkit-scrollbar-thumb {
                    background: #888;
                    border-radius: 4px;
                }
                
                .table-responsive-container .dataTables_scrollBody::-webkit-scrollbar-thumb:hover {
                    background: #555;
                }

                .transferred-to-cell {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    min-height: 45px;
                    background: #e0f7f6;
                    border: 1.5px solid #00a9a9;
                    border-radius: 3px;
                    padding: 4px 6px;
                }

                .transferred-badge {
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    gap: 2px;
                    text-align: center;
                }

                .transferred-label {
                    font-size: 6px;
                    font-weight: 600;
                    color: #00a9a9;
                    text-transform: uppercase;
                    letter-spacing: 0.3px;
                }

                .transferred-dept {
                    font-size: 8px;
                    font-weight: 700;
                    color: #00a9a9;
                    word-wrap: break-word;
                    max-width: 80px;
                    line-height: 1.1;
                }
                
                .employee-name-wrapper.has-active-transfer {
                    background: #e0f7f6;
                    padding: 8px;
                    border-radius: 4px;
                    border-left: 4px solid #00a9a9;
                }
                
                .emp-transfer-info {
                    font-size: 12px;
                    color: #00a9a9;
                    margin-top: 4px;
                    display: flex;
                    align-items: center;
                    gap: 6px;
                }
                
                .transfer-text {
                    font-weight: 600;
                }
                
                .transfer-duration {
                    font-size: 10px;
                    color: #0088a0;
                    margin-top: 2px;
                    font-style: italic;
                }
                
                .pagination-info {
                    font-size: var(--base-font-size);
                    color: var(--siemens-text);
                    padding: calc(var(--base-padding) * 0.8) 0;
                    text-align: center;
                    font-weight: 600;
                    background: #f0f8f8;
                    border-radius: 4px;
                    margin-bottom: 12px;
                }
                
                .pagination-info-text {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 8px;
                }
                
                .pagination-badge {
                    display: inline-block;
                    background: var(--siemens-teal);
                    color: white;
                    padding: 4px 8px;
                    border-radius: 3px;
                    font-size: calc(var(--base-font-size) * 0.85);
                    font-weight: 700;
                }

                /* ===== STATUS BADGE STYLES ===== */
                .status-badge {
                    display: inline-block;
                    padding: 4px 8px;
                    border-radius: 3px;
                    font-size: 11px;
                    font-weight: 600;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                }

                .status-badge.saved {
                    background: #FFF3CD;
                    color: #856404;
                    border: 1px solid #FFEAA7;
                }

                .status-badge.submitted {
                    background: #D4EDDA;
                    color: #155724;
                    border: 1px solid #C3E6CB;
                }

                .status-badge.updated {
                    background: #CCE5FF;
                    color: #004085;
                    border: 1px solid #B8DAFF;
                }

                .status-badge.incomplete {
                    background: #FFECB3;
                    color: #FF8F00;
                    border: 1px solid #FFD54F;
                }

                .status-badge.mixed {
                    background: #E1BEE7;
                    color: #6A1B9A;
                    border: 1px solid #CE93D8;
                }

                .date-header .button-group {
                    display: flex;
                    gap: 4px;
                    margin-top: 6px;
                    justify-content: center;
                    flex-wrap: nowrap;
                    position: relative;
                }

                .date-header button {
                    padding: 4px 6px;
                    font-size: 11px;
                    border: 1px solid #ddd;
                    background: #fff;
                    color: #333;
                    border-radius: 3px;
                    cursor: pointer;
                    transition: all 0.2s ease;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 2px;
                    position: relative;
                    min-width: 32px;
                    height: 28px;
                    flex-shrink: 0;
                }

                .date-header button i {
                    font-size: 13px;
                    line-height: 1;
                }

                .date-header button:hover:not(:disabled) {
                    background: var(--siemens-teal);
                    color: white;
                    border-color: var(--siemens-teal);
                }

                .date-header button:disabled {
                    opacity: 0.5;
                    cursor: not-allowed;
                }

                .button-tooltip {
                    display: none;
                    position: absolute;
                    background: #333;
                    color: white;
                    padding: 6px 10px;
                    border-radius: 3px;
                    font-size: 11px;
                    white-space: nowrap;
                    top: -36px;
                    left: 50%;
                    transform: translateX(-50%);
                    z-index: 1000;
                    font-weight: 600;
                    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
                }

                .button-tooltip::before {
                    content: '';
                    position: absolute;
                    bottom: -5px;
                    left: 50%;
                    transform: translateX(-50%);
                    border: 5px solid transparent;
                    border-top-color: #333;
                }

                .date-header button:hover .button-tooltip {
                    display: block;
                }

                .save-date-btn.saved,
                .submit-date-btn.submitted,
                .update-date-btn.updated,
                .save-overtime-date-btn.saved,
                .submit-overtime-date-btn.submitted,
                .update-overtime-date-btn.updated {
                    background: #C8E6C9;
                    border-color: #388E3C;
                    color: #1B5E20;
                }

                /* ===== FUTURE DATE DISABLED STYLES ===== */
                .future-date-cell {
                    opacity: 0.6;
                    background: #f5f5f5 !important;
                    cursor: not-allowed;
                }

                .attendance-select.future-disabled,
                .overtime-input.future-disabled {
                    background-color: #E8E8E8 !important;
                    color: #999 !important;
                    cursor: not-allowed !important;
                    opacity: 0.6 !important;
                    border-color: #CCCCCC !important;
                    pointer-events: none !important;
                }

                .attendance-select.future-disabled:hover,
                .overtime-input.future-disabled:hover {
                    background-color: #E8E8E8 !important;
                    border-color: #CCCCCC !important;
                }

                .future-date {
                    opacity: 0.6;
                    background: #f5f5f5 !important;
                }

                .future-date button {
                    opacity: 0.5;
                    cursor: not-allowed !important;
                    background: #E8E8E8 !important;
                    color: #999 !important;
                    border-color: #CCCCCC !important;
                }

                .future-date button:hover {
                    background: #E8E8E8 !important;
                    border-color: #CCCCCC !important;
                }

                /* ===== GREYED OUT SUBMITTED DATA STYLES ===== */
                .attendance-select.dept-submitted,
                .overtime-input.dept-submitted {
                    background-color: #E8E8E8 !important;
                    color: #999 !important;
                    cursor: not-allowed !important;
                    opacity: 0.6 !important;
                    border-color: #CCCCCC !important;
                }

                .attendance-select.dept-submitted:hover,
                .overtime-input.dept-submitted:hover {
                    background-color: #E8E8E8 !important;
                    border-color: #CCCCCC !important;
                }

                .attendance-select.dept-submitted option,
                .overtime-input.dept-submitted option {
                    color: #999;
                }

                /* ===== N/A CELL WITH TRANSFER INFO STYLES ===== */
                .not-in-dept-cell {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    height: 100%;
                    gap: 8px;
                    padding: 8px;
                    background: #f9f9f9;
                    border-radius: 4px;
                    position: relative;
                }

                .not-in-dept-text {
                    font-weight: 600;
                    color: #999;
                    font-size: 13px;
                    letter-spacing: 0.5px;
                }

                .transfer-info-icon-wrapper {
                    position: relative;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    cursor: help;
                }

                .transfer-info-icon-wrapper i {
                    font-size: 14px;
                    color: var(--siemens-teal);
                    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    width: 18px;
                    height: 18px;
                    border-radius: 50%;
                    background: rgba(0, 169, 169, 0.08);
                }

                .transfer-info-icon-wrapper:hover i {
                    font-size: 15px;
                    color: #0088a0;
                    background: rgba(0, 169, 169, 0.15);
                    text-shadow: 0 0 8px rgba(0, 169, 169, 0.3);
                    transform: scale(1.1);
                }

                .transfer-info-tooltip {
                    display: none;
                    position: absolute;
                    background: white;
                    color: #333;
                    padding: 0;
                    border-radius: 8px;
                    font-size: 12px;
                    white-space: normal;
                    bottom: 100%;
                    left: 50%;
                    transform: translateX(-50%);
                    z-index: 10000;
                    font-weight: 500;
                    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
                    border: 2px solid var(--siemens-teal);
                    margin-bottom: 12px;
                    min-width: 320px;
                    max-width: 340px;
                    overflow: hidden;
                    animation: tooltipFadeInUp 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
                }

                .transfer-info-tooltip::before {
                    content: '';
                    position: absolute;
                    bottom: -10px;
                    left: 50%;
                    transform: translateX(-50%);
                    width: 0;
                    height: 0;
                    border-left: 10px solid transparent;
                    border-right: 10px solid transparent;
                    border-top: 10px solid var(--siemens-teal);
                    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
                }

                .transfer-info-icon-wrapper:hover .transfer-info-tooltip {
                    display: block;
                }

                .transfer-info-tooltip-header {
                    background: linear-gradient(135deg, var(--siemens-teal) 0%, #0088a0 100%);
                    color: white;
                    padding: 12px 16px;
                    font-weight: 700;
                    font-size: 13px;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }

                .transfer-info-tooltip-content {
                    padding: 14px 16px;
                    display: flex;
                    flex-direction: column;
                    gap: 10px;
                }

                .transfer-info-tooltip-row {
                    display: flex;
                    gap: 12px;
                    align-items: flex-start;
                    padding-bottom: 12px;
                    border-bottom: 1px solid #e0e0e0;
                }

                .transfer-info-tooltip-row:last-child {
                    padding-bottom: 0;
                    border-bottom: none;
                }

                .transfer-info-tooltip-label {
                    font-weight: 700;
                    color: var(--siemens-teal);
                    min-width: 80px;
                    font-size: 11px;
                    text-transform: uppercase;
                    letter-spacing: 0.3px;
                    flex-shrink: 0;
                }

                .transfer-info-tooltip-value {
                    color: #333;
                    font-size: 12px;
                    font-weight: 600;
                    word-break: break-word;
                    flex: 1;
                }

                .transfer-info-tooltip-value.status {
                    color: #FF8F00;
                    font-weight: 700;
                    font-size: 12px;
                    background: #FFF3E0;
                    padding: 4px 8px;
                    border-radius: 4px;
                    display: inline-block;
                    border-left: 3px solid #FF8F00;
                }

                @keyframes tooltipFadeInUp {
                    from {
                        opacity: 0;
                        transform: translateX(-50%) translateY(8px);
                        visibility: hidden;
                    }
                    to {
                        opacity: 1;
                        transform: translateX(-50%) translateY(0);
                        visibility: visible;
                    }
                }

                @keyframes pulse-error {
                    0%, 100% {
                        box-shadow: 0 0 0 0 rgba(255, 107, 107, 0.7);
                    }
                    50% {
                        box-shadow: 0 0 0 8px rgba(255, 107, 107, 0);
                    }
                }

                /* Unfilled entry indicator */
                .unfilled-entry {
                    background: #FFE5E5 !important;
                    border: 2px solid #FF6B6B !important;
                    border-radius: 4px !important;
                    animation: pulse-error 1.5s infinite;
                }

                .unfilled-entry::after {
                    content: '⚠️';
                    position: absolute;
                    top: -8px;
                    right: -8px;
                    background: #FF6B6B;
                    color: white;
                    width: 20px;
                    height: 20px;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 12px;
                    font-weight: bold;
                }

                /* Filled entry indicator */
                .filled-entry {
                    background: #E8F5E9 !important;
                    border: 2px solid #4CAF50 !important;
                    border-radius: 4px !important;
                }

                .filled-entry::after {
                    content: '✓';
                    position: absolute;
                    top: -8px;
                    right: -8px;
                    background: #4CAF50;
                    color: white;
                    width: 20px;
                    height: 20px;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 12px;
                    font-weight: bold;
                }

                /* Alert modal styles */
                #customAlertModal .alert-list {
                    max-height: 300px;
                    overflow-y: auto;
                    background: #f5f5f5;
                    border-radius: 4px;
                    padding: 12px;
                    margin: 12px 0;
                }

                #customAlertModal .alert-list li {
                    padding: 6px 8px;
                    margin: 4px 0;
                    background: white;
                    border-left: 3px solid #FF6B6B;
                    border-radius: 2px;
                    font-size: 12px;
                    color: #333;
                }

                #customAlertModal .alert-list li:hover {
                    background: #FFF5F5;
                }

                /* Validation message styles */
                .validation-message {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    padding: 10px 12px;
                    background: #FFE5E5;
                    border: 1px solid #FF6B6B;
                    border-radius: 4px;
                    color: #C62828;
                    font-weight: 500;
                    margin: 10px 0;
                }

                .validation-message i {
                    font-size: 16px;
                }

                .validation-success {
                    background: #E8F5E9;
                    border-color: #4CAF50;
                    color: #2E7D32;
                }

                .validation-success i {
                    color: #4CAF50;
                }

                /* Mobile responsive */
                @media (max-width: 768px) {
                    .transfer-info-tooltip {
                        font-size: 11px;
                        min-width: 260px;
                        max-width: 300px;
                    }
                    
                    .transfer-info-tooltip-label {
                        min-width: 65px;
                        font-size: 10px;
                    }
                    
                    .transfer-info-tooltip-value {
                        font-size: 11px;
                    }
                }

                /* Tablet responsive */
                @media (max-width: 1024px) {
                    .transfer-info-tooltip {
                        min-width: 270px;
                        max-width: 310px;
                    }
                }
            </style>
        `);
    }
}

function applyResponsiveTableLayout(isOvertime = false) {
    const tableId = isOvertime ? '#table_overtime_items' : '#table_all_items';
    const $table = $(tableId);
    const $container = $table.closest('.table-responsive-container');
    
    if ($container.length === 0) {
        return;
    }
    
    applyZoomAdjustments();
    
    $table.css({
        'width': '100%',
        'table-layout': 'fixed',
        'border-collapse': 'collapse',
        'margin': '0',
        'padding': '0'
    });
    
    const root = document.documentElement;
    const nameWidth = getComputedStyle(root).getPropertyValue('--base-name-width').trim();
    const idWidth = getComputedStyle(root).getPropertyValue('--base-id-width').trim();
    const hoursWidth = getComputedStyle(root).getPropertyValue('--base-hours-width').trim();
    const columnWidth = getComputedStyle(root).getPropertyValue('--base-column-width').trim();
    
    $table.find('thead th, tbody td').each(function(index) {
        const $cell = $(this);
        
        if (index === 0) {
            $cell.addClass('col-employee-name').css({
                'width': nameWidth,
                'min-width': nameWidth,
                'max-width': nameWidth
            });
        } else if (index === 1) {
            $cell.addClass('col-employee-id').css({
                'width': idWidth,
                'min-width': idWidth,
                'max-width': idWidth
            });
        } else if (index === 2) {
            $cell.addClass('col-man-hours').css({
                'width': hoursWidth,
                'min-width': hoursWidth,
                'max-width': hoursWidth
            });
        } else {
            $cell.addClass('col-date').css({
                'width': columnWidth,
                'min-width': columnWidth,
                'max-width': columnWidth,
                'overflow': 'hidden',
                'text-overflow': 'ellipsis',
                'word-wrap': 'break-word'
            });
        }
    });
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function handleTableResize() {
    let resizeTimeout;
    
    const resizeHandler = function() {
        clearTimeout(resizeTimeout);
        
        resizeTimeout = setTimeout(function() {
            applyZoomAdjustments();
            applyResponsiveTableLayout(false);
            applyResponsiveTableLayout(true);
            
            if (dataTable) {
                dataTable.columns.adjust().draw();
            }
            if (overtimeDataTable) {
                overtimeDataTable.columns.adjust().draw();
            }
        }, 300);
    };
    
    $(window).on('resize', resizeHandler);
    
    const zoomMediaQuery = window.matchMedia('(min-resolution: 0.001dpcm)');
    if (zoomMediaQuery.addEventListener) {
        zoomMediaQuery.addEventListener('change', resizeHandler);
    }
}

// ===== CUSTOM ALERT FUNCTION =====
function showCustomAlert(options) {
    const {
        type = 'info',
        title = 'Information',
        message = '',
        items = [],
        note = '',
        onOk = null
    } = options;

    const $modal = $('#customAlertModal');
    const $header = $('#alertHeader');
    const $icon = $('#alertIcon');
    const $titleEl = $('#alertTitle');
    const $messageEl = $('#alertMessage');
    const $listEl = $('#alertList');
    const $noteEl = $('#alertNote');
    const $okBtn = $('#alertOkBtn');

    $header.removeClass('error warning success info');
    $header.addClass(type);

    const iconMap = {
        'error': '<i class="fa fa-exclamation-circle"></i>',
        'warning': '<i class="fa fa-exclamation-triangle"></i>',
        'success': '<i class="fa fa-check-circle"></i>',
        'info': '<i class="fa fa-info-circle"></i>'
    };
    $icon.html(iconMap[type] || iconMap['info']);

    $titleEl.text(title);
    $messageEl.text(message);

    if (items && items.length > 0) {
        $listEl.empty();
        
        items.forEach((item) => {
            const itemText = typeof item === 'string' ? item : 
                           (item.name && item.gid ? `${item.name} (ID:${item.gid})` : JSON.stringify(item));
            
            if (itemText.includes('📄 PAGE')) {
                $listEl.append(`<li data-page-header="true" style="font-weight: 600; color: var(--siemens-teal); border-left-color: var(--siemens-teal);">${itemText}</li>`);
            } else {
                $listEl.append(`<li data-employee="true"><span>${itemText}</span></li>`);
            }
        });
        
        $listEl.show();
    } else {
        $listEl.hide();
    }

    if (note) {
        $noteEl.html(note);
        $noteEl.show();
    } else {
        $noteEl.hide();
    }

    $okBtn.off('click').on('click', function() {
        $modal.removeClass('show');
        if (typeof onOk === 'function') {
            onOk();
        }
    });

    $modal.addClass('show');

    $modal.off('click').on('click', function(e) {
        if (e.target === this) {
            $(this).removeClass('show');
        }
    });

    $(document).off('keydown.customAlert').on('keydown.customAlert', function(e) {
        if (e.key === 'Escape') {
            $modal.removeClass('show');
        }
    });
}

function showLoadingIndicator(show = true) {
    if (show) {
        $('#mai_spinner_page').addClass('active');
        $('.custom-dropdown-menu').removeClass('show');
        $('.custom-dropdown-toggle').removeClass('active');
    } else {
        $('#mai_spinner_page').removeClass('active');
    }
}

// ===== TRANSFER FUNCTIONS =====

function isTransferActive(employee) {
    if (!employee.temp_sub_department || !employee.transfer_from_date || !employee.transfer_to_date ||
        employee.temp_sub_department === '' || employee.transfer_from_date === '' || employee.transfer_to_date === '') {
        return false;
    }
    
    const today = moment().startOf('day');
    const transferEnd = moment(employee.transfer_to_date, 'YYYY-MM-DD').startOf('day');
    
    return today.isSameOrBefore(transferEnd);
}

function formatTempAssignmentInfo(employee) {
    if (!isTransferActive(employee)) {
        return null;
    }
    
    return {
        permanent_dept: employee.sub_department,
        temp_dept: employee.temp_sub_department,
        transfer_from: moment(employee.transfer_from_date, 'YYYY-MM-DD').format('MMM DD, YYYY'),
        transfer_to: moment(employee.transfer_to_date, 'YYYY-MM-DD').format('MMM DD, YYYY'),
        from_date: employee.transfer_from_date,
        to_date: employee.transfer_to_date,
        days_remaining: calculateDaysRemaining(employee.transfer_to_date),
        isInTransferPeriod: true
    };
}

function calculateDaysRemaining(endDate) {
    const today = moment().startOf('day');
    const end = moment(endDate, 'YYYY-MM-DD').startOf('day');
    const daysLeft = end.diff(today, 'days');
    
    if (daysLeft < 0) {
        return null;
    }
    
    if (daysLeft === 0) {
        return { remaining: 0, status: 'today', text: 'Last Day Today' };
    } else {
        return { remaining: daysLeft, status: 'active', text: `${daysLeft} days remaining` };
    }
}

function isEmployeeInTransferredDeptOnDate(employee, dateStr) {
    if (!employee.temp_sub_department || !employee.transfer_from_date || !employee.transfer_to_date) {
        return false;
    }
    
    const checkDate = moment(dateStr, 'YYYY-MM-DD');
    const transferStart = moment(employee.transfer_from_date, 'YYYY-MM-DD');
    const transferEnd = moment(employee.transfer_to_date, 'YYYY-MM-DD');
    
    return checkDate.isSameOrAfter(transferStart, 'day') && checkDate.isSameOrBefore(transferEnd, 'day');
}

function collectCurrentPageAttendanceData(dateStr) {
    const dataArray = [];
    const missingData = [];
    const transferredEmployees = [];
    const skippedSubmittedData = [];
    
    if (!dataTable) {
        console.error('❌ DataTable not initialized');
        return { data: dataArray, missing: missingData, transferred: transferredEmployees, skipped: skippedSubmittedData };
    }
    
    const pageData = dataTable.rows({ page: 'current' }).data();
    const currentPageInfo = dataTable.page.info();
    const pageNum = currentPageInfo.page + 1;
    const totalPages = currentPageInfo.pages;
    
    console.log('🔍 Collecting attendance data for:', dateStr);
    console.log('📄 Page:', pageNum, 'of', totalPages);
    console.log('👥 Employees on page:', pageData.length);
    
    pageData.each(function(rowData, index) {
        const gid = rowData.gid;
        const name = rowData.name;
        
        // ===== CHECK IF EMPLOYEE IS TRANSFERRED ON THIS DATE =====
        const isTransferred = isEmployeeInTransferredDeptOnDate(rowData, dateStr);
        
        if (isTransferred) {
            console.log(`⏭️ Employee ${gid}: TRANSFERRED - skipping`);
            transferredEmployees.push({
                gid: gid,
                name: name,
                date: dateStr,
                from_dept: rowData.sub_department,
                to_dept: rowData.temp_sub_department,
                page: pageNum,
                totalPages: totalPages
            });
            return;
        }
        
        // Get the select element
        const $select = $(`select.attendance-select[data-gid="${gid}"][data-date="${dateStr}"]`);
        
        if ($select.length === 0) {
            console.log(`⚠️ Employee ${gid}: SELECT NOT FOUND`);
            return;
        }
        
        // ===== CHECK IF GREYED OUT (TRULY SUBMITTED - status: 'sub') =====
        const isGreyedOut = $select.hasClass('dept-submitted');
        const currentValue = $select.val();
        
        console.log(`🔍 Employee ${gid}:`, {
            isGreyedOut: isGreyedOut,
            currentValue: currentValue,
            hasLiveData: !!(liveAttendanceData[gid] && liveAttendanceData[gid][dateStr])
        });
        
        if (isGreyedOut) {
            console.log(`🔒 Employee ${gid}: GREYED OUT (Already Submitted) - SKIPPING`);
            skippedSubmittedData.push({
                gid: gid,
                name: name,
                status: currentValue,
                reason: 'Already submitted - greyed out'
            });
            return;
        }
        
        // ===== NOT GREYED OUT - CHECK IF HAS NEW DATA OR SAVED DATA =====
        let hasData = false;
        let attendanceStatus = null;
        
        // Check live data (newly entered - highest priority)
        if (liveAttendanceData[gid] && liveAttendanceData[gid][dateStr]) {
            attendanceStatus = liveAttendanceData[gid][dateStr];
            hasData = true;
            console.log(`✅ Employee ${gid}: HAS NEW LIVE DATA - ${attendanceStatus}`);
        }
        
        // Check cached data (previously saved - status: 'save' or status: 'sub' but not greyed out)
        if (!hasData && attendanceCachedData && Array.isArray(attendanceCachedData)) {
            const cachedRecord = attendanceCachedData.find(record => 
                record.gid === gid && record.attendance_date === dateStr
            );
            
            if (cachedRecord && cachedRecord.attendance_status) {
                attendanceStatus = cachedRecord.attendance_status;
                hasData = true;
                console.log(`✅ Employee ${gid}: HAS CACHED DATA (status: ${cachedRecord.status}) - ${attendanceStatus}`);
            }
        }
        
        // Check allocated shift
        if (!hasData) {
            const allocatedShift = rowData.allocated_shift || null;
            if (allocatedShift) {
                attendanceStatus = allocatedShift;
                hasData = true;
                console.log(`✅ Employee ${gid}: HAS ALLOCATED SHIFT - ${attendanceStatus}`);
            }
        }
        
        // Check current form value (if not greyed out)
        if (!hasData && currentValue && currentValue !== '') {
            attendanceStatus = currentValue;
            hasData = true;
            console.log(`✅ Employee ${gid}: HAS FORM INPUT - ${attendanceStatus}`);
        }
        
        if (hasData && attendanceStatus && attendanceStatus !== '') {
            const hoursForDatabase = getAttendanceHours(attendanceStatus, dateStr, rowData.employment_type);
            
            dataArray.push({
                employee_name: name,
                gid: gid,
                date: dateStr,
                attendance_status: attendanceStatus,
                actual_man_hours: hoursForDatabase,
                allocated_shift: rowData.allocated_shift || null,
                is_allocated: rowData.allocated_shift && !liveAttendanceData[gid]?.[dateStr] ? 1 : 0
            });
            console.log(`✅ INCLUDED in submission: ${gid} - ${name} - ${attendanceStatus}`);
        } else {
            missingData.push({
                gid: gid,
                name: name,
                date: dateStr,
                reason: 'No attendance status selected',
                page: pageNum,
                totalPages: totalPages
            });
            console.log(`⚠️ MISSING DATA: ${gid} - ${name}`);
        }
    });
    
    console.log('📊 Final collected data:', {
        included: dataArray.length,
        missing: missingData.length,
        transferred: transferredEmployees.length,
        skipped_submitted: skippedSubmittedData.length
    });
    
    return { 
        data: dataArray, 
        missing: missingData, 
        transferred: transferredEmployees,
        skipped: skippedSubmittedData
    };
}

function validateEmployeeRelevanceForDate(employee, dateStr, selectedDepartments) {
    if (!selectedDepartments || selectedDepartments.length === 0) {
        return true;  // No filter, all employees relevant
    }
    
    const isInTransferPeriod = isEmployeeInTransferredDeptOnDate(employee, dateStr);
    
    if (isInTransferPeriod && employee.temp_sub_department) {
        // Employee is in temp dept on this date
        return selectedDepartments.includes(employee.temp_sub_department);
    } else {
        // Employee is in permanent dept on this date
        return selectedDepartments.includes(employee.sub_department);
    }
}

function collectCurrentPageOvertimeData(dateStr) {
    const dataArray = [];
    const noAttendanceData = [];
    const transferredEmployees = [];
    const skippedSubmittedData = [];
    
    if (!overtimeDataTable) {
        console.error('❌ overtimeDataTable not initialized');
        return { 
            data: dataArray, 
            noAttendance: noAttendanceData, 
            transferred: transferredEmployees,
            skipped: skippedSubmittedData
        };
    }
    
    const pageData = overtimeDataTable.rows({ page: 'current' }).data();
    const currentPageInfo = overtimeDataTable.page.info();
    const pageNum = currentPageInfo.page + 1;
    const totalPages = currentPageInfo.pages;
    
    console.log('🔍 Collecting overtime data for:', dateStr);
    console.log('📄 Page:', pageNum, 'of', totalPages);
    console.log('👥 Employees on page:', pageData.length);
    
    const holidays = window.holidaysData || cachedHolidaysData || {};
    const isHoliday = holidays[dateStr] === 'holiday';
    
    console.log('📅 Is Holiday:', isHoliday);
    
    pageData.each(function(rowData, index) {
        const gid = rowData.gid;
        const name = rowData.name;
        
        console.log(`\n👤 Processing Employee ${index + 1}/${pageData.length}: ${gid} - ${name}`);
        
        // Check if employee is transferred on this date
        const isTransferred = isEmployeeInTransferredDeptOnDate(rowData, dateStr);
        
        if (isTransferred) {
            console.log(`⏭️ Employee ${gid}: TRANSFERRED - skipping`);
            transferredEmployees.push({
                gid: gid,
                name: name,
                date: dateStr,
                from_dept: rowData.sub_department,
                to_dept: rowData.temp_sub_department,
                page: pageNum,
                totalPages: totalPages
            });
            return;
        }
        
        // Get the input element
        const $input = $(`input.overtime-input[data-gid="${gid}"][data-date="${dateStr}"]`);
        
        console.log(`🔍 Looking for input: input.overtime-input[data-gid="${gid}"][data-date="${dateStr}"]`);
        console.log(`📍 Input found: ${$input.length > 0 ? 'YES' : 'NO'}`);
        
        if ($input.length === 0) {
            console.log(`⚠️ Employee ${gid}: INPUT NOT FOUND`);
            return;
        }
        
        // ===== CHECK IF GREYED OUT (TRULY SUBMITTED - status: 'sub') =====
        const isGreyedOut = $input.hasClass('dept-submitted');
        const currentValue = $input.val();
        
        console.log(`🔍 Input Details:`, {
            isGreyedOut: isGreyedOut,
            currentValue: currentValue,
            hasClass_dept_submitted: $input.hasClass('dept-submitted'),
            hasLiveData: !!(liveOvertimeData[gid] && liveOvertimeData[gid][dateStr] !== undefined)
        });
        
        if (isGreyedOut) {
            console.log(`🔒 Employee ${gid}: GREYED OUT (Already Submitted) - SKIPPING`);
            skippedSubmittedData.push({
                gid: gid,
                name: name,
                overtime_hours: currentValue || 0,
                reason: 'Already submitted - greyed out'
            });
            return;
        }
        
        // ===== COLLECT DATA - INCLUDE EMPTY VALUES =====
        let overtimeHours = null;
        
        // Check live overtime data (newly entered - highest priority)
        if (liveOvertimeData[gid] && liveOvertimeData[gid][dateStr] !== undefined) {
            overtimeHours = parseFloat(liveOvertimeData[gid][dateStr]) || 0;
            console.log(`✅ Employee ${gid}: HAS LIVE OVERTIME DATA - ${overtimeHours}`);
        }
        
        // Check cached overtime data (previously saved)
        if (overtimeHours === null && overtimeCachedData && Array.isArray(overtimeCachedData)) {
            const cachedRecord = overtimeCachedData.find(record => 
                record.gid === gid && (record.date === dateStr || (record.date && record.date.split('T')[0] === dateStr))
            );
            
            if (cachedRecord && cachedRecord.overtime_hours !== null && cachedRecord.overtime_hours !== undefined) {
                overtimeHours = parseFloat(cachedRecord.overtime_hours) || 0;
                console.log(`✅ Employee ${gid}: HAS CACHED OVERTIME DATA - ${overtimeHours}`);
            }
        }
        
        // Check current form value (if not greyed out)
        if (overtimeHours === null && currentValue !== '' && currentValue !== null && currentValue !== undefined) {
            overtimeHours = parseFloat(currentValue) || 0;
            console.log(`✅ Employee ${gid}: HAS FORM INPUT - ${overtimeHours}`);
        }
        
        // ===== INCLUDE RECORD WITH HOURS (EVEN IF 0) =====
        if (overtimeHours !== null) {
            dataArray.push({
                employee_name: name,
                gid: gid,
                date: dateStr,
                overtime_hours: overtimeHours,
                is_holiday: isHoliday ? 1 : 0
            });
            console.log(`✅ INCLUDED: ${gid} - ${name} - ${overtimeHours} hours`);
        } else {
            // ===== INCLUDE EMPTY ENTRIES AS 0 =====
            dataArray.push({
                employee_name: name,
                gid: gid,
                date: dateStr,
                overtime_hours: 0,
                is_holiday: isHoliday ? 1 : 0
            });
            console.log(`✅ INCLUDED (zero): ${gid} - ${name} - 0 hours`);
        }
    });
    
    console.log('\n📊 Final collected overtime data:', {
        included: dataArray.length,
        transferred: transferredEmployees.length,
        skipped_submitted: skippedSubmittedData.length
    });
    console.log('📋 Data Array:', dataArray);
    
    return { 
        data: dataArray, 
        noAttendance: noAttendanceData, 
        transferred: transferredEmployees,
        skipped: skippedSubmittedData
    };
}

// ===== HELPER FUNCTIONS =====

function isUserAdmin() {
    return typeof USER_IS_ADMIN !== 'undefined' && USER_IS_ADMIN === 1;
}

function isUserSupervisor() {
    return typeof USER_IS_SUPERVISOR !== 'undefined' && USER_IS_SUPERVISOR === 1;
}

function isUserRegularUser() {
    return typeof USER_IS_REGULAR !== 'undefined' && USER_IS_REGULAR === 1;
}

function getSupervisorId() {
    return typeof SUPERVISOR_ID !== 'undefined' ? SUPERVISOR_ID : null;
}

function isFutureDate(dateStr) {
    const today = moment().startOf('day');
    const checkDate = moment(dateStr, 'YYYY-MM-DD').startOf('day');
    return checkDate.isAfter(today);
}

function showInfoMessage(message) {
    if (currentInfoMessage === message) {
        return;
    }
    
    $('#customInfoBox').remove();
    currentInfoMessage = message;
    
    $('body').append(`
        <div id="customInfoBox" style="position: fixed; top: 20px; right: 20px; z-index: 10000; background: var(--siemens-white); border: 2px solid var(--siemens-teal); border-radius: 4px; padding: 16px 20px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); max-width: 400px; animation: slideInRight 0.3s ease;">
            <span class="close-info" onclick="$('#customInfoBox').fadeOut(function() { currentInfoMessage = null; $(this).remove(); })" style="position: absolute; top: 8px; right: 12px; font-size: 24px; font-weight: bold; color: var(--siemens-gray-dark); cursor: pointer; line-height: 1;">×</span>
            <strong style="color: var(--siemens-teal); font-size: 16px; display: block; margin-bottom: 8px;">ℹ️ Information</strong>
            <div id="infoBoxMessage" style="color: var(--siemens-text); font-size: 14px; line-height: 1.5;">${message}</div>
        </div>
    `);
    
    if (!$('#infoBoxAnimation').length) {
        $('head').append(`
            <style id="infoBoxAnimation">
                @keyframes slideInRight {
                    from {
                        transform: translateX(100%);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(0);
                        opacity: 1;
                    }
                }
            </style>
        `);
    }
    
    $('#customInfoBox').fadeIn();
    
    setTimeout(function() {
        $('#customInfoBox').fadeOut(function() {
            currentInfoMessage = null;
            $(this).remove();
        });
    }, 5000);
}

function initializeCustomMultiSelect(config) {
    const {
        wrapperId,
        toggleId,
        menuId,
        optionsId,
        labelId,
        hiddenInputId,
        options
    } = config;

    const $wrapper = $(`#${wrapperId}`);
    const $toggle = $(`#${toggleId}`);
    const $menu = $(`#${menuId}`);
    const $optionsContainer = $(`#${optionsId}`);
    const $label = $(`#${labelId}`);
    const $hiddenInput = $(`#${hiddenInputId}`);

    let selectedValues = [];

    function populateOptions() {
        $optionsContainer.empty();
        $optionsContainer.append(`
            <div class="custom-dropdown-option select-all">
                <input type="checkbox" id="${optionsId}_selectAll">
                <label for="${optionsId}_selectAll">Select All</label>
            </div>
        `);
        options.forEach((option, index) => {
            $optionsContainer.append(`
                <div class="custom-dropdown-option">
                    <input type="checkbox" id="${optionsId}_${index}" value="${option}">
                    <label for="${optionsId}_${index}">${option}</label>
                </div>
            `);
        });
    }

    function updateLabel() {
        $label.empty();
        if (selectedValues.length === 0) {
            $label.addClass('empty');
            $hiddenInput.val('[]');
        } else {
            $label.removeClass('empty');
            selectedValues.forEach(value => {
                $label.append(`
                    <span class="selected-tag">
                        ${value}
                        <span class="remove-tag" data-value="${value}">×</span>
                    </span>
                `);
            });
            $hiddenInput.val(JSON.stringify(selectedValues));
        }
        $hiddenInput.trigger('change');
    }

    $toggle.on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        $('.custom-dropdown-menu').not($menu).removeClass('show');
        $('.custom-dropdown-toggle').not($toggle).removeClass('active');
        
        const isShowing = $menu.hasClass('show');
        if (isShowing) {
            $menu.removeClass('show');
            $toggle.removeClass('active');
        } else {
            $menu.addClass('show');
            $toggle.addClass('active');
        }
    });

    $optionsContainer.on('change', '.custom-dropdown-option:not(.select-all) input[type="checkbox"]', function() {
        const $checkbox = $(this);
        const value = $checkbox.val();
        const isChecked = $checkbox.prop('checked');
        
        if (isChecked) {
            if (!selectedValues.includes(value)) {
                selectedValues.push(value);
            }
        } else {
            selectedValues = selectedValues.filter(v => v !== value);
        }
        
        updateLabel();
        updateSelectAllCheckbox();
    });

    $optionsContainer.on('change', '.select-all input[type="checkbox"]', function() {
        const isChecked = $(this).prop('checked');
        if (isChecked) {
            selectedValues = [...options];
            $optionsContainer.find('input[type="checkbox"]').prop('checked', true);
        } else {
            selectedValues = [];
            $optionsContainer.find('input[type="checkbox"]').prop('checked', false);
        }
        updateLabel();
    });

    function updateSelectAllCheckbox() {
        const $selectAllCheckbox = $optionsContainer.find('.select-all input[type="checkbox"]');
        $selectAllCheckbox.prop('checked', selectedValues.length === options.length);
    }

    $label.on('click', '.remove-tag', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const value = $(this).data('value');
        selectedValues = selectedValues.filter(v => v !== value);
        $optionsContainer.find(`input[value="${value}"]`).prop('checked', false);
        updateLabel();
        updateSelectAllCheckbox();
    });

    $(document).on('click.dropdown_' + hiddenInputId, function(e) {
        if (!$wrapper.is(e.target) && $wrapper.has(e.target).length === 0) {
            $menu.removeClass('show');
            $toggle.removeClass('active');
        }
    });

    populateOptions();
    updateLabel();

    return {
        getSelectedValues: () => selectedValues,
        setSelectedValues: (values) => {
            selectedValues = values;
            $optionsContainer.find('input[type="checkbox"]').prop('checked', false);
            values.forEach(value => {
                $optionsContainer.find(`input[value="${value}"]`).prop('checked', true);
            });
            updateLabel();
            updateSelectAllCheckbox();
        },
        destroy: () => {
            $(document).off('click.dropdown_' + hiddenInputId);
        }
    };
}

function initializeAllCustomDropdowns() {
    attendanceDeptDropdown = initializeCustomMultiSelect({
        wrapperId: 'deptDropdownAttendance',
        toggleId: 'sub_departmentToggle',
        menuId: 'sub_departmentMenu',
        optionsId: 'sub_departmentOptions',
        labelId: 'sub_departmentLabel',
        hiddenInputId: 'sub_departmentSelect',
        options: DEPARTMENT_OPTIONS
    });

    $('#joinedSelect').val('after');
    $('#overtimejoinedSelect').val('after');

    attendanceEmpTypeDropdown = initializeCustomMultiSelect({
        wrapperId: 'empDropdownAttendance',
        toggleId: 'employment_typeToggle',
        menuId: 'employment_typeMenu',
        optionsId: 'employment_typeOptions',
        labelId: 'employment_typeLabel',
        hiddenInputId: 'employment_typeSelect',
        options: EMPLOYMENT_OPTIONS
    });

    overtimeDeptDropdown = initializeCustomMultiSelect({
        wrapperId: 'deptDropdownOvertime',
        toggleId: 'overtimeSub_departmentToggle',
        menuId: 'overtimeSub_departmentMenu',
        optionsId: 'overtimeSub_departmentOptions',
        labelId: 'overtimeSub_departmentLabel',
        hiddenInputId: 'overtimeSub_departmentSelect',
        options: DEPARTMENT_OPTIONS
    });

    overtimeEmpTypeDropdown = initializeCustomMultiSelect({
        wrapperId: 'empDropdownOvertime',
        toggleId: 'overtimeemployment_typeToggle',
        menuId: 'overtimeemployment_typeMenu',
        optionsId: 'overtimeemployment_typeOptions',
        labelId: 'overtimeemployment_typeLabel',
        hiddenInputId: 'overtimeemployment_typeSelect',
        options: EMPLOYMENT_OPTIONS
    });

    $('#sub_departmentSelect').on('change', function() {
        if (!isRegularUser) {
            currentAttendancePage = 1;
            checkAndLoadEmployees();
        }
    });

    $('#employment_typeSelect').on('change', function() {
        updateJoinedDropdownBasedOnEmploymentType(false);
        if (!isRegularUser) {
            currentAttendancePage = 1;
            checkAndLoadEmployees();
        }
    });

    $('#overtimeSub_departmentSelect').on('change', function() {
        if (!isRegularUser) {
            currentOvertimePage = 1;
            checkAndLoadOvertimeEmployees();
        }
    });

    $('#overtimeemployment_typeSelect').on('change', function() {
        updateJoinedDropdownBasedOnEmploymentType(true);
        if (!isRegularUser) {
            currentOvertimePage = 1;
            checkAndLoadOvertimeEmployees();
        }
    });
}

function updateJoinedDropdownBasedOnEmploymentType(isOvertime = false) {
    const employmentTypeSelectId = isOvertime ? '#overtimeemployment_typeSelect' : '#employment_typeSelect';
    const joinedSelectId = isOvertime ? '#overtimejoinedSelect' : '#joinedSelect';
    
    const employmentTypeValue = $(employmentTypeSelectId).val();
    const $joinedSelect = $(joinedSelectId);
    
    let selectedEmploymentTypes = [];
    try {
        if (employmentTypeValue && employmentTypeValue.startsWith('[')) {
            selectedEmploymentTypes = JSON.parse(employmentTypeValue);
        } else if (employmentTypeValue) {
            selectedEmploymentTypes = [employmentTypeValue];
        }
    } catch (e) {
        selectedEmploymentTypes = [];
    }
    
    $joinedSelect.empty();
    $joinedSelect.append('<option value="">-- Select Option --</option>');
    
    if (selectedEmploymentTypes.length === 1 && selectedEmploymentTypes[0] === 'Blue Collar') {
        $joinedSelect.append('<option value="before">Before</option>');
        $joinedSelect.val('before');
        $joinedSelect.prop('disabled', true);
        return;
    }
    
    if (selectedEmploymentTypes.length === 1 && selectedEmploymentTypes[0] === 'Blue Collar Learner') {
        $joinedSelect.append('<option value="after">After</option>');
        $joinedSelect.val('after');
        $joinedSelect.prop('disabled', true);
        return;
    }
    
    $joinedSelect.append('<option value="before">Before</option>');
    $joinedSelect.append('<option value="after">After</option>');
    $joinedSelect.prop('disabled', false);
    $joinedSelect.val('after');
}

function updateWeekDisplay(isOvertime = false) {
    const startStr = currentWeekStart.format('MMM DD, YYYY');
    const endStr = currentWeekEnd.format('MMM DD, YYYY');
    const weekDisplay = `Week: ${startStr} - ${endStr}`;
    
    $(isOvertime ? '#overtimeTab' : '#attendanceTab')
        .find('.current-week-display')
        .text(weekDisplay);
    
    const rangeSelector = isOvertime ? '#overtimeReportrange span' : '#reportrange span';
    $(rangeSelector).text(selectedRangeStart.format('MM/DD/YYYY') + ' - ' + selectedRangeEnd.format('MM/DD/YYYY'));
    
    selectedStartDate = currentWeekStart.format('YYYY-MM-DD');
    selectedEndDate = currentWeekEnd.format('YYYY-MM-DD');
}

function navigateWeek(direction, isOvertime = false) {
    if (!selectedRangeStart || !selectedRangeEnd) return;

    const newWeekStart = moment(currentWeekStart);
    
    if (direction === 'next') {
        newWeekStart.add(7, 'days');
        if (newWeekStart.isAfter(selectedRangeEnd)) return;
    } else {
        newWeekStart.subtract(7, 'days');
        if (newWeekStart.isBefore(selectedRangeStart)) {
            newWeekStart.set(selectedRangeStart);
        }
    }
    
    currentWeekStart = newWeekStart;
    
    const potentialWeekEnd = moment(currentWeekStart).add(6, 'days');
    currentWeekEnd = potentialWeekEnd.isAfter(selectedRangeEnd) ? 
                    moment(selectedRangeEnd) : potentialWeekEnd;
    
    selectedStartDate = currentWeekStart.format('YYYY-MM-DD');
    selectedEndDate = currentWeekEnd.format('YYYY-MM-DD');
    
    updateWeekDisplay(false);
    updateWeekDisplay(true);
    
    showLoadingIndicator(true);
    
    initializeDataTable(isOvertime);
    
    updateNavigationButtons(false);
    updateNavigationButtons(true);

    if (!isOvertime) {
        liveAttendanceData = {};
    } else {
        liveOvertimeData = {};
    }

    setTimeout(() => {
        applyCachedLeaveData(false);
        applyCachedLeaveData(true);
        applyCachedAttendanceData();
        applyCachedOvertimeData();
        applyResponsiveTableLayout(false);
        applyResponsiveTableLayout(true);
        showLoadingIndicator(false);
    }, 500);

    if (!isOvertime) {
        setTimeout(() => {
            updateDisplayedActualManHours();
        }, 600);
    }
}

function updateNavigationButtons(isOvertime = false) {
    const tab = isOvertime ? '#overtimeTab' : '#attendanceTab';
    const prevBtn = $(`${tab} .prev-week`);
    const nextBtn = $(`${tab} .next-week`);
    
    const isAtStart = currentWeekStart.isSame(selectedRangeStart, 'day');
    prevBtn.prop('disabled', isAtStart);
    prevBtn.toggleClass('disabled', isAtStart);
    
    const nextWeekStart = moment(currentWeekEnd).add(1, 'day');
    const hasMoreDays = nextWeekStart.isSameOrBefore(selectedRangeEnd);
    
    nextBtn.prop('disabled', !hasMoreDays);
    nextBtn.toggleClass('disabled', !hasMoreDays);
}

function updateSubDepartmentOptions() {
    if (!isUserSupervisor()) {
        return;
    }
    
    const supervisorDepts = typeof SUPERVISOR_DEPARTMENTS !== 'undefined' ? SUPERVISOR_DEPARTMENTS : [];
    
    if (supervisorDepts.length === 0) {
        return;
    }
    
    if (attendanceDeptDropdown && supervisorDepts.length > 0) {
        attendanceDeptDropdown.setSelectedValues(supervisorDepts);
    }
    
    if (overtimeDeptDropdown && supervisorDepts.length > 0) {
        overtimeDeptDropdown.setSelectedValues(supervisorDepts);
    }
}

function validateSupervisorAccess(subDepartment) {
    if (!isUserSupervisor()) {
        return true;
    }
    
    const supervisorDepts = typeof SUPERVISOR_DEPARTMENTS !== 'undefined' ? SUPERVISOR_DEPARTMENTS : [];
    return supervisorDepts.includes(subDepartment);
}

function loadEmployees(subDepartment, groupType, isOvertime = false) {
    
    // ===== ⭐ FIXED: CLEAR SESSION STORAGE WHEN LOADING NORMALLY =====
    sessionStorage.removeItem('lastSearchValue_' + (isOvertime ? 'overtime' : 'attendance'));
    console.log('✅ Cleared search value from session storage on normal load');
    
    let subDepartmentSelect = isOvertime ? 
        $('#overtimeSub_departmentSelect').val() : 
        $('#sub_departmentSelect').val();
    
    let employmentTypeSelect = isOvertime ? 
        $('#overtimeemployment_typeSelect').val() : 
        $('#employment_typeSelect').val();
    
    let selectedDepartments = [];
    let selectedEmploymentTypes = [];
    
    try {
        if (subDepartmentSelect && subDepartmentSelect.startsWith('[')) {
            selectedDepartments = JSON.parse(subDepartmentSelect);
        }
    } catch (e) {
        if (subDepartmentSelect) {
            selectedDepartments = [subDepartmentSelect];
        }
    }
    
    try {
        if (employmentTypeSelect && employmentTypeSelect.startsWith('[')) {
            selectedEmploymentTypes = JSON.parse(employmentTypeSelect);
        }
    } catch (e) {
        if (employmentTypeSelect) {
            selectedEmploymentTypes = [employmentTypeSelect];
        }
    }
    
    const groupTypeSelect = isOvertime ? 
        $('#overtimeGroup_typeSelect').val() : 
        $('#group_typeSelect').val();
    
    const tableId = isOvertime ? '#table_overtime_items' : '#table_all_items';
    if ($.fn.DataTable.isDataTable(tableId)) {
        $(tableId).DataTable().destroy();
        $(tableId + ' tbody').empty();
    }

    showLoadingIndicator(true);
    pendingRequests++;
    
    const startDateToSend = selectedRangeStart.format('YYYY-MM-DD');
    const endDateToSend = selectedRangeEnd.format('YYYY-MM-DD');
    
    const action = isOvertime ? 'fetchOvertimeData' : 'fetchAttendanceData';
    
    const currentPage = isOvertime ? currentOvertimePage : currentAttendancePage;
    const pageLength = isOvertime ? overtimePageLength : attendancePageLength;
    
    // ===== GENERATE ALL DATES IN THE FILTER RANGE FOR TRANSFER CHECKING =====
    const dateRangeForTransferCheck = [];
    const currentDate = moment(startDateToSend, 'YYYY-MM-DD');
    const endDate = moment(endDateToSend, 'YYYY-MM-DD');
    
    while (currentDate.isSameOrBefore(endDate)) {
        dateRangeForTransferCheck.push(currentDate.format('YYYY-MM-DD'));
        currentDate.add(1, 'day');
    }
    
    console.log('📅 Transfer Check Dates Generated:', {
        startDate: startDateToSend,
        endDate: endDateToSend,
        totalDates: dateRangeForTransferCheck.length,
        dates: dateRangeForTransferCheck
    });
    
    // ===== CRITICAL: INCLUDE FULL DATE RANGE FOR TRANSFER CHECKING =====
    const ajaxData = {
        action: action,
        sub_department: isRegularUser ? '' : JSON.stringify(selectedDepartments),
        employment_type: isRegularUser ? '' : JSON.stringify(selectedEmploymentTypes),
        group_type: isRegularUser ? '' : (groupTypeSelect || ''),
        joined: isOvertime ? $('#overtimejoinedSelect').val() : $('#joinedSelect').val(),
        start_date: startDateToSend,
        end_date: endDateToSend,
        transfer_check_start_date: startDateToSend,
        transfer_check_end_date: endDateToSend,
        transfer_check_dates: JSON.stringify(dateRangeForTransferCheck),
        page: currentPage,
        per_page: pageLength
    };
    
    if (isUserSupervisor()) {
        ajaxData.supervisor_id = getSupervisorId();
    }
    
    console.log('📄 Loading page:', currentPage, 'per_page:', pageLength, 'action:', action);
    console.log('📤 AJAX Request Data:', {
        action: ajaxData.action,
        departments: selectedDepartments,
        employmentTypes: selectedEmploymentTypes,
        groupType: groupTypeSelect,
        dateRange: `${startDateToSend} to ${endDateToSend}`,
        transferCheckRange: `${ajaxData.transfer_check_start_date} to ${ajaxData.transfer_check_end_date}`,
        transferCheckDatesCount: dateRangeForTransferCheck.length,
        transferCheckDates: dateRangeForTransferCheck,
        page: currentPage,
        perPage: pageLength,
        isOvertime: isOvertime
    });
    
    $.ajax({
        url: '/dpm/api/PMSController.php',
        method: 'POST',
        data: ajaxData,
        dataType: 'json',
        timeout: 30000,
        success: function(response) {
            console.log('✅ AJAX RESPONSE RECEIVED:', response);
            
            // ===== ERROR HANDLING =====
            if (response.success === false) {
                if (response.message) {
                    showInfoMessage(response.message);
                } else {
                    alert(`Error loading ${isOvertime ? 'overtime ' : ''}data`);
                }
                
                if (isOvertime) {
                    $('#overtimeDetailsegment').hide();
                } else {
                    $('#detailsegment').hide();
                }
                showLoadingIndicator(false);
                pendingRequests--;
                return;
            }
            
            // ===== SUCCESS HANDLING =====
            if (response.success && response.employees && Array.isArray(response.employees)) {
                console.log('📊 RESPONSE DATA BREAKDOWN:');
                console.log('  Employees:', response.employees.length);
                console.log('  Attendance/Overtime Records:', isOvertime ? (response.overtime ? response.overtime.length : 0) : (response.attendance ? response.attendance.length : 0));
                console.log('  Leaves:', response.leaves ? response.leaves.length : 0);
                console.log('  Holidays:', response.holidays ? Object.keys(response.holidays).length : 0);
                console.log('  Transfer History:', response.transfer_history ? response.transfer_history.length : 0);
                console.log('  Page-wise Status:', response.page_wise_status);
                
                const filteredEmployees = response.employees;
                
                console.log('📊 Using backend-filtered employees:', {
                    count: filteredEmployees.length,
                    selectedDepartments: selectedDepartments,
                    dateRange: `${startDateToSend} to ${endDateToSend}`,
                    transferHistoryCount: response.transfer_history ? response.transfer_history.length : 0
                });
                
                // ===== VALIDATE TRANSFER HISTORY FOR EVERY DATE IN THE RANGE =====
                if (response.transfer_history && Array.isArray(response.transfer_history)) {
                    console.log('📋 Transfer History Validation for All Dates in Range:');
                    console.log('═══════════════════════════════════════════════════════════════');
                    
                    const transfersByEmployeeAndDate = {};
                    
                    response.transfer_history.forEach(transfer => {
                        const gid = transfer.gid;
                        const transferStart = moment(transfer.transfer_from_date, 'YYYY-MM-DD');
                        const transferEnd = moment(transfer.transfer_to_date, 'YYYY-MM-DD');
                        
                        if (!transfersByEmployeeAndDate[gid]) {
                            transfersByEmployeeAndDate[gid] = {};
                        }
                        
                        dateRangeForTransferCheck.forEach(dateStr => {
                            const checkDate = moment(dateStr, 'YYYY-MM-DD');
                            const isInTransferPeriod = checkDate.isSameOrAfter(transferStart) && 
                                                     checkDate.isSameOrBefore(transferEnd);
                            
                            if (isInTransferPeriod) {
                                transfersByEmployeeAndDate[gid][dateStr] = {
                                    from_dept: transfer.sub_department,
                                    to_dept: transfer.temp_sub_department,
                                    transfer_from_date: transfer.transfer_from_date,
                                    transfer_to_date: transfer.transfer_to_date,
                                    is_in_transfer: true
                                };
                            }
                        });
                    });
                    
                    Object.keys(transfersByEmployeeAndDate).forEach(gid => {
                        const employeeTransfers = transfersByEmployeeAndDate[gid];
                        const transferDates = Object.keys(employeeTransfers);
                        
                        if (transferDates.length > 0) {
                            console.log(`\n👤 Employee GID: ${gid}`);
                            console.log('  Transfer Dates in Range:');
                            
                            transferDates.forEach(dateStr => {
                                const transferInfo = employeeTransfers[dateStr];
                                const isInSelectedDept = selectedDepartments.includes(transferInfo.from_dept) || 
                                                       selectedDepartments.includes(transferInfo.to_dept);
                                
                                console.log(`    📅 ${dateStr}: ${transferInfo.from_dept} → ${transferInfo.to_dept} ` +
                                          `(Period: ${transferInfo.transfer_from_date} to ${transferInfo.transfer_to_date}) ` +
                                          `| In Selected Dept: ${isInSelectedDept}`);
                            });
                        }
                    });
                    
                    console.log('\n═══════════════════════════════════════════════════════════════');
                    console.log('📊 Transfer Summary:');
                    console.log(`  Total Employees with Transfers: ${Object.keys(transfersByEmployeeAndDate).length}`);
                    console.log(`  Total Dates Checked: ${dateRangeForTransferCheck.length}`);
                    console.log(`  Date Range: ${startDateToSend} to ${endDateToSend}`);
                    
                    console.log('\n📋 Employee-Department-Date Validation:');
                    console.log('═══════════════════════════════════════════════════════════════');
                    
                    filteredEmployees.forEach(employee => {
                        const gid = employee.gid;
                        console.log(`\n👤 ${employee.name} (GID: ${gid})`);
                        console.log(`  Permanent Dept: ${employee.sub_department}`);
                        console.log(`  Temporary Dept: ${employee.temp_sub_department || 'N/A'}`);
                        console.log(`  Transfer Period: ${employee.transfer_from_date || 'N/A'} to ${employee.transfer_to_date || 'N/A'}`);
                        console.log('  Date-wise Status:');
                        
                        dateRangeForTransferCheck.forEach(dateStr => {
                            const hasTransferOnDate = transfersByEmployeeAndDate[gid] && 
                                                    transfersByEmployeeAndDate[gid][dateStr];
                            
                            let status = '';
                            let dept = '';
                            
                            if (hasTransferOnDate) {
                                const transferInfo = transfersByEmployeeAndDate[gid][dateStr];
                                status = `IN TRANSFER: ${transferInfo.from_dept} → ${transferInfo.to_dept}`;
                                dept = transferInfo.to_dept;
                            } else {
                                status = `PERMANENT: ${employee.sub_department}`;
                                dept = employee.sub_department;
                            }
                            
                            const isInSelectedDept = selectedDepartments.includes(dept);
                            const visibility = isInSelectedDept ? '✅ VISIBLE' : '❌ HIDDEN';
                            
                            console.log(`    ${dateStr}: ${status} | ${visibility}`);
                        });
                    });
                    
                    console.log('\n═══════════════════════════════════════════════════════════════');
                }
                
                // ===== STORE USER DATA =====
                if (response.user_shift !== undefined) {
                    userCurrentShift = response.user_shift;
                    userHasShiftEntry = response.user_has_shift_entry || false;
                }
                
                if (response.user_attendance_records && Array.isArray(response.user_attendance_records)) {
                    userAttendanceRecords = response.user_attendance_records;
                }
                
                // ===== STORE HOLIDAYS =====
                if (response.holidays) {
                    window.holidaysData = response.holidays;
                    cachedHolidaysData = response.holidays;
                    console.log('📅 Holidays stored:', response.holidays);
                }
                
                // ===== STORE LEAVES =====
                if (response.leaves && Array.isArray(response.leaves)) {
                    cachedLeaveData = {};
                    response.leaves.forEach(record => {
                        const gid = record.gid;
                        if (!cachedLeaveData[gid]) {
                            cachedLeaveData[gid] = [];
                        }
                        cachedLeaveData[gid].push(record);
                    });
                    console.log('🏖️ Leaves stored:', Object.keys(cachedLeaveData).length, 'employees');
                }
                
                // ===== HANDLE OVERTIME DATA =====
                if (isOvertime) {
                    // ===== STORE page_wise_status FROM RESPONSE =====
                    if (response.page_wise_status) {
                        console.log('📊 Storing page_wise_status from response');
                        overtimePageWiseStatus = response.page_wise_status;
                        
                        console.log('📊 page_wise_status stored:', overtimePageWiseStatus);
                    } else {
                        console.warn('⚠️ No page_wise_status in response');
                        overtimePageWiseStatus = {};
                    }
                    
                    if (response.overtime && Array.isArray(response.overtime)) {
                        overtimeCachedData = response.overtime;
                        
                        console.log('📊 Overtime data loaded:', response.overtime.length, 'records');
                    }
                    
                    // ===== COMBINE REGULAR AND HOLIDAY OVERTIME =====
                    if (response.holiday_overtime && Array.isArray(response.holiday_overtime)) {
                        console.log('📊 Holiday overtime data loaded:', response.holiday_overtime.length, 'records');
                        
                        if (!overtimeCachedData) overtimeCachedData = [];
                        overtimeCachedData = overtimeCachedData.concat(response.holiday_overtime);
                        
                        console.log('📊 Total overtime records after merge:', overtimeCachedData.length);
                    }
                    
                    // ===== SET OVERTIME PAGINATION =====
                    currentOvertimeEmployees = filteredEmployees;
                    totalOvertimeEmployees = response.pagination ? response.pagination.total : 0;
                    totalOvertimePages = response.pagination ? response.pagination.total_pages : 0;
                    currentsub_department = isRegularUser ? '' : (subDepartmentSelect || '');
                    
                    overtimePaginationInfo = {
                        current_page: response.pagination ? response.pagination.current_page : 1,
                        per_page: response.pagination ? response.pagination.per_page : 20,
                        total: response.pagination ? response.pagination.total : 0,
                        filtered: response.pagination ? response.pagination.filtered : 0,
                        total_pages: response.pagination ? response.pagination.total_pages : 0,
                        pagination_text: response.pagination ? response.pagination.pagination_text : 'Showing 0 to 0 of 0 entries'
                    };
                    
                    console.log('📊 Overtime Pagination:', overtimePaginationInfo);
                    
                    // ===== CREATE OVERTIME TABLE =====
                    if (filteredEmployees.length > 0) {
                        $('#overtimeDetailsegment').show();
                        $('#detailsegment').hide();
                        
                        console.log('🔄 Creating server-side paginated table for overtime...');
                        
                        const combinedOvertimeData = {
                            overtime: response.overtime || [],
                            holiday_overtime: response.holiday_overtime || []
                        };
                        
                        createServerSidePaginatedTable(true, filteredEmployees, combinedOvertimeData, overtimePaginationInfo);
                        
                        setTimeout(() => {
                            console.log('🔄 Applying leave data to overtime table...');
                            if (response.leaves && response.leaves.length > 0) {
                                applyLeaveDataToTable(response.leaves, selectedStartDate, selectedEndDate, true);
                            }
                            
                            console.log('🔄 Populating existing overtime data...');
                            populateExistingOvertimeData(combinedOvertimeData);
                            
                            console.log('🔄 Applying responsive layout...');
                            applyResponsiveTableLayout(true);
                            
                            console.log('🔄 Updating submit button states...');
                            updateSubmitButtonStates(true);
                            
                            console.log('🔄 Refreshing all date badges...');
                            refreshAllDateBadges(true);
                            
                            showLoadingIndicator(false);
                            pendingRequests--;
                            
                            console.log('✅ Overtime table fully loaded and populated');
                        }, 300);
                    } else {
                        showInfoMessage('No employees found in selected department');
                        $('#overtimeDetailsegment').hide();
                        showLoadingIndicator(false);
                        pendingRequests--;
                    }
                } 
                // ===== HANDLE ATTENDANCE DATA =====
                else {
                    if (response.attendance && Array.isArray(response.attendance)) {
                        attendanceCachedData = response.attendance;
                        
                        attendanceStatusByDate = {};
                        
                        if (response.page_wise_status) {
                            console.log('📊 Processing page-wise status for attendance:');
                            Object.keys(response.page_wise_status).forEach(dateStr => {
                                const statusData = response.page_wise_status[dateStr];
                                attendanceStatusByDate[dateStr] = {
                                    status: statusData.status,
                                    saved: statusData.saved,
                                    submitted: statusData.submitted,
                                    unfilled: statusData.unfilled,
                                    transferred: statusData.transferred || 0,
                                    total: statusData.total_employees_on_page_for_date,
                                    page: statusData.page,
                                    total_pages: statusData.total_pages
                                };
                                
                                console.log(`🏷️ Attendance Date ${dateStr}:`, attendanceStatusByDate[dateStr]);
                            });
                        } else {
                            console.log('⚠️ No page_wise_status in response');
                        }
                        
                        console.log('📊 Attendance data loaded:', response.attendance.length, 'records');
                        console.log('📊 Attendance Status Map:', attendanceStatusByDate);
                    }
                    
                    // ===== SET ATTENDANCE PAGINATION =====
                    currentEmployees = filteredEmployees;
                    totalAttendanceEmployees = response.pagination ? response.pagination.total : 0;
                    totalAttendancePages = response.pagination ? response.pagination.total_pages : 0;
                    currentsub_department = isRegularUser ? '' : (subDepartmentSelect || '');
                    
                    attendancePaginationInfo = {
                        current_page: response.pagination ? response.pagination.current_page : 1,
                        per_page: response.pagination ? response.pagination.per_page : 20,
                        total: response.pagination ? response.pagination.total : 0,
                        filtered: response.pagination ? response.pagination.filtered : 0,
                        total_pages: response.pagination ? response.pagination.total_pages : 0,
                        pagination_text: response.pagination ? response.pagination.pagination_text : 'Showing 0 to 0 of 0 entries'
                    };
                    
                    console.log('📊 Attendance Pagination:', attendancePaginationInfo);
                    
                    // ===== CREATE ATTENDANCE TABLE =====
                    if (filteredEmployees.length > 0) {
                        $('#detailsegment').show();
                        $('#overtimeDetailsegment').hide();
                        
                        console.log('🔄 Creating server-side paginated table for attendance...');
                        createServerSidePaginatedTable(false, filteredEmployees, response.attendance || [], attendancePaginationInfo);
                        
                        setTimeout(() => {
                            console.log('🔄 Applying leave data to attendance table...');
                            if (response.leaves && response.leaves.length > 0) {
                                applyLeaveDataToTable(response.leaves, selectedStartDate, selectedEndDate, false);
                            }
                            
                            console.log('🔄 Populating existing attendance data...');
                            populateExistingAttendanceData(response.attendance || []);
                            
                            console.log('🔄 Applying responsive layout...');
                            applyResponsiveTableLayout(false);
                            
                            console.log('🔄 Updating submit button states...');
                            updateSubmitButtonStates(false);
                            
                            console.log('🔄 Refreshing all date badges...');
                            refreshAllDateBadges(false);
                            
                            showLoadingIndicator(false);
                            pendingRequests--;
                            
                            console.log('✅ Attendance table fully loaded and populated');
                        }, 300);
                        
                        setTimeout(() => {
                            console.log('🔄 Calculating initial man hours...');
                            calculateInitialManHours();
                        }, 400);
                    } else {
                        showInfoMessage('No employees found in selected department');
                        $('#detailsegment').hide();
                        showLoadingIndicator(false);
                        pendingRequests--;
                    }
                }
            } else {
                const message = response.message || 'No employees found';
                showInfoMessage(message);
                
                if (isOvertime) {
                    $('#overtimeDetailsegment').hide();
                } else {
                    $('#detailsegment').hide();
                }
                showLoadingIndicator(false);
                pendingRequests--;
            }
        },
        error: function(xhr, status, error) {
            console.error('❌ AJAX ERROR:', error);
            console.error('Status:', status);
            console.error('Response:', xhr.responseText);
            
            let errorMessage = 'Unknown error occurred';
            
            if (status === 'timeout') {
                errorMessage = 'Request timeout - server took too long to respond';
            } else if (status === 'error') {
                errorMessage = `Server error (${xhr.status})`;
            } else if (status === 'parsererror') {
                errorMessage = 'Invalid response format from server';
            } else if (xhr.status === 0) {
                errorMessage = 'Network error - check your connection';
            }
            
            showCustomAlert({
                type: 'error',
                title: 'Loading Error',
                message: `Error loading ${isOvertime ? 'overtime ' : ''}data`,
                note: `${errorMessage}. Please try again.`
            });
            
            if (isOvertime) {
                $('#overtimeDetailsegment').hide();
            } else {
                $('#detailsegment').hide();
            }
            showLoadingIndicator(false);
            pendingRequests--;
        },
        complete: function() {
            pendingRequests--;
            if (pendingRequests === 0) {
                showLoadingIndicator(false);
            }
            console.log('📄 AJAX Request Complete - Pending Requests:', pendingRequests);
        }
    });
}

function applyLeaveDataToTable(leaveData, viewStartDate, viewEndDate, isOvertime = false) {
    if (!leaveData || (Array.isArray(leaveData) && leaveData.length === 0)) {
        return;
    }

    if (!Array.isArray(leaveData)) {
        if (typeof leaveData === 'object') {
            leaveData = Object.values(leaveData);
        } else {
            return;
        }
    }

    const tableId = isOvertime ? '#table_overtime_items' : '#table_all_items';
    
    const leaveMap = {};
    
    leaveData.forEach(record => {
        const gid = record.gid || record.employee_gid;
        const leaveStartDate = record.start_date;
        const leaveEndDate = record.end_date || record.start_date;
        
        if (!gid || !leaveStartDate) {
            return;
        }
        
        if (!leaveMap[gid]) {
            leaveMap[gid] = {};
        }
        
        const currentDate = moment(leaveStartDate, 'YYYY-MM-DD').startOf('day');
        const endDate = moment(leaveEndDate, 'YYYY-MM-DD').startOf('day');
        const viewStart = moment(viewStartDate, 'YYYY-MM-DD').startOf('day');
        const viewEnd = moment(viewEndDate, 'YYYY-MM-DD').startOf('day');
        
        const tempDate = moment(currentDate);
        while (tempDate.isSameOrBefore(endDate, 'day')) {
            const dateStr = tempDate.format('YYYY-MM-DD');
            
            const isInView = tempDate.isSameOrAfter(viewStart, 'day') && 
                           tempDate.isSameOrBefore(viewEnd, 'day');
            
            leaveMap[gid][dateStr] = {
                leave_type: record.leave_type || 'Leave',
                start_date: leaveStartDate,
                end_date: leaveEndDate,
                full_record: record,
                in_current_view: isInView
            };
            
            tempDate.add(1, 'day');
        }
    });

    let leaveDaysMarked = 0;
    
    $(`${tableId} tbody tr`).each(function() {
        const row = $(this);
        const gid = row.find('td:eq(1)').text().trim();
        
        if (leaveMap[gid]) {
            if (isOvertime) {
                // ===== OVERTIME TAB =====
                row.find('.overtime-input').each(function() {
                    const $input = $(this);
                    const dateStr = $input.data('date');
                    
                    if (leaveMap[gid][dateStr]) {
                        if (leaveMap[gid][dateStr].in_current_view) {
                            const leaveInfo = leaveMap[gid][dateStr];
                            
                            $input.addClass('on-leave-from-system');
                            $input.val('0');
                            
                            // ===== GET THE PARENT CELL CONTAINER =====
                            const $cellContainer = $input.closest('div[style*="flex-direction"]');
                            
                            if (isAdminUser) {
                                $input.prop('disabled', false);
                                $input.addClass('admin-editable');
                            } else {
                                $input.prop('readonly', true);
                            }
                            
                            // ===== REMOVE OLD LEAVE INDICATOR =====
                            $cellContainer.find('.leave-indicator').remove();
                            
                            // ===== ADD LEAVE BADGE ON NEW LINE WITH BACKGROUND =====
                            if (!$cellContainer.find('.leave-indicator').length) {
                                $cellContainer.append(`
                                    <span class="leave-indicator" style="
                                        font-size: 9px; 
                                        font-weight: 600; 
                                        color: #FF6B6B; 
                                        background: #FFE5E5; 
                                        padding: 4px 8px; 
                                        border-radius: 3px; 
                                        margin-top: 4px; 
                                        text-align: center; 
                                        width: 90%; 
                                        display: block; 
                                        white-space: normal; 
                                        word-wrap: break-word; 
                                        word-break: break-word; 
                                        line-height: 1.3;
                                        border: 1px solid #FF6B6B;
                                    ">
                                        ${leaveInfo.leave_type}
                                    </span>
                                `);
                            }
                            
                            leaveDaysMarked++;
                        }
                    }
                });
            } else {
                // ===== ATTENDANCE TAB =====
                row.find('.attendance-select').each(function() {
                    const $select = $(this);
                    const dateStr = $select.data('date');
                    
                    if (leaveMap[gid][dateStr]) {
                        if (leaveMap[gid][dateStr].in_current_view) {
                            const leaveInfo = leaveMap[gid][dateStr];
                            
                            $select.val('leave');
                            $select.addClass('leave on-leave-from-system');
                            
                            // ===== GET THE CELL CONTAINER =====
                            const $cellContainer = $select.closest('div[style*="flex"]');
                            
                            if (isAdminUser) {
                                $select.prop("disabled", false);
                                $select.addClass('admin-editable');
                                
                                // ===== REMOVE OLD CHECKMARK =====
                                $select.next('.admin-edit-indicator').remove();
                                
                                // ===== ADD CHECKMARK NEXT TO SELECT =====
                                if (!$select.next('.admin-edit-indicator').length) {
                                    $select.after(`<span class="admin-edit-indicator" style="
                                        font-size: 16px; 
                                        color: #00a9a9; 
                                        font-weight: bold; 
                                        margin-left: 6px;
                                        flex-shrink: 0;
                                    ">✓</span>`);
                                }
                            } else {
                                $select.prop('disabled', true);
                            }
                            
                            // ===== REMOVE OLD LEAVE INDICATOR =====
                            $cellContainer.find('.leave-indicator').remove();
                            
                            // ===== ADD LEAVE BADGE ON NEW LINE WITH BACKGROUND =====
                            if (!$cellContainer.find('.leave-indicator').length) {
                                $cellContainer.append(`
                                    <span class="leave-indicator" style="
                                        font-size: 9px; 
                                        font-weight: 600; 
                                        color: #FF6B6B; 
                                        background: #FFE5E5; 
                                        padding: 4px 8px; 
                                        border-radius: 3px; 
                                        margin-top: 4px; 
                                        text-align: center; 
                                        width: 90%; 
                                        display: block; 
                                        white-space: normal; 
                                        word-wrap: break-word; 
                                        word-break: break-word; 
                                        line-height: 1.3;
                                        border: 1px solid #FF6B6B;
                                    ">
                                        ${leaveInfo.leave_type}
                                    </span>
                                `);
                            }
                            
                            $select.addClass('existing-data');
                            
                            leaveDaysMarked++;
                        }
                    }
                });
            }
        }
    });

    if (!isOvertime) {
        updateDisplayedActualManHours();
    } else {
        $(`${tableId} tbody tr`).each(function() {
            let totalOvertimeHours = 0;
            $(this).find('.overtime-input').each(function() {
                const hours = parseFloat($(this).val()) || 0;
                totalOvertimeHours += hours;
            });
            $(this).find('.overtime-total').text(totalOvertimeHours.toFixed(1));
        });
    }
}

function showTab(tabName) {
    sessionStorage.setItem('activeTab', tabName);
    
    $('#attendanceTab').removeClass('active').hide();
    $('#overtimeTab').removeClass('active').hide();
    
    $('.tab-button').removeClass('active');
    
    if (tabName === 'attendance') {
        $('#attendanceTab').addClass('active').show();
        $('.tab-button').eq(0).addClass('active');
        
        setTimeout(() => {
            reloadCurrentTabTable('attendance');
        }, 300);
    } else if (tabName === 'overtime') {
        $('#overtimeTab').addClass('active').show();
        $('.tab-button').eq(1).addClass('active');
        
        setTimeout(() => {
            reloadCurrentTabTable('overtime');
        }, 300);
    }
}

function reloadCurrentTabTable(tabType) {
    const isOvertime = tabType === 'overtime';
    
    let subDepartmentSelect = isOvertime ? 
        $('#overtimeSub_departmentSelect').val() : 
        $('#sub_departmentSelect').val();
    
    let employmentTypeSelect = isOvertime ? 
        $('#overtimeemployment_typeSelect').val() : 
        $('#employment_typeSelect').val();
    
    let selectedDepartments = [];
    let selectedEmploymentTypes = [];
    
    try {
        if (subDepartmentSelect && subDepartmentSelect.startsWith('[')) {
            selectedDepartments = JSON.parse(subDepartmentSelect);
        }
    } catch (e) {
        // ignore
    }
    
    try {
        if (employmentTypeSelect && employmentTypeSelect.startsWith('[')) {
            selectedEmploymentTypes = JSON.parse(employmentTypeSelect);
        }
    } catch (e) {
        // ignore
    }
    
    const groupTypeSelect = isOvertime ? 
        $('#overtimeGroup_typeSelect').val() : 
        $('#group_typeSelect').val();
    
    if (isRegularUser) {
        loadEmployees('', '', isOvertime);
        return;
    }
    
    if (selectedDepartments.length === 0 || !groupTypeSelect) {
        if (isOvertime) {
            $('#overtimeDetailsegment').hide();
        } else {
            $('#detailsegment').hide();
        }
        return;
    }
    
    loadEmployees(subDepartmentSelect, groupTypeSelect, isOvertime);
}

function applyCachedLeaveData(isOvertime = false) {
    if (!cachedLeaveData || Object.keys(cachedLeaveData).length === 0) {
        return;
    }

    const leaveDataArray = [];
    Object.keys(cachedLeaveData).forEach(gid => {
        cachedLeaveData[gid].forEach(record => {
            leaveDataArray.push(record);
        });
    });
    
    applyLeaveDataToTable(leaveDataArray, selectedStartDate, selectedEndDate, isOvertime);
}

function applyCachedAttendanceData() {
    if (!attendanceCachedData || attendanceCachedData.length === 0) {
        return;
    }

    populateExistingAttendanceData(attendanceCachedData);
}

function applyCachedOvertimeData() {
    if (!overtimeCachedData || overtimeCachedData.length === 0) {
        return;
    }

    populateExistingOvertimeData(overtimeCachedData);
}

function generateDateColumns(startDate, endDate, isOvertime = false) {
    const dates = [];
    const current = moment(startDate);
    const end = moment(endDate);
    
    const holidays = window.holidaysData || cachedHolidaysData || {};
    
    const MAX_DAYS_IN_VIEW = 31;
    let dayCount = 0;
    
    while (current.isSameOrBefore(end) && dayCount < MAX_DAYS_IN_VIEW) {
        const dateStr = current.format('YYYY-MM-DD');
        const isHoliday = holidays[dateStr] === 'holiday';
        const columnClass = isHoliday ? "holiday-column" : "";
        
        updateStatusTrackingForDate(dateStr, isOvertime);
        
        dates.push({
            title: generateDateColumnHeader(dateStr, current.format('DD MMM'), isHoliday, isOvertime),
            className: `ta-c${columnClass}`,
            data: null,
            width: "65px",  
            orderable: false,
            render: function(data, type, row) {
                return generateDateCellContent(dateStr, row, isHoliday, isOvertime);
            }
        });
        
        current.add(1, 'day');
        dayCount++;
    }

    if (!isOvertime) {
        totalDateColumns = dates.length;
    }

    return dates;
}

function generateAttendanceButtons(dateStr, status, isFuture, isOvertime = false) {
    console.log('🔘 Generating Attendance Buttons:', {
        dateStr: dateStr,
        status: status,
        isFuture: isFuture,
        isAdmin: isAdminUser
    });
    
    if (isAdminUser) {
        return generateAdminAttendanceButtons(dateStr, status, isFuture);
    } else {
        return generateSupervisorAttendanceButtons(dateStr, status, isFuture);
    }
}

function generateOvertimeButtons(dateStr, status, isFuture, isOvertime = false) {
    console.log('🔘 Generating Overtime Buttons:', {
        dateStr: dateStr,
        status: status,
        isFuture: isFuture,
        isAdmin: isAdminUser
    });
    
    if (isAdminUser) {
        return generateAdminOvertimeButtons(dateStr, status, isFuture);
    } else {
        return generateSupervisorOvertimeButtons(dateStr, status, isFuture);
    }
}

// ===== UPDATED: updateStatusTrackingForDate function =====
function updateStatusTrackingForDate(dateStr, isOvertime = false) {
    const statusMap = isOvertime ? overtimeStatusByDate : attendanceStatusByDate;
    const cachedData = isOvertime ? overtimeCachedData : attendanceCachedData;
    const liveData = isOvertime ? liveOvertimeData : liveAttendanceData;
    const employeesOnPage = isOvertime ? currentOvertimeEmployees : currentEmployees;
    const tableId = isOvertime ? '#table_overtime_items' : '#table_all_items';

    console.log(`🔍 updateStatusTrackingForDate - Date: ${dateStr}, isOvertime: ${isOvertime}`);
    
    let savedCountOnPage = 0;
    let submittedCountOnPage = 0;
    let unfilledCountOnPage = 0;
    let transferredCountOnPage = 0;
    
    const employeeStatusMap = {};
    
    // ===== CHECK LIVE DATA FIRST (HIGHEST PRIORITY) =====
    Object.keys(liveData).forEach(gid => {
        if (isOvertime) {
            const liveValue = liveData[gid][dateStr];
            if (liveValue !== undefined && liveValue !== null && liveValue !== '') {
                employeeStatusMap[gid] = 'live';
                console.log(`✅ Live ${isOvertime ? 'OT' : 'Attendance'} data found for ${gid}: ${liveValue}`);
            }
        } else {
            const liveValue = liveData[gid][dateStr];
            if (liveValue && liveValue !== '' && liveValue !== '0') {
                employeeStatusMap[gid] = 'live';
                console.log(`✅ Live attendance data found for ${gid}: ${liveValue}`);
            }
        }
    });
    
    // ===== CHECK CACHED DATA (SECOND PRIORITY) =====
    if (Array.isArray(cachedData)) {
        cachedData.forEach(record => {
            let recordDate = null;
            
            if (isOvertime) {
                if (record.attendance_date) {
                    recordDate = record.attendance_date.includes('T') ? 
                        record.attendance_date.split('T')[0] : 
                        record.attendance_date;
                } else if (record.date) {
                    recordDate = record.date.includes('T') ? 
                        record.date.split('T')[0] : 
                        record.date;
                }
            } else {
                recordDate = record.attendance_date;
            }
            
            // Only use cached data if we don't already have live data for this employee
            if (recordDate === dateStr && !employeeStatusMap[record.gid]) {
                employeeStatusMap[record.gid] = record.status;
                console.log(`📋 Cached ${isOvertime ? 'OT' : 'Attendance'} data found for ${record.gid}: status=${record.status}`);
            }
        });
    }

    console.log(`📊 Employee Status Map for ${isOvertime ? 'Overtime' : 'Attendance'}:`, employeeStatusMap);

    // ===== COUNT STATUSES FOR EMPLOYEES ON PAGE =====
    employeesOnPage.forEach(employee => {
        const gid = employee.gid;

        // ===== CHECK IF EMPLOYEE IS TRANSFERRED ON THIS DATE =====
        const isInTransferPeriod = isEmployeeInTransferredDeptOnDate(employee, dateStr);
        
        let isRelevant = true;
        
        const deptSelectValue = isOvertime ? $('#overtimeSub_departmentSelect').val() : $('#sub_departmentSelect').val();
        let selectedDepartments = [];
        try {
            if (deptSelectValue && deptSelectValue.startsWith('[')) {
                selectedDepartments = JSON.parse(deptSelectValue);
            } else if (deptSelectValue) {
                selectedDepartments = [deptSelectValue];
            }
        } catch (e) {
            selectedDepartments = [];
        }

        if (selectedDepartments.length > 0) {
            const employeePermanentDept = employee.sub_department;
            const employeeTempDept = employee.temp_sub_department;

            if (isInTransferPeriod && employeeTempDept) {
                isRelevant = selectedDepartments.includes(employeeTempDept);
                if (!isRelevant) {
                    isRelevant = selectedDepartments.includes(employeePermanentDept);
                    if (isRelevant) {
                        transferredCountOnPage++;
                        console.log(`⏭️ Employee ${gid}: TRANSFERRED - not counting in status`);
                        return;
                    }
                }
            } else {
                isRelevant = selectedDepartments.includes(employeePermanentDept);
            }
        }

        if (!isRelevant) {
            console.log(`⏭️ Employee ${gid}: Not relevant to selected departments`);
            return;
        }

        if (employeeStatusMap[gid]) {
            const status = employeeStatusMap[gid];
            console.log(`📊 Counting ${gid}: status="${status}"`);
            
            if (status === 'live' || status === 'save') {
                savedCountOnPage++;
                console.log(`  → Counted as SAVED`);
            } else if (status === 'sub') {
                submittedCountOnPage++;
                console.log(`  → Counted as SUBMITTED`);
            }
        } else {
            unfilledCountOnPage++;
            console.log(`📊 ${gid}: NO DATA - counted as UNFILLED`);
        }
    });
    
    const totalEmployeesForDate = savedCountOnPage + submittedCountOnPage + unfilledCountOnPage;

    let overallStatus = null;
    
    console.log(`📊 Status Summary for ${dateStr} (${isOvertime ? 'Overtime' : 'Attendance'}):`, {
        saved: savedCountOnPage,
        submitted: submittedCountOnPage,
        unfilled: unfilledCountOnPage,
        transferred: transferredCountOnPage,
        total: totalEmployeesForDate
    });
    
    if (totalEmployeesForDate > 0) {
        if (!isOvertime) {
            // ===== ATTENDANCE BADGE LOGIC =====
            if (submittedCountOnPage === totalEmployeesForDate) {
                overallStatus = 'sub';
                console.log(`✅ All submitted (${submittedCountOnPage}/${totalEmployeesForDate})`);
            } else if (submittedCountOnPage > 0 && savedCountOnPage > 0) {
                overallStatus = 'mixed';
                console.log(`🔀 Mixed status: ${submittedCountOnPage} submitted, ${savedCountOnPage} saved`);
            } else if (savedCountOnPage > 0) {
                overallStatus = 'save';
                console.log(`📋 Has saved data (${savedCountOnPage}/${totalEmployeesForDate})`);
            } else if (unfilledCountOnPage > 0) {
                overallStatus = 'incomplete';
                console.log(`⚠️ Incomplete (${unfilledCountOnPage}/${totalEmployeesForDate})`);
            }
        }
        else {
            // ===== OVERTIME BADGE LOGIC =====
            // Use page_wise_status from AJAX response IF available and fresh
            if (overtimePageWiseStatus && overtimePageWiseStatus[dateStr]) {
                const pageStatus = overtimePageWiseStatus[dateStr];
                
                console.log('📊 Using page_wise_status from AJAX:', {
                    dateStr: dateStr,
                    status: pageStatus.status,
                    regular_saved: pageStatus.regular?.saved || 0,
                    regular_submitted: pageStatus.regular?.submitted || 0,
                    holiday_saved: pageStatus.holiday?.saved || 0,
                    holiday_submitted: pageStatus.holiday?.submitted || 0,
                    unfilled: pageStatus.unfilled || 0
                });
                
                // ===== COMBINE REGULAR AND HOLIDAY COUNTS =====
                const totalSaved = (pageStatus.regular?.saved || 0) + (pageStatus.holiday?.saved || 0);
                const totalSubmitted = (pageStatus.regular?.submitted || 0) + (pageStatus.holiday?.submitted || 0);
                const totalUnfilled = pageStatus.unfilled || 0;
                const totalForDate = totalSaved + totalSubmitted + totalUnfilled;
                
                console.log('📊 Combined counts:', {
                    totalSaved: totalSaved,
                    totalSubmitted: totalSubmitted,
                    totalUnfilled: totalUnfilled,
                    totalForDate: totalForDate
                });
                
                // ===== DETERMINE OVERALL STATUS BASED ON COMBINED COUNTS =====
                if (totalForDate > 0) {
                    if (totalSubmitted === totalForDate) {
                        overallStatus = 'sub';
                        console.log(`✅ All submitted (${totalSubmitted}/${totalForDate})`);
                    } else if (totalSaved > 0 || totalSubmitted > 0) {
                        overallStatus = 'save';
                        console.log(`📋 Has data (${totalSaved} saved, ${totalSubmitted} submitted)`);
                    } else if (totalUnfilled > 0) {
                        overallStatus = 'incomplete';
                        console.log(`⚠️ Incomplete (${totalUnfilled}/${totalForDate})`);
                    }
                }
                
                // ===== UPDATE STATUS MAP WITH COMBINED DATA =====
                savedCountOnPage = totalSaved;
                submittedCountOnPage = totalSubmitted;
                unfilledCountOnPage = totalUnfilled;
            } else {
                // ===== FALLBACK: Calculate from cached data if page_wise_status not available =====
                console.log('⚠️ page_wise_status not available, calculating from cached data');
                
                if (submittedCountOnPage === totalEmployeesForDate) {
                    overallStatus = 'sub';
                    console.log(`✅ All submitted (${submittedCountOnPage}/${totalEmployeesForDate})`);
                } else if (savedCountOnPage > 0 || submittedCountOnPage > 0) {
                    overallStatus = 'save';
                    console.log(`📋 Has data (${savedCountOnPage} saved, ${submittedCountOnPage} submitted)`);
                } else if (unfilledCountOnPage > 0) {
                    overallStatus = 'incomplete';
                    console.log(`⚠️ Incomplete (${unfilledCountOnPage}/${totalEmployeesForDate})`);
                }
            }
        }
    }
    
    statusMap[dateStr] = {
        status: overallStatus,
        saved: savedCountOnPage,
        submitted: submittedCountOnPage,
        unfilled: unfilledCountOnPage,
        transferred: transferredCountOnPage,
        total: totalEmployeesForDate
    };
    
    console.log(`🏷️ Status Map Updated for ${dateStr} (${isOvertime ? 'Overtime' : 'Attendance'}):`, statusMap[dateStr]);
}

function getDateStatus(dateStr, isOvertime = false) {
    const statusMap = isOvertime ? overtimeStatusByDate : attendanceStatusByDate;
    const statusData = statusMap[dateStr];
    
    if (!statusData) {
        return null;
    }
    
    return statusData.status;
}

function generateDateColumnHeader(dateStr, displayDate, isHoliday, isOvertime = false) {
    const dayName = moment(dateStr, 'YYYY-MM-DD').format('ddd');
    const isFuture = isFutureDate(dateStr);
    
    const status = getDateStatus(dateStr, isOvertime);
    const statusData = isOvertime ? overtimeStatusByDate[dateStr] : attendanceStatusByDate[dateStr];
    
    console.log(`🏷️ Generating header for ${dateStr}:`, {
        status: status,
        statusData: statusData,
        isOvertime: isOvertime,
        isFuture: isFuture,
        isAdmin: isAdminUser,
        isHoliday: isHoliday
    });
    
    // ===== FOR ATTENDANCE TAB: Don't show status badge for holidays =====
    let statusBadge = '';
    if (!isHoliday || isOvertime) {  // ===== Only show badge if NOT holiday OR if it's overtime =====
        if (status === 'save') {
            statusBadge = '<span class="status-badge saved">Saved</span>';
        } else if (status === 'sub') {
            statusBadge = '<span class="status-badge submitted">Submitted</span>';
        } else if (status === 'mixed') {
            statusBadge = '<span class="status-badge mixed">Pending</span>';
        } else if (status === 'incomplete') {
            statusBadge = '<span class="status-badge incomplete">Incomplete</span>';
        }
    }
    
    if (isRegularUser) {
        if (isHoliday) {
            return `
                <div class="date-header holiday-header ${isFuture ? 'future-date' : ''}">
                    <div class="day-name">${dayName}</div>
                    ${displayDate}
                    <div class="holiday-label">HOLIDAY</div>
                    ${statusBadge}
                </div>
            `;
        } else {
            return `
                <div class="date-header ${isFuture ? 'future-date' : ''}">
                    <div class="day-name">${dayName}</div>
                    ${displayDate}
                    ${statusBadge}
                </div>
            `;
        }
    }
    
    if (isHoliday) {
        // ===== FOR ATTENDANCE HOLIDAYS: No buttons =====
        if (!isOvertime) {
            return `
                <div class="date-header holiday-header ${isFuture ? 'future-date' : ''}">
                    <div class="day-name">${dayName}</div>
                    ${displayDate}
                    <div class="holiday-label">HOLIDAY</div>
                    ${statusBadge}
                </div>
            `;
        }
        // ===== FOR OVERTIME HOLIDAYS: Show buttons =====
        else {
            return `
                <div class="date-header holiday-header ${isFuture ? 'future-date' : ''}">
                    <div class="day-name">${dayName}</div>
                    ${displayDate}
                    <div class="holiday-label">HOLIDAY</div>
                    ${statusBadge}
                    <div class="button-group">
                        ${generateOvertimeButtons(dateStr, status, isFuture, isOvertime)}
                    </div>
                </div>
            `;
        }
    }
    
    // ===== NON-HOLIDAY DATES =====
    if (!isOvertime) {
        // ===== ATTENDANCE NON-HOLIDAY: Show buttons =====
        return `
            <div class="date-header ${isFuture ? 'future-date' : ''}">
                <div class="day-name">${dayName}</div>
                ${displayDate}
                ${statusBadge}
                <div class="button-group">
                    ${generateAttendanceButtons(dateStr, status, isFuture, isOvertime)}
                </div>
            </div>
        `;
    } else {
        // ===== OVERTIME NON-HOLIDAY: Show buttons =====
        return `
            <div class="date-header ${isFuture ? 'future-date' : ''}">
                <div class="day-name">${dayName}</div>
                ${displayDate}
                ${statusBadge}
                <div class="button-group">
                    ${generateOvertimeButtons(dateStr, status, isFuture, isOvertime)}
                </div>
            </div>
        `;
    }
}

// ===== ATTENDANCE BUTTONS (KEEP ORIGINAL LOGIC) =====
function generateAdminAttendanceButtons(dateStr, status, isFuture) {
    const holidays = window.holidaysData || cachedHolidaysData || {};
    const isHoliday = holidays[dateStr] === 'holiday';
    
    // ===== NO BUTTONS FOR ATTENDANCE HOLIDAYS =====
    if (isHoliday) {
        return '';
    }
    
    if (isFuture) {
        return `
            <button class="save-date-btn" data-date="${dateStr}" disabled style="opacity: 0.5;">
                <i class="fa fa-floppy-o"></i>
                <span class="button-tooltip">Save</span>
            </button>
            <button class="update-date-btn" data-date="${dateStr}" disabled style="opacity: 0.5;">
                <i class="fa fa-pencil"></i>
                <span class="button-tooltip">Update</span>
            </button>
        `;
    }
    
    const saveClass = (status === 'save' || status === 'incomplete' || status === 'mixed') ? 'saved' : '';
    const updateClass = status === 'sub' ? 'updated' : '';
    
    return `
        <button class="save-date-btn ${saveClass}" data-date="${dateStr}">
            <i class="fa fa-floppy-o"></i>
            <span class="button-tooltip">Save</span>
        </button>
        <button class="update-date-btn ${updateClass}" data-date="${dateStr}">
            <i class="fa fa-pencil"></i>
            <span class="button-tooltip">Update</span>
        </button>
    `;
}

function generateSupervisorAttendanceButtons(dateStr, status, isFuture) {
    const holidays = window.holidaysData || cachedHolidaysData || {};
    const isHoliday = holidays[dateStr] === 'holiday';
    
    // ===== NO BUTTONS FOR ATTENDANCE HOLIDAYS =====
    if (isHoliday) {
        return '';
    }
    
    if (isFuture) {
        return `
            <button class="save-date-btn" data-date="${dateStr}" disabled style="opacity: 0.5;">
                <i class="fa fa-floppy-o"></i>
                <span class="button-tooltip">Save</span>
            </button>
            <button class="submit-date-btn" data-date="${dateStr}" disabled style="opacity: 0.5;">
                <i class="fa fa-paper-plane-o"></i>
                <span class="button-tooltip">Submit</span>
            </button>
        `;
    }
    
    let saveDisabled = false;
    let submitDisabled = false;
    let saveClass = '';
    let submitClass = '';

    if (status === 'sub') {
        saveDisabled = true;
        submitDisabled = true;
        submitClass = 'submitted';
        console.log(`✅ Supervisor Attendance: Status is 'sub' - DISABLING both buttons for ${dateStr}`);
    } else if (status === 'mixed') {
        saveDisabled = false;
        submitDisabled = false;
        saveClass = 'saved';
        console.log(`🔀 Supervisor Attendance: Status is 'mixed' - ENABLING both buttons for ${dateStr}`);
    } else {
        if (status === 'save') {
            saveClass = 'saved';
            console.log(`📋 Supervisor Attendance: Status is 'save' - ENABLING both buttons for ${dateStr}`);
        } else if (status === 'incomplete') {
            console.log(`⚠️ Supervisor Attendance: Status is 'incomplete' - ENABLING both buttons (user can save/submit remaining) for ${dateStr}`);
        } else {
            console.log(`⚪ Supervisor Attendance: Status is null - ENABLING both buttons for initial entry ${dateStr}`);
        }
    }
    
    return `
        <button class="save-date-btn ${saveClass}" data-date="${dateStr}" ${saveDisabled ? 'disabled' : ''}>
            <i class="fa fa-floppy-o"></i>
            <span class="button-tooltip">Save</span>
        </button>
        <button class="submit-date-btn ${submitClass}" data-date="${dateStr}" ${submitDisabled ? 'disabled' : ''}>
            <i class="fa fa-paper-plane-o"></i>
            <span class="button-tooltip">Submit</span>
        </button>
    `;
}

// ===== OVERTIME BUTTONS (NEW LOGIC - BASED ON action_type) =====
function generateAdminOvertimeButtons(dateStr, status, isFuture) {
    if (isFuture) {
        return `
            <button class="save-overtime-date-btn" data-date="${dateStr}" disabled style="opacity: 0.5;">
                <i class="fa fa-floppy-o"></i>
                <span class="button-tooltip">Save</span>
            </button>
            <button class="update-overtime-date-btn" data-date="${dateStr}" disabled style="opacity: 0.5;">
                <i class="fa fa-pencil"></i>
                <span class="button-tooltip">Update</span>
            </button>
        `;
    }
    
    // ===== OVERTIME: Show saved badge for 'save' or 'incomplete' status =====
    const saveClass = (status === 'save' || status === 'incomplete') ? 'saved' : '';
    const updateClass = status === 'sub' ? 'updated' : '';
    
    return `
        <button class="save-overtime-date-btn ${saveClass}" data-date="${dateStr}">
            <i class="fa fa-floppy-o"></i>
            <span class="button-tooltip">Save</span>
        </button>
        <button class="update-overtime-date-btn ${updateClass}" data-date="${dateStr}">
            <i class="fa fa-pencil"></i>
            <span class="button-tooltip">Update</span>
        </button>
    `;
}

function generateSupervisorOvertimeButtons(dateStr, status, isFuture) {
    if (isFuture) {
        return `
            <button class="save-overtime-date-btn" data-date="${dateStr}" disabled style="opacity: 0.5;">
                <i class="fa fa-floppy-o"></i>
                <span class="button-tooltip">Save</span>
            </button>
            <button class="submit-overtime-date-btn" data-date="${dateStr}" disabled style="opacity: 0.5;">
                <i class="fa fa-paper-plane-o"></i>
                <span class="button-tooltip">Submit</span>
            </button>
        `;
    }
    
    let saveDisabled = false;
    let submitDisabled = false;
    let saveClass = '';
    let submitClass = '';

    // ===== OVERTIME: Use page_wise_status logic =====
    if (status === 'sub') {
        // ===== ALL SUBMITTED - DISABLE BOTH =====
        saveDisabled = true;
        submitDisabled = true;
        submitClass = 'submitted';
        console.log(`✅ Supervisor Overtime: Status is 'sub' - DISABLING both buttons for ${dateStr}`);
    } else if (status === 'save') {
        // ===== HAS SAVED DATA - ENABLE BOTH, SHOW SAVED BADGE =====
        saveDisabled = false;
        submitDisabled = false;
        saveClass = 'saved';
        console.log(`📋 Supervisor Overtime: Status is 'save' - ENABLING both buttons with SAVED badge for ${dateStr}`);
    } else if (status === 'incomplete') {
        // ===== INCOMPLETE (NO DATA) - ENABLE BOTH =====
        saveDisabled = false;
        submitDisabled = false;
        console.log(`⚠️ Supervisor Overtime: Status is 'incomplete' - ENABLING both buttons for ${dateStr}`);
    } else {
        // ===== NULL STATUS - ENABLE BOTH =====
        saveDisabled = false;
        submitDisabled = false;
        console.log(`⚪ Supervisor Overtime: Status is null - ENABLING both buttons for initial entry ${dateStr}`);
    }
    
    return `
        <button class="save-overtime-date-btn ${saveClass}" data-date="${dateStr}" ${saveDisabled ? 'disabled' : ''}>
            <i class="fa fa-floppy-o"></i>
            <span class="button-tooltip">Save</span>
        </button>
        <button class="submit-overtime-date-btn ${submitClass}" data-date="${dateStr}" ${submitDisabled ? 'disabled' : ''}>
            <i class="fa fa-paper-plane-o"></i>
            <span class="button-tooltip">Submit</span>
        </button>
    `;
}

function generateDateCellContent(dateStr, row, isHoliday, isOvertime) {
    const isFuture = isFutureDate(dateStr);
    
    let showDropdown = true;
    let transferInfo = null;
    let isEmployeeTransferredToSelectedDept = false;
    
    if (!isRegularUser) {
        const deptSelectValue = isOvertime ? 
            $('#overtimeSub_departmentSelect').val() : 
            $('#sub_departmentSelect').val();
        
        let selectedDepartments = [];
        
        try {
            if (deptSelectValue && deptSelectValue.startsWith('[')) {
                selectedDepartments = JSON.parse(deptSelectValue);
            } else if (deptSelectValue) {
                selectedDepartments = [deptSelectValue];
            }
        } catch (e) {
            selectedDepartments = [];
        }
        
        if (selectedDepartments.length > 0) {
            const employeePermanentDept = row.sub_department;
            const employeeTempDept = row.temp_sub_department;
            
            // ===== CHECK IF EMPLOYEE HAS VALID TRANSFER DATA =====
            const hasValidTransfer = !!(employeeTempDept && row.transfer_from_date && row.transfer_to_date);
            
            // ===== CHECK IF EMPLOYEE IS IN TRANSFER PERIOD ON THIS DATE =====
            const isInTransferPeriod = hasValidTransfer && isEmployeeInTransferredDeptOnDate(row, dateStr);
            
            console.log(`🔍 Transfer Check for ${row.gid} on ${dateStr}:`, {
                hasTransferData: hasValidTransfer,
                isInTransferPeriod: isInTransferPeriod,
                permanentDept: employeePermanentDept,
                tempDept: employeeTempDept,
                selectedDepts: selectedDepartments,
                transferFromDate: row.transfer_from_date,
                transferToDate: row.transfer_to_date
            });
            
            // ===== CASE 1: EMPLOYEE IS IN TRANSFER PERIOD =====
            if (isInTransferPeriod && employeeTempDept) {
                console.log(`📋 Employee ${row.gid} IS in transfer period on ${dateStr}`);
                
                // ===== VIEWING TEMPORARY DEPARTMENT =====
                if (selectedDepartments.includes(employeeTempDept)) {
                    // ✅ SHOW DROPDOWN - Employee is in temp dept during transfer
                    showDropdown = true;
                    isEmployeeTransferredToSelectedDept = true;
                    console.log(`✅ ${row.gid}: SHOWING in TEMP dept (${employeeTempDept}) - IN TRANSFER PERIOD`);
                } 
                // ===== VIEWING PERMANENT DEPARTMENT =====
                else if (selectedDepartments.includes(employeePermanentDept)) {
                    // ❌ SHOW N/A - Employee is transferred out of permanent dept
                    showDropdown = false;
                    transferInfo = {
                        toDept: employeeTempDept,
                        fromDept: employeePermanentDept,
                        isTransferred: true,
                        isInTransferPeriod: true,
                        transferFromDate: row.transfer_from_date,
                        transferToDate: row.transfer_to_date,
                        daysRemaining: calculateDaysRemaining(row.transfer_to_date)
                    };
                    console.log(`⏭️ ${row.gid}: SHOWING N/A in PERM dept (${employeePermanentDept}) - TRANSFERRED to ${employeeTempDept}`);
                } else {
                    // ===== NOT VIEWING EITHER DEPARTMENT =====
                    showDropdown = false;
                    console.log(`⏭️ ${row.gid}: Not in selected departments`);
                }
            } 
            // ===== CASE 2: EMPLOYEE IS NOT IN TRANSFER PERIOD =====
            else {
                console.log(`📋 Employee ${row.gid} NOT in transfer period on ${dateStr}`);
                
                // ===== VIEWING PERMANENT DEPARTMENT =====
                if (selectedDepartments.includes(employeePermanentDept)) {
                    // ✅ SHOW DROPDOWN - Employee is in permanent dept (no transfer or outside transfer period)
                    showDropdown = true;
                    console.log(`✅ ${row.gid}: SHOWING in PERM dept (${employeePermanentDept}) - NO TRANSFER OR OUTSIDE TRANSFER PERIOD`);
                } 
                // ===== VIEWING TEMPORARY DEPARTMENT (OUTSIDE TRANSFER PERIOD) =====
                else if (selectedDepartments.includes(employeeTempDept) && employeeTempDept) {
                    // ❌ SHOW N/A - Employee not in temp dept on this date (outside transfer period)
                    showDropdown = false;
                    transferInfo = {
                        currentDept: employeePermanentDept,
                        tempDept: employeeTempDept,
                        isTransferred: false,
                        isInTransferPeriod: false,
                        transferFromDate: row.transfer_from_date,
                        transferToDate: row.transfer_to_date,
                        daysRemaining: calculateDaysRemaining(row.transfer_to_date)
                    };
                    console.log(`⏭️ ${row.gid}: SHOWING N/A in TEMP dept (${employeeTempDept}) - NOT in transfer period`);
                } else {
                    // ===== NOT VIEWING EITHER DEPARTMENT =====
                    showDropdown = false;
                    console.log(`⏭️ ${row.gid}: Not in selected departments`);
                }
            }
        }
    }
    
    // ===== SHOW HOLIDAY "DAY OFF" FOR ATTENDANCE =====
    if (isHoliday && !isOvertime && showDropdown) {
        const disabledAttr = isFuture ? 'disabled' : '';
        const disabledClass = isFuture ? 'future-disabled' : '';
        
        return `
            <div class="holiday-cell ${isFuture ? 'future-date-cell' : ''}" 
                data-date="${dateStr}"
                data-gid="${row.gid}"
                data-emp-type="${row.employment_type}"
                style="background: var(--siemens-gray-light); padding: 10px; border-radius: 4px; display: flex; align-items: center; justify-content: center; min-height: 45px; ${isFuture ? 'opacity: 0.6;' : ''}">
                <div class="holiday-text" style="color: var(--siemens-gray-dark); font-weight: 600; font-size: 13px; text-align: center;">Day Off</div>
            </div>
        `;
    }
    
    // ===== SHOW OVERTIME INPUT FOR HOLIDAY (OVERTIME TAB) =====
    if (isHoliday && isOvertime && showDropdown) {
        const holidayClass = "holiday-overtime-cell";
        const futureClass = isFuture ? "future-date-cell" : "";
        const disabledAttr = (isRegularUser || isFuture) ? 'disabled readonly' : '';
        const disabledInputClass = isFuture ? 'future-disabled' : '';
        
        return `
            <div class="${holidayClass} ${futureClass}" style="display: flex; flex-direction: column; align-items: center; justify-content: flex-start; min-height: 45px; gap: 4px; padding: 4px;">
                <input type="number" 
                    class="overtime-input form-control ${disabledInputClass}" 
                    data-date="${dateStr}" 
                    data-gid="${row.gid}"
                    placeholder="Hours" 
                    min="0" 
                    max="24" 
                    step="0.5" 
                    ${disabledAttr}
                    style="width: 100%; padding: 8px; text-align: center;">
            </div>
        `;
    }
    
    // ===== SHOW N/A CELL FOR NON-APPLICABLE DEPARTMENTS =====
    if (!showDropdown && transferInfo) {
        const tempDept = row.temp_sub_department;
        const permanentDept = row.sub_department;
        
        // ===== CASE 1: EMPLOYEE TRANSFERRED - VIEWING PERMANENT DEPT =====
        if (transferInfo.isTransferred && transferInfo.isInTransferPeriod) {
            const daysRemaining = transferInfo.daysRemaining;
            const daysText = daysRemaining ? `${daysRemaining.text}` : 'Transfer ended';
            
            const transferFromDate = moment(row.transfer_from_date, 'YYYY-MM-DD').format('DD MMM YYYY');
            const transferToDate = moment(row.transfer_to_date, 'YYYY-MM-DD').format('DD MMM YYYY');
            const dateRange = `${transferFromDate} to ${transferToDate}`;
            
            return `
                <div class="not-in-dept-cell transfer-info-cell ${isFuture ? 'future-date-cell' : ''}" 
                    data-date="${dateStr}"
                    data-gid="${row.gid}"
                    style="display: flex; align-items: center; justify-content: center; height: 100%; gap: 6px; position: relative; min-height: 45px; ${isFuture ? 'opacity: 0.6;' : ''}">
                    
                    <div class="not-in-dept-text" style="font-weight: 600; color: #999; font-size: 13px;">N/A</div>
                    
                    <div class="transfer-info-icon-wrapper" 
                        style="position: relative; display: inline-flex; align-items: center; justify-content: center; cursor: help;">
                        
                        <i class="fa fa-info-circle" 
                           style="color: var(--siemens-teal); font-size: 14px; transition: all 0.2s ease;"
                           title="Employee transferred to ${tempDept}"></i>
                        
                        <div class="transfer-info-tooltip">
                            <div class="transfer-info-tooltip-header">
                                <i class="fa fa-exchange"></i>
                                📋 Employee Transferred
                            </div>
                            
                            <div class="transfer-info-tooltip-content">
                                
                                <div class="transfer-info-tooltip-row">
                                    <span class="transfer-info-tooltip-label">📍 From Dept:</span>
                                    <span class="transfer-info-tooltip-value">${permanentDept || 'N/A'}</span>
                                </div>
                                
                                <div class="transfer-info-tooltip-row">
                                    <span class="transfer-info-tooltip-label">📍 To Dept:</span>
                                    <span class="transfer-info-tooltip-value" style="color: var(--siemens-teal); font-weight: 700;">${tempDept || 'N/A'}</span>
                                </div>
                                
                                <div class="transfer-info-tooltip-row">
                                    <span class="transfer-info-tooltip-label">📅 Duration:</span>
                                    <span class="transfer-info-tooltip-value">${dateRange}</span>
                                </div>
                                
                                <div class="transfer-info-tooltip-row">
                                    <span class="transfer-info-tooltip-label">⏱️ Status:</span>
                                    <span class="transfer-info-tooltip-value status">${daysText}</span>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        // ===== CASE 2: EMPLOYEE NOT IN TRANSFER PERIOD - VIEWING TEMP DEPT =====
        else if (!transferInfo.isInTransferPeriod && transferInfo.tempDept) {
            const transferFromDate = moment(row.transfer_from_date, 'YYYY-MM-DD').format('DD MMM YYYY');
            const transferToDate = moment(row.transfer_to_date, 'YYYY-MM-DD').format('DD MMM YYYY');
            const dateRange = `${transferFromDate} to ${transferToDate}`;
            
            return `
                <div class="not-in-dept-cell transfer-info-cell ${isFuture ? 'future-date-cell' : ''}" 
                    data-date="${dateStr}"
                    data-gid="${row.gid}"
                    style="display: flex; align-items: center; justify-content: center; height: 100%; gap: 6px; position: relative; min-height: 45px; ${isFuture ? 'opacity: 0.6;' : ''}">
                    
                    <div class="not-in-dept-text" style="font-weight: 600; color: #999; font-size: 13px;">N/A</div>
                    
                    <div class="transfer-info-icon-wrapper" 
                        style="position: relative; display: inline-flex; align-items: center; justify-content: center; cursor: help;">
                        
                        <i class="fa fa-info-circle" 
                           style="color: var(--siemens-teal); font-size: 14px; transition: all 0.2s ease;"
                           title="Employee not in this department on this date"></i>
                        
                        <div class="transfer-info-tooltip">
                            <div class="transfer-info-tooltip-header">
                                <i class="fa fa-calendar-times-o"></i>
                                📋 Not in Department
                            </div>
                            
                            <div class="transfer-info-tooltip-content">
                                
                                <div class="transfer-info-tooltip-row">
                                    <span class="transfer-info-tooltip-label">📍 Permanent Dept:</span>
                                    <span class="transfer-info-tooltip-value" style="color: var(--siemens-teal); font-weight: 700;">${permanentDept || 'N/A'}</span>
                                </div>
                                
                                <div class="transfer-info-tooltip-row">
                                    <span class="transfer-info-tooltip-label">📍 Temporary Dept:</span>
                                    <span class="transfer-info-tooltip-value">${transferInfo.tempDept || 'N/A'}</span>
                                </div>
                                
                                <div class="transfer-info-tooltip-row">
                                    <span class="transfer-info-tooltip-label">📅 Transfer Period:</span>
                                    <span class="transfer-info-tooltip-value">${dateRange}</span>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        // ===== REGULAR N/A (NO TRANSFER) =====
        else {
            return `
                <div class="not-in-dept-cell ${isFuture ? 'future-date-cell' : ''}" 
                    data-date="${dateStr}"
                    data-gid="${row.gid}"
                    style="display: flex; align-items: center; justify-content: center; height: 100%; gap: 8px; min-height: 45px; ${isFuture ? 'opacity: 0.6;' : ''}">
                    
                    <div class="not-in-dept-text" style="font-weight: 600; color: #999; font-size: 13px;">N/A</div>
                </div>
            `;
        }
    }
    
    // ===== SHOW ACTUAL INPUT FIELDS FOR APPLICABLE DEPARTMENTS =====
        if (isOvertime) {
            const futureClass = isFuture ? "future-date-cell" : "";
            const disabledAttr = (isRegularUser || isFuture) ? 'disabled readonly' : '';
            const disabledInputClass = isFuture ? 'future-disabled' : '';
            
            return `
                <div class="${futureClass}" style="display: flex; flex-direction: column; align-items: center; justify-content: flex-start; min-height: 45px; gap: 4px; padding: 4px;">
                    <input type="number" 
                        class="overtime-input form-control ${disabledInputClass}" 
                        data-date="${dateStr}" 
                        data-gid="${row.gid}"
                        placeholder="Hours" 
                        min="0" 
                        max="24" 
                        step="0.5" 
                        ${disabledAttr}
                        style="width: 100%; padding: 8px; text-align: center;">
                </div>
            `;
        }
    
    // ===== ATTENDANCE DROPDOWN =====
    return `
        <div class="attendance-cell ${isFuture ? 'future-date-cell' : ''}" 
            data-emp-type="${row.employment_type}" 
            data-joined-date="${row.joined}" 
            data-date="${dateStr}"
            data-gid="${row.gid}"
            data-is-transferred="${isEmployeeTransferredToSelectedDept}"
            style="display: flex; flex-direction: column; align-items: center; justify-content: flex-start; min-height: 45px; gap: 4px; padding: 4px;">
            ${createAttendanceDropdown(dateStr, row, isFuture)}
        </div>
    `;
}

function getAttendanceHours(value, dateStr = null, employmentType = null) {
    const option = attendanceOptions.find(opt => opt.value === value);
    let hours = option ? option.hours : 0;
    
    if (dateStr && employmentType && value === 'general_shift') {
        const dayOfWeek = moment(dateStr, 'YYYY-MM-DD').day();
        
        if (dayOfWeek === 6) {
            hours = 4.25;
        }
    }
    
    return hours;
}

function createAttendanceDropdown(dateStr, employeeData, shouldDisable = false) {
    let selectHTML = '';
    const isFuture = isFutureDate(dateStr);
    const disabledAttr = ((shouldDisable && !isAdminUser) || isFuture || isRegularUser) ? 'disabled' : '';
    const disabledClass = ((shouldDisable && !isAdminUser) || isFuture || isRegularUser) ? 'future-disabled' : '';
    
    let allocatedShift = employeeData.allocated_shift || null;
    const hasShiftAllocation = employeeData.has_shift_allocation || false;
    
    if (allocatedShift && shiftValueMap[allocatedShift]) {
        allocatedShift = shiftValueMap[allocatedShift];
    }
    
    selectHTML += `<select class="attendance-select ${disabledClass}" 
                            data-date="${dateStr}" 
                            data-gid="${employeeData.gid}"
                            data-allocated-shift="${allocatedShift || ''}"
                            data-has-allocation="${hasShiftAllocation}"
                            ${disabledAttr}>`;

    if (!hasShiftAllocation || !allocatedShift) {
        selectHTML += `<option value="">-- Select Status --</option>`;
    }
    
    attendanceOptions.forEach(option => {
        if (option.value !== '') {
            const isSelected = (allocatedShift && option.value === allocatedShift) ? 'selected' : '';
            
            selectHTML += `<option value="${option.value}" 
                                   data-hours="${option.hours}" 
                                   data-class="${option.class}"
                                   ${isSelected}>${option.text}
                        </option>`;
        }
    });
    
    selectHTML += '</select>';
    
    return selectHTML;
}

function rebuildPaginationButtons(tableId, paginationInfo) {
    const $table = $(tableId);
    const $wrapper = $table.closest('.dataTables_wrapper');
    const $paginate = $wrapper.find('.dataTables_paginate');
    
    if ($paginate.length === 0) {
        return;
    }
    
    const totalPages = paginationInfo.total_pages || 1;
    const currentPage = paginationInfo.current_page || 1;
    
    console.log('📊 Rebuilding pagination:', {
        totalPages: totalPages,
        currentPage: currentPage,
        total: paginationInfo.total
    });
    
    let $paginateButtonGroup = $paginate.find('.paginate_button');
    
    if ($paginateButtonGroup.length === 0) {
        return;
    }
    
    $paginate.find('.paginate_button').not('.previous, .next, .first, .last').remove();
    
    const $nextBtn = $paginate.find('.paginate_button.next');
    
    for (let i = 1; i <= totalPages; i++) {
        const isCurrentPage = i === currentPage;
        const buttonClass = isCurrentPage ? 'paginate_button current' : 'paginate_button';
        const $pageBtn = $(`<a href="#" class="${buttonClass}" data-page="${i}">${i}</a>`);
        
        if ($nextBtn.length > 0) {
            $pageBtn.insertBefore($nextBtn);
        } else {
            $paginate.append($pageBtn);
        }
    }
    
    console.log('✅ Pagination buttons rebuilt - showing pages 1 to', totalPages);
}

// ===== SEARCH FILTER IMPLEMENTATION =====

function initializeSearchFilter(isOvertime = false) {
    const tableId = isOvertime ? '#table_overtime_items' : '#table_all_items';
    const $table = $(tableId);
    
    if ($table.length === 0) {
        console.warn('⚠️ Table not found:', tableId);
        return;
    }
    
    // ===== CREATE SEARCH INPUT IF NOT EXISTS =====
    const $wrapper = $table.closest('.dataTables_wrapper');
    let $searchBox = $wrapper.find('.dataTables_filter input[type="search"]');
    
    if ($searchBox.length === 0) {
        console.warn('⚠️ Search box not found in DataTable wrapper');
        return;
    }
    
    // ===== REMOVE OLD EVENT HANDLERS =====
    $searchBox.off('keyup.search');
    
    // ===== BIND NEW SEARCH HANDLER =====
    $searchBox.on('keyup.search', debounce(function() {
        const searchTerm = $(this).val().toLowerCase().trim();
        
        console.log('🔍 Search initiated:', {
            searchTerm: searchTerm,
            isOvertime: isOvertime,
            tableId: tableId
        });
        
        performTableSearch(searchTerm, isOvertime);
    }, 300));
}

function performTableSearch(searchTerm, isOvertime = false) {
    const tableId = isOvertime ? '#table_overtime_items' : '#table_all_items';
    const $table = $(tableId);
    const $tbody = $table.find('tbody');
    const $rows = $tbody.find('tr');
    
    console.log('🔍 Performing search:', {
        searchTerm: searchTerm,
        totalRows: $rows.length,
        isOvertime: isOvertime
    });
    
    let visibleCount = 0;
    let hiddenCount = 0;
    
    $rows.each(function() {
        const $row = $(this);
        
        // ===== GET EMPLOYEE NAME AND ID =====
        const employeeName = $row.find('td:eq(0)').text().toLowerCase();
        const employeeId = $row.find('td:eq(1)').text().toLowerCase();
        
        // ===== COMBINE SEARCHABLE TEXT =====
        const searchableText = `${employeeName} ${employeeId}`;
        
        // ===== PERFORM SEARCH =====
        const matches = searchableText.includes(searchTerm);
        
        if (searchTerm === '' || matches) {
            // ===== SHOW ROW =====
            $row.show();
            $row.removeClass('search-hidden');
            visibleCount++;
            
            console.log(`✅ Row visible: ${employeeId} - ${employeeName}`);
        } else {
            // ===== HIDE ROW =====
            $row.hide();
            $row.addClass('search-hidden');
            hiddenCount++;
            
            console.log(`❌ Row hidden: ${employeeId} - ${employeeName}`);
        }
    });
    
    // ===== UPDATE PAGINATION INFO =====
    updateSearchPaginationInfo(visibleCount, hiddenCount, isOvertime);
    
    console.log('📊 Search results:', {
        visible: visibleCount,
        hidden: hiddenCount,
        total: $rows.length
    });
}

function updateSearchPaginationInfo(visibleCount, hiddenCount, isOvertime = false) {
    const tableId = isOvertime ? '#table_overtime_items' : '#table_all_items';
    const $wrapper = $(tableId).closest('.dataTables_wrapper');
    const $info = $wrapper.find('.dataTables_info');
    
    if ($info.length === 0) {
        return;
    }
    
    const totalCount = visibleCount + hiddenCount;
    
    if (hiddenCount > 0) {
        const infoText = `Showing ${visibleCount} of ${totalCount} entries (${hiddenCount} filtered out)`;
        $info.text(infoText);
        
        console.log('📊 Updated pagination info:', infoText);
    } else {
        const infoText = `Showing ${visibleCount} to ${visibleCount} of ${totalCount} entries`;
        $info.text(infoText);
    }
}

function clearSearchFilter(isOvertime = false) {
    const tableId = isOvertime ? '#table_overtime_items' : '#table_all_items';
    const $wrapper = $(tableId).closest('.dataTables_wrapper');
    const $searchBox = $wrapper.find('.dataTables_filter input[type="search"]');
    
    if ($searchBox.length > 0) {
        $searchBox.val('').trigger('keyup.search');
        console.log('✅ Search filter cleared for:', isOvertime ? 'Overtime' : 'Attendance');
    }
}

// ===== INITIALIZE SEARCH FILTERS WHEN TABLE IS CREATED =====
function initializeSearchFiltersAfterTableCreation(isOvertime = false) {
    console.log('🔄 Initializing search filters for:', isOvertime ? 'Overtime' : 'Attendance');
    
    setTimeout(() => {
        initializeSearchFilter(isOvertime);
        console.log('✅ Search filter initialized for:', isOvertime ? 'Overtime' : 'Attendance');
    }, 500);
}

// ===== UPDATE: createServerSidePaginatedTable function =====
function createServerSidePaginatedTable(isOvertime, employees, existingData, paginationInfo = {}) {
    const tableId = isOvertime ? '#table_overtime_items' : '#table_all_items';
    
    if ($.fn.DataTable.isDataTable(tableId)) {
        $(tableId).DataTable().destroy();
    }

    $(tableId).empty();
    
    // ===== REMOVE DUPLICATES FROM EMPLOYEES ARRAY =====
    const uniqueEmployees = [];
    const seenGids = new Set();
    
    employees.forEach(emp => {
        if (!seenGids.has(emp.gid)) {
            uniqueEmployees.push(emp);
            seenGids.add(emp.gid);
        } else {
            console.warn('⚠️ Duplicate employee removed:', emp.gid, emp.name);
        }
    });
    
    console.log('📊 Employees before dedup:', employees.length);
    console.log('📊 Employees after dedup:', uniqueEmployees.length);
    
    const dateColumns = generateDateColumns(selectedStartDate, selectedEndDate, isOvertime);
    
    const columns = [
        {
            title: "Employee Name",
            data: null,
            className: "text-left employee-name-col",
            width: "200px",
            orderable: false,
            render: function(data, type, row) {
                const transferData = formatTempAssignmentInfo(row);
                
                // ===== FIX: Use the correct dropdown based on which table is being rendered =====
                const deptSelectValue = isOvertime ? 
                    $('#overtimeSub_departmentSelect').val() : 
                    $('#sub_departmentSelect').val();
                
                let selectedDepartments = [];
                
                try {
                    if (deptSelectValue && deptSelectValue.startsWith('[')) {
                        selectedDepartments = JSON.parse(deptSelectValue);
                    } else if (deptSelectValue) {
                        selectedDepartments = [deptSelectValue];
                    }
                } catch (e) {
                    selectedDepartments = [];
                }
                
                // ===== SHOW TRANSFER INFO IN BOTH DEPARTMENTS =====
                let html = `<div class="employee-name-wrapper${transferData ? ' has-active-transfer' : ''}">`;
                html += `<span class="emp-name-text" style="color: var(--siemens-text); font-weight: 700;">${row.name}</span>`;
                
                if (transferData) {
                    // ===== SHOW TRANSFER INFO REGARDLESS OF WHICH DEPT IS SELECTED =====
                    html += `<div class="emp-transfer-info">`;
                    html += `<i class="fa fa-exchange"></i>`;
                    
                    // ===== DETERMINE CURRENT VIEWING CONTEXT =====
                    const isViewingTempDept = selectedDepartments.includes(transferData.temp_dept);
                    
                    if (isViewingTempDept) {
                        // ===== VIEWING TEMP DEPT: Show "From Permanent → To Temporary" =====
                        html += `<span class="transfer-text">${transferData.permanent_dept} → <strong>${transferData.temp_dept}</strong></span>`;
                    } else {
                        // ===== VIEWING PERMANENT DEPT: Show "To Temporary ← From Permanent" =====
                        html += `<span class="transfer-text"><strong>${transferData.permanent_dept}</strong> → ${transferData.temp_dept}</span>`;
                    }
                    
                    html += `</div>`;
                    html += `<div class="transfer-duration">`;
                    html += `${transferData.transfer_from} to ${transferData.transfer_to}`;
                    html += `</div>`;
                    
                    if (transferData.days_remaining) {
                        const daysInfo = transferData.days_remaining;
                        const badgeClass = daysInfo.status === 'today' ? 'transfer-last-day' : 'transfer-active';
                        html += `<div class="transfer-days-badge ${badgeClass}">`;
                        html += `⏱️ ${daysInfo.text}`;
                        html += `</div>`;
                    }
                }
                
                html += `</div>`;
                return html;
            }
        },
        {
            title: "Employee ID",
            data: "gid",
            className: "text-center",
            width: "110px",
            orderable: false
        },
        {
            title: isOvertime ? "Total OT Hours" : "Actual Man Hours",
            data: null,
            className: "text-center",
            width: "130px",
            orderable: false,
            render: function(data, type, row) {
                return `<span class="${isOvertime ? 'overtime-total' : 'actual_man_hours-total'}">0</span>`;
            }
        },
        ...dateColumns
    ];

    const table = $(tableId).DataTable({
        destroy: true,
        data: uniqueEmployees,  // ===== USE DEDUPLICATED ARRAY =====
        columns: columns,
        pageLength: paginationInfo.per_page || 20,
        scrollX: false,
        scrollY: false,
        scrollCollapse: false,
        autoWidth: false,
        responsive: false,
        ordering: false,
        paging: true,
        lengthMenu: [[10, 20, 50, 100], [10, 20, 50, 100]],
        dom: '<"top"lf>rt<"bottom"ip>',
        language: {
            lengthMenu: "_MENU_",
            search: "",
            searchPlaceholder: "Search employee name or ID...",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next »",
                previous: "« Previous"
            }
        },
        columnDefs: [
            {
                targets: "_all",
                className: "text-center"
            },
            {
                targets: 0,
                className: "text-left"
            }
        ],
        createdRow: function(row, data, dataIndex) {
            $(row).attr('data-employment-type', data.employment_type);
            $(row).attr('data-gid', data.gid);
            
            if (isTransferActive(data)) {
                const daysInfo = calculateDaysRemaining(data.transfer_to_date);
                
                $(row).attr('data-temp-dept', data.temp_sub_department);
                $(row).attr('data-transfer-from', data.transfer_from_date);
                $(row).attr('data-transfer-to', data.transfer_to_date);
                $(row).attr('data-permanent-dept', data.sub_department);
                $(row).attr('data-days-remaining', daysInfo ? daysInfo.remaining : 0);
                $(row).attr('data-transfer-status', daysInfo ? daysInfo.status : 'completed');
                
                $(row).addClass('has-temp-transfer');
                
                if (daysInfo && daysInfo.status === 'today') {
                    $(row).addClass('transfer-last-day');
                }
            }
        },

        initComplete: function(settings, json) {
            const totalRecords = paginationInfo.total || 0;
            const filteredRecords = paginationInfo.filtered || totalRecords;
            const totalPages = paginationInfo.total_pages || 1;
            
            settings._iRecordsTotal = totalRecords;
            settings._iRecordsDisplay = filteredRecords;
            
            settings.fnRecordsTotal = function() { return totalRecords; };
            settings.fnRecordsDisplay = function() { return filteredRecords; };
            
            $(tableId + '_wrapper .dataTables_length label').prepend('<span style="margin-right: 4px;">Show</span>');
            $(tableId + '_wrapper .dataTables_length label').append('<span style="margin-left: 4px;">entries per page</span>');
            $(tableId + '_wrapper .dataTables_filter label').prepend('<span style="margin-right: 4px;">Search:</span>');
            
            // ===== ⭐ RESTORE SEARCH VALUE =====
            restoreSearchValue(isOvertime);
            
            // ===== Initialize search functionality =====
            initializeDataTableSearch();
            
            if (isOvertime) {
                $('#overtimeDetailsegment').show();
                
                let overtimeDataToPopulate = existingData;
                
                if (typeof existingData === 'object' && !Array.isArray(existingData)) {
                    if (existingData.overtime || existingData.holiday_overtime) {
                        overtimeDataToPopulate = [];
                        if (Array.isArray(existingData.overtime)) {
                            overtimeDataToPopulate = overtimeDataToPopulate.concat(existingData.overtime);
                        }
                        if (Array.isArray(existingData.holiday_overtime)) {
                            overtimeDataToPopulate = overtimeDataToPopulate.concat(existingData.holiday_overtime);
                        }
                    }
                }
                
                console.log('📋 Populating overtime data - Total records:', 
                    Array.isArray(overtimeDataToPopulate) ? overtimeDataToPopulate.length : 0);
                populateExistingOvertimeData(overtimeDataToPopulate);
            } else {
                $('#detailsegment').show();
                console.log('📋 Populating attendance data from existingData');
                populateExistingAttendanceData(existingData);
                updateDisplayedActualManHours();
            }
            
            setTimeout(() => {
                console.log('🔄 Applying leave data to table...');
                applyCachedLeaveData(isOvertime);
            }, 200);
            
            setTimeout(() => {
                console.log('🔄 Applying responsive layout...');
                applyResponsiveTableLayout(isOvertime);
            }, 300);
            
            updatePaginationInfoDisplay(isOvertime, paginationInfo);
            
            setTimeout(() => {
                rebuildPaginationButtons(tableId, paginationInfo);
                updateDataTablesPaginationInfo(tableId, paginationInfo);
            }, 100);
        },
        drawCallback: function(settings) {
            const totalRecords = paginationInfo.total || 0;
            const filteredRecords = paginationInfo.filtered || totalRecords;
            
            settings._iRecordsTotal = totalRecords;
            settings._iRecordsDisplay = filteredRecords;
            
            if (!isOvertime) {
                updateDisplayedActualManHours();
                populateExistingAttendanceData(attendanceCachedData);
                ensureCorrectFieldStates(false);
            } else {
                populateExistingOvertimeData(overtimeCachedData);
                ensureCorrectFieldStates(true);
            }
            
            setTimeout(() => {
                applyCachedLeaveData(isOvertime);
            }, 100);
            
            updateDataTablesPaginationInfo(tableId, paginationInfo);
            
            rebuildPaginationButtons(tableId, paginationInfo);
            
            // ===== REINITIALIZE SEARCH FILTER AFTER DRAW =====
            initializeSearchFilter(isOvertime);
            
            setTimeout(() => {
                refreshAllDateBadges(isOvertime);
            }, 200);
        }
    });

    if (isOvertime) {
        overtimeDataTable = table;
    } else {
        dataTable = table;
    }

    table.on('draw', function() {
        if (!isOvertime) {
            updateDisplayedActualManHours();
            populateExistingAttendanceData(attendanceCachedData);
        } else {
            populateExistingOvertimeData(overtimeCachedData);
        }
        
        setTimeout(() => {
            applyCachedLeaveData(isOvertime);
        }, 100);
    });
    
    // ===== PAGINATION CLICK EVENT =====
    $(document).off('click.pagination').on('click.pagination', '.dataTables_paginate .paginate_button', function(e) {
        e.preventDefault();
        
        const $button = $(this);
        
        if ($button.hasClass('disabled')) {
            return;
        }
        
        const $activeTab = $('#attendanceTab.active, #overtimeTab.active');
        const activeTabId = $activeTab.attr('id');
        const isOvertimeTab = (activeTabId === 'overtimeTab');
        
        const tableId_local = isOvertimeTab ? '#table_overtime_items' : '#table_all_items';
        const $table = $(tableId_local);
        
        if ($table.length === 0) {
            return;
        }
        
        const dataTableInstance = isOvertimeTab ? overtimeDataTable : dataTable;
        
        if (!dataTableInstance) {
            return;
        }
        
        let pageNum = parseInt($button.text());
        
        if (isNaN(pageNum)) {
            const currentPageInfo = dataTableInstance.page.info();
            const currentPage = currentPageInfo.page + 1;
            const totalPages = currentPageInfo.pages;
            
            if ($button.text().includes('Next') || $button.text().includes('›')) {
                pageNum = currentPage + 1;
            } else if ($button.text().includes('Previous') || $button.text().includes('‹')) {
                pageNum = currentPage - 1;
            } else if ($button.text().includes('First')) {
                pageNum = 1;
            } else if ($button.text().includes('Last')) {
                pageNum = totalPages;
            }
        }
        
        if (isNaN(pageNum) || pageNum < 1) {
            return;
        }
        
        if (isOvertimeTab) {
            currentOvertimePage = pageNum;
        } else {
            currentAttendancePage = pageNum;
        }
        
        let subDepartmentSelect, employmentTypeSelect, groupTypeSelect;
        
        if (isOvertimeTab) {
            subDepartmentSelect = $('#overtimeSub_departmentSelect').val();
            employmentTypeSelect = $('#overtimeemployment_typeSelect').val();
            groupTypeSelect = $('#overtimeGroup_typeSelect').val();
        } else {
            subDepartmentSelect = $('#sub_departmentSelect').val();
            employmentTypeSelect = $('#employment_typeSelect').val();
            groupTypeSelect = $('#group_typeSelect').val();
        }
        
        console.log('📄 ===== PAGINATION CLICK =====');
        console.log('📄 Active Tab ID:', activeTabId);
        console.log('📄 Is Overtime Tab:', isOvertimeTab);
        console.log('📄 Table ID:', tableId_local);
        console.log('📄 Parameters:', {
            subDepartment: subDepartmentSelect,
            groupType: groupTypeSelect,
            isOvertime: isOvertimeTab,
            page: isOvertimeTab ? currentOvertimePage : currentAttendancePage,
            action: isOvertimeTab ? 'fetchOvertimeData' : 'fetchAttendanceData'
        });
        
        loadEmployees(
            subDepartmentSelect,
            groupTypeSelect,
            isOvertimeTab
        );
        
        console.log('📄 ===== PAGINATION CLICK COMPLETE =====');
    });

    // ===== PAGE LENGTH CHANGE EVENT =====
    $(document).off('length.dt').on('length.dt', 'table', function(e, settings, len) {
        const $activeTab = $('#attendanceTab.active, #overtimeTab.active');
        const activeTabId = $activeTab.attr('id');
        const isOvertimeTab = (activeTabId === 'overtimeTab');
        
        console.log('📄 ===== PAGE LENGTH CHANGE =====');
        console.log('📄 Active Tab ID:', activeTabId);
        console.log('📄 Is Overtime Tab:', isOvertimeTab);
        console.log('📄 New Page Length:', len);
        
        const $table = $(settings.nTable);
        const tableId_local = $table.attr('id');
        
        console.log('📄 Table ID:', tableId_local);
        
        if (isOvertimeTab) {
            overtimePageLength = len;
            currentOvertimePage = 1;
            console.log('✅ Updated overtimePageLength:', overtimePageLength);
            console.log('✅ Reset currentOvertimePage to: 1');
        } else {
            attendancePageLength = len;
            currentAttendancePage = 1;
            console.log('✅ Updated attendancePageLength:', attendancePageLength);
            console.log('✅ Reset currentAttendancePage to: 1');
        }
        
        let subDepartmentSelect, groupTypeSelect;
        
        if (isOvertimeTab) {
            subDepartmentSelect = $('#overtimeSub_departmentSelect').val();
            groupTypeSelect = $('#overtimeGroup_typeSelect').val();
            
            console.log('📄 Overtime Filters:', {
                subDepartment: subDepartmentSelect,
                groupType: groupTypeSelect
            });
        } else {
            subDepartmentSelect = $('#sub_departmentSelect').val();
            groupTypeSelect = $('#group_typeSelect').val();
            
            console.log('📄 Attendance Filters:', {
                subDepartment: subDepartmentSelect,
                groupType: groupTypeSelect
            });
        }
        
        console.log('📄 ===== CALLING loadEmployees =====');
        console.log('📄 Parameters:', {
            subDepartment: subDepartmentSelect,
            groupType: groupTypeSelect,
            isOvertime: isOvertimeTab,
            page: isOvertimeTab ? currentOvertimePage : currentAttendancePage,
            pageLength: isOvertimeTab ? overtimePageLength : attendancePageLength,
            action: isOvertimeTab ? 'fetchOvertimeData' : 'fetchAttendanceData'
        });
        
        loadEmployees(
            subDepartmentSelect,
            groupTypeSelect,
            isOvertimeTab
        );
        
        console.log('📄 ===== PAGE LENGTH CHANGE COMPLETE =====');
    });
}

function updateDataTablesPaginationInfo(tableId, paginationInfo) {
    const $table = $(tableId);
    const $wrapper = $table.closest('.dataTables_wrapper');
    const $info = $wrapper.find('.dataTables_info');
    
    if ($info.length === 0) {
        return;
    }
    
    const totalRecords = paginationInfo.total || 0;
    const perPage = paginationInfo.per_page || 20;
    const currentPage = paginationInfo.current_page || 1;
    
    const start = ((currentPage - 1) * perPage) + 1;
    const end = Math.min(currentPage * perPage, totalRecords);
    
    const infoText = `Showing ${start} to ${end} of ${totalRecords} entries`;
    $info.text(infoText);
    
    console.log('📊 Updated pagination info:', {
        text: infoText,
        total: totalRecords,
        current_page: currentPage,
        per_page: perPage
    });
}

function updatePaginationInfoDisplay(isOvertime, paginationInfo) {
    const tabId = isOvertime ? '#overtimeTab' : '#attendanceTab';
    const pageInfo = paginationInfo;
    
    if (!pageInfo || !pageInfo.total) {
        return;
    }
    
    let $paginationInfoBox = $(tabId).find('.pagination-info');
    
    if ($paginationInfoBox.length === 0) {
        const $tableContainer = $(tabId).find('.table-responsive-container');
        if ($tableContainer.length > 0) {
            $paginationInfoBox = $(`
                <div class="pagination-info">
                    <div class="pagination-info-text">
                        <span class="pagination-text"></span>
                        <span class="pagination-badge"></span>
                    </div>
                </div>
            `);
            $tableContainer.before($paginationInfoBox);
        }
    }
    
    const paginationText = pageInfo.pagination_text || `Showing ${pageInfo.current_page} to ${pageInfo.total_pages} of ${pageInfo.total} entries`;
    const pageBadgeText = `Page ${pageInfo.current_page} of ${pageInfo.total_pages}`;
    
    $paginationInfoBox.find('.pagination-text').text(paginationText);
    $paginationInfoBox.find('.pagination-badge').text(pageBadgeText);
    
    console.log('📊 Updated pagination display:', {
        text: paginationText,
        badge: pageBadgeText,
        currentPage: pageInfo.current_page,
        totalPages: pageInfo.total_pages,
        total: pageInfo.total
    });
}

function calculateInitialManHours() {
    $('#table_all_items tbody tr').each(function() {
        let totalHours = 0;
        const employeeRow = $(this);
        const employmentType = employeeRow.attr('data-employment-type');
        
        $(this).find('.attendance-select').each(function() {
            const selectedValue = $(this).val();
            const dateStr = $(this).data('date');
            
            if (selectedValue) {
                totalHours += getAttendanceHours(selectedValue, dateStr, employmentType);
            }
        });
        
        $(this).find('.actual_man_hours-total').text(totalHours);
    });
}

function populateExistingAttendanceData(existingData) {
    console.log('📋 populateExistimeAttendanceData called');
    console.log('Data type:', typeof existingData);
    console.log('Is Array:', Array.isArray(existingData));

    if (!existingData || (Array.isArray(existingData) && existingData.length === 0)) {
        console.log('⚠️ No attendance data provided');
        $('#table_all_items tbody tr').each(function() {
            const row = $(this);
            row.find('.attendance-select').each(function() {
                const $select = $(this);
                const dateStr = $select.data('date');
                const isFuture = isFutureDate(dateStr);
                const allocatedShift = $select.data('allocated-shift');
                const hasAllocation = $select.data('has-allocation');
                
                // ===== REMOVE OLD BADGES =====
                $select.next('.can-update-badge').remove();
                $select.next('.admin-edit-indicator').remove();
                
                if (isFuture) {
                    $select.prop('disabled', true);
                    $select.addClass('future-disabled');
                } else {
                    if (hasAllocation && allocatedShift) {
                        $select.val(allocatedShift);
                    }
                    
                    const selectedOption = $select.find('option:selected');
                    const cssClass = selectedOption.attr('data-class');
                    
                    $select.removeClass('first-shift second-shift third_shift general-shift leave late half-day-first half-day-second outdoor training absent holiday dept-submitted future-disabled');
                    if (cssClass) {
                        $select.addClass(cssClass);
                    }
                    
                    $select.prop('disabled', false);
                    $select.removeClass('dept-submitted');
                }
            });
        });
        
        updateDisplayedActualManHours();
        return;
    }
    
    if (!Array.isArray(existingData)) {
        if (typeof existingData === 'object') {
            existingData = Object.values(existingData);
        } else {
            updateDisplayedActualManHours();
            return;
        }
    }
    
    const attendanceMap = {};
    const submittedDatesWithData = new Set();
    
    console.log('📊 Processing', existingData.length, 'attendance records');
    
    existingData.forEach(record => {
        const gid = record.gid;
        const date = record.attendance_date;
        
        if (!attendanceMap[gid]) {
            attendanceMap[gid] = {};
        }
        
        attendanceMap[gid][date] = {
            status: record.attendance_status,
            recordStatus: record.status
        };
        
        if (record.status === 'sub') {
            submittedDatesWithData.add(`${gid}|${date}`);
            console.log('📊 Submitted Record Found:', gid, date, record.attendance_status);
        }
    });
    
    console.log('📊 Total Submitted Data Points:', submittedDatesWithData.size);
    console.log('📊 Submitted Set:', Array.from(submittedDatesWithData));
    
    $('#table_all_items tbody tr').each(function() {
        const row = $(this);
        const gid = row.find('td:eq(1)').text().trim();
        
        console.log('🔍 Processing Employee:', gid);
        
        row.find('.attendance-select').each(function() {
            const $select = $(this);
            const dateStr = $select.data('date');
            const isFuture = isFutureDate(dateStr);
            const allocatedShift = $select.data('allocated-shift');
            const hasAllocation = $select.data('has-allocation');
            
            const dataKey = `${gid}|${dateStr}`;
            const hasSubmittedData = submittedDatesWithData.has(dataKey);
            
            console.log('🔍 Checking:', dataKey, '| Submitted:', hasSubmittedData, '| Future:', isFuture);
            
            // ===== REMOVE OLD BADGES FIRST =====
            $select.next('.can-update-badge').remove();
            $select.next('.admin-edit-indicator').remove();
            
            $select.removeClass('dept-submitted admin-editable can-update-badge future-disabled');
            
            // ===== FUTURE DATE HANDLING =====
            if (isFuture) {
                $select.prop('disabled', true);
                $select.addClass('future-disabled');
                console.log('⏳ FUTURE DATE DISABLED:', gid, dateStr);
                return;
            }
            
            if (attendanceMap[gid] && attendanceMap[gid][dateStr]) {
                const recordData = attendanceMap[gid][dateStr];
                const status = recordData.status;
                
                $select.val(status);
                
                const selectedOption = $select.find('option:selected');
                const cssClass = selectedOption.attr('data-class');
                $select.removeClass('first-shift second-shift third_shift general-shift leave late half-day-first half-day-second outdoor training absent holiday dept-submitted');
                if (cssClass) {
                    $select.addClass(cssClass);
                }
                
                $select.addClass('existing-data');
                
                // ===== CRITICAL FIX: Check if data is SUBMITTED (status === 'sub') =====
                if (hasSubmittedData && !isAdminUser) {
                    // ===== NON-ADMIN: DISABLE IF SUBMITTED =====
                    $select.prop('disabled', true);
                    $select.addClass('dept-submitted');
                    console.log('🔒 GREYED OUT (Submitted - Non-Admin):', gid, dateStr, status);
                } else if (isAdminUser && hasSubmittedData) {
                    // ===== ADMIN: ALWAYS EDITABLE EVEN IF SUBMITTED =====
                    $select.prop('disabled', false);
                    $select.addClass('admin-editable');
                    // ===== ADD ONLY ONE BADGE =====
                    if (!$select.next('.can-update-badge').length) {
                        $select.after('<span class="can-update-badge">✓</span>');
                    }
                    console.log('✏️ ADMIN EDITABLE (Submitted):', gid, dateStr);
                } else {
                    // ===== SUPERVISOR: EDITABLE IF NOT SUBMITTED =====
                    $select.prop('disabled', false);
                    console.log('✏️ ENABLED (Not Submitted):', gid, dateStr);
                }
            } else {
                // NO EXISTING DATA - EDITABLE
                if (hasAllocation && allocatedShift) {
                    $select.val(allocatedShift);
                    const selectedOption = $select.find('option:selected');
                    const cssClass = selectedOption.attr('data-class');
                    
                    $select.removeClass('first-shift second-shift third_shift general-shift leave late half-day-first half-day-second outdoor training absent holiday dept-submitted');
                    if (cssClass) {
                        $select.addClass(cssClass);
                    }
                }
                
                $select.prop('disabled', false);
                console.log('✏️ ENABLED (No Data):', gid, dateStr);
            }
        });
    });
    
    const allDates = new Set();
    if (Array.isArray(existingData)) {
        existingData.forEach(record => {
            allDates.add(record.attendance_date);
        });
    }
    
    console.log('📅 All Dates to Update:', Array.from(allDates));
    
    allDates.forEach(dateStr => {
        updateStatusTrackingForDate(dateStr, false);
    });
    
    updateSubmitButtonStates(false);
    updateDisplayedActualManHours();
    
    console.log('✅ populateExistingAttendanceData COMPLETED\n');
}

function populateExistingOvertimeData(response) {
    console.log('📋 populateExistingOvertimeData called');
    console.log('Response type:', typeof response);
    console.log('Is Array:', Array.isArray(response));

    // ===== EXTRACT OVERTIME DATA FROM RESPONSE =====
    let allOvertimeData = [];
    
    if (Array.isArray(response)) {
        allOvertimeData = response;
        console.log('✅ Response is array, records:', allOvertimeData.length);
    } 
    else if (response && typeof response === 'object') {
        if (response.overtime && Array.isArray(response.overtime)) {
            allOvertimeData = allOvertimeData.concat(response.overtime);
            console.log('✅ Found overtime array, records:', response.overtime.length);
        }
        if (response.holiday_overtime && Array.isArray(response.holiday_overtime)) {
            allOvertimeData = allOvertimeData.concat(response.holiday_overtime);
            console.log('✅ Found holiday_overtime array, records:', response.holiday_overtime.length);
        }
    }

    console.log('📊 Total overtime records to populate:', allOvertimeData.length);
    
    // ===== STEP 1: RESET ALL INPUTS =====
    $('#table_overtime_items tbody tr').each(function() {
        const $inputs = $(this).find('.overtime-input');
        $inputs.each(function() {
            const $input = $(this);
            const dateStr = $input.data('date');
            const isFuture = isFutureDate(dateStr);
            
            $input.removeClass('overtime-low overtime-medium overtime-high dept-submitted admin-editable future-disabled');
            
            if (isFuture) {
                $input.prop('disabled', true);
                $input.addClass('future-disabled');
                console.log(`⏳ Future date ${dateStr}: DISABLED`);
            } else {
                $input.prop('disabled', false);
                $input.prop('readonly', false);
            }
        });
    });

    if (allOvertimeData.length === 0) {
        console.warn('⚠️ No overtime data to populate');
        
        $('#table_overtime_items tbody tr').each(function() {
            const row = $(this);
            row.find('.overtime-input').each(function() {
                const $input = $(this);
                const dateStr = $input.data('date');
                const isFuture = isFutureDate(dateStr);
                
                if (!isFuture) {
                    $input.val('0');
                    $input.addClass('existing-data');
                }
            });

            let totalOvertimeHours = 0;
            row.find('.overtime-input').each(function() {
                const val = parseFloat($(this).val()) || 0;
                totalOvertimeHours += val;
            });
            row.find('.overtime-total').text(totalOvertimeHours.toFixed(1));
        });
        
        const allDates = new Set();
        $('#table_overtime_items tbody tr').each(function() {
            $(this).find('.overtime-input').each(function() {
                const dateStr = $(this).data('date');
                if (dateStr) allDates.add(dateStr);
            });
        });
        
        allDates.forEach(dateStr => {
            updateStatusTrackingForDate(dateStr, true);
        });
        
        updateSubmitButtonStates(true);
        return;
    }

    // ===== STEP 2: CREATE MAP OF INDIVIDUAL RECORDS =====
    const overtimeMap = {};
    
    allOvertimeData.forEach((record) => {
        let dateStr = null;
        
        if (record.attendance_date) {
            dateStr = record.attendance_date.includes('T') ? 
                record.attendance_date.split('T')[0] : 
                record.attendance_date;
        } else if (record.date) {
            dateStr = record.date.includes('T') ? 
                record.date.split('T')[0] : 
                record.date;
        }
        
        if (!dateStr || !record.gid) {
            console.warn(`⚠️ Record missing date or GID:`, record);
            return;
        }
        
        const key = `${record.gid}|${dateStr}`;
        const overtimeHours = record.overtime_hours !== null && record.overtime_hours !== undefined ? 
            parseFloat(record.overtime_hours) : 0;
        
        // ===== CRITICAL: Use ot_status for overtime, NOT status =====
        const otStatus = String(record.ot_status || 'save').trim().toLowerCase();
        
        overtimeMap[key] = {
            overtime_hours: overtimeHours,
            ot_status: otStatus,
            attendance_status: record.attendance_status || 'present'
        };
        
        console.log(`📍 Mapped "${key}": hours=${overtimeHours}, ot_status='${otStatus}'`);
    });

    console.log('🗺️ Overtime Map created with', Object.keys(overtimeMap).length, 'entries');

    // ===== STEP 3: POPULATE EACH INPUT BASED ON INDIVIDUAL RECORD OT_STATUS =====
    $('#table_overtime_items tbody tr').each(function() {
        const row = $(this);
        const gid = row.find('td:eq(1)').text().trim();

        if (!gid) {
            console.warn('⚠️ Could not extract GID from row');
            return;
        }

        console.log(`\n👤 Processing employee: ${gid}`);

        const $inputs = row.find('.overtime-input');

        $inputs.each(function() {
            const $input = $(this);
            const dateStr = $input.data('date');
            
            if (!dateStr) {
                console.warn(`⚠️ Input has no date attribute`);
                return;
            }

            const key = `${gid}|${dateStr}`;
            const isFuture = isFutureDate(dateStr);

            console.log(`   📅 ${dateStr}: key="${key}"`);

            // ===== FUTURE DATES: SKIP (ALREADY DISABLED ABOVE) =====
            if (isFuture) {
                console.log(`   ⏳ FUTURE - Already disabled above`);
                return;
            }

            // ===== CHECK IF THIS SPECIFIC GID|DATE HAS DATA =====
            if (overtimeMap[key]) {
                const data = overtimeMap[key];
                const hours = data.overtime_hours;
                const otStatus = data.ot_status;  // ===== USE ot_status =====

                console.log(`   ✅ DATA FOUND: hours=${hours}, ot_status='${otStatus}'`);

                // ===== SET VALUE =====
                $input.val(hours);
                $input.addClass('existing-data');
                
                // ===== APPLY COLOR CODING =====
                $input.removeClass('overtime-low overtime-medium overtime-high');
                if (hours > 0 && hours <= 4) {
                    $input.addClass('overtime-low');
                } else if (hours > 4 && hours <= 8) {
                    $input.addClass('overtime-medium');
                } else if (hours > 8) {
                    $input.addClass('overtime-high');
                }

                // ===== CRITICAL: CHECK INDIVIDUAL RECORD OT_STATUS =====
                // ===== FOR OVERTIME TAB: Only disable if THIS SPECIFIC RECORD's ot_status is 'sub' =====
                if (otStatus === 'sub') {
                    // THIS SPECIFIC EMPLOYEE'S OVERTIME RECORD IS SUBMITTED
                    if (!isAdminUser) {
                        // ===== SUPERVISOR: DISABLE IF SUBMITTED =====
                        $input.prop('disabled', true);
                        $input.prop('readonly', true);
                        $input.addClass('dept-submitted');
                        $input.removeClass('admin-editable');
                        console.log(`   🔒 SUPERVISOR - SUBMITTED (ot_status='sub') - DISABLED`);
                    } else {
                        // ===== ADMIN: ALWAYS EDITABLE EVEN IF SUBMITTED =====
                        $input.prop('disabled', false);
                        $input.prop('readonly', false);
                        $input.removeClass('dept-submitted');
                        $input.addClass('admin-editable');
                        if (!$input.next('.admin-edit-indicator').length) {
                            $input.after('<span class="admin-edit-indicator">✓</span>');
                        }
                        console.log(`   ✏️ ADMIN - SUBMITTED (ot_status='sub') - ENABLED`);
                    }
                } else {
                    // THIS SPECIFIC EMPLOYEE'S OVERTIME RECORD IS SAVED OR OTHER STATUS
                    $input.prop('disabled', false);
                    $input.prop('readonly', false);
                    $input.removeClass('dept-submitted admin-editable');
                    $input.next('.admin-edit-indicator').remove();
                    console.log(`   ✏️ SAVED/OTHER (ot_status='${otStatus}') - ENABLED`);
                }
            } else {
                // NO DATA FOR THIS SPECIFIC GID|DATE
                console.log(`   ⚪ NO DATA - ENABLED`);
                $input.val('0');
                $input.prop('disabled', false);
                $input.prop('readonly', false);
                $input.removeClass('dept-submitted admin-editable');
                $input.addClass('existing-data');
            }
        });

        // ===== UPDATE TOTAL FOR THIS ROW =====
        let totalOvertimeHours = 0;
        row.find('.overtime-input').each(function() {
            const val = parseFloat($(this).val()) || 0;
            totalOvertimeHours += val;
        });
        row.find('.overtime-total').text(totalOvertimeHours.toFixed(1));
    });

    // ===== STEP 4: UPDATE STATUS FOR ALL DATES =====
    const allDates = new Set();
    allOvertimeData.forEach(record => {
        let dateStr = null;
        if (record.attendance_date) {
            dateStr = record.attendance_date.includes('T') ? 
                record.attendance_date.split('T')[0] : 
                record.attendance_date;
        } else if (record.date) {
            dateStr = record.date.includes('T') ? 
                record.date.split('T')[0] : 
                record.date;
        }
        if (dateStr) allDates.add(dateStr);
    });
    
    console.log('\n🔄 Updating status for dates:', Array.from(allDates));
    allDates.forEach(dateStr => {
        updateStatusTrackingForDate(dateStr, true);
    });
    
    updateSubmitButtonStates(true);

    console.log('\n✅ populateExistingOvertimeData COMPLETED\n');
}

function ensureCorrectFieldStates(isOvertime = false) {
    console.log('🔧 Ensuring correct field states for:', isOvertime ? 'Overtime' : 'Attendance');

    const tableId = isOvertime ? '#table_overtime_items' : '#table_all_items';
    const statusByDate = isOvertime ? overtimeStatusByDate : attendanceStatusByDate;

    Object.keys(statusByDate).forEach(dateStr => {
        const status = statusByDate[dateStr];

        if (status === 'sub') {
            // ===== CRITICAL FIX: Always disable submitted dates =====
            if (isOvertime) {
                // ===== FOR OVERTIME: Check user role and use ot_status =====
                if (!isAdminUser) {
                    // ===== SUPERVISOR: DISABLE =====
                    console.log(`🔒 Supervisor - Disabling submitted overtime for date: ${dateStr} (ot_status='sub')`);
                    disableOvertimeInputsForSubmittedDate(dateStr);
                } else {
                    // ===== ADMIN: KEEP EDITABLE =====
                    console.log(`✏️ Admin - Keeping submitted overtime editable for date: ${dateStr} (ot_status='sub')`);
                    enableOvertimeInputsForSavedDate(dateStr);
                }
            } else {
                // ===== FOR ATTENDANCE: Check user role =====
                if (!isAdminUser) {
                    // ===== SUPERVISOR: DISABLE =====
                    console.log(`🔒 Supervisor - Disabling submitted attendance for date: ${dateStr} (status='sub')`);
                    disableAttendanceInputsForSubmittedDate(dateStr);
                } else {
                    // ===== ADMIN: KEEP EDITABLE =====
                    console.log(`✏️ Admin - Keeping submitted attendance editable for date: ${dateStr} (status='sub')`);
                }
            }
        }
    });
}

function ensureCorrectFieldStatesAndRespectSubmitted(isOvertime = false) {
    // Run the original function
    ensureCorrectFieldStates(isOvertime);

    // ===== FIX: Re-enforce submitted state AFTER ensureCorrectFieldStates =====
    const statusByDate = isOvertime ? overtimeStatusByDate : attendanceStatusByDate;

    Object.keys(statusByDate).forEach(dateStr => {
        if (statusByDate[dateStr] === 'sub') {
            console.log(`🔒 Re-enforcing disabled state for submitted date: ${dateStr}`);
            if (isOvertime) {
                disableOvertimeInputsForSubmittedDate(dateStr);
            } else {
                disableAttendanceInputsForSubmittedDate(dateStr);
            }
        }
    });
}

// ===== CORRECTED: updateSubmitButtonStates function =====
function updateSubmitButtonStates(isOvertime = false) {
    const tableId = isOvertime ? '#table_overtime_items' : '#table_all_items';
    
    const uniqueDates = [];
    $(`.save-date-btn, .submit-date-btn, .update-date-btn, .save-overtime-date-btn, .submit-overtime-date-btn, .update-overtime-date-btn`).each(function() {
        const dateStr = $(this).data('date');
        if (!uniqueDates.includes(dateStr)) {
            uniqueDates.push(dateStr);
        }
    });
    
    console.log('🔘 Updating button states for dates:', uniqueDates);
    
    uniqueDates.forEach(dateStr => {
        updateStatusTrackingForDate(dateStr, isOvertime);
        
        const $saveButton = $(`.save-${isOvertime ? 'overtime-' : ''}date-btn[data-date="${dateStr}"]`);
        const $submitButton = $(`.submit-${isOvertime ? 'overtime-' : ''}date-btn[data-date="${dateStr}"]`);
        const $updateButton = $(`.update-${isOvertime ? 'overtime-' : ''}date-btn[data-date="${dateStr}"]`);
        
        const dateStatus = getDateStatus(dateStr, isOvertime);
        const isFuture = isFutureDate(dateStr);
        
        const statusData = isOvertime ? overtimeStatusByDate[dateStr] : attendanceStatusByDate[dateStr];
        
        console.log('🔘 Button State Update:', {
            dateStr: dateStr,
            dateStatus: dateStatus,
            isFuture: isFuture,
            statusData: statusData,
            isAdmin: isAdminUser,
            isOvertime: isOvertime
        });
        
        // ===== RESET ALL BUTTON STYLES =====
        $saveButton.removeClass('saved').css({'background': '', 'border-color': '', 'opacity': ''});
        $submitButton.removeClass('submitted').css({'background': '', 'border-color': '', 'opacity': ''});
        $updateButton.removeClass('updated').css({'background': '', 'border-color': '', 'opacity': ''});

        if (isAdminUser) {
            // ===== ADMIN USER LOGIC =====
            if (isFuture) {
                // FUTURE DATE: Disable all buttons
                $saveButton.prop('disabled', true).css('opacity', '0.5');
                $updateButton.prop('disabled', true).css('opacity', '0.5');
                console.log('⏳ Admin - Future date, buttons DISABLED:', dateStr);
            } else {
                // PAST/CURRENT DATE: Enable all buttons
                $saveButton.prop('disabled', false).css('opacity', '1');
                $updateButton.prop('disabled', false).css('opacity', '1');
                console.log('✅ Admin - Buttons ENABLED:', dateStr);
                
                // Show badges based on status
                if (dateStatus === 'save' || dateStatus === 'incomplete') {
                    $saveButton.addClass('saved').css('background', '#C8E6C9').css('border-color', '#388E3C');
                    console.log('📋 Save button marked as SAVED:', dateStr);
                } else if (dateStatus === 'sub') {
                    $updateButton.addClass('updated').css('background', '#C8E6C9').css('border-color', '#388E3C');
                    console.log('✅ Update button marked as SUBMITTED:', dateStr);
                }
            }
        } else {
            // ===== SUPERVISOR/REGULAR USER LOGIC =====
            if (isFuture) {
                // FUTURE DATE: Disable all buttons
                $saveButton.prop('disabled', true).css('opacity', '0.5');
                $submitButton.prop('disabled', true).css('opacity', '0.5');
                console.log('⏳ Supervisor - Future date, buttons DISABLED:', dateStr);
            } 
            else if (dateStatus === 'sub') {
                // ===== ALL SUBMITTED - DISABLE BOTH =====
                $saveButton.prop('disabled', true).css('opacity', '0.5');
                $submitButton.prop('disabled', true).css('opacity', '0.5');
                $submitButton.addClass('submitted').css('background', '#C8E6C9').css('border-color', '#388E3C');
                console.log('✅ Supervisor - All submitted, buttons DISABLED:', dateStr);
            } 
            else {
                // ===== NOT ALL SUBMITTED - ENABLE BOTH =====
                $saveButton.prop('disabled', false).css('opacity', '1');
                $submitButton.prop('disabled', false).css('opacity', '1');
                
                // Show SAVED badge if has data
                if (dateStatus === 'save') {
                    $saveButton.addClass('saved').css('background', '#C8E6C9').css('border-color', '#388E3C');
                    console.log('📋 Supervisor - Has saved data, SAVED badge shown:', dateStr);
                } else if (dateStatus === 'incomplete') {
                    console.log('⚠️ Supervisor - Incomplete, buttons ENABLED:', dateStr);
                } else {
                    console.log('⚪ Supervisor - No data, buttons ENABLED for initial entry:', dateStr);
                }
            }
        }
    });
    
    console.log('🔘 Button states updated for:', uniqueDates);
}

function initializeDataTable(isOvertime = false) {
    const tableId = isOvertime ? '#table_overtime_items' : '#table_all_items';
    const employees = isOvertime ? currentOvertimeEmployees : currentEmployees;
    
    if (!employees || employees.length === 0) {
        return;
    }

    if ($.fn.DataTable.isDataTable(tableId)) {
        $(tableId).DataTable().destroy();
        $(tableId + ' tbody').empty();
    }

    $(tableId).empty();

    loadEmployees(isOvertime ? $('#overtimeSub_departmentSelect').val() : $('#sub_departmentSelect').val(), 
                  isOvertime ? $('#overtimeGroup_typeSelect').val() : $('#group_typeSelect').val(), 
                  isOvertime);
}

function updateDisplayedActualManHours() {
    if (!dataTable) return;
    
    dataTable.rows().nodes().each(function(rowNode) {
        let totalHours = 0;
        const employmentType = $(rowNode).attr('data-employment-type');
        
        $(rowNode).find('.attendance-select').each(function() {
            const selectedValue = $(this).val();
            const dateStr = $(this).data('date');
            
            if (selectedValue) {
                totalHours += getAttendanceHours(selectedValue, dateStr, employmentType);
            }
        });
        
        $(rowNode).find('.actual_man_hours-total').text(totalHours);
    });
}

function checkForUnfilledAttendanceEntries(dateStr) {
    const unfilled = {
        count: 0,
        employees: []
    };
    
    if (!dataTable) {
        console.error('❌ DataTable not initialized');
        return unfilled;
    }
    
    const pageData = dataTable.rows({ page: 'current' }).data();
    const currentPageInfo = dataTable.page.info();
    const pageNum = currentPageInfo.page + 1;
    const totalPages = currentPageInfo.pages;
    
    console.log('🔍 Checking unfilled attendance entries for:', dateStr);
    console.log('📄 Page:', pageNum, 'of', totalPages);
    console.log('👥 Employees on page:', pageData.length);
    
    pageData.each(function(rowData, index) {
        const gid = rowData.gid;
        const name = rowData.name;
        
        // ===== CHECK IF EMPLOYEE IS TRANSFERRED ON THIS DATE =====
        const isTransferred = isEmployeeInTransferredDeptOnDate(rowData, dateStr);
        
        if (isTransferred) {
            console.log(`⏭️ Employee ${gid}: TRANSFERRED - skipping validation`);
            return;  // Skip transferred employees
        }
        
        // Get the select element
        const $select = $(`select.attendance-select[data-gid="${gid}"][data-date="${dateStr}"]`);
        
        if ($select.length === 0) {
            return;
        }
        
        // ===== SKIP ONLY GREYED OUT (TRULY SUBMITTED) =====
        const isGreyedOut = $select.hasClass('dept-submitted');
        
        if (isGreyedOut) {
            console.log(`🔒 Employee ${gid}: GREYED OUT (Truly Submitted) - skipping validation`);
            return;  // SKIP VALIDATION FOR GREYED OUT ONLY
        }
        
        // Check if entry is filled
        let hasData = false;
        
        // Check live data
        if (liveAttendanceData[gid] && liveAttendanceData[gid][dateStr]) {
            hasData = true;
            console.log(`✅ Employee ${gid}: HAS LIVE DATA`);
        }
        
        // Check cached data (any status - saved or submitted)
        if (!hasData && attendanceCachedData && Array.isArray(attendanceCachedData)) {
            const cachedRecord = attendanceCachedData.find(record => 
                record.gid === gid && record.attendance_date === dateStr
            );
            
            if (cachedRecord && cachedRecord.attendance_status) {
                hasData = true;
                console.log(`✅ Employee ${gid}: HAS CACHED DATA (status: ${cachedRecord.status})`);
            }
        }
        
        // Check allocated shift
        if (!hasData) {
            const allocatedShift = rowData.allocated_shift || null;
            if (allocatedShift) {
                hasData = true;
                console.log(`✅ Employee ${gid}: HAS ALLOCATED SHIFT`);
            }
        }
        
        // Check form input
        if (!hasData) {
            const value = $select.val();
            if (value && value !== '') {
                hasData = true;
                console.log(`✅ Employee ${gid}: HAS FORM INPUT`);
            }
        }
        
        if (!hasData) {
            unfilled.count++;
            unfilled.employees.push({
                gid: gid,
                name: name,
                date: dateStr,
                page: pageNum,
                totalPages: totalPages
            });
            console.log(`❌ UNFILLED: ${gid} - ${name}`);
        }
    });
    
    console.log('📊 Unfilled entries found:', unfilled.count);
    return unfilled;
}

function checkForUnfilledOvertimeEntries(dateStr) {
    const unfilled = {
        count: 0,
        employees: []
    };
    
    if (!overtimeDataTable) {
        console.error('❌ DataTable not initialized');
        return unfilled;
    }
    
    const pageData = overtimeDataTable.rows({ page: 'current' }).data();
    const currentPageInfo = overtimeDataTable.page.info();
    const pageNum = currentPageInfo.page + 1;
    const totalPages = currentPageInfo.pages;
    
    console.log('🔍 Checking unfilled overtime entries for:', dateStr);
    console.log('📄 Page:', pageNum, 'of', totalPages);
    console.log('👥 Employees on page:', pageData.length);
    
    const holidays = window.holidaysData || cachedHolidaysData || {};
    const isHoliday = holidays[dateStr] === 'holiday';
    
    console.log('📅 Is Holiday:', isHoliday);
    console.log('🔍 Holiday data available:', Object.keys(holidays).length, 'dates');
    
    pageData.each(function(rowData, index) {
        const gid = rowData.gid;
        const name = rowData.name;
        
        // Check if employee is transferred on this date
        const isTransferred = isEmployeeInTransferredDeptOnDate(rowData, dateStr);
        
        if (isTransferred) {
            console.log(`⏭️ Employee ${gid}: TRANSFERRED - skipping`);
            return;
        }
        
        // Get the input element
        const $input = $(`input.overtime-input[data-gid="${gid}"][data-date="${dateStr}"]`);
        
        if ($input.length === 0) {
            console.log(`⚠️ Input not found for ${gid}`);
            return;
        }
        
        // ===== SKIP ONLY GREYED OUT (TRULY SUBMITTED) =====
        const isGreyedOut = $input.hasClass('dept-submitted');
        
        if (isGreyedOut) {
            console.log(`🔒 Employee ${gid}: GREYED OUT (Truly Submitted) - skipping validation`);
            return;
        }
        
        // ===== FOR NON-HOLIDAY DAYS: CHECK IF ATTENDANCE IS REQUIRED AND SUBMITTED =====
        if (!isHoliday) {
            let hasSubmittedAttendance = false;
            
            if (Array.isArray(attendanceCachedData)) {
                const attendanceRecord = attendanceCachedData.find(record => {
                    if (!record || !record.gid) return false;
                    return record.gid === gid && 
                           record.attendance_date === dateStr && 
                           record.status === 'sub';
                });
                
                if (attendanceRecord) {
                    hasSubmittedAttendance = true;
                    console.log(`✅ Employee ${gid}: HAS SUBMITTED ATTENDANCE RECORD`);
                }
            }
            
            if (!hasSubmittedAttendance) {
                unfilled.count++;
                unfilled.employees.push({
                    gid: gid,
                    name: name,
                    date: dateStr,
                    page: pageNum,
                    totalPages: totalPages,
                    reason: 'Attendance must be submitted before overtime submission on non-holiday days'
                });
                console.log(`❌ UNFILLED: ${gid} - ${name} (no submitted attendance on non-holiday day)`);
                return;
            }
        }
        
        // ===== CHECK IF OVERTIME ENTRY IS FILLED =====
        let hasData = false;
        
        // Check live overtime data
        if (liveOvertimeData[gid] && liveOvertimeData[gid][dateStr] !== undefined) {
            hasData = true;
            console.log(`✅ Employee ${gid}: HAS LIVE OVERTIME DATA`);
        }
        
        // Check cached overtime data - FIX: Handle undefined date
        if (!hasData && overtimeCachedData && Array.isArray(overtimeCachedData)) {
            const cachedRecord = overtimeCachedData.find(record => {
                if (!record || !record.gid) return false;
                
                // ===== FIX: Safely extract date =====
                let recordDate = record.date || record.attendance_date;
                if (!recordDate) return false;
                
                const cleanDate = recordDate.includes('T') ? recordDate.split('T')[0] : recordDate;
                return record.gid === gid && cleanDate === dateStr;
            });
            
            if (cachedRecord && cachedRecord.overtime_hours !== null && cachedRecord.overtime_hours !== undefined) {
                hasData = true;
                console.log(`✅ Employee ${gid}: HAS CACHED OVERTIME DATA (status: ${cachedRecord.status})`);
            }
        }
        
        // Check form input
        if (!hasData) {
            const value = $input.val();
            if (value !== '' && value !== null && value !== undefined) {
                hasData = true;
                console.log(`✅ Employee ${gid}: HAS FORM INPUT`);
            }
        }
        
        if (!hasData) {
            unfilled.count++;
            unfilled.employees.push({
                gid: gid,
                name: name,
                date: dateStr,
                page: pageNum,
                totalPages: totalPages,
                reason: 'Overtime hours not entered'
            });
            console.log(`❌ UNFILLED: ${gid} - ${name} (no overtime hours)`);
        }
    });
    
    console.log('📊 Unfilled overtime entries found:', unfilled.count);
    console.log('📋 Unfilled employees:', unfilled.employees);
    
    return unfilled;
}

function highlightUnfilledEntries(dateStr, isOvertime = false) {
    const unfilled = isOvertime ? 
        checkForUnfilledOvertimeEntries(dateStr) : 
        checkForUnfilledAttendanceEntries(dateStr);
    
    if (unfilled.count === 0) {
        return;
    }
    
    const tableId = isOvertime ? '#table_overtime_items' : '#table_all_items';
    
    unfilled.employees.forEach(emp => {
        const $row = $(`${tableId} tbody tr`).filter(function() {
            return $(this).find('td:eq(1)').text().trim() === emp.gid;
        });
        
        if ($row.length > 0) {
            const $cell = isOvertime ?
                $row.find(`.overtime-input[data-date="${dateStr}"]`).closest('div') :
                $row.find(`.attendance-select[data-date="${dateStr}"]`).closest('div');
            
            if ($cell.length > 0) {
                $cell.css({
                    'background': '#FFE5E5',
                    'border': '2px solid #FF6B6B',
                    'border-radius': '4px',
                    'animation': 'pulse-error 1.5s infinite'
                });
            }
        }
    });
}

function clearHighlightedEntries(dateStr, isOvertime = false) {
    const tableId = isOvertime ? '#table_overtime_items' : '#table_all_items';
    const selector = isOvertime ? '.overtime-input' : '.attendance-select';
    
    $(tableId).find(`${selector}[data-date="${dateStr}"]`).closest('div').css({
        'background': '',
        'border': '',
        'border-radius': '',
        'animation': ''
    });
}

function saveAttendanceForDate(dateStr) {
    console.log('💾 Save Attendance clicked for date:', dateStr);
    
    debugAttendanceData(dateStr);
    
    if (isFutureDate(dateStr)) {
        showCustomAlert({
            type: 'error',
            title: 'Cannot Save',
            message: `Cannot save data for future date: ${dateStr}`,
            note: '⚠️ Only past or current dates can be saved.'
        });
        return;
    }
    
    const holidays = window.holidaysData || cachedHolidaysData || {};
    const isHoliday = holidays[dateStr] === 'holiday';
    
    if (!isAdminUser && isHoliday) {
        showCustomAlert({
            type: 'warning',
            title: 'Holiday Save Blocked',
            message: `Cannot save attendance for ${dateStr} as it is a holiday.`,
            note: '📅 Only administrators can save data for holidays.'
        });
        return;
    }
    
    const result = collectCurrentPageAttendanceData(dateStr);
    const dataArray = result.data;
    const missingData = result.missing;
    
    console.log('📊 Collected current page data:', {
        data_count: dataArray.length,
        missing_count: missingData.length,
        current_page: currentAttendancePage,
        total_pages: totalAttendancePages
    });
    
    if (dataArray.length === 0) {
        console.error('❌ No data collected. Debug info:');
        console.log('Live data keys:', Object.keys(liveAttendanceData));
        console.log('Cached data count:', Array.isArray(attendanceCachedData) ? attendanceCachedData.length : 0);
        console.log('Current employees:', currentEmployees.length);
        
        showCustomAlert({
            type: 'info',
            title: 'No Data on This Page',
            message: `No attendance data available on page ${currentAttendancePage} of ${totalAttendancePages} for ${dateStr}.`,
            note: '📋 Please enter attendance data before saving.'
        });
        return;
    }
    
    let confirmMessage = '';
    let confirmNote = '';
    
    if (missingData.length > 0) {
        confirmMessage = `Save attendance for ${dataArray.length} employee(s)? (${missingData.length} employee(s) without data will be skipped)`;
        confirmNote = `Date: ${moment(dateStr, 'YYYY-MM-DD').format('MMMM DD, YYYY')}<br>Page: ${currentAttendancePage} of ${totalAttendancePages}<br>Saving: ${dataArray.length}<br>Skipping: ${missingData.length}`;
    } else {
        confirmMessage = `Save attendance for ${dataArray.length} employee(s) on page ${currentAttendancePage}?`;
        confirmNote = `Date: ${moment(dateStr, 'YYYY-MM-DD').format('MMMM DD, YYYY')}<br>Page: ${currentAttendancePage} of ${totalAttendancePages}<br>Employees: ${dataArray.length}`;
    }
    
    showCustomAlert({
        type: 'info',
        title: 'Confirm Attendance Save',
        message: confirmMessage,
        note: confirmNote,
        onOk: function() {
            proceedWithAttendanceSave(dateStr, dataArray);
        }
    });
}

function proceedWithAttendanceSave(dateStr, dataArray) {
    console.log('🔄 Proceeding with attendance save for:', dateStr, 'Records:', dataArray.length, 'Page:', currentAttendancePage);
    
    const buttonSelector = `.save-date-btn[data-date="${dateStr}"]`;
    const $saveButton = $(buttonSelector);
    
    if ($saveButton.length === 0) {
        console.error('❌ Save button not found:', buttonSelector);
        showCustomAlert({
            type: 'error',
            title: 'Button Not Found',
            message: 'Save button could not be located.',
            note: '🔄 Please refresh the page and try again.'
        });
        return;
    }
    
    const originalButtonHTML = $saveButton.html();
    $saveButton.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
    showLoadingIndicator(true);
    
    const sub_department = currentsub_department;
    const group_type = $('#group_typeSelect').val();
    
    if (!sub_department) {
        showLoadingIndicator(false);
        $saveButton.prop('disabled', false).html(originalButtonHTML);
        showCustomAlert({
            type: 'error',
            title: 'Department Information Missing',
            message: 'Error: Department information is missing.',
            note: '🔄 Please reload the page and try again.'
        });
        return;
    }
    
    if (!group_type) {
        showLoadingIndicator(false);
        $saveButton.prop('disabled', false).html(originalButtonHTML);
        showCustomAlert({
            type: 'error',
            title: 'Group Type Missing',
            message: 'Error: Group type information is missing.',
            note: '📋 Please select a group type and try again.'
        });
        return;
    }
    
    const cleanedData = dataArray.map(record => ({
        employee_name: record.employee_name,
        gid: record.gid,
        date: record.date,
        attendance_status: record.attendance_status,
        actual_man_hours: record.actual_man_hours || 0,
        allocated_shift: record.allocated_shift || null,
        is_allocated: record.is_allocated || 0
    }));
    
    const ajaxData = {
        action: 'submitAttendanceForDate',
        data: JSON.stringify(cleanedData),
        sub_department: sub_department,
        group_type: group_type,
        date: dateStr,
        is_admin: isAdminUser ? 'true' : 'false',
        allow_update: isAdminUser ? 'true' : 'false',
        total_employees: cleanedData.length,
        preserve_overtime: 'true',
        current_page: currentAttendancePage,
        total_pages: totalAttendancePages,
        is_page_wise_save: 'true',
        is_save: 'true'
    };
    
    console.log('📤 Sending AJAX request:', {
        action: ajaxData.action,
        date: dateStr,
        employees: cleanedData.length,
        page: currentAttendancePage,
        totalPages: totalAttendancePages,
        data_sample: cleanedData[0]
    });
    
    $.ajax({
        url: '/dpm/api/PMSController.php',
        method: 'POST',
        data: ajaxData,
        dataType: 'json',
        timeout: 30000,
        success: function(response) {
            console.log('✅ AJAX Success:', response);
            
            showLoadingIndicator(false);
            $saveButton.prop('disabled', false).html(originalButtonHTML);
            
            if (response.success) {
                cleanedData.forEach(record => {
                    if (!attendanceCachedData) attendanceCachedData = [];
                    
                    attendanceCachedData = attendanceCachedData.filter(r => 
                        !(r.gid === record.gid && r.attendance_date === record.date)
                    );
                    
                    attendanceCachedData.push({
                        gid: record.gid,
                        name: record.employee_name,
                        attendance_date: record.date,
                        attendance_status: record.attendance_status,
                        actual_man_hours: record.actual_man_hours,
                        allocated_shift: record.allocated_shift,
                        is_allocated: record.is_allocated,
                        status: 'save'
                    });
                });
                
                // ===== UPDATE STATUS TRACKING =====
                updateStatusTrackingForDate(dateStr, false);
                
                cleanedData.forEach(record => {
                    if (liveAttendanceData[record.gid]) {
                        delete liveAttendanceData[record.gid][dateStr];
                    }
                });
                
                showCustomAlert({
                    type: 'success',
                    title: 'Attendance Saved',
                    message: `✅ Attendance has been saved successfully!`,
                    items: [
                        `Employees: ${cleanedData.length}`,
                        `Date: ${moment(dateStr, 'YYYY-MM-DD').format('MMMM DD, YYYY')}`,
                        `Page: ${currentAttendancePage} of ${totalAttendancePages}`,
                        `Status: SAVED`
                    ],
                    note: response.message || 'You can still edit this data before submitting.',
                    onOk: function() {
                        // ===== RELOAD ATTENDANCE TABLE ONLY =====
                        reloadAttendanceTableWithFiltersIntact(dateStr);
                    }
                });
            } else {
                showCustomAlert({
                    type: 'error',
                    title: 'Save Failed',
                    message: response.message || 'An error occurred while saving.',
                    note: '🔄 Please try again.'
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('❌ AJAX Error:', status, error, xhr);
            console.error('Response text:', xhr.responseText);
            
            showLoadingIndicator(false);
            $saveButton.prop('disabled', false).html(originalButtonHTML);
            
            let errorMessage = 'Unknown error';
            if (status === 'timeout') {
                errorMessage = 'Request timeout - server took too long to respond';
            } else if (status === 'error') {
                errorMessage = `Server error (${xhr.status})`;
            } else if (xhr.status === 0) {
                errorMessage = 'Network error - check your connection';
            }
            
            showCustomAlert({
                type: 'error',
                title: 'Save Error',
                message: `Failed to save attendance for ${cleanedData.length} employee(s) on page ${currentAttendancePage} of ${totalAttendancePages}.`,
                note: `${errorMessage}. Please try again.`
            });
        }
    });
}

function reloadAttendanceTableWithFiltersIntact(dateStr = null) {
    console.log('🔄 Reloading ATTENDANCE table ONLY - Date:', dateStr);

    const subDepartmentSelect = $('#sub_departmentSelect').val();
    const groupTypeSelect = $('#group_typeSelect').val();

    const currentPage = currentAttendancePage;
    const pageLength = attendancePageLength;

    showLoadingIndicator(true);

    if (dateStr) {
        Object.keys(liveAttendanceData).forEach(gid => {
            if (liveAttendanceData[gid][dateStr]) {
                delete liveAttendanceData[gid][dateStr];
            }
        });
        console.log('✅ Cleared live ATTENDANCE data for date:', dateStr);
    }

    // ===== SNAPSHOT OVERTIME STATE BEFORE RELOAD =====
    const overtimeStatusSnapshot = JSON.parse(JSON.stringify(overtimeStatusByDate));
    console.log('📸 Overtime status snapshot taken:', overtimeStatusSnapshot);

    loadEmployees(subDepartmentSelect, groupTypeSelect, false);

    setTimeout(() => {
        if (dateStr) {
            updateStatusTrackingForDate(dateStr, false);
            refreshDateColumnHeader(dateStr, false);
            updateSubmitButtonStates(false);
            refreshAllDateBadges(false);
            ensureCorrectFieldStates(false);
        }

        showLoadingIndicator(false);

        // ===== CRITICAL FIX: Restore overtime states after attendance reload =====
        setTimeout(() => {
            console.log('🔧 Restoring overtime field states after attendance reload...');

            // Restore overtime status from snapshot
            Object.keys(overtimeStatusSnapshot).forEach(date => {
                overtimeStatusByDate[date] = overtimeStatusSnapshot[date];
            });

            // ===== Enforce correct overtime states =====
            enforceOvertimeFieldStates();

            console.log('✅ Overtime states restored and enforced');
        }, 200);  // ← Small delay after attendance reload completes

        console.log('✅ ATTENDANCE table reload complete');
    }, 500);
}

function reloadOvertimeTabWithFiltersPreserved(dateStr = null) {
    console.log('🔄 Reloading OVERTIME table ONLY - Date:', dateStr);

    const subDepartmentSelect = $('#overtimeSub_departmentSelect').val();
    const employmentTypeSelect = $('#overtimeemployment_typeSelect').val();
    const groupTypeSelect = $('#overtimeGroup_typeSelect').val();
    const joinedSelect = $('#overtimejoinedSelect').val();

    const currentPage = currentOvertimePage;
    const pageLength = overtimePageLength;

    showLoadingIndicator(true);

    if (dateStr) {
        Object.keys(liveOvertimeData).forEach(gid => {
            if (liveOvertimeData[gid] && liveOvertimeData[gid][dateStr]) {
                delete liveOvertimeData[gid][dateStr];
            }
        });
        console.log('✅ Cleared live OVERTIME data for date:', dateStr);
    }

    // ===== SNAPSHOT OVERTIME STATE BEFORE RELOAD =====
    const attendanceDataSnapshot = JSON.parse(JSON.stringify(liveAttendanceData));
    console.log('📸 Attendance data snapshot taken');

    loadEmployees(subDepartmentSelect, employmentTypeSelect, groupTypeSelect, joinedSelect, true);

    setTimeout(() => {
        if (dateStr) {
            updateStatusTrackingForDate(dateStr, true);
            refreshDateColumnHeader(dateStr, true);
            updateSubmitButtonStates(true);
            refreshAllDateBadges(true);
            ensureCorrectFieldStates(true);
        }

        showLoadingIndicator(false);

        // ===== CRITICAL FIX: Restore attendance data after overtime reload =====
        setTimeout(() => {
            console.log('🔧 Restoring attendance data after overtime reload...');

            // Restore attendance data from snapshot
            Object.keys(attendanceDataSnapshot).forEach(gid => {
                liveAttendanceData[gid] = attendanceDataSnapshot[gid];
            });

            console.log('✅ Attendance data restored');
        }, 200);  // ← Small delay after overtime reload completes

        console.log('✅ OVERTIME table reload complete');
    }, 500);
}

function submitAttendanceForDate(dateStr) {
    console.log('📤 Submit Attendance clicked for date:', dateStr);
    
    if (isFutureDate(dateStr)) {
        showCustomAlert({
            type: 'error',
            title: 'Cannot Submit',
            message: `Cannot submit data for future date: ${dateStr}`,
            note: '⚠️ Only past or current dates can be submitted.'
        });
        return;
    }
    
    const holidays = window.holidaysData || cachedHolidaysData || {};
    const isHoliday = holidays[dateStr] === 'holiday';
    
    if (!isAdminUser && isHoliday) {
        showCustomAlert({
            type: 'warning',
            title: 'Holiday Submission Blocked',
            message: `Cannot submit attendance for ${dateStr} as it is a holiday.`,
            note: '📅 Only administrators can submit data for holidays.'
        });
        return;
    }
    
    // ===== NEW: CHECK FOR UNFILLED ENTRIES (excluding greyed-out) =====
    const unfilled = checkForUnfilledAttendanceEntries(dateStr);
    
    if (unfilled.count > 0) {
        showCustomAlert({
            type: 'error',
            title: '❌ Incomplete Data - Cannot Submit',
            message: `${unfilled.count} employee(s) on this page have missing attendance data for ${moment(dateStr, 'YYYY-MM-DD').format('MMMM DD, YYYY')}.`,
            items: unfilled.employees.slice(0, 10).map(emp => `${emp.name} (ID: ${emp.gid})`),
            note: unfilled.count > 10 ? 
                `<strong>⚠️ ${unfilled.count - 10} more employee(s) not shown</strong><br>📋 All entries on this page must be filled before submission.` :
                '📋 <strong>All entries on this page must be filled before submission.</strong>'
        });
        return;
    }
    
    const result = collectCurrentPageAttendanceData(dateStr);
    const dataArray = result.data;
    const missingData = result.missing;
    const transferredEmployees = result.transferred;
    const skippedSubmitted = result.skipped;
    
    console.log('📊 Collected current page data:', {
        data_count: dataArray.length,
        missing_count: missingData.length,
        transferred_count: transferredEmployees.length,
        skipped_submitted_count: skippedSubmitted.length,
        current_page: currentAttendancePage,
        total_pages: totalAttendancePages
    });
    
    if (dataArray.length === 0) {
        let message = `No new attendance data to submit on page ${currentAttendancePage} of ${totalAttendancePages} for ${dateStr}.`;
        let note = '📋 ';
        
        if (skippedSubmitted.length > 0) {
            message += ` (${skippedSubmitted.length} employee(s) already submitted)`;
            note += `${skippedSubmitted.length} employee(s) already have submitted data and were skipped. `;
        }
        
        note += 'Please enter new attendance data before submitting.';
        
        showCustomAlert({
            type: 'info',
            title: 'No New Data to Submit',
            message: message,
            note: note
        });
        return;
    }
    
    let confirmMessage = '';
    let confirmNote = '';
    let confirmItems = [];
    
    confirmMessage = `Submit attendance for ${dataArray.length} employee(s) on page ${currentAttendancePage}?`;
    confirmItems.push(`📋 Employees: ${dataArray.length}`);
    confirmItems.push(`📅 Date: ${moment(dateStr, 'YYYY-MM-DD').format('MMMM DD, YYYY')}`);
    confirmItems.push(`📄 Page: ${currentAttendancePage} of ${totalAttendancePages}`);
    
    if (skippedSubmitted.length > 0) {
        confirmItems.push(`⏭️ Skipped (Already Submitted): ${skippedSubmitted.length}`);
    }
    
    confirmItems.push(`✅ Status: All entries verified and complete`);
    
    confirmNote = confirmItems.join('<br>');
    
    showCustomAlert({
        type: 'info',
        title: 'Confirm Attendance Submission',
        message: confirmMessage,
        items: skippedSubmitted.length > 0 ? skippedSubmitted.slice(0, 5).map(emp => `⏭️ ${emp.name} (Already submitted)`) : [],
        note: confirmNote,
        onOk: function() {
            proceedWithAttendanceSubmission(dateStr, dataArray);
        }
    });
}

function proceedWithAttendanceSubmission(dateStr, dataArray) {
    console.log('🔄 Proceeding with attendance submission for:', dateStr, 'Records:', dataArray.length, 'Page:', currentAttendancePage);
    console.log('⚠️ CRITICAL: This will ONLY submit ATTENDANCE - NOT overtime');
    
    const buttonSelector = `.submit-date-btn[data-date="${dateStr}"]`;
    const $submitButton = $(buttonSelector);
    
    if ($submitButton.length === 0) {
        console.error('❌ Submit button not found:', buttonSelector);
        showCustomAlert({
            type: 'error',
            title: 'Button Not Found',
            message: 'Submit button could not be located.',
            note: '🔄 Please refresh the page and try again.'
        });
        return;
    }
    
    const originalButtonHTML = $submitButton.html();
    $submitButton.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
    showLoadingIndicator(true);
    
    const sub_department = currentsub_department;
    const group_type = $('#group_typeSelect').val();
    
    if (!sub_department) {
        showLoadingIndicator(false);
        $submitButton.prop('disabled', false).html(originalButtonHTML);
        showCustomAlert({
            type: 'error',
            title: 'Department Information Missing',
            message: 'Error: Department information is missing.',
            note: '🔄 Please reload the page and try again.'
        });
        return;
    }
    
    if (!group_type) {
        showLoadingIndicator(false);
        $submitButton.prop('disabled', false).html(originalButtonHTML);
        showCustomAlert({
            type: 'error',
            title: 'Group Type Missing',
            message: 'Error: Group type information is missing.',
            note: '📋 Please select a group type and try again.'
        });
        return;
    }
    
    const cleanedData = dataArray.map(record => ({
        employee_name: record.employee_name,
        gid: record.gid,
        date: record.date,
        attendance_status: record.attendance_status,
        actual_man_hours: record.actual_man_hours || 0,
        allocated_shift: record.allocated_shift || null,
        is_allocated: record.is_allocated || 0
    }));
    
    const ajaxData = {
        action: 'submitAttendanceForDate',
        data: JSON.stringify(cleanedData),
        sub_department: sub_department,
        group_type: group_type,
        date: dateStr,
        is_admin: isAdminUser ? 'true' : 'false',
        allow_update: isAdminUser ? 'true' : 'false',
        total_employees: cleanedData.length,
        preserve_overtime: 'true',
        current_page: currentAttendancePage,
        total_pages: totalAttendancePages,
        is_page_wise_submit: 'true',
        is_save: 'false',
        is_submit: 'true',
        do_not_submit_overtime: 'true'  // ===== CRITICAL: Tell backend NOT to submit overtime =====
    };
    
    console.log('📤 Sending AJAX request:', {
        action: ajaxData.action,
        date: dateStr,
        employees: cleanedData.length,
        page: currentAttendancePage,
        totalPages: totalAttendancePages,
        preserve_overtime: ajaxData.preserve_overtime,
        do_not_submit_overtime: ajaxData.do_not_submit_overtime,
        data_sample: cleanedData[0]
    });
    
    $.ajax({
        url: '/dpm/api/PMSController.php',
        method: 'POST',
        data: ajaxData,
        dataType: 'json',
        timeout: 30000,
        success: function(response) {
            console.log('✅ AJAX Success:', response);

            showLoadingIndicator(false);
            $submitButton.prop('disabled', false).html(originalButtonHTML);

            if (response.success) {
                if (response.overtime_submitted === true || response.overtime_submitted === 'true') {
                    console.error('❌ ERROR: Overtime was submitted unexpectedly!');
                    showCustomAlert({
                        type: 'error',
                        title: '⚠️ Unexpected Behavior',
                        message: 'Attendance was submitted, but overtime was also submitted unexpectedly.',
                        note: '🔄 Please contact support.'
                    });
                    return;
                }

                // ===== STEP 1: UPDATE CACHED DATA FIRST =====
                cleanedData.forEach(record => {
                    if (!attendanceCachedData) attendanceCachedData = [];

                    attendanceCachedData = attendanceCachedData.filter(r =>
                        !(r.gid === record.gid && r.attendance_date === record.date)
                    );

                    attendanceCachedData.push({
                        gid: record.gid,
                        name: record.employee_name,
                        attendance_date: record.date,
                        attendance_status: record.attendance_status,
                        actual_man_hours: record.actual_man_hours,
                        allocated_shift: record.allocated_shift,
                        is_allocated: record.is_allocated,
                        status: 'sub'
                    });
                });

                // ===== STEP 2: CLEAR LIVE DATA BEFORE STATUS UPDATE =====
                // FIX: Clear live data BEFORE calling updateStatusTrackingForDate
                cleanedData.forEach(record => {
                    if (liveAttendanceData[record.gid]) {
                        delete liveAttendanceData[record.gid][dateStr];
                    }
                });

                // ===== STEP 3: NOW UPDATE STATUS (live data is cleared, cache has 'sub') =====
                updateStatusTrackingForDate(dateStr, false);

                // ===== STEP 4: DISABLE ATTENDANCE INPUTS =====
                disableAttendanceInputsForSubmittedDate(dateStr);

                // ===== STEP 5: DO NOT TOUCH OVERTIME =====
                console.log('✅ OVERTIME data preserved - NOT modified');
                console.log('📊 Overtime cache size:', overtimeCachedData.length);
                console.log('📊 Live overtime data keys:', Object.keys(liveOvertimeData).length);

                showCustomAlert({
                    type: 'success',
                    title: '✅ Attendance Submitted Successfully',
                    message: `Attendance has been submitted for ${cleanedData.length} employee(s)!`,
                    items: [
                        `📋 Employees: ${cleanedData.length}`,
                        `📅 Date: ${moment(dateStr, 'YYYY-MM-DD').format('MMMM DD, YYYY')}`,
                        `📄 Page: ${currentAttendancePage} of ${totalAttendancePages}`,
                        `✅ Status: SUBMITTED`,
                        `⚠️ Overtime: NOT affected - remains independent`
                    ],
                    note: '📋 <strong>Overtime data is completely independent.</strong>',
                    onOk: function() {
                        refreshDateColumnHeader(dateStr, false);
                        updateSubmitButtonStates(false);
                        
                        // ===== USE SAFE WRAPPER INSTEAD =====
                        ensureCorrectFieldStatesAndRespectSubmitted(false);
                    }
                });
            } else {
                showCustomAlert({
                    type: 'error',
                    title: 'Submission Failed',
                    message: response.message || 'An error occurred while submitting.',
                    note: '🔄 Please try again.'
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('❌ AJAX Error:', status, error, xhr);
            console.error('Response text:', xhr.responseText);
            showLoadingIndicator(false);
            
            let errorMessage = 'Unknown error';
            if (status === 'timeout') {
                errorMessage = 'Request timeout - server took too long to respond';
            } else if (status === 'error') {
                errorMessage = `Server error (${xhr.status})`;
            } else if (xhr.status === 0) {
                errorMessage = 'Network error - check your connection';
            }
            
            showCustomAlert({
                type: 'error',
                title: 'Submission Error',
                message: `Failed to submit attendance for ${cleanedData.length} employee(s) on page ${currentAttendancePage} of ${totalAttendancePages}.`,
                note: `${errorMessage}. Please try again.`
            });
            $submitButton.prop('disabled', false).html(originalButtonHTML);
        }
    });
}

function reloadAttendanceTableOnlyWithoutOvertimeImpact(dateStr = null) {
    console.log('🔄 Reloading ATTENDANCE table ONLY - Date:', dateStr);
    
    // ===== PRESERVE ATTENDANCE FILTERS ONLY =====
    const subDepartmentSelect = $('#sub_departmentSelect').val();
    const employmentTypeSelect = $('#employment_typeSelect').val();
    const groupTypeSelect = $('#group_typeSelect').val();
    const joinedSelect = $('#joinedSelect').val();
    
    console.log('📋 Preserved ATTENDANCE filters:', {
        subDepartment: subDepartmentSelect,
        employmentType: employmentTypeSelect,
        groupType: groupTypeSelect,
        joined: joinedSelect
    });
    
    const currentPage = currentAttendancePage;
    const pageLength = attendancePageLength;
    
    console.log('📄 Current ATTENDANCE pagination:', {
        page: currentPage,
        pageLength: pageLength
    });
    
    showLoadingIndicator(true);
    
    // ===== CLEAR LIVE ATTENDANCE DATA FOR THIS DATE ONLY =====
    if (dateStr) {
        Object.keys(liveAttendanceData).forEach(gid => {
            if (liveAttendanceData[gid][dateStr]) {
                delete liveAttendanceData[gid][dateStr];
            }
        });
        console.log('✅ Cleared live ATTENDANCE data for date:', dateStr);
    }
    
    // ===== DO NOT CLEAR OVERTIME DATA - KEEP IT INDEPENDENT =====
    console.log('✅ OVERTIME data preserved - NOT cleared');
    
    // ===== RELOAD ATTENDANCE TABLE ONLY =====
    console.log('📋 Loading ATTENDANCE employees with filters...');
    
    $.ajax({
        url: '/dpm/api/PMSController.php',
        method: 'POST',
        data: {
            action: 'fetchAttendanceData',
            sub_department: subDepartmentSelect,
            employment_type: employmentTypeSelect,
            group_type: groupTypeSelect,
            joined: joinedSelect,
            start_date: selectedRangeStart.format('YYYY-MM-DD'),
            end_date: selectedRangeEnd.format('YYYY-MM-DD'),
            transfer_check_dates: JSON.stringify([]),
            page: currentAttendancePage,
            per_page: attendancePageLength
        },
        dataType: 'json',
        timeout: 30000,
        success: function(response) {
            console.log('✅ AJAX Response received for attendance reload');
            
            if (response.success && response.employees && Array.isArray(response.employees)) {
                // ===== STORE ATTENDANCE DATA =====
                if (response.attendance && Array.isArray(response.attendance)) {
                    attendanceCachedData = response.attendance;
                }
                
                // ===== RECREATE ATTENDANCE TABLE ONLY =====
                console.log('🔄 Recreating attendance table...');
                currentEmployees = response.employees;
                
                attendancePaginationInfo = {
                    current_page: response.pagination ? response.pagination.current_page : 1,
                    per_page: response.pagination ? response.pagination.per_page : 20,
                    total: response.pagination ? response.pagination.total : 0,
                    filtered: response.pagination ? response.pagination.filtered : 0,
                    total_pages: response.pagination ? response.pagination.total_pages : 0,
                    pagination_text: response.pagination ? response.pagination.pagination_text : 'Showing 0 to 0 of 0 entries'
                };
                
                createServerSidePaginatedTable(false, response.employees, response.attendance || [], attendancePaginationInfo);
                
                setTimeout(() => {
                    console.log('🔄 Applying leave data to attendance table...');
                    if (response.leaves && response.leaves.length > 0) {
                        applyLeaveDataToTable(response.leaves, selectedRangeStart.format('YYYY-MM-DD'), selectedRangeEnd.format('YYYY-MM-DD'), false);
                    }
                    
                    console.log('🔄 Populating existing attendance data...');
                    populateExistingAttendanceData(response.attendance || []);
                    
                    console.log('🔄 Applying responsive layout...');
                    applyResponsiveTableLayout(false);
                    
                    console.log('🔄 Updating submit button states...');
                    updateSubmitButtonStates(false);
                    
                    console.log('🔄 Refreshing all date badges...');
                    refreshAllDateBadges(false);
                    
                    // ===== ENSURE CORRECT FIELD STATES =====
                    ensureCorrectFieldStates(false);
                    
                    // ===== CRITICAL: Disable inputs for submitted date =====
                    if (dateStr) {
                        const status = getDateStatus(dateStr, false);
                        console.log('🏷️ Status for date:', dateStr, '=', status);
                        
                        if (status === 'sub') {
                            console.log('🔒 Disabling inputs for submitted date:', dateStr);
                            disableAttendanceInputsForSubmittedDate(dateStr);
                        }
                    }
                    
                    showLoadingIndicator(false);
                    console.log('✅ ATTENDANCE table reload complete');
                }, 300);
            } else {
                showLoadingIndicator(false);
                console.error('❌ Failed to reload attendance table');
            }
        },
        error: function(xhr, status, error) {
            console.error('❌ AJAX Error during reload:', status, error);
            showLoadingIndicator(false);
        }
    });
}

function updateAttendanceForDate(dateStr) {
    console.log('✏️ Update Attendance clicked for date:', dateStr);
    
    if (!isAdminUser) {
        showCustomAlert({
            type: 'error',
            title: 'Access Denied',
            message: 'Only administrators can update attendance records.',
            note: '🔒 Contact your administrator for assistance.'
        });
        return;
    }
    
    if (isFutureDate(dateStr)) {
        showCustomAlert({
            type: 'error',
            title: 'Cannot Update',
            message: `Cannot update data for future date: ${dateStr}`,
            note: '⚠️ Only past or current dates can be updated.'
        });
        return;
    }
    
    const result = collectCurrentPageAttendanceData(dateStr);
    const dataArray = result.data;

    console.log('📊 Collected current page data:', {
        data_count: dataArray.length,
        current_page: currentAttendancePage,
        total_pages: totalAttendancePages
    });

    if (dataArray.length === 0) {
        showCustomAlert({
            type: 'info',
            title: 'No Data to Update',
            message: `No attendance data on page ${currentAttendancePage} of ${totalAttendancePages} for ${dateStr}.`,
            note: '📋 Enter attendance data before updating.'
        });
        return;
    }

    proceedWithAttendanceUpdate(dateStr, dataArray);
}

function proceedWithAttendanceUpdate(dateStr, dataArray) {
    console.log('🔄 Proceeding with attendance update for:', dateStr, 'Records:', dataArray.length, 'Page:', currentAttendancePage);
    
    const buttonSelector = `.update-date-btn[data-date="${dateStr}"]`;
    const $updateButton = $(buttonSelector);
    
    if ($updateButton.length === 0) {
        console.error('❌ Update button not found:', buttonSelector);
        showCustomAlert({
            type: 'error',
            title: 'Button Not Found',
            message: 'Update button could not be located.',
            note: '🔄 Please refresh the page and try again.'
        });
        return;
    }
    
    const originalButtonHTML = $updateButton.html();
    $updateButton.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
    showLoadingIndicator(true);
    
    const sub_department = currentsub_department;
    const group_type = $('#group_typeSelect').val();
    
    if (!sub_department) {
        showLoadingIndicator(false);
        $updateButton.prop('disabled', false).html(originalButtonHTML);
        showCustomAlert({
            type: 'error',
            title: 'Department Missing',
            message: 'Error: Department information is missing.',
            note: '🔄 Please reload the page.'
        });
        return;
    }
    
    if (!group_type) {
        showLoadingIndicator(false);
        $updateButton.prop('disabled', false).html(originalButtonHTML);
        showCustomAlert({
            type: 'error',
            title: 'Group Type Missing',
            message: 'Error: Group type is missing.',
            note: '📋 Please select a group type.'
        });
        return;
    }
    
    const cleanedData = dataArray.map(record => ({
        employee_name: record.employee_name,
        gid: record.gid,
        date: record.date,
        attendance_status: record.attendance_status,
        actual_man_hours: record.actual_man_hours || 0,
        allocated_shift: record.allocated_shift || null,
        is_allocated: record.is_allocated || 0
    }));
    
    const ajaxData = {
        action: 'submitAttendanceForDate',
        data: JSON.stringify(cleanedData),
        sub_department: sub_department,
        group_type: group_type,
        date: dateStr,
        is_admin: 'true',
        allow_update: 'true',
        total_employees: cleanedData.length,
        preserve_overtime: 'true',
        current_page: currentAttendancePage,
        total_pages: totalAttendancePages,
        is_page_wise_update: 'true',
        is_update: 'true'
    };
    
    console.log('📤 Sending AJAX request:', {
        action: ajaxData.action,
        date: dateStr,
        employees: cleanedData.length,
        page: currentAttendancePage,
        totalPages: totalAttendancePages,
        is_update: ajaxData.is_update,
        data_sample: cleanedData[0]
    });
    
    $.ajax({
        url: '/dpm/api/PMSController.php',
        method: 'POST',
        data: ajaxData,
        dataType: 'json',
        timeout: 30000,
        success: function(response) {
            console.log('✅ AJAX Success:', response);
            
            showLoadingIndicator(false);
            $updateButton.prop('disabled', false).html(originalButtonHTML);
            
            if (response.success) {
                cleanedData.forEach(record => {
                    if (!attendanceCachedData) attendanceCachedData = [];
                    
                    attendanceCachedData = attendanceCachedData.filter(r => 
                        !(r.gid === record.gid && r.attendance_date === record.date)
                    );
                    
                    attendanceCachedData.push({
                        gid: record.gid,
                        name: record.employee_name,
                        attendance_date: record.date,
                        attendance_status: record.attendance_status,
                        actual_man_hours: record.actual_man_hours,
                        allocated_shift: record.allocated_shift,
                        is_allocated: record.is_allocated,
                        status: 'sub'
                    });
                });
                
                // ===== UPDATE STATUS TRACKING =====
                attendanceStatusByDate[dateStr] = 'sub';
                
                cleanedData.forEach(record => {
                    if (liveAttendanceData[record.gid]) {
                        delete liveAttendanceData[record.gid][dateStr];
                    }
                });
                
                showCustomAlert({
                    type: 'success',
                    title: 'Attendance Updated',
                    message: `✅ Attendance has been updated successfully!`,
                    items: [
                        `Employees: ${cleanedData.length}`,
                        `Date: ${moment(dateStr, 'YYYY-MM-DD').format('MMMM DD, YYYY')}`,
                        `Page: ${currentAttendancePage} of ${totalAttendancePages}`,
                        `Status: UPDATED`
                    ],
                    note: response.message || 'Records have been updated.',
                    onOk: function() {
                        // ===== RELOAD ATTENDANCE TABLE ONLY =====
                        reloadAttendanceTableWithFiltersIntact(dateStr);
                    }
                });
            } else {
                showCustomAlert({
                    type: 'error',
                    title: 'Update Failed',
                    message: response.message || 'An error occurred while updating.',
                    note: '🔄 Please try again.'
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('❌ AJAX Error:', status, error, xhr);
            console.error('Response text:', xhr.responseText);
            showLoadingIndicator(false);
            
            let errorMessage = 'Unknown error';
            if (status === 'timeout') {
                errorMessage = 'Request timeout - server took too long to respond';
            } else if (status === 'error') {
                errorMessage = `Server error (${xhr.status})`;
            } else if (xhr.status === 0) {
                errorMessage = 'Network error - check your connection';
            }
            
            showCustomAlert({
                type: 'error',
                title: 'Update Error',
                message: `Failed to update attendance for ${cleanedData.length} employee(s) on page ${currentAttendancePage} of ${totalAttendancePages}.`,
                note: `${errorMessage}. Please try again.`
            });
            $updateButton.prop('disabled', false).html(originalButtonHTML);
        }
    });
}

function saveOvertimeForDate(dateStr) {
    console.log('💾 Save Overtime clicked for date:', dateStr);
    
    try {
        if (isFutureDate(dateStr)) {
            showCustomAlert({
                type: 'error',
                title: 'Cannot Save',
                message: `Cannot save data for future date: ${dateStr}`,
                note: '⚠️ Only past or current dates can be saved.'
            });
            return;
        }
        
        const holidays = window.holidaysData || cachedHolidaysData || {};
        const isHoliday = holidays[dateStr] === 'holiday';
        
        const result = collectCurrentPageOvertimeData(dateStr);
        let dataArray = result.data;
        const transferredEmployees = result.transferred;
        
        console.log('📊 Collected current page overtime data:', {
            data_count: dataArray.length,
            transferred_count: transferredEmployees.length,
            current_page: currentOvertimePage,
            total_pages: totalOvertimePages,
            is_holiday: isHoliday
        });
        
        if (dataArray.length === 0) {
            showCustomAlert({
                type: 'info',
                title: 'No Data on This Page',
                message: `No overtime data available on page ${currentOvertimePage} of ${totalOvertimePages} for ${dateStr}.`,
                note: '📋 Please enter overtime data before saving.'
            });
            return;
        }
        
        let confirmMessage = '';
        let confirmNote = '';
        
        // ===== FOR SAVE: NO ATTENDANCE REQUIREMENT - WORKS FOR BOTH HOLIDAY AND NON-HOLIDAY =====
        confirmMessage = `Save overtime for ${dataArray.length} employee(s) on page ${currentOvertimePage}?`;
        confirmNote = `Date: ${moment(dateStr, 'YYYY-MM-DD').format('MMMM DD, YYYY')}<br>Page: ${currentOvertimePage} of ${totalOvertimePages}<br>Employees: ${dataArray.length}`;
        
        if (transferredEmployees.length > 0) {
            confirmNote += `<br>Transferred (Skipped): ${transferredEmployees.length}`;
        }
        
        if (isHoliday) {
            confirmNote += `<br>📅 Holiday Day`;
        }
        
        confirmNote += `<br><strong>ℹ️ Note:</strong> Data will be saved. You can edit before submitting.`;
        
        showCustomAlert({
            type: 'info',
            title: 'Confirm Overtime Save',
            message: confirmMessage,
            note: confirmNote,
            onOk: function() {
                proceedWithOvertimeSave(dateStr, dataArray);
                reloadOvertimeTabWithFiltersPreserved(dateStr, 'save');
            }
        });

    } catch (error) {
        console.error('❌ Error:', error);
        showCustomAlert({
            type: 'error',
            title: 'Error',
            message: 'Error: ' + error.message,
            note: '📞 Contact support.'
        });
    }
}

function disableAttendanceInputsForSubmittedDate(dateStr) {
    console.log('🔒 Disabling ATTENDANCE inputs for submitted date:', dateStr);
    
    // ✅ ONLY affect attendance table
    $('#table_all_items').find(`.attendance-select[data-date="${dateStr}"]`).each(function() {
        const $select = $(this);
        const gid = $select.data('gid');
        
        // Add the dept-submitted class to grey it out
        $select.addClass('dept-submitted');
        
        // Disable the select
        $select.prop('disabled', true);
        
        // Remove any editable classes
        $select.removeClass('admin-editable');
        
        // Remove any admin edit indicators
        $select.next('.can-update-badge').remove();
        
        console.log(`🔒 Disabled ATTENDANCE select for ${gid} on ${dateStr}`);
    });
    
    console.log('✅ All ATTENDANCE inputs disabled for submitted date:', dateStr);
}

function disableOvertimeInputsForSubmittedDate(dateStr) {
    console.log('🔒 Disabling OVERTIME inputs for SUBMITTED date:', dateStr);

    // ===== SAFETY CHECK: Only disable if status is actually 'sub' =====
    const status = getDateStatus(dateStr, true);
    
    if (status !== 'sub') {
        console.warn('⚠️ BLOCKED: Tried to disable overtime for date:', dateStr, 
                     'but status is:', status, '(not sub) - SKIPPING');
        return;  // ← EXIT if not submitted
    }

    $('#table_overtime_items')
        .find(`.overtime-input[data-date="${dateStr}"]`)
        .each(function() {
            const $input = $(this);
            const gid = $input.data('gid');

            // ===== FOR SUPERVISORS: DISABLE IF SUBMITTED =====
            if (!isAdminUser) {
                $input.addClass('dept-submitted');
                $input.prop('disabled', true);
                $input.prop('readonly', true);
                $input.removeClass('admin-editable');
                $input.next('.admin-edit-indicator').remove();
                console.log(`🔒 SUPERVISOR - Disabled OVERTIME input for ${gid} on ${dateStr} (ot_status='sub')`);
            } 
            // ===== FOR ADMINS: KEEP EDITABLE =====
            else {
                $input.removeClass('dept-submitted');
                $input.prop('disabled', false);
                $input.prop('readonly', false);
                $input.addClass('admin-editable');
                if (!$input.next('.admin-edit-indicator').length) {
                    $input.after('<span class="admin-edit-indicator">✓</span>');
                }
                console.log(`✏️ ADMIN - Kept OVERTIME input editable for ${gid} on ${dateStr} (ot_status='sub')`);
            }
        });

    console.log('✅ All OVERTIME inputs processed for submitted date:', dateStr);
}

function enableOvertimeInputsForSavedDate(dateStr) {
    console.log('🔓 Enabling OVERTIME inputs for SAVED date:', dateStr);

    // ===== SAFETY CHECK: Only enable if ot_status is 'save' not 'sub' =====
    const status = getDateStatus(dateStr, true);

    if (status === 'sub') {
        console.warn('⚠️ BLOCKED: Cannot enable overtime for date:', dateStr,
                     '- status is sub (submitted)');
        return;  // ← EXIT if submitted
    }

    $('#table_overtime_items')
        .find(`.overtime-input[data-date="${dateStr}"]`)
        .each(function() {
            const $input = $(this);
            const gid = $input.data('gid');

            // ===== Only enable if it was disabled due to 'save' status =====
            // Do NOT enable if it's N/A (transferred) or has other reasons
            if ($input.hasClass('dept-submitted') && !$input.hasClass('na-input')) {
                $input.removeClass('dept-submitted');
                $input.prop('disabled', false);
                $input.prop('readonly', false);

                console.log(`🔓 Enabled OVERTIME input for ${gid} on ${dateStr} (ot_status='save')`);
            }
        });

    console.log('✅ OVERTIME inputs re-enabled for saved date:', dateStr);
}

function enforceOvertimeFieldStates() {
    console.log('🔧 Enforcing correct OVERTIME field states...');
    console.log('User is Admin:', isAdminUser);
    console.log('User is Supervisor:', isSupervisorUser);

    const allOvertimeDates = Object.keys(overtimeStatusByDate);

    allOvertimeDates.forEach(dateStr => {
        const status = overtimeStatusByDate[dateStr];

        console.log(`📅 Date: ${dateStr}, OT Status: ${status}`);

        if (status === 'sub') {
            // ===== SUBMITTED: Handle based on user role =====
            if (!isAdminUser) {
                // ===== SUPERVISOR: DISABLE =====
                console.log(`🔒 ${dateStr}: SUBMITTED (ot_status='sub', Supervisor) - disabling inputs`);
                disableOvertimeInputsForSubmittedDate(dateStr);
            } else {
                // ===== ADMIN: ENABLE =====
                console.log(`✏️ ${dateStr}: SUBMITTED (ot_status='sub', Admin) - enabling inputs`);
                enableOvertimeInputsForSavedDate(dateStr);
            }

        } else if (status === 'save') {
            // ===== SAVED: Enable inputs (should be editable) =====
            console.log(`🔓 ${dateStr}: SAVED (ot_status='save') - enabling inputs`);
            enableOvertimeInputsForSavedDate(dateStr);

        } else {
            // ===== INCOMPLETE/LIVE: Enable inputs =====
            console.log(`🔓 ${dateStr}: ${status} - enabling inputs`);
            $('#table_overtime_items')
                .find(`.overtime-input[data-date="${dateStr}"]`)
                .not('.na-input')
                .each(function() {
                    if (!$(this).hasClass('na-input')) {
                        $(this).prop('disabled', false);
                        $(this).prop('readonly', false);
                    }
                });
        }
    });

    console.log('✅ OVERTIME field states enforced');
}

function proceedWithOvertimeSave(dateStr, dataArray) {
    console.log('💾 ===== proceedWithOvertimeSave START =====');
    console.log('Date:', dateStr);
    console.log('Records to save:', dataArray.length);
    console.log('⚠️ CRITICAL: Overtime is INDEPENDENT from Attendance');
    
    const buttonSelector = `.save-overtime-date-btn[data-date="${dateStr}"]`;
    const $saveButton = $(buttonSelector);
    
    if ($saveButton.length === 0) {
        console.error('❌ Save button not found:', buttonSelector);
        showCustomAlert({
            type: 'error',
            title: 'Button Not Found',
            message: 'Save button could not be located.',
            note: '🔄 Please refresh the page and try again.'
        });
        return;
    }
    
    const originalButtonHTML = $saveButton.html();
    $saveButton.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
    showLoadingIndicator(true);
    
    const sub_department = currentsub_department;
    const group_type = $('#overtimeGroup_typeSelect').val();
    
    if (!sub_department) {
        showLoadingIndicator(false);
        $saveButton.prop('disabled', false).html(originalButtonHTML);
        showCustomAlert({
            type: 'error',
            title: 'Department Missing',
            message: 'Error: Department information is missing.',
            note: '🔄 Please reload the page.'
        });
        return;
    }
    
    if (!group_type) {
        showLoadingIndicator(false);
        $saveButton.prop('disabled', false).html(originalButtonHTML);
        showCustomAlert({
            type: 'error',
            title: 'Group Type Missing',
            message: 'Error: Group type is missing.',
            note: '📋 Please select a group type.'
        });
        return;
    }
    
    const holidays = window.holidaysData || cachedHolidaysData || {};
    const isHoliday = holidays[dateStr] === 'holiday';
    
    const cleanedData = dataArray.map(record => ({
        employee_name: record.employee_name,
        gid: record.gid,
        date: record.date,
        overtime_hours: record.overtime_hours || 0,
        is_holiday: record.is_holiday || 0
    }));
    
    const ajaxData = {
        action: 'submitOvertimeForDate',
        data: JSON.stringify(cleanedData),
        sub_department: sub_department,
        group_type: group_type,
        date: dateStr,
        is_admin: isAdminUser ? 'true' : 'false',
        allow_update: isAdminUser ? 'true' : 'false',
        total_employees: cleanedData.length,
        is_holiday: isHoliday ? 1 : 0,
        current_page: currentOvertimePage,
        total_pages: totalOvertimePages,
        is_page_wise_save: 'true',
        is_save: 'true',
        is_submit: 'false'
    };
    
    console.log('📤 Sending AJAX request (SAVE ONLY - NOT SUBMIT):', {
        action: ajaxData.action,
        date: dateStr,
        employees: cleanedData.length,
        page: currentOvertimePage,
        totalPages: totalOvertimePages,
        is_save: ajaxData.is_save,
        is_submit: ajaxData.is_submit,
        is_holiday: isHoliday,
        data_sample: cleanedData[0]
    });
    
    $.ajax({
        url: '/dpm/api/PMSController.php',
        method: 'POST',
        data: ajaxData,
        dataType: 'json',
        timeout: 30000,
        success: function(response) {
            console.log('✅ AJAX Success:', response);
            console.log('📊 Response Details:', {
                success: response.success,
                action_type: response.action_type,
                ot_status: response.ot_status,
                message: response.message
            });
            
            showLoadingIndicator(false);
            $saveButton.prop('disabled', false).html(originalButtonHTML);
            
            if (response.success) {
                console.log('📋 Updating cached overtime data with ot_status...');
                
                // ===== DETERMINE OT_STATUS FROM RESPONSE =====
                const otStatusFromResponse = ajaxData.is_save === 'true' ? 'save' :
                                           (response.action_type === 'submitted' ? 'sub' :
                                           response.action_type === 'saved' ? 'save' :
                                           response.ot_status || 'save');

                console.log('🏷️ Overtime Status (ot_status) from response:', otStatusFromResponse);
                
                cleanedData.forEach(record => {
                    if (!overtimeCachedData) overtimeCachedData = [];
                    
                    overtimeCachedData = overtimeCachedData.filter(r => {
                        const rDate = r.date.includes('T') ? r.date.split('T')[0] : r.date;
                        return !(r.gid === record.gid && rDate === record.date);
                    });
                    
                    overtimeCachedData.push({
                        gid: record.gid,
                        name: record.employee_name,
                        date: record.date,
                        overtime_hours: record.overtime_hours,
                        is_holiday: record.is_holiday,
                        attendance_status: record.attendance_status || 'present',
                        ot_status: otStatusFromResponse  // ===== USE ot_status =====
                    });
                    
                    console.log(`✅ Updated overtime cache for ${record.gid}: ot_status='${otStatusFromResponse}', hours=${record.overtime_hours}`);
                });
                
                console.log('🔄 Clearing live OVERTIME data for date:', dateStr);
                cleanedData.forEach(record => {
                    if (liveOvertimeData[record.gid]) {
                        delete liveOvertimeData[record.gid][dateStr];
                    }
                });
                
                console.log('✅ ATTENDANCE data preserved - NOT modified');
                console.log('✅ Live overtime data cleared');
                
                let successItems = [
                    `Employees: ${cleanedData.length}`,
                    `Date: ${moment(dateStr, 'YYYY-MM-DD').format('MMMM DD, YYYY')}`,
                    `Page: ${currentOvertimePage} of ${totalOvertimePages}`,
                    `Status: ${response.action_type ? response.action_type.toUpperCase() : 'SAVED'}`,
                    `OT Status: ${otStatusFromResponse}`
                ];
                
                if (isHoliday || response.is_holiday) {
                    successItems.push('📅 Holiday Day');
                }
                
                showCustomAlert({
                    type: 'success',
                    title: 'Overtime Saved',
                    message: `✅ Overtime has been ${response.action_type || 'saved'} successfully!`,
                    items: successItems,
                    note: '📋 Overtime data is INDEPENDENT from Attendance. You can still edit this data before submitting.',
                    onOk: function() {
                        console.log('✅ User confirmed save success');
                        reloadOvertimeTabWithFiltersPreserved(dateStr, 'save');
                    }
                });
                
                console.log('💾 ===== proceedWithOvertimeSave END =====\n');
            } else {
                showCustomAlert({
                    type: 'error',
                    title: 'Save Failed',
                    message: response.message || 'An error occurred while saving.',
                    note: '🔄 Please try again.'
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('❌ AJAX Error:', status, error, xhr);
            console.error('Response text:', xhr.responseText);
            showLoadingIndicator(false);
            
            let errorMessage = 'Unknown error';
            if (status === 'timeout') {
                errorMessage = 'Request timeout - server took too long to respond';
            } else if (status === 'error') {
                errorMessage = `Server error (${xhr.status})`;
            } else if (xhr.status === 0) {
                errorMessage = 'Network error - check your connection';
            }
            
            showCustomAlert({
                type: 'error',
                title: 'Save Error',
                message: `Failed to save overtime for ${cleanedData.length} employee(s) on page ${currentOvertimePage}.`,
                note: `${errorMessage}. Please try again.`
            });
            $saveButton.prop('disabled', false).html(originalButtonHTML);
        }
    });
}

function reloadOvertimeTableOnlyWithoutAttendanceImpact(dateStr = null) {
    console.log('🔄 Reloading OVERTIME table ONLY - Date:', dateStr);
    console.log('⚠️ CRITICAL: ATTENDANCE table will NOT be affected');
    
    // ===== PRESERVE OVERTIME FILTERS ONLY =====
    const subDepartmentSelect = $('#overtimeSub_departmentSelect').val();
    const employmentTypeSelect = $('#overtimeemployment_typeSelect').val();
    const groupTypeSelect = $('#overtimeGroup_typeSelect').val();
    const joinedSelect = $('#overtimejoinedSelect').val();
    
    console.log('📋 Preserved OVERTIME filters:', {
        subDepartment: subDepartmentSelect,
        employmentType: employmentTypeSelect,
        groupType: groupTypeSelect,
        joined: joinedSelect
    });
    
    const currentPage = currentOvertimePage;
    const pageLength = overtimePageLength;
    
    console.log('📄 Current OVERTIME pagination:', {
        page: currentPage,
        pageLength: pageLength
    });
    
    showLoadingIndicator(true);
    
    // ===== CLEAR LIVE OVERTIME DATA FOR THIS DATE ONLY =====
    if (dateStr) {
        Object.keys(liveOvertimeData).forEach(gid => {
            if (liveOvertimeData[gid] && liveOvertimeData[gid][dateStr]) {
                delete liveOvertimeData[gid][dateStr];
            }
        });
        console.log('✅ Cleared live OVERTIME data for date:', dateStr);
    }
    
    // ===== DO NOT CLEAR ATTENDANCE DATA - KEEP IT INDEPENDENT =====
    console.log('✅ ATTENDANCE data preserved - NOT cleared');
    
    // ===== RELOAD OVERTIME TABLE ONLY =====
    console.log('📋 Loading OVERTIME employees with filters...');
    
    $.ajax({
        url: '/dpm/api/PMSController.php',
        method: 'POST',
        data: {
            action: 'fetchOvertimeData',
            sub_department: subDepartmentSelect,
            employment_type: employmentTypeSelect,
            group_type: groupTypeSelect,
            joined: joinedSelect,
            start_date: selectedRangeStart.format('YYYY-MM-DD'),
            end_date: selectedRangeEnd.format('YYYY-MM-DD'),
            transfer_check_dates: JSON.stringify([]),
            page: currentOvertimePage,
            per_page: overtimePageLength
        },
        dataType: 'json',
        timeout: 30000,
        success: function(response) {
            console.log('✅ AJAX Response received for overtime reload');
            
            if (response.success && response.employees && Array.isArray(response.employees)) {
                // ===== STORE page_wise_status FROM RESPONSE =====
                if (response.page_wise_status) {
                    overtimePageWiseStatus = response.page_wise_status;
                    console.log('📊 Updated overtimePageWiseStatus from response');
                }
                
                // ===== STORE OVERTIME DATA =====
                if (response.overtime && Array.isArray(response.overtime)) {
                    overtimeCachedData = response.overtime;
                }
                
                // ===== COMBINE WITH HOLIDAY OVERTIME =====
                if (response.holiday_overtime && Array.isArray(response.holiday_overtime)) {
                    if (!overtimeCachedData) overtimeCachedData = [];
                    overtimeCachedData = overtimeCachedData.concat(response.holiday_overtime);
                }
                
                // ===== RECREATE OVERTIME TABLE ONLY =====
                console.log('🔄 Recreating overtime table...');
                currentOvertimeEmployees = response.employees;
                
                const combinedOvertimeData = {
                    overtime: response.overtime || [],
                    holiday_overtime: response.holiday_overtime || []
                };
                
                overtimePaginationInfo = {
                    current_page: response.pagination ? response.pagination.current_page : 1,
                    per_page: response.pagination ? response.pagination.per_page : 20,
                    total: response.pagination ? response.pagination.total : 0,
                    filtered: response.pagination ? response.pagination.filtered : 0,
                    total_pages: response.pagination ? response.pagination.total_pages : 0,
                    pagination_text: response.pagination ? response.pagination.pagination_text : 'Showing 0 to 0 of 0 entries'
                };
                
                createServerSidePaginatedTable(true, response.employees, combinedOvertimeData, overtimePaginationInfo);
                
                setTimeout(() => {
                    console.log('🔄 Applying leave data to overtime table...');
                    if (response.leaves && response.leaves.length > 0) {
                        applyLeaveDataToTable(response.leaves, selectedRangeStart.format('YYYY-MM-DD'), selectedRangeEnd.format('YYYY-MM-DD'), true);
                    }
                    
                    console.log('🔄 Populating existing overtime data...');
                    populateExistingOvertimeData(combinedOvertimeData);
                    
                    console.log('🔄 Applying responsive layout...');
                    applyResponsiveTableLayout(true);
                    
                    console.log('🔄 Updating submit button states...');
                    updateSubmitButtonStates(true);
                    
                    console.log('🔄 Refreshing all date badges...');
                    refreshAllDateBadges(true);
                    
                    // ===== ENSURE CORRECT FIELD STATES =====
                    ensureCorrectFieldStates(true);
                    
                    showLoadingIndicator(false);
                    console.log('✅ OVERTIME table reload complete');
                }, 300);
            } else {
                showLoadingIndicator(false);
                console.error('❌ Failed to reload overtime table');
            }
        },
        error: function(xhr, status, error) {
            console.error('❌ AJAX Error during reload:', status, error);
            showLoadingIndicator(false);
        }
    });
}

function submitOvertimeForDate(dateStr) {
    console.log('📤 Submit Overtime clicked for date:', dateStr);
    console.log('🔍 Debugging info:', {
        dateStr: dateStr,
        isFutureDate: isFutureDate(dateStr),
        holidays: window.holidaysData || cachedHolidaysData || {},
        isHoliday: (window.holidaysData || cachedHolidaysData || {})[dateStr] === 'holiday'
    });
    
    if (isFutureDate(dateStr)) {
        showCustomAlert({
            type: 'error',
            title: 'Cannot Submit',
            message: `Cannot submit data for future date: ${dateStr}`,
            note: '⚠️ Only past or current dates can be submitted.'
        });
        return;
    }
    
    const holidays = window.holidaysData || cachedHolidaysData || {};
    const isHoliday = holidays[dateStr] === 'holiday';
    
    console.log('📅 Holiday Check:', {
        dateStr: dateStr,
        isHoliday: isHoliday,
        holidaysData: holidays
    });
    
    // ===== FOR NON-HOLIDAY DAYS: CHECK ATTENDANCE SUBMISSION =====
    if (!isHoliday) {
        console.log('📋 Non-holiday day detected - checking attendance submission requirement');
        
        // Check if all employees have SUBMITTED attendance
        const unsubmittedAttendance = checkForUnsubmittedAttendance(dateStr);
        
        if (unsubmittedAttendance.count > 0) {
            console.log('❌ Found employees without submitted attendance:', unsubmittedAttendance.count);
            
            showCustomAlert({
                type: 'error',
                title: '❌ Attendance Submission Required',
                message: `${unsubmittedAttendance.count} employee(s) on this page do not have SUBMITTED attendance records for ${moment(dateStr, 'YYYY-MM-DD').format('MMMM DD, YYYY')}.`,
                items: unsubmittedAttendance.employees.slice(0, 10).map(emp => `${emp.name} (ID: ${emp.gid}) - Attendance Not Submitted`),
                note: unsubmittedAttendance.count > 10 ? 
                    `<strong>⚠️ ${unsubmittedAttendance.count - 10} more employee(s) not shown</strong><br>📋 <strong>Attendance MUST BE SUBMITTED before submitting overtime on non-holiday days.</strong><br>Go to Attendance tab and submit attendance first.` :
                    '📋 <strong>Attendance MUST BE SUBMITTED before submitting overtime on non-holiday days.</strong><br>Go to Attendance tab and submit attendance first.'
            });
            return;
        }
        
        console.log('✅ All employees have submitted attendance');
    } else {
        console.log('📅 Holiday day detected - skipping attendance requirement check');
    }
    
    // ===== CHECK FOR UNFILLED OVERTIME ENTRIES =====
    const unfilled = checkForUnfilledOvertimeEntries(dateStr);
    
    if (unfilled.count > 0) {
        console.log('❌ Found unfilled overtime entries:', unfilled.count);
        
        showCustomAlert({
            type: 'error',
            title: '❌ Incomplete Data - Cannot Submit',
            message: `${unfilled.count} employee(s) on this page have missing overtime hours for ${moment(dateStr, 'YYYY-MM-DD').format('MMMM DD, YYYY')}.`,
            items: unfilled.employees.slice(0, 10).map(emp => `${emp.name} (ID: ${emp.gid}) - No Overtime Hours`),
            note: unfilled.count > 10 ? 
                `<strong>⚠️ ${unfilled.count - 10} more employee(s) not shown</strong><br>📋 All entries on this page must be filled before submission.` :
                '📋 <strong>All entries on this page must be filled before submission.</strong>'
        });
        return;
    }
    
    const result = collectCurrentPageOvertimeData(dateStr);
    let dataArray = result.data;
    const transferredEmployees = result.transferred;
    const skippedSubmitted = result.skipped;
    
    console.log('📊 Collected current page overtime data:', {
        data_count: dataArray.length,
        transferred_count: transferredEmployees.length,
        skipped_submitted_count: skippedSubmitted.length,
        current_page: currentOvertimePage,
        total_pages: totalOvertimePages,
        is_holiday: isHoliday
    });
    
    if (dataArray.length === 0) {
        let message = `No new overtime data to submit on page ${currentOvertimePage} of ${totalOvertimePages} for ${dateStr}.`;
        let note = '📋 ';
        
        if (skippedSubmitted.length > 0) {
            message += ` (${skippedSubmitted.length} employee(s) already submitted)`;
            note += `${skippedSubmitted.length} employee(s) already have submitted data and were skipped. `;
        }
        
        note += 'Please enter new overtime data before submitting.';
        
        showCustomAlert({
            type: 'info',
            title: 'No New Data to Submit',
            message: message,
            note: note
        });
        return;
    }
    
    let confirmMessage = '';
    let confirmNote = '';
    let confirmItems = [];
    
    confirmMessage = `Submit overtime for ${dataArray.length} employee(s) on page ${currentOvertimePage}?`;
    confirmItems.push(`📋 Employees: ${dataArray.length}`);
    confirmItems.push(`📅 Date: ${moment(dateStr, 'YYYY-MM-DD').format('MMMM DD, YYYY')}`);
    confirmItems.push(`📄 Page: ${currentOvertimePage} of ${totalOvertimePages}`);
    
    if (isHoliday) {
        confirmItems.push(`📅 Holiday Day - Attendance NOT required`);
    } else {
        confirmItems.push(`✅ All employees have SUBMITTED attendance records`);
    }
    
    if (skippedSubmitted.length > 0) {
        confirmItems.push(`⏭️ Skipped (Already Submitted): ${skippedSubmitted.length}`);
    }
    
    confirmItems.push(`✅ Status: All entries verified and complete`);
    
    confirmNote = confirmItems.join('<br>');
    
    showCustomAlert({
        type: 'info',
        title: 'Confirm Overtime Submission',
        message: confirmMessage,
        items: skippedSubmitted.length > 0 ? skippedSubmitted.slice(0, 5).map(emp => `⏭️ ${emp.name} (Already submitted)`) : [],
        note: confirmNote,
        onOk: function() {
            proceedWithOvertimeSubmission(dateStr, dataArray);
            reloadOvertimeTabWithFiltersPreserved(dateStr, 'submit');
        }
    });
}

function checkForUnsubmittedAttendance(dateStr) {
    const unsubmitted = {
        count: 0,
        employees: []
    };
    
    if (!overtimeDataTable) {
        console.error('❌ DataTable not initialized');
        return unsubmitted;
    }
    
    const pageData = overtimeDataTable.rows({ page: 'current' }).data();
    const currentPageInfo = overtimeDataTable.page.info();
    const pageNum = currentPageInfo.page + 1;
    const totalPages = currentPageInfo.pages;
    
    console.log('🔍 Checking for unsubmitted ATTENDANCE (not overtime) on:', dateStr);
    console.log('📄 Page:', pageNum, 'of', totalPages);
    console.log('👥 Employees on page:', pageData.length);
    
    pageData.each(function(rowData, index) {
        const gid = rowData.gid;
        const name = rowData.name;
        
        // ===== CHECK IF EMPLOYEE IS TRANSFERRED ON THIS DATE =====
        const isTransferred = isEmployeeInTransferredDeptOnDate(rowData, dateStr);
        
        if (isTransferred) {
            console.log(`⏭️ Employee ${gid}: TRANSFERRED - skipping`);
            return; // Skip transferred employees
        }
        
        // ===== CHECK IF EMPLOYEE HAS SUBMITTED ATTENDANCE RECORD =====
        // ===== NOTE: This checks ATTENDANCE, not OVERTIME =====
        let hasSubmittedAttendance = false;
        
        if (Array.isArray(attendanceCachedData)) {
            const attendanceRecord = attendanceCachedData.find(record => 
                record.gid === gid && record.attendance_date === dateStr && record.status === 'sub'
            );
            
            if (attendanceRecord) {
                hasSubmittedAttendance = true;
                console.log(`✅ Employee ${gid}: HAS SUBMITTED ATTENDANCE`);
            } else {
                console.log(`❌ Employee ${gid}: NO SUBMITTED ATTENDANCE`);
            }
        }
        
        if (!hasSubmittedAttendance) {
            unsubmitted.count++;
            unsubmitted.employees.push({
                gid: gid,
                name: name,
                date: dateStr,
                page: pageNum,
                totalPages: totalPages,
                reason: 'Attendance not submitted'
            });
            console.log(`❌ UNSUBMITTED: ${gid} - ${name} (no submitted attendance)`);
        }
    });
    
    console.log('📊 Unsubmitted ATTENDANCE found:', unsubmitted.count);
    return unsubmitted;
}

function proceedWithOvertimeSubmission(dateStr, dataArray) {
    console.log('🔄 Proceeding with overtime submission for:', dateStr, 'Records:', dataArray.length, 'Page:', currentOvertimePage);
    console.log('⚠️ CRITICAL: This will ONLY submit OVERTIME - NOT attendance');
    
    const buttonSelector = `.submit-overtime-date-btn[data-date="${dateStr}"]`;
    const $submitButton = $(buttonSelector);
    
    if ($submitButton.length === 0) {
        console.error('❌ Submit button not found:', buttonSelector);
        showCustomAlert({
            type: 'error',
            title: 'Button Not Found',
            message: 'Submit button could not be located.',
            note: '🔄 Please refresh the page and try again.'
        });
        return;
    }
    
    const originalButtonHTML = $submitButton.html();
    $submitButton.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
    showLoadingIndicator(true);
    
    const sub_department = currentsub_department;
    const group_type = $('#overtimeGroup_typeSelect').val();
    
    if (!sub_department) {
        showLoadingIndicator(false);
        $submitButton.prop('disabled', false).html(originalButtonHTML);
        showCustomAlert({
            type: 'error',
            title: 'Department Missing',
            message: 'Error: Department information is missing.',
            note: '🔄 Please reload the page.'
        });
        return;
    }
    
    if (!group_type) {
        showLoadingIndicator(false);
        $submitButton.prop('disabled', false).html(originalButtonHTML);
        showCustomAlert({
            type: 'error',
            title: 'Group Type Missing',
            message: 'Error: Group type is missing.',
            note: '📋 Please select a group type.'
        });
        return;
    }
    
    const holidays = window.holidaysData || cachedHolidaysData || {};
    const isHoliday = holidays[dateStr] === 'holiday';
    
    const cleanedData = dataArray.map(record => ({
        employee_name: record.employee_name,
        gid: record.gid,
        date: record.date,
        overtime_hours: record.overtime_hours !== null && record.overtime_hours !== undefined ? record.overtime_hours : 0,
        is_holiday: record.is_holiday || 0
    }));
    
    const ajaxData = {
        action: 'submitOvertimeForDate',
        data: JSON.stringify(cleanedData),
        sub_department: sub_department,
        group_type: group_type,
        date: dateStr,
        is_admin: isAdminUser ? 'true' : 'false',
        allow_update: isAdminUser ? 'true' : 'false',
        total_employees: cleanedData.length,
        is_holiday: isHoliday ? 1 : 0,
        current_page: currentOvertimePage,
        total_pages: totalOvertimePages,
        is_page_wise_submit: 'true',
        is_save: 'false',
        is_submit: 'true',
        do_not_submit_attendance: 'true'
    };
    
    console.log('📤 Sending AJAX request (OVERTIME ONLY):', {
        action: ajaxData.action,
        date: dateStr,
        employees: cleanedData.length,
        page: currentOvertimePage,
        totalPages: totalOvertimePages,
        isHoliday: isHoliday,
        is_submit: ajaxData.is_submit,
        do_not_submit_attendance: ajaxData.do_not_submit_attendance,
        data_sample: cleanedData[0]
    });
    
    $.ajax({
        url: '/dpm/api/PMSController.php',
        method: 'POST',
        data: ajaxData,
        dataType: 'json',
        timeout: 30000,
        success: function(response) {
            console.log('✅ AJAX Success:', response);
            console.log('📊 Response Details:', {
                success: response.success,
                action_type: response.action_type,
                ot_status: response.ot_status,
                message: response.message,
                attendance_submitted: response.attendance_submitted
            });
            
            showLoadingIndicator(false);
            $submitButton.prop('disabled', false).html(originalButtonHTML);
            
            if (response.success) {
                if (response.attendance_submitted === true || response.attendance_submitted === 'true') {
                    console.error('❌ ERROR: Attendance was submitted when it should NOT have been!');
                    showCustomAlert({
                        type: 'error',
                        title: '⚠️ Unexpected Behavior',
                        message: 'Overtime was submitted, but attendance was also submitted unexpectedly.',
                        note: '🔄 Please contact support. This should not happen.'
                    });
                    return;
                }
                
                console.log('✅ Confirmed: ONLY overtime was submitted, attendance was NOT submitted');
                
                // ===== DETERMINE OT_STATUS FROM RESPONSE =====
                const otStatusFromResponse = response.action_type === 'submitted' ? 'sub' : 
                                            response.action_type === 'saved' ? 'save' : 
                                            response.ot_status || 'sub';
                
                console.log('🏷️ Overtime Status (ot_status) determined from action_type:', response.action_type, '→', otStatusFromResponse);
                
                cleanedData.forEach(record => {
                    if (!overtimeCachedData) overtimeCachedData = [];
                    
                    overtimeCachedData = overtimeCachedData.filter(r => {
                        const rDate = r.date.includes('T') ? r.date.split('T')[0] : r.date;
                        return !(r.gid === record.gid && rDate === record.date);
                    });
                    
                    overtimeCachedData.push({
                        gid: record.gid,
                        name: record.employee_name,
                        date: record.date,
                        overtime_hours: record.overtime_hours,
                        is_holiday: record.is_holiday,
                        attendance_status: record.attendance_status || 'present',
                        ot_status: otStatusFromResponse  // ===== USE ot_status =====
                    });
                    
                    console.log(`✅ Updated overtime cache for ${record.gid}: ot_status='${otStatusFromResponse}', hours=${record.overtime_hours}`);
                });
                
                console.log('🔄 Clearing live OVERTIME data for date:', dateStr);
                cleanedData.forEach(record => {
                    if (liveOvertimeData[record.gid]) {
                        delete liveOvertimeData[record.gid][dateStr];
                    }
                });
                
                console.log('✅ Verifying attendance data was not modified...');
                console.log('📊 Attendance cache size:', attendanceCachedData.length);
                console.log('📊 Live attendance data keys:', Object.keys(liveAttendanceData).length);
                
                console.log('✅ ATTENDANCE data preserved - NOT modified');
                console.log('✅ Live overtime data cleared');
                
                let successItems = [
                    `📋 Employees: ${cleanedData.length}`,
                    `📅 Date: ${moment(dateStr, 'YYYY-MM-DD').format('MMMM DD, YYYY')}`,
                    `📄 Page: ${currentOvertimePage} of ${totalOvertimePages}`,
                    `✅ Status: ${response.action_type ? response.action_type.toUpperCase() : 'SUBMITTED'}`,
                    `OT Status: ${otStatusFromResponse}`,
                    `⚠️ Attendance: NOT affected - remains independent`
                ];
                
                if (isHoliday) {
                    successItems.push('📅 Holiday Day');
                }
                
                showCustomAlert({
                    type: 'success',
                    title: '✅ Overtime Submitted Successfully',
                    message: `Overtime has been ${response.action_type || 'submitted'} for ${cleanedData.length} employee(s)!`,
                    items: successItems,
                    note: '📋 <strong>Attendance data is completely independent.</strong> Attendance remains unaffected.',
                    onOk: function() {
                        console.log('✅ User confirmed overtime submission');
                        reloadOvertimeTabWithFiltersPreserved(dateStr, 'submit');
                    }
                });
                
                console.log('📤 ===== proceedWithOvertimeSubmission END =====\n');
            } else {
                showCustomAlert({
                    type: 'error',
                    title: 'Submission Failed',
                    message: response.message || 'An error occurred while submitting.',
                    note: '🔄 Please try again.'
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('❌ AJAX Error:', status, error, xhr);
            console.error('Response text:', xhr.responseText);
            showLoadingIndicator(false);
            
            let errorMessage = 'Unknown error';
            if (status === 'timeout') {
                errorMessage = 'Request timeout - server took too long to respond';
            } else if (status === 'error') {
                errorMessage = `Server error (${xhr.status})`;
            } else if (xhr.status === 0) {
                errorMessage = 'Network error - check your connection';
            }
            
            showCustomAlert({
                type: 'error',
                title: 'Submission Error',
                message: `Failed to submit overtime for ${cleanedData.length} employee(s) on page ${currentOvertimePage}.`,
                note: `${errorMessage}. Please try again.`
            });
            $submitButton.prop('disabled', false).html(originalButtonHTML);
        }
    });
}

// ===== UPDATED: updateOvertimeForDate function =====
function updateOvertimeForDate(dateStr) {
    console.log('✏️ Update Overtime clicked for date:', dateStr);
    
    if (!isAdminUser) {
        showCustomAlert({
            type: 'error',
            title: 'Access Denied',
            message: 'Only administrators can update overtime records.',
            note: '🔒 Contact your administrator for assistance.'
        });
        return;
    }
    
    if (isFutureDate(dateStr)) {
        showCustomAlert({
            type: 'error',
            title: 'Cannot Update',
            message: `Cannot update data for future date: ${dateStr}`,
            note: '⚠️ Only past or current dates can be updated.'
        });
        return;
    }
    
    const result = collectCurrentPageOvertimeData(dateStr);
    const dataArray = result.data;

    console.log('📊 Collected current page overtime data:', {
        data_count: dataArray.length,
        current_page: currentOvertimePage
    });

    if (dataArray.length === 0) {
        showCustomAlert({
            type: 'info',
            title: 'No Data to Update',
            message: `No overtime data on page ${currentOvertimePage} for ${dateStr}.`,
            note: '📋 Enter overtime data before updating.'
        });
        return;
    }

    proceedWithOvertimeUpdate(dateStr, dataArray);
}

function proceedWithOvertimeUpdate(dateStr, dataArray) {
    console.log('🔄 Proceeding with overtime update for:', dateStr, 'Records:', dataArray.length, 'Page:', currentOvertimePage);
    
    const buttonSelector = `.update-overtime-date-btn[data-date="${dateStr}"]`;
    const $updateButton = $(buttonSelector);
    
    if ($updateButton.length === 0) {
        console.error('❌ Update button not found:', buttonSelector);
        showCustomAlert({
            type: 'error',
            title: 'Button Not Found',
            message: 'Update button could not be located.',
            note: '🔄 Please refresh the page and try again.'
        });
        return;
    }
    
    const originalButtonHTML = $updateButton.html();
    $updateButton.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
    showLoadingIndicator(true);
    
    const sub_department = currentsub_department;
    const group_type = $('#overtimeGroup_typeSelect').val();
    
    if (!sub_department) {
        showLoadingIndicator(false);
        $updateButton.prop('disabled', false).html(originalButtonHTML);
        showCustomAlert({
            type: 'error',
            title: 'Department Missing',
            message: 'Error: Department information is missing.',
            note: '🔄 Please reload the page.'
        });
        return;
    }
    
    if (!group_type) {
        showLoadingIndicator(false);
        $updateButton.prop('disabled', false).html(originalButtonHTML);
        showCustomAlert({
            type: 'error',
            title: 'Group Type Missing',
            message: 'Error: Group type is missing.',
            note: '📋 Please select a group type.'
        });
        return;
    }
    
    const holidays = window.holidaysData || cachedHolidaysData || {};
    const isHoliday = holidays[dateStr] === 'holiday';
    
    // ===== ENSURE ALL RECORDS HAVE HOURS (EVEN IF 0) =====
    const cleanedData = dataArray.map(record => ({
        employee_name: record.employee_name,
        gid: record.gid,
        date: record.date,
        overtime_hours: record.overtime_hours !== null && record.overtime_hours !== undefined ? record.overtime_hours : 0,
        is_holiday: record.is_holiday || 0
    }));
    
    const ajaxData = {
        action: 'submitOvertimeForDate',
        data: JSON.stringify(cleanedData),
        sub_department: sub_department,
        group_type: group_type,
        date: dateStr,
        is_admin: 'true',
        allow_update: 'true',
        total_employees: cleanedData.length,
        is_holiday: isHoliday ? 1 : 0,
        current_page: currentOvertimePage,
        total_pages: totalOvertimePages,
        is_page_wise_update: 'true',
        is_update: 'true'
    };
    
    console.log('📤 Sending AJAX request:', {
        action: ajaxData.action,
        date: dateStr,
        employees: cleanedData.length,
        page: currentOvertimePage,
        totalPages: totalOvertimePages,
        isHoliday: isHoliday,
        is_update: ajaxData.is_update,
        data_sample: cleanedData[0]
    });
    
    $.ajax({
        url: '/dpm/api/PMSController.php',
        method: 'POST',
        data: ajaxData,
        dataType: 'json',
        timeout: 30000,
        success: function(response) {
            console.log('✅ AJAX Success:', response);
            console.log('📊 Response Details:', {
                success: response.success,
                action_type: response.action_type,
                ot_status: response.ot_status,
                message: response.message
            });
            
            showLoadingIndicator(false);
            $updateButton.prop('disabled', false).html(originalButtonHTML);
            
            if (response.success) {
                console.log('📋 Updating cached data with ot_status from response...');
                
                // ===== DETERMINE OT_STATUS FROM RESPONSE =====
                const otStatusFromResponse = response.action_type === 'submitted' ? 'sub' : 
                                            response.action_type === 'saved' ? 'save' : 
                                            response.ot_status || 'sub';
                
                console.log('🏷️ Overtime Status (ot_status) determined from action_type:', response.action_type, '→', otStatusFromResponse);
                
                // ===== UPDATE CACHED DATA WITH OT_STATUS FROM RESPONSE =====
                cleanedData.forEach(record => {
                    if (!overtimeCachedData) overtimeCachedData = [];
                    
                    overtimeCachedData = overtimeCachedData.filter(r => {
                        const rDate = r.date.includes('T') ? r.date.split('T')[0] : r.date;
                        return !(r.gid === record.gid && rDate === record.date);
                    });
                    
                    overtimeCachedData.push({
                        gid: record.gid,
                        name: record.employee_name,
                        date: record.date,
                        overtime_hours: record.overtime_hours,
                        is_holiday: record.is_holiday,
                        attendance_status: record.attendance_status || 'present',
                        ot_status: otStatusFromResponse  // ===== USE ot_status FROM RESPONSE =====
                    });
                    
                    console.log(`✅ Updated cache for ${record.gid}: ot_status='${otStatusFromResponse}'`);
                });
                
                // ===== CLEAR LIVE DATA =====
                console.log('🔄 Clearing live overtime data for date:', dateStr);
                cleanedData.forEach(record => {
                    if (liveOvertimeData[record.gid]) {
                        delete liveOvertimeData[record.gid][dateStr];
                    }
                });
                
                // ===== RECALCULATE STATUS FOR THIS DATE =====
                console.log('🔄 Recalculating status for date:', dateStr);
                updateStatusTrackingForDate(dateStr, true);
                
                // ===== REFRESH HEADER WITH NEW BADGE =====
                console.log('🔄 Refreshing header for date:', dateStr);
                refreshDateColumnHeader(dateStr, true);
                
                // ===== UPDATE BUTTON STATES =====
                console.log('🔄 Updating button states');
                updateSubmitButtonStates(true);
                
                // ===== DISABLE INPUTS FOR SUBMITTED DATE =====
                if (otStatusFromResponse === 'sub') {
                    console.log('🔄 Disabling inputs for submitted date (ot_status=sub)');
                    disableOvertimeInputsForSubmittedDate(dateStr);
                }
                
                showCustomAlert({
                    type: 'success',
                    title: 'Overtime Updated',
                    message: `✅ Overtime has been ${response.action_type || 'updated'} successfully!`,
                    items: [
                        `Employees: ${cleanedData.length}`,
                        `Date: ${moment(dateStr, 'YYYY-MM-DD').format('MMMM DD, YYYY')}`,
                        `Page: ${currentOvertimePage} of ${totalOvertimePages}`,
                        `Status: ${response.action_type ? response.action_type.toUpperCase() : 'UPDATED'}`,
                        `OT Status: ${otStatusFromResponse}`,
                        isHoliday ? '📅 Holiday' : ''
                    ].filter(item => item !== ''),
                    note: response.message || 'Records have been updated.',
                    onOk: function() {
                        console.log('✅ User confirmed update success');
                        reloadOvertimeTabWithFiltersPreserved(dateStr, 'update');
                    }
                });
                
                console.log('✏️ ===== proceedWithOvertimeUpdate END =====\n');
            } else {
                showCustomAlert({
                    type: 'error',
                    title: 'Update Failed',
                    message: response.message || 'An error occurred while updating.',
                    note: '🔄 Please try again.'
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('❌ AJAX Error:', status, error, xhr);
            console.error('Response text:', xhr.responseText);
            showLoadingIndicator(false);
            
            let errorMessage = 'Unknown error';
            if (status === 'timeout') {
                errorMessage = 'Request timeout - server took too long to respond';
            } else if (status === 'error') {
                errorMessage = `Server error (${xhr.status})`;
            } else if (xhr.status === 0) {
                errorMessage = 'Network error - check your connection';
            }
            
            showCustomAlert({
                type: 'error',
                title: 'Update Error',
                message: `Failed to update overtime for ${cleanedData.length} employee(s) on page ${currentOvertimePage}.`,
                note: `${errorMessage}. Please try again.`
            });
            $updateButton.prop('disabled', false).html(originalButtonHTML);
        }
    });
}

function applyDateFilter(isOvertime = false) {
    if (pendingRequests > 0) {
        return;
    }
    
    const rangeSelector = isOvertime ? '#overtimeReportrange span' : '#reportrange span';
    const dateRangeText = $(rangeSelector).text();
    
    if (!dateRangeText || dateRangeText.trim() === '') {
        return;
    }
    
    const dates = dateRangeText.split(' - ');
    if (dates.length !== 2) {
        return;
    }
    
    selectedRangeStart = moment(dates[0], 'MM/DD/YYYY');
    selectedRangeEnd = moment(dates[1], 'MM/DD/YYYY');
    
    currentWeekStart = moment(selectedRangeStart);
    
    const potentialWeekEnd = moment(currentWeekStart).add(6, 'days');
    currentWeekEnd = potentialWeekEnd.isAfter(selectedRangeEnd) ? 
                    moment(selectedRangeEnd) : potentialWeekEnd;
    
    selectedStartDate = currentWeekStart.format('YYYY-MM-DD');
    selectedEndDate = currentWeekEnd.format('YYYY-MM-DD');
    
    showLoadingIndicator(true);
    
    currentAttendancePage = 1;
    currentOvertimePage = 1;
    
    if (isOvertime) {
        if (isRegularUser) {
            loadEmployees('', '', true);
        } else {
            const subDepartment = $('#overtimeSub_departmentSelect').val();
            const groupType = $('#overtimeGroup_typeSelect').val();
            
            if (subDepartment && groupType) {
                loadEmployees(subDepartment, groupType, true);
            } else {
                showLoadingIndicator(false);
            }
        }
    } else {
        if (isRegularUser) {
            loadEmployees('', '', false);
        } else {
            const subDepartment = $('#sub_departmentSelect').val();
            const groupType = $('#group_typeSelect').val();
            
            if (subDepartment && groupType) {
                loadEmployees(subDepartment, groupType, false);
            } else {
                showLoadingIndicator(false);
            }
        }
    }
    
    if (!isOvertime) {
        $('#overtimeReportrange span').text(selectedRangeStart.format('MM/DD/YYYY') + ' - ' + selectedRangeEnd.format('MM/DD/YYYY'));
    } else {
        $('#reportrange span').text(selectedRangeStart.format('MM/DD/YYYY') + ' - ' + selectedRangeEnd.format('MM/DD/YYYY'));
    }
    
    updateWeekDisplay(false);
    updateWeekDisplay(true);
    updateNavigationButtons(false);
    updateNavigationButtons(true);
}

function initializeDatePicker(selector, callback) {
    const start = moment().startOf('week');
    const end = moment().endOf('week');

    $(selector).daterangepicker({
        startDate: start,
        endDate: end,
        showDropdowns: true,
        minYear: 2021,
        maxYear: parseInt(moment().format('YYYY'), 10) + 1,
        alwaysShowCalendars: true,
        autoApply: true,
        locale: {
            format: 'MM/DD/YYYY'
        },
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'This Week': [moment().startOf('week'), moment().endOf('week')],
            'Last Week': [moment().subtract(1, 'week').startOf('week'), moment().subtract(1, 'week').endOf('week')],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            'This Quarter': [moment().startOf('quarter'), moment().endOf('quarter')],
            'Last Quarter': [moment().subtract(1, 'quarter').startOf('quarter'), moment().subtract(1, 'quarter').endOf('quarter')],
            'This Year': [moment().startOf('year'), moment().endOf('year')],
            'Last Year': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')]
        }
    }, function(start, end) {
        callback(start, end);
        
        setTimeout(() => {
            const isOvertime = selector === '#overtimeReportrange' ? true : false;
            applyDateFilter(isOvertime);
        }, 300);
    });

    callback(start, end);
}

// ===== ATTENDANCE INPUT HANDLER =====
$(document).on('change', '.attendance-select', function() {
    const $select = $(this);
    const gid = $select.data('gid');
    const dateStr = $select.data('date');
    const value = $select.val();
    
    console.log('🔄 Attendance changed:', gid, dateStr, value);
    
    if (!liveAttendanceData[gid]) {
        liveAttendanceData[gid] = {};
    }
    liveAttendanceData[gid][dateStr] = value;
    console.log('✅ Stored in liveAttendanceData:', gid, dateStr, value);
    
    const selectedOption = $select.find('option:selected');
    const cssClass = selectedOption.attr('data-class');
    
    $select.removeClass('first-shift second-shift third_shift general-shift leave late half-day-first half-day-second outdoor training absent holiday');
    if (cssClass) {
        $select.addClass(cssClass);
    }
    
    updateDisplayedActualManHours();
    
    updateStatusTrackingForDate(dateStr, false);
    
    updateSubmitButtonStates(false);
    
    console.log('✅ Status and buttons updated for date:', dateStr);
});

$(document).on('input change', '.overtime-input', function() {
    const $input = $(this);
    const gid = $input.data('gid');
    const dateStr = $input.data('date');
    let value = parseFloat($input.val());
    
    // Handle empty input
    if (isNaN(value)) {
        value = '';
    }
    
    console.log('🔄 Overtime changed:', gid, dateStr, value);
    
    if (!liveOvertimeData[gid]) {
        liveOvertimeData[gid] = {};
    }
    liveOvertimeData[gid][dateStr] = value;
    console.log('✅ Stored in liveOvertimeData:', gid, dateStr, value);
    
    if (value !== '' && value !== null && value !== undefined) {
        if (value < 0) {
            value = 0;
            $input.val('0');
        } else if (value > 24) {
            value = 24;
            $input.val(24);
        }
        
        $input.removeClass('overtime-low overtime-medium overtime-high');
        if (value > 0 && value <= 4) {
            $input.addClass('overtime-low');
        } else if (value > 4 && value <= 8) {
            $input.addClass('overtime-medium');
        } else if (value > 8) {
            $input.addClass('overtime-high');
        }
    }
    
    const row = $input.closest('tr');
    let totalOvertimeHours = 0;
    row.find('.overtime-input').each(function() {
        const val = parseFloat($(this).val());
        if (!isNaN(val)) {
            totalOvertimeHours += val;
        }
    });
    row.find('.overtime-total').text(totalOvertimeHours.toFixed(1));
    
    // ===== UPDATE STATUS TRACKING AND BADGES =====
    console.log('🔄 Updating status for date:', dateStr);
    updateStatusTrackingForDate(dateStr, true);
    
    console.log('🔄 Refreshing header for date:', dateStr);
    refreshDateColumnHeader(dateStr, true);
    
    console.log('🔄 Updating button states');
    updateSubmitButtonStates(true);
    
    console.log('✅ Status and buttons updated for date:', dateStr);
});

// ===== SEARCH FUNCTIONALITY WITH PROPER SESSION STORAGE =====
let searchTimeout;
let currentSearchValue = '';

function performDataTableSearch(searchValue, isOvertime = false) {
    console.log('🔍 ===== performDataTableSearch START =====');
    console.log('Search value:', searchValue);
    console.log('Is Overtime:', isOvertime);
    
    currentSearchValue = searchValue;
    
    // ===== SAVE SEARCH VALUE TO SESSION STORAGE =====
    if (searchValue && searchValue.length > 0) {
        sessionStorage.setItem('lastSearchValue_' + (isOvertime ? 'overtime' : 'attendance'), searchValue);
        console.log('✅ Search value saved to session storage:', searchValue);
    } else {
        sessionStorage.removeItem('lastSearchValue_' + (isOvertime ? 'overtime' : 'attendance'));
        console.log('✅ Search value cleared from session storage');
    }
    
    showLoadingIndicator(true);
    
    // ===== GET CURRENT FILTERS =====
    let subDepartmentSelect, employmentTypeSelect, groupTypeSelect, joinedSelect;
    
    if (isOvertime) {
        subDepartmentSelect = $('#overtimeSub_departmentSelect').val();
        employmentTypeSelect = $('#overtimeemployment_typeSelect').val();
        groupTypeSelect = $('#overtimeGroup_typeSelect').val();
        joinedSelect = $('#overtimejoinedSelect').val();
    } else {
        subDepartmentSelect = $('#sub_departmentSelect').val();
        employmentTypeSelect = $('#employment_typeSelect').val();
        groupTypeSelect = $('#group_typeSelect').val();
        joinedSelect = $('#joinedSelect').val();
    }
    
    let selectedDepartments = [];
    let selectedEmploymentTypes = [];
    
    try {
        if (subDepartmentSelect && subDepartmentSelect.startsWith('[')) {
            selectedDepartments = JSON.parse(subDepartmentSelect);
        } else if (subDepartmentSelect) {
            selectedDepartments = [subDepartmentSelect];
        }
    } catch (e) {
        selectedDepartments = [];
    }
    
    try {
        if (employmentTypeSelect && employmentTypeSelect.startsWith('[')) {
            selectedEmploymentTypes = JSON.parse(employmentTypeSelect);
        } else if (employmentTypeSelect) {
            selectedEmploymentTypes = [employmentTypeSelect];
        }
    } catch (e) {
        selectedEmploymentTypes = [];
    }
    
    console.log('📋 Current Filters:', {
        departments: selectedDepartments,
        employmentTypes: selectedEmploymentTypes,
        groupType: groupTypeSelect,
        joined: joinedSelect,
        searchValue: searchValue
    });
    
    // ===== GENERATE DATE RANGE FOR TRANSFER CHECK =====
    const dateRangeForTransferCheck = [];
    const currentDate = moment(selectedRangeStart, 'YYYY-MM-DD');
    const endDate = moment(selectedRangeEnd, 'YYYY-MM-DD');
    
    while (currentDate.isSameOrBefore(endDate)) {
        dateRangeForTransferCheck.push(currentDate.format('YYYY-MM-DD'));
        currentDate.add(1, 'day');
    }
    
    // ===== BUILD AJAX REQUEST WITH SEARCH + FILTERS =====
    const ajaxData = {
        action: isOvertime ? 'fetchOvertimeData' : 'fetchAttendanceData',
        sub_department: isRegularUser ? '' : JSON.stringify(selectedDepartments),
        employment_type: isRegularUser ? '' : JSON.stringify(selectedEmploymentTypes),
        group_type: isRegularUser ? '' : (groupTypeSelect || ''),
        joined: joinedSelect || '',
        start_date: selectedRangeStart.format('YYYY-MM-DD'),
        end_date: selectedRangeEnd.format('YYYY-MM-DD'),
        transfer_check_start_date: selectedRangeStart.format('YYYY-MM-DD'),
        transfer_check_end_date: selectedRangeEnd.format('YYYY-MM-DD'),
        transfer_check_dates: JSON.stringify(dateRangeForTransferCheck),
        search_value: searchValue,  // ===== SEARCH VALUE =====
        search_fields: JSON.stringify(['name', 'gid']),  // ===== SEARCH FIELDS =====
        page: 1,  // ===== RESET TO PAGE 1 FOR SEARCH =====
        per_page: isOvertime ? overtimePageLength : attendancePageLength
    };
    
    if (isUserSupervisor()) {
        ajaxData.supervisor_id = getSupervisorId();
    }
    
    console.log('📤 Sending search AJAX:', ajaxData);
    
    $.ajax({
        url: '/dpm/api/PMSController.php',
        method: 'POST',
        data: ajaxData,
        dataType: 'json',
        timeout: 30000,
        success: function(response) {
            console.log('✅ Search AJAX Success:', response);
            
            showLoadingIndicator(false);
            
            if (response.success && response.employees && Array.isArray(response.employees)) {
                console.log('📊 Search returned', response.employees.length, 'employees');
                
                // ===== STORE HOLIDAYS =====
                if (response.holidays) {
                    window.holidaysData = response.holidays;
                    cachedHolidaysData = response.holidays;
                }
                
                // ===== STORE LEAVES =====
                if (response.leaves && Array.isArray(response.leaves)) {
                    cachedLeaveData = {};
                    response.leaves.forEach(record => {
                        const gid = record.gid;
                        if (!cachedLeaveData[gid]) {
                            cachedLeaveData[gid] = [];
                        }
                        cachedLeaveData[gid].push(record);
                    });
                }
                
                // ===== UPDATE GLOBAL VARIABLES =====
                if (isOvertime) {
                    currentOvertimeEmployees = response.employees;
                    totalOvertimeEmployees = response.pagination ? response.pagination.total : 0;
                    totalOvertimePages = response.pagination ? response.pagination.total_pages : 0;
                    currentOvertimePage = 1;  // ===== RESET TO PAGE 1 =====
                    
                    overtimePaginationInfo = {
                        current_page: response.pagination ? response.pagination.current_page : 1,
                        per_page: response.pagination ? response.pagination.per_page : 20,
                        total: response.pagination ? response.pagination.total : 0,
                        filtered: response.pagination ? response.pagination.filtered : 0,
                        total_pages: response.pagination ? response.pagination.total_pages : 0,
                        pagination_text: response.pagination ? response.pagination.pagination_text : 'Showing 0 to 0 of 0 entries'
                    };
                    
                    if (response.page_wise_status) {
                        overtimeStatusByDate = {};
                        Object.keys(response.page_wise_status).forEach(dateStr => {
                            const statusData = response.page_wise_status[dateStr];
                            overtimeStatusByDate[dateStr] = {
                                status: statusData.status,
                                saved: statusData.saved,
                                submitted: statusData.submitted,
                                unfilled: statusData.unfilled,
                                transferred: statusData.transferred || 0,
                                total: statusData.total_employees_on_page_for_date,
                                page: statusData.page,
                                total_pages: statusData.total_pages
                            };
                        });
                    }
                    
                    if (response.overtime) {
                        overtimeCachedData = response.overtime;
                    }
                    if (response.holiday_overtime) {
                        overtimeCachedData = (overtimeCachedData || []).concat(response.holiday_overtime);
                    }
                    
                    const combinedOvertimeData = {
                        overtime: response.overtime || [],
                        holiday_overtime: response.holiday_overtime || []
                    };
                    
                    $('#overtimeDetailsegment').show();
                    createServerSidePaginatedTable(true, response.employees, combinedOvertimeData, overtimePaginationInfo);
                    
                    setTimeout(() => {
                        if (response.leaves && response.leaves.length > 0) {
                            applyLeaveDataToTable(response.leaves, selectedRangeStart.format('YYYY-MM-DD'), selectedRangeEnd.format('YYYY-MM-DD'), true);
                        }
                        populateExistingOvertimeData(combinedOvertimeData);
                        applyResponsiveTableLayout(true);
                        updateSubmitButtonStates(true);
                        refreshAllDateBadges(true);
                    }, 300);
                } else {
                    currentEmployees = response.employees;
                    totalAttendanceEmployees = response.pagination ? response.pagination.total : 0;
                    totalAttendancePages = response.pagination ? response.pagination.total_pages : 0;
                    currentAttendancePage = 1;  // ===== RESET TO PAGE 1 =====
                    
                    attendancePaginationInfo = {
                        current_page: response.pagination ? response.pagination.current_page : 1,
                        per_page: response.pagination ? response.pagination.per_page : 20,
                        total: response.pagination ? response.pagination.total : 0,
                        filtered: response.pagination ? response.pagination.filtered : 0,
                        total_pages: response.pagination ? response.pagination.total_pages : 0,
                        pagination_text: response.pagination ? response.pagination.pagination_text : 'Showing 0 to 0 of 0 entries'
                    };
                    
                    if (response.page_wise_status) {
                        attendanceStatusByDate = {};
                        Object.keys(response.page_wise_status).forEach(dateStr => {
                            const statusData = response.page_wise_status[dateStr];
                            attendanceStatusByDate[dateStr] = {
                                status: statusData.status,
                                saved: statusData.saved,
                                submitted: statusData.submitted,
                                unfilled: statusData.unfilled,
                                transferred: statusData.transferred || 0,
                                total: statusData.total_employees_on_page_for_date,
                                page: statusData.page,
                                total_pages: statusData.total_pages
                            };
                        });
                    }
                    
                    if (response.attendance) {
                        attendanceCachedData = response.attendance;
                    }
                    
                    $('#detailsegment').show();
                    createServerSidePaginatedTable(false, response.employees, response.attendance || [], attendancePaginationInfo);
                    
                    setTimeout(() => {
                        if (response.leaves && response.leaves.length > 0) {
                            applyLeaveDataToTable(response.leaves, selectedRangeStart.format('YYYY-MM-DD'), selectedRangeEnd.format('YYYY-MM-DD'), false);
                        }
                        populateExistingAttendanceData(response.attendance || []);
                        applyResponsiveTableLayout(false);
                        updateSubmitButtonStates(false);
                        refreshAllDateBadges(false);
                        calculateInitialManHours();
                    }, 300);
                }
                
                console.log('✅ Search results displayed');
            } else {
                showCustomAlert({
                    type: 'info',
                    title: 'No Results',
                    message: `No employees found matching "${searchValue}" with current filters.`,
                    note: '📋 Try adjusting your search or filters.'
                });
                
                if (isOvertime) {
                    $('#overtimeDetailsegment').hide();
                } else {
                    $('#detailsegment').hide();
                }
            }
        },
        error: function(xhr, status, error) {
            console.error('❌ Search AJAX Error:', status, error);
            showLoadingIndicator(false);
            
            showCustomAlert({
                type: 'error',
                title: 'Search Error',
                message: 'Error performing search',
                note: '🔄 Please try again.'
            });
        }
    });
    
    console.log('🔍 ===== performDataTableSearch END =====');
}

// ===== INITIALIZE DATATABLE SEARCH WITH PROPER DEBOUNCING =====
function initializeDataTableSearch() {
    // ===== REMOVE OLD HANDLERS =====
    $(document).off('keyup.DT', '.dataTables_filter input');
    
    // ===== ADD NEW HANDLER WITH PROPER DEBOUNCING =====
    $(document).on('keyup.DT', '.dataTables_filter input', function(e) {
        const searchValue = $(this).val().trim();
        const $activeTab = $('#attendanceTab.active, #overtimeTab.active');
        const activeTabId = $activeTab.attr('id');
        const isOvertimeTab = (activeTabId === 'overtimeTab');
        
        console.log('🔍 DataTable Search triggered:', {
            searchValue: searchValue,
            activeTab: activeTabId,
            isOvertime: isOvertimeTab,
            keyCode: e.keyCode,
            inputLength: searchValue.length
        });
        
        // ===== CLEAR PREVIOUS TIMEOUT =====
        clearTimeout(searchTimeout);
        
        if (searchValue.length === 0) {
            // ===== EMPTY SEARCH - RELOAD WITH FILTERS =====
            console.log('🔄 Empty search - reloading with current filters');
            
            let subDepartmentSelect, groupTypeSelect;
            
            if (isOvertimeTab) {
                subDepartmentSelect = $('#overtimeSub_departmentSelect').val();
                groupTypeSelect = $('#overtimeGroup_typeSelect').val();
            } else {
                subDepartmentSelect = $('#sub_departmentSelect').val();
                groupTypeSelect = $('#group_typeSelect').val();
            }
            
            currentAttendancePage = 1;
            currentOvertimePage = 1;
            
            // ===== CLEAR THE SEARCH FROM SESSION STORAGE =====
            sessionStorage.removeItem('lastSearchValue_' + (isOvertimeTab ? 'overtime' : 'attendance'));
            console.log('✅ Search value removed from session storage');
            
            loadEmployees(subDepartmentSelect, groupTypeSelect, isOvertimeTab);
            return;
        }
        
        if (searchValue.length < 2) {
            // ===== MINIMUM 2 CHARACTERS =====
            console.log('⚠️ Search term too short (minimum 2 characters)');
            return;
        }
        
        // ===== DEBOUNCE SEARCH TO AVOID TOO MANY REQUESTS =====
        searchTimeout = setTimeout(() => {
            console.log('⏱️ Debounce timeout triggered, executing search');
            performDataTableSearch(searchValue, isOvertimeTab);
        }, 500);
    });
}

// ===== RESTORE SEARCH VALUE ON TABLE INITIALIZATION =====
// Update the initComplete function in createServerSidePaginatedTable to include:

// Inside the initComplete callback, add this code:
function restoreSearchValue(isOvertime) {
    const savedSearchValue = sessionStorage.getItem('lastSearchValue_' + (isOvertime ? 'overtime' : 'attendance'));
    const tableId = isOvertime ? '#table_overtime_items' : '#table_all_items';
    
    if (savedSearchValue && savedSearchValue.length > 0) {
        const $searchInput = $(tableId + '_wrapper .dataTables_filter input');
        if ($searchInput.length > 0) {
            $searchInput.val(savedSearchValue);
            console.log('✅ Restored search value from session:', savedSearchValue);
        }
    } else {
        const $searchInput = $(tableId + '_wrapper .dataTables_filter input');
        if ($searchInput.length > 0) {
            $searchInput.val('');
            console.log('✅ Search input cleared (no saved value)');
        }
    }
}

let lastRefreshDate = moment().format('YYYY-MM-DD');

function checkAndRefreshForExpiredTransfers() {
    const today = moment().format('YYYY-MM-DD');
    
    if (today !== lastRefreshDate) {
        lastRefreshDate = today;
        
        if (dataTable) {
            dataTable.draw();
        }
        if (overtimeDataTable) {
            overtimeDataTable.draw();
        }
    }
}

setInterval(checkAndRefreshForExpiredTransfers, 3600000);

$(document).on('visibilitychange', function() {
    if (!document.hidden) {
        checkAndRefreshForExpiredTransfers();
    }
});

function checkAndLoadEmployees() {
    if (isRegularUser) {
        loadEmployees('', '', false);
        return;
    }
    
    const subDepartmentValue = $('#sub_departmentSelect').val();
    const groupType = $('#group_typeSelect').val();
    
    let selectedDepartments = [];
    try {
        if (subDepartmentValue && subDepartmentValue.startsWith('[')) {
            selectedDepartments = JSON.parse(subDepartmentValue);
        }
    } catch (e) {
        // ignore
    }
    
    if (selectedDepartments.length === 0 || !groupType) {
        $('#detailsegment').hide();
        return;
    }
    
    loadEmployees(subDepartmentValue, groupType, false);
}

function checkAndLoadOvertimeEmployees() {
    if (isRegularUser) {
        loadEmployees('', '', true);
        return;
    }
    
    const subDepartmentValue = $('#overtimeSub_departmentSelect').val();
    const groupType = $('#overtimeGroup_typeSelect').val();
    
    let selectedDepartments = [];
    try {
        if (subDepartmentValue && subDepartmentValue.startsWith('[')) {
            selectedDepartments = JSON.parse(subDepartmentValue);
        }
    } catch (e) {
        // ignore
    }
    
    if (selectedDepartments.length === 0 || !groupType) {
        $('#overtimeDetailsegment').hide();
        return;
    }
    
    loadEmployees(subDepartmentValue, groupType, true);
}

// ===== UPDATED: refreshDateColumnHeader function =====
function refreshDateColumnHeader(dateStr, isOvertime = false) {
    console.log('🔄 Refreshing date column header for:', dateStr, 'isOvertime:', isOvertime);
    
    const tableId = isOvertime ? '#table_overtime_items' : '#table_all_items';
    const $table = $(tableId);
    
    if ($table.length === 0) {
        console.warn('⚠️ Table not found:', tableId);
        return;
    }
    
    const $thead = $table.find('thead');
    const $headerCells = $thead.find('th');
    
    let headerIndex = -1;
    
    $headerCells.each(function(index) {
        const $th = $(this);
        const cellDateStr = $th.find('.date-header').data('date');
        
        if (cellDateStr === dateStr) {
            headerIndex = index;
            return false;
        }
    });
    
    if (headerIndex === -1) {
        console.warn('⚠️ Date column not found for:', dateStr, 'in table:', tableId);
        return;
    }
    
    console.log('📍 Found date column at index:', headerIndex, 'in table:', tableId);
    
    const $headerCell = $headerCells.eq(headerIndex);
    
    const dayName = moment(dateStr, 'YYYY-MM-DD').format('ddd');
    const displayDate = moment(dateStr, 'YYYY-MM-DD').format('DD MMM');
    const isFuture = isFutureDate(dateStr);
    
    const holidays = window.holidaysData || cachedHolidaysData || {};
    const isHoliday = holidays[dateStr] === 'holiday';
    
    // ===== CRITICAL: Recalculate status for this date - ONLY FOR THIS TABLE =====
    updateStatusTrackingForDate(dateStr, isOvertime);
    
    const status = getDateStatus(dateStr, isOvertime);
    
    console.log('🏷️ New status for', dateStr, '(', isOvertime ? 'Overtime' : 'Attendance', '):', status);
    
    const newHeaderHTML = generateDateColumnHeader(dateStr, displayDate, isHoliday, isOvertime);
    
    $headerCell.html(newHeaderHTML);
    
    console.log('✅ Date column header refreshed for:', dateStr, 'in table:', tableId);
    
    // ===== REBIND BUTTON CLICK HANDLERS =====
    rebindDateColumnButtonHandlers($headerCell, dateStr, isOvertime);
}

function rebindDateColumnButtonHandlers($headerCell, dateStr, isOvertime) {
    console.log('🔗 Rebinding button handlers for date:', dateStr, 'isOvertime:', isOvertime);
    
    // ===== REMOVE OLD HANDLERS =====
    $headerCell.find('.save-date-btn, .save-overtime-date-btn').off('click');
    $headerCell.find('.submit-date-btn, .submit-overtime-date-btn').off('click');
    $headerCell.find('.update-date-btn, .update-overtime-date-btn').off('click');
    
    if (isOvertime) {
        // ===== SAVE OVERTIME BUTTON HANDLER =====
        $headerCell.find('.save-overtime-date-btn').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('🔘 Save overtime button clicked (rebound) for date:', dateStr);
            saveOvertimeForDate(dateStr);
            return false;
        });
        
        // ===== SUBMIT OVERTIME BUTTON HANDLER =====
        $headerCell.find('.submit-overtime-date-btn').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('🔘 Submit overtime button clicked (rebound) for date:', dateStr);
            submitOvertimeForDate(dateStr);
            return false;
        });
        
        // ===== UPDATE OVERTIME BUTTON HANDLER =====
        $headerCell.find('.update-overtime-date-btn').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('🔘 Update overtime button clicked (rebound) for date:', dateStr);
            updateOvertimeForDate(dateStr);
            return false;
        });
        
        console.log('✅ Overtime button handlers rebound for date:', dateStr);
    } else {
        // ===== SAVE ATTENDANCE BUTTON HANDLER =====
        $headerCell.find('.save-date-btn').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('🔘 Save attendance button clicked (rebound) for date:', dateStr);
            saveAttendanceForDate(dateStr);
            return false;
        });
        
        // ===== SUBMIT ATTENDANCE BUTTON HANDLER =====
        $headerCell.find('.submit-date-btn').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('🔘 Submit attendance button clicked (rebound) for date:', dateStr);
            submitAttendanceForDate(dateStr);
            return false;
        });
        
        // ===== UPDATE ATTENDANCE BUTTON HANDLER =====
        $headerCell.find('.update-date-btn').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('🔘 Update attendance button clicked (rebound) for date:', dateStr);
            updateAttendanceForDate(dateStr);
            return false;
        });
        
        console.log('✅ Attendance button handlers rebound for date:', dateStr);
    }
}

// ===== UPDATED: refreshAllDateBadges function =====
function refreshAllDateBadges(isOvertime = false) {
    console.log('🔄 Refreshing all date badges for:', isOvertime ? 'Overtime' : 'Attendance');
    
    const statusMap = isOvertime ? overtimeStatusByDate : attendanceStatusByDate;
    const allDates = Object.keys(statusMap);
    
    console.log('📅 Dates to refresh:', allDates, 'for', isOvertime ? 'Overtime' : 'Attendance');
    
    allDates.forEach(dateStr => {
        // ===== RECALCULATE STATUS - ONLY FOR THIS TABLE =====
        updateStatusTrackingForDate(dateStr, isOvertime);
        
        // ===== REFRESH THE HEADER - ONLY FOR THIS TABLE =====
        refreshDateColumnHeader(dateStr, isOvertime);
    });
    
    // ===== UPDATE BUTTON STATES - ONLY FOR THIS TABLE =====
    updateSubmitButtonStates(isOvertime);
    
    console.log('✅ All date badges refreshed for:', isOvertime ? 'Overtime' : 'Attendance');
}

function debugAttendanceData(dateStr) {
    console.log('\n🔍 ===== DEBUG ATTENDANCE DATA =====');
    console.log('Date:', dateStr);
    console.log('\n📊 Live Attendance Data:');
    console.log(liveAttendanceData);
    
    console.log('\n📋 Cached Attendance Data:');
    if (Array.isArray(attendanceCachedData)) {
        const filtered = attendanceCachedData.filter(r => r.attendance_date === dateStr);
        console.log('Records for this date:', filtered.length);
        filtered.forEach(r => {
            console.log(`  ${r.gid}: ${r.attendance_status}`);
        });
    }
    
    console.log('\n🔗 Select Elements on Page:');
    const selects = $(`select.attendance-select[data-date="${dateStr}"]`);
    console.log('Found selects:', selects.length);
    selects.each(function() {
        const $sel = $(this);
        console.log(`  ${$sel.data('gid')}: value="${$sel.val()}", greyed=${$sel.hasClass('dept-submitted')}`);
    });
    
    console.log('\n👥 Current Employees on Page:');
    console.log('Count:', currentEmployees.length);
    currentEmployees.forEach(emp => {
        console.log(`  ${emp.gid}: ${emp.name}`);
    });
    console.log('===== END DEBUG =====\n');
}

// ===== GLOBAL VARIABLES =====
let currentEmployees = [];
let currentOvertimeEmployees = [];
let selectedStartDate = moment().startOf('week').format('YYYY-MM-DD');
let selectedEndDate = moment().endOf('week').format('YYYY-MM-DD');
let currentsub_department = '';
let dataTable = null;
let overtimeDataTable = null;
let totalDateColumns = 0;
let pendingRequests = 0;
let currentWeekStart = moment().startOf('week');
let currentWeekEnd = moment().endOf('week');
let selectedRangeStart = moment().startOf('week');
let selectedRangeEnd = moment().endOf('week');
let isAdminUser = false;
let isSupervisorUser = false;
let isRegularUser = false;
let cachedLeaveData = {};
let cachedHolidaysData = {};
let attendanceCachedData = [];
let overtimeCachedData = [];
let currentInfoMessage = null;

let currentAttendancePage = 1;
let currentOvertimePage = 1;
let attendancePageLength = 10;
let overtimePageLength = 10;
let totalAttendanceEmployees = 0;
let totalOvertimeEmployees = 0;
let totalAttendancePages = 0;
let totalOvertimePages = 0;

let attendancePaginationInfo = {
    current_page: 1,
    per_page: 20,
    total: 0,
    filtered: 0,
    total_pages: 0,
    pagination_text: 'Showing 0 to 0 of 0 entries'
};

let overtimePaginationInfo = {
    current_page: 1,
    per_page: 20,
    total: 0,
    filtered: 0,
    total_pages: 0,
    pagination_text: 'Showing 0 to 0 of 0 entries'
};

let liveAttendanceData = {};
let liveOvertimeData = {};

let userCurrentShift = null;
let userHasShiftEntry = false;
let userAttendanceRecords = [];

let attendanceDeptDropdown, attendanceEmpTypeDropdown;
let overtimeDeptDropdown, overtimeEmpTypeDropdown;

// ===== STATUS TRACKING FOR DATES =====
let attendanceStatusByDate = {};
let overtimeStatusByDate = {};

// ===== NEW: Store page_wise_status from AJAX response =====
let overtimePageWiseStatus = {};

const shiftValueMap = {
    '1': 'first_shift',
    '2': 'second_shift',
    '3': 'third_shift',
    'first_shift': 'first_shift',
    'second_shift': 'second_shift',
    'third_shift': 'third_shift'
};

const attendanceOptions = [
    { value: '', text: 'Select Status', hours: 0, class: '' },
    { value: 'first_shift', text: '1st Shift', hours: 8.5, class: 'first-shift' },
    { value: 'second_shift', text: '2nd Shift', hours: 8.5, class: 'second-shift' },
    { value: 'third_shift', text: '3rd Shift', hours: 8.5, class: 'third_shift' },
    { value: 'general_shift', text: 'General Shift', hours: 8.5, class: 'general-shift' },
    { value: 'outdoor', text: 'Outdoor', hours: 8.5, class: 'outdoor' },
    { value: 'training', text: 'Training', hours: 8.5, class: 'training' },
    { value: 'leave', text: 'Leave', hours: 0, class: 'leave' },
    { value: 'absent', text: 'Absent', hours: 0, class: 'absent' },
    { value: 'holiday', text: 'Holiday', hours: 0, class: 'holiday' },
    { value: 'one_hour_late', text: '1hr Late', hours: 7.5, class: 'late' },
    { value: 'two_hour_late', text: '2hr Late', hours: 6.5, class: 'late' },
    { value: 'general_shift_half', text: 'General Shift Half Day', hours: 4.25, class: 'half-day-general' },
    { value: 'first_shift_half', text: '1st Shift Half Day', hours: 4.25, class: 'half-day-first' },
    { value: 'second_shift_half', text: '2nd Shift Half Day', hours: 4.25, class: 'half-day-second' }
];

$(document).ready(function() {
    isAdminUser = isUserAdmin();
    isSupervisorUser = isUserSupervisor();
    isRegularUser = isUserRegularUser();
    
    initializeResponsiveTableLayout();
    applyZoomAdjustments();
    initializeAllCustomDropdowns();

    setTimeout(() => {
        updateJoinedDropdownBasedOnEmploymentType(false);
        updateJoinedDropdownBasedOnEmploymentType(true);
    }, 500);
    
    $('#joinedSelect').val('after');
    $('#overtimejoinedSelect').val('after');
    
    const activeTab = sessionStorage.getItem('activeTab') || 'attendance';
    showTab(activeTab);
    
    $('#detailsegment').hide();
    $('#overtimeDetailsegment').hide();
    
    selectedRangeStart = moment().startOf('week');
    selectedRangeEnd = moment().endOf('week');
    currentWeekStart = moment().startOf('week');
    currentWeekEnd = moment().endOf('week');
    selectedStartDate = currentWeekStart.format('YYYY-MM-DD');
    selectedEndDate = currentWeekEnd.format('YYYY-MM-DD');
    
    updateWeekDisplay(false);
    updateWeekDisplay(true);
    
    $('#joinedSelect').on('change', function() {
        checkAndLoadEmployees();
    });
    
    $('#overtimejoinedSelect').on('change', function() {
        checkAndLoadOvertimeEmployees();
    });
    
    $('#attendanceTab .prev-week').on('click', function() {
        navigateWeek('prev', false);
    });

    $('#attendanceTab .next-week').on('click', function() {
        navigateWeek('next', false);
    });

    $('#overtimeTab .prev-week').on('click', function() {
        navigateWeek('prev', true);
    });

    $('#overtimeTab .next-week').on('click', function() {
        navigateWeek('next', true);
    });
    
    initializeDatePicker('#reportrange', function(start, end) {
        selectedRangeStart = moment(start);
        selectedRangeEnd = moment(end);
        
        $('#reportrange span').html(selectedRangeStart.format('MM/DD/YYYY') + ' - ' + selectedRangeEnd.format('MM/DD/YYYY'));
        $('#overtimeReportrange span').html(selectedRangeStart.format('MM/DD/YYYY') + ' - ' + selectedRangeEnd.format('MM/DD/YYYY'));
        
        currentWeekStart = moment(selectedRangeStart);
        
        const potentialWeekEnd = moment(currentWeekStart).add(6, 'days');
        currentWeekEnd = potentialWeekEnd.isAfter(selectedRangeEnd) ? 
                        moment(selectedRangeEnd) : potentialWeekEnd;
        
        selectedStartDate = currentWeekStart.format('YYYY-MM-DD');
        selectedEndDate = currentWeekEnd.format('YYYY-MM-DD');
        
        updateWeekDisplay(false);
        updateWeekDisplay(true);
        updateNavigationButtons(false);
        updateNavigationButtons(true);
    });

    initializeDatePicker('#overtimeReportrange', function(start, end) {
        selectedRangeStart = moment(start);
        selectedRangeEnd = moment(end);
        
        $('#overtimeReportrange span').html(selectedRangeStart.format('MM/DD/YYYY') + ' - ' + selectedRangeEnd.format('MM/DD/YYYY'));
        $('#reportrange span').html(selectedRangeStart.format('MM/DD/YYYY') + ' - ' + selectedRangeEnd.format('MM/DD/YYYY'));
        
        currentWeekStart = moment(selectedRangeStart);
        
        const potentialWeekEnd = moment(currentWeekStart).add(6, 'days');
        currentWeekEnd = potentialWeekEnd.isAfter(selectedRangeEnd) ? 
                        moment(selectedRangeEnd) : potentialWeekEnd;
        
        selectedStartDate = currentWeekStart.format('YYYY-MM-DD');
        selectedEndDate = currentWeekEnd.format('YYYY-MM-DD');
        
        updateWeekDisplay(false);
        updateWeekDisplay(true);
        updateNavigationButtons(false);
        updateNavigationButtons(true);
    });

    if (isRegularUser) {
        setTimeout(() => {
            loadEmployees('', '', false);
            
            setTimeout(() => {
                loadEmployees('', '', true);
            }, 500);
        }, 1000);
    } else {
        if (isUserSupervisor()) {
            setTimeout(function() {
                updateSubDepartmentOptions();
            }, 500);
        }
    }
    
    handleTableResize();
    
    setTimeout(() => {
        applyResponsiveTableLayout(false);
        applyResponsiveTableLayout(true);
    }, 500);
    
    setInterval(function() {
        applyZoomAdjustments();
    }, 1000);

     $(document).on('click', '.save-date-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const dateStr = $(this).data('date');
        console.log('🔘 Save button clicked for date:', dateStr);
        saveAttendanceForDate(dateStr);
        return false;
    });

    $(document).on('click', '.submit-date-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const dateStr = $(this).data('date');
        console.log('🔘 Submit button clicked for date:', dateStr);
        submitAttendanceForDate(dateStr);
        return false;
    });

    $(document).on('click', '.update-date-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const dateStr = $(this).data('date');
        console.log('🔘 Update button clicked for date:', dateStr);
        updateAttendanceForDate(dateStr);
        return false;
    });

    $(document).on('click', '.save-overtime-date-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const dateStr = $(this).data('date');
        console.log('🔘 Save overtime button clicked for date:', dateStr);
        saveOvertimeForDate(dateStr);
        return false;
    });

    $(document).on('click', '.submit-overtime-date-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const dateStr = $(this).data('date');
        console.log('🔘 Submit overtime button clicked for date:', dateStr);
        console.log('Button element:', $(this));
        console.log('Data attribute:', $(this).data('date'));
        submitOvertimeForDate(dateStr);
        return false;
    });

    $(document).on('click', '.update-overtime-date-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const dateStr = $(this).data('date');
        console.log('🔘 Update overtime button clicked for date:', dateStr);
        updateOvertimeForDate(dateStr);
        return false;
    });
});