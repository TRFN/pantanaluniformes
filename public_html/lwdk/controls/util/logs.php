<?php
/*
    VER: 1.0
    LAST-UPDATE: 22/11/2021
*/

    function ctrl_util_logs($args){
        $instance = new class extends APPControls {
			function push(Array $what){
				$this->database()->push("logs/" . $this->nowid(), array($what), "log_remove");
			}

			function nowid(){
				$id = strtotime(date("Y-m-d"));
				$query = $this->database()->query("logs/indexes", "i = {$id}");
				if(count($query) == 0){
					$this->database()->push("logs/indexes", [["i" => $id]], "log_remove");
				}
				return $id;
			}

			function ipdata(){
				if(!isset($_COOKIE["ipdata"]) || is_null($_COOKIE["ipdata"])){
					$cookie = $this->parent->control("interactive/ip")->get("*", true);
					setrawcookie("ipdata", urlencode(serialize($cookie)), time()+(60*30));
				} else {
					$cookie = unserialize(urldecode($_COOKIE["ipdata"]));
				}

				return(object)$cookie;
			}

			function sessao(){
				return $this->parent->admin_sessao();
			}

			function indexes(){
				return $this->database()->getAll("logs/indexes");
			}

			function actions($by="*", $day = "*"){
				$table = [
					"name" => "1",
					"email" => "2",
					"ip" => "3",
					"act" => "6"
				];

				$query = [];

				if($by !== "*"){
					foreach(explode(";", $by) as $term){
						$term = explode(":", $term);
						if(isset($table[$term[0]])){
							$query[] = "{$table[$term[0]]} = %{$term[1]}%";
						}
					}
				}

				if(count($query) == 0){
					$query = "@ID > -1";
				} else {
					$query = implode(" and ", $query);
				}

				// echo $query;

				if($day == "*"){
					$day = [];
					foreach($this->indexes() as $index){
						$day[] = $index["i"];
					}
				} else {
					$day = [$day];
				}

				$output = [];

				foreach($day as $log){
					$output = array_merge($output, $this->database()->query("logs/{$log}", $query));
				}

				return $output;
			}

			function post($message="", $forced = -1){

				if($forced == -1 && $this->sessao() !== false){
					$u = [$this->sessao()->nome, $this->sessao()->email];
				} elseif(is_array($forced)) {
					$u = $forced;
				} else {
					$u = false;
				}

				if($u !== false){

					$data = [];

					$data[] = strtotime(date("c"));   # Data e hora
					$data[] = $u[0];                  # Nome do usuario
					$data[] = $u[1];                  # Email do usuario
					$data[] = $this->ipdata()->ip;    # IP do usuario

					/* Cidade, estado e país */

					$data[] = "{$this->ipdata()->cidade} / {$this->ipdata()->estado}";
					$data[] = $this->ipdata()->pais;

					$data[] = $message; # Mensagem / Observação
					$this->push($data);
				} else {
					return false;
				}
			}
        };

		$instance->parent = $args["ux"];

		return $instance;
    }
