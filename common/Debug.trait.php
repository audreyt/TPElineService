<?php
trait TDebugLog
{
    /**
     * @var mixed
     */
    private $path, $data, $prefix, $suffix;
    /**
     * @param $path
     * @param $data
     */
    public function setDebugInfo($path, $data)
    {
        $this->path = $path;
        $this->data = var_export($data, true);
        $this->prefix = PHP_EOL . '========[' . date('Y-m-d H:i:s') . ']========' . PHP_EOL;
        $this->suffix = PHP_EOL . '========================' . PHP_EOL;
    }
    public function saveDebugInfo()
    {
        file_put_contents($this->path, $this->prefix . $this->data . $this->suffix, FILE_APPEND | LOCK_EX);
    }
}
