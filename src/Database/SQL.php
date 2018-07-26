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

namespace Mitha\Database;

use Config\Database;
use PDO;

class SQL
{
    private $db;
    private $query;

    public function __construct($group = 'default')
    {
        $this->init($group);
    }

    protected function init(string $group)
    {
        $config = new Database();
        $dsn = 'mysql:host=' . $config->$group['hostname'] . ';dbname=' . $config->$group['database'] . ';charset=' . $config->$group['charset'];
        $this->db = new PDO($dsn, $config->$group['username'], $config->$group['password'], $config->$group['options']);

        return $this->db;
    }

    public function escape(string $string)
    {
        return $this->db->quote($string);
    }

    public function query(string $sql)
    {
        $this->query = $this->db->query($sql);
        return $this;
    }


    public function getResult(string $returnType = 'array')
    {
        if ($returnType == 'object' || $returnType == 'array') {
            $type = $returnType == 'object' ? PDO::FETCH_OBJ : PDO::FETCH_ASSOC;
        }
        return $this->query->fetchAll($type);
    }

    public function prepare(string $query)
    {
        return $this->db->prepare($query);
    }
}