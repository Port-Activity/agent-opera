<?php
namespace SMA\PAA\AGENT\OPERA;

use SMA\PAA\CURL\ICurlRequest;
use SMA\PAA\RESULTPOSTER\IResultPoster;
use SMA\PAA\AGENT\ApiConfig;
use SMA\PAA\AINO\AinoClient;
use Exception;

class OperaAgent
{
    private $curlRequest;
    private $resultPoster;
    private $ssh;
    private $aino;
    public function __construct(
        ICurlRequest $curlRequest,
        IResultPoster $resultPoster,
        SshConnection $ssh,
        AinoClient $aino = null
    ) {
        $this->curlRequest = $curlRequest;
        $this->resultPoster = $resultPoster;
        $this->ssh = $ssh;
        $this->aino = $aino;
    }

    public function getCsvFilenameFromServer(): ?array
    {
        $data = $this->ssh->exec("ls opera/in");
        $files = explode("\n", $data);
        return array_filter($files, function ($file) {
            return preg_match("/^MOUNK.*\.csv$/", $file);
        });
    }
    private function getData(string $file): string
    {
        return $this->ssh->exec("cat 'opera/in/$file'");
    }
    public function execute(ApiConfig $apiConfig)
    {
        $ainoTimestamp = gmdate("Y-m-d\TH:i:s\Z");
        $dir = date("Y-m-d");
        $this->ssh->exec("test -d failed/$dir || mkdir failed/$dir");
        $this->ssh->exec("date");
        $this->ssh->exec("test -d processed/$dir || mkdir processed/$dir");
        $files = $this->getCsvFilenameFromServer();
        $parser = new OperaParser();
        $countOk = 0;
        $countFailed = 0;
        foreach ($files as $filename) {
            $result = null;
            try {
                $result = $parser->parse($filename, $this->getData($filename));
            } catch (\Exception $e) {
                echo "Failed to parse $filename\n. Error: " . $e->getMessage() . "\n";
                echo "Moving file to failed dir.\n";
                $this->ssh->exec("mv opera/in/$filename failed/$dir");
                $countFailed++;
                if (isset($this->aino)) {
                    $this->aino->failure(
                        $ainoTimestamp,
                        "Opera agent failed",
                        "Parse",
                        "timestamp",
                        [],
                        ["file" => $filename]
                    );
                }
            }
            if ($result) {
                $ainoFlowId = $this->resultPoster->resultChecksum($apiConfig, $result);
                $resultPoster = $this->resultPoster;
                try {
                    $resultPoster->postResult($apiConfig, $result);
                    $this->ssh->exec("mv opera/in/$filename processed/$dir");
                    $countOk++;
                    if (isset($this->aino)) {
                        $this->aino->succeeded(
                            $ainoTimestamp,
                            "Opera agent succeeded",
                            "Post",
                            "timestamp",
                            ["imo" => $result["imo"]],
                            ["file" => $filename],
                            $ainoFlowId
                        );
                    }
                } catch (\Exception $e) {
                    error_log("Something unexpected happened: " . $e->getMessage());
                    $countFailed++;
                    if (isset($this->aino)) {
                        $this->aino->failure(
                            $ainoTimestamp,
                            "Opera agent failed",
                            "Post",
                            "timestamp",
                            [],
                            ["file" => $filename],
                            $ainoFlowId
                        );
                    }
                }
            }
        }
        return [
            "ok" => $countOk,
            "failed" => $countFailed
        ];
    }
}
