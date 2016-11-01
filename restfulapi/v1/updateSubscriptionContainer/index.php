<?php

include_once __DIR__ . '/../../../config/Global.config.php';
include_once ROOT_PATH . SRU . 'ws/ws_sc03.php';

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    exit('Method not allowed');
}

if (!isset($httpRawData)) {
    $httpRawData = file_get_contents('php://input');
}

$obj = new UpdateSubscriptionContainer;
$obj->setParam($httpRawData);
echo $obj->set();
