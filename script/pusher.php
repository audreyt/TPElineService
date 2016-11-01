<?php
require_once __DIR__ . '/../config/Global.config.php';
require_once ROOT_PATH . '/common/Debug.trait.php';
require_once ROOT_PATH . '/script/Pusher.class.php';

pushNCDRFloodData();
pushNCDRWSCData();
pushNCDRParkingData();
pushNCDRWatergateData();
function pushNCDRFloodData()
{
    $obj = new NCDRFloodPusher;
    $data = $obj->getDataToPush();
    $pushMemberList = $obj->getPushableMemberList();
    $detail = [];
    if ($data && !empty($pushMemberList)) {
        foreach ($pushMemberList as $k => $memberInfo) {
            $detail[$k]['mid'] = $memberInfo['mid'];
            $detail[$k]['detail'] = json_decode($memberInfo['detail'], true);
        }
        foreach ($data as $info) {
            $sendToList = [];
            foreach ($detail as $k => $v) {
                if (in_array($info['area_code'], $v['detail']['area'])) {
                    $sendToList[] = $v['mid'];
                }
            }
            if (!empty($sendToList)) {
                $obj->pushData($sendToList, $info['area_code'], 'ncdr');
            }
        }
    }
}

function pushNCDRWSCData()
{
    $obj = new NCDRWSCPusher;
    $data = $obj->getDataToPush();
    $pushMemberList = $obj->getPushableMemberList();

    if ($data && !empty($pushMemberList)) {
        $memberLen = count($pushMemberList);
        for ($i = 0; $i < $memberLen; $i++) {
            $sendToList[] = $pushMemberList[$i]['mid'];
        }
        if (!empty($sendToList)) {
            $obj->pushData($sendToList, '', 'ncdr');
        }
    }
}

function pushNCDRParkingData()
{
    $obj = new NCDRParkingPusher;
    $data = $obj->getDataToPush();
    $pushMemberList = $obj->getPushableMemberList();
    if ($data && !empty($pushMemberList)) {
        $memberLen = count($pushMemberList);
        for ($i = 0; $i < $memberLen; $i++) {
            $sendToList[] = $pushMemberList[$i]['mid'];
        }
        if (!empty($sendToList)) {
            $obj->pushData($sendToList, '', 'ncdr');
        }
    }
}

function pushNCDRWatergateData()
{
    $obj = new NCDRWatergatePusher;
    $data = $obj->getDataToPush();
    $pushMemberList = $obj->getPushableMemberList();
    if ($data && !empty($pushMemberList)) {
        $memberLen = count($pushMemberList);
        for ($i = 0; $i < $memberLen; $i++) {
            $sendToList[] = $pushMemberList[$i]['mid'];
        }
        if (!empty($sendToList)) {
            $obj->pushData($sendToList, '', 'ncdr');
        }
    }
}
