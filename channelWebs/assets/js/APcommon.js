function getGeocode(obj) {
    return Object.keys(obj)[0];
}

function getGeocodeName(obj) {
    return obj[Object.keys(obj)[0]];
}
/**
 * check if obj is empty
 */
function objIsEmpty(obj) {
    for (var key in obj) {
        if (obj.hasOwnProperty(key)) {
            return false;
        }
    }
    return true;
}
// End A
/**
 * 取得URL query string
 */
function getURIQueryString(name, url) {
    if (!url) url = window.location.href;
    name = name.replace(/[\[\]]/g, "\\$&");
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, " "));
}

function gotoSetupPage(sel, action) {
    action = (typeof action === 'undefined') ? '' : action;
    location.href = 'setupAirboxSubInfo.php?mid=' + sel.dataset.mid + '&act=' + action + '&ac=' + sel.value + '&ptc=' + sel.dataset.postcode;
}
