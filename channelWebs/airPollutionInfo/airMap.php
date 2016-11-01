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
    <script src="/tpelinebot/channelWebs/assets/js/APcommon.js"></script>
    <script src="/tpelinebot/channelWebs/assets/js/lib/jquery-2.2.4.min.js"></script>
    <link rel="stylesheet" type="text/css" href="/tpelinebot/channelWebs/assets/css/air.css">

    <script>
    window.addEventListener('load', function(e) {
        document.addEventListener('deviceready', function(e) {
            document.addEventListener('touchstart', function(e) { e.stopPropagation(); }, false);
            var options = {
                pageKey: "APMap",
                entryPage: false,
                titleBar: {
                    left: {
                        imgId: "btn_default",
                        text: (navigator.language === 'zh-tw') ? "回首頁" : "Home",
                        visible: true,
                        enable: true,
                    },
                    center: {
                        text: '【'+getGeocodeName(TAIWAN_POSTWITHGEO_CODE_TPE[getURIQueryString('ptc')]) + '】空氣盒子地圖',
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
        var distCenter = {
            '6300100': {
                'lat': 25.0598266,
                'lng': 121.5417977
            },
            '6300200': {
                'lat': 25.0287521,
                'lng': 121.5548065
            },
            '6300300': {
                'lat': 25.0263453,
                'lng': 121.5263363
            },
            '6300400': {
                'lat': 25.0685406,
                'lng': 121.5280918
            },
            '6300500': {
                'lat': 25.0293386,
                'lng': 121.5030736
            },
            '6300600': {
                'lat': 25.0645219,
                'lng': 121.5046295
            },
            '6300700': {
                'lat': 25.0295301,
                'lng': 121.4803742
            },
            '6300800': {
                'lat': 24.9880809,
                'lng': 121.5402521
            },
            '6300900': {
                'lat': 25.0381881,
                'lng': 121.5869685
            },
            '6301000': {
                'lat': 25.0837532,
                'lng': 121.5553559
            },
            '6301100': {
                'lat': 25.134982,
                'lng': 121.4661992
            },
            '6301200': {
                'lat': 25.1535036,
                'lng': 121.4833168
            }
        };
        getLatLng('airbox', distCenter);
    });

    function getLatLng(did, distCenter) {
        var ac = getGeocode(TAIWAN_POSTWITHGEO_CODE_TPE[getURIQueryString('ptc')]);
        var payload = {
            'datasetId': did,
            'areaCode': ac,
            'authToken': AUTH_TOKEN,
            'st': new Date().getTime(),
        };
        var api = API_HOST + SRU + '/listDatasetInfoToShow/';
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
            var boxCircle = [];
            var i = 0;
            infoData['result'].forEach(function(ele, idx) {
                if (typeof ele['pm25'] === 'number') {
                    boxCircle.push({
                        name: ele['deviceName'],
                        center: ele['gps'],
                        pm25: ele['pm25'],
                        color: pm25Color(ele['pm25'])
                    });
                }
            });
            drawMap(distCenter[ac], boxCircle, 1.5);
        }).fail(function(jqXhr, text, et) {
            console.error(et);
        });
    }

    function drawMap(mapCenter, box, radius) {
        var h = (window.innerHeight > 0) ? window.innerHeight : screen.height;
        document.getElementById('map').style.height = h - 100 + 'px';
        var map = new google.maps.Map(document.getElementById('map'), {
            zoom: 13,
            center: mapCenter,
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            mapTypeControl: false,
            scrollwheel: false,
            draggable: true,
            scaleControl: false,
            zoomControl: false,
            streetViewControl: false
        });
        var school = new google.maps.InfoWindow;
        box.forEach(function(ele, idx) {
            var effectCircle = new google.maps.Circle({
                strokeColor: '#000',
                strokeOpacity: 1.0,
                strokeWeight: 2,
                fillColor: ele['color'],
                fillOpacity: 0.5,
                map: map,
                center: {
                    lat: ele['center']['lat'],
                    lng: ele['center']['lng']
                },
                radius: Math.sqrt(radius) * 200
            });
            var schoolDetail = ele['name'] + '<br>PM2.5濃度' + ele['pm25'] + '(' + pm25Str(ele['pm25']) + ')';
            google.maps.event.addListener(effectCircle, 'click', function(e) {
                school.setPosition({
                    lat: ele['center']['lat'],
                    lng: ele['center']['lng']
                });
                school.setContent(schoolDetail);
                map.setCenter({
                    lat: ele['center']['lat'],
                    lng: ele['center']['lng']
                });
                school.open(map);
            });
        });
    }

    function pm25Color(pm25) {
        if (pm25 < 36) {
            return '#6ffb35';
        } else if (pm25 >= 36 && pm25 < 54) {
            return '#f9fd04';
        } else if (pm25 >= 54 && pm25 < 71) {
            return '#ff8686';
        } else {
            return '#de8bf5';
        }
    }
    function pm25Str(pm25) {
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
    </script>
    <style>
    #map {
        width: 100%;
        min-height: 300px;
    }
    </style>
</head>

<body>
    <div id="wrapper">
        <div id="map"></div>
    </div>
</body>

</html>
