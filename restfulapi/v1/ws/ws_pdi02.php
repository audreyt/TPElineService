<?php
include_once __DIR__ . '/lib/Dataset.lib.php';
class ListPDatasetInfoToShow
{
    private $obj, $area, $did, $result, $authToken;
    public function __construct()
    {
        $this->obj = new DatasetLib;
    }
    public function setParam($rawdata)
    {
        $json = json_decode($rawdata, true);
        $this->authToken = $json['authToken'];
        $this->did = $json['datasetId'];
        $this->area = $json['areaCode'];
    }
    public function set()
    {
        if (!isset($this->authToken) || !isset($this->did)) {
            $this->result = ['result' => false, 'errorMessage' => 'Input keys not fullfill'];
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
        if ($this->authToken !== AUTH_TOKEN) {
            $this->result = ['result' => false, 'errorMessage' => 'Authorization fail'];
            return json_encode($this->result);
        }
        $this->result = $this->obj->listPDatasetInfoToShow($this->did, $this->area);
        return json_encode($this->result);
    }
}
