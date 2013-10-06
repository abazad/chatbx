<?php
define("DIR_ROOT", dirname(dirname(__FILE__)));
if (!file_exists(DIR_ROOT.'/lib/settings.php')) {
    $install_dir = DIR_ROOT.'/install';
    if (!file_exists($install_dir)) {
        trigger_error("Install folder not found in $install_dir. Please get a fresh copy.");
    }
    header("Location:../install/");
}