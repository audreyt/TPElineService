<?php

include_once ROOT_PATH . '/common/DbAccess.class.php';

class LINELib
{
    /**
     * @var mixed
     */
    private $dbObj;
    public function __construct()
    {
        $this->dbObj = new PdoDatabase(DB_NAME);
    }
    /**
     * 列出單一member
     */
    public function getLineToken()
    {
        $query = 'SELECT * FROM `line_service_token`';
        $this->dbObj->prepareQuery($query);
        $rst = $this->dbObj->getQuery();

        if (empty($rst)) {
            return ['result' => false, 'errorMessage' => 'no token'];
        }
        return ['result' => true, 'errorMessage' => 'success', 'data' => json_encode($rst)];
    }
}
