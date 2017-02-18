<?php


require __DIR__ . "/../vendor/autoload.php";


$srv = new \Rudl\UdpServer("192.168.2.100");
$srv->addProcessor(new \Rudl\Processor\SyslogProcessor());

$srv->run(5);