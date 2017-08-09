<?php
/**
 * Created by PhpStorm.
 * User: matthes
 * Date: 07.08.17
 * Time: 19:54
 */

namespace Rudl\App\Plugins\StatsView;


use Gismo\Component\Application\Context;
use Gismo\Component\Partial\Page;
use Gismo\Component\Plugin\Plugin;
use MongoDB\BSON\ObjectID;
use MongoDB\BSON\Timestamp;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Client;
use MongoDB\Driver\Query;
use Rudl\App\FrontendContext;

class StatsViewPlugin implements Plugin
{

    public function onContextInit(Context $context)
    {
        if ($context instanceof FrontendContext) {
            $context["const.sidebar.navi"] = $context->filter(function ($§§input) {
                $§§input[] = [
                    "name" => "Stats",
                    "icon" => "fa-board",
                    "link" => "/stats"
                ];
                return $§§input;
            });

            $context["tpl.statsView"] = $context->template(
                __DIR__."/tpl.statView.html"
            );

            $context->route->add("/stats", function () use ($context) {
                echo $context["tpl.statsView"]();
            });


            $context->route->add("/api/statsView/from/:lastId", function ($lastId, Client $con) {
                $coll = $con->selectCollection("Rudl", "Resource_SysId");

                $ret = [];

                $res = $coll->aggregate([
                    [ '$match' => [ 'timestamp' => ['$gte' => new UTCDateTime(strtotime("- 1 day") * 1000)] ] ],
                    [
                        '$group' => [
                            '_id' => '$sysId',
                            'num_requests' => [ '$sum' => '$num_requests'],
                            'ru_utime_tv_sec' => [ '$sum' => '$ru_utime_tv_sec'],
                            'ru_stime_tv_sec' => [ '$sum' => '$ru_stime_tv_sec']
                        ]
                    ]
                ]);


                print_r ($res);

                $ret = [];
                foreach ($res as $data) {
                    $ret[] = $data;
                }

                print_r ($ret);
                header ("Content-Type: text/json");
                echo json_encode($res);
            });



        }
    }
}