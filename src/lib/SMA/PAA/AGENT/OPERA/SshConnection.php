<?php
namespace SMA\PAA\AGENT\OPERA;

class SshConnection
{
    private $hostname;
    private $username;
    private $password;
    private $connection;
    public function __construct(string $hostname, string $username, string $password)
    {
        if (!$hostname) {
            throw new \Exception("Missing hostname");
        }
        if (!$username) {
            throw new \Exception("Missing username");
        }
        $this->hostname = $hostname;
        $this->username = $username;
        $this->password = $password;
    }
    private function connect()
    {
        $connection = ssh2_connect($this->hostname, 22);
        ssh2_auth_password($connection, $this->username, $this->password);
        return $connection;
    }
    private function connection()
    {
        if (!$this->connection) {
            $this->connection = $this->connect();
        }
        return $this->connection;
    }
    public function exec($command)
    {
        $connection = $this->connection();
        $stream = ssh2_exec($connection, $command);
        $errorStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
        stream_set_blocking($errorStream, true);
        stream_set_blocking($stream, true);
        //TODO: error handling
        $errors = stream_get_contents($errorStream);
        if ($errors) {
            throw new \Exception("SSH errors: " . $errors);
        }
        return stream_get_contents($stream);
    }
}
