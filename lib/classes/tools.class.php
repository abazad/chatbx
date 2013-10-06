<?php
class Dmz_Tools {

    public static function getAvatar($link, $id) {
        $img = file_get_contents($link);
        $file = DIR_ROOT.'/themes/img/avatars/'.$id.'.jpg';
        file_put_contents($file, $img);
    }

    public static function unsetAllSession() {
        foreach($_SESSION as $key => $val) {
            unset($_SESSION[$key]);
        }
    }

    public static function createSession($key, $val) {
        $_SESSION[$key] = $val;
    }

}