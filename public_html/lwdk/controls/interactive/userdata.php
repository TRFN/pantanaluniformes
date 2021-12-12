<?php

/*
    VER: 1.0
    LAST-UPDATE: 30/04/2021
*/

function ctrl_interactive_userdata(){
    $instance = new class extends APPControls {
        private $id = null;
        private $time = 0;

        private function getSeconds($time=-1){
            return $time==-1?(int)strtotime(date("d-m-Y H:i:s")):(int)strtotime("January 1 1970 {$time}")-10800;
        }

        function __construct(){
            $this->id = md5($_SERVER['REMOTE_ADDR']);
            $this->Timeout("23:59:60");
        }

        function Timeout(String $time){
            $this->time = $this->getSeconds($time);
        }

        function Get($_key=-1){
            $query = $this->database()->get("userdata",$this->id);
            $query = (!is_array($query)?[]:$query);
            $preserve = array();

            foreach($query as $key=>$value){
                if(($this->getSeconds() - (int)$query[$key]["t"]) > $this->time && $this->time > 0){
                    unset($query[$key]);
                } else {
                    $preserve[$key] = $query[$key];
                    unset($preserve[$key]["@ID"]);
                    $query[$key] = $query[$key]["v"];
                }
            }

            $this->database()->set("userdata", $this->id, $preserve);
            $result = $_key==="preserved_data"?$preserve:($_key===-1?$query:(isset($query[$_key])?$query[$_key]:false));

            return $result;
        }

        function Set($data, $value=null, $preset=null){
            $array = $preset==null ? $this->Get("preserved_data"):$preset;
            if($value===null && is_array($data)){
                foreach(array_keys($data) as $key){
                    $array[$key] = array("v"=>$data[$key],"t"=>$this->getSeconds());
                }
            } else {
                $array[$data] = array("v"=>$value,"t"=>$this->getSeconds());
            }

            $this->database()->set("userdata", $this->id, $array);
        }

        function Delete($key="__clear_cookie__"){
            if($key !== "__clear_cookie__"){
                $data = $this->Get("preserved_data");
                if(isset($data[$key])){
                    unset($data[$key]);
                } else {
                    $key = explode(";", $key);
                    foreach($key as $delete){
                        if(isset($data[($delete=trim($delete))])){
                            unset($data[$delete]);
                        }
                    }
                }
            } else {
                $data = [];
            }

            if(is_int($key)){
                $data = array_values($data);
            }

            return $this->database()->set("userdata", $this->id, $data);
        }

        function Find($by, $sub="__none__"){
            $data = $this->Get("preserved_data");
            if($sub !== "__none__" && isset($data[$sub]) && is_array($data[$sub])){
                $data = $data[$sub];
            }

            $newdata = array();
            $output = array();

            foreach($data as $key=>$value){
                $newdata[] = array("value"=>json_encode(array("{$key}" => $value)));
            }

            foreach($this->database()->query($newdata, "value=%{$by}%") as $query){
                $value = json_decode($query["value"], true);
                unset($value["@ID"]);
                $output = array_merge($output, $value);
            }

            return $output;
        }

        function Push($data){
            $array = array_values($this->Get("preserved_data"));

            $array[] = array("v"=>$data,"t"=>$this->getSeconds());

            $this->database()->set("userdata", $this->id, $array);
        }
    };

    return $instance;
}
