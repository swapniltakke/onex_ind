// async function fetchOrderSummaryData() {
//     try {
//         const projectNo = getUrlParam('project-no');
//         const nachbauNo = getUrlParam('nachbau-no');
//         const url = '/dpm/dtoconfigurator/api/controllers/NachbauController.php';
//         const response = await axios.get(url, { params: { action: 'getOrderSummary', projectNo: projectNo, nachbauNo: nachbauNo }});
//         return response.data;
//     } catch (error) {
//         fireToastr('error', 'Error fetching Order Summary Data', error);
//         return [];
//     }
// }
//
// let affectedDtoDescs = {};
// let grouppedDtoNumbers = new Set();
// async function getOrderSummary() {
//     grouppedDtoNumbers.clear();
//
//     //Order summary dropdown filters
//     let typical_noFilter = new Filter('typical_no', 'orderSummaryTable', 6);
//     let ortz_kzFilter = new Filter('ortz_kz', 'orderSummaryTable', 7);
//     let dto_numberFilter = new Filter('dto_number', 'orderSummaryTable', 9);
//     // let material_descriptionFilter = new Filter('material_description', 'orderSummaryTable', 5);  // New Filter
//
//     document.getElementById('clearFiltersButton').addEventListener('click', () => {
//         const filters = [dto_numberFilter, typical_noFilter, ortz_kzFilter];
//         clearAllFilters(filters, '#orderSummaryTable');
//     });
//
//     let addedDtosIntoFilter = [];
//     await setDescriptionsOfAffectedDtoNumbers();
//     const orderSummaryData = await fetchOrderSummaryData();
//
//     if ($.fn.DataTable.isDataTable('#orderSummaryTable')) {
//         $('#orderSummaryTable').DataTable().clear().destroy();
//         $('#orderSummaryTable tbody').empty(); // Clear only the table body, not the headers
//     }
//
//     if (orderSummaryData.length === 0)
//         $('#orderSummaryV2Container').hide();
//     else {
//        $('#orderSummaryTable').DataTable({
//             data: orderSummaryData,
//             autoWidth: false,
//             ordering: false,
//             paging:false,
//             createdRow: function (row, data) {
//                 if (data.material_deleted_number.startsWith('003003') || data.material_deleted_number.startsWith('003013')) {
//                     $(row).css('font-weight', 'bold');
//                     $(row).css('text-decoration', 'underline');
//                     $(row).css('color', 'mediumblue');
//                 }
//                 if (data.operation === 'add') {
//                     $(row).css("background", "#377B33");
//                     $(row).css("color", "white");
//                 }
//                 if (data.operation === "delete") {
//                     $(row).css("background", "darkred");
//                     $(row).css("color", "white");
//                 }
//                 if (data.operation === "replace") {
//                     $(row).css("background", "darkcyan");
//                     $(row).css("color", "white");
//                 }
//                 if (data.parentSpareRow) {
//                     $(row).css('background-color', 'darkgreen');
//                 }
//             },
//             initComplete: function () {
//                 let filterArray = [typical_noFilter, ortz_kzFilter, dto_numberFilter];
//                 filterArray.forEach(filter => {
//                     // Initialize only if not already initialized
//                     if (!$(`.ui.dropdown[data-filter='${filter.dtTableName + filter.name}']`).hasClass('initialized')) {
//                         filter.init();
//                         $(`.ui.dropdown[data-filter='${filter.dtTableName + filter.name}']`).addClass('initialized');
//                     }
//                 });
//                 // If Spare DTO exists in project, display the msg
//                 if (SpareWdaDtosOfProject.length > 0) {
//                     const targetElement = $('#orderSummaryTable_wrapper .row:first .eight.wide.column:first'); // Entries dropdownunun oluşturduğu boşluk
//                     const uniqueSpareTypicalNos = [...new Set(SpareWdaDtosOfProject.map(item => item.typical_no))].join(" | ");
//                     const spareDtoExistInfoMsg =
//                         `<div id="spareDtoExistMsg" class="ui compact message warning small">
//                              <p><i class="info circle icon"></i>Nachbau contains Spare Withdrawable DTO. Check <b>PA Withdrawable Unit KMAT</b> of typicals: <span id="spareDtoExistMsgTypicals" style="font-weight:700;color: #2185d0;">${uniqueSpareTypicalNos}</span></p>
//                          </div>`
//
//                     targetElement.html(spareDtoExistInfoMsg);
//                     $('#spareDtoExistMsg').css('width', '130%');
//
//                 }
//             },
//             columnDefs: [
//                 { width: '3%', targets: [0], className: 'center aligned' },
//                 { width: '7%', targets: [1,2], className: 'center aligned' },
//                 { width: '4%', targets: [3,4], className: 'center aligned' },
//                 { width: '25%', targets: [5], className: 'center aligned' },
//                 { width: '9%', targets: [6], className: 'center aligned' },
//                 { width: '10%', targets: [7], className: 'center aligned' },
//                 { width: '8%', targets: [8], className: 'center aligned' },
//                 { width: '18%', targets: [9], className: 'center aligned' },
//             ],
//             columns: [
//                 {
//                     data: "position",
//                     render: function(data) {
//                         return data ? data : '';
//                     }
//                 },
//                 {
//                     data: function (data) {
//                         if (data.operation === 'delete')
//                             return `<span>SİL</span>`;
//
//                         if (data.material_added_starts_by === 'A7ETKBL')
//                             return `${data.material_added_starts_by}${data.material_added_number}`
//
//                         return data.material_added_number;
//                     }
//                 },
//                 {
//                     data: function (data) {
//                         if (data.material_deleted_starts_by === 'A7ETKBL')
//                             return `${data.material_deleted_starts_by}${data.material_deleted_number}`
//
//                         return data.material_deleted_number;
//                     }
//                 },
//                 {
//                     data: 'quantity',
//                     render: function (data, type, row) {
//                         if (!row.quantity) return '';
//                         return row.quantity.replace(/\.000$/, '');
//                     }
//                 },
//                 {
//                     data: "unit",
//                     render: function(data) {
//                         return data ? data : '';
//                     }
//                 },
//                 {
//                     data: "kmat_name",
//                     render: (data, type, row) =>
//                         (row.isCable || row.isDescription)
//                             ? data.split("V:").filter(s => s.trim()).join("<br>")
//                             : data
//                 },
//                 {
//                     data: 'typical_no',
//                     render: function (data, type, row) {
//                         initDtFilter(row.typical_no, typical_noFilter, "typical_noFilter", "orderSummaryTable");
//                         return row.typical_no;
//                     }
//                 },
//                 {
//                     data: 'ortz_kz',
//                     render: function (data, type, row) {
//                         initDtFilter(row.ortz_kz, ortz_kzFilter, "ortz_kzFilter", "orderSummaryTable");
//
//                         if (row['release_type'] === 'Extension') {
//                             if (row["isAccessory"] === '1')
//                                 return `<span>ACCESSORY</span>`
//                         }
//                         return row['ortz_kz'] === '' ? row['panel_no'] : `${row['ortz_kz']}/${row['panel_no']}`;
//                     }
//                 },
//                 {
//                     render: function (data, type, row) {
//                         if (type === 'display') {
//                             if (row['isAccessory'] === true) {
//                                 if (row.release_type === 'Spare') {
//
//                                     if (row.spare_dto_type === '2')
//                                         return `<span>Accessory (S)</span>`;
//
//                                     let dtoTypicalKey = `${row.dto_number}-${row.spare_typical_number}`;
//
//                                     if (grouppedDtoNumbers.has(dtoTypicalKey)) {
//                                         return ``;
//                                     } else {
//                                         grouppedDtoNumbers.add(dtoTypicalKey);
//                                         row.parentSpareRow = true;
//
//                                         return `Accessory (S) <br> <div class="ui circular teal icon button mini"
//                                                                  data-tooltip="View details of Spare DTO work (${row.spare_typical_number})" data-position="top center"
//                                                                  onclick="getSpareAccessoryDataOfProject('${row.spare_project_id}')">
//                                                                <i class="eye large icon"></i>
//                                                             </div>`;
//                                     }
//                                 }
//                                 return `<span>Accessory</span>`;
//                             }
//                         }
//
//                         // Spare WDA DTOsunun bulunduğu ana KMAT ise.
//                         if (row['isSpareDtoParentKmat']) {
//                             const kmatOfWda = row['material_deleted_number'].replace(/^00/, '');
//                             const typicalOfWda = row['typical_no'];
//
//                             // Siparişin Spare Dtoları hangi tipiklerde geçiyorsa o tipiklerde modal açma butonu olsun
//                             const matchingItem = SpareWdaDtosOfProject.find(item => item.typical_no === typicalOfWda);
//
//                             if (matchingItem) {
//                                 return `<button class="ui icon blue button small open-spare-wda-btn" style="padding-right: 0.4rem;padding-left: 0.4rem;"
//                                                 data-tooltip="Spare DTO Number : ${matchingItem.dto_number} in Typical : ${typicalOfWda}" data-position="top center" data-inverted=""
//                                                 onclick="checkSpareWdaDtoCountsInSameTypical('${kmatOfWda}', '${typicalOfWda}', '${matchingItem.dto_number}', '${shortDescription(escapeString(matchingItem.description), 100)}')">
//                                             <i class="icon medium puzzle piece" style="margin-right: 0.3rem !important;"></i><span style="font-size:0.8rem;">Spare Dto</span>
//                                         </button>`;
//                             }
//                         }
//
//
//                         // Check if this is a special DTO kmat
//                         if (row.kmat_name === 'PA Withdrawable Unit') {
//                             const matchingItem = SpecialDtosOfProject.find(item => item.typical_no === row.typical_no);
//
//                             if (matchingItem) {
//                                 return `<button class="ui icon violet button small special-dto-btn" style="padding-right: 0.4rem;padding-left: 0.4rem;"
//                                         data-tooltip="${matchingItem.dto_number} ${row.typical_no}" data-position="top center"
//                                         onclick="openSpecialDtoWorkModal('${matchingItem.dto_number}', '${matchingItem.description}', '${matchingItem.typical_no}', '${row.material_deleted_number}')">
//                                     <i class="settings icon" style="margin-right: 0.3rem !important;"></i><span style="font-size:0.8rem;">Special DTO</span>
//                                 </button>`;
//                             }
//                         }
//
//                         if (row['release_type'] === 'Extension' && row['isAccessory'] === '1')
//                             return `<span>Accessory (Ext.)</span>`;
//
//                         return ``;
//                     },
//                 },
//                 {
//                     data: "dto_number",
//                     render: function (data, type, row) {
//
//                         if (type === 'filter') {
//                             if (row['release_type'] === 'Nachbau Error') {
//                                 return 'NACHBAU ERROR';
//                             }
//
//                             let dtoList = [];
//
//                             if (row['dto_number']) {
//                                 dtoList.push(row['dto_number']);
//                             }
//
//                             if (row['affected_dto_numbers']) {
//                                 const affectedList = row['affected_dto_numbers'].split('|').map(s => s.trim());
//                                 dtoList = dtoList.concat(affectedList);
//                             }
//
//                             return dtoList.join(' ');
//                         }
//                         let noteFilterData = '';
//                         if (row['release_type'] === 'Nachbau Error') {
//                             noteFilterData = 'NACHBAU ERROR';
//                             initDtFilter(noteFilterData, dto_numberFilter, "dto_numberFilter", "orderSummaryTable");
//                         }
//                         else if(row['dto_number'] && row['affected_dto_numbers']) {
//                             const affectedDtoNumbersArray = row['affected_dto_numbers'].split('|');
//
//                             affectedDtoNumbersArray.forEach((dto) => {
//                                 if (!addedDtosIntoFilter.includes(dto)) {
//                                     let affectedDtoDesc = affectedDtoDescs[dto];
//                                     noteFilterData = `<b>${dto}</b> - ${affectedDtoDesc}`;
//                                     initDtFilter(noteFilterData, dto_numberFilter, "dto_numberFilter", "orderSummaryTable");
//                                     addedDtosIntoFilter.push(dto);
//                                 }
//                             });
//                         } else {
//                             if (!addedDtosIntoFilter.includes(row['dto_number'])) {
//                                 noteFilterData = `<b>${row['dto_number']}</b> - ${shortDescription(row['dto_description'], 75)}`;
//                                 initDtFilter(noteFilterData, dto_numberFilter, "dto_numberFilter", "orderSummaryTable");
//                                 addedDtosIntoFilter.push(row['dto_number']);
//                             }
//                         }
//
//                         if (type === 'display') {
//                             if (row['release_type'] === 'Nachbau Error') {
//                                 return `<span><b>${row['note']}</b></span><br>
//                                     <div id="deleteNachbauErrorChange" class="ui red circular mini icon button"
//                                          onclick="deleteNachbauErrorChange('${row['nachbau_error_id']}')"
//                                          data-position="top center" data-tooltip="Click here to delete nachbau error change.">
//                                         <i class="trash large icon" style="margin-right:0.3rem;"></i>
//                                     </div>`;
//                             }
//                             else if (row['release_type'] === 'Extension') {
//                                 let output = `<span><b>${row['dto_number']}</b></span><br>
//                                                      <span style="font-size: 13px!important;">${shortDescription(row['dto_description'], 100)}</span>`;
//
//                                 if (row['isAccessory'] === '1') {
//                                     output += `<br><br><b><span style="font-size: 15px!important;">${row['ortz_kz']}</span></b><br>
//                                <b><span style="font-size: 14px!important;">${row['note']}</span></b>`;
//                                 } else {
//                                     output += `<br><br><b><span style="font-size: 15px!important;">${row['note']}</span></b>`;
//                                 }
//
//                                 return output;
//                             }
//                             else if (row['release_type'] === 'Interchange') {
//                                 return `<span><b>${row['dto_number']}</b></span><br>
//                                         <span style="font-size: 13px!important;">${shortDescription(row['dto_description'], 100)}</span><br><br>
//                                         <strong>TYPICAL BASED CHANGE</strong>`;
//                             }
//                             else if (row['release_type'] === 'Spare') {
//                                 return `<span><b>${row['dto_number']}</b></span><br>
//                                         <span style="font-size: 13px!important;">${shortDescription(row['dto_description'], 100)}</span><br><br>
//                                         <strong><span>${row['spare_typical_number']} TYPICAL</span></strong>`;
//                             }
//                             else if (row['release_type'] === 'MinusPrice') {
//                                 return `<span><b>${row['dto_number']}</b></span><br>
//                                         <span style="font-size: 13px!important;">${shortDescription(row['dto_description'], 90)}</span><br>
//                                         <strong><span>ÜRETİLMEYECEK</span></strong>`;
//                             }
//                             else if (row['dto_number'] && row['affected_dto_numbers']) {
//                                 let output = '';
//                                 const affectedDtoNumbersArray = row['affected_dto_numbers'].split('|');
//
//                                 affectedDtoNumbersArray.forEach((dto) => {
//                                     let affectedDtoDesc = affectedDtoDescs[dto];
//                                     output += `<span><b>${dto}</b></span><br>
//                                               <span style="font-size: 13px!important;">${affectedDtoDesc}</span><br><br>`;
//                                 });
//
//                                 output += `<strong><span>${row['tk_type'] === 'Typical' ? 'TYPICAL BASED CHANGE' : 'PANEL BASED CHANGE'}</span></strong>`;
//
//                                 return output;
//                             }
//                             else if (row['dto_number']) {
//                                 return `<span><b>${row['dto_number']}</b></span><br>
//                                         <span style="font-size: 13px!important;">${shortDescription(row['dto_description'], 100)}</span><br>
//                                         <strong><span>${row['tk_type'] === 'Typical' ? 'TYPICAL BASED CHANGE' : 'PANEL BASED CHANGE'}</span></strong>`;
//                             }
//                         }
//
//                         return '';
//                     }
//                 },
//                 {
//                     data: function () { return ''; }
//                 }
//             ],
//             destroy: true
//         });
//
//         //Search customization
//         const $searchInput = $('.dt-search input[type="search"]');
//         $searchInput.attr('placeholder', 'Search').wrap('<div class="ui icon input"></div>').after('<i class="search icon"></i>');
//
//         hideLoader('#orderSummaryV2');
//         showElement('#orderSummaryV2Container');
//
//         await checkIfThereIsPossibleMissingMaterialLists();
//     }
// }
//
// async function checkIfThereIsPossibleMissingMaterialLists() {
//     try {
//         const response = await axios.get('/dpm/dtoconfigurator/api/controllers/NachbauController.php',
//                         { params: { action: 'checkIfThereIsPossibleMissingMaterialLists', projectNo: getUrlParam('project-no'), nachbauNo: getUrlParam('nachbau-no') }});
//
//         const missingMaterials = response.data;
//         if (missingMaterials.length > 0) {
//
//             let rowsHtml = '';
//             missingMaterials.forEach(material => {
//                 rowsHtml += `
//                     <tr>
//                         <td>${material.dto_number}</td>
//                         <td>${material.material_added_number || ''}</td>
//                         <td>${material.material_deleted_number || ''}</td>
//                         <td>${material.release_item || ''}</td>
//                         <td>${material.work_content || ''}</td>
//                     </tr>
//                 `;
//             });
//
//             $('#missingMaterialListTable tbody').html(rowsHtml);
//             $('#possibleMissingMaterialLists').css('display', '');
//
//         } else {
//             $('#possibleMissingMaterialLists').css('display', 'none');
//         }
//
//         return response.data;
//     } catch (error) {
//         fireToastr('error', 'Error fetching missing materials:', error);
//         return [];
//     }
// }
//
//
// function initDtFilter(data, ColumnFilter, ColumnFilterStr, dtName) {
//     if(data !== undefined && data !== null){
//
//         data = data.trim();
//
//         if (!ColumnFilter.data.includes(data) &&
//             !["", null, false, true, "false", "true", "00.00.0000",
//                 "00-00-0000", "0000-00-00", "0000.00.00", "Invalid date"].includes(data)) {
//
//             ColumnFilter.data.push(data);
//
//             // Sort the array
//             ColumnFilter.data.sort((a, b) => a.localeCompare(b, undefined, { numeric: true }));
//
//             // Clear the existing dropdown options
//             $(`#${dtName}${ColumnFilterStr}DropData`).empty();
//
//             // Rebuild dropdown with sorted options
//             ColumnFilter.data.forEach(item => {
//                 if (item === '<b></b> -' || item === `<b>undefined</b> -`)
//                     return;
//
//                 $(`#${dtName}${ColumnFilterStr}DropData`).append(`
//                     <div class="item" data-value="${item}" style="line-height:1.5rem;">${item}</div>
//                 `);
//             });
//         }
//     }
// }
//
// async function downloadBomChangeExcel() {
//     const orderSummaryData =  await fetchOrderSummaryData();
//
//     const form = document.createElement('form');
//     form.method = 'POST';
//     form.action = '/dpm/dtoconfigurator/api/controllers/ExcelFunctions.php';
//     form.target = '_blank';
//
//     const projectNoInput = document.createElement('input');
//     projectNoInput.type = 'hidden';
//     projectNoInput.name = 'projectNo';
//     projectNoInput.value = getUrlParam('project-no');
//
//     const nachbauNoInput = document.createElement('input');
//     nachbauNoInput.type = 'hidden';
//     nachbauNoInput.name = 'nachbauNo';
//     nachbauNoInput.value = getUrlParam('nachbau-no');
//
//     const assemblyStartInput = document.createElement('input');
//     assemblyStartInput.type = 'hidden';
//     assemblyStartInput.name = 'assemblyStart';
//     assemblyStartInput.value = assemblyStartGlobal;
//
//     const orderSummaryDataInput = document.createElement('input');
//     orderSummaryDataInput.type = 'hidden';
//     orderSummaryDataInput.name = 'orderSummaryData';
//     orderSummaryDataInput.value = JSON.stringify(orderSummaryData);
//
//     form.appendChild(projectNoInput);
//     form.appendChild(nachbauNoInput);
//     form.appendChild(assemblyStartInput);
//     form.appendChild(orderSummaryDataInput);
//
//     document.body.appendChild(form);
//     form.submit();
// }
//
// // document.getElementById('downloadBomExcel').addEventListener('click', async () => {
// //     $('#orderSummaryActionButtons #downloadBomExcel').addClass('loading disabled');
// //     await downloadBomChangeExcel();
// //     $('#orderSummaryActionButtons #downloadBomExcel').removeClass('loading disabled');
// // });
// //
// // document.getElementById('generateAKDImportXml').addEventListener('click', async () => {
// //     $('#orderSummaryActionButtons #generateAKDImportXml').addClass('loading disabled');
// //     await generateAKDImportXml();
// //     $('#orderSummaryActionButtons #generateAKDImportXml').removeClass('loading disabled');
// // });
//
// async function generateAKDImportXml() {
//
//     const form = document.createElement('form');
//     form.method = 'POST';
//     form.action = '/dpm/dtoconfigurator/api/controllers/NachbauController.php';
//     form.target = '_blank';
//
//     const actionInput = document.createElement('input');
//     actionInput.type = 'hidden';
//     actionInput.name = 'action';
//     actionInput.value = 'generateAKDImportXmlFile';
//
//     const projectNoInput = document.createElement('input');
//     projectNoInput.type = 'hidden';
//     projectNoInput.name = 'projectNo';
//     projectNoInput.value = getUrlParam('project-no');
//
//     const nachbauNoInput = document.createElement('input');
//     nachbauNoInput.type = 'hidden';
//     nachbauNoInput.name = 'nachbauNo';
//     nachbauNoInput.value = getUrlParam('nachbau-no');
//
//     form.appendChild(actionInput);
//     form.appendChild(projectNoInput);
//     form.appendChild(nachbauNoInput);
//
//     document.body.appendChild(form);
//     form.submit();
// }
//
//
// function clearAllFilters(filters, tableId) {
//     $('#clearFiltersButton').addClass('loading disabled');
//
//     filters.forEach(filter => {
//         filter.filteredData = []; // Clear the filtered data array
//         $(`.ui.dropdown[data-filter='${filter.dtTableName + filter.name}']`).dropdown('clear'); // Clear the Semantic UI dropdown
//         $(`.ui.dropdown[data-filter='${filter.dtTableName + filter.name}']`).removeAttr('style'); // Remove custom styling
//         $(filter.table).DataTable().column(filter.colIndex).search(''); // Clear DataTable column search
//         $(filter.table).DataTable().column(filter.colIndex).nodes().to$().removeAttr('style'); // Reset column styles
//     });
//
//     $('#clearFiltersButton').removeClass('loading disabled');
//
//     $(tableId).DataTable().draw();
// }
//
//
// async function getSpareAccessoryDataOfProject(spareProjectId) {
//
//     try {
//         const response = await axios.get('/dpm/dtoconfigurator/api/controllers/ProjectController.php', {
//             params: { action: 'getSpareParametersOfProject', spareProjectId: spareProjectId}
//         });
//
//         if (response.status === 200) {
//             const spareDtoData = response.data;
//             await openSpareWorkModal(spareDtoData.spare_parent_kmat, spareDtoData.spare_typical_number, spareDtoData.dto_number, shortDescription(escapeString(spareDtoData.dto_description), 100))
//         }
//     } catch (error) {
//         const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
//         showErrorDialog(`Error: ${errorMessage}`);
//     }
// }
//
//
// async function deleteNachbauErrorChange(nachbauErrorId) {
//
//     showConfirmationDialog({
//         title: 'Delete Nachbau Error?',
//         htmlContent: 'Are you sure to remove nachbau error change?',
//         confirmButtonText: 'Yes, delete it!',
//         confirmButtonColor: "#d33",
//         onConfirm: async function () {
//             try {
//                 await axios.post('/dpm/dtoconfigurator/api/controllers/ProjectController.php?',
//                     {
//                         action: 'deleteNachbauErrorChange', nachbauErrorId: nachbauErrorId
//                     },
//                     { headers: { 'Content-Type': 'multipart/form-data' }}
//                 );
//
//                 showSuccessDialog('Nachbau error change deleted successfully.').then(() => {
//                     getOrderSummaryV2();
//                     resetNachbauErrorModal();
//                     $('#nachbauErrorModal').modal('hide');
//                 });
//             } catch (error) {
//                 const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
//                 showErrorDialog(`Error: ${errorMessage}`);
//             }
//         },
//     });
// }
//
//
// async function setDescriptionsOfAffectedDtoNumbers() {
//
//     const response = await axios.get('/dpm/dtoconfigurator/api/controllers/NachbauController.php', {
//         params: {
//             action: 'getDescriptionsOfAffectedDtoNumbers',
//             projectNo: getUrlParam('project-no'),
//             nachbauNo: getUrlParam('nachbau-no')
//         }
//     });
//
//     affectedDtoDescs = response.data;
// }
// // NOTE COLUMN RENDERING SECTION ENDS
//
// $(document).ready(async function() {
//     await scrollTopButton();
// });
// async function scrollTopButton() {
//     let $scrollButton = $('#scrollToTopButton');
//
//     $(window).on('scroll', function () {
//         if ($(this).scrollTop() > 100) {
//             $scrollButton.fadeIn(); // Show button when scrolling down
//         } else {
//             $scrollButton.fadeOut(); // Hide button when at the top
//         }
//     });
//
//     $scrollButton.on('click', function () {
//         $('html, body').animate({ scrollTop: 0 }, 300);
//     });
// }