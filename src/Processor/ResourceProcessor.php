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
use MongoDB\BSON\UTCDateTime;
use MongoDB\Client;
use Rudl\UdpServerProcessor;

class ResourceProcessor implements UdpServerProcessor
{


    private $bufferBySysId = [];
    private $bufferByClientIp = [];
    private $bufferByAccount = [];
    private $bufferByRequest = [];

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

        $mongoDb->selectCollection("Rudl", "Resource_Request");
        $col->createIndex(["account" => 1, "timestamp" => 1]);
        $col->createIndex(["sysId" => 1, "timestamp" => 1]);
        $col->createIndex(["clientIp" => 1, "timestamp" => 1]);
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
        $var["ru_utime_tv_sec"] += (float)$message[5];
        $var["ru_stime_tv_sec"] += (float)$message[6];
    }

    public function injectJsonMessage($senderIp, $senderPort, array $message)
    {
        $_sysId = (string)$message[0];

        $_hostname = (string)$message[1];
        $_accountId = (string)$message[2];
        $_clientIp = (string)$message[3];
        $this->_fill($this->bufferBySysId[$_sysId], $message);
        $this->_fill($this->bufferByClientIp[$_clientIp], $message);
        $this->_fill($this->bufferByAccount[$_accountId], $message);

        $this->bufferByRequest[] = [
            "timestamp" => new UTCDateTime((int)(microtime(true) * 1000)),
            "sysId" => $_sysId,
            "clientIp" => $_clientIp,
            "account" => $_accountId,
            "ru_utime_tv_sec" => (float)$message[5],
            "ru_stime_tv_sec" => (float)$message[6],
            "request" => (string)$message[7],
            "duration_sec" => (float)$message[8]
        ];
    }

    public function flush()
    {
        unset ($this->bufferBySysId);
        $this->bufferBySysId = [];

        unset ($this->bufferByAccount);
        $this->bufferByAccount = [];

        unset ($this->bufferByClientIp);
        $this->bufferByClientIp = [];

        unset ($this->bufferByRequest);
        $this->bufferByRequest = [];
    }





    public function processData(int $flushTimestamp, Client $mongoDb)
    {
        $set = [];
        foreach ($this->bufferBySysId as $sysId => $value) {
            $rec = [
                "timestamp" => new UTCDateTime($flushTimestamp * 1000),
                "sysId" => $sysId,
                "num_requests" => (int)$value["num_requests"],
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
                "timestamp" => new UTCDateTime($flushTimestamp * 1000),
                "clientIp" => $clientIp,
                "num_requests" => (int)$value["num_requests"],
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
                "timestamp" => new UTCDateTime($flushTimestamp * 1000),
                "account" => $account,
                "num_requests" => (int)$value["num_requests"],
                "ru_utime_tv_sec" => (float)$value["ru_utime_tv_sec"],
                "ru_stime_tv_sec" => (float)$value["ru_stime_tv_sec"]
            ];
            $set[] = $rec;
        }
        if (count ($set) > 0) {
            $mongoDb->selectCollection("Rudl", "Resource_Account")
                ->insertMany($set);
        }


        if (count ($this->bufferByRequest) > 0) {
            $mongoDb->selectCollection("Rudl", "Resource_Request")
                ->insertMany($this->bufferByRequest);
        }
    }

    public function injectStringMessage($senderIp, $senderPort, string $message)
    {
        // Not used
    }
}