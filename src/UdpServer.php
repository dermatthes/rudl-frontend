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


    private $mLogLevel = 5;

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
        $this->log("Listening on $listenAddr:$port", 2);
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
        $this->log("Installing " . get_class($processor), 2);
        $processor->installDb($this->getMongoConnection());
    }


    private $mStats = [];

    private function resetStats() {

    }

    public function setLogLevel ($level) {
        $this->mLogLevel = $level;
    }

    private function log($msg, $level=9) {
        if ($level > $this->mLogLevel)
            return;
        $warnMsg = "DEBUG";
        if ($level <= 5) {
            $warnMsg = "INFO";
        }
        if ($level == 0) {
            $warnMsg = "ERROR";
        }
        $msg = "\n[" . date ("Y-m-d H:i:s") . "][$warnMsg]: $msg";
        echo $msg;
    }


    private function _processMessage ($message, $remoteIp, $remotePort) {
        $this->log("New Message from $remoteIp: $message", 5);
        if (substr($message, 0,1) === "G" && ($dpos = strpos($message, ":")) !== false) {
            $msgId = (int) substr($message, 1, ($dpos-1));
            $msg = substr($message, $dpos+1);
            if ( ! isset ($this->mProcessors[$msgId])) {
                return false;
            }
            $arrayMessage = json_decode($msg, true);
            if ( ! is_array($arrayMessage)) {
                $this->log("Invalid message from $remoteIp: $message",3);
                return false;
            }
            $this->mProcessors[$msgId]->injectJsonMessage(
                $remoteIp,
                $remotePort,
                $arrayMessage
            );
            return true;

        } else if (substr($message, 0, 1) == "<") {
            // Syslog
            if ( ! isset ($this->mProcessors["syslog"]))
                return false;

            $this->mProcessors["syslog"]->injectStringMessage($remoteIp, $remotePort, $message);
        } else {
            $this->log("Received garbage from $remoteIp: $message", 3);
            return false;
        }
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
                $this->_processMessage($buf, $remote_ip, $remote_port);
            }

            if (time() == $lastFlush || time() % $flushInterval !== 0) {
                continue;
            }
            $lastFlush = time();

            if ($pid !== false) {
                $exit = pcntl_waitpid($pid, $status, WNOHANG);
                if ($exit === 0) {
                    $this->log("Process still running. Waiting another round for it to complete.", 5);
                    continue;
                }
                if ($exit != $pid) {
                    $this->log("Got wrong pid: $exit", 5);
                }
                if ( ! pcntl_wifexited($status)) {
                    $this->log( "Got failed exit status for job $pid: Returned $status", 5);
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
                    $startTime = microtime(true);
                    $value->processData($lastFlush, $mongoDb);
                    $this->log("Processing " . get_class($value) . ": In " . round((microtime(true) - $startTime), 3) . " sec");
                }
                exit (0);
            }
        }


    }




}