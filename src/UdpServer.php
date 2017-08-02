<?php
/**
 * Created by PhpStorm.
 * User: matthes
 * Date: 18.02.17
 * Time: 20:21
 */

namespace Rudl;


use MongoDB\Client;

class UdpServer
{


    private $mSock = false;

    /**
     * @var UdpServerProcessor[]
     */
    private $mProcessors = [];

    private $mMongoDbConStr;

    public function __construct(string $listenAddr = null, int $port=62111, $mongoDbConnectString="mongodb://localhost:27017")
    {
        if ($listenAddr == null) {
            $listenAddr = gethostbyname(gethostname());
        }
        $this->log("Listening on $listenAddr:$port");
        $this->mMongoDbConStr = $mongoDbConnectString;
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


    public function getMongoConnection() : Client {
        return new \MongoDB\Client("mongodb://localhost:27017");
    }


    public function addProcessor (UdpServerProcessor $processor) {
        $this->mProcessors[$processor->getMessageId()] = $processor;
        $this->log("Installing " . get_class($processor));
        $processor->installDb($this->getMongoConnection());
    }


    private $mStats = [];

    private function resetStats() {

    }

    private function log($msg, $level=0) {
        $warnMsg = "INFO";
        if ($level == 9) {
            $warnMsg = "ERROR";
        }
        $msg = "\n[" . date ("Y-m-d H:i:s") . "][$warnMsg]: $msg";
        echo $msg;
    }

    public function run($flushInterval = 5) {
        $lastFlush = time();
        $pid = false;
        while(1)
        {
            //Receive some data
            $recLen = socket_recvfrom($this->mSock, $buf, 8024, MSG_DONTWAIT, $remote_ip, $remote_port);
            if ($recLen == 0) {
                usleep(5000);
            } else {
                $msg = json_decode($buf, true);
                if ( ! is_array($msg)) {
                    $this->log("Invalid message from $remote_ip");
                } else {
                    //$this->log("Message in from $remote_ip.. $buf");
                    $_msgType = $msg[0];
                    // echo "\nIN: msgType: $_msgType, Body: $_body";
                    if (isset ($this->mProcessors[$_msgType])) {
                        $this->mProcessors[$_msgType]->injectMessage(
                            $remote_ip,
                            $remote_port,
                            $msg
                        );
                    }
                }
            }

            if (time() == $lastFlush || time() % $flushInterval !== 0) {
                continue;
            }
            $lastFlush = time();

            if ($pid !== false) {
                $exit = pcntl_waitpid($pid, $status, WNOHANG);
                if ($exit === 0) {
                    $this->log("Process still running. Waiting another round for it to complete.");
                    continue;
                }
                if ($exit != $pid) {
                    $this->log("Got wrong pid: $exit");
                }
                if ( ! pcntl_wifexited($status)) {
                    $this->log( "Got failed exit status for job $pid: Returned $status");
                }
            }

            $pid = pcntl_fork();
            if ($pid == -1) {
                throw new \Exception("Cannot fork!");
            } else if ($pid) {
                $this->log("Parent Process - resetting buffer");

                foreach ($this->mProcessors as $key => $value) {
                    $value->flush();
                }
            } else {
                // Child Process
                $mongoDb = $this->getMongoConnection();
                foreach ($this->mProcessors as $key => $value) {
                    $value->processData($lastFlush, $mongoDb);
                }
                exit (0);
            }
        }


    }




}