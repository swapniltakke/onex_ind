$(document).ready(function() {
    initializeProjectNcListDataTable();
});

async function fetchNcListByProjectNumber() {
    try {
        const projectNo = getUrlParam('project-no');

        const url = `/dpm/dtoconfigurator/api/controllers/NCController.php?action=getNCsByProjectNo&projectNo=${projectNo}`;
        const response = await axios.get(url, { projectNo: projectNo }, { headers: { "Content-Type": "multipart/form-data" } });

        return response.data;
    } catch (error) {
        fireToastr('error', 'Error fetching NC List:', error);
        return [];
    }
}

async function initializeProjectNcListDataTable() {
    showLoader('#projectNcListContainer');
    hideElement('#projectNcListTableContainer');

    const data = await fetchNcListByProjectNumber();

    const isOpenNcExists = data.some(row => row.Status === '0'); //Checks of Status column of each row
    const numberOfNCs = data.length;

    const messageElement = document.getElementById('openNcCheckMsg');
    if (isOpenNcExists) {
        messageElement.innerHTML = '<p><i class="exclamation circle icon"></i>There is an open NC information related to this Project.</p>';
        messageElement.classList.add('negative')
        $('#projectNcListCount').css('background-color', '#FFF6F6').text(numberOfNCs).attr('data-tooltip', 'Open NC exists.').attr('data-position', 'top left');
    } else {
        messageElement.innerHTML = '<p><i class="info circle icon"></i>There is no open NC information related to this Project.</p>';
        messageElement.classList.add('positive');
        $('#projectNcListCount').css('background-color', 'seagreen').text(numberOfNCs).attr('data-tooltip', 'Open NC does not exists.').attr('data-position', 'top left');
    }

    if (data.length === 0)
        $('#projectNcListContainer').hide()
    else {
        const projectNcTable = $('#projectNcListTable').DataTable({
            data: data,
            pageLength: 25,
            autoWidth: false,
            fixedHeader:true,
            columnDefs: [{ width: '10%', targets: [0,1,2,3,7] }],
            columns: [
                { data: function(data) {
                        return `<a href="/dpm/dtoconfigurator/core/projects/detail/info.php?project-no=${data}" target="_blank">${data.Descriptor}</a>`
                    },
                    className: 'center aligned',
                },
                { data: 'DtoNo', className: 'center aligned' },
                { data: 'PanelNo', className: 'center aligned' },
                { data: 'NcNo', className: 'center aligned' },
                { data: function(data) {
                        return `<a href="/nc/ncdisplay.php?id=${data.NcNo}" target="_blank">${data.NcNo}</a>`
                    },
                    className: 'center aligned',
                },
                {
                    data: function (data) {
                        if (data.Status === '1') {
                            return `<span data-tooltip="NC is closed."><i class="check circle icon green big"></span></i>`;
                        } else {
                            return `<span data-tooltip="There is an open NC exists."><i class="window close icon red big"></i></span>`;
                        }
                    },
                    className: 'center aligned'
                },
                { data: 'NcDetails', className: 'center aligned' },
                { data: function(data) {
                        return moment(data.NcDate).format('DD.MM.YYYY HH:mm');
                    },
                    className: 'center aligned',
                },
                { data: 'OriginCode', className: 'center aligned'},
            ],
            destroy: true
        });


        //Search customization
        const $searchInput = $('.dt-search input[type="search"]');
        $searchInput.attr('placeholder', 'Search').wrap('<div class="ui icon input"></div>').after('<i class="search icon"></i>');

        $('#projectNcListTableContainer').transition('zoom', function() {
            requestAnimationFrame(() => {
                projectNcTable.fixedHeader.adjust(); // İlk açılışta fix header olmama sorununun çözümü
            });
        });
        projectNcTable.draw();
    }
    hideLoader('#projectNcListContainer');
}