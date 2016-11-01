<?php
abstract class Fetcher
{
    use TDebugLog;
    /**
     * @var mixed
     */
    protected $filePath, $extName, $fileName, $dbObj, $rawDataLength;
    /**
     * @var mixed
     */
    public $rawdata;
    /**
     * fetch any kind of ncdr data
     */
    abstract public function fetchData();
    /**
     * 抓取回來的資料非rawData時調用
     * @param $fpath
     * @param $fname
     * @param $fextname
     * @param $uri
     */
    public function setFileInfo($fpath, $fname, $fextname)
    {
        $this->filePath = $fpath;
        $this->fileName = $fname;
        $this->extName = $fextname;
    }
    /**
     * @param $datasetName
     * @param $uri
     */
    protected function fetchRawData($datasetName, $uri, $extName)
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
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HEADER => false,
        ];
        curl_setopt_array($curl, $options);

        $rst = curl_exec($curl);
        $info = curl_getinfo($curl);
        $error = curl_error($curl);

        curl_close($curl);
        if (!empty($error)) {
            return $error;
        }
        $dest = ROOT_PATH . DISPLAY_DATASET_PATH . $datasetName . '.' . $extName;
        if (!empty($rst)) {
            $file = fopen($dest, 'w');
            fputs($file, $rst);
            fclose($file);
            return true;
        }
        return false;
    }
}

/**
 * fetch all flood data of ncdr
 */
class NCDRFloodFetcher extends Fetcher
{
    private $topUri;

    public function setTopUri($uri)
    {
        $this->topUri = $uri;
    }
    public function fetchData()
    {
        $xmlData = simplexml_load_string(file_get_contents($this->topUri));
        $this->rawDataLength = count($xmlData->{'entry'});
        $rawDataURIs = [];
        for ($i = 0; $i < $this->rawDataLength; $i++) {
            foreach ($xmlData->{'entry'}[$i]->{'link'} as $link) {
                $rawDataURIs[] = (string) $link->attributes()[1];
            }
        }
        for ($j = 0; $j < $this->rawDataLength; $j++) {
            $rawData = simplexml_load_string(file_get_contents($rawDataURIs[$j]));
            if (!preg_match('/^630[0-9]*$/', $rawData->{'info'}->{'area'}->{'geocode'}->{'value'})) {
                continue;
            }
            // only tpe will be saved
            $rawData->asXML($this->filePath . $this->fileName . '_' . (string) $rawData->{'info'}->{'area'}->{'geocode'}->{'value'} . '.' . $this->extName);
        }
    }
}

/**
 * fetch all workschoolclose data of ncdr
 */
class NCDRWSCFetcher extends Fetcher
{
    private $topUri;

    public function setTopUri($uri)
    {
        $this->topUri = $uri;
    }

    public function fetchData()
    {
        $xmlData = simplexml_load_string(file_get_contents($this->topUri));

        $this->rawDataLength = count($xmlData->{'entry'});
        $rawDataURIs = [];
        for ($i = 0; $i < $this->rawDataLength; $i++) {
            foreach ($xmlData->{'entry'}[$i]->{'link'} as $link) {
                $rawDataURIs[] = (string) $link->attributes()[1];
            }
        }

        for ($j = 0; $j < $this->rawDataLength; $j++) {
            $rawData = simplexml_load_string(file_get_contents($rawDataURIs[$j]));
            if ($rawData->{'info'}->{'area'}->{'geocode'}->{'value'} !== '63') {
                continue;
            }
            $rawData->asXML($this->filePath . $this->fileName . '_' . (string) $rawData->{'info'}->{'area'}->{'geocode'}->{'value'} . '.' . $this->extName);
        }
    }
}
/**
 * fetch all workschoolclose data of ncdr
 */
class NCDRParkingFetcher extends Fetcher
{
    private $topUri;

    public function setTopUri($uri)
    {
        $this->topUri = $uri;
    }

    public function fetchData()
    {
        $xmlData = simplexml_load_string(file_get_contents($this->topUri));
        $this->rawDataLength = count($xmlData->{'entry'});
        $rawDataURIs = [];
        for ($i = 0; $i < $this->rawDataLength; $i++) {
            foreach ($xmlData->{'entry'}[$i]->{'link'} as $link) {
                $rawDataURIs[] = (string) $link->attributes()[1];
            }
        }
        $rawData = simplexml_load_string(file_get_contents(end($rawDataURIs)));
        $rawData->asXML($this->filePath . $this->fileName . '.' . $this->extName);
    }
}

/**
 * fetch all workschoolclose data of ncdr
 */
class NCDRWgateFetcher extends Fetcher
{
    private $topUri;

    public function setTopUri($uri)
    {
        $this->topUri = $uri;
    }

    public function fetchData()
    {
        $xmlData = simplexml_load_string(file_get_contents($this->topUri));
        $this->rawDataLength = count($xmlData->{'entry'});
        $rawDataURIs = [];
        for ($i = 0; $i < $this->rawDataLength; $i++) {
            foreach ($xmlData->{'entry'}[$i]->{'link'} as $link) {
                $rawDataURIs[] = (string) $link->attributes()[1];
            }
        }
        $rawData = simplexml_load_string(file_get_contents(end($rawDataURIs)));
        $rawData->asXML($this->filePath . $this->fileName . '.' . $this->extName);
    }
}
/**
 * fetch all eocdisaster data
 */
class EOCFetcher extends Fetcher
{
    public function fetchData()
    {
        global $taiwanGeocodeTpe, $uriConfig;
        foreach ($taiwanGeocodeTpe as $code => $area) {
            $this->fetchRawData('eoc_disaster_' . $code, $uriConfig['eoc_disaster'] . $area, 'json');
        }
    }
}
/**
 * fetch all airbox data
 */
class AirboxFetcher extends Fetcher
{
    private $topUri;

    public function setTopUri($uri)
    {
        $this->topUri = $uri;
    }

    public function fetchData()
    {
        $this->fetchRawData($this->fileName, $this->topUri, 'gz');
    }
}
