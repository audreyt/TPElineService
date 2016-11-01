window.addEventListener("load", function(e) {
    document.addEventListener("deviceready", function(e) {
        document.addEventListener('touchstart', function(e) { e.stopPropagation(); }, false);
        var options = {
            pageKey: "EOC",
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
            closeBtn = document.querySelector('span.modal__close');

        closeBtn.setAttribute('onclick', 'closeEOCRst()');

        var payload = {
            'memberId': getURIQueryString('mid'),
            'datasetId': getURIQueryString('did'),
            'authToken': AUTH_TOKEN,
            'st': new Date().getTime()
        };
        desc.innerHTML = '<span class="subc__support">按下「行政區」，即可查詢各項災情數量統計，點下「查看地圖資訊」，即可以地圖查看區域災情。</span>';
        for (var i in TAIWAN_POSTWITHGEO_CODE_TPE) {
            var btn = document.createElement('button');
            btn.className = 'areaBtn';
            btn.value = getGeocode(TAIWAN_POSTWITHGEO_CODE_TPE[i]);
            btn.setAttribute('onclick', 'showEOCInfo(this)');
            btn.setAttribute('data-postcode', i);
            btn.innerHTML = getGeocodeName(TAIWAN_POSTWITHGEO_CODE_TPE[i]);
            btnblock.appendChild(btn);
        }
    }, false);
});


function showEOCInfo(sel) {
    var infoaction = document.getElementById('subc__infoaction'),
        modal = document.getElementById('eoc__modal');
    var mm = document.getElementById('eoc_modal_master');
    var api = './GetDisasterStat.php?d=' + sel.innerHTML;
    $.ajax({
        url: api,
        method: 'GET',
        dataType: 'json',
        async: true
    }).done(function(data) {
        infoaction.innerHTML = '<p>【' + getGeocodeName(TAIWAN_POSTWITHGEO_CODE_TPE[sel.dataset.postcode]) + '】</p>';
        var le = document.createElement('br');
        if (!objIsEmpty(data)) {
            for (var i in data) {
                var infoPerLine = document.createElement('span');
                infoPerLine.style.display = 'block';
                var infoText = document.createTextNode(i + ' : ' + data[i]);
                infoPerLine.appendChild(infoText);
                infoaction.appendChild(infoPerLine);
            }
            var iaa = document.createElement('a');
            iaa.href = 'http://210.59.250.198/EOCLineMap/Map.html?District=' + getGeocodeName(TAIWAN_POSTWITHGEO_CODE_TPE[sel.dataset.postcode]);
            iaa.style.fontSize = '22px';
            iaa.setAttribute('target', '_blank');
            iaa.innerHTML = '<br>查看更多資訊請點我';
            infoaction.appendChild(iaa);
        } else {
            infoaction.innerHTML += '<span style="font-size: 20px;">目前尚無災情資訊</span>';
        }
    }).fail(function(jqXhr, text, et) {});
}

function closeEOCRst() {
    var modal = document.getElementById('eoc__modal');
    modal.style.display = 'none';
}
