<?php
    trait function_plans {
		function plano_saldo(){
			return number_format($this->plano_saldo_float(), 2, ",", ".");
		}

		function meu_plano_atual_ativo(){
			switch($this->sessao()->category){
				case "anunciantes": $getby = "anunciante"; break;
				case "construtoras": $getby = "construtora"; break;
				case "clientes": $getby = -1; break;
			}

			$myplan = isset($this->sessao()->plan) ? $this->sessao()->plan:"null";

			$plano = parent::database()->query("planos", "ativo = true and id = {$myplan} and aplicarpara={$getby}",array(  "titulo","valor","qimov","qsimov","dimov","cor1","cor2","d-efec","ordem","aplicarpara"));
			if(count($plano) < 1){
				$plano = parent::database()->query("planos", "ativo = true and aplicarpara={$getby}",array(  "titulo","valor","qimov","qsimov","dimov","cor1","cor2","d-efec","ordem","aplicarpara"));
			}

			if(count($plano) == 0 && $getby === -1){
				return array(
					"cor1" => "#6FCFFD",
					"cor2" => "#1A9AA8",
					"d-efec" => "bottom",
					"plan_ativo" => "true",
					"plan_data_inicio" => date("Y-m-d"),
					"plan_prazo" => 10000,
					"plan_valor" => (float)0.00000000000001
				);
			}

			$plano = $plano[0];

			if(count($query = parent::database()->query("plano_usuarios", "plan_ativo = 1 or plan_ativo = true and user_id = " . $this->sessao()->id)) < 1){
				$defaults = array(
					"user_id" => $this->sessao()->id,
					"plan_id" => $myplan,
					"plan_data_inicio" => date("Y-m-d"),
					"plan_ativo" => false,
					"plan_prazo" => (int)$plano["dimov"],
					"plan_valor" => (float)str_replace(",",".",preg_replace("/[^0-9,]/","",str_replace("R$ ", "", $plano["valor"]))),
					"plan_valor_pago" => 0
				);

				$ret = array_merge($defaults, $plano);

				ksort($ret);

				return $ret;

			} else {
				// $defaults = array(
				// 	"user_id" => $this->sessao()->id,
				// 	"plan_id" => $this->sessao()->plan,
				// 	"plan_data_inicio" => date("Y-m-d"),
				// 	"plan_ativo" => false,
				// 	"plan_prazo" => (int)$plano["dimov"],
				// 	"plan_valor" => (float)str_replace(",",".",preg_replace("/[^0-9,]/","",str_replace("R$ ", "", $plano["valor"]))),
				// 	"plan_valor_pago" => 0
				// );
				//
				// foreach(array_keys($defaults) as $key_check){
				// 	if(!isset($query[0][$key_check])){
				// 		$query[0][$key_check] = $defaults[$key_check];
				// 	}
				// }
				//
				// foreach(array_keys($plano) as $key_check){
				// 	if(!isset($query[0][$key_check])){
				// 		$query[0][$key_check] = $plano[$key_check];
				// 	}
				// }

				ksort($query[0]);

				// $this->dbg($query[0]);

				return $query[0];
			}
		}

		function plano_processar_troca($tentativa,$q="",$date_opt=""){
			if(strlen($q) > 0){
				$q = " and {$q}";
			}
			$query  = parent::database()->query("plano_usuarios", "user_id=" . $this->sessao()->id . $q);

			$saldo_atual = 0;
			$saldos = [];

			foreach($query as $data){
				$diferenca_dias = $this->diff_dates(
					$data["plan_data_inicio"],
					$data["plan_ativo"]
						? $tentativa === -1 ? date("Y-m-d") : $data["plan_data_inicio"]
						: (isset($data["plan_data_fim"])?$data["plan_data_fim"]:date("Y-m-d"))
					);

				$proporcional = $diferenca_dias / (int)$data["plan_prazo"];

				// echo "<pre>{$proporcional}\n";

				$valor_deste = $data["plan_valor_pago"] - ($data["plan_valor"] * $proporcional);

				// echo "<pre>{$data["plan_valor_pago"]} - ({$data["plan_valor"]} * {$proporcional})\n";



				$saldo_atual += $valor_deste;

				if($tentativa == -2){
					$saldos[] = [$valor_deste, $saldo_atual];
				}
			}

			if($tentativa == -2){
				return $saldos;
			}

			$saldo_atual = $saldo_atual < 0 ? 0:$saldo_atual;

			return round(($tentativa === -1 ? $saldo_atual : $tentativa - $saldo_atual)*100)/100;
		}

		function plano_saldo_float(){
			$n = $this->plano_processar_troca(-1);
			// if($n > 10){
			// 	$n += 3.29;
			// }
			return (float)min(1000000,$n);
		}

		function plano_atual($plan=-1){
			if($plan === -1){
				$plan = isset($this->sessao()->plan) ? $this->sessao()->plan:"null";
			}

			if(count($plano=parent::database()->query("planos", "id = {$plan}"))){
				return $plano[0];
			} else {
				return -1;
			}
		}

		function plano_proximo_vencimento(){
			$plano = $this->meu_plano_atual_ativo();
			return $this->sum_days($plano["plan_data_inicio"], (int)$plano["plan_prazo"]*($this->plano_saldo_float()/$plano["plan_valor"]));
		}

		function plano_dias_restantes(){
			$data1 = date("Y-m-d");
			// $this->dbg($this->plano_proximo_vencimento());
			$data2 = $this->plano_proximo_vencimento();

			$diff = $this->diff_dates($data1, $data2);

			return strtotime($data2) > strtotime($data1) ? $diff : 0;
		}

		function plano_ja_esta_vencido(){
			$plano = $this->meu_plano_atual_ativo();
			return !$plano["plan_ativo"] || (int)$this->plano_dias_restantes() < 1;
		}

		function renovar_plano(){
			$plano_atual = $this->meu_plano_atual_ativo();

		if(!$this->plano_ja_esta_vencido()/* || true*/){
				if($this->plano_ja_esta_vencido()){
					$plano_atual["plan_valor_pago"] = (float)($plano_atual["plan_valor"] - $this->plano_saldo_float());

					$plano_atual["plan_ativo"] = true;

					$plano_atual["plan_acao"] = "Ativa&ccedil;&atilde;o do plano ";

					if(count($query=parent::database()->query("plano_usuarios", "@ID = {$plano_atual["@ID"]}")) > 0){
						parent::database()->setWhere("plano_usuarios", "@ID = {$plano_atual["@ID"]}", $plano_atual);
					} else {
						parent::database()->push("plano_usuarios", array($plano_atual));
					}
				}

				return "paid";
			}

			if($this->plano_ja_esta_vencido() && isset($_POST["c"]) && isset($this->sessao()->cards) && isset($this->sessao()->cards[$_POST["c"]])){

				# Dados Pessoais e Metodo de Pagamento

				$this->pagarme()->passive = true;

				$this->pagarme()->set->client
					->name($this->sessao()->name)
					->document($this->sessao()->cards[$_POST["c"]]["c_cpf"])
					->phone(isset($this->sessao()->phone)?("+55" . preg_replace("/[^0-9]/","", $this->sessao()->phone)):"+5511111111111")
					->email($this->sessao()->email)
					->street($this->sessao()->cards[$_POST["c"]]["c_rua"])
					->street_number($this->sessao()->cards[$_POST["c"]]["c_numero"])
					->neighborhood($this->sessao()->cards[$_POST["c"]]["c_bairro"])
					->city($this->sessao()->cards[$_POST["c"]]["c_cidade"])
					->state($this->sessao()->cards[$_POST["c"]]["c_estado"])
					->zipcode(preg_replace("/[^0-9]/","",$this->sessao()->cards[$_POST["c"]]["c_cep"]))
					->parent->method(1);


				# Dados do Cartao para Pagamento

				$this->pagarme()->set->card
					->name($this->sessao()->cards[$_POST["c"]]["c_name"])
					->number(preg_replace("/[^0-9]/","",$this->sessao()->cards[$_POST["c"]]["c_number"]))
					->cvv(preg_replace("/[^0-9]/","",$this->sessao()->cards[$_POST["c"]]["c_cvv"]))
					->expires(preg_replace("/[^0-9]/","",$this->sessao()->cards[$_POST["c"]]["c_expires"]));

				# Produtos para Pagamento

				$this->pagarme()->set->product
					->add(array(
						"name" => "Ativacao {$plano_atual["titulo"]}",
						// "price" => ($plano_atual["plan_valor"] - $this->plano_saldo_float()  -86),
						"price" => 1.01,
						"quantity" => 1,
						"virtual" => true
					));

				if(($the=$this->pagarme()->pay()) !== false && $the["success"] === true){
					$plano_atual["plan_valor_pago"] = (float)($plano_atual["plan_valor"] - $this->plano_saldo_float());

					$plano_atual["plan_ativo"] = true;

					$plano_atual["plan_acao"] = "Ativa&ccedil;&atilde;o do plano ";

					if(count($query=parent::database()->query("plano_usuarios", "@ID = {$plano_atual["@ID"]}")) > 0){
						parent::database()->setWhere("plano_usuarios", "@ID = {$plano_atual["@ID"]}", $plano_atual);
					} else {
						parent::database()->push("plano_usuarios", array($plano_atual));
					}

					return "paid";
				} else {
					return "error";
				}
			}

		return "default";
		}
	}
