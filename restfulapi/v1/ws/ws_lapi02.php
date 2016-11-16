<?php
include_once __DIR__ . '/lib/LINE.lib.php';
class GetLineToken
{
    private $obj, $authToken, $result;
    public function __construct()
    {
        $this->obj = new LINELib;
    }
    public function setParam($rawdata)
    {
        $json = json_decode($rawdata, true);
        $this->authToken = $json['authToken'];
    }
    public function set()
    {
        if (!isset($this->authToken) || is_null($this->authToken)) {
            $this->result = ['result' => false, 'errorMessage' => 'No authorization key'];
            return json_encode($this->result);
        }
        if ($this->authToken !== AUTH_TOKEN) {
            $this->result = ['result' => false, 'errorMessage' => 'Authorization fail'];
            return json_encode($this->result);
        }
        $this->result = $this->obj->getLineToken();
        return json_encode($this->result);
    }
}
