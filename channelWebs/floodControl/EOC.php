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
    <script src="/tpelinebot/channelWebs/assets/js/common.js"></script>
    <script src="/tpelinebot/channelWebs/assets/js/EOC.js"></script>
    <link rel="stylesheet" type="text/css" href="/tpelinebot/channelWebs/assets/css/all.css">
</head>

<body>
    <div id="wrapper">
        <div id="main">
            <div id="subc__desc"></div>
            <div id="subc__btnblock"></div>
            <div id="eoc__modal" class="modal">
                <span class="modal__close">關閉</span>
                <div id="subc__infoaction" style="clear: both;"></div>
            </div>
        </div>
    </div>
</body>
</html>
