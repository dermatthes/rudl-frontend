<?php
    /**
     * Created by PhpStorm.
     * User: matthes
     * Date: 10.02.17
     * Time: 00:30
     */

    $last = microtime(true);

    function stop(&$last) {
        echo "\n" . number_format(microtime(true) - $last, 5);
        $last = microtime(true);
    }


    $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

    $msg = "Pingsss!";


    $data = [
        "sysId" => 728399,
        "pw" => 18827,
        "msg" => "Ping!"
    ];



    $i = 0;
    stop ($last);
    while(1) {
        if ($i==1000000)
            break;
        $i++;
        $data["msg"] = "Pi20jdlakjsdflksjaldfkjalskdfjlskaasldkjaslkdfjlaksjfdlkasjldkfjw0942jelkj20i94jolkjalkdjkkkkkkkkkkkkkkkkkkkalsdkflaksdjflkajsdlfkjasldkfjlsakdfjlaskdfjlsakdjflkasdjflkj2lkjrlkjafoijwlkefjlakjerfpowoejflakjfowaijelfkajsldkfjwoiewjolfkwjokjdjflaksjfow4r02ijng!" . $i;

        //$ps = "ping";
        $ps = pack("iiZ*", $data["sysId"], $data["pw"], $data["msg"]);

        //$ps = gzcompress($ps, 2);

        $len = strlen($ps);
        //stop($last);
        socket_sendto($sock, $ps, $len, 0, '127.0.0.1', 5555);
    }

    echo $i;
    stop($last);
    socket_close($sock);