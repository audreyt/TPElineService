window.addEventListener("load", function(e) {
    document.addEventListener("deviceready", function(e) {
        document.addEventListener('touchstart', function(e) { e.stopPropagation(); }, false);
        var options = {
            pageKey: "SL",
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
            infoaction = document.getElementById('subc__infoaction');
        var payload = {
            'memberId': getURIQueryString('mid'),
            'datasetId': getURIQueryString('did'),
            'authToken': AUTH_TOKEN,
            'st': new Date().getTime()
        };
        var isSubscribed = API_HOST + SRU + '/listSubscriptionContainer/';
        $.ajax({
            url: isSubscribed,
            data: payload,
            dataType: 'json',
            method: 'GET',
            async: true
        }).done(function(data) {
            if (data['result'] === true) {
                desc.innerHTML = '<span class="subc__support">如您要取消訂閱' + SUBSCRIBE_TYPE[payload['datasetId']] + '資訊，請按下「我要取消訂閱」。</span>';
                infoaction.innerHTML = '<button class="sbtn--cancel" data-mid="' + payload['memberId'] + '" value="' + payload['datasetId'] + '" onclick="deleteSub(this);">我要取消訂閱</button>';
                return;
            } else {
                desc.innerHTML = '<span class="subc__support">按下「我要訂閱' + SUBSCRIBE_TYPE[payload['datasetId']] + '資訊」，當有' + SUBSCRIBE_TYPE[payload['datasetId']] + '資訊時，我們將會用LINE訊息通知您。</span>';
                infoaction.innerHTML = '<button class="sbtn--want" data-mid="' + payload['memberId'] + '" value="' + payload['datasetId'] + '" onclick="addSub(this);">我要訂閱' + SUBSCRIBE_TYPE[payload['datasetId']] + '資訊</button>';
                return;
            }
        }).fail(function(jqXhr, text, et) {

        });
    }, false);
});
