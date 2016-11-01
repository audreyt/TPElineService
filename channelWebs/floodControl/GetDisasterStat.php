<?php
require '../DetectDevice.php';
if($rst === false){
    exit('操作錯誤');
}
header('Content-Type: application/json;charset=UTF-8');

$rst = file_get_contents('http://210.59.250.198/DisasterOperationSystemWebAPIUnite/api/DisasterServiceApi/GetDisasterCategoryAndSumByDPID?District=' . $_GET['d']);

echo $rst;
