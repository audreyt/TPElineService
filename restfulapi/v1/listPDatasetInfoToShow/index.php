<?php
#################################################
# 處理經緯度相關推播資訊
#################################################
include_once __DIR__ . '/../../../config/Global.config.php';
include_once ROOT_PATH . SRU . 'ws/ws_pdi02.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    exit('Method not allowed');
}

if (!isset($httpRawData)) {
    $httpRawData = json_encode($_GET);
}

$obj = new ListPDatasetInfoToShow;
$obj->setParam($httpRawData);
echo $obj->set();
