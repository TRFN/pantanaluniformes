<?php
	function ctrl_interactive_ip($args){
	    return new class extends APPControls {
			private $instance;

			function __construct(){
				return $this->update();
			}

	        function update(){
	            return ($this->instance = (
					$this->loadPlugin("GeoLocation@ipdetails")
						->setip($this->__getIp())
						->scan()
						->close()
				));
	        }

			private function __getIp(){
				if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
				    return $_SERVER['HTTP_CLIENT_IP'];
				} elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				    return $_SERVER['HTTP_X_FORWARDED_FOR'];
				} elseif(!empty($_SERVER['REMOTE_ADDR'])) {
				    return $_SERVER['REMOTE_ADDR'];
				}

				return "127.0.0.1"; // Local IP
			}

			function get($by = "*", $array = false){
				$data = [];
				$data["status"] = isset($this->instance->details["geoplugin_status"]) && !empty($this->instance->details["geoplugin_status"]) && $this->instance->details["geoplugin_status"] == "200" ? "success":"fail";

				if($data["status"] == "success"){
					$data["ip"] = $this->instance->details["geoplugin_request"];
					$data["timezone"] = $this->instance->details["geoplugin_timezone"];
					$data["latitude"] = $this->instance->get_latitude();
					$data["longitude"] = $this->instance->get_longitude();
					$data["cidade"] = $this->instance->get_city();
					$data["estado"] = $this->instance->get_regioncode();
					$data["estadoNome"] = $this->instance->get_regionname();
					$data["pais"] = $this->instance->get_country();
					$data["paisCodigo"] = $this->instance->get_countrycode();
					$data["continenteCodigo"] = $this->instance->get_continentcode();
					$data["moedaCodigo"] = $this->instance->get_currencycode();
					$data["moedaSimbolo"] = htmlspecialchars_decode($this->instance->get_currencysymbol());				$data["cotacaoDolar"] = $this->instance->get_currencyconverter();
				} else {
					return false;
				}

				if($by === "*"){
					return !$array ? (object)$data : (array)$data;
				} else {
					if(is_array($by)){
						$gets = [];
						foreach($by as $term){
							if(isset($data[$term])){
								$gets[$term] = $data[$term];
							}
						}
						return !$array ? (object)$gets : (array)$gets;
					} else {
						if(isset($data[$by])){
							return $data[$by];
						} else {
							return false;
						}
					}
				}

				return false;
			}
		};
	}
