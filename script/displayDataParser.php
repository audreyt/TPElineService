<?php
require_once __DIR__ . '/../config/Global.config.php';
require_once ROOT_PATH . '/common/Debug.trait.php';
require_once ROOT_PATH . '/script/Parser.class.php';
require_once ROOT_PATH . '/config/Script.config.php';

$toExecute = $argv[1];
if (strpos($toExecute, 'NCDRFlood') > -1) {
    parseNCDRDFloodData();
}
if (strpos($toExecute, 'NCDRWorkSchoolClose') > -1) {
    parseNCDRDWorkSchoolCloseData();
}
if (strpos($toExecute, 'NCDRWatergate') > -1) {
    parseNCDRDWatergateData();
}
if (strpos($toExecute, 'NCDRParking') > -1) {
    parseNCDRDParkingData();
}
if (strpos($toExecute, 'EOCDisaster') > -1) {
    parseEOCDisasterData();
}
if (strpos($toExecute, 'Airbox') > -1) {
    parseAirboxDData();
}
function parseNCDRDFloodData()
{
    global $taiwanGeocodeTpe;
    $obj = new NCDRDFloodParser;
    foreach ($taiwanGeocodeTpe as $code => $area) {
        $xmlData = $obj->getRawData(ROOT_PATH . DISPLAY_DATASET_PATH, 'ncdr_flood_' . $code, 'xml');
        if ($xmlData) {
            $obj->parseData();
        } else {
            $obj->saveEmptyData($code);
        }
    }
}

function parseNCDRDWorkSchoolCloseData()
{
    global $taiwanGeocode;
    $dataToDB = [];
    $obj = new NCDRDWSCParser;
    $xmlData = $obj->getRawData(ROOT_PATH . DISPLAY_DATASET_PATH, 'ncdr_workschoolclose_63', 'xml');
    if ($xmlData) {
        $dataToDB['result'] = $obj->parseData();
    }
    $obj->saveToDB('ncdr_workschoolclose', '', json_encode($dataToDB), 'dataset_to_display');
}
function parseNCDRDParkingData()
{
    $dataToDB = [];
    $obj = new NCDRDParkingParser;

    $xmlData = $obj->getRawData(ROOT_PATH . DISPLAY_DATASET_PATH, 'ncdr_parking', 'xml');
    if ($xmlData) {
        $dataToDB['result'] = $obj->parseData();
    }
    $obj->saveToDB('ncdr_parking', '', json_encode($dataToDB), 'dataset_to_display');
}
function parseNCDRDWatergateData()
{
    global $taiwanGeocode;
    $dataToDB = [];
    $obj = new NCDRDWatergateParser;
    $xmlData = $obj->getRawData(ROOT_PATH . DISPLAY_DATASET_PATH, 'ncdr_watergate', 'xml');
    if ($xmlData) {
        $dataToDB['result'] = $obj->parseData();
    }
    $obj->saveToDB('ncdr_watergate', '', json_encode($dataToDB), 'dataset_to_display');
}
function parseEOCDisasterData()
{
    global $taiwanGeocodeTpe;
    $obj = new EOCDisasterParser;
    foreach ($taiwanGeocodeTpe as $code => $area) {
        $jsonData = $obj->getRawData(ROOT_PATH . DISPLAY_DATASET_PATH, 'eoc_disaster_' . $code, 'json');
        if ($jsonData) {
            $obj->parseData();
        }
    }
}
/**
 * 需先下載
 * 1. http://data.taipei/opendata/datalist/datasetMeta/download?id=58b4f7b9-d0c5-4de8-aa7f-981fcb625e45&rid=a1c35319-c67d-4c7b-86fe-442874cb3d79 => save as ROOT_PATH . '/files/TPEschools.csv' , UTF8-encoding
 * 2. http://data.taipei/opendata/datalist/datasetMeta/download?id=4ba06157-3854-4111-9383-3b8a188c962a&rid=121311db-55f0-4bf3-908c-5456d8491d43 => save as ROOT_PATH . DISPLAY_DATASET_PATH .'AirBoxDevicesList.csv', UTF8-encoding
 *
 * 之後才能進行資料處理作業
 */
function parseAirboxDData()
{
    /*
     * get devices key-value pair data
     */
    $deviceInfoList = [];
    $row = 0;
    if (($csvFile = fopen(ROOT_PATH . DISPLAY_DATASET_PATH . 'AirBoxDevicesList.csv', 'r')) !== false) {
        while (($csvBody = fgetcsv($csvFile, 1000, ',')) !== false) {
            $deviceInfoList[$row]['deviceId'] = $csvBody[0];
            $deviceInfoList[$row]['devicePos'] = $csvBody[1];
            $row++;
        }
    }
    fclose($csvFile);
    unset($csvBoy);
    unset($csvFile);
    /*
     * get schools key-value pair data
     */
    $TPEschoolsList = [];
    $row = 0;
    if (($csvFile = fopen(ROOT_PATH . '/files/TPEschools.csv', 'r')) !== false) {
        while (($csvBody = fgets($csvFile, 1000)) !== false) {
            $csvBody = explode(',', $csvBody);
            $TPEschoolsList[$row]['schoolName'] = $csvBody[1];
            $TPEschoolsList[$row]['schoolDist'] = mb_substr($csvBody[3], 3, 3);
            $TPEschoolsList[$row]['gps']['lat'] = floatval($csvBody[5]);
            $TPEschoolsList[$row]['gps']['lng'] = floatval($csvBody[6]);
            $row++;
        }
    }
    unset($csvBoy);
    unset($csvFile);
    array_shift($TPEschoolsList);
    array_shift($deviceInfoList);

    $airboxGZ = ['AirBoxData_V2', 'AirBoxDevice_V2'];
    $obj = new AirboxDParser;
    foreach ($airboxGZ as $fn) {
        $obj->uncompressGZ(ROOT_PATH . DISPLAY_DATASET_PATH, $fn, 'json');
    }

    $airboxData = $obj->getRawData(ROOT_PATH . DISPLAY_DATASET_PATH, 'AirBoxData_V2', 'json');
    $airboxDeviceData = $obj->getRawData(ROOT_PATH . DISPLAY_DATASET_PATH, 'AirBoxDevice_V2', 'json');

    $obj->setupData($airboxData, $airboxDeviceData, $deviceInfoList, $TPEschoolsList);
    $obj->parseData();
}
