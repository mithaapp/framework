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

namespace Mitha\Framework\Routing;

class Router
{
    protected $defaultNamespace;

    protected $defaultController;

    protected $defaultMethod;

    protected $default404Override;

    protected $routes = [];

    protected $placeholders = [
        '{any}' => '[^/]+',
        '{alphanum}' => '[a-zA-Z0-9]+',
        '{num}' => '[0-9]+',
        '{alpha}' => '[a-zA-Z]+',
    ];

    protected $params = [];

    public function add($route, $params = [])
    {
        foreach ($this->placeholders as $key => $value) {
            $route = str_replace($key, $value, $route);
        }
        $route = preg_replace('/\//', '\\/', $route);
        $route = preg_replace('/\{([a-z]+):([^\}]+)\}/', '(?P<\1>\2)', $route);
        $route = '/^' . $route . '$/i';
        $this->routes[$route] = $params;
    }

    public function getRoutes()
    {
        return $this->routes;
    }

    public function match($url)
    {
        foreach ($this->routes as $route => $params) {
            if (preg_match($route, $url, $matches)) {
                foreach ($matches as $key => $match) {
                    if (is_string($key)) {
                        $params[$key] = $match;
                    }
                }
                $this->params = $params;
                return true;
            }
        }
        return false;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function routeUrl($url)
    {
        $url = $this->removeQueryStringVariables($url);
        $url = ltrim($url, '/');
        if ($this->match($url)) {

            $controller = $this->params['controller'];

            $controller = $this->getNamespace() . $controller;

            if (class_exists($controller)) {
                $object = new $controller($this->params);

                $action = $this->params['action'];

                if (method_exists($object, $action)) {
                    $object->$action();
                } else {
                    throw new \Exception("Method $action in controller $controller cannot be called directly - remove the Action suffix to call this method");
                }
            } else {
                throw new \Exception("Controller class $controller not found");
            }
        } else {
            throw new \Exception('No route matched.', 404);
        }
    }

    protected function removeQueryStringVariables($url)
    {
        if ($url != '') {
            $parts = explode('?', $url, 2);

            if (strpos($parts[0], '=') === false) {
                $url = $parts[0];
            } else {
                $url = '';
            }
        }

        return $url;
    }

    public function setDefaultNamespace(string $namespace)
    {
        $this->defaultNamespace = $namespace;
    }

    public function setDefaultController(string $controller)
    {
        $this->defaultController = $controller;
    }

    public function setDefaultMethod(string $method)
    {
        $this->defaultMethod = $method;
    }

    public function set404Override(string $override)
    {
        $this->default404Override = $override;
    }

    protected function getNamespace()
    {
        $namespace = $this->defaultNamespace;

        if (array_key_exists('namespace', $this->params)) {
            $namespace .= $this->params['namespace'];
        }
        $namespace .= '\\';

        return $namespace;
    }

}