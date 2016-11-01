<?php
require __DIR__ . '/../config/Global.config.php';
include ROOT_PATH . '/config/LocalDb.config.php';

class PdoDatabase
{
    /**
     * @var mixed
     */
    private $host, $user, $passwd, $dblink, $dbname, $dsn, $dbLtype, $dbTblCharset, $stmt;
    /**
     * @param $dbname
     */
    public function __construct($dbname)
    {
        $this->getDbConnect($dbname);
    }
    /**
     * @param $dbname
     */
    public function getDbConnect($dbname)
    {
        $this->dbname = $dbname;
        $this->host = DB_HOST;
        $this->user = DB_USER;
        $this->passwd = DB_PASS;
        $this->dbLtype = DB_LTYPE;
        $this->dbTblCharset = DB_TBLCHARSET;
        $this->dsn = "{$this->dbLtype}:host={$this->host};dbname={$this->dbname};charset={$this->dbTblCharset}";
        try {
            $this->dblink = new PDO($this->dsn, $this->user, $this->passwd);
            $this->dblink->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo 'Connetcion failed: ' . $e->getMessage();
        }
    }
    /**
     * 複寫PDO::prepare，讓外部可以調用
     * @param  SQL-statement $query
     */
    public function prepareQuery($query)
    {
        $this->stmt = $this->dblink->prepare($query);
    }
    /**
     * 綁定單一變數,避免SQL injection
     *
     * 如要綁定多變數需要啟動prepare的emulation mode,但對prepare的效能有很大影響
     *
     * @param mixed $param 傳入變數名稱
     * @param mixed $value 傳入參數值
     */
    public function bindSingleParam($param, $value, $type = null)
    {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
                    break;
            }
        }
        $this->stmt->bindParam($param, $value, $type);
    }

    /**
     * 綁定多個變數,避免SQL injection
     * @param array $param 傳入變數陣列
     */
    public function bindMultiParams(array $param)
    {
        foreach ($param as $k => $v) {
            $this->bindSingleParam($k, $v);
        }
    }
    /**
     * @return int
     */
    public function getAffetedRows()
    {
        return $this->stmt->rowCount();
    }
    /**
     * @param int $type 0->column name=>value;1=>num=>value
     * @return mixed
     */
    public function getQuery($type = 0)
    {
        $rst = [];
        if ($type === 0) {
            $this->stmt->setFetchMode(PDO::FETCH_ASSOC);
        }
        if ($type === 1) {
            $this->stmt->setFetchMode(PDO::FETCH_NUM);
        }
        $this->stmt->execute();
        while ($row = $this->stmt->fetch()) {
            $rst[] = $row;
        }
        if (!is_null($rst)) {
            return $rst;
        } else {
            return $this->stmt->errorInfo();
        }
    }
    /**
     * @return int
     */
    public function doQuery()
    {
        $this->stmt->execute();
        return $this->getAffetedRows();
    }
    /**
     * 執行須回傳的多條件sql statement
     * @param  array $whereAry 條件參數
     * @return mixed
     */
    public function getQueryWithMultiWhere(array $whereAry, $type = 0)
    {
        $rst = [];
        if ($type === 0) {
            $this->stmt->setFetchMode(PDO::FETCH_ASSOC);
        }
        if ($type === 1) {
            $this->stmt->setFetchMode(PDO::FETCH_NUM);
        }
        $this->stmt->execute($whereAry);
        while ($row = $this->stmt->fetch()) {
            $rst[] = $row;
        }
        if (!is_null($rst)) {
            return $rst;
        } else {
            return $this->stmt->errorInfo();
        }
    }
    /**
     * 執行不須回傳的多條件sql statement
     * @param array $whereAry 條件參數
     * @return mixed
     */
    public function doQueryWithMultiWhere(array $whereAry)
    {
        $this->stmt->execute($whereAry);
    }

    public function closeDbConn()
    {
        $this->dblink = null;
    }

}
