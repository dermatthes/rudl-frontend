<?php
/**
 * Created by PhpStorm.
 * User: matthes
 * Date: 02.08.17
 * Time: 15:34
 */

namespace Rudl\App\Plugins\UdpServer\Processor;


use MongoDB\BSON\UTCDateTime;
use MongoDB\Client;
use Rudl\App\Plugins\UdpServer\UdpServerProcessor;

class LogProcessor implements UdpServerProcessor
{


    private $buffer = [];

    /**
     * Return tow-character Message ID
     *
     * @return string
     */
    public function installDb(Client $mongoDb)
    {
        $col = $mongoDb->selectCollection("Rudl", "Log");
        $col->createIndex(["sysId" => 1, "timestamp" => 1]);
        $col->createIndex(["type" => 1, "timestamp" => 1]);
        $col->createIndex(["account" => 1, "timestamp" => 1]);
        $col->createIndex(["timestamp" => 1]);
    }

    public function getMessageId(): string
    {
        return 12;
    }

    public function injectJsonMessage($senderIp, $senderPort, array $message)
    {
        $_sysId = (string)$message[0];

        $_hostname = (string)$message[1];
        $_accountId = (string)$message[2];
        $_clientIp = (string)$message[3];
        $_request = (string)$message[4];
        $_scriptDuration = (float)$message[5];
        $_type = (string)$message[6];
        $_msg = (string)$message[7];
        $_code = (string)$message[8];
        $_filename = (string)$message[9];
        $_line = (int)$message[10];
        $_text = (string)$message[11];

        $this->buffer[] = [
            "timestamp" => new UTCDateTime((int)(microtime(true) * 1000)),
            "sysId" => $_sysId,
            "clientIp" => $_clientIp,
            "account" => $_accountId,
            "hostname" => $_hostname,
            "request" => $_request,
            "duration_sec" => $_scriptDuration,
            "type" => $_type,
            "msg" => $_msg,
            "code" => $_code,
            "filename" => $_filename,
            "line" => $_line,
            "text" => $_text
        ];
    }

    public function flush()
    {
        unset ($this->buffer);
        $this->buffer = [];
    }





    public function processData(int $flushTimestamp, Client $mongoDb)
    {

        if (count ($this->buffer) > 0) {
            $mongoDb->selectCollection("Rudl", "Log")
                ->insertMany($this->buffer);
        }
    }

    public function injectStringMessage($senderIp, $senderPort, string $message)
    {
        // Not used
    }

    public function concentrateData(Client $mongoDb)
    {
        $mongoDb->selectCollection("Rudl", "Log")
            ->deleteMany([ "timestamp" => [ "\$lt" => new UTCDateTime(strtotime("-8 days") * 1000) ] ]);
    }
}