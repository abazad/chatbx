<?php

require_once dirname(__FILE__).'/user.class.php';
require_once dirname(__FILE__).'/tools.class.php';
require_once dirname(__FILE__).'/message.class.php';
require_once dirname(__FILE__).'/template.class.php';
require_once dirname(__FILE__).'/mysqlidb.class.php';

class Dmz_Core {
    
    protected static $_instance = array();

    protected $db;

    public function __construct() {
        $this->db = new mysqlidb(
                        CHATBX_DB_HOST,
                        CHATBX_DB_USER,
                        CHATBX_DB_PASS,
                        CHATBX_DB_NAME,
                        CHATBX_DB_PRFX);
        // self::$_instance['mysqlidb'] = $this->db;
    }

    public function __call($class, $args) {
        if(strpos('Dmz_', $class) !== 0) {
            $class = 'Dmz_'.ucwords(strtolower($class));
        }
        return $class::getInstance();
    }

    public static function _getInstance($class) {
        if(!isset(self::$_instance[$class])) {
            self::$_instance[$class] = new $class;
        }
        return self::$_instance[$class];
    }

}