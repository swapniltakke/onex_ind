let assemblyStartGlobal = '';

$(document).ready(function() {
    $('#projectDataTabularMenu .item').tab();

    // Add a click event listener to all tab menu items
    $('#projectDataTabularMenu .item').on('click', function () {
        // Remove the 'active' class from all tabs and hide their content
        $('#projectDataTabularMenu .menu .item').removeClass('active');
        $('#projectDataTabs .tab.segment').css('display', 'none');

        // Add the 'active' class to the clicked tab and show its content
        $(this).addClass('active');
        const tabName = $(this).data('tab');
        $(`#${tabName}`).css('display', 'block');
    });

    // Initialize the first active tab
    $('#projectDataTabularMenu .item.active').trigger('click');


    removeUrlParamArray(['nachbau-no', 'type', 'type-number', 'dto-number']);
    initializeSearchProjectSelect();
    getProjectInfo();
});


async function getProjectInfo() {
    try {
        const projectNo = new URLSearchParams(window.location.search).get('project-no');
        const url = `/dpm/dtoconfigurator/api/controllers/ProjectController.php?action=getProjectInfo&projectNo=${projectNo}`;
        const response = await axios.get(url, { projectNo: projectNo });

        if (response.status === 200) {
            const data = response.data;

            $('#downloadProjectFolder').html(`<a href="/dpm/dtoconfigurator/partials/projectPathDownload.php?project=${data.FactoryNumber}&mode=download" 
                                                 data-tooltip="Click here to download project folder" data-position="top left">
                                                <i class="download icon"></i>
                                              </a>`);
            $('#projectNumberLabel').text(data.FactoryNumber);
            $('#projectNameLabel').text(data.ProjectName);
            $('#orderManagerLabel').text(data.OrderManager);
            $('#productLabel').text(data.Product);

            if (data.Product === 'NXAIR') {
                const ratedShortCircuit = parseFloat(data.ratedShortCircuit.replace(',', '.')); //kA
                const ratedCurrent = parseInt(data.ratedCurrent, 10); // "A"

                if ((ratedShortCircuit < 31.5) || (ratedShortCircuit === 31.5 && ratedCurrent <= 2500)) {
                    $('#packTypeLabel').text('Pack 1');
                } else if ((ratedShortCircuit === 40) || (ratedShortCircuit === 31.5 && ratedCurrent > 2500)) {
                    $('#packTypeLabel').text('Pack 2');
                } else {
                    $('#packTypeLabel').text('Unknown');
                }

                showElement('#packTypeDiv');
            }

            $('#panelCountLabel').text(data.Qty);
            $('#ratedVoltageLabel').text(data.ratedVoltage + ' kV');
            $('#ratedShortCircuitLabel').text(data.ratedShortCircuit + ' kA');
            $('#ratedCurrentLabel').text(data.ratedCurrent + ' A');
            $('#assemblyStartDateLabel').text('Ass. Start: ' + data.assemblyStartDate ?? 'Unknown');
            assemblyStartGlobal = data.assemblyStartDate;


            $('#panelCountLabel').text(data.Qty);
            $('#ratedVoltageLabel').text(data.ratedVoltage + ' kV');
            $('#ratedShortCircuitLabel').text(data.ratedShortCircuit + ' kA');
            $('#ratedCurrentLabel').text(data.ratedCurrent + ' A');
            $('#assemblyStartDateLabel').text('Ass. Start: ' + data.assemblyStartDate ?? 'Unknown');

// Assembly Start Remaining Week hesaplama
            if (data.assemblyStartDate) {
                const today = new Date();
                today.setHours(0, 0, 0, 0); // Zamanı sıfırla, sadece tarihi karşılaştır

                // "23.10.2025" formatını parse et
                const dateParts = data.assemblyStartDate.split('.');
                const day = parseInt(dateParts[0], 10);
                const month = parseInt(dateParts[1], 10) - 1; // JavaScript'te aylar 0-11 arası
                const year = parseInt(dateParts[2], 10);

                const assemblyDate = new Date(year, month, day);
                assemblyDate.setHours(0, 0, 0, 0);

                // Tarihler arası farkı hesapla (milisaniye cinsinden)
                const timeDiff = assemblyDate - today;

                // Milisaniyeyi günlere çevir
                const daysDiff = timeDiff / (1000 * 60 * 60 * 24);

                // Günleri haftaya çevir
                let weeksDiff = daysDiff / 7;

                // 0'ın altına inmesin
                weeksDiff = Math.max(0, weeksDiff);

                // Label'ı güncelle
                $('#assemblyStartRemainingWeekLabel').text(weeksDiff.toFixed(1) + ' weeks');

                // Mevcut class'ları temizle
                $('#assemblyStartRemainingWeek').removeClass('red green');

                // Duruma göre class ve icon ekle
                if (weeksDiff < 6.5) {
                    $('#assemblyStartRemainingWeek').addClass('red');
                    $('#assemblyStartRemainingWeek i').removeClass().addClass('exclamation triangle icon'); // Danger icon
                } else {
                    $('#assemblyStartRemainingWeek').addClass('green');
                    $('#assemblyStartRemainingWeek i').removeClass().addClass('clock icon'); // Yellow için saat iconu
                }

                $('#assemblyStartRemainingWeek').show();
            } else {
                $('#assemblyStartRemainingWeek').hide();
            }

            if (!data.isNachbauExists) {
                $('#nachbauFileExistsOrNot .ui.segment').css('background-color', '#f3f3f0');
                $('#nachbauFileMsg').html(`<i class="exclamation circle icon red big"></i> 
                                           <h2 style="margin:0;">Nachbau TXT could not be found for this project.</h2>`);
            }
            else if(!data.isProjectPlanned) {
                $('#nachbauFileExistsOrNot .ui.segment').css('background-color', '#f3f3f0');
                $('#nachbauFileMsg').html(`<i class="exclamation circle icon red big"></i> 
                                           <h3 style="margin:0;line-height: 1.8rem;">Project has MTool record but has not planned yet. <br> Please get contact with planning department.</h3>`);
            } else {
                $('#nachbauFileExistsOrNot .ui.segment').css('background-color', 'mintcream');
                $('#nachbauFileMsg').html(`<i class="check circle icon green big"></i> <h2 style="margin: 0.7rem;">Nachbau Data is available for this project.</h2>
                                           <div class="ui positive button" style="margin-top:0.8rem;" onclick="showNachbauData('${projectNo}')">View Project Nachbau Data</div>`);
            }
            $('#projectPageContainer').transition('pulse');
        } else {
            $('#projectPageErrorMsg').text('An unexpected error occurred. Please get contact with DGT Team.');
            $('#projectPageErrorDiv').transition('pulse');
        }
    } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred";
        $('#projectPageErrorMsg').text(errorMessage);
        $('#projectPageErrorDiv').transition('pulse');
        return [];
    } finally {
        $('#projectPage .loader').hide();
    }
}

