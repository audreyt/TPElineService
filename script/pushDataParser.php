<?php
require_once __DIR__ . '/../config/Global.config.php';
require_once ROOT_PATH . '/common/Debug.trait.php';
require_once ROOT_PATH . '/script/Parser.class.php';
require_once ROOT_PATH . '/config/Script.config.php';
// all files structure
$floodFiles = $wscloseFiles = $parkingFiles = $watergateFiles = [];

$pushDataFolder = scandir(ROOT_PATH . PUSH_DATASET_PATH, 1);
for ($i = 0; $i < 2; $i++) {
    // pop . & ..
    array_pop($pushDataFolder);
}

foreach ($pushDataFolder as $key => $fileName) {
    if (itsTimeToParse(filemtime(ROOT_PATH . PUSH_DATASET_PATH . $fileName))) {
        if (strpos($fileName, 'FloodWarn') > -1) {
            $floodFiles[$key]['time'] = filemtime(ROOT_PATH . PUSH_DATASET_PATH . $fileName);
            $fs = explode('.', $fileName);
            $floodFiles[$key]['name'] = $fs[0];
            $floodFiles[$key]['extName'] = $fs[1];
        } elseif (strpos($fileName, 'workSchlClos') > -1) {
            $wscloseFiles[$key]['time'] = filemtime(ROOT_PATH . PUSH_DATASET_PATH . $fileName);
            $fs = explode('.', $fileName);
            $wscloseFiles[$key]['name'] = $fs[0] . '.' . $fs[1] . '.' . $fs[2];
            $wscloseFiles[$key]['extName'] = $fs[3];
        } elseif (strpos($fileName, 'TPE_parking') > -1) {
            $parkingFiles[$key]['time'] = filemtime(ROOT_PATH . PUSH_DATASET_PATH . $fileName);
            $fs = explode('.', $fileName);
            $parkingFiles[$key]['name'] = $fs[0];
            $parkingFiles[$key]['extName'] = $fs[1];
        } elseif (strpos($fileName, 'TPE_gates') > -1) {
            $watergateFiles[$key]['time'] = filemtime(ROOT_PATH . PUSH_DATASET_PATH . $fileName);
            $fs = explode('.', $fileName);
            $watergateFiles[$key]['name'] = $fs[0];
            $watergateFiles[$key]['extName'] = $fs[1];
        }
        // other dataset... need to refactor
    }
}
if (gettype($floodFiles) === 'array' && !empty($floodFiles)) {
    parseNCDRPFloodData($floodFiles);
}

if (gettype($wscloseFiles) === 'array' && !empty($wscloseFiles)) {
    parseNCDRPWorkSchoolCloseData($wscloseFiles);
}
if (gettype($parkingFiles) === 'array' && !empty($parkingFiles)) {
    parseNCDRPParkingData($parkingFiles);
}

if (gettype($watergateFiles) === 'array' && !empty($watergateFiles)) {
    parseNCDRPWatergateData($watergateFiles);
}

function itsTimeToParse($fileTime)
{
    // parse files only two minute before
    if ((time() - $fileTime) < 120) {
        return true;
    }
    return false;
}

function parseNCDRPFloodData($files)
{
    $obj = new NCDRPFloodParser;
    foreach ($files as $fp) {
        $xmlData = $obj->getRawData(ROOT_PATH . PUSH_DATASET_PATH, $fp['name'], $fp['extName']);
        if ($xmlData) {
            $obj->parseData();
        }
    }
}

function parseNCDRPWorkSchoolCloseData($files)
{
    $dataToDB = [];
    $obj = new NCDRPWSCParser;
    foreach ($files as $fp) {
        if (!strpos($fp['name'], '_63_')) {
            continue;
        }
        $xmlData = $obj->getRawData(ROOT_PATH . PUSH_DATASET_PATH, $fp['name'], $fp['extName']);
        if ($xmlData) {
            $dataToDB['result'] = $obj->parseData();
        }
    }
    if (!empty($dataToDB['result'])) {
        $obj->saveToDB('ncdr_workschoolclose', '', json_encode($dataToDB), 'dataset_to_push');
    }
}
function parseNCDRPParkingData($files)
{
    $obj = new NCDRPParkingParser;
    // parse latested file
    usort($files, function ($a, $b) {
        return $a['time'] < $b['time'];
    });
    $toParse = array_shift($files);

    $xmlData = $obj->getRawData(ROOT_PATH . PUSH_DATASET_PATH, $toParse['name'], $toParse['extName']);
    if ($xmlData) {
        $dataToDB['result'] = $obj->parseData();
        $obj->saveToDB('ncdr_parking', '', json_encode($dataToDB), 'dataset_to_push');
    }
}

function parseNCDRPWatergateData($files)
{
    $dataToDB = [];
    $obj = new NCDRPWatergateParser;
    usort($files, function ($a, $b) {
        return $a['time'] < $b['time'];
    });
    $toParse = array_shift($files);

    $xmlData = $obj->getRawData(ROOT_PATH . PUSH_DATASET_PATH, $toParse['name'], $toParse['extName']);
    if ($xmlData) {
        $dataToDB['result'] = $obj->parseData();
        $obj->saveToDB('ncdr_watergate', '', json_encode($dataToDB), 'dataset_to_push');
    }
}
// air box is done by displayDataParser
