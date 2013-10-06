<?php
/**
 * Chatr
 *
 * @category  Facebook Connect
 * @author    Foobar (dmz.gfx@gmail.com)
 * @copyright Copyright (c) 2013
 * @version   1.0
 **/
 
class FBConnect {

	private $baseUrl;
	private $fb_appid;
	private $fb_appsecret;
	private $fb_scope;
	private $fb_graphUrl;
	private $fb_sess;
	
	public $output;

	function __construct() {
	
		$this->baseUrl = APP_WEB_URL."/connect?to=fb";
		$this->fb_appid = FB_APP_ID;
		$this->fb_appsecret = FB_APP_SECRET;
		$this->fb_scope = 'offline_access,publish_stream';
		$this->fb_graphUrl = 'https://graph.facebook.com/';
		$this->fb_sess = 'SmartCrownGFX_'.$this->fb_appid.'_';
		$this->output = $this->connect();
		
	}
	
	function connect() {
		
		if($this->getStatus()==0) {
		
			if(isset($_REQUEST['code'])) {
				return true;
			}
			
			return false;
			
		} else {
		
			return true;
		
		}
		
	}
	
	function setState() {
	
		if(!isset($_SESSION[$this->fb_sess.'state'])) {
			$_SESSION[$this->fb_sess.'state'] = md5(uniqid(mt_rand(), true));
		} else {
			$this->fb_state = $_SESSION[$this->fb_sess.'state'];
		}
	
		return $_SESSION[$this->fb_sess.'state'];
		
	}
	
	function getStatus() {
	
		if(isset($_SESSION[$this->fb_sess.'code']) && isset($_SESSION[$this->fb_sess.'access_token'])) {
			$this->fb_status = 1;
		} else {
			$this->fb_status = 0;
		}
		
		return $this->fb_status;
	
	}
	
	function getUserInfo() {
	
			if(isset($_REQUEST['code'])) {
				$this->fb_createsess('code', $_REQUEST['code']);
			}
			$url = $this->fb_graphUrl.'oauth/access_token?client_id='.$this->fb_appid.'&redirect_uri='.urlencode($this->baseUrl).'&client_secret='.$this->fb_appsecret.'&code='.$_SESSION[$this->fb_sess.'code'];
			$data = $this->fb_curl($url);
			if(substr($data, 0, 13)=="access_token=") {
				$this->fb_createsess('access_token', substr($data, 13, strlen($data)));
				$url = $this->fb_graphUrl.'me?access_token='.$_SESSION[$this->fb_sess.'access_token'];
				$data = $this->fb_curl($url);
				$data = $this->JsonDec($data);
				return $data;
			}
	
	}
	
	function getLoginUrl() {
	
		return "https://www.facebook.com/dialog/oauth?client_id=".$this->fb_appid."&redirect_uri=".$this->baseUrl."&state=".$this->setState()."&scope=".$this->fb_scope;
	
	}
	
	function fb_curl($url) {
	
		$ch = curl_init();
		$timeout = 5;
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
		curl_setopt($ch,CURLOPT_CAINFO, dirname(__FILE__)."/fb_cert.crt");
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
		
	}
	
	function fb_createsess($key, $val) { 
	
		$_SESSION[$this->fb_sess.$key]= $val;
		
	}
	
	function JsonDec($data) {
	
		$data = json_decode($data, true);
		return $data;
		
	}

}