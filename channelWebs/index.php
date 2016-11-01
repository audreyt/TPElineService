<?php
$toPage = [
    'fc' => 'floodControl/index.php',
    'pm' => 'floodControl/NCDRParkingMap.php',
    'wg' => 'floodControl/NCDRWatergateMap.php',
    'ap' => 'airPollutionInfo/index.php',
    'apsa' => 'airPollutionInfoLocal/activeSuggestion.php',
    'apm' => 'airPollutionInfoLocal/airMap.php',
];
if (empty($_GET) || !isset($_GET['page'])) {
    http_response_code(400);
    exit('Bad request');
}
if ($_GET['page'] === 'apm') {
    $toPage[$_GET['page']] .= '?ptc=' . $_GET['ptc'];
}

$goto = $toPage[$_GET['page']];
header('Location: ./' . $goto);
