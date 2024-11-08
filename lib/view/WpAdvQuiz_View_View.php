<?php

class WpAdvQuiz_View_View
{

    private $data = array();

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function __get($name)
    {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }
    }

    public static function admin_notices($msg, $type = 'error')
    {
        if ($type === 'info') {
            echo '<div class="updated"><p><strong>' . esc_html($msg) . '</strong></p></div>';
        } else {
            echo '<div class="error"><p><strong>' . esc_html($msg) . '</strong></p></div>';
        }
    }

    public function redirect($url)
    {

    }

    public function checked($v, $check = true, $echo = true)
    {

        $r = ($v == $check) ? 'checked="checked"' : '';

        if ($echo) {
            echo esc_html($r);
        } else {
            return $r;
        }
    }

    public function selected($v, $check = true, $echo = true)
    {
        $r = ($v == $check) ? 'selected="selected"' : '';

        if ($echo) {
            echo esc_html($r);
        } else {
            return $r;
        }
    }

    public function selectedArray($v, $check)
    {
        $a = array();

        foreach ($check as $c) {
            $a[] = ($v == $c) ? 'selected="selected"' : '';
        }

        return $a;
    }

    public function isDisplayNone($v)
    {
		$vo = $v ? '' : 'style="display:none;"';
		$vo = str_replace('&quot;', '', esc_attr($vo));
        echo $vo;
    }
}
