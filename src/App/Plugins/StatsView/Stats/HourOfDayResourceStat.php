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
                    'num_requests' => [ '$sum' => '$num_requests'],
                    'ru_utime_tv_sec' => [ '$sum' => '$ru_utime_tv_sec'],
                    'ru_stime_tv_sec' => [ '$sum' => '$ru_stime_tv_sec']
                ]
            ],
            [
                '$sort' => [
                    "date_hod" => 1,
                    "sysId" => 1
                ]
            ],
            [
                '$group' => [
                    '_id' => [
                        "date_hod" => '$_id.date_hod'
                    ],
                    '$sysId' => '$num_requests'
                ]
            ]
        ]);

        $ret = [];

        $hodArr = [];
        foreach ($res as $data) {
            $data = (array)$data;
            $data["_id"] = (array)$data["_id"];
            $ret[] = $data;


        }

        print_r ($ret);
        return $ret;

    }


}