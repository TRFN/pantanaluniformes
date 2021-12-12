<?php
/*
    VER: 1.0
    LAST-UPDATE: 17/11/2021
*/

    function ctrl_util_dates($args){

        /* EXTENSAO DE CLASSE CASO NECESSARIO */

        // (new APPControls)->loadPlugin("plugin_exemplo");

        return new class extends APPControls {
			protected $instance = null;

			function set(String $date){
				return ($this->instance = new DateTime($date));
			}

			private function loaded(){
				return !($this->instance === null);
			}

			function sum(String $qtd){
				if(!$this->loaded())return false;
				return $this->instance->add(DateInterval::createFromDateString($qtd));
			}

			function sub(String $qtd){
				if(!$this->loaded())return false;
				return $this->instance->sub(DateInterval::createFromDateString($qtd));
			}

			function get(String $format){
				if(!$this->loaded())return false;
				return $this->instance->format($format);
			}

			function filter(String $secondDate, String $key, Array $data){
				$dates = [];

				$dates[0] = strtotime($this->instance->format("Y-m-d"));
				$dates[1] = strtotime($secondDate);

				sort($dates);

				$output = [];

				foreach(array_keys($data) as $i){
					$dcomp = strtotime($data[$i][$key]);
					if($dcomp >= $dates[0] && $dcomp <= $dates[1]){
						$output[] = $data[$i];
					}
				}

				return $output;
			}
        };
    }
