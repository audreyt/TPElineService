<?php
require_once __DIR__ . '/../config/Global.config.php';
require_once ROOT_PATH . '/config/Script.config.php';
require_once ROOT_PATH . '/common/Debug.trait.php';
require_once ROOT_PATH . '/script/AirboxPusher.class.php';

global $taiwanGeocodeTpe;
$obj = new AirboxPusher;
$data = $obj->getAirboxDataToPush();
$pushMemberList = $obj->getAirboxPushableMemberList();
if ($data && !empty($pushMemberList)) {
    $obj->pushData();
}
