<?php
require_once __DIR__ . '/../common/DbAccess.class.php';
$dbObj = new PdoDatabase('linebot');
// get token to reissue
$query = 'SELECT * FROM `line_service_token`';
$dbObj->prepareQuery($query);
$tokenToReissue = $dbObj->getQuery();

$reissuingTokenAPI = 'https://api.line.me/v1/oauth/accessToken';
$header = [
    'Content-Type: application/x-www-form-urlencoded',
    'X-Line-ChannelToken: ' . $tokenToReissue[0]['access_token'],
];
$newToken = reissuingToken($reissuingTokenAPI, $header, ['refreshToken' => $tokenToReissue[0]['refresh_token']]);

$toDB = json_decode($newToken, true);

$query = 'UPDATE `line_service_token` SET `access_token` = :accessToken, `refresh_token` = :refreshToken, `created_at` = NOW(), `expired` = :expire;';

$dbObj->prepareQuery($query);
$dbObj->bindMultiParams([
    ':accessToken' => $toDB['accessToken'],
    ':refreshToken' => $toDB['refreshToken'],
    ':expire' => date('Y-m-d H:i:s', substr($toDB['expire'], 0, -3)),
]);

$dbObj->doQuery();
$dbObj->closeDbConn();
function reissuingToken($uri, $header, $data)
{
    $curl = curl_init();

    $options = [
        CURLOPT_URL => $uri,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_VERBOSE => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_AUTOREFERER => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_HEADER => true,
        CURLOPT_HTTPHEADER => $header,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($data),
    ];
    curl_setopt_array($curl, $options);

    $rst = curl_exec($curl);
    $info = curl_getinfo($curl);
    $error = curl_error($curl);
    // get header and content apart
    list($header, $content) = explode("\r\n\r\n", $rst, 2);
    curl_close($curl);
    if (!empty($error) || strpos($header, ' 200 ') < 0) {
        return $error;
    }
    if (!empty($rst)) {
        return $content;
    }
    return false;
}
