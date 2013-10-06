<?php
class index Extends Dmz_Core {

	public function init($name) {
		$this->template()
				->title("Chatbx - Dmz")
				->addMeta(array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1.0'))
				->favicon(CHATBX_THEME_DIR."/favicon.ico")
				->addCss(CHATBX_CSS_DIR."/bootstrap.min.css")
				->addCss(CHATBX_CSS_DIR."/font-awesome.min.css")
				->addCss(CHATBX_CSS_DIR."/style.css")
				->addCss(CHATBX_ROOT."/style")
				->addJsBottom(CHATBX_JS_DIR."/jquery.min.js")
				->addJsBottom(CHATBX_JS_DIR."/bootstrap.min.js")
				->addJsBottom(CHATBX_JS_DIR."/chatbx.core-re.js");

		if(!isset($_SESSION['user_id'])) {
			$this->template()->display(DIR_ROOT."/themes/connect.tpl");
		} else {
			$this->template()->display(DIR_ROOT."/themes/index.tpl");
		}

	}

}