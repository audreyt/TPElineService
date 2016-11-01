<?php
require_once ROOT_PATH . '/common/DbAccess.class.php';
require_once ROOT_PATH . '/config/Line.config.php';
include_once ROOT_PATH . '/common/Common.php';

class AirboxPusher
{
    use TDebugLog;
    /**
     * @var mixed
     */
    private $purpleData, $airboxData, $dataToPush, $currentTimestamp;

    /**
     * @var mixed
     */
    public $origData, $pushableMemberList;

    public function __construct()
    {
        $this->currentTimestamp = time();
        $this->dbObj = new PdoDatabase(DB_NAME);
    }

    /**
     * @return mixed
     */
    public function getAirboxDataToPush()
    {
        $jsonData = $this->airboxData = $this->purpleData = [];
        $query = "SELECT * FROM `dataset_to_push` WHERE id = 'airbox'";
        $this->dbObj->prepareQuery($query);
        $rst = $this->dbObj->getQuery();
        // 12 district
        for ($i = 0; $i < 12; $i++) {
            $jsonData[$i] = json_decode($rst[$i]['info_to_show'], true);
            foreach ($jsonData[$i]['result'] as $key => $device) {
                if (!isset($device['pm25'])) {
                    unset($jsonData[$i]['result'][$key]);
                }
            }
            $jsonData[$i]['result'] = array_values($jsonData[$i]['result']);
            $this->airboxData['result'][$rst[$i]['area_code']] = $jsonData[$i]['result'];
        }
        foreach ($this->airboxData['result'] as $k => $abd) {
            if (is_null($abd) || empty($abd)) {
                continue;
            }
            $j = 0;
            foreach ($abd as $airboxDetail) {
                if ($airboxDetail['pm25'] > 71) {
                    $this->purpleData['result'][$k][$j] = $airboxDetail;
                    $j++;
                }
            }
        }
        if (!empty($this->purpleData['result'])) {
            $this->origData = $this->purpleData;
            $this->origData['purple'] = true;
        } else {
            $this->origData = $this->airboxData;
            $this->origData['purple'] = false;
        }
        if (!empty($this->origData)) {
            // return only those with pm25
            return $this->origData;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getAirboxPushableMemberList()
    {
        $currentTime = date('Hi', $this->currentTimestamp);
        $currentTime = '1230';
        $this->pushableMemberList = $rst = $detail = [];
        $query = "SELECT * FROM `subscription_container`
                  WHERE `is_pushed` = 0
                  AND `dataset_id` = 'airbox'
                  AND `detail` LIKE :detail";
        if ($this->origData['purple']) {
            // purple
            // get lasted_pushed_at > 1 hour to push
            $query .= " AND (" . $this->currentTimestamp . " - IF(UNIX_TIMESTAMP(`last_pushed_at`) IS NULL, UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 2 HOUR)), UNIX_TIMESTAMP(`last_pushed_at`))) /60 /60 > 1;";
            $this->dbObj->prepareQuery($query);
            foreach ($this->origData['result'] as $area => $airInfo) {
                $this->dbObj->bindSingleParam(':detail', '%' . $area . '%');
                $rst[] = $this->dbObj->getQuery();
            }
        } else {
            // time
            $this->dbObj->prepareQuery($query);
            foreach ($this->origData['result'] as $area => $airInfo) {
                $this->dbObj->bindSingleParam(':detail', '%' . $currentTime . '%');
                $rst[] = $this->dbObj->getQuery();
            }
        }
        foreach ($rst as $sdetail) {
            foreach ($sdetail as $info) {
                // use for detect
                $this->pushableMemberList[] = $info['mid'];
                // save to pushConfig
                $detail[$info['mid']] = json_decode($info['detail'], true);
            }
        }
        // setup data to push
        $currentTime = ($this->origData['purple']) ? '' : $currentTime;
        $this->setupDataToPush($this->origData, $detail, $currentTime);
        $this->pushableMemberList = array_unique($this->pushableMemberList);
        return $this->pushableMemberList;
    }
    /**
     * @param $sendto
     * @param $areaCode
     */
    public function pushData()
    {
        $msg = $this->dataToPush;
        $midChunk = $midList = [];
        foreach ($msg['result'] as $k => $v) {
            $midList[$k] = $this->setSendTo($v['mids']);
        }
        foreach ($midList as $k => $v) {
            $midList[$k]['midChunk'] = count($v);
        }

        $results = $this->sendMessage($msg, $midList);
        foreach ($results as $rst) {
            $isSuccess = json_decode($rst['result'], true);
            if (isset($isSuccess['failed']) && empty($isSuccess['failed'])) {
                $membersToModify = $this->formatSendToListToDB($rst['midList']);
                switch ($msg['purple']) {
                    case true:
                        $this->changeLastPushedTime($membersToModify);
                        break;
                    default:
                        // do nothing
                        break;
                }
            } else {
                $this->setDebugInfo(ROOT_PATH . '/logs/' . 'pusher.airbox.log', json_encode($rst));
                $this->saveDebugInfo();
            }
        }
    }
    /**
     * 建立推播項目與被推播者的關係
     * @param $data
     * @param $memberList
     */
    private function setupDataToPush($data, $memberInfo, $timeToPush)
    {
        $data = $this->origData;
        foreach ($data['result'] as $area => $info) {
            $data['result'][$area]['mids'] = [];
            foreach ($memberInfo as $mid => $minfo) {
                for ($i = 0; $i < count($minfo); $i++) {
                    if ($minfo[$i]['area'] === (string) $area) {
                        switch (empty($timeToPush)) {
                            case true:
                                array_push($data['result'][$area]['mids'], $mid);
                                break;
                            default:
                                if (strpos($minfo[$i]['timeToPush'], $timeToPush) > -1) {
                                    array_push($data['result'][$area]['mids'], $mid);
                                }
                                break;
                        }
                    }
                }
            }
        }
        $this->dataToPush = $data;
    }
    /**
     * @param array $memberList
     */
    private function setSendTo(array $memberList)
    {
        return array_chunk($memberList, 150);
    }
    /**
     * @param $msg
     * @return mixed
     */
    private function sendMessage($msg, $midInfo)
    {
        global $lineApi, $lineConst;
        // $message = '';
        foreach ($msg['result'] as $k => $v) {
            unset($msg['result'][$k]['mids']);
        }
        $rst = [];
        $j = 0;
        foreach ($midInfo as $area => $midDetail) {
            for ($i = 0; $i < $midDetail['midChunk']; $i++) {
                $message = '空氣盒子資訊:' . PHP_EOL;
                foreach ($msg['result'] as $key => $info) {
                    if ($area === $key && isset($info[0]['deviceDist'])) {
                        $message .= '【' . $info[0]['deviceDist'] . '】' . PHP_EOL . '各監測點空氣盒子情形如下:';
                        foreach ($info as $airInfo) {
                            if ($airInfo['pm25'] < 71) {
                                $message .= PHP_EOL . $airInfo['deviceName'] . 'PM2.5濃度: ' . $airInfo['pm25'] . '(' . $this->pm25toStr($airInfo['pm25']) . ')';
                            } else {
                                $message .= PHP_EOL . $airInfo['deviceName'] . 'PM2.5濃度: ' . $airInfo['pm25'] . '(已達紫爆等級，建議民眾不要於該區域逗留)';
                            }
                        }
                    }
                }
                if (strlen($message) > 21) {
                    $message .= PHP_EOL . '(此為自動推播訊息)';
                    $rst[$j]['result'] = messagesFromBot(
                        $lineApi['sendMessage']['BC'],
                        $midDetail[$i],
                        [
                            'contentType' => $lineConst['contentType']['Text'],
                            'toType' => $lineConst['toType']['User'],
                            'text' => $message,
                        ],
                        [
                            'toChannel' => $lineConst['toChannel']['Message'],
                            'eventType' => $lineConst['eventType']['OutgoingMessage'],
                        ]
                    );
                    $rst[$j]['midList'] = $midDetail[$i];
                    $j++;
                }
            }
        }
        return $rst;
    }

    /**
     * @param $pm25
     */
    private function pm25toStr($pm25)
    {
        if ($pm25 < 36) {
            return '低';
        } else if ($pm25 >= 36 && $pm25 < 54) {
            return '中';
        } else if ($pm25 >= 54 && $pm25 < 71) {
            return '高';
        } else {
            return '非常高';
        }
    }
    /**
     * format member list to do IN statement update
     */
    private function formatSendToListToDB(array $midList)
    {
        $memberStr = '';
        $i = 0;
        foreach ($midList as $mid) {
            $memberStr .= "'" . $mid . "',";
        }
        $memberStr = substr($memberStr, 0, -1);
        return $memberStr;
    }
    /**
     * change last pushed time, for purple explosive
     */
    private function changeLastPushedTime($mid)
    {
        $query = "UPDATE `subscription_container`
                  SET `last_pushed_at` = NOW()
                  WHERE `mid` IN (" . $mid . ")
                  AND `dataset_id` = 'airbox';";
        $this->dbObj->prepareQuery($query);
        $this->dbObj->doQuery();
    }
}
