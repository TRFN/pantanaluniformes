<?php

/*
    VER: 1.2
    LAST-UPDATE: 24/04/2021
*/

function ctrl_users_session($args){
    $instance = new class extends APPControls {
        public $keyid    = "usuario";
        public $database = "usuarios";
        public $keyuser  = "email";
        public $keypass  = "senha";
        public $hash     = "md5";
        public $mainkey  = "@ID";
		public $expires  = 0;

        private function getSeconds($time=-1){
            return $time==-1?(int)strtotime(date("d-m-Y H:i:s")):(int)strtotime("January 1 1970 {$time}")-10800;
        }

        function session($timer=true){
			if($this->expires() > 0){
				if(($this->getSeconds() - $this->connTime()) > $this->expires()){
					$this->logout();
				} else {
					if($timer){
						$this->connTime($this->getSeconds());
					}
				}
			}

            if(isset($_SESSION[$this->keyid])){
                if(is_string($this->database) && count($user = $this->database()->query("{$this->database}", "{$this->mainkey} = {$_SESSION[$this->keyid]}")) === 1){
                    return (Object)$user[0];
                } elseif(is_array($this->database)) {
                    foreach($this->database as $db){
                        if(count($user = $this->database()->query("{$db}", "{$this->mainkey} = {$_SESSION[$this->keyid]}")) === 1){
                            return (Object)$user[0];
                        }
                    }
                }
            }

            return false;
        }

        function connect($keyuser, $keypass){
            switch($this->hash){
                case "md5": $keypass = md5($keypass); break;
            }

            $user = array();

			// header("Content-type: application/json");
			//
			// exit(json_encode(["{$this->database} => {$this->keyuser} = {$keyuser} and {$this->keypass} = {$keypass}",$this->database()->query("{$this->database}", "{$this->keyuser} = {$keyuser} and {$this->keypass} = {$keypass}"),$this->database()->getAll("{$this->database}")]));

            if(is_string($this->database)){
                $user = $this->database()->query("{$this->database}", "{$this->keyuser} = {$keyuser} and {$this->keypass} = {$keypass}");
            } elseif(is_array($this->database)) {
                foreach($this->database as $db){
                    $user = $this->database()->query("{$db}", "{$this->keyuser} = {$keyuser} and {$this->keypass} = {$keypass}");
                    if(count($user) === 1){
                        break;
                    }
                }
            }

            $result = count($user) === 1 ? (function($user,$mainkey,$ctx){
				$ctx->connTime($ctx->getSeconds());
                $_SESSION[$this->keyid] = $user[0][$mainkey];
                return $user[0][$mainkey];
            })($user,$this->mainkey,$this):false;

            return $result;
        }

        function logout(){
            session_unset();
        }

		private function connTime(int $time=-1){
			return $time === -1
				? (isset($_SESSION["connTime"])?$_SESSION["connTime"]:-1)
				: ($_SESSION["connTime"]=$time);
		}

		function expires(int $time=-1){
			return $time === -1
				? (isset($_SESSION["expiresTime"])?$_SESSION["expiresTime"]:0)
				: ($_SESSION["expiresTime"]=$time);
		}
    };

    $instance->args = $args;

    return $instance;
}
