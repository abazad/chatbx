<?php
function __autoload($class_name) {
    $path = DIR_ROOT . '/lib/classes/' . strtolower($class_name) . '.class.php';
    if(file_exists($path)) {
        include($path);
    }
}