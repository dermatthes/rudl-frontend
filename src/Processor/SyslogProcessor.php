<?php
/**
 * Created by PhpStorm.
 * User: matthes
 * Date: 18.02.17
 * Time: 21:26
 */



namespace Rudl\Processor;
use MongoDB\BSON\ObjectID;
use MongoDB\BSON\Timestamp;
use MongoDB\BSON\UTCDateTime;
use Rudl\UdpServerProcessor;

class SyslogProcessor implements UdpServerProcessor
{

    private $mMsgs = [];


    public function getMessageId(): string
    {
        return "01";
    }

    public function injectMessage($senderIp, $senderPort, $message)
    {
        $this->mMsgs[] = [$senderIp, $senderPort, $message];
    }

    public function flush()
    {
        $this->mMsgs = [];
    }


    private function msg2doc (string $input, $senderIp) {
        $d = json_decode($input, true);

        $res = [
            "_id" => new ObjectID(),
            "sysId" => $d[0],
            "time" => new UTCDateTime((new \DateTime())->format("U.u")),
            "host" => $senderIp,
            "type" => $d[2],
            "msg" => $d[3]
        ];
        return $res;
    }


    public function processData()
    {
        $client = new \MongoDB\Client("mongodb://localhost:27017");
        $collection = $client->selectCollection("UdpLog", "Syslog");

        $data = [];
        foreach ($this->mMsgs as $curMsg) {
            $data[] = $this->msg2doc($curMsg[2], $curMsg[0]);
        }
        if (count ($data) === 0)
            return;
        echo "Writing " . count ($data) . " datasets...";
        $collection->insertMany($data);
        echo "Done.";
    }
}