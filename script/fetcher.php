<?php
require_once __DIR__ . '/../config/Global.config.php';
require_once ROOT_PATH . '/common/Debug.trait.php';
require_once ROOT_PATH . '/config/Script.config.php';
require_once ROOT_PATH . '/script/Fetcher.class.php';

global $fetcherInterval;
$toExecute = [];
$currentTime = (int) date('i', time());
foreach ($fetcherInterval as $datasetName => $interval) {
    if (gettype($currentTime / $interval) === 'integer') {
        $toExecute[] = $datasetName;
    }
}

foreach ($toExecute as $item) {
    switch ($item) {
        case 'ncdr_flood':
            // 淹水資訊
            fetchNCDRFloodData();
            break;
        case 'ncdr_workschoolclose':
            // 停班停課
            fetchNCDRWorkSchoolCloseData();
            break;
        case 'ncdr_parking':
            // 紅黃線停車
            fetchNCDRParkingData();
            break;
        case 'ncdr_watergate':
            // 水閘門啟閉
            fetchNCDRWaterGateData();
            break;
        case 'eoc_disaster':
            // 災情資訊
            fetchEOCDisasterData();
            break;
        case 'airbox':
            // 空氣盒子
            fetchAirboxData();
            break;
        default:
            exit(0);
    }
}

function fetchNCDRFloodData()
{
    global $uriConfig;
    $obj = new NCDRFloodFetcher;
    $obj->setFileInfo(ROOT_PATH . DISPLAY_DATASET_PATH, 'ncdr_flood', 'xml');
    $obj->setTopUri($uriConfig['ncdr_flood']);
    $obj->fetchData();

    sleep(5);
    chdir(SCRIPT_PATH);
    exec(PHP_PATH . ' displayDataParser.php NCDRFlood');
}
function fetchNCDRWorkSchoolCloseData()
{
    global $uriConfig;
    $obj = new NCDRWSCFetcher;
    $obj->setTopUri($uriConfig['ncdr_workschoolclose']);
    $obj->setFileInfo(ROOT_PATH . DISPLAY_DATASET_PATH, 'ncdr_workschoolclose', 'xml');
    $obj->fetchData();

    sleep(5);
    chdir(SCRIPT_PATH);
    exec(PHP_PATH . ' displayDataParser.php NCDRWorkSchoolClose');
}
function fetchNCDRParkingData()
{
    global $uriConfig;
    $obj = new NCDRParkingFetcher;
    $obj->setTopUri($uriConfig['ncdr_parking']);
    $obj->setFileInfo(ROOT_PATH . DISPLAY_DATASET_PATH, 'ncdr_parking', 'xml');
    $obj->fetchData();

    sleep(5);
    chdir(SCRIPT_PATH);
    exec(PHP_PATH . ' displayDataParser.php NCDRParking');
}
function fetchNCDRWaterGateData()
{
    global $uriConfig;
    $obj = new NCDRWgateFetcher;
    $obj->setTopUri($uriConfig['ncdr_watergate']);
    $obj->setFileInfo(ROOT_PATH . DISPLAY_DATASET_PATH, 'ncdr_watergate', 'xml');
    $obj->fetchData();

    sleep(5);
    chdir(SCRIPT_PATH);
    exec(PHP_PATH . ' displayDataParser.php NCDRWatergate');
}
function fetchEOCDisasterData()
{
    $obj = new EOCFetcher;
    $obj->fetchData();

    sleep(5);
    chdir(SCRIPT_PATH);
    exec(PHP_PATH . ' displayDataParser.php EOCDisaster');
}

function fetchAirboxData()
{
    global $uriConfig;
    $obj = new AirboxFetcher;
    foreach ($uriConfig['airbox'] as $dataUri) {
        $obj->setTopUri($dataUri);
        $fileName = substr($dataUri, 46, 50);
        $fileName = substr($fileName, 0, -3);
        $obj->setFileInfo(ROOT_PATH . DISPLAY_DATASET_PATH, $fileName, 'gz');
        $obj->fetchData();
    }

    sleep(5);
    chdir(SCRIPT_PATH);
    exec(PHP_PATH . ' displayDataParser.php Airbox');
}
