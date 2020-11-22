<?php

namespace SMA\PAA\AGENT\OPERA;

use PHPUnit\Framework\TestCase;

final class OperaParserTest extends TestCase
{
    public function testParsingEtd(): void
    {
        $parser = new OperaParser();
        $this->assertEquals([
            "imo" => 9202558,
            "vessel_name" => "SPAARNEGRACHT",
            "state" => "Departure_Vessel_Berth",
            "time_type" => "Estimated",
            "time" => "2020-01-02T21:05:00+0000", //20200201230500
            "payload" => []
        ], $parser->parse("MOUNKETD.csv", file_get_contents(__DIR__ . "/MOUNKETD.csv")));
    }
    public function testParsingOps(): void
    {
        $parser = new OperaParser();
        $this->assertEquals([
            "imo" => 9202558,
            "vessel_name" => "SPAARNEGRACHT",
            "state" => "CargoOp_Commenced",
            "time_type" => "Actual",
            "time" => "2019-12-30T05:00:00+0000", // 20193012070000
            "payload" => []
        ], $parser->parse("MOUNKOPS.csv", file_get_contents(__DIR__ . "/MOUNKOPS.csv")));
    }
    public function testParsingOpe(): void
    {
        $parser = new OperaParser();
        $this->assertEquals([
            "imo" => 9202558,
            "vessel_name" => "SPAARNEGRACHT",
            "state" => "CargoOp_Completed",
            "time_type" => "Actual",
            "time" => "2020-01-02T10:55:00+0000", // 20200201125500
            "payload" => []
        ], $parser->parse("MOUNKOPE.csv", file_get_contents(__DIR__ . "/MOUNKOPE.csv")));
    }
    public function testParsingOpeWhenTimeIsSummerTime(): void
    {
        $parser = new OperaParser();
        $this->assertEquals([
            "imo" => 9202558,
            "vessel_name" => "SPAARNEGRACHT",
            "state" => "CargoOp_Completed",
            "time_type" => "Actual",
            "time" => "2020-07-02T09:55:00+0000",
            "payload" => []
        ], $parser->parse("MOUNKOPE.csv", "SPAARNEGRACHT;9202558;20200207132648;20200207125500"));
    }
    public function testParsingEtdWhenBadCharsOnVesselName(): void
    {
        $parser = new OperaParser();
        $this->assertEquals([
            "imo" => 9436226,
            "vessel_name" => "VÄSTERBOTTEN",
            "state" => "Departure_Vessel_Berth",
            "time_type" => "Estimated",
            "time" => "2020-04-08T07:20:00+0000", // 20200804102000
            "payload" => []
        ], $parser->parse("MOUNKETD-messy-chars.csv", file_get_contents(__DIR__ . "/MOUNKETD-messy-chars.csv")));
    }
}
