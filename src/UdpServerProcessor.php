<?php
/**
 * Created by PhpStorm.
 * User: matthes
 * Date: 18.02.17
 * Time: 20:36
 */

namespace Rudl;


interface UdpServerProcessor
{

    public function getMessageId() : string;

    public function injectMessage($senderIp, $senderPort, $message);

    public function flush();

    public function processData();

}