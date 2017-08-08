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
use MongoDB\BSON\ObjectID;
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
                    "name" => "Syslog",
                    "icon" => "fa-car",
                    "link" => "/syslog"
                ];
                return $§§input;
            });

            $context["tpl.logview"] = $context->template(__DIR__ . "/tpl.logview.html");

            $context->route->add("/syslog", function () use ($context) {
                echo $context["tpl.logview"]();
            });


            $context->route->add("/api/syslog/from/:lastId", function ($lastId) {
                $con = new \MongoDB\Client("mongodb://localhost:27017");
                $coll = $con->selectCollection("Rudl", "Syslog");


                $restriction = ["timestamp" => ["\$gt" => 0 ]];
                if ($lastId != "")
                    $restriction = ["_id" => ["\$gt" => new ObjectID($lastId)]];

                if (@$_GET["severity"] != "") {
                    $restriction["severity"] = ["\$lte" => (int)$_GET["severity"]];
                }

                if (@$_GET["filter"] != "") {
                    $restriction["\$text"] = ["\$search" => (string)@$_GET["filter"]];
                }

                if (@$_GET["system"] != "") {
                    $restriction["system"] = ["\$eq" => (string)@$_GET["system"]];
                }
                if (@$_GET["hostname"] != "") {
                    $restriction["hostname"] = ["\$eq" => (string)@$_GET["hostname"]];
                }
                if (@$_GET["clientIp"] != "") {
                    $restriction["clientIp"] = ["\$eq" => (string)@$_GET["clientIp"]];
                }
                $res = $coll->find($restriction, ["sort" => ["_id" => -1], "limit" => 200]);

                $it = new \IteratorIterator($res);

                $svres = [
                    LOG_EMERG => "EMERG",
                    LOG_ALERT => "ALERT",
                    LOG_CRIT => "CRIT",
                    LOG_ERR => "ERR",
                    LOG_WARNING => "WARNING",
                    LOG_NOTICE => "NOTICE",
                    LOG_INFO => "INFO",
                    LOG_DEBUG => "DEBUG"
                ];

                $ret = [
                    "lastId" => null,
                    "result" => []
                ];
                foreach ($it as $cur) {
                    if ($ret["lastId"] === null)
                        $ret["lastId"] = (string)$cur->_id;

                    $sv = $svres[$cur["severity"]];

                    $line = "<br>";
                    $line .= "<span class='log_date'>" . htmlentities("[{$cur->syslogDate}]") . "</span>";
                    $line .= "<span class='log_hostname'>" . htmlentities("[{$cur->hostname}]") . "</span>";
                    $line .= "<span class='log_ip'>" . htmlentities("[{$cur->clientIp}]") . "</span>";
                    $line .= "<span class='log_system'>" . htmlentities("[{$cur->system}]") . "</span>";
                    $line .= "<span class='LOG_$sv'>" . htmlentities("[{$sv}]") . "</span>";
                    $line .= "<span class='LOG_$sv'>" . htmlentities(" {$cur->message}") . "</span>";
                    $ret["result"][] = $line;
                }
                header ("Content-Type: text/json");
                echo json_encode($ret);
            });



        }
    }
}