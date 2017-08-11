<?php
/**
 * Created by PhpStorm.
 * User: matthes
 * Date: 05.08.16
 * Time: 23:31
 */

    namespace App;
    use Gismo\Component\Config\AppConfig;
    use Gismo\Component\Config\ConfigLoader;
    use Gismo\Component\HttpFoundation\Request\RequestFactory;
    use Gismo\Component\PhpFoundation\Helper\ErrorHandler;
    use Rudl\App\RudlApp;



    define("GISMO_BOOTSTRAP_FILE", __DIR__ . "/../src/bootstrap.inc.php");

    ini_set("display_errors", 1);

    if ( ! file_exists(GISMO_BOOTSTRAP_FILE))
        throw new \Exception("Bootstrap file missing. Please ensure to run 'composer update'.");


    require GISMO_BOOTSTRAP_FILE;

    // Aktivieren der Html-Sauberen Exception Darstellung
    ErrorHandler::UseHttpErrorHandler();

    ConfigLoader::FromFile(
        __DIR__ . "/../app.ini.dist",
        ConfigLoader::DEVELOPMENT,
        $config = new AppConfig()
    );

    // Request aus Environment bauen
    $request = RequestFactory::BuildFromEnv($config);

    // App Laden und ausführen.
    $app = new RudlApp($config);
    $app->run($request);
