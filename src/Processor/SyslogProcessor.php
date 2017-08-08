<?php
/**
 * Created by PhpStorm.
 * User: matthes
 * Date: 03.08.17
 * Time: 12:41
 */

namespace Rudl\Processor;


use MongoDB\Client;
use Rudl\UdpServerProcessor;

class SyslogProcessor implements UdpServerProcessor
{

    private $buffer = [];


    /**
     * Return tow-character Message ID
     *
     * @return string
     */
    public function installDb(Client $mongoDb)
    {
        $col = $mongoDb->selectCollection("Rudl", "Syslog");
        $col->createIndex(["clientIp" => 1, "timestamp" => 1]);
        $col->createIndex(["hostname" => 1, "timestamp" => 1]);
        $col->createIndex(["clientIp" => 1, "timestamp" => 1]);
        $col->createIndex(["timestamp" => 1]);
        $col->createIndex(["$**" => "text"]);

    }

    public function getMessageId(): string
    {
        return "syslog";
    }

    private function _fill (&$var, array $message) {
        if ( ! isset ($var)) {
            $var = [
                "num_requests" => 0,
                "ru_utime_tv_sec" => 0.,
                "ru_stime_tv_sec" => 0.
            ];
        }
        $var["num_requests"]++;
        $var["ru_utime_tv_sec"] += $message[6];
        $var["ru_stime_tv_sec"] += $message[7];
    }

    public function injectJsonMessage($senderIp, $senderPort, array $message) { }

    public function injectStringMessage($senderIp, $senderPort, string $message)
    {
        if (($posx = strpos($message, ">")) === false)
            return false;

        $data = [
            "timestamp" => time(),
            "clientIp" => $senderIp,
            "syslogDate" => null,
            "hostname" => null,
            "system" => null,
            "facility" => null,
            "severity" => null,
            "message" => null
        ];

        $id = (int)substr($message, 1, $posx-1);
        $data["severity"] = $id % 8; // see https://tools.ietf.org/html/rfc3164#section-4.1.1
        $data["facility"] = ($id - $data["severity"]) / 8;

        $message = substr($message, $posx+1);

        $data["syslogDate"] = substr($message, 0, 15);
        $message = substr($message, 16);

        list($data["hostname"], $data["system"]) = explode(" ", substr($message, 0, $msgStartIndex = strpos($message, ":")));

        $data["message"] = substr($message, $msgStartIndex+2);

        $this->buffer[] = $data;
        return true;
    }

    public function flush()
    {
        unset ($this->buffer);
        $this->buffer = [];
    }

    public function processData(int $flushTimestamp, Client $mongoDb)
    {
        if (count ($this->buffer) > 0) {
            $mongoDb->selectCollection("Rudl", "Syslog")
                ->insertMany($this->buffer);
        }
    }


}