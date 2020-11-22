<?php
namespace SMA\PAA\AGENT;

require_once "init.php";

use DateTimeInterface;
use SMA\PAA\CURL\CurlRequest;
use SMA\PAA\RESULTPOSTER\ResultPoster;
use SMA\PAA\AGENT\OPERA\OperaAgent;
use SMA\PAA\AGENT\OPERA\SshConnection;
use SMA\PAA\AINO\AinoClient;
use Exception;

$ainoKey = getenv("AINO_API_KEY");
$aino = null;
if ($ainoKey) {
    $aino = new AinoClient($ainoKey, "Opera service", "Opera");
}
$ainoTimestamp = gmdate("Y-m-d\TH:i:s\Z");

if (getenv("ENABLED") !== "1") {
    echo "Agent is disabled.\n";
    if (isset($aino)) {
        $aino->failure($ainoTimestamp, "Opera agent is disabled", "Batch run", "timestamp", [], []);
    }
    exit(0);
};

$apiKey = getenv("API_KEY");
$apiUrl = getenv("API_URL");
$apiParameters = ["imo", "vessel_name", "time_type", "state", "time", "payload"];

$host = getenv("SSH_HOST");
$user = getenv("SSH_USER");
$password = getenv("SSH_PASSWORD");

$apiConfig = new ApiConfig($apiKey, $apiUrl, $apiParameters);
$ssh = new SshConnection($host, $user, $password);

$ainoForAgent = null;
if ($ainoKey) {
    $toApplication = parse_url($apiUrl, PHP_URL_HOST);
    $ainoForAgent = new AinoClient($ainoKey, "Opera", $toApplication);
}
$agent = new OperaAgent(new CurlRequest(), new ResultPoster(new CurlRequest()), $ssh, $ainoForAgent);
echo "Starting: " . date(DateTimeInterface::ISO8601) . "\n";
$counts = [];
try {
    $counts = $agent->execute($apiConfig);
    if (isset($aino)) {
        $aino->succeeded($ainoTimestamp, "Opera agent succeeded", "Batch run", "timestamp", [], $counts);
    }
} catch (\Exception $e) {
    error_log($e->getMessage());
    error_log($e->getTraceAsString());
    if (isset($aino)) {
        $aino->failure($ainoTimestamp, "Opera agent failed", "Batch run", "timestamp", [], []);
    }
}
echo "Ended: " . date(DateTimeInterface::ISO8601) . "\n";
echo json_encode($counts);
