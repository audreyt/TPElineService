<?php
include __DIR__ . '/../DetectDevice.php';
if ($rst === false):
    exit('請使用行動裝置進入此頁面');
endif;
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=no">
    <script src="https://scdn.line-apps.com/channel/sdk/js/loader_20150909.js"></script>
    <script>
    window.addEventListener('load', function() {
        document.addEventListener("deviceready", function(e) {
            document.addEventListener('touchstart', function(e) {
                e.stopPropagation();
            }, false);
            var options = {
                pageKey: "APsug",
                entryPage: false,
                titleBar: {
                    left: {
                        imgId: "btn_default",
                        text: (navigator.language === 'zh-tw') ? "回首頁" : "Home",
                        visible: true,
                        enable: true,
                    },
                    center: {
                        text: "空氣盒子指標與活動建議",
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
    });
    </script>
    <style>
    html,
    body {
        font-family: "Microsoft JhengHei";
    }
    
    #wrapper {
        background-image: url('../assets/images/backGround1.png');
        background-repeat: no-repeat;
        position: relative;
        background-size: 100% 100%;
        height: 100%;
        padding-top: 0;
        margin: 0;
    }
    
    .suggest--desc>span {
        text-align: center;
    }
    
    .suggest--content {
        border-color: rgba(0, 0, 0, 0.1);
        border-width: 1px;
        border-style: solid;
    }
    
    .active-suggest--header {
        font-weight: 700;
        font-size: 20px;
        line-height: 20px;
    }
    
    .active-suggest--content {
        font-size: 18px;
        line-height: 20px;
    }
    
    .pm25--desc {
        font-size: 25px;
        font-weight: bold;
        display: block;
    }
    
    .pm25--low {
        background-color: #dff0d8;
    }
    
    .pm25--medium {
        background-color: #ffe988;
    }
    
    .pm25--high {
        background-color: #ff8686;
    }
    
    .pm25--veryhigh {
        background-color: #de8bf5;
    }
    
    .back {
        background-image: url('/tpelinebot/channelWebs/assets/images/back_before.png');
        margin-top: 15px;
        background-repeat: no-repeat;
        background-size: 100% 100%;
        background-color: rgba(0, 0, 0, 0);
        border: none;
        display: inline-block;
        min-width: 298px;
        height: 60px;
    }
    
    .back:active,
    .back:focus {
        background-image: url('/tpelinebot/channelWebs/assets/images/back_after.png');
        position: relative;
        left: 2px;
        top: 4px;
    }
    </style>
</head>

<body>
    <div id="wrapper">
        <div class="suggest--desc">
            <span class="pm25--desc pm25--low">低 (<36) </span>
            <div class="suggest--content">
                <header class="active-suggest--header">一般民眾活動建議:</header>
                <p class="active-suggest--content">正常戶外活動。</p>
                <header class="active-suggest--header">敏感性族群活動建議:</header>
                <p class="active-suggest--content">正常戶外活動。</p>
            </div>
        </div>
        <div class="suggest--desc">
            <span class="pm25--desc pm25--medium">中 (36~54)</span>
            <div class="suggest--content">
                <header class="active-suggest--header">一般民眾活動建議:</header>
                <p class="active-suggest--content">正常戶外活動。</p>
                <header class="active-suggest--header">敏感性族群活動建議:</header>
                <p class="active-suggest--content">
                    有心臟、呼吸道及心血管疾病的成人與孩童感受到癥狀時，應考慮減少體力消耗，特別是減少戶外活動。
                </p>
            </div>
        </div>
        <div class="suggest--desc">
            <span class="pm25--desc pm25--high">高 (54~71)</span>
            <div class="suggest--content">
                <header class="active-suggest--header">一般民眾活動建議:</header>
                <p class="active-suggest--content">任何人如果有不適，如眼痛，咳嗽或喉嚨痛等，應該考慮減少戶外活動。</p>
                <header class="active-suggest--header">敏感性族群活動建議:</header>
                <p class="active-suggest--content">
                    1. 有心臟、呼吸道及心血管疾病的成人與孩童，應減少體力消耗，特別是減少戶外活動。
                    <br>2. 老年人應減少體力消耗。
                    <br>3. 具有氣喘的人可能需增加使用吸入劑的頻率。
                </p>
            </div>
        </div>
        <div class="suggest--desc">
            <span class="pm25--desc pm25--veryhigh">非常高 (>71)</span>
            <div class="suggest--content">
                <header class="active-suggest--header">一般民眾活動建議:</header>
                <p class="active-suggest--content">
                    任何人如果有不適，如眼痛，咳嗽或喉嚨痛等，應減少體力消耗，特別是減少戶外活動。
                </p>
                <header class="active-suggest--header">敏感性族群活動建議:</header>
                <p class="active-suggest--content">
                    1. 有心臟、呼吸道及心血管疾病的成人與孩童，以及老年人應避免體力消耗，特別是避免戶外活動。
                    <br>2. 具有氣喘的人可能需增加使用吸入劑的頻率。
                </p>
            </div>
        </div>
    </div>
</body>

</html>
