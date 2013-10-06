<?php
define('CHATBX_DEVELOPMENT', true);
define('CHATBX_PROTOCOL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https' : 'http');
define('CHATBX_HOST_NAME', $_SERVER['HTTP_HOST']);
define('CHATBX_BASE_URI', '/myworks/chatbx');
define('CHATBX_ROOT', CHATBX_PROTOCOL . '://' . CHATBX_HOST_NAME . CHATBX_BASE_URI);
define('CHATBX_THEME_DIR', CHATBX_ROOT.'/themes');
define('CHATBX_CSS_DIR', CHATBX_THEME_DIR.'/css');
define('CHATBX_JS_DIR', CHATBX_THEME_DIR.'/js');
define('CHATBX_IMG_DIR', CHATBX_THEME_DIR.'/img');