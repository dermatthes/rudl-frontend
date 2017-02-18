<?php

require __DIR__ . "/../vendor/autoload.php";


$log = new \Rudl\UdpLogConnector("192.168.2.100");

$i =0;
while (1) {
    $i++;
    $log->setSysId("sys$i");
    $log->log("alksdjflaskdjflsakjdflksajdlfkj$i");
}