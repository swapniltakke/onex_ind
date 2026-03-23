$(document).ready(async function() {
    const counts = await fetchCountOfNcAndTkNotesOfTkForm();
    document.getElementById('ncListCount').innerText = counts.nc_count;
    document.getElementById('tkNotesCount').innerText = counts.tk_notes_count;

    if (parseInt(counts.nc_count) > 0)
        document.getElementById('ncListCount').classList.add('brown');
    if (parseInt(counts.tk_notes_count) > 0)
        document.getElementById('tkNotesCount').classList.add('brown');

    await initializeNcListDataTable();
});

async function fetchNcListByTkFormId() {
    try {
        const urlParams = new URLSearchParams(window.location.search);
        const id = urlParams.get('id');

        const url = `/dpm/dtoconfigurator/api/controllers/NCController.php?action=getNCsByTkFormId&id=${id}`;
        const response = await axios.get(url, { id: id }, { headers: { "Content-Type": "multipart/form-data" } });

        return response.data;
    } catch (error) {
        fireToastr('error', 'Error fetching NC List:', error);
        return [];
    }
}

async function initializeNcListDataTable() {
    const data = await fetchNcListByTkFormId();
    const isOpenNcExists = data.some(row => row.Status === '0'); //Checks of Status column of each row

    const messageElement = document.getElementById('openNcCheckMsg');
    if (isOpenNcExists) {
        messageElement.innerHTML = '<p><i class="info circle icon"></i>There is an open NC information related to this DTO Number.</p>';
        messageElement.classList.add('red');
    } else {
        messageElement.innerHTML = '<p><i class="info circle icon"></i>There is no open NC information related to this DTO Number.</p>';
        messageElement.classList.add('positive');
    }

    if (data.length === 0)
        $('#ncListTable').hide()
    else {
        $('#ncListTable').DataTable({
            data: data,
            pageLength: 25,
            autoWidth: false,
            order: [[0, 'desc']],
            fixedHeader:true,
            columnDefs: [
                { width: '10%', targets: [0,1,2] },
                { width: '5%', targets: [3] },
                { width: '10%', targets: [5] }
            ],
            columns: [
                { data: 'Descriptor', className: 'center aligned' },
                { data: 'PanelNo', className: 'center aligned'  },
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
                { data: 'NcDetails'},
                { data: function(data) {
                        return moment(data.NcDate).format('DD.MM.YYYY HH:mm');
                    },
                    className: 'center aligned',
                }
            ],
            destroy: true
        });
    }

    $('#ncListPage .loader').hide();
    $('#ncListContainer').transition('zoom')
}

