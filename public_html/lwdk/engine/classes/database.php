<?php
    /*
    DOCUMENTACAO:

        Esta classe pertence ao escopo do LWDK - Light Weight Development Kit (PHP).

        Uso: $ctx->database->{funcao}();

        $ctx->database->set(@[string]{file}, @[string|array]{key}, [string]{value}) -> Define um valor direto em um banco de dados.

        ...TERMINAR DE ESCREVER PARA NAO ESQUECER DPS...
        KKKKKKKKKKKKKKKKKK
    */

    class __database {
        private $password = "none";
        private $backupToRepeatRequest = "none";
        public  $debug = true;

        private function backupToPreventRepeat(){
            if($this->backupToRepeatRequest === "none"){
                return false;
            } else {
                return $this->backupToRepeatRequest;
            }
        }

        private function saveData($file, $content){
            $this->backupToRepeatRequest = is_array($content) ? $content[1]:unserialize($content);
            return !!file_put_contents($file, is_array($content) ? $content[1]:($content));
        }

        private function like(String $needle, String $haystack, String $options = ""){
			$needle = implode("\/",explode("/",$needle));
			$needle = preg_replace(array("/(á|à|ã|â|ä)/","/(Á|À|Ã|Â|Ä)/","/(é|è|ê|ë)/","/(É|È|Ê|Ë)/","/(í|ì|î|ï)/","/(Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö)/","/(Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü)/","/(Ú|Ù|Û|Ü)/","/(ñ)/","/(Ñ)/","/(ç)/","/(Ç)/"),explode(" ","a A e E i I o O u U n N c C"),$needle);
			$haystack = preg_replace(array("/(á|à|ã|â|ä)/","/(Á|À|Ã|Â|Ä)/","/(é|è|ê|ë)/","/(É|È|Ê|Ë)/","/(í|ì|î|ï)/","/(Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö)/","/(Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü)/","/(Ú|Ù|Û|Ü)/","/(ñ)/","/(Ñ)/","/(ç)/","/(Ç)/"),explode(" ","a A e E i I o O u U n N c C"),$haystack);
			$match = @preg_match( "/^" . str_replace( '%', '(.*?)', trim($needle)) .  "$/{$options}", trim($haystack) ) || ($options=="i"&&!!preg_match( "/^" . str_replace( '%', '(.*?)', trim(strtolower($needle))) .  "$/{$options}", strtolower(trim($haystack))));
			return!!($match);
		}

        private function getCurrentDateTime(){
            return array(array(date("d"),date("m"),date("Y")),array(date("H"),date("i"),date("s")));
        }

        private function readQuery(String $query){
            $conditions = preg_split('/ (and|\&\&) /', $query);

            foreach($conditions as $key1=>$value1){
                $conditions[$key1] = preg_split('/ (or|\|\|) /', $value1);
                foreach($conditions[$key1] as $key2=>$value2){
                    if($this->like("%\!\=%", $conditions[$key1][$key2])){
                        $conditions[$key1][$key2] = array(0, array_map('trim', explode("!=", $value2)));
                    } elseif($this->like("%\=%", $conditions[$key1][$key2])){
                        $conditions[$key1][$key2] = array(1, array_map('trim', explode("=", $value2)));
                    } elseif($this->like("%\>%", $conditions[$key1][$key2])){
                        $conditions[$key1][$key2] = array(2, array_map('trim', explode(">", $value2)));
                    } elseif($this->like("%\<%", $conditions[$key1][$key2])){
                        $conditions[$key1][$key2] = array(3, array_map('trim', explode("<", $value2)));
                    } elseif($this->like("%\~%", $conditions[$key1][$key2])){
                        $conditions[$key1][$key2] = array(4, array_map('trim', explode("~", $value2)));
                    }
                }
            }

            return $conditions;
        }

        public function path($file){
            return (new __paths)->get()->database . "/{$file}.data";
        }

        public function getAll($database){
            return $this->query($database, "@ID != -1");
        }

        public function set(String $file, $key, $value=null){
            if($value != "rewrite"){
				$content = $this->get($file);
			} else {
				$content = [];
			}

			$file = $this->path($file);

            if(is_array($key)){
                foreach($key as $keyword=>$value){
                    $content[$keyword] = $value;
                }
            } else {
                $content[$key] = $value;
            }

            $content = serialize($content);
            if($this->password != "none"){
                $content = array(crypto::crypt($content, $this->password),$content);
            }
            $this->saveData($file, $content);
        }

        public function clean($file, String $mainId = "@ID", Array $filter = array(), String $by = "@ID > -1"){
            if(count($filter) === 0){
                $filter = "*";
            }
            $data = is_array($file) ? $file : $this->getAll($file);
            $result = array();
            $registered = array();
            foreach($data as $content){
        		if(isset($content[$mainId])){
                    $id = sha1($content[$mainId]);
                    $hash = md5(serialize($content));
                    if(!in_array($hash, $registered)){
                        $result[$id] = $content;
                        $registered[] = $hash;
                    }
                }
            }
            return $this->query(array_values($result), $by, $filter);
        }

        public function get(String $file, $key="*", $primary_key_set = true){
            $_file = $this->path($file);
            $content = array();
            if(file_exists($_file)){
                $content = file_get_contents($_file);
                if($this->password != "none"){
                    $content = crypto::unCrypt($content, $this->password);
                }
                $content = @unserialize($content);
            }

            if($content == false){
                return array();
            }

            if(is_array($key)){
                $result = array();
                foreach($key as $keyword){
                    $result[$keyword] = $content[$keyword];
                }
            } elseif($key == "*") {
                $result = $content;
            } else {
                $result = $content[$key];
            }

			// var_dump($result);

            if(is_array($result)){
				foreach($result as $key=>$value){
	                if($value == -1){
	                    unset($result[$key]);
	                } elseif($primary_key_set && is_array($result[$key])){
	                    $result[$key]["@ID"] = $key;
	                }
	            }
			}

            return $result;
        }

        public function push(String $file, $key, $value=null){
            $content = $this->get($file, "*", false);
            $file = $this->path($file);

            if(is_array($key)){
                if($value === "log_remove"){
                    $log = false;
                } else {
                    $log = true;
                }
                foreach($key as $keyword=>$value){
                    if(is_array($value) && $log){
                        $value["@CREATED"] = $this->getCurrentDateTime();
                    }
                    $content[] = $value;
                }
            } else {

                if(is_array($value)){
                    $value["@CREATED"] = $this->getCurrentDateTime();
                }
                $content[$key] = $value;

            }

            $lastid = count($content)-1;

            $content = serialize($content);
            if($this->password != "none"){
                $content = array(crypto::crypt($content, $this->password),$content);
            }

            return $this->saveData($file, $content);

            // return $lastid;
        }

        public function query($file, String $query, $keys = "*", bool $ignoreCase = true){
            if($this->backupToPreventRepeat()!==false && $this->backupToPreventRepeat()[0] === $file){
                $content = $this->backupToPreventRepeat()[1];
            }

            elseif(is_array($file)){
                $content = $file;
            } else {
                $content = $this->get($file);
                $this->backupToRepeatRequest = array($file, $content);
            }

            $results = [];

            $queryTanslate = $this->readQuery($query);

            foreach($content as $key=>$value){
                if(is_array($content[$key])){

                    $content[$key]["@ID"] = $key;

                    $findGlobal = true;
                    // print_r($queryTanslate);
                    foreach($queryTanslate as $andKeyword){
                        $find = false;
                        foreach($andKeyword as $orKeyword){
                            switch($orKeyword[0]){
                                case 0:
                                    $find = ($find || (isset($content[$key][$orKeyword[1][0]]) && !$this->like($orKeyword[1][1],$content[$key][$orKeyword[1][0]],($ignoreCase?"i":""))));
                                break;
                                case 1:
                                    // var_dump("{$content[$key][$orKeyword[1][0]]}-{$orKeyword[1][1]}") . "\n";
                                    $find = ($find || (isset($content[$key][$orKeyword[1][0]]) && $this->like($orKeyword[1][1],$content[$key][$orKeyword[1][0]],($ignoreCase?"i":""))));
                                break;
                                case 2:
                                    $find = ($find || (isset($content[$key][$orKeyword[1][0]]) && ((int)$orKeyword[1][1]<(int)$content[$key][$orKeyword[1][0]])));
                                break;
                                case 3:
                                    $find = ($find || (isset($content[$key][$orKeyword[1][0]]) && ((int)$orKeyword[1][1]>(int)$content[$key][$orKeyword[1][0]])));
                                break;
                                case 4:
                                    if(isset($content[$key][$orKeyword[1][0]]) && is_array($content[$key][$orKeyword[1][0]])){
                                        $string_array = array((string)$orKeyword[1][1],array());
                                        $number_array = array((int)$orKeyword[1][1],array());

                                        foreach((array)$content[$key][$orKeyword[1][0]] as $val){
                                            $string_array[1][] = (string)$val;
                                            $number_array[1][] = (int)$val;
                                        }

                                        $find = ($find || in_array($string_array[0],$string_array[1]));
                                        $find = ($find || in_array($number_array[0],$number_array[1]));
                                    }
                                break;
                            }
                        }

                        // echo "\nEND-OF-OR:" . print_r($find, true) . "\n";
                        $findGlobal = $findGlobal && $find;
                    }

                    // echo "\nEND-OF-AND:" . print_r($findGlobal,true) . "\n";

                    $result = array();

                    if($findGlobal){
                        if(is_array($keys)){
                            foreach($keys as $keyword){
                                if(isset($content[$key][$keyword])){
									$result[$keyword] = $content[$key][$keyword];
								}

                                // $test = json_encode($content[$key][$keyword]);
                                // echo "\n\n-IF1::incorrect::{$test}-\n\n";
                            }
                        } elseif($keys == "*") {
                            $result = $content[$key];
                            $test = json_encode($content[$key]);
                            // echo "\n\n-IF2::incorrect::{$test}-\n\n";
                        } else {
                            if(preg_match_all("/\//",$keys) === 1){
                                $keys_copy = explode("/", $keys);
                                $results[$content[$key][$keys_copy[0]]] = $content[$key][$keys_copy[1]];
                                // echo "\n\n-IF3::correct::{$content[$key][$keys_copy[1]]}-\n\n";
                            } else {
                                $result = $content[$key][$keys];
                                $test = $content[$key][$keys];
                                // echo "\n\n-IF4::incorrect::{$test}-\n\n";
                            }
                        }
                        if(!(is_array($result) && count($result) == 0)){
                            $results[] = $result;
                        }
                    }
                }
            }
            return $results;
        }

        public function setWhere(String $file, String $by, $key, $value=null){
            $content = $this->get($file);
            $ids = $this->query($file, $by, array("@ID"));
            $file = $this->path($file);

            // var_dump($ids);

            foreach($ids as $id){
                $id = $id["@ID"];
                if(is_array($key)){
                    // var_dump($content[$id]);
                    foreach($key as $keyword => $value){
                        $content[$id][$keyword] = $value;
                        // var_dump($content[$id][$keyword]);
                        // var_dump($keyword);
                    }
                } else {
                    $content[$id][$keys] = $value;
                }
                // $content[$id]["@MODIFIED"] = $this->getCurrentDateTime();
            }

            $content = serialize($content);
            if($this->password != "none"){
                $content = array(crypto::crypt($content, $this->password),$content);
            }
            return $this->saveData($file, $content);
        }

        public function deleteWhere(String $file, String $by){
            $content = $this->get($file);
            $ids = $this->query($file, $by, array("@ID"));

            if(count($ids) < 1)return false;

            $file = $this->path($file);

            foreach($ids as $id){
                $content[$id["@ID"]] = -1;
            }

            $content = serialize($content);
            if($this->password != "none"){
                $content = array(crypto::crypt($content, $this->password),$content);
            }
            return $this->saveData($file, $content);
        }

        public function setPassword(String $password){
            $this->password = md5($password);
        }

        public function removePassword(){
            $this->password = "none";
        }

        public function protectOnPassword(String $file, String $password){
            $this->removePassword();
            $content = $this->get($file);
            if(count($content) == 0)return false;
            // var_dump($content);
            // exit;
            $content = serialize($content);
            // var_dump($content);
            // exit;
            $this->setPassword($password);
            $content = array(crypto::crypt($content, $this->password),$content);
            // var_dump($content);
            // exit;
            $this->saveData($this->path($file), $content);
        }

        public function unProtectPassword(String $file, String $password){
            $this->setPassword($password);
            $content = $this->get($file);
            // var_dump($content);
            if(count($content) == 0)return false;
            $content = serialize($content);
            // var_dump($content);
            $this->saveData($this->path($file), $content);
            $this->removePassword();
        }

        public function changePassword(String $file, String $oldPassword, String $password){
            $this->setPassword($oldPassword);
            $content = $this->get($file);
            if(count($content) == 0)return false;
            $content = serialize($content);
            $this->setPassword($password);
            $content = array(crypto::crypt($content, $this->password),$content);
            $this->saveData($this->path($file), $content);
        }

        public function newID($db,$extraquery="",$mainkey="id"){
            do {
                $id = (string)mt_rand(1,64369026654353);
            } while(count($this->query("{$db}", "{$mainkey} = {$id}" . (empty("{$extraquery}")?"":" and {$extraquery}")))>0);

            return $id;
        }

		public function rewrite(String $db, $content = []){
			$this->set($db, $content, "rewrite");
		}
	}
?>
