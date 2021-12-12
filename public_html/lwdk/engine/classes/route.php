<?php
    trait route {
        private $Applications;
        private $app = false;

        function __construct(){
            $this->Applications = array();
        }

        function addApp(APPObject $class, lwdk $parent, bool $default = false){
            $class->setParent($parent);

            if($default){
                array_unshift($this->Applications, $class);
            } else {
                $this->Applications[] = $class;
            }
        }

        function selectApp(){
            $this->app = $this->checkDir();
        }

        function url(int $index=-1, String $url = "empty", $shift=-1){
            $shift = $shift == -1 ? count($this->url(-1, $this->getApp()->rootDir(), 1)):$shift;
            $url = $url == "empty" ? $_SERVER["REQUEST_URI"]:$url;
            $url = explode("/", $url);

            for($i = 0; $i < (int)$shift; $i++){
                array_shift($url);
            }

            return $index == -1 ? $url:(isset($url[$index])?$url[$index]:"");
        }

        function checkDir(){
            $thisApplication = -1;
            foreach($this->Applications as $key=>$application){
                for($i = 0; $i < count($this->url(-1, $application->rootDir()))-1; $i++){
                    if($this->url($i, $application->rootDir(), 1) == $this->url($i,$_SERVER["REQUEST_URI"], 1)){
                        $thisApplication = $key;
                    }
                }
            }
            if($thisApplication > -1){
                return $thisApplication;
            }
            return 0;
        }

        function getApp(){
            return $this->Applications[$this->app];
        }
    }
?>
