<?php
require_once __DIR__ . '/../config/Global.config.php';
require_once ROOT_PATH . '/config/Script.config.php';

$dataToMove = [];
$pushDataFolder = scandir(ROOT_PATH . PUSH_DATASET_PATH, 1);
for ($i = 0; $i < 2; $i++) {
    // pop . & ..
    array_pop($pushDataFolder);
}
chdir(ROOT_PATH . PUSH_DATASET_PATH);
foreach ($pushDataFolder as $k => $v) {
    if (itsTimetomove(filemtime($v))) {
        array_push($dataToMove, $v);
    }
}
$dataToMove = implode(' ', $dataToMove);
exec('/bin/mv ' . $dataToMove . ' ' . ROOT_PATH . EXPIRED_DATASET_PATH);

function itsTimetomove($filetime)
{
    // mv files which file modify time > 30 minutes to prevent resend old data
    if ((time() - $filetime) >= 1800) {
        return true;
    }
    return false;
}
