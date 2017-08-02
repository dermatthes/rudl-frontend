<?php
/**
 * Created by PhpStorm.
 * User: matthes
 * Date: 18.02.17
 * Time: 20:36
 */

namespace Rudl;


use MongoDB\Client;

interface UdpServerProcessor
{

    /**
     * Return tow-character Message ID
     * @return string
     */
    public function installDb(Client $mongoDb);

    public function getMessageId() : string;

    public function injectMessage($senderIp, $senderPort, array $message);

    public function flush();

    public function processData(int $flushTimestamp, Client $mongoDb);

}