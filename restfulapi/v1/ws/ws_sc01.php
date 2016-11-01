<?php
include_once __DIR__ . '/lib/SubscriptionContainer.lib.php';
class AddSubscriptionContainer
{
    /**
     * @var mixed
     */
    private $obj, $mid, $sdetail, $did, $result, $authToken;
    public function __construct()
    {
        $this->obj = new SubscriptionContainerLib;
    }

    public function setParam($rawdata)
    {
        $json = json_decode($rawdata, true);
        $this->authToken = $json['authToken'];
        $this->mid = $json['memberId'];
        $this->did = $json['datasetId'];
        $this->sdetail = $json['subscribeDetail'];
    }
    public function set()
    {
        if (!isset($this->authToken) || !isset($this->mid) || !isset($this->did)) {
            $this->result = ['result' => false, 'errorMessage' => 'Input columns invalidate'];
            return json_encode($this->result);
        }
        if (is_null($this->authToken)) {
            $this->result = ['result' => false, 'errorMessage' => 'No authorization key'];
            return json_encode($this->result);
        }
        if (is_null($this->did)) {
            $this->result = ['result' => false, 'errorMessage' => 'No dataset id'];
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
        $this->result = $this->obj->addSubscriptionContainer($this->mid, $this->did, $this->sdetail);
        return json_encode($this->result);
    }
}
