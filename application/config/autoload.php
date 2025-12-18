<?php
    defined('BASEPATH') OR exit('No direct script access allowed');

    $autoload['packages']  = array();
    $autoload['libraries'] = array('database','session','satusehat');
    $autoload['drivers']   = array();
    $autoload['helper']    = array('url','curl','satusehat');
    $autoload['config']    = array();
    $autoload['language']  = array();
    $autoload['model']     = array();
    $autoload['time_zone'] = date_default_timezone_set('Asia/Jakarta');
?>