<?php
/**
 * Created by PhpStorm.
 * User: matthes
 * Date: 07.08.17
 * Time: 19:54
 */

namespace Rudl\App\Plugins\Logview;


use Gismo\Component\Application\Context;
use Gismo\Component\Partial\Page;
use Gismo\Component\Plugin\Plugin;
use MongoDB\BSON\Timestamp;
use MongoDB\Driver\Query;
use Rudl\App\FrontendContext;

class LogviewPlugin implements Plugin
{

    public function onContextInit(Context $context)
    {
        if ($context instanceof FrontendContext) {
            $context["const.sidebar.navi"] = $context->filter(function ($§§input) {
                $§§input[] = [
                    "name" => "Logview",
                    "icon" => "fa-car",
                    "link" => "/log"
                ];
                return $§§input;
            });

            $context->route->add("/log", function () use ($context) {
                $page = new Page($context);
                $page->setTemplate(__DIR__ . "/tpl.logview.html");
                echo $page();
            });


            $context->route->add("/api/logview/from/:lastId", function ($lastId) {
                $con = new \MongoDB\Client("mongodb://localhost:27017");
                $coll = $con->selectCollection("Rudl", "Syslog");

                $last = $_COOKIE["LOG_LASTTS"];
                $now = time()-2;
                setcookie("LOG_LASTTS", $now);



                $res = $coll->find(["timestamp" => ["\$gte" => (int)$last, "\$lte" => (int)$now]], ["\$sort" => ["_id" => 1]]);
                $res->sort(["_id" => -1]);

                $it = new \IteratorIterator($res);


                foreach ($it as $cur) {
                    echo "<br> " . htmlentities("[{$cur->syslogDate}][{$cur->hostname}][{$cur->clientIp}][{$cur->system}] {$cur->message}");
                }
            });



        }
    }
}