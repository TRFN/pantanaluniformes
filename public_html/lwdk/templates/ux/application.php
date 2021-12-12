<?php
    class application extends APPObject {
        function __construct(){
            # CONFIGURATIONS #
            $this->rootDir("/");
        }

        function page_main($content){
			header("Content-Type: text/plain");
            var_dump($content);
        }
    }
?>
