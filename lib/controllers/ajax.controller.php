<?php
class ajax Extends Dmz_Core {

    public function init() {}

    public function initialize() {
        $this->response(array(
            "settings" => $this->getSettings(),
            "template" => $this->getTemplate(),
            "user" => $this->getUser()));
    }

    public function sendMessage() {
        $response = $this->message()->send($_POST['message']);
        $this->response($response);
    }

    public function getMessage() {
        $response = $this->message()->get($_GET['lastid']);
        $this->response($response);
    }

    private function getUser() {
        if(isset($_SESSION['social_id'])) {
            $user = $this->user()->getUserInfo($_SESSION['social_id']);
            if($user) {
                return $user;
            }
        }
            
        return array("error" => "No session yet.");

    }

    private function getSettings() {
        return array(
            "min_char" => 3,
            "max_chat_displayed" => 15,
            "soundfx" => "2713_1329133091.mp3");
    }

    private function getTemplate() {
        return array(
            "normal" => $this->template()->returnOutput(DIR_ROOT."/themes/chatline-normal.tpl"),
            "global" => $this->template()->returnOutput(DIR_ROOT."/themes/chatline-global.tpl"),
            "user_list" => $this->template()->returnOutput(DIR_ROOT."/themes/user-list.tpl"));
    }

    private function response($data) {
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT);
    }
    
}