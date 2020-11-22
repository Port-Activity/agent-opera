<?php
namespace SMA\PAA\AGENT\OPERA;

class SshConnection
{
    public $results;

    public function exec(string $req)
    {
        if ($req === "ls opera/in") {
            return "MOUNKETDTestGood.csv\nMOUNKETDTestBad.csv";
        } elseif ($req === "cat 'opera/in/MOUNKETDTestGood.csv'") {
            return "SPAARNEGRACHT;9202558;20193112084125;20200201230500\n";
        } elseif ($req === "cat 'opera/in/MOUNKETDTestBad.csv'") {
            return "dummy";
        }
    }
}
