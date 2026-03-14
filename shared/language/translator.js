class Translator{
    constructor(translate_key, selector = 'body', languages = [], default_language = null) {
        this.default_language = default_language ?? localStorage.getItem("language");
        this.selector = selector;
        this.translate_key = translate_key
        this.defined_languages = {
            tr:  "<svg version=\"1.1\" id=\"Layer_1\" xmlns=\"http://www.w3.org/2000/svg\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" x=\"0px\" y=\"0px\" viewBox=\"0 0 512 512\" style=\"enable-background:new 0 0 512 512;\" xml:space=\"preserve\"><title>Turkish</title><circle style=\"fill:#D80027;\" cx=\"256\" cy=\"256\" r=\"256\"/><g><polygon style=\"fill:#F0F0F0;\" points=\"245.518,209.186 266.523,238.131 300.54,227.101 279.502,256.021 300.504,284.965 266.499,273.893 245.462,302.813 245.484,267.052 211.478,255.98 245.496,244.95 \t\"/><path style=\"fill:#F0F0F0;\" d=\"M188.194,328.348c-39.956,0-72.348-32.392-72.348-72.348s32.392-72.348,72.348-72.348 c12.458,0,24.18,3.151,34.414,8.696c-16.055-15.702-38.012-25.392-62.24-25.392c-49.178,0-89.043,39.866-89.043,89.043 s39.866,89.043,89.043,89.043c24.23,0,46.186-9.691,62.24-25.392C212.374,325.197,200.652,328.348,188.194,328.348z\"/></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg>",
            en: "<svg version=\"1.1\" id=\"Layer_1\" xmlns=\"http://www.w3.org/2000/svg\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" x=\"0px\" y=\"0px\"\t viewBox=\"0 0 512 512\" style=\"enable-background:new 0 0 512 512;\" xml:space=\"preserve\"><title>English</title><circle style=\"fill:#F0F0F0;\" cx=\"256\" cy=\"256\" r=\"256\"/><g>\t<path style=\"fill:#0052B4;\" d=\"M52.92,100.142c-20.109,26.163-35.272,56.318-44.101,89.077h133.178L52.92,100.142z\"/>\t<path style=\"fill:#0052B4;\" d=\"M503.181,189.219c-8.829-32.758-23.993-62.913-44.101-89.076l-89.075,89.076H503.181z\"/>\t<path style=\"fill:#0052B4;\" d=\"M8.819,322.784c8.83,32.758,23.993,62.913,44.101,89.075l89.074-89.075L8.819,322.784L8.819,322.784\t\tz\"/>\t<path style=\"fill:#0052B4;\" d=\"M411.858,52.921c-26.163-20.109-56.317-35.272-89.076-44.102v133.177L411.858,52.921z\"/>\t<path style=\"fill:#0052B4;\" d=\"M100.142,459.079c26.163,20.109,56.318,35.272,89.076,44.102V370.005L100.142,459.079z\"/>\t<path style=\"fill:#0052B4;\" d=\"M189.217,8.819c-32.758,8.83-62.913,23.993-89.075,44.101l89.075,89.075V8.819z\"/>\t<path style=\"fill:#0052B4;\" d=\"M322.783,503.181c32.758-8.83,62.913-23.993,89.075-44.101l-89.075-89.075V503.181z\"/>\t<path style=\"fill:#0052B4;\" d=\"M370.005,322.784l89.075,89.076c20.108-26.162,35.272-56.318,44.101-89.076H370.005z\"/></g><g>\t<path style=\"fill:#D80027;\" d=\"M509.833,222.609h-220.44h-0.001V2.167C278.461,0.744,267.317,0,256,0\t\tc-11.319,0-22.461,0.744-33.391,2.167v220.44v0.001H2.167C0.744,233.539,0,244.683,0,256c0,11.319,0.744,22.461,2.167,33.391\t\th220.44h0.001v220.442C233.539,511.256,244.681,512,256,512c11.317,0,22.461-0.743,33.391-2.167v-220.44v-0.001h220.442\t\tC511.256,278.461,512,267.319,512,256C512,244.683,511.256,233.539,509.833,222.609z\"/>\t<path style=\"fill:#D80027;\" d=\"M322.783,322.784L322.783,322.784L437.019,437.02c5.254-5.252,10.266-10.743,15.048-16.435\t\tl-97.802-97.802h-31.482V322.784z\"/>\t<path style=\"fill:#D80027;\" d=\"M189.217,322.784h-0.002L74.98,437.019c5.252,5.254,10.743,10.266,16.435,15.048l97.802-97.804\t\tV322.784z\"/>\t<path style=\"fill:#D80027;\" d=\"M189.217,189.219v-0.002L74.981,74.98c-5.254,5.252-10.266,10.743-15.048,16.435l97.803,97.803\t\tH189.217z\"/>\t<path style=\"fill:#D80027;\" d=\"M322.783,189.219L322.783,189.219L437.02,74.981c-5.252-5.254-10.743-10.266-16.435-15.047\t\tl-97.802,97.803V189.219z\"/></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg>",
            in: "<svg xmlns=\"http://www.w3.org/2000/svg\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" id=\"flag-icon-css-in\" viewBox=\"0 0 512 512\"> <title>Indian</title>   <path fill=\"#f93\" d=\"M0 0h512v170.7H0z\"/>    <path fill=\"#fff\" d=\"M0 170.7h512v170.6H0z\"/>    <path fill=\"#128807\" d=\"M0 341.3h512V512H0z\" />    <g transform=\"translate(256 256) scale(3.41333)\">        <circle r=\"20\" fill=\"#008\"/>        <circle r=\"17.5\" fill=\"#fff\"/>        <circle r=\"3.5\" fill=\"#008\"/>        <g id=\"d\">            <g id=\"c\">                <g id=\"b\">                    <g id=\"a\" fill=\"#008\">                        <circle r=\".9\" transform=\"rotate(7.5 -8.8 133.5)\"/>                        <path d=\"M0 17.5L.6 7 0 2l-.6 5L0 17.5z\"/>                    </g>                    <use width=\"100%\" height=\"100%\" transform=\"rotate(15)\" xlink:href=\"#a\"/>                </g>                <use width=\"100%\" height=\"100%\" transform=\"rotate(30)\" xlink:href=\"#b\"/>            </g>            <use width=\"100%\" height=\"100%\" transform=\"rotate(60)\" xlink:href=\"#c\"/>        </g>        <use width=\"100%\" height=\"100%\" transform=\"rotate(120)\" xlink:href=\"#d\"/>        <use width=\"100%\" height=\"100%\" transform=\"rotate(-120)\" xlink:href=\"#d\"/>    </g></svg>",
            de: "<svg xmlns=\"http://www.w3.org/2000/svg\" id=\"flag-icon-css-de\" viewBox=\"0 0 512 512\">  <title>German</title>    <path fill=\"#ffce00\" d=\"M0 341.3h512V512H0z\"/>    <path d=\"M0 0h512v170.7H0z\"/>    <path fill=\"#d00\" d=\"M0 170.7h512v170.6H0z\"/></svg>",
        }
        this.defined_language_keys = Object.keys(this.defined_languages);
        this.languages = this.getLanguages(languages);
        this.createBreadCrumb()
        this.translate()
        this.selectDefaultLanguage(default_language);
    }

    getLanguages(languages){
        if(languages.length === 0)
            return this.defined_languages;

        let languagesObj = {};
        const defined_language_keys = this.defined_language_keys;
        for(const language of languages){
            if(defined_language_keys.includes(language))
                languagesObj[language] = this.defined_languages[language];
            else
                console.warn(`Language '${language}' is not defined.`)
        }
        return languagesObj;
    }

    selectDefaultLanguage(default_language){
        if(!default_language)
            return;
        if(!this.defined_language_keys.includes(default_language))
            console.warn(`default_language parameter passed to the function '${default_language}' does not exist`)
        else
            document.querySelector(`[data-id="${default_language}"]`).click()
    }

    createBreadCrumb(){
        let elemDiv = document.createElement('div');
        Object.keys(this.languages).forEach(key => {
            let lang = document.createElement('div')
            lang.innerHTML = this.languages[key].trim();
            lang.setAttribute('data-id', key)
            this.addEventListenerToElement(lang)
            elemDiv.appendChild(lang)
        });

        elemDiv.setAttribute('id', 'translator-breadcrumb')
        if(this.selector !== "body")
            elemDiv.setAttribute('style', 'position: unset !important;');
        document.querySelector(this.selector).appendChild(elemDiv)
    }

    addEventListenerToElement(lang){
        let _this = this;
        lang.addEventListener("click", function() {
            _this.toggleActive(this)
            _this.translate(this.getAttribute('data-id'))
        }, false);
    }

    toggleActive(elem) {
        let listOfElements = document.querySelectorAll('#translator-breadcrumb div');
        listOfElements = [...listOfElements];
        listOfElements.forEach(element => {
            element.classList.remove("active");
        });
        elem.classList.add("active");
        localStorage.setItem("language", elem.getAttribute('data-id'));
    }

    translate(language = null){
        if(!language) document.querySelector(`[data-id=${this.default_language}]`).classList.add('active')
        let listOfTranslateObjects = [...document.querySelectorAll('[data-translate]')];
        let listOfTranslateObjectPlaceHolders = [...document.querySelectorAll('[data-translate][placeholder]')];
        let defaultTexts = [...document.querySelectorAll('.dropdown div.text[data-translate] ')];
        listOfTranslateObjects = listOfTranslateObjects
            .filter(x => !listOfTranslateObjectPlaceHolders.includes(x));
        this.translateObjects(language,[...listOfTranslateObjects])
        this.translateObjects(language,[...listOfTranslateObjectPlaceHolders], 'placeholder')
        this.translateObjects(language,[...defaultTexts], 'default-text')
    }

    translateObjects(language, Objects, type="html"){
        let _this = this;
        let dictionary_page = OneXTranslateDictionary[this.translate_key];
        if(type==='default-text'){
            Objects.forEach(element => {
                let item = element.parentElement.querySelector('div.item.selected');
                if(item && typeof item !== 'undefined'){
                    _this.changeData(element, dictionary_page[item.getAttribute('data-translate')][language ? language : this.default_language], "html")
                }
            });
        }else{
            if(!dictionary_page || typeof dictionary_page === 'undefined'){
                console.warn(`No dictionary is found for '${language}'(translate-key: ${this.translate_key}). Update dictionary`)
            }else{
                Objects.forEach(element => {
                    const translationKeyword = element.getAttribute('data-translate');
                    let word_dict = dictionary_page[translationKeyword] ?? OneXTranslateGeneralWords[translationKeyword]
                    if(!word_dict || typeof word_dict === 'undefined'){
                        console.warn(`Key '${translationKeyword}' is not found. Update dictionary`)
                    }else{
                        let word = word_dict[language ? language : this.default_language];
                        if(!word || typeof word === 'undefined'){
                            const wordData = OneXTranslateGeneralWords[translationKeyword];
                            if(wordData)
                                word = [language ? language : this.default_language];
                        }
                        _this.changeData(element, word, type)
                    }
                });
            }
        }
    }

    changeData(element, word, type){
        if(type==="html"){
            element.innerHTML = word;
        }else{
            element.setAttribute(type, word);
        }
    }

    reTranslate(){
        this.translate()
    }
}
