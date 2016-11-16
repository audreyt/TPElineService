<?php

include_once __DIR__ . '/../../../config/Global.config.php';
include_once ROOT_PATH . SRU . 'ws/ws_lapi02.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    exit('Method not allowed');
}

if (!isset($httpRawData)) {
    $httpRawData = json_encode($_GET);
}

$obj = new GetLineToken;
$obj->setParam($httpRawData);
echo $obj->set();
