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

    /**
     * @var UdpServerProcessor[]
     */
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


    private $mStats = [];

    private function resetStats() {

    }

    public function run($flushInterval = 5) {
        $lastFlush = time();
        $pid = false;
        while(1)
        {
            //Receive some data
            $recLen = socket_recvfrom($this->mSock, $buf, 8024, MSG_DONTWAIT, $remote_ip, $remote_port);
            if ($recLen == 0) {
                usleep(100);
            } else {
                $_header = substr($buf, 0, 32);
                $_body = substr($buf, 32);
                $_msgType = substr($_header, 0, 2);
                if (isset ($this->mProcessors[$_msgType])) {
                    $this->mProcessors[$_msgType]->injectMessage($remote_ip, $remote_port, $_body);
                }
            }

            if (time() - $lastFlush < $flushInterval) {
                continue;
            }


            $pid = pcntl_fork();
            if ($pid == -1) {
                throw new \Exception("Cannot fork!");
            } else if ($pid) {
                foreach ($this->mProcessors as $key => $value) {
                    $value->flush();
                }
                echo "\nParent Process - resetting buffer";
            } else {
                // Child Process
                foreach ($this->mProcessors as $key => $value) {
                    $value->processData();
                }
                exit (0);
            }




            //sleep(1);
            //Send back the data to the client
            //socket_sendto($sock, "OK " . $buf , 100 , 0 , $remote_ip , $remote_port);

        }


    }




}