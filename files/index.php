<?php
/**
 * 接收NCDR推播資料
 **/

require __DIR__ . '/../config/Global.config.php';

$name = $_GET['name'];
$rawdata = file_get_contents('php://input');
$dest = ROOT_PATH . '/files/pushDataset/';
$isSuccess = (empty($rawdata)) ? 'false' : 'true';
if (!empty($name)) {
    header('Content-Type: text/xml');
    $xml = new DOMDocument('1.0', 'UTF-8');
    $xml->formatOutput = true;
    $xmlRoot = $xml->createElement('Data');
    $xmlStatus = $xml->createElement('Status');
    $xmlStatus->appendChild($xml->createTextNode($isSuccess));
    $xmlRoot->appendChild($xmlStatus);
    $xml->appendChild($xmlRoot);
    $returnToNcdr = $xml->saveXML();
    // save accessed data
    if ($isSuccess === 'true') {
        $file = fopen($dest . $name, 'w');
        fputs($file, $rawdata);
        fclose($file);
        // use for cron
        chdir(ROOT_PATH . '/files');
        exec('chmod -R 777 ./pushDataset/*');
        exec('chown -R root:root ./pushDataset/*');
    }
    echo $returnToNcdr;
}
