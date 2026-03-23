$(document).ready(async function() {
    const counts = await fetchCountOfNcAndTkNotesOfTkForm();
    document.getElementById('ncListCount').innerText = counts.nc_count;
    document.getElementById('tkNotesCount').innerText = counts.tk_notes_count;

    if (parseInt(counts.nc_count) > 0)
        document.getElementById('ncListCount').classList.add('brown');
    if (parseInt(counts.tk_notes_count) > 0)
        document.getElementById('tkNotesCount').classList.add('brown');

    await prepareOrderListTable();
});

async function fetchOrderListByTkFormId() {
    try {
        const urlParams = new URLSearchParams(window.location.search);
        const dtoNumber = urlParams.get('dto-number');

        const url = `/dpm/dtoconfigurator/api/controllers/TkFormController.php?action=getOrdersOfTkForm&dtoNumber=${dtoNumber}`;
        const response = await axios.get(url, { dtoNumber: dtoNumber }, { headers: { "Content-Type": "multipart/form-data" } });

        return response.data;
    } catch (error) {
        fireToastr('error', 'Error fetching Order List:', error);
        return [];
    }
}

async function prepareOrderListTable() {
    const orders = await fetchOrderListByTkFormId();
    const tableBody = $('#dtoOrderList tbody');
    tableBody.empty();

    if (orders.length !== 0) {
        orders.forEach(order => {
            const newRow = `
            <tr>
                <td class="two wide column center aligned">
                    <a href="/dpm/dtoconfigurator/core/projects/detail/info.php?project-no=${order.projectno}" target="_blank" 
                       data-tooltip="Navigate to Project Work page"
                       data-position="top center"
                       data-variation="inverted">
                        ${order.projectno}
                    </a>
                </td>
                <td>${order.projectname}</td>
            </tr>
        `;

            tableBody.append(newRow);
        });
    } else {
        $('#orderListContainer .segment').hide();
        $('#orderListCheckMsg').show();
    }

    $('#orderListPage #fetchOrderListLoader').hide();
    $('#orderListContainer').transition('zoom');
}

