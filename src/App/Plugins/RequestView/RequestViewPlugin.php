<?php
/**
 * Created by PhpStorm.
 * User: matthes
 * Date: 07.08.17
 * Time: 19:54
 */

namespace Rudl\App\Plugins\RequestView;


use Gismo\Component\Application\Context;
use Gismo\Component\Partial\Page;
use Gismo\Component\Plugin\Plugin;
use MongoDB\BSON\ObjectID;
use MongoDB\BSON\Timestamp;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Client;
use MongoDB\Driver\Query;
use Rudl\App\FrontendContext;

class RequestViewPlugin implements Plugin
{

    public function onContextInit(Context $context)
    {
        if ($context instanceof FrontendContext) {
            $context["const.sidebar.navi"] = $context->filter(function ($§§input) {
                $§§input[] = [
                    "name" => "Requests",
                    "icon" => "fa-pencil",
                    "link" => "/requests"
                ];
                return $§§input;
            });

            $context["tpl.requestView"] = $context->template(
                __DIR__."/tpl.requestView.html"
            );

            $context->route->add("/requests", function () use ($context) {
                echo $context["tpl.requestView"]();
            });


            $context->route->add("/api/requestView/from/:lastId", function ($lastId, Client $con) {
                $coll = $con->selectCollection("Rudl", "Resource_Request");

                $restriction = [];
                if ($lastId != "")
                    $restriction = ["_id" => ["\$gt" => new ObjectID($lastId)]];

                if (@$_GET["sysId"] != "") {
                    $restriction["sysId"] = ["\$eq" => (string)@$_GET["sysId"]];
                }
                if (@$_GET["clientIp"] != "") {
                    $restriction["clientIp"] = ["\$eq" => (string)@$_GET["clientIp"]];
                }
                if (@$_GET["account"] != "") {
                    $restriction["account"] = ["\$eq" => (string)@$_GET["account"]];
                }
                $res = $coll->find($restriction, ["sort" => ["_id" => -1], "limit" => 200]);

                $it = new \IteratorIterator($res);


                $ret = [
                    "lastId" => null,
                    "result" => []
                ];
                foreach ($it as $cur) {
                    if ($ret["lastId"] === null)
                        $ret["lastId"] = (string)$cur->_id;

                    $dateTime = $cur->timestamp->toDateTime();
                    /* @var $dateTime \DateTime */
                    $dateTime->setTimezone(new \DateTimeZone("Europe/Berlin"));

                    $line = "<br>";
                    $line .= "<span class='log_date'>" . htmlentities("[{$dateTime->format("Y-m-d H:i:s")}]") . "</span>";
                    $line .= "<span class='log_hostname'>" . htmlentities("[{$cur->sysId}]") . "</span>";
                    $line .= "<span class='log_hostname'>" . htmlentities("[{$cur->hostname}]") . "</span>";
                    $line .= "<span class='log_ip'>" . htmlentities("[{$cur->clientIp}]") . "</span>";
                    $line .= "<span class='log_system'>" . htmlentities("[{$cur->account}]") . "</span>";
                    $line .= "<span class=''>" . htmlentities("[" . number_format($cur->ru_utime_tv_sec, 3) . "]") . "</span>";
                    $line .= "<span class=''>" . htmlentities("[" . number_format($cur->ru_stime_tv_sec, 3) . "]") . "</span>";
                    $line .= "<span class=''>" . htmlentities("[" . number_format($cur->duration_sec, 3) . "]") . "</span>";
                    $line .= "<span class=''>" . htmlentities(" {$cur->request}") . "</span>";
                    $ret["result"][] = $line;
                }
                header ("Content-Type: text/json");
                echo json_encode($ret);
            });



        }
    }
}