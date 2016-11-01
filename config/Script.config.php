<?php
/**
 * 定義 script/ 所需共用變數
 */
global $fetcherInterval, $uriConfig, $eocDisasterCasecate, $taiwanGeocodeTpe;
if (!defined('DISPLAY_DATASET_PATH')) define('DISPLAY_DATASET_PATH', '/files/displayDataset/');
if (!defined('PUSH_DATASET_PATH')) define('PUSH_DATASET_PATH', '/files/pushDataset/');
if (!defined('EXPIRED_DATASET_PATH')) define('EXPIRED_DATASET_PATH', '/files/expiredDataset/');
if (!defined('PHP_PATH')) define('PHP_PATH', '/path/to/php');
if (!defined('SCRIPT_PATH')) define('SCRIPT_PATH', '/path/to/script');
// self define in minute
$fetcherInterval = [
    // 'ncdr_workschoolclose' => 50,
    // 'ncdr_flood' => 50,
    // 'ncdr_parking' => 50,
    // 'eoc_disaster' => 50,
    // 'airbox' => 6,
];
$uriConfig = [
    // 停班停課
    'ncdr_workschoolclose' => 'https://alerts.ncdr.nat.gov.tw/RssAtomFeed.ashx?AlertType=33',
    // 淹水
    'ncdr_flood' => 'https://alerts.ncdr.nat.gov.tw/RssAtomFeed.ashx?AlertType=8',
    // 紅黃線停車
    'ncdr_parking' => 'https://alerts.ncdr.nat.gov.tw/RssAtomFeed.ashx?AlertType=1057',
    // 水閘門啟閉
    'ncdr_watergate' => 'https://alerts.ncdr.nat.gov.tw/RssAtomFeed.ashx?AlertType=1059',
    // 災情資訊,EOC測試IP，需與EOC申請開通
    'eoc_disaster' => 'http://210.59.250.198/DisasterOperationSystemWebAPIUnite/api/DisasterServiceApi/GetDisasterSummary?District=',
    // 空氣盒子
    // airbox要先下載下面兩份檔案
    // http://data.taipei/opendata/datalist/datasetMeta/download?id=58b4f7b9-d0c5-4de8-aa7f-981fcb625e45&rid=a1c35319-c67d-4c7b-86fe-442874cb3d79 => save as ROOT_PATH . '/files/TPEschools.csv' , UTF8-encoding
    // http://data.taipei/opendata/datalist/datasetMeta/download?id=4ba06157-3854-4111-9383-3b8a188c962a&rid=121311db-55f0-4bf3-908c-5456d8491d43 => save as ROOT_PATH . DISPLAY_DATASET_PATH .'AirBoxDevicesList.csv', UTF8-encoding
    'airbox' => [
        // 'https://tpairbox.blob.core.windows.net/blobfs/AirBoxDevice_V2.gz',
        'https://tpairbox.blob.core.windows.net/blobfs/AirBoxData_V2.gz',
    ],
];
$eocDisasterCasecate = [
    '00100' => '路樹災情',
    '00200' => '廣告招牌災情',
    '00300' => '道路、隧道災情',
    '00400' => '橋梁災情',
    '00500' => '鐵路、高鐵及捷運災情',
    '00600' => '積淹水災情',
    '00700' => '土石災情',
    '00800' => '建物毀損',
    '00900' => '水利設施災害',
    '01000' => '民生、基礎設施災情',
    '01100' => '車輛及交通事故',
    '01200' => '環境汙染',
    '01300' => '火災',
    '09900' => '其他災情',
];
$taiwanGeocodeTpe = [
    '6300100' => '松山區',
    '6300200' => '信義區',
    '6300300' => '大安區',
    '6300400' => '中山區',
    '6300500' => '中正區',
    '6300600' => '大同區',
    '6300700' => '萬華區',
    '6300800' => '文山區',
    '6300900' => '南港區',
    '6301000' => '內湖區',
    '6301100' => '士林區',
    '6301200' => '北投區',
];
