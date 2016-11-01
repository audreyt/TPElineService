<?php
include_once __DIR__ . '/lib/Member.lib.php';

class AddMember
{
    /**
     * @var member table columns
     */
    private $mid, $displayName, $pictureUrl, $statusMessage;
    /**
     * @var mixed
     */
    private $obj, $result, $authToken;
    public function __construct()
    {
        $this->obj = new MemberLib;
    }
    /**
     * @param $rawdata
     */
    public function setParam($rawdata)
    {
        $json = json_decode($rawdata, true);
        $this->mid = $json['memberId'];
        $this->displayName = $json['displayName'];
        $this->pictureUrl = $json['puctureUrl'];
        $this->statusMessage = $json['statusMessage'];
        $this->authToken = $json['authToken'];
    }
    public function set()
    {
        if (is_null($this->authToken)) {
            $this->result = ['result' => false, 'errorMessage' => 'No authorization key'];
            return json_encode($this->result);
        }
        if (is_null($this->mid)) {
            $this->result = ['result' => false, 'errorMessage' => 'No member id'];
            return json_encode($this->result);
        }
        if ($this->authToken !== AUTH_TOKEN) {
            $this->result = ['result' => false, 'errorMessage' => 'Authorization fail'];
            return json_encode($this->result);
        }
        $this->result = $this->obj->addMember($this->mid, $this->displayName, $this->pictureUrl, $this->statusMessage);
        return json_encode($this->result);
    }
}
