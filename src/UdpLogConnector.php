<?php
/**
 * Created by PhpStorm.
 * User: matthes
 * Date: 10.02.17
 * Time: 02:27
 */

    namespace Rudl;


    class UdpLogConnector
    {


        private $mSock = false;
        private $mServerIp;
        private $mServerPort;

        public function __construct($serverIp, $serverPort=62111)
        {
            $this->mSock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
            $this->mServerIp = $serverIp;
            $this->mServerPort = $serverPort;
        }

        private $mSysId;

        public function setSysId ($id) {
            $this->mSysId = $id;
        }


        private function sendMsg ($msgId, $body) {
            $buf = str_pad($msgId, 2, "0");
            $buf .= str_pad("Some", 30, " ");
            $buf .= $body;
            socket_sendto($this->mSock, $buf, strlen($buf), 0, $this->mServerIp, $this->mServerPort);

        }


        /**
         * Push statistics
         */
        public function stat()
        {

        }


        public function log(string $what, string $level="LOG") {
            $data = [
                $this->mSysId,
                microtime(true),
                $level,
                $what
            ];
            $this->sendMsg("01", json_encode($data));
        }

        public function error() {

        }

        public function emerg() {

        }


        public function exception() {

        }






    }