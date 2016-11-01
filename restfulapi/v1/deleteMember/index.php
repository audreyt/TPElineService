<?php
include_once __DIR__ . '/../../../config/Global.config.php';
include_once ROOT_PATH . SRU . 'ws/ws_m04.php';

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    exit('Method not allowed');
}

if (!isset($httpRawData)) {
    $httpRawData = file_get_contents('php://input');
}

$obj = new DeleteMember;
$obj->setParam($httpRawData);
echo $obj->set();
