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
    <script src="/tpelinebot/channelWebs/assets/js/lib/jquery-2.2.4.min.js"></script>
    <script src="/tpelinebot/channelWebs/assets/js/fCindex.js"></script>
    <style>
        html,
        body {
            height: 100%;
            text-align: center;
            font-family: "Microsoft JhengHei";
            padding: 0;
            margin: 0;
        }
        .mgts {
            background-image: url('../assets/images/index_before.png');
            background-repeat: no-repeat;
            background-size: 100% 100%;
            background-color: rgba(0 ,0 ,0 , 0);
            border: none;
            display: inline-block;
            font-size: 20px;
            font-weight: 600;
            width: 260px;
            height: 60px;
            margin-bottom: 10px;
            font-family: inherit;
        }

        .mgts:active {
            background-image: url('../assets/images/index_after.png');
            position: relative;
            left: 2px;
            top: 4px;
        }

        #wrapper {
            background-image: url('../assets/images/bg01.png');
            background-repeat: no-repeat;
            position: relative;
            background-size: 100% 100%;
            min-height: 100%;
            padding-top: 0;
            margin: 0;
        }
    </style>
</head>

<body>
    <div id="wrapper">
        <div style="background-color: #F6AD3A; margin-bottom: 20px; max-width: 100%">
            <img src="/tpelinebot/channelWebs/assets/images/index_welcome.png" style="max-width: 100%;">
        </div>
        <div id="main">
            <button class="mgts" value="eoc_disaster" id="eoc_disaster">災情資訊</button>
            <button class="mgts" value="ncdr_workschoolclose" id="ncdr_workschoolclose">停班停課</button>
            <button class="mgts" value="ncdr_flood" id="ncdr_flood">淹水警示</button>
            <button class="mgts" value="ncdr_watergate" id="ncdr_watergate">水閘門啟閉</button>
            <button class="mgts" value="ncdr_parking" id="ncdr_parking">紅黃線停車</button>
        </div>
    </div>
</body>

</html>
