window.addEventListener("load", function(e) {
    document.addEventListener('deviceready', function(e) {
        document.addEventListener('touchstart', function(e) { e.stopPropagation(); }, false);
        LCS.Interface.getProfile(function(data) {
            var mid = data.id;
            var options = {
                pageKey: "APindex",
                entryPage: true,
                titleBar: {
                    left: {
                        imgId: "btn_default",
                        text: "",
                        visible: false,
                        enable: false,
                    },
                    center: {
                        text: "空氣盒子資訊服務",
                        clickable: false
                    },
                }
            };
            LCS.Interface.updateTitleBar(options);
            LCS.Interface.registerTitleBarCallback(function(evt) {
                switch (evt.target) {
                    case "LBUTTON":
                        location.href = 'index.php';
                        break;
                    case "RBUTTON":
                        // do nothing
                        break;
                    case "BACK":
                        location.href = 'index.php';
                        break;
                    case "TITLE":
                        // do nothing
                        break;
                }
            });
            var abtns = document.getElementsByClassName('areaBtn');
            for (var i = 0; i < 12; i++) {
                abtns[i].setAttribute('data-mid', mid);
            }
            var closeBtn = document.querySelector('.modal__close');
            closeBtn.setAttribute('onclick', 'closeAirRst()');
        }, function() {
            alert('not validate member');
        });
    }, false);
});

function showAirboxInfo(sel) {
    var airinfo = document.getElementById('air__info'),
        modal = document.getElementById('air__modal');
    var payload = {
        'datasetId': 'airbox',
        'authToken': AUTH_TOKEN,
        'areaCode': sel.value,
        'st': new Date().getTime()
    };
    var api = API_HOST + SRU + '/listDatasetInfoToShow/';
    $.ajax({
        url: api,
        data: payload,
        method: 'GET',
        dataType: 'json',
        async: true
    }).done(function(data) {
        if (data['result'] === false) {
            airinfo.innerHTML = '';
            alert('此區域尚無資料可見!');
            return;
        } else {
            var jsonData = JSON.parse(data['data']);
            var dist = JSON.parse(jsonData[0]['info_to_show']);
            airinfo.innerHTML = '<p style="line-height:0.5;">【' + sel.innerHTML + '】</p>';
            if (typeof dist['result'][0] === 'undefined') {
                airinfo.innerHTML += '<span style="font-size:20px;margin-bottom:10px;display:block;">本區域尚無資料</span>';
            } else {
                airinfo.innerHTML += '<span style="font-size:20px;margin-bottom:10px;display:block;">各監測點空氣盒子如下:</span>';
                var le = document.createElement('br');
                for (var i in dist['result']) {
                    var infoPerLine = document.createElement('span');
                    infoPerLine.style.display = 'block';
                    if (typeof dist['result'][i]['pm25'] !== 'undefined') {
                        var infoText = document.createTextNode(dist['result'][i]['deviceName'] + ' PM2.5濃度 : ' + dist['result'][i]['pm25'] + '(' + pm25toStr(dist['result'][i]['pm25']) + ')');
                        infoPerLine.appendChild(infoText);
                        airinfo.appendChild(infoPerLine);
                    }
                }
                var toappend = {
                    '查看活動建議請點我': '../index.php?page=apsa',
                    '查看地圖資訊請點我': '../index.php?page=apm&ptc=' + sel.dataset.postcode,
                };
                for (var j in toappend) {
                    var iaa = document.createElement('a');
                    iaa.href = toappend[j];
                    iaa.style.fontSize = '22px';
                    iaa.innerHTML = '<br>' + j + '<br>';
                    airinfo.appendChild(iaa);
                }
            }
            checkIsSubscribed(sel, airinfo);
            // deal with modal
            var w = (window.innerWidth > 0) ? window.innerWidth : screen.width,
                h = (window.innerHeight > 0) ? window.innerHeight : screen.height;
            modal.style.display = 'block';
            modal.style.width = w - 50 + 'px';
            modal.style.height = h - 20 + 'px';
            return;
        }
    }).fail(function(jqXhr, text, et) {});
}

function pm25toStr(pm25) {
    if (pm25 < 36) {
        return '低';
    } else if (pm25 >= 36 && pm25 < 54) {
        return '中';
    } else if (pm25 >= 54 && pm25 < 71) {
        return '高';
    } else {
        return '非常高';
    }
}

function checkIsSubscribed(sel, airinfoblock) {
    $.ajax({
        url: API_HOST + SRU + '/listSubscriptionContainer/',
        data: {
            'memberId': sel.dataset.mid,
            'datasetId': 'airbox',
            'authToken': AUTH_TOKEN,
            'st': new Date().getTime(),
        },
        method: 'GET',
        dataType: 'json',
        async: true
    }).done(function(data) {
        var editbtn = document.createElement('button'),
            cancelbtn = document.createElement('button');
        editbtn.innerHTML = '訂閱/修改' + sel.innerHTML + '空氣盒子資訊';
        editbtn.className = 'sbtn--want';
        editbtn.setAttribute('data-mid', sel.dataset.mid);
        editbtn.setAttribute('data-postcode', sel.dataset.postcode);
        editbtn.value = sel.value;
        if (data['result'] === false) {
            editbtn.setAttribute('onclick', 'gotoSetupPage(this)');
            airinfoblock.appendChild(editbtn);
        } else {
            var jsonData = JSON.parse(data['data']);
            if (jsonData[0]['detail'].indexOf(sel.value) > -1) {
                editbtn.setAttribute('onclick', 'gotoSetupPage(this, \'updateAirboxSubArea\')');
                airinfoblock.appendChild(editbtn);
                airinfoblock.appendChild(document.createElement('br'));
                cancelbtn.innerHTML = '取消訂閱' + sel.innerHTML + '空氣盒子資訊';
                cancelbtn.value = sel.value;
                cancelbtn.className = 'sbtn--cancel';
                cancelbtn.setAttribute('data-mid', sel.dataset.mid);
                cancelbtn.setAttribute('data-postcode', sel.dataset.postcode);
                cancelbtn.setAttribute('onclick', 'cancelAirboxSubArea(this)');
                airinfoblock.appendChild(cancelbtn);
            } else {
                editbtn.setAttribute('onclick', 'gotoSetupPage(this, \'addAirboxSubArea\')');
                airinfoblock.appendChild(editbtn);
            }
        }
    }).fail(function(jqXhr, text, et) {});
    var cancelbtn = document.createElement('button');
}

function cancelAirboxSubArea(sel) {
    var desc = document.getElementById('subc__desc');

    var detailData = {
        area: sel.value.toString(),
    };
    detailData = JSON.stringify(detailData);
    var payload = {
        'authToken': AUTH_TOKEN,
        'memberId': sel.dataset.mid,
        'datasetId': 'airbox',
        'subscribeDetail': detailData,
        'todo': 'cancelAirboxSubArea'
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
        desc.innerHTML = '<span class="subc__support">已為您取消 【' + getGeocodeName(TAIWAN_POSTWITHGEO_CODE_TPE[sel.dataset.postcode]) + '】 ' + SUBSCRIBE_TYPE['airbox'] + '推播服務!</span>';
        document.getElementById('content').innerHTML = '<button class="sbtn--want" onclick="location.href=\'index.php\'">繼續訂閱其他服務</button>';
        document.getElementById('air__modal').style.display = 'none';
    }).fail(function(jqXhr, text, et) {});
}

function closeAirRst() {
    var modal = document.getElementById('air__modal');
    modal.style.display = 'none';
}
