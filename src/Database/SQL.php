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
 * @db  https://mitha.app
 */

namespace Mitha\Database;

use Config\Database;
use PDO;

class SQL extends SQLBuilder
{
    private $db;
    private $query;
    protected $config;
    protected $group;

    public function __construct($group = 'default')
    {
        $this->group = $group;
        $this->config();
        $this->init();
    }

    private function init()
    {
        if (is_null($this->db)) {
            $this->db = $this->connect($this->group);
        }
    }

    private function config()
    {
        $this->config = new Database();
        return $this->config;
    }


    protected function connect(string $group)
    {
        $group = $this->config->$group;
        $dsn = 'mysql:host=' . $group['hostname'] . ';dbname=' . $group['database'] . ';charset=' . $group['charset'];
        $this->db = new PDO($dsn, $group['username'], $group['password'], $group['options']);

        return $this->db;
    }

    public function escape($string)
    {
        if (is_null($this->db)) {
            $this->init();
        }

        return $this->db->quote($string);
    }

    public function insertId()
    {
        return $this->db->lastInsertId();
    }

    public function results($query, $returnType = false)
    {
        $return = false;

        if ($returnType) {
            $this->returnType = $returnType;
        }

        if (is_null($query)) {
            $query = $this->command();
        }

        $result = $this->query($query);

        if ($this->returnType == 'object' || $this->returnType == 'array') {
            $fetch = $this->returnType == 'object' ? PDO::FETCH_OBJ : PDO::FETCH_ASSOC;

            while ($row = $result->fetch($fetch)) {
                $return[] = $row;
            }

            return $return;
        }

        if ($this->returnType == 'iterator') {
            return $result;
        }

        return $return;
    }

    public function query($sql, $method = 'query')
    {
        if (is_null($this->db)) {
            $this->init();
        }

        $this->lastQuery = $sql;
        $query = $this->db->$method($sql);

        if (!$query) {
            $backtrace = debug_backtrace()[2];
            throw new \ErrorException($this->db->errorInfo()[2], 0, 1, $backtrace['file'], $backtrace['line']);
        }

        return $query;
    }

    public function row($query, $returnType = false)
    {
        if ($returnType) {
            $this->returnType = $returnType;
        }

        if (is_null($query)) {
            $query = $this->command();
        }

        if (is_null($this->db)) {
            $this->init();
        }

        $result = $this->query($query);

        return $result->fetch($this->returnType == 'object' ? PDO::FETCH_OBJ : PDO::FETCH_ASSOC);
    }

    public function prepare(string $query)
    {
        return $this->db->prepare($query);
    }

    public function version()
    {
        return $this->db->getAttribute(constant('PDO::ATTR_SERVER_VERSION'));
    }

    public function close()
    {
        $this->db = null;
    }

    public function begin()
    {
        $this->db->beginTransaction();
    }

    public function commit()
    {
        $this->db->commit();
    }

    public function rollback()
    {
        $this->db->rollBack();
    }

    public function exec($sql)
    {
        return $this->query($sql, 'exec');
    }

    public function table(string $table)
    {
        $group = $this->group;
        return $this->config->$group['dbprefix'] . $table;
    }
}