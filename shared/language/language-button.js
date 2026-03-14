const customTranslator = function () {
    let lang = localStorage.getItem('language');

    if ($("#mainChartReasonDiv")) {
        const new_title = `${changeLang[lang]["rework-reasons"]} - ${changeLang[lang]["main-category"]}`
        $("#mainChartReasonDiv").html(new_title);
    }
    if ($("#subChartReasonDiv")) {
        const new_title = `${changeLang[lang]["rework-reasons"]} - ${changeLang[lang]["sub-category"]}`
        $("#subChartReasonDiv").html(new_title);
    }
    if ($("#mai-qty-1")) {
        const new_title = `${changeLang[lang]["qty"]}`
        $("#mai-qty-1").html(new_title);
    }
    if ($("#mai-mn-1")) {
        const new_title = `${changeLang[lang]["mn"]}`
        $("#mai-mn-1").html(new_title);
    }
}

function translator() {
    if (!localStorage.getItem('language') || localStorage.getItem('language') == undefined) {
        localStorage.setItem('language', 'tr');
    }


    let lang = localStorage.getItem('language');


    $('#langChoiceIcons a img').removeClass('active');
    $('#langChoiceIcons a#' + lang).children('img').addClass('active');
    $('[data-translate]').each(function (idx, element) {
        $(this).text(changeLang[lang][$(this).data('translate')]);
    });
    $('[data-input-translate]').each(function (idx, element) {
        $(this)[0].placeholder = changeLang[lang][$(this).attr("data-input-translate")];
    });
    $('[data-placeholder-translate]').each(function (idx, element) {
        $(this)[0].placeholder = changeLang[lang][$(this).attr("data-placeholder-translate")];
    });
    customTranslator();
}


var langPref;
$(".lang").click(function () {
    langPref = $(this).attr('id');
    localStorage.setItem("language", langPref);
    translator();
});
