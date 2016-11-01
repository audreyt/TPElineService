window.addEventListener("load", function(e) {
    document.addEventListener("deviceready", function(e) {
        document.addEventListener('touchstart', function(e) { e.stopPropagation(); }, false);
        var options = {
            pageKey: "NCDRFlood",
            entryPage: false,
            titleBar: {
                left: {
                    imgId: "btn_default",
                    text: (navigator.language === 'zh-tw') ? "回首頁" : "Home",
                    visible: true,
                    enable: true,
                },
                center: {
                    text: "防汛資訊訂閱平台",
                    clickable: false
                },
            }
        };
        LCS.Interface.updateTitleBar(options);
        LCS.Interface.registerTitleBarCallback(function(evt) {
            switch (evt.target) {
                case "LBUTTON":
                    history.go(-1);
                    break;
                case "RBUTTON":
                    // window.close();
                    break;
                case "BACK":
                    history.go(-1);
                    break;
                case "TITLE":
                    // do nothing
                    break;
            }
        });
        var desc = document.getElementById('subc__desc'),
            btnblock = document.getElementById('subc__btnblock'),
            infoaction = document.getElementById('subc__infoaction');
        var payload = {
            'memberId': getURIQueryString('mid'),
            'datasetId': getURIQueryString('did'),
            'authToken': AUTH_TOKEN,
            'st': new Date().getTime(),
        };
        var isSubscribed = API_HOST + SRU + '/listSubscriptionContainer/';
        $.ajax({
            url: isSubscribed,
            data: payload,
            dataType: 'json',
            method: 'GET',
            async: true
        }).done(function(data) {
            if (data['result'] === false) {
                desc.innerHTML = '<span class="subc__support">按下「行政區」，再按下「我要訂閱【ＯＯ區】的淹水資訊」，當有淹水警示時，我們將會用LINE訊息通知您。</span>';
                for (var i in TAIWAN_POSTWITHGEO_CODE_TPE) {
                    var btn = document.createElement('button');
                    btn.className = 'areaBtn';
                    btn.value = getGeocode(TAIWAN_POSTWITHGEO_CODE_TPE[i]);
                    btn.setAttribute('data-postcode', i);
                    btn.setAttribute('onclick', 'showInfo(this)');
                    btn.innerHTML = getGeocodeName(TAIWAN_POSTWITHGEO_CODE_TPE[i]);
                    btnblock.appendChild(btn);
                }
                return;
            }
            var unSubList = {
                    '100': { '6300500': '中正區' },
                    '103': { '6300600': '大同區' },
                    '104': { '6300400': '中山區' },
                    '105': { '6300100': '松山區' },
                    '106': { '6300300': '大安區' },
                    '108': { '6300700': '萬華區' },
                    '110': { '6300200': '信義區' },
                    '111': { '6301100': '士林區' },
                    '112': { '6301200': '北投區' },
                    '114': { '6301000': '內湖區' },
                    '115': { '6300900': '南港區' },
                    '116': { '6300800': '文山區' },
                },
                subcedInfo = document.getElementById('subcedInfo');

            var jsonData = JSON.parse(data['data']);
            var detailData = JSON.parse(jsonData[0]['detail']);

            desc.innerHTML = '<span class="subc__support">您已訂閱下列資訊</span>';

            for (var i = 0; i < 13; i++) {
                if (typeof detailData['area'][i] === 'undefined') {
                    continue;
                }
                // appending table element
                var row = document.createElement('tr'),
                    cell = document.createElement('td'),
                    cellbtn = document.createElement('button');
                var postCode;
                for (var j in TAIWAN_POSTWITHGEO_CODE_TPE) {
                    if (detailData['area'][i] === getGeocode(TAIWAN_POSTWITHGEO_CODE_TPE[j])) {
                        var cellText = getGeocodeName(TAIWAN_POSTWITHGEO_CODE_TPE[j]);
                        postCode = j;
                        delete unSubList[j];
                    }
                }
                cellbtn.setAttribute('data-postcode', postCode);
                cellbtn.className = 'sbtn--cancel';
                cellbtn.value = payload['datasetId'];
                cellbtn.innerHTML = '【' + cellText + '】取消訂閱';
                cellbtn.setAttribute('data-action', 'cancelArea');
                cellbtn.setAttribute('data-areacode', detailData['area'][i]);
                cellbtn.setAttribute('data-mid', payload['memberId']);
                cellbtn.setAttribute('onclick', 'cancelNCDRFloodSubArea(this)');
                cell.appendChild(cellbtn);
                row.appendChild(cell);
                subcedInfo.appendChild(row);
            }
            if (objIsEmpty(unSubList)) {
                return;
            }
            infoaction.innerHTML = '<span class="subc__support" style="color:#000;">繼續訂閱下列項目</span><br>';
            for (var j in unSubList) {
                var iabtn = document.createElement('button');
                iabtn.value = getGeocode(unSubList[j]);
                iabtn.className = 'areaBtn';
                iabtn.setAttribute('data-postcode', j);
                iabtn.setAttribute('onclick', 'showInfo(this, \'addNewArea\')');
                iabtn.innerHTML = getGeocodeName(unSubList[j]);

                infoaction.appendChild(iabtn);
            };
            return;
        }).fail(function(jqXhr, text, et) {});
    }, false);
});

function cancelNCDRFloodSubArea(sel) {
    var desc = document.getElementById('subc__desc'),
        btnblock = document.getElementById('subc__btnblock'),
        tbl = document.getElementById('subc__table'),
        infoaction = document.getElementById('subc__infoaction');

    var detailData = {
        area: [sel.dataset.areacode.toString()],
    };
    detailData = JSON.stringify(detailData);
    var payload = {
        'authToken': AUTH_TOKEN,
        'memberId': sel.dataset.mid,
        'datasetId': sel.value,
        'subscribeDetail': detailData,
        'todo': sel.dataset.action
    };
    var cancelSubCAPI = API_HOST + SRU + '/updateSubscriptionContainer/';
    $.ajax({
        url: cancelSubCAPI,
        data: JSON.stringify(payload),
        dataType: 'json',
        method: 'PUT',
        async: false
    }).done(function(data) {
        if (data['result'] === false) {
            alert('您已訂閱過本服務');
            return;
        }
        desc.innerHTML = '<span class="subc__support">已為您取消' + SUBSCRIBE_TYPE[sel.value] + '-【' + getGeocodeName(TAIWAN_POSTWITHGEO_CODE_TPE[sel.dataset.postcode]) + '】服務!</span>';
        btnblock.innerHTML = '<button style="margin-top: 5px;" class="sbtn--want" onclick="location.reload();">繼續訂閱其他區域</button>';
        tbl.innerHTML = '';
        infoaction.innerHTML = '';
    }).fail(function(jqXhr, text, et) {});
}

function addNCDRFloodSubArea(sel) {
    var desc = document.getElementById('subc__desc'),
        btnblock = document.getElementById('subc__btnblock'),
        tbl = document.getElementById('subc__table'),
        infoaction = document.getElementById('subc__infoaction');
    var detailData = {
        area: [sel.dataset.areacode.toString()],
    };
    detailData = JSON.stringify(detailData);
    var payload = {
        'authToken': AUTH_TOKEN,
        'memberId': sel.dataset.mid,
        'datasetId': sel.value,
        'subscribeDetail': detailData,
        'todo': sel.dataset.action
    };
    var addSubCAPI = API_HOST + SRU + '/updateSubscriptionContainer/';
    $.ajax({
        url: addSubCAPI,
        data: JSON.stringify(payload),
        dataType: 'json',
        method: 'PUT',
        async: false
    }).done(function(data) {
        if (data['result'] === false) {
            alert('您已訂閱過本服務');
            return;
        }
        desc.innerHTML = '<span class="subc__support">您已完成訂閱【' + getGeocodeName(TAIWAN_POSTWITHGEO_CODE_TPE[sel.dataset.postcode]) + '】的淹水資訊 ，如欲訂閱其他行政區，請點擊下方按鈕。</span>';
        btnblock.innerHTML = '<button style="margin-top: 5px;" class="sbtn--want" onclick="location.reload();">繼續訂閱其他區域</button>';
        tbl.innerHTML = '';
        infoaction.innerHTML = '';
    }).fail(function(jqXhr, text, et) {});

}

function showInfo(sel, action) {
    var desc = document.getElementById('subc__desc'),
        infoaction = document.getElementById('subc__infoaction'),
        btnblock = document.getElementById('subc__btnblock'),
        btntable = document.getElementById('subc__table');

    var payload = {
        'memberId': getURIQueryString('mid'),
        'datasetId': getURIQueryString('did'),
        'authToken': AUTH_TOKEN,
        'areaCode': sel.value,
    };
    var api = API_HOST + SRU + '/listDataset/';
    $.ajax({
        url: api,
        data: payload,
        dataType: 'json',
        method: 'GET',
        async: true
    }).done(function(data) {
        desc.innerHTML = '<span class="subc__support">按下「我要訂閱【ＯＯ區】的淹水資訊」，當有淹水警示時，我們將會用LINE訊息通知您。</span>';
        var nl = document.createElement('br');
        if (data['result'] === false) {
            alert('此區域尚無此服務');
            return;
        }
        btntable.innerHTML = '';
        btnblock.innerHTML = '';
        var jsonData = JSON.parse(data['data']);
        // deal with subscribe btns
        infoaction.innerHTML = '';
        infoaction.appendChild(nl);
        var areabtn = document.createElement('button');
        areabtn.className = 'sbtn--want';
        areabtn.innerHTML = '我要訂閱【' + getGeocodeName(TAIWAN_POSTWITHGEO_CODE_TPE[sel.dataset.postcode]) + '】的淹水資訊';
        areabtn.value = payload['datasetId'];
        areabtn.setAttribute('data-action', 'addArea');
        areabtn.setAttribute('data-postcode', sel.dataset.postcode);
        areabtn.setAttribute('data-areacode', payload['areaCode']);
        areabtn.setAttribute('data-mid', payload['memberId']);
        var backbtn = document.createElement('button');
        backbtn.className = 'back';
        backbtn.setAttribute('onclick', 'location.reload()');
        if (action === 'addNewArea') {
            areabtn.setAttribute('onclick', 'addNCDRFloodSubArea(this)');
        } else {
            areabtn.setAttribute('onclick', 'addSub(this)');
        }
        infoaction.appendChild(areabtn);
        infoaction.appendChild(nl);
        infoaction.appendChild(backbtn);
        return;
    }).fail(function(jqXhr, text, et) {});
}
