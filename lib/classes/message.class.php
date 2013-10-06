<?php
class Dmz_Message Extends Dmz_Core {

    public static function getInstance() {
        return parent::_getInstance(__CLASS__);
    }

    public function send($msg) {
        if(!isset($_SESSION['ses_id'])) {
            return array("error" => "Chatbx error! Please re-login.");
        }

        if(empty($msg) || ctype_space($msg)) {
            return array("error" => "Message empty!");
        }

        if($this->user()->checkUserBan($_SESSION['social_id'])) {
            return array("error" => "You are currently banned.");
        }

        $msg = $this->db->esc($this->stripMessage($msg));

        $this->db->insert("chats")
                 ->values(array(
                    "chat_id" => "",
                    "user_id" => $_SESSION['user_id'],
                    "message" => $msg,
                    "chat_type" => "normal",
                    "chat_time" => time()))
                 ->execute();

        $lastid = $this->db->getLastInsertId();

        if($lastid%20==0) {
            $this->db->insert("chats")
                     ->values(array(
                        "chat_id" => "",
                        "user_id" => 0,
                        "message" => CHATBX_GLOBAL_MESSAGE,
                        "chat_type" => "global",
                        "chat_time" => time()))
                     ->execute();
        }

        $this->db->query("UPDATE `".CHATBX_DB_PRFX."users` 
            SET `chat_count`=chat_count+1 
            WHERE `social_id`='".$_SESSION['social_id']."'");

        return array("status" => 1, "lastmsgid" => $lastid);

    }

    public function get($lastChatID = 0) {
        
        if($lastChatID == 0) {
            $dbstat = $this->db->getDbStatus("chats");
            $lastChatID = $dbstat['Auto_increment']-15;
        }

        $result = $this->db->query("SELECT * FROM `".CHATBX_DB_PRFX."chats` AS ch 
            LEFT JOIN `".CHATBX_DB_PRFX."users` AS ua 
            ON ua.`user_id` = ch.`user_id`
            LEFT JOIN `".CHATBX_DB_PRFX."groups` AS g
            ON ua.`group_id` = g.`group_id` 
            WHERE chat_id > '".$this->db->esc($lastChatID)."' 
            ORDER BY chat_id ASC");

        $chat = array();
        if(count($result)>0) {
            // let's make sure that it is a two dimensional array
            $sc = array_filter($result,'is_array');
            if(count($sc)==0) {
                $result = array($result);
            }

            foreach($result as $row) {
                if($row['chat_type'] == 'normal') {
                    $chat[] = array(
                        "chat_id" => $row['chat_id'],
                        "message" => $row['message'],
                        "social_id" => $row['social_id'],
                        "social_name" => $row['social_name'],
                        "gender" => $row['gender'],
                        "group_id" => $row['group_id'],
                        "group_name" => $row['group_name'],
                        "chat_type" => $row['chat_type'],
                        "chat_time" => $row['chat_time'],
                        "connected_with" => $row['connected_with'],
                        "link" => (($row['connected_with'] == "facebook") 
                                    ? 'http://www.facebook.com/'.$row['social_id']
                                    : 'https://twitter.com/account/redirect_by_id?id='.$row['social_id']));
                } else {
                    $chat[] = array(
                        "chat_id" => $row['chat_id'],
                        "message" => $row['message'],
                        "chat_type" => $row['chat_type']);
                }
            }
        }

        $this->user()->updateUserStatus();

        return array("chats" => $chat,
            "user" => (isset($_SESSION['social_id'])) ? $this->user()->getUserInfo($_SESSION['social_id']) : '0',
            "user_list" => $this->user()->getUserList());

    }

    protected function stripMessage($str) {
        $a = explode('] ', $str);
        if(isset($a[1])) {
            $b = explode('$[', $a[0]);
            $c = explode(',', $b[1]);
            switch($c[2]) {
                case 'facebook':
                    $d = '<a href="http://www.facebook.com/'.$c[0].'" target="_blank">To '.$c[1].'</a> &#187; '.$a[1];
                break;

                case 'twitter':
                    $d = '<a href="https://twitter.com/account/redirect_by_id?id='.$c[0].'" target="_blank">To '.$c[1].'</a> &#187; '.$a[1];
                break;

                default: break;
            }
            return $d;
        }
        return $str;
    }

}