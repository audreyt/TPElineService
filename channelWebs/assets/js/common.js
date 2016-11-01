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

function deleteSub(sel) {
    var desc = document.getElementById('subc__desc'),
        infoaction = document.getElementById('subc__infoaction');
    var payload = {
        'authToken': AUTH_TOKEN,
        'memberId': sel.dataset.mid,
        'datasetId': sel.value
    };

    var api = API_HOST + SRU + '/deleteSubscriptionContainer/';
    $.ajax({
        url: api,
        data: JSON.stringify(payload),
        method: 'DELETE',
        dataType: 'json',
        async: false
    }).done(function(data) {
        if (data['result'] === false) {
            alert('您已刪除服務');
            return;
        }
        infoaction.innerHTML = '';
        desc.innerHTML = '';
        desc.innerHTML = '<span class="subc__support">已為您刪除' + SUBSCRIBE_TYPE[sel.value] + '服務</span>';

    }).fail(function(jqXhr, text, et) {

    });
}

function addSub(sel) {
    var desc = document.getElementById('subc__desc'),
        infoaction = document.getElementById('subc__infoaction');
    var payload = {
        'authToken': AUTH_TOKEN,
        'memberId': sel.dataset.mid,
        'datasetId': sel.value
    };
    if (typeof sel.dataset.areacode !== 'undefined') {
        var detailData = {
            'area': [sel.dataset.areacode.toString()],
        };
        payload['subscribeDetail'] = JSON.stringify(detailData);
    }
    var api = API_HOST + SRU + '/addSubscriptionContainer/';
    $.ajax({
        url: api,
        method: 'POST',
        data: JSON.stringify(payload),
        dataType: 'json',
        async: false
    }).done(function(data) {
        if (data['result'] === false) {
            alert('您已訂閱過本服務');
            return;
        }
        infoaction.innerHTML = '';
        if (sel.value === 'ncdr_flood') {
            var btnblock = document.getElementById('subc__btnblock');
            desc.innerHTML = '<span class="subc__support">您已完成訂閱【' + getGeocodeName(TAIWAN_POSTWITHGEO_CODE_TPE[sel.dataset.postcode]) + '】的淹水資訊 ，如欲訂閱其他行政區，請點擊下方按鈕。</span>';
            btnblock.innerHTML = '<button style="margin-top: 5px;" class="sbtn--want" onclick="location.reload();">繼續訂閱其他區域</button>';
        } else {
            desc.innerHTML = '<span class="subc__support">感謝您訂閱' + SUBSCRIBE_TYPE[sel.value] + '服務!</span>';
        }
    }).fail(function(jqXhr, text, et) {});
}
