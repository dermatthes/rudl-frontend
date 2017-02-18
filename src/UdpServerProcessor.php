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

    /**
     * Return tow-character Message ID
     * @return string
     */
    public function getMessageId() : string;

    public function injectMessage($senderIp, $senderPort, $message);

    public function flush();

    public function processData();

}