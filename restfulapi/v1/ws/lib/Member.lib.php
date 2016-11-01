<?php
/**
 * member's CRUD controller
 */
include_once ROOT_PATH . '/common/DbAccess.class.php';

class MemberLib
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
     * 新增member
     */
    public function addMember($mid, $displayName, $picUrl, $statusMsg)
    {
        // $memberIsExists = $this->listMember($mid);
        // if ($memberIsExists['result'] === true) {
        //     return ['result' => false, 'errorMessage' => 'Member already exists'];
        // }
        $query = 'INSERT INTO `member` VALUES (:mid, :dn, :pu, :sm)';
        $this->dbObj->prepareQuery($query);
        $this->dbObj->bindMultiParams([
            ':mid' => $mid,
            ':dn' => $displayName,
            ':pu' => $picUrl,
            ':sm' => $statusMsg,
        ]);
        $rst = $this->dbObj->doQuery();
        if (!$rst) {
            return ['result' => false, 'errorMessage' => 'add member failed!'];
        }
        return ['result' => true, 'errorMessage' => 'success'];
    }
    /**
     * 刪除member
     */
    public function deleteMember($mid)
    {
        $query = 'DELETE FROM `member` WHERE `mid` = :mid';
        $this->dbObj->prepareQuery($query);
        $this->dbObj->bindSingleParam(':mid', $mid);
        $rst = $this->dbObj->doQuery();

        if (!$rst) {
            return ['result' => false, 'errorMessage' => 'No this member'];
        }
        return ['result' => true, 'errorMessage' => 'success', 'data' => json_encode($rst)];
    }
    /**
     * 列出單一member
     */
    public function listMember($mid)
    {
        $query = 'SELECT * FROM `member` WHERE `mid` = :mid';
        $this->dbObj->prepareQuery($query);
        $this->dbObj->bindSingleParam(':mid', $mid);
        $rst = $this->dbObj->getQuery();

        if (empty($rst)) {
            return ['result' => false, 'errorMessage' => 'no this member'];
        }
        return ['result' => true, 'errorMessage' => 'Member already exists ', 'data' => json_encode($rst)];
    }
}
