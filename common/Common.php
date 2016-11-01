<?php
/**
 * 處裡所有錯誤請求
 */
function requestFail($httpCode)
{
    switch ($httpCode) {
        case 500:
            $text = 'Internal server error';
            break;
        case 405:
            $text = 'Method not allowed';
            break;
        case 404:
            $text = 'Bad request';
            break;
        case 401:
            $text = 'Unauth­orized';
            break;
        case 400:
            $text = 'Bad request';
            break;
        default:
            // do nothing
            break;
    }
    http_response_code($httpCode);
    exit($text);
}

/**
 * @param $uri
 * @param array $uids
 * @param array $messageFormat
 * @param array $postData 傳送資料型態
 * @return mixed
 */
function messagesFromBot($uri, array $uids, array $messageFormat, array $messageType)
{
    $postData = [
        'to' => $uids,
        'toChannel' => $messageType['toChannel'],
        'eventType' => $messageType['eventType'],
        'content' => $messageFormat,
    ];

    // 機器人推播基本設定
    $headers = [
        'Content-Type: application/json;charset=UTF-8',
        'X-Line-ChannelToken:' . X_LINE_CHANNELTOKEN,
    ];

    $curl = curl_init($uri);
    $options = [
        CURLOPT_URL => $uri,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_VERBOSE => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_AUTOREFERER => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_HEADER => false,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POSTFIELDS => json_encode($postData),
    ];

    curl_setopt_array($curl, $options);

    $rst = curl_exec($curl);
    $info = curl_getinfo($curl);
    $error = curl_error($curl);
    // get header and content apart
    curl_close($curl);
    if (!empty($error) || strpos($header, ' 200 ') < 0) {
        return $error;
    }

    if (!empty($rst)) {
        return $rst;
    }
    return false;
}

/**
 * @param $uri
 * @param array $uids
 * @param array $messageFormat
 * @param array $postData 傳送資料型態
 * @return mixed
 */
function getLineUserProfile($uri)
{
    // 機器人推播基本設定
    $headers = [
        'Content-Type: application/json;charset=UTF-8',
        'X-Line-ChannelToken:' . X_LINE_CHANNELTOKEN,
    ];

    $curl = curl_init();

    $options = [
        CURLOPT_URL => $uri,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_VERBOSE => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_AUTOREFERER => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_HEADER => true,
        CURLOPT_HTTPHEADER => $headers,
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

/**
 * 呼叫寫好的WebServices
 * @param $uri
 * @param $method
 * @param $postData
 * @return mixed
 */
function callingWS($uri, $method, $sendData)
{
    $method = strtoupper($method);
    $curl = curl_init();
    $headers = ['Content-Type:application/json;charset=UTF-8'];
    $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_VERBOSE => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_AUTOREFERER => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_HEADER => true,
        CURLOPT_HTTPHEADER => $headers,
    ];
    switch ($method) {
        case 'GET':
            $sendData = http_build_query($sendData);
            $uri .= '?' . $sendData;
            break;
        case 'POST':
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = json_encode($sendData);
            break;
        case 'PUT':
            $options[CURLOPT_PUT] = true;
            $options[CURLOPT_POSTFIELDS] = json_encode($sendData);
            break;
        case 'DELETE':
            $options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
            $options[CURLOPT_POSTFIELDS] = json_encode($sendData);
            break;
        default:
            requestFail(400);
            break;
    }
    $options[CURLOPT_URL] = $uri;
    curl_setopt_array($curl, $options);

    $rst = curl_exec($curl);
    $info = curl_getinfo($curl);
    $error = curl_error($curl);
    // get header and content apart
    list($header, $content) = explode("\r\n\r\n", $rst, 2);
    curl_close($curl);
    if (!empty($error)) {
        return $error;
    }
    if (!empty($rst)) {
        return $content;
    }
    return false;
}
