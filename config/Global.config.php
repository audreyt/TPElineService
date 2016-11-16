<?php
/**
 * 定義共用常數
 */
error_reporting(E_ALL &~E_NOTICE);
date_default_timezone_set('Asia/Taipei');
if (!defined('ROOT_PATH')) define('ROOT_PATH', '/path/to/docroot');
if (!defined('API_HOST')) define('API_HOST', '/path/to/api/host/docroot');
if (!defined('SRU')) define('SRU', '/restfulapi/v1/');
if (!defined('AUTH_TOKEN')) define('AUTH_TOKEN', '');
