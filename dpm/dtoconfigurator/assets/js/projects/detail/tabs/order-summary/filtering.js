class Filter {
    constructor(name, table, colIndex) {
        this.name = name;
        this.table = "#" + table;
        this.dtTableName = table;
        this.colIndex = parseInt(colIndex);
        this.data = [];
        this.filteredData = [];
        this.selectAllTypicalsClicked = false;
        this.clearAllTypicalsClicked = false;
        this.createHTML();
    }

    createHTML() {
        let defaultText = '';

        switch (this.name) {
            case 'typical_no':
                defaultText = 'Typical No';
                break;
            case 'ortz_kz':
                defaultText = 'Panel No';
                break;
            case 'dto_number':
                defaultText = 'Note';
                break;
        }

        // Check if the dropdown already exists, if not, append it
        if ($(`#${this.dtTableName + this.name}Filter`).length === 0) {
            $(`#${this.dtTableName}Filtering`).append(`
            <div class="filteringColumn">
                <div class="ui multiple search selection dropdown dropStyles" id="${this.dtTableName + this.name}Filter" data-filter="${this.dtTableName + this.name}">
                    <input type="hidden">
                    <i class="dropdown icon"></i>
                    <div class="default text" data-translate="${this.name}">${defaultText}</div>  
                    <div class="menu" id="${this.dtTableName + this.name}FilterDropData"></div>
                </div>
                <div class="dropdown-actions" style="display:flex; justify-content:space-between;margin-top:0.2rem;">
                    <button class="ui mini button select-all very compact" data-filter="${this.dtTableName + this.name}">Select All</button>
                    <button class="ui mini button clear-all very compact" data-filter="${this.dtTableName + this.name}">Clear All</button>
                </div>
            </div>
        `);
        }
    }

    init() {
        let self = this;

        $(`.ui.dropdown[data-filter='${this.dtTableName + this.name}']`).dropdown({
            clearable: true,
            fullTextSearch: true,
            onAdd: async function (value) {
                if(self.name === 'dto_number')
                    value = value.split(' - ')[0].trim().replace(/^<b>(.*?)<\/b>$/, '$1'); // html tag sil ve - öncesi al. Sadece DTO.

                self.filteredData.push(value);
                $(self.table).DataTable().column(self.colIndex).nodes().to$().attr('style', 'background:beige;color:#000;');
                $(`.ui.dropdown[data-filter='${self.dtTableName + self.name}']`).attr('style', 'background:beige;color:#000;');
                $(self.table).DataTable().column(self.colIndex).search(
                    self.filteredData
                        .map(v => v.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, '\\$&'))
                        .join('|'),
                    true,
                    false
                ).draw();

                if (!self.selectAllTypicalsClicked && self.name === 'typical_no') {
                    const selectedTypicals = self.filteredData;
                    await updatePanelsDropdown('typical-filter-add', selectedTypicals);
                }
            },
            onRemove: async function (value) {
                if(self.name === 'dto_number')
                    value = value.split(' - ')[0].trim().replace(/^<b>(.*?)<\/b>$/, '$1'); // html tag sil ve - öncesi al. Sadece DTO.

                self.filteredData.splice(self.filteredData.indexOf(value), 1);
                if (!self.filteredData.length) {
                    $(`.ui.dropdown[data-filter='${self.dtTableName + self.name}']`).removeAttr('style');
                    $(self.table).DataTable().column(self.colIndex).nodes().to$().removeAttr('style');
                }
                $(self.table).DataTable().column(self.colIndex).search(
                    self.filteredData
                        .map(v => v.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, '\\$&'))
                        .join('|'),
                    true,
                    false
                ).draw();

                if (!self.clearAllTypicalsClicked && self.name === 'typical_no' ) {
                    const selectedTypicals = self.filteredData;
                    await updatePanelsDropdown('typical-filter-remove', selectedTypicals);
                }
                else if (self.name === 'ortz_kz') {
                    const selectedPanels  = self.filteredData;
                    await updatePanelsDropdown('panel-filter-remove', selectedPanels);
                }

            },
        });

        // Add event listeners for "Select All" and "Clear All" buttons
        $(`.dropdown-actions .select-all[data-filter='${this.dtTableName + this.name}']`).on('click', async () => {
            const button = $(event.currentTarget);
            button.addClass('loading disabled');

            self.selectAllTypicalsClicked = true;  // Set the flag to true

            const projectNo = new URLSearchParams(window.location.search).get('project-no');
            const nachbauNo = new URLSearchParams(window.location.search).get('nachbau-no');
            let values = [];

            if (this.name === 'typical_no') {
                $('#orderSummaryTableortz_kzFilter').dropdown('clear');

                const response = await axios.get('/dpm/dtoconfigurator/api/controllers/NachbauController.php', {
                    params: { action: 'getTypicalsOfProject', projectNo: projectNo, nachbauNo: nachbauNo },
                    headers: { "Content-Type": "multipart/form-data" },
                });

                values = response.data;

            } else if (this.name === 'ortz_kz'){
                const selectedTypicals = $('#orderSummaryTabletypical_noFilter').dropdown('get value');

                const response = await axios.get('/dpm/dtoconfigurator/api/controllers/NachbauController.php', {
                    params: { action: 'getPanelsOfSelectedTypicals', projectNo: projectNo, nachbauNo: nachbauNo, selectedTypicals: selectedTypicals },
                    headers: { "Content-Type": "multipart/form-data" },
                });

                values = response.data;
            }

            $(`.ui.dropdown[data-filter='${this.dtTableName + this.name}']`).dropdown('set selected', values);
            self.selectAllTypicalsClicked = false;  // Reset the flag after selection
            button.removeClass('loading disabled');
        });
        $(`.dropdown-actions .clear-all[data-filter='${this.dtTableName + this.name}']`).on('click', async () => {
            self.clearAllTypicalsClicked = true;
            $(`.ui.dropdown[data-filter='${this.dtTableName + this.name}']`).dropdown('clear');

            if (!self.selectAllTypicalsClicked && this.name === 'typical_no') {
                await updatePanelsDropdown([]); // if clear all typicals, send empty typicals array.
            }

            self.clearAllTypicalsClicked = false;  // Reset the flag after the operation
        });
    }
}


async function updatePanelsDropdown(operation, selectedData) {
    if (Array.isArray(selectedData))
        selectedData = selectedData.join(",");

    const panelDropdown = $('#orderSummaryTableortz_kzFilter');
    const panelDropdownMenu = $('#orderSummaryTableortz_kzFilterDropData');
    const projectNo = new URLSearchParams(window.location.search).get('project-no');
    const nachbauNo = new URLSearchParams(window.location.search).get('nachbau-no');

    try {
        if (operation === 'typical-filter-remove' || operation === 'typical-filter-add') {

            // Fetch panels for the selected typicals
            const response = await axios.get('/dpm/dtoconfigurator/api/controllers/NachbauController.php', {
                params: {
                    action: 'getPanelsOfSelectedTypicals',
                    projectNo: projectNo,
                    nachbauNo: nachbauNo,
                    selectedTypicals: selectedData
                },
                headers: { "Content-Type": "multipart/form-data" }
            });


            // Fetch currently selected panels
            let selectedPanels = panelDropdown.dropdown('get value'); // Returns an array of selected values
            if (typeof selectedPanels === 'string')
                selectedPanels = selectedPanels.split(','); // Convert to array (in semantic sometimes returns string)

            // Ensure unique panels and discard already selected panels
            const uniquePanels = [...new Set(response.data)];
            const filteredPanels = uniquePanels.filter(panel => !selectedPanels.includes(panel));

            panelDropdownMenu.empty();
            filteredPanels.forEach((panel) => {
                panelDropdownMenu.append(`<div class="item" data-value="${panel}">${panel}</div>`);
            });

            // Check and remove selected panels not present in uniquePanels
            selectedPanels.forEach((panel) => {
                if (!uniquePanels.includes(panel)) {
                    panelDropdown.dropdown('remove selected', panel); // Remove the panel from selected state
                }
            });

        }

        panelDropdown.dropdown('refresh');

    } catch (error) {
        console.error('Error fetching panels:', error);
    }
}
