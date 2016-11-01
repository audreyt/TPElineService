<?php
include_once __DIR__ . '/lib/Message.lib.php';

class AddMessage
{
    /**
     * @var member table columns
     */
    private $mid, $msgId, $rawdata, $payload, $sendAt;
    /**
     * @var mixed
     */
    private $obj, $result, $authToken;
    public function __construct()
    {
        $this->obj = new MessageLib;
    }
    /**
     * @param $rawdata
     */
    public function setParam($rawdata)
    {
        $json = json_decode($rawdata, true);
        $this->authToken = $json['authToken'];
        $this->msgId = $json['msgId'];
        $this->mid = $json['memberId'];
        $this->payload = $json['payload'];
        $this->sendAt = $json['sendAt'];
        $this->rawdata = $json['rawdata'];
    }
    public function set()
    {
        if (is_null($this->authToken)) {
            $this->result = ['result' => false, 'errorMessage' => 'No authorization key'];
            return json_encode($this->result);
        }
        if ($this->authToken !== AUTH_TOKEN) {
            $this->result = ['result' => false, 'errorMessage' => 'Authorization fail'];
            return json_encode($this->result);
        }
        if (is_null($this->mid)) {
            $this->result = ['result' => false, 'errorMessage' => 'No member id'];
            return json_encode($this->result);
        }
        if (is_null($this->msgId)) {
            $this->result = ['result' => false, 'errorMessage' => 'No message id'];
            return json_encode($this->result);
        }
        if (is_null($this->sendAt)) {
            $this->result = ['result' => false, 'errorMessage' => 'No send time'];
            return json_encode($this->result);
        }
        if (is_null($this->rawdata)) {
            $this->result = ['result' => false, 'errorMessage' => 'No raw message data'];
            return json_encode($this->result);
        }
        if (is_null($this->payload)) {
            $this->result = ['result' => false, 'errorMessage' => 'No payload'];
            return json_encode($this->result);
        }
        $this->result = $this->obj->addMessage($this->msgId, $this->mid, $this->payload, $this->sendAt, $this->rawdata);
        return json_encode($this->result);
    }
}
