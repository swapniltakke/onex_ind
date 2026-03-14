// /dpm/dto_configurator/js/main.js
let userInfo = [];

$(document).ready(async function() {
    $('.ui.accordion').accordion();

    // Get user info from DTOConfiguratorController
    userInfo = await getUserInfo();
    
    if (userInfo && userInfo.isAdmin) {
        $('#adminMenuItem').css('display', '');
    } else {
        $('#adminMenuItem').css('display', 'none');
    }

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
});

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

function hideElement(selector) {
    $(selector).hide();
}

function showElement(selector) {
    $(selector).show();
}

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

function scrollToTopButton() {
    let $scrollButton = $('#goToTopButton');

    $(window).on('scroll', function () {
        if ($(this).scrollTop() > 100) {
            $scrollButton.fadeIn();
        } else {
            $scrollButton.fadeOut();
        }
    });

    $scrollButton.on('click', function () {
        $('html, body').animate({ scrollTop: 0 }, 300);
    });
}

/**
 * Get user information from DTOConfiguratorController
 */
async function getUserInfo() {
    try {
        const response = await axios.get('/dpm/api/DTOConfiguratorController.php', {
            params: { action: 'getUserInfo' }
        });
        
        if (response.data && response.data.success && response.data.data) {
            return response.data.data;
        }
        
        return {
            success: false,
            isAdmin: false
        };
    } catch (error) {
        console.error('Error fetching user info:', error);
        return {
            success: false,
            isAdmin: false
        };
    }
}