<?php
class Dmz_Template Extends Dmz_Core {

    protected $_isDisplayed     = false;
    protected $_minify          = false;
    protected $_tidy            = false;
    protected $_docType         = 'html';
    protected $_lang            = 'en-US';
    protected $_charset         = 'utf-8';
    protected $_title           = '';
    protected $_description     = '';
    protected $_keywords        = '';
    protected $_favicon         = '';
    protected $_meta            = '';
    protected $_css             = '';
    protected $_jsTop           = '';
    protected $_jsBottom        = '';
    protected $_jsTopLiteral    = '';
    protected $_jsBottomLiteral = '';
    protected $_customHeader    = '';
    protected $_body            = '';

    public static function getInstance() {
        return parent::_getInstance(__CLASS__);
    }

    public function docType($str) {
        $this->_docType = $str;
        return $this;
    }

    public function lang($str) {
        $this->_lang = $str;
        return $this;
    }

    public function charset($str) {
        $this->_charset = $str;
        return $this;
    }

    public function title($str) {
        $this->_title = $str;
        return $this;
    }

    public function description($str) {
        $this->_description = $str;
        return $this;
    }

    public function keywords($str) {
        $this->_keywords = $str;
        return $this;
    }

    public function favicon($src, $type = 'image/ico') {
        $this->_favicon = '<link rel="shortcut icon" href="' . $src . '" type="' . $type . '" />' . "\n";
        return $this;
    }

    public function addMeta($attr = array()) {
        $attr = $this->_createAttr($attr);
        $this->_meta .= '<meta' . $attr . ' />' . "\n";
        return $this;
    }

    public function addCss($src, $attr = array()) {
        $attr = $this->_createAttr($attr);
        $this->_css .= '<link rel="stylesheet" type="text/css" href="' . $src . '"' . $attr . ' />' . "\n";
        return $this;
    }

    public function addJs($src, $attr = array()) {
        $attr = $this->_createAttr($attr);
        $this->_jsTop .= '<script type="text/javascript" src="' . $src . '"' . $attr . '></script>' . "\n";
        return $this;
    }

    public function addJsBottom($src, $attr = array()) {
        $attr = $this->_createAttr($attr);
        $this->_jsBottom .= '<script type="text/javascript" src="' . $src . '"' . $attr . '></script>' . "\n";
        return $this;
    }

    public function addJsLiteral($str) {
        $this->_jsTopLiteral .= '<script type="text/javascript">' . $str . '</script>' . "\n";
        return $this;
    }

    public function addJsBottomLiteral($str) {
        $this->_jsBottomLiteral .= '<script type="text/javascript">' . $str . '</script>' . "\n";
        return $this;
    }

    public function addBody($html = '') {
        $this->_body .= $html;
        return $this;
    }

    public function display($file) {
        $output = '';
        ob_start();
        if(file_exists($file)) {
            include $file;
        }
        $output = ob_get_contents();
        ob_end_clean();
        $this->addBody($output);
    }

    public function returnOutput($file) {
        $this->_isDisplayed = true;
        $output = '';
        ob_start();
        if(file_exists($file)) {
            include $file;
        }
        $output = ob_get_contents();
        ob_end_clean();
        return $this->minify($output);
    }

    public function minify($html) {
        $search = array('/\>[^\S ]+/s', '/[^\S ]+\</s', '/(\s)+/s');
        $replace = array('>', '<', '\\1');
        $html = preg_replace($search, $replace, $html);
        return $html;
    }

    public function getOutput() {
        $n = "\n";
        $html[] = '<!DOCTYPE ' . $this->_docType . '>'.$n;
        $html[] = '<html lang="' . $this->_lang . '">'.$n;
        $html[] = '<head>'.$n;
        $html[] = '<meta http-equiv="Content-Type" content="text/html; charset=' . $this->_charset . '">'.$n;
        $html[] = '<title>' . $this->_title . '</title>'.$n;
        $html[] = '<meta name="description" content="' . $this->_description . '" />'.$n;
        $html[] = '<meta name="keywords" content="' . $this->_keywords . '" />'.$n;
        $html[] = $this->_meta;
        $html[] = $this->_favicon;
        $html[] = $this->_css;
        $html[] = $this->_jsTop;
        $html[] = $this->_jsTopLiteral;
        $html[] = $this->_customHeader;
        $html[] = '</head>'.$n;
        $html[] = '<body>'.$n;
        $html[] = $this->_body;
        $html[] = $this->_jsBottom;
        $html[] = $this->_jsBottomLiteral;
        $html[] = '</body>'.$n;
        $html[] = '</html>'.$n;

        header('Content-type: text/html; charset=' . $this->_charset);

        echo implode("", $html);
    }

    public function __destruct() {
        if(!$this->_isDisplayed) {
            $this->getOutput();
        }
    }

    protected function _createAttr($attr = array()) {
        $attributes = '';
        if (is_array($attr) && $attr != NULL) {
            foreach ($attr as $key => $value) {
                $attributes .= ' ' . $key ;
                if (!empty($value)) {
                    $attributes .= '="' . $value . '"';
                }
            }
            
            $attributes = rtrim($attributes);
        }

        return $attributes;
    }

}