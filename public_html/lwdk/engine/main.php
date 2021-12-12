<?php
    session_start();
    // set_time_limit(0);
    error_reporting(E_ALL);
    ini_set('memory_limit','-1');
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    date_default_timezone_set('America/Sao_Paulo');

    foreach(glob("lwdk/engine/classes/*.php") as $file){
        require_once $file;
    }

    foreach(glob("lwdk/sections/*/*/*/*.php") as $file){
        require_once $file;
    }

    foreach(glob("lwdk/sections/*/*/*.php") as $file){
        require_once $file;
    }

    foreach(glob("lwdk/sections/*/*.php") as $file){
        require_once $file;
    }

    foreach(glob("lwdk/sections/*.php") as $file){
        require_once $file;
    }

    spl_autoload_register(function($c) {
        // echo $c;
        include(__paths::get()->templates . "/ux/{$c}.php");
    });

    class lwdk {

        private $msgs = array();

        public function setup(){
            $this->path = __paths::get();
            $this->database = new __database($this);
        }

        public function message(String $msg){
            $msg = "\n{$msg}<br /><br />\n\n";
            if(!in_array($msg, $this->msgs)){
                $this->msgs[] = $msg;
                echo $msg;
            }
        }

        public function renderApp(){
            $this->selectApp();
            $this->getApp()->getPage();
        }
    }
?>
