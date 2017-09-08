<?php
/**
 * Created by PhpStorm.
 * User: matthes
 * Date: 08.09.17
 * Time: 09:41
 */

namespace Rudl\App\Plugins\UdpServer;


use Gismo\Component\Application\Context;
use Gismo\Component\Plugin\Plugin;
use MongoDB\Client;
use Rudl\App\FrontendContext;

class UdpServerPlugin implements Plugin
{
    public function onContextInit(Context $context)
    {
        if ($context instanceof FrontendContext) {
            $context->cligroup("rudl")
                ->command("server")
                ->description("Run the server instance")
                ->run(function (UdpServer $udpServer) {
                    $udpServer->run();
                });

            $context->cligroup("rudl")
                ->command("concentrate")
                    ->description("Concentrate Logs / Remove Data")
                    ->run(function (UdpServer $udpServer, Client $mongoDb) {
                        foreach ($udpServer->getProcessors() as $curProcessor) {
                            echo "\nRunning " . get_class($curProcessor) . "...";
                            $curProcessor->concentrateData($mongoDb);
                            echo "[DONE]";
                        }
                        echo "Finished!";
                    });
        }
    }
}