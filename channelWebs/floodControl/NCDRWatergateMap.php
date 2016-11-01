<?php
include __DIR__ . '/../DetectDevice.php';
if ($rst === false):
    exit('請使用行動裝置進入此頁面');
endif;
?>
<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="UTF-8">
    <title></title>
    <script defer async src="https://maps.google.com/maps/api/js?key={YOUR_GMAP_JS_API_KEY}&signed_in=false"></script>
    <script src="https://scdn.line-apps.com/channel/sdk/js/loader_20150909.js"></script>
    <script src="/tpelinebot/channelWebs/assets/js/config.js"></script>
    <script src="/tpelinebot/channelWebs/assets/js/common.js"></script>
    <script src="/tpelinebot/channelWebs/assets/js/lib/jquery-2.2.4.min.js"></script>
</body>
<script>
    window.addEventListener('load', function(e) {
        document.addEventListener("deviceready", function(e) {
            var options = {
                pageKey: "NCDRWatergate",
                entryPage: false,
                titleBar: {
                    left: {
                        imgId: "btn_default",
                        text: "",
                        visible: false,
                        enable: false,
                    },
                    center: {
                        text: "水閘門啟閉影響範圍資訊",
                        clickable: false
                    },
                }
            };
            LCS.Interface.updateTitleBar(options);
            LCS.Interface.registerTitleBarCallback(function(evt) {
                switch (evt.target) {
                    case "LBUTTON":
                    break;
                    case "RBUTTON":
                    break;
                    case "BACK":
                    window.history.back();
                    break;
                    case "TITLE":
                    break;
                }
            });
            getLatLng('ncdr_watergate');
        }, false);
    });

    function getLatLng(did) {
        var payload = {
            'datasetId': did,
            'areaCode': '',
            'authToken': AUTH_TOKEN,
            'st': new Date().getTime(),
        };
        var api = API_HOST + SRU + '/listPDatasetInfoToShow/';
        $.ajax({
            url: api,
            data: payload,
            dataType: 'json',
            method: 'GET',
            async: false
        }).done(function(data) {
            if (data['result'] === false) {
                alert('尚無資料');
                return;
            }
            var jsonData = JSON.parse(data['data']);
            var infoData = JSON.parse(jsonData[0]['info_to_show']);
            drawMap(infoData['result']['areaDetail']);
        }).fail(function(jqXhr, text, et) {
            console.error(et);
        });
    }

    function drawMap(data) {
        var h = (window.innerHeight > 0) ? window.innerHeight : screen.height;
        document.getElementById('map').style.height = h - 200 + 'px';
        var map = new google.maps.Map(document.getElementById('map'), {
            zoom: 13,
            center: {
                lat: 25.0302624,
                lng: 121.508669
            },
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            mapTypeControl: false,
            scrollwheel: false,
            draggable: true,
            scaleControl: false,
            zoomControl: false,
            streetViewControl: false
        });
        data.forEach(function(ele, idx) {
            var effectCircle = new google.maps.Circle({
                strokeColor: '#FF0000',
                strokeOpacity: 0.8,
                strokeWeight: 2,
                fillColor: '#FF0000',
                fillOpacity: 0.35,
                map: map,
                center: {
                    lat: ele['circle']['center']['lat'],
                    lng: ele['circle']['center']['lng']
                },
                radius: Math.sqrt(ele['circle']['radius']) * 500
            });
        })
    }
</script>
<style>
    html,
    body {
        height: 100%;
    }
    
    #map {
        width: 100%;
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
        <div>
            <span>【影響範圍地圖】</span>
        </div>
        <div id="map"></div>
    </div>
</body>

</html>
