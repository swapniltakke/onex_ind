let JTDataOfProject = [];

async function getJTTypicalAndPanels() {
    try {
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/NachbauController.php', {
            params: { action: 'getJTTypicalAndPanels', projectNo: getUrlParam('project-no') }
        });

        JTDataOfProject = response.data;
    } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        showErrorDialog(`Error: ${errorMessage}`);
    }
}

async function showJTCollectionCardsByType() {
    const nachbauNo = getUrlParam('nachbau-no');
    const type = getUrlParam('type');
    const typeNumber = getUrlParam('type-number');

    hideElement('#jtCollectionMessage');
    hideElement('#jtCollectButton');
    showElement('#jtCollectionInfo');
    $('#jtCollectionCards').empty().transition('pulse');
    let data, headerText;

    if (type === 'Typical') {
        headerText = 'Filtered by Typical';
        data = typeNumber
            ? { [typeNumber]: JTDataOfProject[nachbauNo]['Typical'][typeNumber] }
            : JTDataOfProject[nachbauNo]['Typical'];
    } else if (type === 'Panel') {
        headerText = 'Filtered by Panel';
        data = typeNumber
            ? { [typeNumber]: JTDataOfProject[nachbauNo]['Panel'][typeNumber] }
            : JTDataOfProject[nachbauNo]['Panel'];
    } else if (type === 'Accessories') {
        return;
    }

    // const minusPriceTypicalNos = MinusPriceDtosOfProject.map(dto => dto.typical_no);


    Object.entries(data).forEach(([key, value]) => {
        $('#jtCollectionFilterHeader').html(`<h3 style="text-align:center;margin-bottom:1.2rem;">${headerText}</h3>`);
        const ortzKzNumbers = type === 'Typical' ? Object.keys(value) : [key];
        let isFirst = true;

        ortzKzNumbers.forEach((ortzKz, index) => {
            const panelNo = value[ortzKz]?.panel_no || value.panel_no;
            const typicalNo = value[ortzKz]?.typical_no || value.typical_no || key;

            // // Skip Minus Price Typical Numbers
            // if (minusPriceTypicalNos.includes(typicalNo)) {
            //     return;
            // }
            const isFirstTypical = index === 0;

            const card = $(`
                <div class="ui card ${isFirstTypical ? 'first-typical-card' : ''}" style="margin: 1rem;" 
                    data-typical="${typicalNo}" data-ortzkz="${ortzKz}" data-panelno="${panelNo}">
                    <div class="content">
                        <div class="header">${type === 'Typical' ? typicalNo : ortzKz}</div>
                        <div class="meta" style="margin-top:0.5rem;color:#003333;font-weight:700;">
                            ${type === 'Typical' ? 'OrtzKz' : 'Typical Number'} : ${type === 'Typical' ? ortzKz : typicalNo}
                        </div>
                        <div class="description">Panel Number: ${panelNo}</div>
                    </div>
                    <div class="extra content" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="left-aligned">
                            <div class="ui checkbox" style="display: flex; align-items: center; gap: 5px;">
                                <input type="checkbox" class="jt-option" data-mode="dto" data-typical="${typicalNo}" data-ortzkz="${ortzKz}" data-panelno="${panelNo}">
                                <label style="margin: 0;">DTO</label>
                                <span class="jt-check-icon" style="display: none; color: green;">
                                    <i class="check circle big icon"></i>
                                </span>
                            </div>
                        </div>
                        <div class="right-aligned" style="display:flex;justify-content:end;width:100%;">
                            <div class="ui checkbox" style="display: flex; align-items: center; gap: 5px;">
                                <input type="checkbox" class="jt-option" data-mode="standard" data-typical="${typicalNo}" data-ortzkz="${ortzKz}" data-panelno="${panelNo}">
                                <label>Standard</label>
                                <span class="jt-check-icon" style="display:none; color:green;">
                                    <i class="check circle big icon"></i>
                                </span>
                                
                            </div>
                        </div>
                    </div>
                </div>
            `);

            $('#jtCollectionCards').append(card);
            card.find('.ui.checkbox').checkbox();
            isFirst = false;
        });
    });

    if ($('#jtCollectButton').length === 0) {
        $('#jtCollectionContainer').append(`
            <div id="jtCollectButton" class="ui center aligned container" style="margin-top: 2rem;">
                <button class="ui primary button" id="collectJTs">COLLECT JT's</button>
            </div>
        `);
    }

    showElement('#jtCollectButton');
    hideLoader('#jtCollection');
    showElement('#jtCollectionContainer');
}


// Collect JT files when the button is clicked
$(document).on('click', '#collectJTs', function () {
    $('#collectJTs').addClass('loading disabled');
    const selectedOptions = [];

    // Find all checked checkboxes
    $('#jtCollectionCards input:checked').each(function () {
        const mode = $(this).data('mode');
        const typical = $(this).data('typical');
        const ortzkz = $(this).data('ortzkz');
        const panelno = $(this).data('panelno');

        selectedOptions.push(`${mode},${typical},${ortzkz},${panelno}`);
    });

    if (selectedOptions.length === 0) {
        fireToastr("error", "Please select at least one option.");
        $('#collectJTs').removeClass('loading disabled');
        return;
    }

    if (selectedOptions.length >= 5) {
        fireToastr("error", "Please choose a maximum of four options.");
        $('#collectJTs').removeClass('loading disabled');
        return;
    }

    // Encode JT Data array to handle special characters
    const encodedJTNumbers = selectedOptions.map(option => encodeURIComponent(option)).join('|');
    Swal.fire({
        title: "Collecting JT files...",
        html: "This process may take up to one minute. Please wait.",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });

    const url = `/dpm/dtoconfigurator/api/controllers/JTCollect?project-no=${getUrlParam('project-no')}&nachbau-no=${getUrlParam('nachbau-no')}&jt-numbers=${encodedJTNumbers}`;
    const jtWindow = window.open(url, "_blank");

    const interval = setInterval(() => {
        if (jtWindow.closed) {
            clearInterval(interval);
            Swal.close();
            $('#collectJTs').removeClass('loading disabled');
            fireToastr("success", "JT files collected successfully!");

            // Show check icons for downloaded checkboxes
            $('#jtCollectionCards input:checked').each(function () {
                $(this).siblings('.jt-check-icon').fadeIn();
            });

            $('#jtCollectionCards input:checked').prop('checked', false);
        }
    }, 1000);
});
