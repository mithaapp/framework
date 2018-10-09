<?php

/**
 * Mitha Framework
 *
 * A lightweight PHP framework for developers
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2018 MithaApp
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package  Mitha Framework
 * @author  Mitha Framework Dev Team
 * @author  Mitha Aprilia <mitha@mithaaprilia.com>
 * @author  Mutasim Ridlo, S.Kom <ridho@mutasimridlo.com>
 * @copyright 2018, MithaApp (https://mitha.app/)
 * @license  https://opensource.org/licenses/MIT	MIT License
 * @link  https://mitha.app
 */

namespace Mitha;

use Mitha\Routing\Router;

class Aprilia
{
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function run()
    {
        date_default_timezone_set($this->config->appTimezone ?? 'UTC');

        $this->detectEnvironment();
        $this->bootEnvironment();

        if(CATCH_ERRORS) {
            $this->setErrorException();
        }

        $routes = \Config\Services::routes();

        require APP_PATH . 'Config/Routes.php';

        $routes->routeUrl($_SERVER['REQUEST_URI']);
    }

    protected function detectEnvironment()
    {
        if (!defined('ENVIRONMENT')) {
            define('ENVIRONMENT', $_SERVER['MF_ENV'] ?? 'production');
        }

        if (!defined('CATCH_ERRORS')) {
            define('CATCH_ERRORS', $_SERVER['MF_CATCH_ERRORS'] ?? 1);
        }
    }

    protected function bootEnvironment()
    {
        if (file_exists(ROOT_PATH . 'boot/environment/' . ENVIRONMENT . '.php')) {
            require_once ROOT_PATH . 'boot/environment/' . ENVIRONMENT . '.php';
        } else {
            header('HTTP/1.1 503 Service Unavailable.', true, 503);
            echo 'The application environment is not set correctly.';
            exit(1);
        }
    }

    protected function setErrorException()
    {
        set_error_handler('\Mitha\Exception\Handler::errorHandler');
        set_exception_handler('\Mitha\Exception\Handler::exceptionHandler');
    }
}