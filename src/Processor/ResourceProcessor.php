<?php
/**
 * Created by PhpStorm.
 * User: matthes
 * Date: 02.08.17
 * Time: 15:34
 */

namespace Rudl\Processor;


use MongoDB\BSON\ObjectID;
use MongoDB\BSON\Timestamp;
use MongoDB\Client;
use Rudl\UdpServerProcessor;

class ResourceProcessor implements UdpServerProcessor
{


    private $bufferBySysId = [];
    private $bufferByClientIp = [];
    private $bufferByAccount = [];


    /**
     * Return tow-character Message ID
     *
     * @return string
     */
    public function installDb(Client $mongoDb)
    {
        $col = $mongoDb->selectCollection("Rudl", "Resource_SysId");
        $col->createIndex(["sysId" => 1, "timestamp" => 1]);
        $col->createIndex(["timestamp" => 1]);

        $mongoDb->selectCollection("Rudl", "Resource_ClientIp");
        $col->createIndex(["sysId" => 1, "timestamp" => 1]);
        $col->createIndex(["timestamp" => 1]);

        $mongoDb->selectCollection("Rudl", "Resource_Account");
        $col->createIndex(["account" => 1, "timestamp" => 1]);
        $col->createIndex(["timestamp" => 1]);
    }

    public function getMessageId(): string
    {
        return 11;
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

    public function injectJsonMessage($senderIp, $senderPort, array $message)
    {
        $_sysId = $message[1];
        $_clientIp = $message[2];
        $_accountId = $message[4];
        $this->_fill($this->bufferBySysId[$_sysId], $message);
        $this->_fill($this->bufferByClientIp[$_clientIp], $message);
        $this->_fill($this->bufferByAccount[$_accountId], $message);
    }

    public function flush()
    {
        unset ($this->bufferBySysId);
        $this->bufferBySysId = [];

        unset ($this->bufferByAccount);
        $this->bufferByAccount = [];

        unset ($this->bufferByClientIp);
        $this->bufferByClientIp = [];
    }





    public function processData(int $flushTimestamp, Client $mongoDb)
    {
        $set = [];
        foreach ($this->bufferBySysId as $sysId => $value) {
            $rec = [
                "timestamp" => new Timestamp(0, $flushTimestamp),
                "sysId" => $sysId,
                "num_requests" => $value["num_requests"],
                "ru_utime_tv_sec" => (float)$value["ru_utime_tv_sec"],
                "ru_stime_tv_sec" => (float)$value["ru_stime_tv_sec"]
            ];
            $set[] = $rec;
        }
        if (count ($set) > 0) {
            $mongoDb->selectCollection("Rudl", "Resource_SysId")
                ->insertMany($set);
        }

        $set = [];
        foreach ($this->bufferByClientIp as $clientIp => $value) {
            $rec = [
                "timestamp" => new Timestamp(0, $flushTimestamp),
                "clientIp" => $clientIp,
                "num_requests" => $value["num_requests"],
                "ru_utime_tv_sec" => (float)$value["ru_utime_tv_sec"],
                "ru_stime_tv_sec" => (float)$value["ru_stime_tv_sec"]
            ];
            $set[] = $rec;
        }
        if (count ($set) > 0) {
            $mongoDb->selectCollection("Rudl", "Resource_ClientIp")
                ->insertMany($set);
        }

        $set = [];
        foreach ($this->bufferByAccount as $account => $value) {
            $rec = [
                "timestamp" => new Timestamp(0, $flushTimestamp),
                "accounty" => $account,
                "num_requests" => $value["num_requests"],
                "ru_utime_tv_sec" => (float)$value["ru_utime_tv_sec"],
                "ru_stime_tv_sec" => (float)$value["ru_stime_tv_sec"]
            ];
            $set[] = $rec;
        }
        if (count ($set) > 0) {
            $mongoDb->selectCollection("Rudl", "Resource_Account")
                ->insertMany($set);
        }
    }

    public function injectStringMessage($senderIp, $senderPort, string $message)
    {
        // Not used
    }
}