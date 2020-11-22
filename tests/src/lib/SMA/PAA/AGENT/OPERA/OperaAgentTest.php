<?php

namespace SMA\PAA\AGENT\OPERA;

use PHPUnit\Framework\TestCase;

use SMA\PAA\FAKECURL\FakeCurlRequest;
use SMA\PAA\FAKERESULTPOSTER\FakeResultPoster;
use SMA\PAA\AGENT\ApiConfig;

use SMA\PAA\AINO\AinoClient;

final class OperaAgentTest extends TestCase
{
    public function testExecute(): void
    {
        $fakeResultPoster = new FakeResultPoster();
        $agent = new OperaAgent(new FakeCurlRequest(), $fakeResultPoster, new SshConnection());
        $res = $agent->execute(new ApiConfig("key", "http://url/foo", ["foo"]));
        $expectedRes = ["ok" => 1, "failed" => 1];
        $expectedPost = [[
            "vessel_name" => "SPAARNEGRACHT",
            "imo" => 9202558,
            "time" => "2020-01-02T21:05:00+0000",
            "time_type" => "Estimated",
            "state" => "Departure_Vessel_Berth",
            "payload" => []
        ]];
        $this->assertEquals($expectedRes, $res);
        $this->assertEquals($expectedPost, $fakeResultPoster->results);
    }
}
