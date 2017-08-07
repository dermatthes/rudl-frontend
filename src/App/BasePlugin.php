<?php
    
    namespace YourProject\App;
    use Gismo\Component\Application\Context;
    use Gismo\Component\HttpFoundation\Request\Request;
    use Gismo\Component\Plugin\Plugin;
    use Gismo\Component\Route\Type\RouterRequest;
    /**
     * Created by PhpStorm.
     * User: matthes
     * Date: 21.06.17
     * Time: 10:59
     */ 
    class BasePlugin implements Plugin {

        public function onContextInit(Context $context) {
            



            // Entwicklerseite
            if ($context instanceof FrontendContext) {

                $context->route->add ("/debug", function(Context $di) {
                    echo "<h1>Golafix Debugger</h1>";
                    var_dump($di);

                });


            }
        }

    }