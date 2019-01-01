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

namespace Mitha\Routing;

class Router
{
    protected $appNamespace;
    protected $page404Override = [];
    protected $page500Override = [];
    protected $routes = [];
    protected $placeholders = [
        '(any)' => '[^/]+',
        '(alphanum)' => '[a-zA-Z0-9]+',
        '(num)' => '[0-9]+',
        '(alpha)' => '[a-zA-Z]+',
    ];
    protected $params = [];

    public function add(string $route, array $params = [])
    {
        foreach ($this->placeholders as $key => $value) {
            $route = str_replace($key, $value, $route);
        }
        $route = preg_replace('/\//', '\\/', $route);
        $route = preg_replace('/\{([a-z]+):([^\}]+)\}/', '(?P<\1>\2)', $route);
        $route = '/^' . $route . '$/i';
        $this->routes[$route] = $params;
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function match(string $url): bool
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

    public function getParams(): array
    {
        return $this->params;
    }

    public function getUrlParams(): array
    {
        $params = $this->params;

        unset($params['controller']);
        unset($params['action']);

        return $params;
    }

    public function runController(string $controller, string $action, array $params)
    {
        $controller = $this->getNamespace() . $controller;
        if (class_exists($controller)) {
            $object = new $controller($params);
            if (method_exists($object, $action)) {
                $object->$action();
            } else {
                throw new \Exception("Method $action in controller $controller cannot be called directly");
            }
        } else {
            throw new \Exception("Controller class $controller not found");
        }
    }

    public function routeUrl(string $url)
    {
        $url = $this->removeQuery($url);

        $url = ($url == '/') ? $url : ltrim($url, '/');

        if ($this->match($url)) {
            $controller = $this->params['controller'];
            $action = $this->params['action'];

            $this->runController($controller, $action, $this->getUrlParams());
        } else {
            throw new \Exception('No route matched.', 404);
        }
    }

    protected function removeQuery(string $url): string
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

    public function setAppNamespace(string $namespace)
    {
        $this->appNamespace = $namespace;
    }

    public function getAppNamespace(): string
    {
        return $this->appNamespace;
    }

    public function set404Override(array $override)
    {
        $this->page404Override = $override;
    }

    public function get404Override(): array
    {
        return $this->page404Override;
    }

    public function set500Override(array $override)
    {
        $this->page500Override = $override;
    }

    public function get500Override(): array
    {
        return $this->page500Override;
    }

    public function getNamespace(): string
    {
        $namespace = $this->appNamespace;

        if (array_key_exists('namespace', $this->params)) {
            $namespace .= $this->params['namespace'];
        }
        $namespace .= '\\';

        return $namespace;
    }

}