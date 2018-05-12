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
 * @copyright 2018, MithaApp (https://www.mithaapp.com/)
 * @license  https://opensource.org/licenses/MIT	MIT License
 * @link  https://www.mithaapp.com
 */

namespace Mitha\Config;

use Config\View;
use Mitha\Http\Request;
use Mitha\Http\Response;
use Mitha\Routing\Router;
use Mitha\View\Renderer;

class BaseServices
{
    protected static $services = [];

    public static function renderer($viewPath = APP_PATH . 'Views/', $config = null, $sharedService = true)
    {
        if (is_null($config)) {
            $config = new View();
        }

        if ($sharedService) {
            return self::getSharedService('renderer', $viewPath, $config);
        }

        return new Renderer($viewPath, $config);
    }

    public static function request($sharedService = true)
    {
        if ($sharedService) {
            return self::getSharedService('request');
        }

        return new Request();
    }

    public static function response($sharedService = true)
    {
        if ($sharedService) {
            return self::getSharedService('response');
        }

        return new Response();
    }

    public static function routes($sharedService = true)
    {
        if ($sharedService) {
            return self::getSharedService('routes');
        }

        return new Router();
    }

    //--------------------------------------------------------------------
    // Utility Methods - DO NOT EDIT
    //--------------------------------------------------------------------

    protected static function getSharedService(string $key, ...$params)
    {
        if (!isset(static::$services[$key])) {
            array_push($params, false);
            static::$services[$key] = static::$key(...$params);
        }

        return static::$services[$key];
    }

    public static function __callStatic(string $name, array $params)
    {
        $name = strtolower($name);

        if (method_exists(__CLASS__, $name)) {
            return Services::$name(...$params);
        }
    }
}