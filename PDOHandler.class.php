<?php

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;

class PDOHandler extends AbstractProcessingHandler
{
    private $initialized = false;
    private $pdo;
    private $statement;

    public function __construct(PDO $pdo, $level = Logger::DEBUG, $bubble = true)
    {
        $this->pdo = $pdo;
        parent::__construct($level, $bubble);
    }

    protected function write(array $record)
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        $res = $this->statement->execute(array(
            'channel' => $record['channel'],
            'level' => $record['level'],
            'message' => $record['formatted'],
            'time' => $record['datetime']->format('c'),
        ));
    }

    private function initialize()
    {
        $prefix = cms_db_prefix();
        $this->statement = $this->pdo->prepare(
            "INSERT DELAYED INTO {$prefix}monolog (channel, level, message, time) VALUES (:channel, :level, :message, :time)"
        );

        $this->initialized = true;
    }
}