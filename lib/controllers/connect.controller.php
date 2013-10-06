<?php
class connect Extends Dmz_Core {

    public function __construct() {
        parent::__construct();
    }

    public function init($social = NULL) {
        if(is_null($social)) {
            trigger_error("No argument given.", E_USER_WARNING);
        } else {
            switch($social) {
                case 'fb': 
                    $this->connectFacebook();
                    break;

                case 'tw':
                    $this->connectTwitter();
                    break;

                default: trigger_error("Invalid argument given.", E_USER_WARNING);
            }
        }
    }

    public function to($social) {
        $this->init($social);
    }

    private function connectFacebook() {
        require_once dirname(dirname(__FILE__))."/social_api/fbconnect.php";
        $fb = new FBConnect();
        if(!$fb->output) {
            header("Location:".$fb->getLoginUrl());
            exit();
        } else {
            $fb_info = $fb->getUserInfo();
            $this->connectChatbx(array(
                "id" => $fb_info['id'],
                "name" => $fb_info['name'],
                "gender" => $fb_info['gender'],
                "connected_with" => 'facebook',
                "avatar" => "http://graph.facebook.com/".$fb_info['id']."/picture"));
        }
    }

    private function connectTwitter() {
        require_once dirname(dirname(__FILE__))."/social_api/twitteroauth.php";
        if(!empty($_GET['oauth_verifier'])
         && !empty($_SESSION['oauth_token'])
         && !empty($_SESSION['oauth_token_secret'])) {
            $twitteroauth = new TwitterOAuth(
                             CHATBX_TW_CONSUMER_KEY,
                             CHATBX_CONSUMER_SECRET,
                             $_SESSION['oauth_token'],
                             $_SESSION['oauth_token_secret']);
            $access_token = $twitteroauth->getAccessToken($_GET['oauth_verifier']);
            $_SESSION['access_token'] = $access_token;
            $user_info = $twitteroauth->get('account/verify_credentials');
            $this->connectChatbx(array(
                "id" => $user_info->id,
                "name" => $user_info->screen_name,
                "gender" => 'comments',
                "connected_with" => 'twitter',
                "avatar" => $user_info->profile_image_url));
        } else {
            $connection = new TwitterOAuth(CHATBX_TW_CONSUMER_KEY, CHATBX_CONSUMER_SECRET);
            $request_token = $connection->getRequestToken(CHATBX_ROOT."/connect/to/tw");
            $_SESSION['oauth_token'] = $token = $request_token['oauth_token'];
            $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
            switch($connection->http_code) {
                case 200:
                    $url = $connection->getAuthorizeURL($token);
                    header('Location: ' . $url);
                    exit();
                break;
                default: trigger_error("Could not connect to Twitter. Try again later.", E_USER_WARNING);
            }
        }
    }

    private function connectChatbx($social_info = NULL) {
        if($social_info == NULL) {
            Dmz_Tools::unsetAllSession();
            trigger_error("Something went wrong, please try again.", E_USER_WARNING);
        }

        if(!$this->user()->checkUserBan($social_info['id'])) {
            Dmz_Tools::createSession('ses_id', md5(microtime()));
            $time_now = time();
            $user_data = $this->user()->getUserInfo($social_info['id']);
            if($user_data) {
                $social_info['name'] = ($user_data['group_id'] == 0) 
                    ? $social_info['name'] : $user_data['social_name'];
                if($user_data['last_request'] < (time()-(int)43200)) {}
                $this->db->update("users")
                         ->set(array(
                            "social_name" => $this->db->esc($social_info['name']),
                            "gender" => $social_info['gender'],
                            "ip_address" => $_SERVER['REMOTE_ADDR'],
                            "ses_id" => $_SESSION['ses_id'],
                            "date_signed" => date('Y-m-d H:i:s', $time_now),
                            "last_request" => $time_now))
                         ->where(array("social_id" => $social_info['id']))
                         ->execute();
            } else {
                $this->db->insert("users")
                         ->values(array(
                            "user_id" => "",
                            "group_id" => 0,
                            "social_id" => $social_info['id'],
                            "social_name" => $this->db->esc($social_info['name']),
                            "gender" => $social_info['gender'],
                            "connected_with" => $social_info['connected_with'],
                            "ip_address" => $_SERVER['REMOTE_ADDR'],
                            "ses_id" => $_SESSION['ses_id'],
                            "date_signed" => date('Y-m-d H:i:s', $time_now),
                            "last_request" => $time_now,
                            "chat_count" => 0))
                         ->execute();
            }
            Dmz_Tools::getAvatar($social_info['avatar'], $social_info['id']);
            $user_data = $this->user()->getUserInfo($social_info['id']);
            foreach($user_data as $key => $val) {
                Dmz_Tools::createSession($key,$val);
            }
            $this->template()->addJsBottomLiteral('window.opener.location.href = "'.CHATBX_ROOT.'"; self.close();');
        } else {
            Dmz_Tools::unsetAllSession();
            trigger_error("You account is currently banned.", E_USER_WARNING);
        }
    }

}