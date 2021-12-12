<?php
	trait helper_produtos {
		function loja_produtos($queryby="@ID > -1",$basics="ativo = true",$filter = "quantidade > 0"){
			$query = (parent::database()->query("produtos", "{$basics} && {$queryby}"));

			foreach(array_keys($query) as $i){
				if(!isset($query[$i]["quantidade"]) || empty($query[$i]["quantidade"]) || (int)$query[$i]["quantidade"] == 0){
					$query[$i]["quantidade"] = 0;

					if(isset($query[$i]["quantidade_estoque_barreiro"])){
						$query[$i]["quantidade"] += (int)$query[$i]["quantidade_estoque_barreiro"];
					}

					if(isset($query[$i]["quantidade_estoque_funcionarios"])){
						$query[$i]["quantidade"] += (int)$query[$i]["quantidade_estoque_funcionarios"];
					}

					if(isset($query[$i]["quantidade_estoque"])){
						$query[$i]["quantidade"] += (int)$query[$i]["quantidade_estoque"];
					}

					$query[$i]["quantidade_estoque"] = (string)$query[$i]["quantidade"];
				}

				unset($query[$i]["quantidade_estoque_funcionarios"]);
				unset($query[$i]["quantidade_estoque_barreiro"]);
			}

			return parent::database()->query($query, "{$filter}");
		}
	}
?>
