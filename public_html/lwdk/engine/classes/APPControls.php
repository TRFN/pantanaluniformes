<?php
    class APPControls {
        function database(){
            return new __database;
        }

        function loadPlugin(String $plugin){
            require_once dirname(dirname(dirname(__FILE__))) . "/plugins/" . preg_replace("/\@/","/",$plugin) . ".php";
            $plugin = @end(explode("@", $plugin));
            return new $plugin;
        }

        function free(){
            unset($this->args["ux"]);
            unset($this->args["lwdk"]);
        }
    }
?>
