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
        $col->createIndex(["sysId" => 1, "date_hod" => 1]);
        $col->createIndex(["sysId" => 1, "date_dow" => 1]);
        $col->createIndex(["sysId" => 1, "date_dom" => 1]);
        $col->createIndex(["sysId" => 1, "date_moy" => 1]);
        $col->createIndex(["sysId" => 1, "date_doy" => 1]);
        $col->createIndex(["sysId" => 1, "date_year" => 1]);
        $col->createIndex(["timestamp" => 1]);

        $mongoDb->selectCollection("Rudl", "Resource_ClientIp");
        $col->createIndex(["clientIp" => 1, "timestamp" => 1]);
        $col->createIndex(["clientIp" => 1, "date_hod" => 1]);
        $col->createIndex(["clientIp" => 1, "date_dow" => 1]);
        $col->createIndex(["clientIp" => 1, "date_dom" => 1]);
        $col->createIndex(["clientIp" => 1, "date_moy" => 1]);
        $col->createIndex(["clientIp" => 1, "date_doy" => 1]);
        $col->createIndex(["clientIp" => 1, "date_year" => 1]);
        $col->createIndex(["timestamp" => 1]);

        $mongoDb->selectCollection("Rudl", "Resource_Account");
        $col->createIndex(["account" => 1, "timestamp" => 1]);
        $col->createIndex(["account" => 1, "date_hod" => 1]);
        $col->createIndex(["account" => 1, "date_dow" => 1]);
        $col->createIndex(["account" => 1, "date_dom" => 1]);
        $col->createIndex(["account" => 1, "date_moy" => 1]);
        $col->createIndex(["account" => 1, "date_doy" => 1]);
        $col->createIndex(["account" => 1, "date_year" => 1]);
        $col->createIndex(["timestamp" => 1]);

        $mongoDb->selectCollection("Rudl", "Resource_Request");
        $col->createIndex(["account" => 1, "timestamp" => 1]);
        $col->createIndex(["timestamp" => 1]);
        $col->createIndex(["date_hod" => 1]);
        $col->createIndex(["date_dow" => 1]);
        $col->createIndex(["date_dom" => 1]);
        $col->createIndex(["date_moy" => 1]);
        $col->createIndex(["date_doy" => 1]);
        $col->createIndex(["date_year" => 1]);

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
            "date_hod" => (int)date ("G"), // hour of day
            "date_dow" => (int)date ("w"), // Day of week
            "date_dom" => (int)date ("j"), // Day of month
            "date_moy" => (int)date ("n"), // Month of year
            "date_doy" => (int)date ("z"), // Day of year 0-365
            "date_year" => (int)date ("Y"), // Year
            "sysId" => $_sysId,
            "clientIp" => $_clientIp,
            "account" => $_accountId,
            "hostname" => $_hostname,
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
                "date_hod" => (int)date ("G", $flushTimestamp), // hour of day
                "date_dow" => (int)date ("w", $flushTimestamp), // Day of week
                "date_dom" => (int)date ("j", $flushTimestamp), // Day of month
                "date_moy" => (int)date ("n", $flushTimestamp), // Month of year
                "date_doy" => (int)date ("z", $flushTimestamp), // Day of year 0-365
                "date_year" => (int)date ("Y", $flushTimestamp), // Year
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
                "date_hod" => (int)date ("G", $flushTimestamp), // hour of day
                "date_dow" => (int)date ("w", $flushTimestamp), // Day of week
                "date_dom" => (int)date ("j", $flushTimestamp), // Day of month
                "date_moy" => (int)date ("n", $flushTimestamp), // Month of year
                "date_doy" => (int)date ("z", $flushTimestamp), // Day of year 0-365
                "date_year" => (int)date ("Y", $flushTimestamp), // Year
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
                "date_hod" => (int)date ("G", $flushTimestamp), // hour of day
                "date_dow" => (int)date ("w", $flushTimestamp), // Day of week
                "date_dom" => (int)date ("j", $flushTimestamp), // Day of month
                "date_moy" => (int)date ("n", $flushTimestamp), // Month of year
                "date_doy" => (int)date ("z", $flushTimestamp), // Day of year 0-365
                "date_year" => (int)date ("Y", $flushTimestamp), // Year
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