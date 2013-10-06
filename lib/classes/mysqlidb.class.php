<?php
class mysqlidb Extends Dmz_Core {

    protected static $_instance;

    protected $_mysqli;

    protected $_prefix = NULL;

    protected $_query;

    /****/
    protected $_statement;
    protected $_table;
    protected $_field;
    protected $_where;
    protected $_limit;
    protected $_orderby;
    protected $_set;
    protected $_insert;
    protected $_leftJoin;

    public function __construct($host, $user, $pass = NULL, $name, $prefix = '') {
        // if($port == NULL) {
        //     $port = ini_get('mysqli.default_port');
        // }
        $this->_mysqli = new mysqli($host, $user, $pass, $name);
        $this->_mysqli->set_charset("utf8");
        $this->_prefix = $prefix;
        self::$_instance = $this;
    }

    public function query($query) {
        $this->_query = $query;
        $result = $this->execute();
        return $result;
    }

    public function insert($table) {
        $this->_statement = "INSERT";
        $this->_table = $this->_prefix.$table;
        return $this;
    }

    public function update($table) {
        $this->_statement = "UPDATE";
        $this->_table = $this->_prefix.$table;
        return $this;
    }

    public function delete($table) {
        $this->_statement = "DELETE";
        $this->_table = $this->_prefix.$table;
        return $this;
    }

    public function select($table) {
        $this->_statement = "SELECT";
        $this->_table = $this->_prefix.$table;
        return $this;
    }

    public function columns($field = '*') {
        if(is_array($field)) {
            $field = implode(', ', $field);
        }
        $this->_field = $field;
        return $this;
    }

    public function set($set) {
        if($set AND !empty($set) AND is_array($set)) {
            $nset = array();
            foreach($set as $field => $val) {
                $nset[] = "$field = '$val'";
            }
            $this->_set = implode(", ", $nset);
        }
        return $this;
    }

    public function where($where) {
        if($where AND !empty($where) AND is_array($where)) {
            $nwhere = array();
            foreach($where as $field => $val) {
                $nwhere[] = "$field = '$val'";
            }
            $this->_where = implode(" AND ", $nwhere);
        }
        return $this;
    }

    public function orderby($orderby) {
        if(!empty($orderby)) {
            foreach($orderby as $field => $order) {
                if(is_string($field)) {
                    $order_clause = $field . ' ' . $order;
                }
            }
            $this->_orderby = implode(', ', $order_clause);
        }
        return $this;
    }

    public function limit($from, $to = NULL) {
        if($from) {
            $this->_limit = $from;
            if($to) {
                $this->_limit .= ', '.$to;
            }
        }
        return $this;
    }

    public function values($colVal) {
        if(is_array($colVal) AND !empty($colVal)) {
            $col = array();
            $val = array();
            foreach($colVal as $foo => $bar) {
                $col[] = $foo;
                $val[] = $bar;
            }
            $this->_insert = '('.implode(", ", $col).') VALUES ("'.implode('","', $val).'")';
        }
        return $this;
    }

    public function execute() {
        $stmt = $this->prepareQuery();
        $stmt->execute();
        $result = $stmt->affected_rows;
        $pos = strpos($this->_query, "SELECT");
        if($pos !== false) {
            $result = $this->getQueryResult($stmt);
        }
        $this->reset();
        return $result;
    }

    protected function getQueryResult(mysqli_stmt $stmt) {
        $parameters = array();
        $results = array();
        $meta = $stmt->result_metadata();
        $row = array();

        while ($field = $meta->fetch_field()) {
            $row[$field->name] = null;
            $parameters[] = & $row[$field->name];
        }

        call_user_func_array(array($stmt, 'bind_result'), $parameters);
        while ($stmt->fetch()) {
            $x = array();
            foreach ($row as $key => $val) {
                $x[$key] = $val;
            }
            array_push($results, $x);
        }

        if(count($results)==1) {
            $results = call_user_func_array('array_merge', $results);
        }
        
        return $results;
    }

    protected function prepareQuery() {
        $field = is_null($this->_field) ? '*' : $this->_field;
        $where = is_null($this->_where) ? '' : 'WHERE '.$this->_where;
        $limit = is_null($this->_limit) ? '' : 'LIMIT ' . $this->_limit;
        $order = is_null($this->_orderby) ? '' : 'ORDER BY '.$this->_orderby;
        $set = is_null($this->_set) ? '' : 'SET '.$this->_set;

        switch($this->_statement) {

            case "SELECT":
                $this->_query = sprintf(
                    'SELECT %s FROM %s %s %s %s;',
                    $field, $this->_table, $where,
                    $order, $limit);
                break;

            case "DELETE";
                $this->_query = sprintf(
                    'DELETE FROM %s %s;',
                    $this->_table, $where);
                break;

            case "UPDATE":
                $this->_query = sprintf(
                    'UPDATE %s %s %s;',
                    $this->_table, $set, $where);
                break;

            case "INSERT":
                $this->_query = sprintf(
                    'INSERT INTO %s %s;',
                    $this->_table, $this->_insert);
                break;

        }

        if(!$stmt = $this->_mysqli->prepare($this->_query)) {
            trigger_error("Problem preparing query ($this->_query) " . $this->_mysqli->error, E_USER_ERROR);
        }
        return $stmt;
    }

    public function reset() {
        $this->_query = NULL;
        $this->_statement = NULL;
        $this->_table = NULL;
        $this->_field = NULL;
        $this->_where = NULL;
        $this->_limit = NULL;
        $this->_orderby = NULL;
        $this->_set = NULL;
        $this->_insert = NULL;
        $this->_leftJoin = NULL;
    }

    public function getDbStatus($table) {
        $table = $this->_prefix.$table;
        $this->_query = "SHOW TABLE STATUS LIKE '$table'";
        $stmt = $this->prepareQuery();
        $stmt->execute();
        return $this->getQueryResult($stmt);
    }

    public function getLastInsertId() {
        return $this->_mysqli->insert_id;
    }

    public function esc($str) {
        return $this->_mysqli->real_escape_string($str);
    }

    public function __destruct() {
        $this->_mysqli->close();
    }
}