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
    <script src="https://scdn.line-apps.com/channel/sdk/js/loader_20150909.js"></script>
    <script defer async src="https://maps.google.com/maps/api/js?key={YOUR_GMAP_JS_API_KEY}&signed_in=false"></script>
    <script src="/tpelinebot/channelWebs/assets/js/config.js"></script>
    <script src="/tpelinebot/channelWebs/assets/js/common.js"></script>
    <script src="/tpelinebot/channelWebs/assets/js/lib/jquery-2.2.4.min.js"></script>
</body>
<script>
    window.addEventListener('load', function(e) {
        document.addEventListener("deviceready", function(e) {
            var options = {
                pageKey: "NCDRParking",
                entryPage: false,
                titleBar: {
                    left: {
                        imgId: "btn_default",
                        text: "",
                        visible: false,
                        enable: false,
                    },
                    center: {
                        text: "紅黃線停車範圍資訊",
                        clickable: false
                    },
                }
            };
            LCS.Interface.updateTitleBar(options);
            getLatLng('ncdr_parking');
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
            var selectMap = document.getElementById('getMap');
            selectMap.setAttribute('onchange', 'drawMap(this)');
            if (data['result'] === false) {
                alert('尚無資料');
                return;
            }
            var jsonData = JSON.parse(data['data']);
            var infoData = JSON.parse(jsonData[0]['info_to_show']);
            if(typeof infoData['result']['areaDetail'] === 'undefined'){
                document.body.innerHTML = '';
                alert('全區域紅黃線開放停車');
                return;
            }
            infoData['result']['areaDetail'].forEach(function(ele, idx) {
                var op = document.createElement('option');
                op.text = ele['areaName'];
                op.value = JSON.stringify(ele['polygon']);
                selectMap.appendChild(op);
            });
        }).fail(function(jqXhr, text, et) {
            console.error(et);
        });
    }

    function drawMap(sel) {
        if (sel.options[sel.options.selectedIndex].value === '') {
            document.getElementById('map').innerHTML = '';
            document.getElementById('map').style.height = 0 + 'px';
        } else {
            var h = (window.innerHeight > 0) ? window.innerHeight : screen.height;
            document.getElementById('map').style.height = h - 100 + 'px';
            var latlng = sel.options[sel.options.selectedIndex].value;
            var mapCenter = getMapCenter(latlng);
            var map = new google.maps.Map(document.getElementById('map'), {
                zoom: 14,
                center: mapCenter,
                mapTypeId: google.maps.MapTypeId.ROADMAP,
                mapTypeControl: false,
                scrollwheel: false,
                draggable: true,
                scaleControl: false,
                zoomControl: false,
                streetViewControl: false
            });
            var polygonCoords = reformatLatLng(latlng);
            var polygon = new google.maps.Polygon({
                paths: polygonCoords,
                strokeColor: '#F11',
                strokeOpacity: 0.5,
                strokeWeight: 2,
                fillColor: '#FAC',
                fillOpacity: 0.6
            });

            polygon.setMap(map);
        }
    }

    function reformatLatLng(latlng) {
        latlng = JSON.parse(latlng);
        var rst = [];
        for (var i = 0; i < latlng.length; i++) {
            var newlatlng = latlng[i].split(',');
            rst.push({
                'lat': parseFloat(newlatlng[0]),
                'lng': parseFloat(newlatlng[1])
            });
        }

        return rst;
    }

    function getMapCenter(latlng) {
        latlng = JSON.parse(latlng);
        var latC = [];
        var lngC = [];

        var rst = {};
        for (var i = 0; i < latlng.length; i++) {
            var newlatlng = latlng[i].split(',');
            latC.push(newlatlng[0]);
            lngC.push(newlatlng[1]);
        }
        var lat = getMm(latC),
        lng = getMm(lngC);

        rst['lat'] = lat['min'] + ((lat['max'] - lat['min']) / 2);
        rst['lng'] = lng['min'] + ((lng['max'] - lng['min']) / 2);
        return rst;
    }

    function getMm(ary) {
        var rst = {
            'max': Math.max.apply(null, ary),
            'min': Math.min.apply(null, ary)
        };
        return rst;
    }
</script>
<style>
    html,
    body {
        height: 100%;
    }
    
    #map {
        width: 100%;
        min-height: 300px;
    }
    
    #getMap {
        width: 90%;
        margin-bottom: 10px;
    }
    
    #wrapper {
        background-image: url('../assets/images/bg01.png');
        background-repeat: no-repeat;
        position: relative;
        background-size: 100% 100%;
        height: 100%;
        padding-top: 0;
        margin: 0;
    }
</style>
</head>

<body>
    <div id="wrapper">
        <div style="text-align: center;">
            <select id="getMap">
                <option value="">請選擇</option>
            </select>
        </div>
        <div>
            <span>【影響範圍地圖】</span>
        </div>
        <div id="map"></div>
    </div>
</body>

</html>
