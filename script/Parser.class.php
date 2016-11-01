<?php
require ROOT_PATH . '/common/DbAccess.class.php';

abstract class Parser
{
    use TDebugLog;
    /**
     * @var mixed
     */
    protected $filePath, $extName, $fileName, $dbObj, $debugger;
    /**
     * @var mixed
     */
    public $rawdata;
    /**
     * parsing ncdr data into json
     * @param $data
     */
    abstract public function parseData();
    public function __construct()
    {
        $this->dbObj = new PdoDatabase(DB_NAME);
    }

    /**
     * get files to parse
     * @param $fpath
     * @param $fname
     * @param $fextname
     * @return mixed
     */
    public function getRawData($fpath, $fname, $fextname)
    {
        $this->filePath = $fpath;
        $this->fileName = $fname;
        $this->extName = strtolower($fextname);
        switch ($this->extName) {
            case 'xml':
            case 'cap':
                return $this->getXMLFile();
            case 'json':
                return $this->getJSONFile();
            default:
                $this->setDebugInfo(ROOT_PATH . '/logs/parser.class.log', 'nothing to parse');
                $this->saveDebugInfo();
                break;
        }
    }
    /**
     * save data into database
     * @param $did
     * @param $ac
     * @param $info
     */
    public function saveToDB($did, $ac, $info, $tn)
    {
        if ($tn === 'dataset_to_push') {
            // change push state to 0
            $this->changeIsPushed($did, $ac);
        }
        // check if dataset already exists
        $listDatasetQuery = "SELECT * FROM `" . $tn . "` WHERE `id` = :did AND `area_code` = :ac;";
        $this->dbObj->prepareQuery($listDatasetQuery);
        $this->dbObj->bindSingleParam(':did', $did);
        $this->dbObj->bindSingleParam(':ac', $ac);
        $datasetIsExists = $this->dbObj->getQuery();

        $query = (!empty($datasetIsExists)) ? "UPDATE `" . $tn . "` SET `info_to_show` = :info, `changed_at` = NOW() WHERE `id` = :did AND `area_code` = :ac;" : "INSERT INTO `" . $tn . "` VALUES(:did, :ac, :info, NOW(), NOW());";

        $this->dbObj->prepareQuery($query);
        $this->dbObj->bindMultiParams([
            ':did' => $did,
            ':ac' => $ac,
            ':info' => $info,
        ]);

        $this->dbObj->doQuery();
    }

    /**
     * updage member's push state to 0
     * if member's push state = 1 or dataset's last changed time 30min ago
     * @param $did
     * @param $detail
     */
    protected function changeIsPushed($did, $detail)
    {
        // 修改pushed = 1 or 更新時間 - 最後更新時間> 30分鐘
        $midQuery = "UPDATE `subscription_container`
                     SET `is_pushed` = 0, `changed_at` = NOW()
                     WHERE `dataset_id` = :did
                     AND (`detail` LIKE :dal OR `detail` IS NULL)
                     AND (`is_pushed` = 1
                     OR ((" . time() . " - UNIX_TIMESTAMP(`changed_at`) > 1800)));";
        $this->dbObj->prepareQuery($midQuery);
        $this->dbObj->bindSingleParam(':did', $did);
        $this->dbObj->bindSingleParam(':dal', '%' . $detail . '%');

        $this->dbObj->doQuery();
    }

    /**
     * @return mixed
     */
    protected function getXMLFile()
    {
        $this->rawdata = simplexml_load_file($this->filePath . $this->fileName . '.' . strtolower($this->extName), 'SimpleXMLElement', LIBXML_NOWARNING);
        if (!$this->rawdata) {
            $this->setDebugInfo(ROOT_PATH . '/logs/parser.XML.log', 'No file named ' . $this->fileName . '.' . $this->extName);
            $this->saveDebugInfo();
            return false;
        } else {
            return $this->rawdata;
        }
    }
    /**
     * @return mixed
     */
    protected function getJSONFile()
    {
        $this->rawdata = file_get_contents($this->filePath . $this->fileName . '.' . strtolower($this->extName));
        if (!$this->rawdata) {
            $this->setDebugInfo(ROOT_PATH . '/logs/parser.JSON.log', 'No file named ' . $this->fileName . '.' . $this->extName);
            $this->saveDebugInfo();
        } else {
            return $this->rawdata;
        }
    }
}
/**
 * parse ncdr-flood display-data into json
 */
class NCDRDFloodParser extends Parser
{

    /**
     * @var mixed
     */
    private $datasetId, $areaCode, $info, $tblName;
    public function __construct()
    {
        $this->tblName = 'dataset_to_display';
        $this->datasetId = 'ncdr_flood';
        parent::__construct();
    }
    /**
     * parsing ncdr-flood data
     * @param $xmlData
     * @return mixed
     */
    public function parseData()
    {
        foreach ($this->rawdata->{'info'} as $info) {
            $dataToDB = [];
            $this->info = [];
            $this->areaCode = (string) $info->{'area'}->{'geocode'}->{'value'};
            $this->info['areaName'] = (string) $info->{'area'}->{'areaDesc'};
            $this->info['message'] = (string) $info->{'description'};
            $dataToDB['result'] = $this->info;
            $this->saveToDB($this->datasetId, $this->areaCode, json_encode($dataToDB), $this->tblName);
        }
    }
    /**
     * 由於取回的資料可能沒有台北市，需要讓DB有對應的淹水項目資料才能於channelWeb正常顯示
     */
    public function saveEmptyData($ac)
    {
        $this->areaCode = $ac;
        $this->saveToDB($this->datasetId, $this->areaCode, '', $this->tblName);
    }
}

/**
 * parse ncdr-flood push-data into json
 */
class NCDRPFloodParser extends Parser
{

    /**
     * @var mixed
     */
    private $datasetId, $areaCode, $info, $tblName;
    public function __construct()
    {
        $this->tblName = 'dataset_to_push';
        $this->datasetId = 'ncdr_flood';
        parent::__construct();
    }
    /**
     * parsing ncdr-flood data
     * @param $xmlData
     * @return mixed
     */
    public function parseData()
    {
        foreach ($this->rawdata->{'info'} as $info) {
            $this->areaCode = (string) $info->{'area'}->{'geocode'}->{'value'};
            if (preg_match('/^630[0-9]*$/', $this->areaCode)) {
                $dataToDB = [];
                $this->info = [];
                $this->info['msgType'] = (string) $this->rawdata->{'msgType'};
                $this->info['areaName'] = (string) $info->{'area'}->{'areaDesc'};
                $this->info['message'] = (string) $info->{'description'};
                $dataToDB['result'] = $this->info;
                $this->saveToDB($this->datasetId, $this->areaCode, json_encode($dataToDB), $this->tblName);
            }
        }
    }
}

/**
 * parse ncdr-workschoolclose display-data into json, save into info_to_show
 */
class NCDRDWSCParser extends Parser
{

    /**
     * @var mixed
     */
    public $info;

    /**
     * parsing ncdr-flood data
     * @param $xmlData
     * @return mixed
     */
    public function parseData()
    {
        $this->info = [];
        foreach ($this->rawdata->{'info'} as $info) {
            $this->info['areaName'] = (string) $info->{'area'}->{'areaDesc'};
            $this->info['message'] = (string) $info->{'description'};
        }
        return $this->info;
    }
}

/**
 * parse ncdr-workschoolclose push-data into json, save into info_to_show
 */
class NCDRPWSCParser extends Parser
{

    /**
     * @var mixed
     */
    public $info;

    /**
     * parsing ncdr-flood data
     * @param $xmlData
     * @return mixed
     */
    public function parseData()
    {
        $this->info = [];
        foreach ($this->rawdata->{'info'} as $info) {
            $this->areaCode = (string) $info->{'area'}->{'geocode'}->{'value'};
            if ($this->areaCode === '63') {
                $this->info['areaName'] = (string) $info->{'area'}->{'areaDesc'};
                $this->info['message'] = (string) $info->{'description'};
            }
        }
        return $this->info;
    }
}
/**
 * parse ncdr-workschoolclose display-data into json, save into info_to_show
 */
class NCDRDWatergateParser extends Parser
{

    /**
     * @var mixed
     */
    public $info;

    /**
     * parsing ncdr-flood data
     * @param $xmlData
     * @return mixed
     */
    public function parseData()
    {
        $this->info = [];
        $this->info['title'] = (string) $this->rawdata->{'info'}->{'headline'};
        $this->info['description'] = (string) $this->rawdata->{'info'}->{'description'};
        foreach ($this->rawdata->{'info'} as $info) {
            $this->info['areaDetail'][$i]['gatePosition'] = (string) $this->rawdata->{'info'}->{'area'}[$i]->{'areaDesc'};
            $circle = explode(' ', $this->rawdata->{'info'}->{'area'}[$i]->{'circle'});
            $latlng = explode(',', $circle[0]);
            $this->info['circle']['center']['lat'] = (float) $latlng[0];
            $this->info['circle']['center']['lng'] = (float) $latlng[1];
            $this->info['circle']['radius'] = (float) $circle[1];
        }
        return $this->info;
    }
}
/**
 * parse ncdr-workschoolclose push-data into json, save into info_to_show
 */
class NCDRPWatergateParser extends Parser
{

    /**
     * @var mixed
     */
    public $info;

    /**
     * parsing ncdr-flood data
     * @param $xmlData
     * @return mixed
     */
    public function parseData()
    {
        $this->info = [];
        $this->info['msgType'] = (string) $this->rawdata->{'msgType'};
        $this->info['description'] = (string) $this->rawdata->{'info'}->{'description'};
        $len = count($this->rawdata->{'info'}->{'area'});
        for ($i = 0; $i < $len; $i++) {
            $this->info['areaDetail'][$i]['gatePosition'] = (string) $this->rawdata->{'info'}->{'area'}[$i]->{'areaDesc'};
            $circle = explode(' ', $this->rawdata->{'info'}->{'area'}[$i]->{'circle'});
            $center = explode(',', $circle[0]);
            $this->info['areaDetail'][$i]['circle']['center']['lat'] = (float) $center[0];
            $this->info['areaDetail'][$i]['circle']['center']['lng'] = (float) $center[1];
            $this->info['areaDetail'][$i]['circle']['radius'] = (float) $circle[1];
        }
        return $this->info;
    }
}
/**
 * parse ncdr-workschoolclose display-data into json, save into info_to_show
 */
class NCDRDParkingParser extends Parser
{

    /**
     * @var mixed
     */
    public $info;

    /**
     * parsing ncdr-flood data
     * @param $xmlData
     * @return mixed
     */
    public function parseData()
    {
        $i = 0;
        $this->info = [];
        $this->info['title'] = (string) $this->rawdata->{'info'}->{'headline'};
        $this->info['description'] = (string) $this->rawdata->{'info'}->{'description'};
        foreach ($this->rawdata->{'info'}->{'area'} as $info) {
            if (isset($this->rawdata->{'info'}->{'area'}->{'geocode'})) {
                continue;
            }
            $this->info['areaDetail'][$i]['areaName'] = (string) $info->{'areaDesc'};
            $polygon = (string) $info->{'polygon'};
            $polygon = explode(' ', $polygon);
            $this->info['areaDetail'][$i]['polygon'] = $polygon;
            $i++;
        }
        return $this->info;
    }
}

/**
 * parse ncdr-workschoolclose push-data into json, save into info_to_show
 */
class NCDRPParkingParser extends Parser
{

    /**
     * @var mixed
     */
    public $info;

    /**
     * parsing ncdr-flood data
     * @param $xmlData
     * @return mixed
     */
    public function parseData()
    {
        $i = 0;
        $this->info = [];
        $this->info['msgType'] = (string) $this->rawdata->{'msgType'};
        $this->info['description'] = (string) $this->rawdata->{'info'}->{'description'};
        foreach ($this->rawdata->{'info'}->{'area'} as $info) {
            if (isset($this->rawdata->{'info'}->{'area'}->{'geocode'})) {
                continue;
            }
            $this->info['areaDetail'][$i]['areaName'] = (string) $info->{'areaDesc'};
            $polygon = (string) $info->{'polygon'};
            $polygon = explode(' ', $polygon);
            $this->info['areaDetail'][$i]['polygon'] = $polygon;
            $i++;
        }
        return $this->info;
    }
}
/**
 * parse ncdr-workschoolclose push-data into json, save into info_to_show
 */
class EOCDisasterParser extends Parser
{

    /**
     * @var mixed
     */
    private $datasetId, $areaCode, $info, $tblName;

    public function __construct()
    {
        $this->datasetId = 'eoc_disaster';
        $this->tblName = 'dataset_to_display';
        parent::__construct();
    }
    /**
     * parsing ncdr-flood data
     * @param $xmlData
     * @return mixed
     */
    public function parseData()
    {
        global $taiwanGeocodeTpe;
        $this->info = [];
        $this->areaCode = '';
        $disasterCnt = [
            '00100' => 0,
            '00200' => 0,
            '00300' => 0,
            '00400' => 0,
            '00500' => 0,
            '00600' => 0,
            '00700' => 0,
            '00800' => 0,
            '00900' => 0,
            '01000' => 0,
            '01100' => 0,
            '01200' => 0,
            '01300' => 0,
            '09900' => 0,
        ];
        $data = json_decode($this->rawdata, true);
        foreach ($data as $detail) {
            if (empty($this->areaCode)) {
                $this->areaCode = (string) array_search($detail['CaseLocationDistrict'], $taiwanGeocodeTpe);
            }
            switch ($detail['PName']) {
                case '路樹災情':
                    $disasterCnt['00100']++;
                    break;
                case '廣告招牌災情':
                    $disasterCnt['00200']++;
                    break;
                case '道路、隧道災情':
                    $disasterCnt['00300']++;
                    break;
                case '橋梁災情':
                    $disasterCnt['00400']++;
                    break;
                case '鐵路、高鐵及捷運災情':
                    $disasterCnt['00500']++;
                    break;
                case '積淹水災情':
                    $disasterCnt['00600']++;
                    break;
                case '土石災情':
                    $disasterCnt['00700']++;
                    break;
                case '建物毀損':
                    $disasterCnt['00800']++;
                    break;
                case '水利設施災害':
                    $disasterCnt['00900']++;
                    break;
                case '民生、基礎設施災情':
                    $disasterCnt['01000']++;
                    break;
                case '車輛及交通事故':
                    $disasterCnt['01100']++;
                    break;
                case '環境汙染':
                    $disasterCnt['01200']++;
                    break;
                case '火災':
                    $disasterCnt['01300']++;
                    break;
                case '其他災情':
                    $disasterCnt['09900']++;
                    break;
            }
        }
        $this->info['result'] = $disasterCnt;
        $this->info = json_encode($this->info);
        $this->saveToDB($this->datasetId, $this->areaCode, $this->info, $this->tblName);
    }
}

/**
 * parse ncdr-workschoolclose push-data into json, save into info_to_show
 */
class AirboxDParser extends Parser
{

    /**
     * @var mixed
     */
    private $info, $areaCode, $airboxData, $airboxDeviceData, $deviceInfoList, $tpeSchoolsList;

    public function __construct()
    {
        $this->datasetId = 'airbox';
        $this->tblName = 'dataset_to_display';
        parent::__construct();
    }

    /**
     * @param $fpath
     * @param $fname
     * @param $outExt
     */
    public function uncompressGZ($fpath, $fname, $outExt)
    {

        $bufferSize = 4096; // read 4kb at a time
        $outFileName = $fname . '.' . $outExt;

        $fname .= '.gz';

        $file = gzopen($fpath . $fname, 'rb');
        $outFile = fopen($fpath . $outFileName, 'wb');

        while (!gzeof($file)) {
            fwrite($outFile, gzread($file, $bufferSize));
        }

        fclose($outFile);
        gzclose($file);
    }

    /**
     * @param $abd
     * @param $abdd
     * @param $di
     * @param $tscl
     */
    public function setupData($abd, $abdd, $di, $tscl)
    {
        $this->airboxData = $abd;
        $this->airboxDeviceData = $abdd;
        $this->deviceInfoList = $di;
        $this->tpeSchoolsList = $tscl;
    }
    /**
     * parsing ncdr-flood data
     * @param $xmlData
     * @return mixed
     */
    public function parseData()
    {
        global $taiwanGeocodeTpe;

        $rst = [];
        $this->airboxData = json_decode($this->airboxData, true);
        $this->airboxDeviceData = json_decode($this->airboxDeviceData, true);
        /**
         * append school district to devicesList
         */
        $deviceInfoLen = count($this->deviceInfoList);
        for ($i = 0; $i < $deviceInfoLen; $i++) {
            foreach ($this->tpeSchoolsList as $scl) {
                if (strpos($this->deviceInfoList[$i]['devicePos'], $scl['schoolName']) > -1) {
                    // 需修改學校名稱如下
                    // 國語實小 => 國語實驗小學
                    // 私立靜心小學 => 私立靜心國民中小學
                    // 私立華興小學 => 華興國小
                    // 私立薇閣小學 => 薇閣國小
                    // 私立新民小學 => 新民國小
                    // 私立光仁小學 => 光仁國小
                    // 市大附小 => 台北市立大學附設實驗國小
                    // 私立復興實驗高中 => 私立復興實驗中學
                    $this->deviceInfoList[$i]['deviceDist'] = $scl['schoolDist'];
                    $this->deviceInfoList[$i]['gps']['lat'] = $scl['gps']['lat'];
                    $this->deviceInfoList[$i]['gps']['lng'] = $scl['gps']['lng'];
                }
            }
        }

        // 從小到大排序，assign時才會assign最新一筆
        usort($this->airboxData['entries'], function ($a, $b) {
            return strtotime($a['time']) > strtotime($b['time']);
        });
        $j = 0;
        for ($i = 0; $i < $deviceInfoLen; $i++) {
            // $rst[$j]['deviceId'] = $this->deviceInfoList[$i]['deviceId'];
            $rst[$j]['deviceName'] = preg_replace('/\r|\n/', '', $this->deviceInfoList[$i]['devicePos']);
            $rst[$j]['deviceDist'] = $this->deviceInfoList[$i]['deviceDist'];
            $rst[$j]['gps'] = $this->deviceInfoList[$i]['gps'];
            foreach ($this->airboxData['entries'] as $abd) {
                if ($abd['device_id'] === $this->deviceInfoList[$i]['deviceId']) {
                    $rst[$j]['recoreTime'] = $abd['time'];
                    $rst[$j]['pm25'] = $abd['s_d0'];
                }
            }

            foreach ($this->airboxDeviceData['devices'] as $device) {
                if ($device['device_id'] === $this->deviceInfoList[$i]['deviceId']) {
                    $rst[$j]['gps']['lat'] = $device['gps_lat'];
                    $rst[$j]['gps']['lng'] = $device['gps_lon'];
                }
            }
            $j++;
        }
        $rstLen = count($rst);
        foreach ($taiwanGeocodeTpe as $k => $v) {
            $toSave = $dataToDB = [];
            $j = 0;
            for ($i = 0; $i < $rstLen; $i++) {
                // save only those with pm25 data
                if ($v === $rst[$i]['deviceDist'] && isset($rst[$i]['pm25'])) {
                    $toSave[$j] = $rst[$i];
                    $j++;
                }
            }
            $dataToDB['result'] = $toSave;
            $this->info = json_encode($dataToDB);
            $this->areaCode = $k;
            $this->saveToDB($this->datasetId, $this->areaCode, $this->info, $this->tblName);
            // // 存入pushDB
            sleep(1);
            $this->saveToDB($this->datasetId, $this->areaCode, $this->info, 'dataset_to_push');
            $this->setDebugInfo(ROOT_PATH . '/logs/Pparser.airbox.log', $this->areaCode . ' : ' . $this->info);
            $this->saveDebugInfo();
        }
    }
}
