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
    <script src="/tpelinebot/channelWebs/assets/js/config.js"></script>
    <script src="/tpelinebot/channelWebs/assets/js/APcommon.js"></script>
    <script src="/tpelinebot/channelWebs/assets/js/lib/jquery-2.2.4.min.js"></script>
    <script src="/tpelinebot/channelWebs/assets/js/APsubSetup.js"></script>
    <link rel="stylesheet" href="/tpelinebot/channelWebs/assets/css/air.css">
</head>

<body>
    <div id="wrapper">
        <div id="main">
            <div id="subc__desc"></div>
            <div id="content">
                <div id="setup" style="display: none;">
                    <p>請選擇 1 至 3 個通知時段</p>
                    <div id="time__block">
                        <input type="checkbox" class="aircheck" id="c1" value="0000">
                        <label for="c1" class="isChecked">00:00</label>
                        <input type="checkbox" class="aircheck" id="c2" value="0030">
                        <label for="c2" class="isChecked">00:30</label>
                        <input type="checkbox" class="aircheck" id="c3" value="0100">
                        <label for="c3" class="isChecked">01:00</label>
                        <input type="checkbox" class="aircheck" id="c4" value="0130">
                        <label for="c4" class="isChecked">01:30</label>
                        <input type="checkbox" class="aircheck" id="c5" value="0200">
                        <label for="c5" class="isChecked">02:00</label>
                        <input type="checkbox" class="aircheck" id="c6" value="0230">
                        <label for="c6" class="isChecked">02:30</label>
                        <input type="checkbox" class="aircheck" id="c7" value="0300">
                        <label for="c7" class="isChecked">03:00</label>
                        <input type="checkbox" class="aircheck" id="c8" value="0330">
                        <label for="c8" class="isChecked">03:30</label>
                        <input type="checkbox" class="aircheck" id="c9" value="0400">
                        <label for="c9" class="isChecked">04:00</label>
                        <input type="checkbox" class="aircheck" id="c10" value="0430">
                        <label for="c10" class="isChecked">04:30</label>
                        <input type="checkbox" class="aircheck" id="c11" value="0500">
                        <label for="c11" class="isChecked">05:00</label>
                        <input type="checkbox" class="aircheck" id="c12" value="0530">
                        <label for="c12" class="isChecked">05:30</label>
                        <input type="checkbox" class="aircheck" id="c13" value="0600">
                        <label for="c13" class="isChecked">06:00</label>
                        <input type="checkbox" class="aircheck" id="c14" value="0630">
                        <label for="c14" class="isChecked">06:30</label>
                        <input type="checkbox" class="aircheck" id="c15" value="0700">
                        <label for="c15" class="isChecked">07:00</label>
                        <input type="checkbox" class="aircheck" id="c16" value="0730">
                        <label for="c16" class="isChecked">07:30</label>
                        <input type="checkbox" class="aircheck" id="c17" value="0800">
                        <label for="c17" class="isChecked">08:00</label>
                        <input type="checkbox" class="aircheck" id="c18" value="0830">
                        <label for="c18" class="isChecked">08:30</label>
                        <input type="checkbox" class="aircheck" id="c19" value="0900">
                        <label for="c19" class="isChecked">09:00</label>
                        <input type="checkbox" class="aircheck" id="c20" value="0930">
                        <label for="c20" class="isChecked">09:30</label>
                        <input type="checkbox" class="aircheck" id="c21" value="1000">
                        <label for="c21" class="isChecked">10:00</label>
                        <input type="checkbox" class="aircheck" id="c22" value="1030">
                        <label for="c22" class="isChecked">10:30</label>
                        <input type="checkbox" class="aircheck" id="c23" value="1100">
                        <label for="c23" class="isChecked">11:00</label>
                        <input type="checkbox" class="aircheck" id="c24" value="1130">
                        <label for="c24" class="isChecked">11:30</label>
                        <input type="checkbox" class="aircheck" id="c25" value="1200">
                        <label for="c25" class="isChecked">12:00</label>
                        <input type="checkbox" class="aircheck" id="c26" value="1230">
                        <label for="c26" class="isChecked">12:30</label>
                        <input type="checkbox" class="aircheck" id="c27" value="1300">
                        <label for="c27" class="isChecked">13:00</label>
                        <input type="checkbox" class="aircheck" id="c28" value="1330">
                        <label for="c28" class="isChecked">13:30</label>
                        <input type="checkbox" class="aircheck" id="c29" value="1400">
                        <label for="c29" class="isChecked">14:00</label>
                        <input type="checkbox" class="aircheck" id="c30" value="1430">
                        <label for="c30" class="isChecked">14:30</label>
                        <input type="checkbox" class="aircheck" id="c31" value="1500">
                        <label for="c31" class="isChecked">15:00</label>
                        <input type="checkbox" class="aircheck" id="c32" value="1530">
                        <label for="c32" class="isChecked">15:30</label>
                        <input type="checkbox" class="aircheck" id="c33" value="1600">
                        <label for="c33" class="isChecked">16:00</label>
                        <input type="checkbox" class="aircheck" id="c34" value="1630">
                        <label for="c34" class="isChecked">16:30</label>
                        <input type="checkbox" class="aircheck" id="c35" value="1700">
                        <label for="c35" class="isChecked">17:00</label>
                        <input type="checkbox" class="aircheck" id="c36" value="1730">
                        <label for="c36" class="isChecked">17:30</label>
                        <input type="checkbox" class="aircheck" id="c37" value="1800">
                        <label for="c37" class="isChecked">18:00</label>
                        <input type="checkbox" class="aircheck" id="c38" value="1830">
                        <label for="c38" class="isChecked">18:30</label>
                        <input type="checkbox" class="aircheck" id="c39" value="1900">
                        <label for="c39" class="isChecked">19:00</label>
                        <input type="checkbox" class="aircheck" id="c40" value="1930">
                        <label for="c40" class="isChecked">19:30</label>
                        <input type="checkbox" class="aircheck" id="c41" value="2000">
                        <label for="c41" class="isChecked">20:00</label>
                        <input type="checkbox" class="aircheck" id="c42" value="2030">
                        <label for="c42" class="isChecked">20:30</label>
                        <input type="checkbox" class="aircheck" id="c43" value="2100">
                        <label for="c43" class="isChecked">21:00</label>
                        <input type="checkbox" class="aircheck" id="c44" value="2130">
                        <label for="c44" class="isChecked">21:30</label>
                        <input type="checkbox" class="aircheck" id="c45" value="2200">
                        <label for="c45" class="isChecked">22:00</label>
                        <input type="checkbox" class="aircheck" id="c46" value="2230">
                        <label for="c46" class="isChecked">22:30</label>
                        <input type="checkbox" class="aircheck" id="c47" value="2300">
                        <label for="c47" class="isChecked">23:00</label>
                        <input type="checkbox" class="aircheck" id="c48" value="2330">
                        <label for="c48" class="isChecked">23:30</label>
                    </div>
                    <button id="submitAirboxSubInfo" class="sbtn--want" style="font-weight:bold;font-size:25px;">確認送出</button>
                </div>
                <button class="back" onclick="history.go(-1);"></button>
            </div>
        </div>
    </div>
</body>

</html>
