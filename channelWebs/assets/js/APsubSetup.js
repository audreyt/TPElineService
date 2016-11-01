window.addEventListener("load", function(e) {
    document.addEventListener('deviceready', function(e) {
        document.addEventListener('touchstart', function(e) { e.stopPropagation(); }, false);
        var options = {
            pageKey: "APsubSetup",
            entryPage: false,
            titleBar: {
                left: {
                    imgId: "btn_default",
                    text: (navigator.language === 'zh-tw') ? "回首頁" : "Home",
                    visible: true,
                    enable: true,
                },
                center: {
                    text: "空氣盒子資訊訂閱設定",
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
                    // do nothing
                    break;
                case "BACK":
                    history.go(-1);
                    break;
                case "TITLE":
                    // do nothing
                    break;
            }
        });
    }, false);
    var action = getURIQueryString('act'),
        areaCode = getURIQueryString('ac'),
        postCode = getURIQueryString('ptc'),
        mid = getURIQueryString('mid');

    setupAirbox(mid, postCode, areaCode, action);
});

function setupAirbox(mid, postCode, areaCode, action) {
    var desc = document.getElementById('subc__desc');
    desc.innerHTML = '<span class="subc__support">' + '設定【' + getGeocodeName(TAIWAN_POSTWITHGEO_CODE_TPE[postCode]) + '】的空氣盒子資訊推播頻率</span>';
    switch (action) {
        case 'addAirboxSubArea':
            var checkBoxes = document.querySelectorAll('.aircheck'),
                sbtn = document.getElementById('submitAirboxSubInfo');
            sbtn.value = areaCode;
            sbtn.setAttribute('data-postcode', postCode);
            sbtn.setAttribute('data-mid', mid);
            sbtn.setAttribute('onclick', 'updateAirboxSubArea(this, \'addAirboxSubArea\')');
            checkboxlimit(checkBoxes, 3);

            document.getElementById('setup').style.display = 'block';
            break;
        case 'updateAirboxSubArea':
            $.ajax({
                url: API_HOST + SRU + '/listSubscriptionContainer/',
                data: {
                    'memberId': mid,
                    'datasetId': 'airbox',
                    'authToken': AUTH_TOKEN,
                    'st': new Date().getTime(),
                },
                method: 'GET',
                dataType: 'json',
                async: true
            }).done(function(data) {
                if (data['result'] === false) {
                    alert('操作錯誤');
                    return;
                }
                var jsonData = JSON.parse(data['data']);
                var checkBoxes = document.querySelectorAll('.aircheck'),
                    sbtn = document.getElementById('submitAirboxSubInfo');
                // deal with selected items
                var detailData = JSON.parse(jsonData[0]['detail']);
                detailData.forEach(function(ele, idx) {
                    for (var i = 0; i < 48; i++) {
                        if (ele['area'] !== areaCode) {
                            continue;
                        }
                        if (ele['timeToPush'].indexOf(checkBoxes[i].value) > -1) {
                            checkBoxes[i].checked = true;
                        }
                    }
                });
                sbtn.value = areaCode;
                sbtn.setAttribute('data-postcode', postCode);
                sbtn.setAttribute('data-mid', mid);
                sbtn.setAttribute('onclick', 'updateAirboxSubArea(this, \'updateAirboxSubArea\')');
                checkboxlimit(checkBoxes, 3);

                document.getElementById('setup').style.display = 'block';
            }).fail(function(jqXhr, text, et) {});
            break;
        default:
            // add new sub
            desc.innerHTML = '<span class="subc__support">' + '設定【' + getGeocodeName(TAIWAN_POSTWITHGEO_CODE_TPE[postCode]) + '】的空氣盒子資訊推播頻率</span>';
            var checkBoxes = document.querySelectorAll('.aircheck'),
                sbtn = document.getElementById('submitAirboxSubInfo');
            sbtn.value = areaCode;
            sbtn.setAttribute('data-postcode', postCode);
            sbtn.setAttribute('data-mid', mid);
            sbtn.setAttribute('onclick', 'addAirboxSub(this)');
            checkboxlimit(checkBoxes, 3);

            document.getElementById('setup').style.display = 'block';
            break;
    }
}

function updateAirboxSubArea(sel, action) {

    var desc = document.getElementById('subc__desc');
    var checkedBoxes = document.querySelectorAll('.aircheck:checked');
    if (checkedBoxes.length === 0) {
        alert('至少選 1 個時段');
        return;
    } else {
        var checkedValues = '';
        for (var i = 0; i < checkedBoxes.length; i++) {
            checkedValues += checkedBoxes.item(i).value;
            if (i < checkedBoxes.length - 1) checkedValues += ','
        }
        var detailData = {
            area: sel.value.toString(),
            timeToPush: checkedValues
        };
        detailData = JSON.stringify(detailData);
        var payload = {
            'authToken': AUTH_TOKEN,
            'memberId': sel.dataset.mid,
            'datasetId': 'airbox',
            'subscribeDetail': detailData,
            'todo': action
        };
        var api = API_HOST + SRU + '/updateSubscriptionContainer/';
        $.ajax({
            url: api,
            data: JSON.stringify(payload),
            dataType: 'json',
            method: 'PUT',
            async: false
        }).done(function(data) {
            if (data['result'] === false) {
                alert('您已訂閱過本服務');
                return;
            }
            desc.innerHTML = '<span class="subc__support">感謝您訂閱 【' + getGeocodeName(TAIWAN_POSTWITHGEO_CODE_TPE[sel.dataset.postcode]) + '】 ' + SUBSCRIBE_TYPE['airbox'] + '推播服務!</span>';
            document.getElementById('content').innerHTML = '<button class="sbtn--want" onclick="location.href=\'index.php\'">繼續訂閱其他區域</button>';
        }).fail(function(jqXhr, text, et) {});
    }
}

function addAirboxSub(sel) {
    var desc = document.getElementById('subc__desc');
    var checkedBoxes = document.querySelectorAll('.aircheck:checked');
    if (checkedBoxes.length === 0) {
        alert('至少選 1 個時段');
        return;
    } else {
        var checkedValues = '';
        for (var i = 0; i < checkedBoxes.length; i++) {
            checkedValues += checkedBoxes.item(i).value;
            if (i < checkedBoxes.length - 1) checkedValues += ','
        }
        var detailData = [{
            area: sel.value.toString(),
            timeToPush: checkedValues
        }];
        detailData = JSON.stringify(detailData);
        var payload = {
            'authToken': AUTH_TOKEN,
            'memberId': sel.dataset.mid,
            'datasetId': 'airbox',
            'subscribeDetail': detailData,
        };
        var api = API_HOST + SRU + '/addSubscriptionContainer/';
        $.ajax({
            url: api,
            data: JSON.stringify(payload),
            dataType: 'json',
            method: 'POST',
            async: false
        }).done(function(data) {
            if (data['result'] === false) {
                alert('您已訂閱過本服務');
                return;
            }
            desc.innerHTML = '<span class="subc__support">感謝您訂閱 【' + getGeocodeName(TAIWAN_POSTWITHGEO_CODE_TPE[sel.dataset.postcode]) + '】 ' + SUBSCRIBE_TYPE['airbox'] + '推播服務!</span>';
            document.getElementById('content').innerHTML = '<button class="sbtn--want" onclick="location.href=\'index.php\'">繼續訂閱其他區域</button>';
        }).fail(function(jqXhr, text, et) {});
    }
}

function checkboxlimit(checkgroup, limit) {
    for (var i = 0; i < checkgroup.length; i++) {
        checkgroup[i].onclick = function() {
            var checkedcount = 0;
            for (var i = 0; i < checkgroup.length; i++)
                checkedcount += (checkgroup[i].checked) ? 1 : 0;
            if (checkedcount > limit) {
                alert('您只能指定 ' + limit + ' 個時段!');
                this.checked = false;
            }
        };
    }
}
