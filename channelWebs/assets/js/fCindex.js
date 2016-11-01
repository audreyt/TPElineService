window.addEventListener("load", function(e) {
    document.addEventListener("deviceready", function(e) {
        document.addEventListener('touchstart', function(e) { e.stopPropagation(); }, false);
        LCS.Interface.getProfile(function(data) {
            var mid = data.id;
            var options = {
                pageKey: "index",
                entryPage: true,
                titleBar: {
                    left: {
                        imgId: "btn_default",
                        text: (navigator.language === 'zh-tw') ? "回首頁" : "Home",
                        visible: false,
                        enable: false,
                    },
                    center: {
                        text: "防汛資訊訂閱平台",
                        clickable: false
                    },
                }
            };
            LCS.Interface.updateTitleBar(options);
            // register button's action
            var eocd = document.getElementById('eoc_disaster'),
                ncdrwsc = document.getElementById('ncdr_workschoolclose'),
                ncdrf = document.getElementById('ncdr_flood'),
                ncdrwg = document.getElementById('ncdr_watergate'),
                ncdrp = document.getElementById('ncdr_parking');
            eocd.memberId = ncdrwsc.memberId = ncdrp.memberId = ncdrwg.memberId = ncdrf.memberId = mid;

            eocd.addEventListener('click', gotoEOC, false);
            ncdrwsc.addEventListener('click', gotoNCDRSubList, false);
            ncdrf.addEventListener('click', gotoNCDRFlood, false);
            ncdrwg.addEventListener('click', gotoNCDRSubList, false);
            ncdrp.addEventListener('click', gotoNCDRSubList, false);
        }, function() {
            alert('not validate member');
        });

        function gotoNCDRFlood(sel) {
            location.href = 'NCDRFlood.php?mid=' + sel.currentTarget.memberId + '&did=' + sel.currentTarget.value;
        }

        function gotoEOC(sel) {
            $.ajax({
                url: './GetCenterControl.php',
                method: 'GET',
                dataType: 'json'
            }).done(function(data) {
                if (data['isCenterOpen'] === false) {
                    alert('僅應變中心開設時段提供查詢');
                    return;
                }
                location.href = 'EOC.php?mid=' + sel.target.memberId + '&did=' + sel.target.value;
            }).fail(function(jqXhr, text, et) {});
        }

        function gotoNCDRSubList(sel) {
            location.href = 'NCDRSubLists.php?mid=' + sel.currentTarget.memberId + '&did=' + sel.currentTarget.value;
        }

        function notAvailableYet() {
            alert('即將開放!');
            return;
        }
    }, false);
});
