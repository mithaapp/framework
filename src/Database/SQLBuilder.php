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

class SQLBuilder
{
    public $insertId;
    public $clientFlags = 0;
    public $newLink = true;
    public $persistentConnection = false;
    public $instantiateClass = 'stdClass';
    protected $column = '*';
    protected $distinct = false;
    protected $tables = [];
    protected $joins = null;
    protected $joinsType = null;
    protected $joinsOn = [];
    protected $criteria = [];
    protected $groupBy = null;
    protected $isHaving = [];
    protected $limit = null;
    protected $offset = null;
    protected $orderBy = null;
    protected $order = null;
    protected $isQuotes = true;
    protected $link;
    protected $connection; // return data type option: object, array and iterator (pointer)
    protected $config;
    protected $lastQuery;
    protected $lastError;
    protected $throwError = false;
    protected $returnType = 'array';

    public function distinct()
    {
        $this->distinct = true;

        return $this;
    }

    public function from()
    {
        $tables = func_get_args();

        if (is_array($tables[0])) {
            $tables = $tables[0];
        }

        $this->tables = $tables;

        return $this;
    }

    public function join($table, $type = null)
    {
        $this->joins = $this->table($table);
        $this->joinsType = $type;

        return $this;
    }

    public function on($column, $operator, $value, $separator = false)
    {
        $this->isQuotes = false;
        $this->joinsOn[] = $this->createCriteria($column, $operator, $value, $separator);
        $this->isQuotes = true;

        return $this;
    }

    protected function createCriteria($column, $operator, $value, $separator)
    {
        if (is_string($value) && $this->isQuotes) {
            $value = $this->escape($value);
        }

        $operator = strtoupper($operator);

        if ($operator == 'IN') {
            if (is_array($value)) {
                $value = "('" . implode("', '", $value) . "')";
            }
        }

        if ($operator == 'BETWEEN') {
            $value = $value[0] . ' AND ' . $value[1];
        }

        $return = $column . ' ' . $operator . ' ' . $value;

        if ($separator) {
            $return .= ' ' . strtoupper($separator);
        }

        return $return;
    }

    public function groupBy()
    {
        $this->groupBy = implode(', ', func_get_args());

        return $this;
    }

    public function having($column, $operator, $value, $separator = false)
    {
        $this->isHaving[] = $this->createCriteria($column, $operator, $value, $separator);

        return $this;
    }

    public function orderBy($column, $order = null)
    {
        $this->orderBy = $column;
        $this->order = $order;

        return $this;
    }

    public function limit($limit, $offset = null)
    {
        $this->limit = $limit;
        $this->offset = $offset;

        return $this;
    }

    public function fetchAs($returnType = 'object')
    {
        $this->returnType = $returnType;

        return $this;
    }

    public function getAll($table = false, $where = [], $fields = [], $returnType = false)
    {
        $table = $this->table($table);

        if (!$table) {
            return $this->results($this->command());
        }

        if ($returnType) {
            $this->returnType = $returnType;
        }

        $column = '*';

        if (!empty($fields)) {
            $column = $fields;
        }

        $this->select($column)->from($table);

        if (!empty($where)) {
            foreach ($where as $key => $val) {
                $this->where($key, '=', $val, 'AND');
            }
        }

        return $this->getAll();
    }

    public function command()
    {
        $query = 'SELECT ';

        if ($this->distinct) {
            $query .= 'DISTINCT ';
            $this->distinct = false;
        }

        $column = '*';

        if (is_array($this->column)) {
            $column = implode(', ', $this->column);
            $this->column = '*';
        }

        $query .= $column;

        if (!empty($this->tables)) {
            $query .= ' FROM ' . implode(', ', $this->tables);
            $this->tables = [];
        }

        if (!is_null($this->joins)) {
            if (!is_null($this->joinsType)) {
                $query .= ' ' . strtoupper($this->joinsType);
                $this->joinsType = null;
            }

            $query .= ' JOIN ' . $this->joins;

            if (!empty($this->joinsOn)) {
                $query .= ' ON (' . implode(' ', $this->joinsOn) . ')';
                $this->joinsOn = [];
            }

            $this->joins = null;
        }

        if (!empty($this->criteria)) {
            $cr = implode(' ', $this->criteria);
            $query .= ' WHERE ' . rtrim(rtrim($cr, 'AND'), 'OR');
            $this->criteria = [];
        }

        if (!is_null($this->groupBy)) {
            $query .= ' GROUP BY ' . $this->groupBy;
            $this->groupBy = null;
        }

        if (!empty($this->isHaving)) {
            $query .= ' HAVING ' . implode(' ', $this->isHaving);
            $this->isHaving = [];
        }

        if (!is_null($this->orderBy)) {
            $query .= ' ORDER BY ' . $this->orderBy . ' ' . strtoupper($this->order);
            $this->orderBy = null;
        }

        if (!is_null($this->limit)) {
            $query .= ' LIMIT ' . $this->limit;

            if (!is_null($this->offset)) {
                $query .= ' OFFSET ' . $this->offset;
                $this->offset = null;
            }

            $this->limit = null;
        }

        return $query;
    }

    public function select()
    {
        $column = func_get_args();

        if (!empty($column)) {
            $this->column = $column;

            if (is_array($column[0])) {
                $this->column = $column[0];
            }
        }

        return $this;
    }

    public function where($column, $operator, $value, $separator = false)
    {
        if (is_string($value)) {
            $value_arr = explode('.', $value);
            if (count($value_arr) > 1) {
                if (array_search($value_arr[0], $this->tables) !== false) {
                    $this->isQuotes = false;
                }
            }
        }

        $this->criteria[] = $this->createCriteria($column, $operator, $value, $separator);
        $this->isQuotes = true;

        return $this;
    }

    public function getOne($table = false, $where = [], $fields = [], $returnType = false)
    {
        $table = $this->table($table);

        if (!$table) {
            return $this->row($this->command());
        }

        if ($returnType) {
            $this->returnType = $returnType;
        }

        $column = '*';

        if (!empty($fields)) {
            $column = $fields;
        }

        $this->select($column)->from($table);

        if (!empty($where)) {
            foreach ($where as $key => $val) {
                $this->where($key, '=', $val, 'AND');
            }
        }

        return $this->getOne();
    }

    public function getVar($query = null)
    {
        if (is_null($query)) {
            $query = $this->command();
        }

        $result = $this->row($query);
        $key = array_keys(get_object_vars($result));

        return $result->$key[0];
    }

    public function insert($table, $data = [])
    {
        $table = $this->table($table);
        $fields = array_keys($data);

        foreach ($data as $key => $val) {
            $escapedDate[$key] = $this->escape($val);
        }

        return $this->exec("INSERT INTO $table (" . implode(',', $fields) . ') VALUES (' . implode(', ', $escapedDate) . ')');
    }

    public function update($table, $data, $where = null)
    {
        $table = $this->table($table);

        foreach ($data as $key => $val) {
            $data2[$key] = $this->escape($val);
        }

        $bits = $wheres = [];
        foreach ((array)array_keys($data2) as $k) {
            $bits[] = "$k = $data2[$k]";
        }

        if (!empty($this->criteria)) {
            $criteria = implode(' ', $this->criteria);
            unset($this->criteria);
        } elseif (is_array($where)) {
            foreach ($where as $c => $v) {
                $wheres[] = "$c = " . $this->escape($v);
            }

            $criteria = implode(' AND ', $wheres);
        } else {
            return false;
        }

        return $this->exec("UPDATE $table SET " . implode(', ', $bits) . ' WHERE ' . $criteria);
    }

    public function delete($table, $where = null)
    {
        $table = $this->table($table);

        if (!empty($this->criteria)) {
            $criteria = implode(' ', $this->criteria);
            unset($this->criteria);
        } elseif (is_array($where)) {
            foreach ($where as $c => $v) {
                $wheres[] = "$c = " . $this->escape($v);
            }

            $criteria = implode(' AND ', $wheres);
        } else {
            return false;
        }

        return $this->exec("DELETE FROM $table WHERE " . $criteria);
    }

    public function getLastQuery()
    {
        return $this->lastQuery;
    }

}