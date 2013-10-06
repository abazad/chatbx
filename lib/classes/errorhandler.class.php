<?php
class Dmz_ErrorHandler {

    public static function handler($errno, $errstr, $errfile, $errline) {
        $file = basename($errfile, ".php");
        switch ($errno) {
            case E_NOTICE:
            case E_USER_NOTICE:
            case E_WARNING:
            case E_USER_WARNING:
                $error = array(
                    "Error" => array(
                        "message" => $errstr,
                        "file" => $file,
                        "line" => $errline)
                    );
                break;
            case E_ERROR:
            case E_USER_ERROR:
            default:
                $error = array(
                    "Unknown error" => array(
                        "message" => $errstr,
                        "file" => $file,
                        "line" => $errline)
                    );
                break;
        }
        self::reportError($error);
    }

    protected static function reportError($error) {
        header('Content-Type: application/json');
        if(CHATBX_DEVELOPMENT == false) {
            reset($error);
            $report = current($error);
            $error = array(key($error) => "Code error in [#".$report['line']."-".$report['file']."].");
        }
        echo json_encode($error, JSON_PRETTY_PRINT);
        exit();
    }

}