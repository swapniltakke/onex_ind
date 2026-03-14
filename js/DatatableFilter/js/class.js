
class Filter {
  constructor(name, table, colIndex) {
    this.name = name;

    this.table = "#" + table;

    this.colIndex = parseInt(colIndex);

    this.data = [];

    this.filteredData = [];

    this.createHTML();
  }

  createHTML() {
        let string = this.name.split(/(?=[A-Z])/).join(" ");
	let text = string.charAt(0).toUpperCase() + string.slice(1);

    $(`${this.table}_filterFields`).append(`
			
			<div class="two wide column" style="padding: 2px; margin:0px;">
			<select multiple="" id="${this.name}Filter" data-filter="${this.name}" class="ui search dropdown">
			<option value="">${text}</option>
			</select>
			</div>
			
			
		`);
  }

  init() {
    let self = this;
    $(`.ui.dropdown[data-filter='${this.name}']`).dropdown({
      clearable: true,
      fullTextSearch: true,
      onAdd: function (value, text, choice) {
        self.filteredData.push(value);
        //console.log(value,'-',self.filteredData,self.colIndex);
        //$(self.table).DataTable().column(self.colIndex).search(self.filteredData.join('|').replace(/[-[\]{}()*+?.,\\^$#\s]/g, '\\$&'), true, false).draw();
      },
      onRemove: function (value) {
        self.filteredData.splice(self.filteredData.indexOf(value), 1);

        //$(self.table).DataTable().column(self.colIndex).search(self.filteredData.join('|'), true, false).draw();
      },
    }).css("width","100%");

  }
}

class FilterFields{
	constructor(tableId){
			$(`#${tableId}`).parent().prepend($(`
			<div id='${tableId}_formFields' style='clear: left;padding-left: 30px;display: none;' class="ui form">
			<div id='${tableId}_filterFields' class="ui grid" style="padding: 5px;">
				<div class="two wide column" style="padding: 2px; margin:0px;">
					<button id='${tableId}_filterClearButton' style="width:100%;" class="ui icon red button">

						<i class="icon trash"></i>
						Clear
					</button>
				</div>
				<div class="two wide column" style="padding: 2px; margin:0px;">
					<button id='${tableId}_filterApplyButton' style="width:100%;" class="ui icon teal button">
						<i class="icon filter"></i>
						Apply Filters
					</button>
				</div>
			</div>
		</div>
		`));
		

		
		$(`#${tableId}_formFields`).css('display', 'block');
	}
}

class FilterInitial {
  constructor(tableId,filterArray) {
	$(`#${tableId}_wrapper > .dataTables_length`).prepend($(`
		<button id="${tableId}_filterButton" class="ui button">
			<i class="filter icon"></i>
			<span data-translate="filter">Filter</span>
		</button>
	`));
	$(`#${tableId}_filter`).after($(`#${tableId}_formFields`));
	$(document).on('click', `#${tableId}_filterButton`, function() {
		$(`#${tableId}_formFields`).transition('fade right');
		$('.ui.dropdown.coatingType')
			.dropdown({
				clearable: true,
				onChange: function(value, text) {
					if (value) {
						$('#table').DataTable().column(9 - colFilterOffset).search(text).draw();
					} else {
						$('#table').DataTable().column(9 - colFilterOffset).search('').draw();
					}
	
				}
			});
	
	});

	$(document).on('click' , `#${tableId}_filterClearButton` , function(){
		$("#"+tableId).transition("zoom");
		setTimeout(function () {
		  filterArray.forEach((filter) => {
			let valuesArray = $(`#${filter.name}Filter`).dropdown("get value");
			
			
			let dataArray = valuesArray;
			if (dataArray != null) {
			  $("#"+tableId)
				.DataTable()

				.column(filter.colIndex)
				.search("", true, false)
				.draw();
			  $(`#${filter.name}Filter`).dropdown("remove selected");
			}
		  });
		  $("#"+tableId).transition("zoom");
		}, 10);
	});
	$(document).on('click' , `#${tableId}_filterApplyButton` , function(){
		$("#"+tableId).transition("zoom");
		setTimeout(function () {
		  var values = [];
		  filterArray.forEach((filter) => {
			let valuesArray = $(`#${filter.name}Filter`).dropdown("get value");
			console.log(valuesArray);
			
			let dataArray= valuesArray;
			if (dataArray != null) {
			  let obj = JSON.parse(
				`{"name":"${filter.name}" , "values":"${dataArray}" , "colIndex" : "${filter.colIndex}"}`
			  );
			  values.push(obj);
			}
		  });
		  values.forEach((value) => {
			let searchValues = value.values.replaceAll(",", "|");
			
			$("#"+tableId)
			  .DataTable()
			  .column(value.colIndex)
			  .search(searchValues, true, false)
			  .draw();
		  });
		  $("#"+tableId).transition("zoom");
		}, 10);
	});

  }



}
