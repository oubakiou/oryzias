<?php
namespace Oryzias;

class Form
{
    public $outputCharset;
    public $echo;
    
    public function __construct($outputCharset = 'UTF-8', $echo = true)
    {
        $this->outputCharset = $outputCharset;
        $this->echo = $echo;
    }
    
    public function h($str)
    {
        return Util::h($str, ENT_QUOTES, $this->outputCharset);
    }
    
    public function build($name, $value=null, $attr=[])
    {
        $attr['name'] = $name;
        $attr['value'] = $value;
        if (!isset($attr['id'])) {
            $attr['id'] = $name . 'id';
        }
        return $this->buildAttr($attr);
    }
    
    public function buildAttr($attr = [])
    {
        $attrToken = '';
        foreach ($attr as $k=>$v) {
            $attrToken[] = $this->h($k) . '="' . $this->h($v) . '"';
        }
        return implode(' ', $attrToken);
    }
    
    public function output($html)
    {
        if ($this->echo) {
            echo $html;
        } else {
            return $html;
        }
    }
    
    public function text($name, $value=null, $attr=[])
    {
        $html = '<input type="text" ' . $this->build($name, $value, $attr) . ' />';
        $this->output($html);
    }
    
    public function textarea($name, $value=null, $attr=[])
    {
        $attr['name'] = $name;
        $html = '<textarea ' . $this->buildAttr($attr) . '>' . $this->h($value) . '</textarea>';
        $this->output($html);
    }
    
    public function password($name, $value=null, $attr=[])
    {
        $html = '<input type="password" ' . $this->build($name, $value, $attr) . ' />';
        $this->output($html);
    }
    
    public function select($name, $values=[], $attr=[], $selectedValue=null, $label = [])
    {
        $selectAttr = $attr;
        $selectAttr['name'] = $name;
        if (!isset($selectAttr['id'])) {
            $selectAttr['id'] = $name . '_id';
        }
        $html = '<select ' . $this->buildAttr($selectAttr) . ' >';
        
        foreach ($values as $k=>$v) {
            $optionAttr['value'] = $v;
            if ($v == $selectedValue) {
                $optionAttr['selected'] = 'selected';
            }
            $html .= '<option ' . $this->buildAttr($optionAttr) . ' />' . $label[$k];
        }
        $this->output($html);
    }
    
    public function radio($name, $values=[], $attr=[], $selectedValue=null, $label = [])
    {
        $html = '';
        foreach ($values as $k=>$v) {
            if ($v == $selectedValue) {
                $attr['checked'] = 'checked';
            }
            $attr['id'] = $name . '_id[' . $k . ']';
            $html .= '<input type="radio" ' . $this->build($name, $v, $attr) . ' />' . $label[$k];
        }
        $this->output($html);
    }
    
    public function checkbox($name, $values=[], $attr=[], $selectedValue=[], $label = [])
    {
        $html = '';
        foreach ($values as $k=>$v) {
            if (in_array($v, $selectedValue)) {
                $attr['checked'] = 'checked';
            }
            $attr['id'] = $name . '_id[' . $k . ']';
            $html .= '<input type="checkbox" ' . $this->build($name, $v, $attr) . ' />' . $label[$k];
        }
        $this->output($html);
    }
    
    public function hidden()
    {
        $html = '<input type="hidden" ' . $this->build($name, $value, $attr) . ' />';
        $this->output($html);
    }
    
    public function file($name, $value=null, $attr=[])
    {
        $html = '<input type="file" ' . $this->build($name, $value, $attr) . ' />';
        $this->output($html);
    }
    
    public function tel($name, $value=null, $attr=[])
    {
        $html = '<input type="tel" ' . $this->build($name, $value, $attr) . ' />';
        $this->output($html);
    }
    
    public function url($name, $value=null, $attr=[])
    {
        $html = '<input type="url" ' . $this->build($name, $value, $attr) . ' />';
        $this->output($html);
    }
    
    public function email($name, $value=null, $attr=[])
    {
        $html = '<input type="email" ' . $this->build($name, $value, $attr) . ' />';
        $this->output($html);
    }
    
    public function search($name, $value=null, $attr=[])
    {
        $html = '<input type="search" ' . $this->build($name, $value, $attr) . ' />';
        $this->output($html);
    }
}
