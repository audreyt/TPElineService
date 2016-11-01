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
    <script src="/tpelinebot/channelWebs/assets/js/config.js"></script>
    <script src="/tpelinebot/channelWebs/assets/js/APcommon.js"></script>
    <script src="/tpelinebot/channelWebs/assets/js/APindex.js"></script>
    <link rel="stylesheet" href="/tpelinebot/channelWebs/assets/css/air.css">
</head>

<body>
    <div id="wrapper">
        <div id="main">
            <div id="subc__desc"><span class="subc__support">按下「行政區」，即可查詢各區域空氣盒子指標。</span></div>
            <div id="content">
                <div id="subc__btnblock">
                    <button class="areaBtn" value="6300500" onclick="showAirboxInfo(this);" data-postcode="100">中正區</button>
                    <button class="areaBtn" value="6300600" onclick="showAirboxInfo(this);" data-postcode="103">大同區</button>
                    <button class="areaBtn" value="6300400" onclick="showAirboxInfo(this);" data-postcode="104">中山區</button>
                    <button class="areaBtn" value="6300100" onclick="showAirboxInfo(this);" data-postcode="105">松山區</button>
                    <button class="areaBtn" value="6300300" onclick="showAirboxInfo(this);" data-postcode="106">大安區</button>
                    <button class="areaBtn" value="6300700" onclick="showAirboxInfo(this);" data-postcode="108">萬華區</button>
                    <button class="areaBtn" value="6300200" onclick="showAirboxInfo(this);" data-postcode="110">信義區</button>
                    <button class="areaBtn" value="6301100" onclick="showAirboxInfo(this);" data-postcode="111">士林區</button>
                    <button class="areaBtn" value="6301200" onclick="showAirboxInfo(this);" data-postcode="112">北投區</button>
                    <button class="areaBtn" value="6301000" onclick="showAirboxInfo(this);" data-postcode="114">內湖區</button>
                    <button class="areaBtn" value="6300900" onclick="showAirboxInfo(this);" data-postcode="115">南港區</button>
                    <button class="areaBtn" value="6300800" onclick="showAirboxInfo(this);" data-postcode="116">文山區</button>
                </div>
            </div>
        </div>
    </div>
    <div id="air__modal" class="modal">
        <span class="modal__close">關閉</span>
        <div id="air__info" style="clear: both;"></div>
    </div>
</body>

</html>
