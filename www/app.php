<?php
/**
 * Created by PhpStorm.
 * User: matthes
 * Date: 05.08.16
 * Time: 23:31
 */

    namespace App;
    use Gismo\Component\HttpFoundation\Request\RequestFactory;
    use Gismo\Component\PhpFoundation\Helper\ErrorHandler;
    use Gismo\Component\Plugin\AppLauncher;

    require __DIR__ . "/../src/bootstrap.inc.php";

    // Activate HTML Errors
    ErrorHandler::UseHttpErrorHandler();

    // Build Request
    $request = RequestFactory::BuildFromEnv(AppLauncher::Get()->getConfig());

    // Run the Request
    AppLauncher::Get()->getApp()->run($request);