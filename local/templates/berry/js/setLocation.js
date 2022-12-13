//работа с модальным окном выбора города доставки

function setLocation(location) {
    let urlParam = (document.documentURI.indexOf('?') === -1) ? '?' : '&';

    BX.setCookie('locationSelected', 1, {expires: 31536000, path: '/'}); //на год

    document.location.href = document.documentURI + urlParam + 'location=' + location;
}

window.addEventListener("DOMContentLoaded", function () {
    if (BX.getCookie('locationSelected'))
        return false;
    
    MicroModal.show('tmp555');
});
