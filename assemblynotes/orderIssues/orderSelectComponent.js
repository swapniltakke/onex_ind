$("#orderSelect").select2({
    ajax: {
        url: `/assemblynotes/api/orderSelectboxAPI.php`,
        dataType: 'json',
        type: 'GET',
        data: function (params) {
            return {
                projectNo: params.term, // search term
            };
        },
        processResults: function (data, params) {
            data = $.map(data.items, function (obj) {
                return {
                    id: obj.project_no,
                    text: obj.name
                };
            });
            return {
                results: data
            };
        }
    },
    language: "tr",
    minimumInputLength: 3,
    cache: true
});


