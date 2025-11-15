<?php

namespace Opengerp\App;

use mysqli;


class DbConfig
{

    private  string $db_hostname;
    private  string $db_username;
    private  string $db_password;
    private  string $db_name;
    private  int    $db_port;

    private function __construct(array $d)
    {

        $this->db_hostname   = $d['db_hostname']   ?? '127.0.0.1';
        $this->db_username   = (string) ($d['db_username'] ?? '');
        $this->db_password   = (string) ($d['db_password'] ?? '');
        $this->db_name   = (string) ($d['dbName_hot'] ?? '');
        $this->db_port   = (int) ($d['db_port'] ?? 3306);

    }

    public static function fromEnv(): self
    {
        return new self($_ENV);
    }

    public static function fromArray($vett): self
    {

        return new self($vett);
    }

    public function connect()
    {

        $db_hostname = $this->db_hostname;
        $db_username = $this->db_username;
        $db_password = $this->db_password;
        $db_name = $this->db_name;

        $mysqli = new mysqli($db_hostname, $db_username, $db_password, $db_name, $this->db_port);

        if ($mysqli->connect_errno) {
            throw new \RuntimeException('mysqli connection error: ' . $mysqli->connect_error);
        }

        return $mysqli;

    }



}