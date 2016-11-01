<?php
/**
 * message's CRUD controller
 */
include_once ROOT_PATH . '/common/DbAccess.class.php';

class MessageLib
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
     * 新增message
     */
    public function addMessage($msgid, $mid, $payload, $sendAt, $rawdata)
    {
        // $messageIsExists = $this->listMessage($mid);
        // if ($messageIsExists['result'] === true) {
        //     return ['result' => false, 'errorMessage' => 'Message already exists'];
        // }
        $query = 'INSERT INTO `message` VALUES (:msgid, :mid, :pl, 0, :st, NOW(), :rd)';
        $sendAt = date('Y-m-d H:i:s', $sendAt);
        $this->dbObj->prepareQuery($query);
        $this->dbObj->bindMultiParams([
            ':msgid' => $msgid,
            ':mid' => $mid,
            ':pl' => $payload,
            ':st' => $sendAt,
            ':rd' => $rawdata,
        ]);

        $rst = $this->dbObj->doQuery();
        if (!$rst) {
            return ['result' => false, 'errorMessage' => 'add message failed!'];
        }
        return ['result' => true, 'errorMessage' => 'success'];
    }
    /**
     * 列出單一message
     */
    public function listMessage($mid)
    {
        $query = "SELECT * FROM `message` WHERE `mid` = :mid;";
        $this->dbObj->prepareQuery($query);
        $this->dbObj->bindSingleParam(':mid', $mid);
        $rst = $this->dbObj->getQuery();

        if (empty($rst)) {
            return ['result' => false, 'errorMessage' => 'no this message'];
        }
        return ['result' => true, 'errorMessage' => 'Message already exists ', 'data' => json_encode($rst)];
    }
}
