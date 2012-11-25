<?php

function d($var)
{
    if (Oryzias\Config::get('debug')) {
        echo '<pre>';
        var_dump($var);
        echo '</pre><hr />';
    }
}

function dd($var)
{
    if (Oryzias\Config::get('debug')) {
        d($var);
        exit;
    }
}
