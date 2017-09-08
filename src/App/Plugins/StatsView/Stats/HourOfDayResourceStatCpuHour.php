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

class HourOfDayResourceStatCpuHour
{


    public function query (Client $con) {
        $coll = $con->selectCollection("Rudl", "Resource_SysId");
        $res = $coll->aggregate([
            [ '$match' => [ 'timestamp' => ['$gte' => new UTCDateTime(strtotime("- 3 hour") * 1000)] ] ],
            [
                '$project' => [
                    'time' => [ '$dateToString' => ['format'=> '%Y-%m-%d %H:%M', 'date' => '$timestamp'] ],
                    'ru_utime_tv_sec' => '$ru_utime_tv_sec',
                    'sysId' => '$sysId'
                ]
            ],
            [
                '$group' => [
                    '_id' => [
                        'time' => '$time',
                        'sysId' =>'$sysId'
                    ],
                    'num_requests' => [ '$sum' => '$num_requests'],
                    'ru_utime_tv_sec' => [ '$sum' => '$ru_utime_tv_sec'],
                    'ru_stime_tv_sec' => [ '$sum' => '$ru_stime_tv_sec']
                ]
            ],
            [
                '$sort' => [
                    "_id.time" => 1
                ]
            ],
            [
                '$project' => [
                    '__groupKey' => '$_id.time',
                    'time' => '$_id.time',
                    '__key' => '$_id.sysId',
                    '__value' => '$ru_utime_tv_sec'
                ]
            ]

        ]);

        //$coll->
        $ret = [];

        $allKeys = [];
        foreach ($res as $data) {
            //print_r ($data);
            $gkey = (new \DateTime($data["__groupKey"], new \DateTimeZone("UTC")))->setTimezone(new \DateTimeZone("Europe/Berlin"))->format("Y-m-d H:i");
            $data["time"] = (new \DateTime($data["time"], new \DateTimeZone("UTC")))->setTimezone(new \DateTimeZone("Europe/Berlin"))->format("Y-m-d H:i");

            if ( ! isset ($ret[$gkey]))
                $ret[$gkey] = [];

            $ret[$gkey][$data["__key"]] = round($data["__value"], 2);
            $allKeys[$data["__key"]] = $data["__key"];
            foreach ($data as $key => $value) {
                if (substr ($key,0, 1) == "_")
                    continue;
                $ret[(string)$gkey][$key] = (string)$value;

            }
        }

        $arData = [];
        foreach ($ret as $cur)
            $arData[] = $cur;



        $chart = [
            "data" => $arData,
            "xkey" => "time",
            "ykeys" => array_keys($allKeys),
            "labels" => array_keys($allKeys)
        ];
        return $chart;

    }


}