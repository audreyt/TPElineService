<?php
/**
 * 定義LINE API所需變數
 */
global $lineBotConfig, $lineConst, $lineApi, $lineTrialConfig;
// if using trail account
$lineTrialConfig = [
    'channelId' => 'your line channel id',
    'channelSecret' => 'your line channel secret',
    'channelMid' => 'your line channel mid',
];
// if using business connect account
$lineBotConfig = [
    'channelId' => 'your line channel id',
    'channelSecret' => 'your line channel secret',
];
$lineConst = [
    'contentType' => [
        'Text' => 1,
        'Image' => 2,
        'Video' => 3,
        'Audio' => 4,
        'Location' => 7,
        'Sticker' => 8,
        'Contact' => 10,
    ],
    'eventType' => [
        'Message' => '138311609000106303',
        'Operation' => '138311609100106403',
        'OutgoingMessage' => '138311608800106203',
        'OutgoingMultiMessage' => '140177271400161403',
        'LinkMessage' => '137299299800026303',
    ],
    'operationType' => [
        'Friend' => 4,
        'Group' => 5,
        'Room' => 7,
        'Block' => 8,
    ],
    'toType' => [
        'User' => 1,
        'Room' => 2,
        'Group' => 3,
    ],
    'toChannel' => [
        'Message' => 1383378250,
        'MultiMessage' => 1383378250,
        'LinkMessage' => 1341301715,
    ],
];
$lineApi = [
    'sendMessage' => [
        'BC' => 'https://api.line.me/v1/events',
        'Trail' => 'https://trialbot-api.line.me/v1/events',
    ],
    'getUserProfile' => [
        'BC' => 'https://api.line.me/v1/profiles',
        'Trial' => 'https://trialbot-api.line.me/v1/profiles',
    ]
];
