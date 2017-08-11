<?php
/**
 * Created by PhpStorm.
 * User: matthes
 * Date: 09.08.17
 * Time: 16:34
 */

namespace Rudl\App\Plugins\StatsView\Stats;


use MongoDB\BSON\UTCDateTime;
use MongoDB\Client;

class HourOfDayResourceStat
{


    public function query (Client $con) {
        $coll = $con->selectCollection("Rudl", "Resource_SysId");
        $res = $coll->aggregate([
            [ '$match' => [ 'timestamp' => ['$gte' => new UTCDateTime(strtotime("- 1 day") * 1000)] ] ],
            [
                '$group' => [
                    '_id' => [
                        'date_hod' => '$date_hod',
                        'sysId' =>'$sysId'
                    ],
                    'timestamp' => ['$min' => '$timestamp'],
                    'num_requests' => [ '$sum' => '$num_requests'],
                    'ru_utime_tv_sec' => [ '$sum' => '$ru_utime_tv_sec'],
                    'ru_stime_tv_sec' => [ '$sum' => '$ru_stime_tv_sec']
                ]
            ],
            [
                '$sort' => [
                    "_id.date_hod" => 1
                ]
            ],
            [
                '$project' => [
                    '__groupKey' => '$_id.date_hod',
                    'time' => [ '$dateToString' => ['format'=> '%Y-%m-d% %H:00:00', 'date' => '$timestamp'] ],
                    '__key' => '$_id.sysId',
                    '__value' => '$num_requests'
                ]
            ]

        ]);

        //$coll->
        $ret = [];

        $allKeys = [];
        foreach ($res as $data) {
            if ( ! isset ($ret[$data["__groupKey"]]))
                $ret[$data["__groupKey"]] = [];
            $ret[$data["__groupKey"]][$data["__key"]] = $data["__value"];
            $allKeys[$data["__key"]] = $data["__key"];
            foreach ($data as $key => $value) {
                if (substr ($key,0, 1) == "_")
                    continue;
                $ret[$data["__groupKey"]][$key] = (string)$value;

            }
        }



        $chart = [
            "data" => $ret,
            "xkey" => "time",
            "ykeys" => array_keys($allKeys),
            "labels" => array_keys($allKeys)
        ];
        return $chart;

    }


}