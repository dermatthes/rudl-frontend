<?php

    
    namespace Rudl\App;
    use Gismo\Component\Config\AppConfig;
    use Gismo\Component\HttpFoundation\Request\Request;
    use Gismo\Component\Plugin\AppLauncher;
    use Gismo\Component\Plugin\App;
    use Gismo\Component\Plugin\Loader\JsonFilePluginLoader;
    use Gismo\Component\Route\Type\RouterRequest;
    use Golafix\Conf\DotGolafixYml;
    use Golafix\Conf\GolafixRouter;
    use Golafix\Conf\ZipPool;
    use MongoDB\Client;
    use Phore\Cli\CliController;
    use Rudl\App\Plugins\UdpServer\Processor\ResourceProcessor;
    use Rudl\App\Plugins\UdpServer\Processor\SyslogProcessor;
    use Rudl\App\Plugins\UdpServer\UdpServer;

    /**
     * Created by PhpStorm.
     * User: matthes
     * Date: 21.06.17
     * Time: 10:57
     */
    class RudlApp implements App {
        
        /**
         * @var FrontendContext
         */
        private $mContext;

        public function __construct(AppConfig $config) {
            $debug = false;
            if ($config->ENVIRONMENT === "DEVELOPMENT")
                $debug = true;
            $this->mContext = $c = new FrontendContext(true);
            $c->loadYaml(__DIR__ . "/../../frontend.yml");
            $c[Client::class] = function () {
                return new \MongoDB\Client(CONF_MONGO_CONNECTION);
            };

            $c[UdpServer::class] = $c->service(function () {
                $udpServer = new UdpServer(null, 62111, CONF_MONGO_CONNECTION);
                // Add all Processors below...
                $udpServer->addProcessor(new ResourceProcessor());
                $udpServer->addProcessor(new SyslogProcessor());
                return $udpServer;
            });

            $plugin = new BasePlugin();
            $plugin->onContextInit($c);
        }

        public function runCmd (array $mockParams=null) {
            $context = $this->mContext;

            $ctrl = $context["cli.controller"];
            if ( ! $ctrl instanceof CliController)
                throw new \InvalidArgumentException("'cli.controller' should be of Type CliController");
            $ctrl->dispatch($mockParams);
        }

        public function run(Request $request) {
            $p = $this->mContext;
            $p[Request::class] = $p->constant($request);

            $p->trigger("event.app.onrequest");
            $routeRequest = RouterRequest::BuildFromRequest($request);
            $p->route->dispatch($routeRequest);
        }
    }