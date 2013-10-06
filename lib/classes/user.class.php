<?php
class Dmz_User Extends Dmz_Core {

    public static function getInstance() {
        return parent::_getInstance(__CLASS__);
    }

    public function getUserInfo($social_id) {
        $data = $this->db->query("SELECT * FROM `".CHATBX_DB_PRFX."users` AS ua
            LEFT JOIN `".CHATBX_DB_PRFX."groups` AS g
            ON ua.`group_id` = g.`group_id`
            WHERE ua.`social_id` = '".$this->db->esc($social_id)."'");
        return (is_array($data) AND !empty($data)) ? $data : false;
    }

    public function checkUserBan($social_id) {
        $data = $this->db->query("SELECT bu.user_id, bu.ban_id, bu.banned_by, bu.reason, bu.date
                            FROM `".CHATBX_DB_PRFX."users` AS ua
                            LEFT JOIN `".CHATBX_DB_PRFX."banned_users` AS bu
                            ON ua.`user_id` = bu.`user_id`
                            WHERE ua.`social_id`='$social_id'");
        return (isset($data['ban_id'])) ? $data : false;
    }

    public function updateUserStatus() {
        if(isset($_SESSION['ses_id'])) {
            $this->db->update("users")
                     ->set(array("last_request" => time()))
                     ->where(array("ses_id" => $_SESSION['ses_id']))
                     ->execute();
        }
    }

    public function getUserList() {
        $data = $this->db->query("SELECT
                ua.`user_id`,
                ua.`group_id`,
                ua.`social_id`,
                ua.`social_name`,
                ua.`gender`,
                ua.`connected_with`,
                ua.`chat_count`,
                g.`group_name`
            FROM `".CHATBX_DB_PRFX."users` AS ua
            LEFT JOIN `".CHATBX_DB_PRFX."groups` AS g
            ON ua.`group_id` = g.`group_id`
            WHERE ua.`last_request` > '".(time()-30)."'");
        if(count($data) > 0) {
            $sc = array_filter($data,'is_array');
            if(count($sc)==0) {
                $data = array($data);
            }
            for($i=0;$i<count($data);$i++) {
                $data[$i]['link'] = ($data[$i]['connected_with'] == "facebook")
                    ? 'http://www.facebook.com/'.$data[$i]['social_id']
                    : 'https://twitter.com/account/redirect_by_id?id='.$data[$i]['social_id'];
            }
        }
        return (is_array($data) AND !empty($data)) ? $data : false;
    }

}