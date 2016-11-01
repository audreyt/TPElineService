<?php
require __DIR__ . '/../config/Global.config.php';
include ROOT_PATH . '/config/Line.config.php';
include ROOT_PATH . '/common/Common.php';

global $lineBotConfig;
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    requestFail(405);
}

$header = getallheaders();
$lineBody = file_get_contents('php://input');
$mac = base64_encode(hash_hmac("sha256", $lineBody, utf8_encode($lineBotConfig['channelSecret']), true));
if ($mac !== $header['X-Line-ChannelSignature']) {
    requestFail(401);
}
$lineBody = json_decode($lineBody, true);
if (empty($lineBody['result'])) {
    exit('empty result body');
}
try {
    global $lineConst;
    foreach ($lineBody['result'] as $row) {
        if ($row['eventType'] === $lineConst['eventType']['Operation'] &&
            $row['content']['opType'] === $lineConst['operationType']['Friend']) {
            saveMemberInfoToDB($row['content']['from']);
            continue;
            // add as friend
        } elseif ($row['eventType'] == $lineConst['eventType']['Message'] &&
            $row['content']['toType'] == $lineConst['toType']['User'] &&
            $row['content']['contentType'] == $lineConst['contentType']['Text']) {
            callingWS(
                API_HOST . SRU . 'addMessage/',
                'POST',
                [
                    'authToken' => AUTH_TOKEN,
                    'msgId' => $row['content']['id'],
                    'memberId' => $row['content']['from'],
                    'payload' => $row['content']['text'],
                    'sendAt' => ceil($row['content']['createdTime'] / 1000),
                    'rawdata' => json_encode($lineBody),
                ]
            );
            // }
            saveMemberInfoToDB($row['content']['from']);
            // 傳入訊息不包含taipei則略過
            continue;
        }
    }
    // return to line-BOT-server immediately
    http_response_code(200);
} catch (PDOException $e) {
    requestFail(500);
}

/**
 * @param $mid
 */
function saveMemberInfoToDB($mid)
{
    global $lineApi, $lineBotConfig, $lineConst;
    // ts => change if get is cached by browsers
    $memberIsExists = callingWS(API_HOST . SRU . 'listMember/', 'GET', ['memberId' => $mid, 'authToken' => AUTH_TOKEN, 'ts' => time()]);
    $memberIsExists = json_decode($memberIsExists, true);
    if ($memberIsExists['result'] === false) {
        $memberInfo = getLineUserProfile($lineApi['getUserProfile']['BC'] . '?mids=' . $mid);
        $memberInfo = json_decode($memberInfo, true);
        callingWS(
            API_HOST . SRU . 'addMember/',
            'POST',
            [
                'authToken' => AUTH_TOKEN,
                'memberId' => $memberInfo['contacts'][0]['mid'],
                'displayName' => $memberInfo['contacts'][0]['displayName'],
                'puctureUrl' => $memberInfo['contacts'][0]['pictureUrl'],
                'statusMessage' => $memberInfo['contacts'][0]['statusMessage'],
            ]
        );
    }
}
