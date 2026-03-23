let userInfo = [];
$(document).ready(async function() {
    $('.ui.accordion').accordion();

    userInfo = await getUserInfo();
    if (userInfo.isAdmin)
        $('#adminMenuItem').css('display', '');
    else
        $('#adminMenuItem').css('display', 'none');

    const currentPath = window.location.pathname + window.location.search;
    const sidebarLinks = document.querySelectorAll(".ui.sidebar.menu a.item");

    sidebarLinks.forEach(link => {
        if (link.getAttribute("href") === currentPath) {
            link.classList.add("active");
        } else {
            link.classList.remove("active");
        }
    });

    scrollToTopButton();
})

function showConfirmationDialog({ title, htmlContent, confirmButtonText, confirmButtonColor = "#43BB00", cancelButtonColor = "#6e7881", onConfirm, cancelButtonText = "Cancel" }) {
    Swal.fire({
        title: title,
        html: htmlContent,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: confirmButtonColor,
        cancelButtonColor: cancelButtonColor,
        confirmButtonText: confirmButtonText,
        cancelButtonText: cancelButtonText,
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            onConfirm();
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            console.log('Action cancelled.');
        }
    });
}

function showErrorDialog(htmlContent) {
    return Swal.fire({
        title: 'Error',
        html: htmlContent,
        icon: 'error',
        confirmButtonText: 'OK'
    });
}

function showSuccessDialog(text) {
    return Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: text,
        timer: 2000,
        timerProgressBar: true,
    });
}

function fireToastr(type, text, disableHiding = false){
    const hideAfterVal = (disableHiding) ? disableHiding : 10000;

    if(type === "error"){
        $.toast({
            heading: 'Error!',
            text: text,
            icon: 'error',
            position: 'top-right',
            bgColor: 'red',
            loaderBg: 'brown',
            hideAfter: hideAfterVal,
            stack: false
        })
    }
    else if(type === "success"){
        $.toast({
            heading: 'Saved!',
            text: text,
            icon: 'success',
            position: 'top-right',
            bgColor: '#1E8F39',
            loaderBg: 'yellow',
            hideAfter: 5000,
            stack: false
        })
    }
    else if(type === "warning"){
        $.toast({
            heading: 'Warning!',
            text: text,
            icon: 'warning',
            position: 'top-right',
            bgColor: '#969600',
            loaderBg: 'yellow',
            hideAfter: hideAfterVal,
            stack: false
        })
    }
}


// PROJECTS PAGE SEARCH PROJECT SELECT BOX FOR EVERY PROJECT
async function initializeSearchProjectSelect() {
    const projectSelectBox = $('#searchProjectSelect');

    projectSelectBox.dropdown({
        apiSettings: {
            url: `/dpm/dtoconfigurator/api/controllers/BaseController.php?action=searchProject&keyword={query}`,
            cache: false,
            onResponse: function(response) {
                const menuElement = document.querySelector('.searchProjectSelect .menu.transition.visible');
                const projects = Array.isArray(response) ? response : Object.values(response);
                let results = [];

                if (projects.length === 0) {
                    if (menuElement)
                        menuElement.innerHTML = '';
                    return { results };
                }
                
                results = projects.map(project => ({
                    name: `<b>${project.FactoryNumber}</b> - ${project.ProjectName}`,
                    value: project.FactoryNumber
                }));

                return { results };
            }
        },
        fields: {
            remoteValues: 'results',
            name: 'name',
            value: 'value'
        },
        minCharacters: 2,
        clearable: true,
        allowAdditions: false,
        fullTextSearch: true,
        forceSelection: false,
        selectOnKeydown:false,
        onChange: function(value) {
            if (value) {
                window.open(`/dpm/dtoconfigurator/core/projects/detail/info.php?project-no=${value}`, '_blank');
            }
        }
    });
}

// Helper Functions
function hideElement(selector) {
    $(selector).hide();
}

function showElement(selector) {
    $(selector).show();
}

function showLoader(selector) {
    $(`${selector} .loader`).show();
}

function hideLoader(selector) {
    $(`${selector} .loader`).hide();
}

function addButtonLoader(selector) {
    $(`${selector}`).addClass('loading disabled');
}

function removeButtonLoader(selector) {
    $(`${selector}`).removeClass('loading disabled');}

function updateUrlParameter(key, value) {
    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set(key, value);
    window.history.pushState({}, '', currentUrl);
}

function getUrlParam(paramName) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(paramName);
}

function removeUrlParam(key) {
    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.delete(key);
    window.history.pushState({}, '', currentUrl);
}

function removeUrlParamArray(params) {
    const currentUrl = new URL(window.location.href);
    params.forEach(param => {
        currentUrl.searchParams.delete(param);
    });
    window.history.pushState({}, '', currentUrl);
}

function parseDateTime(dateTimeString) {
    // Format: "DD.MM.YYYY HH:mm"
    if (!dateTimeString) return new Date(0);

    const parts = dateTimeString.split(' ');
    const dateParts = parts[0].split('.');
    const timeParts = parts[1].split(':');

    // new Date(year, month, day, hours, minutes)
    // Not: month 0-indexed olduğu için -1 yapıyoruz
    return new Date(
        parseInt(dateParts[2]), // year
        parseInt(dateParts[1]) - 1, // month
        parseInt(dateParts[0]), // day
        parseInt(timeParts[0]), // hours
        parseInt(timeParts[1])  // minutes
    );
}


function shortDescription(description, charLength) {
    if (!description) return '';
    const cleanedDescription = description.replace(/V:/g, '').trim().replace('Description:', '');
    return cleanedDescription.slice(0, charLength);
}

function decodeHtmlEntities(text) {
    const parser = new DOMParser();
    return parser.parseFromString(text, "text/html").body.textContent;
}

function formatDescription(description, splitLength) {
    if (!description) return '';

    // Decode HTML entities before processing
    description = decodeHtmlEntities(description);

    // Split the description by "V:"
    const splitParts = description.split('V:');
    if (splitParts.length < splitLength) {
        // Return the cleaned up description if there are fewer than splitLength parts
        return description.replace(/V:/g, '').replace(/Description:/g, '').trim();
    }

    // Extract the parts between the second and fourth "V:"
    const extractedParts = splitParts.slice(1, splitLength);

    // Join the extracted parts and clean up "V:" and "Description:"
    return extractedParts.join(' ').replace(/Description:/g, '').trim();
}

function formatKukoDescription(description, splitLength) {
    if (!description) return '';

    // Decode HTML entities before processing
    description = decodeHtmlEntities(description);

    // Remove variants like "NX_-Tools Configuration:", "NXTools+ Configuration :", etc.
    description = description.replace(/nx[\w\s\-+]*configuration\s*:/i, '');

    // Split the description by "V:"
    const splitParts = description.split('V:');
    if (splitParts.length < splitLength) {
        // Return the cleaned-up description if there are fewer than splitLength parts
        return description.replace(/V:/g, '').replace(/Description:/g, '').trim();
    }

    // Extract the parts between the second and fourth "V:"
    const extractedParts = splitParts.slice(1, splitLength);

    // Join the extracted parts and clean up "V:" and "Description:"
    return extractedParts.join(' ').replace(/Description:/g, '').trim();
}



function formatKukoDtoNumber(dtoNumberKuko) {
    // Remove everything after the first dot, including the dot
    dtoNumberKuko = dtoNumberKuko.replace(/\.\d+/, '');

    // Remove ":: KUKO_CON_CST_" from the beginning of the string
    dtoNumberKuko = dtoNumberKuko.replace(/^:: KUKO_CON_CST_/, '');

    // Trim trailing dots
    return dtoNumberKuko.replace(/\.$/, '');
}

function formatDtoNumber(dtoNumber) {
    // Remove everything after the first dot, including the dot
    dtoNumber = dtoNumber.replace(/\.\d+/, '');

    // Remove ":: " from the beginning of the string
    dtoNumber = dtoNumber.replace(/^:: /, '');

    // Trim trailing dots
    return dtoNumber.replace(/\.$/, '');
}

const escapeString = (str) => str.replace(/'/g, "\\'").replace(/"/g, '\\"').replace(/\n/g, '\\n');
const escapeHtml = (str) =>
    str.replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;')
        .replace(/\+/g, '&#43;')
        .replace(/\\/g, '&#92;');


//ENLARGE IMG
$(document).on('click', '.enlargeable-image', function () {
    const imageUrl = $(this).attr('src'); // Get the source of the clicked image
    $('#enlargedImage').attr('src', imageUrl); // Set the source of the modal image
    $('#imageEnlargeModal').modal({
        centered: false, // Disable automatic vertical centering
        allowMultiple: false, // Ensure only one modal is open at a time
        dimmerSettings: {closable: true} // Ensure the dimmer can be clicked to close the modal
    }).modal('show');
});


function scrollToTopButton() {
    let $scrollButton = $('#goToTopButton');

    $(window).on('scroll', function () {
        if ($(this).scrollTop() > 100) {
            $scrollButton.fadeIn(); // Show button when scrolling down
        } else {
            $scrollButton.fadeOut(); // Hide button when at the top
        }
    });

    $scrollButton.on('click', function () {
        $('html, body').animate({ scrollTop: 0 }, 300);
    });
}

async function fetchCountOfNcAndTkNotesOfTkForm() {
    const id = getUrlParam('id');
    const dtoNumber = getUrlParam('dto-number');

    const response = await axios.get('/dpm/dtoconfigurator/api/controllers/TkFormController.php', {
        params: { action: 'getCountOfNcAndTkNotesOfTkForm', id: id, dtoNumber: dtoNumber }
    });

    return response.data;
}

async function getUserInfo() {
    const response = await axios.get('/dpm/dtoconfigurator/api/controllers/BaseController.php', {
        params: { action: 'getUserInfo' }
    });

    return response.data;
}

