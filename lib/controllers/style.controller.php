<?php
class style Extends Dmz_Core {

    public function init() {
        $data = $this->db->select("groups")
                         ->execute();
        header("Content-type: text/css");
        foreach($data as $var) {
            echo ".type-".$var['group_id']." .chatbx_username a { color: ".$var['group_color']." !important; } ";
        }
    }

}