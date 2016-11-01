<?php
require_once ROOT_PATH . '/common/DbAccess.class.php';
require_once ROOT_PATH . '/config/Line.config.php';
include_once ROOT_PATH . '/common/Common.php';

abstract class Pusher
{
    use TDebugLog;
    /**
     * @var mixed
     */
    protected $dbObj, $currentTimestamp, $memberId, $datasetId, $sendTo;
    /**
     * @var mixed
     */
    public $dataToPush, $pushableMemberList;
    /**
     * @param $msg
     */
    abstract protected function sendMessage($msg);

    public function __construct()
    {
        $this->currentTimestamp = time();
        $this->dbObj = new PdoDatabase(DB_NAME);
    }
    /**
     * push data with detail config
     * @param $detail
     */
    public function pushData($sendto, $detail, $dataSource)
    {
        switch (strtoupper($dataSource)) {
            case 'NCDR':
                $this->pushNCDRData($sendto, $detail);
                break;
            default:
                $this->setDebugInfo(ROOT_PATH . '/logs/pusher.dataSourceError.log', 'no such kind of datasource: [' . strtoupper($dataSource) . ']');
                $this->saveDebugInfo();
                break;
        }
    }
    /**
     * @param $sendto
     * @param $areaCode
     */
    protected function pushNCDRData($sendto, $areaCode)
    {
        $query = "SELECT `dtp`.`info_to_show`
                  FROM `dataset_to_push` AS `dtp`
                  INNER JOIN `subscription_container` AS `sc` ON `sc`.`dataset_id` = `dtp`.`id`
                  WHERE `dtp`.`area_code` = :ac
                  AND `sc`.`dataset_id` = :did";
        $this->dbObj->prepareQuery($query);
        $this->dbObj->bindSingleParam(':did', $this->datasetId);
        $this->dbObj->bindSingleParam(':ac', $areaCode);
        $msg = $this->dbObj->getQuery();
        $content = json_decode($msg[0]['info_to_show'], true);
        // 因主要內容是description的傳送
        if (strlen($content['result']['description']) > 8192) {
            $this->setDebugInfo(ROOT_PATH . '/logs/' . $this->datasetId . 'pusher.log', 'exceed maximun length of lineAPI');
            $this->saveDebugInfo();
            return false;
        } else {
            // 100 is acceptable
            $maximumToChunk = (floor((8192 - strlen($content['result']['description'])) / 33) <= 150) ? floor((8192 - strlen($msg[0]['info_to_show'])) / 33) : 100;
            // mid len = 33, maximun len of post = 8192
            $this->setSendTo($sendto, $maximumToChunk);
        }

        $memberChunk = count($this->sendTo);
        if (!empty($msg)) {
            $results = $this->sendMessage($msg);
            foreach ($results as $rst) {
                $rst = json_decode($rst, true);
                if (isset($rst['failed']) && empty($rst['failed'])) {
                    for ($i = 0; $i < $memberChunk; $i++) {
                        $membersToModify = $this->formatSendToListToDB($this->sendTo[$i]);
                        $this->changeIsPushed($membersToModify, $this->datasetId);
                    }
                } else {
                    $this->setDebugInfo(ROOT_PATH . '/logs/' . $this->datasetId . '.pusher.log', $rst);
                    $this->saveDebugInfo();
                }
            }
            return true;
        }
        return false;
    }
    /**
     * @return mixed
     */
    public function getDataToPush()
    {
        // data updated in past 1 hour
        $query = "SELECT * FROM `dataset_to_push` WHERE id = '" . $this->datasetId . "' AND (" . $this->currentTimestamp . " - UNIX_TIMESTAMP(`changed_at`)) <= 60 ;";
        $this->dbObj->prepareQuery($query);
        $this->dataToPush = $this->dbObj->getQuery();
        if (!empty($this->dataToPush)) {
            return $this->dataToPush;
        }
        return false;
    }
    /**
     * @param $mid
     * @param $did
     */
    protected function changeIsPushed($mid, $did)
    {
        $query = "UPDATE `subscription_container`
                  SET `is_pushed` = 1,
                      `last_pushed_at` = NOW(),
                      `changed_at` = NOW()
                  WHERE `mid` IN (" . $mid . ")
                  AND `dataset_id` = :did;";
        $this->dbObj->prepareQuery($query);
        $this->dbObj->bindSingleParam(':did', $did);
        $this->dbObj->doQuery();
    }

    /**
     * @param array $memberAry
     */
    protected function setSendTo(array $memberAry, $numToChunk)
    {
        $this->sendTo = array_chunk($memberAry, $numToChunk);
    }
    /**
     * get pushable member list
     * @return mixed
     */
    public function getPushableMemberList()
    {
        $query = "SELECT * FROM `subscription_container`
                  WHERE `is_pushed` = 0
                  AND `dataset_id` = '" . $this->datasetId . "'";

        $this->dbObj->prepareQuery($query);
        $this->pushableMemberList = $this->dbObj->getQuery();
        if (!empty($this->pushableMemberList)) {
            return $this->pushableMemberList;
        }
        return false;
    }
    /**
     * format member list to do IN statement update
     */
    protected function formatSendToListToDB(array $midList)
    {
        $memberStr = '';
        $i = 0;
        foreach ($midList as $mid) {
            $memberStr .= "'" . $mid . "',";
        }
        $memberStr = substr($memberStr, 0, -1);
        return $memberStr;
    }
}

class NCDRFloodPusher extends Pusher
{

    public function __construct()
    {
        $this->datasetId = 'ncdr_flood';
        parent::__construct();
    }

    /**
     * @param $msg
     * @return mixed
     */
    protected function sendMessage($msg)
    {
        global $lineApi, $lineConst;
        $msg = json_decode($msg[0]['info_to_show'], true);
        $message = '淹水警報:' . PHP_EOL .
            '【' . $msg['result']['areaName'] . '】' .
            $msg['result']['message'];
        $message .= PHP_EOL . '(此為自動推播訊息)';
        $rst = [];
        $len = count($this->sendTo);
        for ($i = 0; $i < $len; $i++) {
            $rst[$i] = messagesFromBot(
                $lineApi['sendMessage']['BC'],
                $this->sendTo[$i],
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
        }
        return $rst;
    }
}

class NCDRWSCPusher extends Pusher
{
    public function __construct()
    {
        $this->datasetId = 'ncdr_workschoolclose';
        parent::__construct();
    }
    /**
     * @param $msg
     * @return mixed
     */
    protected function sendMessage($msg)
    {
        global $lineApi, $lineConst;
        $msg = json_decode($msg[0]['info_to_show'], true);
        $message = '';
        $message .= '停班停課資訊:';
        $message .= PHP_EOL . $msg['result']['message'];
        $message .= PHP_EOL . '(此為自動推播訊息)';
        $rst = [];
        $len = count($this->sendTo);
        for ($i = 0; $i < $len; $i++) {
            $rst[$i] = messagesFromBot(
                $lineApi['sendMessage']['BC'],
                $this->sendTo[$i],
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
        }
        return $rst;
    }
}

class NCDRParkingPusher extends Pusher
{
    public function __construct()
    {
        $this->datasetId = 'ncdr_parking';
        parent::__construct();
    }
    /**
     * @param $msg
     * @return mixed
     */
    protected function sendMessage($msg)
    {
        global $lineApi, $lineConst, $lineBotConfig;
        $msg = json_decode($msg[0]['info_to_show'], true);
        $message = '';
        switch ($msg['result']['msgType']) {
            case 'Cancel':
                $message = '紅黃線停車資訊:' . PHP_EOL;
                $message .= $msg['result']['description'];
                break;
            default:
                $message = '紅黃線停車資訊:' . PHP_EOL;
                $message .= $msg['result']['description'];
                if (isset($msg['result']['areaDetail'])) {
                    $message .= PHP_EOL . '影響範圍請參考:(請使用行動裝置進入此網址)' . PHP_EOL;
                    $message .= 'line://ch/' . $lineBotConfig['channelId'] . '/?page=pm';
                }
                break;
        }
        $message .= PHP_EOL . '(此為自動推播訊息)';
        $rst = [];
        $len = count($this->sendTo);
        for ($i = 0; $i < $len; $i++) {
            $rst[$i] = messagesFromBot(
                $lineApi['sendMessage']['BC'],
                $this->sendTo[$i],
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
        }
        return $rst;
    }
}

class NCDRWatergatePusher extends Pusher
{
    public function __construct()
    {
        $this->datasetId = 'ncdr_watergate';
        parent::__construct();
    }

    /**
     * @param $msg
     * @return mixed
     */
    protected function sendMessage($msg)
    {
        global $lineApi, $lineConst, $lineBotConfig;
        $msg = json_decode($msg[0]['info_to_show'], true);
        $message = '';
        $message = '水閘門啟閉資訊:' . PHP_EOL;
        $message .= $msg['result']['description'];
        $message .= PHP_EOL . '(此為自動推播訊息)';
        $rst = [];
        $len = count($this->sendTo);
        for ($i = 0; $i < $len; $i++) {
            $rst[$i] = messagesFromBot(
                $lineApi['sendMessage']['BC'],
                $this->sendTo[$i],
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
        }
        return $rst;
    }
}
