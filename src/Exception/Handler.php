<?php

/**
 * Mitha Framework
 *
 * An lightweight PHP framework for developers
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
 * @copyright 2018, MithaApp (https://www.mithaapp.com/)
 * @license  https://opensource.org/licenses/MIT	MIT License
 * @link  https://www.mithaapp.com
 */

namespace Mitha\Exception;

use Config\Services;

class Handler
{
    public static function errorHandler($level, $message, $file, $line)
    {
        if (error_reporting() !== 0) {
            throw new \ErrorException($message, 0, $level, $file, $line);
        }
    }

    public static function exceptionHandler($exception)
    {
        $code = $exception->getCode();

        if ($code != 404) {
            $code = 500;
        }
        http_response_code($code);

        $router = Services::routes();

        if (ENVIRONMENT == 'production') {
            if ($code == 404) {
                $page404Override = $router->get404Override();
                $controller = $page404Override['controller'] ?? '';
                $action = $page404Override['action'] ?? '';
                $validController = false;
                $validAction = false;

                if(!empty($controller) && !empty($action)){
                    $validController = (bool) class_exists($router->getNamespace().$controller);
                    $validAction = (bool) method_exists($router->getNamespace().$controller, $action);
                }
                if ($validAction && $validAction) {
                    $router->runController($page404Override['controller'], $page404Override['action'], $router->getUrlParams());
                } else {
                    echo view('errors/404', ['title' => 'Page not Found!', 'content' => 'The page you looking for is doesn\'t exist!.'], ['defaultPath' => true]);
                }
            } else {
                $page500Override = $router->get500Override();
                $controller = $page500Override['controller'] ?? '';
                $action = $page500Override['action'] ?? '';
                $validController = false;
                $validAction = false;

                if(!empty($controller) && !empty($action)){
                    $validController = (bool) class_exists($router->getNamespace().$controller);
                    $validAction = (bool) method_exists($router->getNamespace().$controller, $action);
                }
                if ($validAction && $validAction) {
                    $router->runController($page500Override['controller'], $page500Override['action'], $router->getUrlParams());
                } else {
                    echo view('errors/500', ['title' => 'Something went wrong!', 'content' => 'We will work on fixing that right away. Meanwhile, you may return to home page.'], ['defaultPath' => true]);
                }
            }
        } else {
            echo view('errors/development', ['title' => $exception->getMessage(), 'e' => $exception], ['defaultPath' => true]);
        }
    }
}