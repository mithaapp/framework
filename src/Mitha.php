<?php
/**
 * Created by PhpStorm.
 * User: mitha
 * Date: 23/03/18
 * Time: 12:02
 */

namespace Mitha\Framework;

use Mitha\Framework\Routing\Router;

class Mitha
{
    public function run()
    {
        $router = new Router();

        require APP_PATH.'Config/Routes.php';

        $router->routeUrl($_SERVER['QUERY_STRING']);
    }
}