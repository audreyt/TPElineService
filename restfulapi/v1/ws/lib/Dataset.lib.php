<?php
/**
 * dataset information's CRUD controller
 */
include_once ROOT_PATH . '/common/DbAccess.class.php';

class DatasetLib
{
    private $dbObj;
    public function __construct()
    {
        $this->dbObj = new PdoDatabase('linebot');
    }
    /**
     * 列出單一資料集資料
     */
    public function listDataset($did, $area = '')
    {

        $query = "SELECT * FROM `dataset_to_display` WHERE `id` = :did AND `area_code` = :area;";
        $this->dbObj->prepareQuery($query);
        $this->dbObj->bindSingleParam(':did', $did);
        $this->dbObj->bindSingleParam(':area', $area);
        $rst = $this->dbObj->getQuery();

        if (empty($rst)) {
            return ['result' => false, 'errorMessage' => 'no subscription yet'];
        }
        return ['result' => true, 'errorMessage' => 'success', 'data' => json_encode($rst)];
    }

    /**
     * 列出單一資料集資料
     */
    public function listDatasetInfoToShow($did, $area = '')
    {

        $query = "SELECT `info_to_show` FROM `dataset_to_display` WHERE `id` = :did AND `area_code` = :area;";
        $this->dbObj->prepareQuery($query);
        $this->dbObj->bindSingleParam(':did', $did);
        $this->dbObj->bindSingleParam(':area', $area);
        $rst = $this->dbObj->getQuery();

        if (empty($rst)) {
            return ['result' => false, 'errorMessage' => 'no subscription yet'];
        }
        return ['result' => true, 'errorMessage' => 'success', 'data' => json_encode($rst)];
    }

    /**
     * 列出推播單一資料集資料
     */
    public function listPDatasetInfoToShow($did, $area = '')
    {
        // 只取過去一天
        $query = "SELECT `info_to_show` FROM `dataset_to_push` WHERE `id` = :did AND `area_code` = :area
        -- for test
        AND (UNIX_TIMESTAMP(NOW()) -UNIX_TIMESTAMP(`changed_at`) < 86400);";
        $this->dbObj->prepareQuery($query);
        $this->dbObj->bindSingleParam(':did', $did);
        $this->dbObj->bindSingleParam(':area', $area);
        $rst = $this->dbObj->getQuery();

        if (empty($rst)) {
            return ['result' => false, 'errorMessage' => 'no subscription yet'];
        }
        return ['result' => true, 'errorMessage' => 'success', 'data' => json_encode($rst)];
    }
}
