<?php
/**
 * Created by PhpStorm.
 * User: matthes
 * Date: 18.02.17
 * Time: 20:21
 */

namespace Rudl;


class UdpServer
{


    private $mSock = false;

    private $mProcessors = [];

    public function __construct(string $listenAddr, int $port)
    {
        if( ! ($sock = socket_create(AF_INET, SOCK_DGRAM, 0))) {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);

            throw new \Exception("Cannot create socket $listenAddr:$port : $errormsg ($errorcode)");
        }

        if( !socket_bind($sock, $listenAddr , $port) ) {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);

            throw new \Exception("Could not bind socket  $listenAddr:$port : [$errorcode] $errormsg");
        }
        $this->mSock = $sock;
    }


    public function addProcessor (UdpServerProcessor $processor) {
        $this->mProcessors[$processor->getMessageId()] = $processor;
    }




    public function run() {
        while(1)
        {
            //Receive some data
            $r = socket_recvfrom($this->mSock, $buf, 8024, 0, $remote_ip, $remote_port);
            echo "$remote_ip : $remote_port -- " . " (" . strlen($buf). ")";

            $data[] = $buf;

            if (count($data) > 100) {
                echo "forking.";
                $pid = pcntl_fork();
                if ($pid == -1) {
                    throw new Exception("Cannot fork!");
                } else if ($pid) {
                    // ParentProcess
                    $data = [];
                    echo "Parent Process - resetting buffer";
                } else {
                    // Child Process
                    sleep (1);
                    echo "Child Buffer size: " . count ($data);
                    exit (0);
                }
            }



            //sleep(1);
            //Send back the data to the client
            //socket_sendto($sock, "OK " . $buf , 100 , 0 , $remote_ip , $remote_port);

        }


    }




}