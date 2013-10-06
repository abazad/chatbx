<?php

session_start();

require_once dirname(__FILE__).'/define.php';
require_once dirname(__FILE__).'/classes/errorhandler.class.php';

set_error_handler(array("Dmz_ErrorHandler", "handler"));

if (!file_exists(DIR_ROOT.'/lib/settings.php')) {
    $install_dir = DIR_ROOT.'/install';
    if (!file_exists($install_dir)) {
        trigger_error("Install folder not found in $install_dir. Please get a fresh copy.");
    }
    header("Location:install/");
}

require_once dirname(__FILE__).'/settings.php';
require_once dirname(__FILE__).'/classes/core.class.php';
require_once dirname(__FILE__).'/classes/controller.class.php';