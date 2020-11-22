<?php
namespace SMA\PAA\AGENT\OPERA;

use DateTime;
use DateTimeZone;

class OperaParser
{
    private function firstLineTokens(string $data)
    {
        $tokens = explode("\n", $data);
        return explode(";", $tokens[0]);
    }
    private function isoDateTime(string $nonIsoDateTime)
    {
        date_default_timezone_set("UTC");
        // Note: it is YdmHis and NOT YmdHis !!!
        // Note: timestamps are local Europe/Helsinki timestamps
        $date = DateTime::createFromFormat("YdmHis", $nonIsoDateTime, new DateTimeZone("Europe/Helsinki"));
        if ($date === false) {
            throw new \Exception("Invalid date time: " . $nonIsoDateTime);
        }
        $date->setTimezone(new DateTimeZone("UTC"));
        return $date->format(DateTime::ISO8601);
    }
    private function mapToFirstLine($data, $time_type, $state)
    {
        $tokens = $this->firstLineTokens($data);
        if (!isset($tokens[0]) || !isset($tokens[1]) || !isset($tokens[3])) {
            throw new \Exception("Missing data");
        }
        return [
            "vessel_name" => $tokens[0],
            "imo" => (int)$tokens[1],
            "time" => $this->isoDateTime($tokens[3]),
            "time_type" => $time_type,
            "state" => $state,
            "payload" => []
        ];
    }
    private function parseEtd(string $data)
    {
        return $this->mapToFirstLine($data, "Estimated", "Departure_Vessel_Berth");
    }
    private function parseOpe(string $data)
    {
        return $this->mapToFirstLine($data, "Actual", "CargoOp_Completed");
    }
    private function parseOps(string $data)
    {
        return $this->mapToFirstLine($data, "Actual", "CargoOp_Commenced");
    }
    public function parse($filename, $data): array
    {
        $data = iconv("latin1", "utf8", $data);
        $key = substr($filename, 5, 3);
        $parser = new OperaParser();
        $map = [
            "OPE" => "parseOpe",
            "OPS" => "parseOps",
            "ETD" => "parseEtd"
        ];
        if (isset($map[$key])) {
            $func = $map[$key];
            return $parser->$func($data);
        }
        throw new \Exception("Invalid data");
    }
}
