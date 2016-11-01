<?php
require __DIR__ . '/../libs/mobileDetect/Mobile_Detect.php';

$detect = new Mobile_Detect;
if ($detect->isMobile()) {
    $rst = true;
} else {
    $rst = false;
}
