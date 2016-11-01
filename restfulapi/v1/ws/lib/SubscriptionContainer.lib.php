<?php
/**
 * subscription container 's CRUD controller
 */
include_once ROOT_PATH . '/common/DbAccess.class.php';

class SubscriptionContainerLib
{
    private $dbObj;
    public function __construct()
    {
        $this->dbObj = new PdoDatabase('linebot');
    }
    /**
     * 新增member
     * @param $mid member id
     * @param $did dataset id
     * @param $pushNc push notification config
     */
    public function addSubscriptionContainer($mid, $did, $sdetail = '')
    {
        // $isSubscribed = $this->listSubscriptionContainer($mid, $did);
        // if ($isSubscribed['result'] === true) {
        //     return ['result' => false, 'errorMessage' => 'Relationship already exists'];
        // }
        $query = 'INSERT INTO `subscription_container` VALUES (:mid, :did, :sd, 0, NOW(), 0);';
        $this->dbObj->prepareQuery($query);
        $this->dbObj->bindMultiParams([
            ':mid' => $mid,
            ':did' => $did,
            ':sd' => $sdetail,
        ]);
        $rst = $this->dbObj->doQuery();
        if (!$rst) {
            return ['result' => false, 'errorMessage' => 'add subscription container failed'];
        }
        return ['result' => true, 'errorMessage' => 'success', 'data' => json_encode($rst)];
    }
    /**
     * 列出單一訂閱容器
     */
    public function listSubscriptionContainer($mid, $did)
    {
        $query = "SELECT * FROM `subscription_container` WHERE `mid` = :mid AND `dataset_id` = :did;";
        $this->dbObj->prepareQuery($query);
        $this->dbObj->bindSingleParam(':mid', $mid);
        $this->dbObj->bindSingleParam(':did', $did);
        $rst = $this->dbObj->getQuery();

        if (empty($rst)) {
            return ['result' => false, 'errorMessage' => 'no subscription yet'];
        }
        return ['result' => true, 'errorMessage' => 'already subscribed', 'data' => json_encode($rst)];
    }
    /**
     * 修改單一訂閱容器設定
     */
    public function updateSubscriptionContainer($mid, $did, $dataToUpdate, $todo = '')
    {
        if (!empty($todo)) {
            switch ($todo) {
                case 'cancelArea':
                    // cancel ncdr area
                    $origRaw = $this->listSubscriptionContainer($mid, $did);
                    $origData = json_decode($origRaw['data'], true);
                    $origArea = json_decode($origData[0]['detail'], true);
                    $dataToUpdate = json_decode($dataToUpdate, true);

                    if (false !== $key = array_search($dataToUpdate['area'][0], $origArea['area'])) {
                        unset($origArea['area'][$key]);
                    }
                    $newArea = array_values($origArea['area']);
                    $dataToUpdate['area'] = $newArea;
                    if (empty($dataToUpdate['area'])) {
                        $rst = $this->deleteSubscriptionContainer($mid, $did);
                        return $rst;
                    }
                    $dataToUpdate = json_encode($dataToUpdate);
                    break;
                case 'addArea':
                    // add ncdr area
                    $origRaw = $this->listSubscriptionContainer($mid, $did);
                    $origData = json_decode($origRaw['data'], true);
                    $origArea = json_decode($origData[0]['detail'], true);
                    $dataToUpdate = json_decode($dataToUpdate, true);
                    array_push($origArea['area'], $dataToUpdate['area'][0]);
                    $dataToUpdate['area'] = $origArea['area'];
                    $dataToUpdate = json_encode($dataToUpdate);
                    break;
                case 'addAirboxSubArea':
                    $origRaw = $this->listSubscriptionContainer($mid, $did);
                    $origData = json_decode($origRaw['data'], true);
                    $origArea = json_decode($origData[0]['detail'], true);
                    $dataToUpdate = json_decode($dataToUpdate, true);
                    array_push($origArea, $dataToUpdate);
                    $dataToUpdate = json_encode($origArea);
                    break;
                case 'updateAirboxSubArea':
                    $origRaw = $this->listSubscriptionContainer($mid, $did);
                    $origData = json_decode($origRaw['data'], true);
                    $origArea = json_decode($origData[0]['detail'], true);
                    $dataToUpdate = json_decode($dataToUpdate, true);
                    foreach ($origArea as $k => $v) {
                        if ($v['area'] === $dataToUpdate['area']) {
                            $origArea[$k]['timeToPush'] = $dataToUpdate['timeToPush'];
                        }
                    }
                    $dataToUpdate = json_encode($origArea);
                    break;
                case 'cancelAirboxSubArea':
                    $origRaw = $this->listSubscriptionContainer($mid, $did);
                    $origData = json_decode($origRaw['data'], true);
                    $origArea = json_decode($origData[0]['detail'], true);
                    $dataToUpdate = json_decode($dataToUpdate, true);
                    foreach ($origArea as $k => $v) {
                        if ($v['area'] === $dataToUpdate['area']) {
                            unset($origArea[$k]);
                        }
                    }
                    $newArea = array_values($origArea);
                    if (empty($origArea)) {
                        $rst = $this->deleteSubscriptionContainer($mid, $did);
                        return $rst;
                    }
                    $dataToUpdate = json_encode($newArea);
                    break;
                // case 'pushNotification':
                //     $query = "UPDATE `subscription_container` SET `last_pushed_at` = NOW(), `is_pushed` = 1 WHERE `mid` = :mid AND `dataset_id` = :did;";
                //     break;
                // case 'parseNotification':
                //     $query = "UPDATE `subscription_container` SET `last_pushed_at` = NOW(), `is_pushed` = 0 WHERE `mid` = :mid AND `dataset_id` = :did;";
                //     break;
                default:
                    break;
            }
        }
        $query = "UPDATE `subscription_container` SET `detail` = :sd, `changed_at` = NOW() WHERE `mid` = :mid AND `dataset_id` = :did;";
        $this->dbObj->prepareQuery($query);
        $this->dbObj->bindMultiParams([
            ':mid' => $mid,
            ':did' => $did,
            ':sd' => $dataToUpdate,
        ]);
        $rst = $this->dbObj->doQuery();

        if (!$rst) {
            return ['result' => false, 'errorMessage' => 'Update failed'];
        }
        return ['result' => true, 'errorMessage' => 'subscription container updated', 'data' => json_encode($rst)];
    }
    /**
     * 刪除單一訂閱容器設定
     */
    public function deleteSubscriptionContainer($mid, $did)
    {
        $query = 'DELETE FROM `subscription_container` WHERE `mid` = :mid AND `dataset_id` = :did';
        $this->dbObj->prepareQuery($query);
        $this->dbObj->bindSingleParam(':mid', $mid);
        $this->dbObj->bindSingleParam(':did', $did);
        $rst = $this->dbObj->doQuery();
        if (!$rst) {
            return ['result' => false, 'errorMessage' => 'Invalid input'];
        }
        return ['result' => true, 'errorMessage' => 'subscription container deleted', 'data' => json_encode($rst)];
    }
}
