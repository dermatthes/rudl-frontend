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
use Rudl\App\Plugins\StatsView\Stats\HourOfDayResourceStat;

class StatsViewPlugin implements Plugin
{

    public function onContextInit(Context $context)
    {
        if ($context instanceof FrontendContext) {
            $context["const.sidebar.navi"] = $context->filter(function ($§§input) {
                $§§input[] = [
                    "name" => "Stats",
                    "icon" => "fa-bar-chart-o",
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


            $context->route->add("/api/statsView/:class", function ($class, Client $con) {
                $coll = $con->selectCollection("Rudl", "Resource_SysId");

                $class = '\Rudl\App\Plugins\StatsView\Stats\\' . $class;
                $proc = new $class();

                header ("Content-Type: text/json");
                echo json_encode($proc->query($con));
            });



        }
    }
}