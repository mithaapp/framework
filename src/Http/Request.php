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

namespace Mitha\Http;

class Request
{
    public function getGet(string $key = null, string $filterType = null, string $flag = null)
    {

        if (!$key) {
            return $filterType ? filter_var_array($_GET, $filterType) : $_GET;
        }

        if (!isset($_GET[$key])) {
            return false;
        }

        if ($filterType) {
            return filter_input(INPUT_GET, $key, $filterType, $flag);
        } else {
            return $_GET[$key];
        }
    }

    public function getPost(string $key = null, string $filterType = null, string $flag = null)
    {
        if (!$key) {
            return $filterType ? filter_var_array($_POST, $filterType) : $_POST;
        }

        if (!isset($_POST[$key])) {
            return false;
        }

        if ($filterType) {
            return filter_input(INPUT_POST, $key, $filterType, $flag);
        } else {
            return $_POST[$key];
        }
    }

    public function getCookie(string $key = null, string $filterType = null, string $flag = null)
    {
        if (!$key) {
            return $filterType ? filter_var_array($_COOKIE, $filterType) : $_COOKIE;
        }

        if (!isset($_COOKIE[$key])) {
            return false;
        }

        if ($filterType) {
            return filter_input(INPUT_COOKIE, $key, $filterType, $flag);
        } else {
            return $_COOKIE[$key];
        }
    }
}