<?php
	trait moduloPagSeguro {
		function get_produtos_pagseguro($frete = 0){
			$carrinho = parent::control("interactive/userdata")->Get("carrinho");

			$retorno = [];

			foreach($carrinho as $id=>$qtd){
				if($qtd > 0){
					$produto = $this->loja_produtos("id = {$id}");
					$retorno[] = array(
						"id" => $produto[0]["id"],
						"nome" => $produto[0]["nome"],
						"preco" => isset($produto[0]["valor-a-vista"]) && !empty($produto[0]["valor-a-vista"]) && $produto[0]["valor-a-vista"] !== "R$ 0,00" ? $produto[0]["valor-a-vista"]:$produto[0]["valor"],
						"qtd" => (string)$qtd,
						"frete" => 0
					);
				}
			}

			return $retorno;
		}

		function get_transaction_now_id(){
			return date("dmY") . preg_replace("/[A-z]/","",md5(serialize($this->sessao()) . serialize($this->get_produtos_pagseguro())));
		}

		function status_pagseguro($ref){
			$status = $this->PagSeguro->sys->getStatusByReference("{$ref}");
			$formasdepgto = array();
			$formasdepgto[1] = "Cartão de Crédito";
			$formasdepgto[2] = "Boleto Bancário";
			$formasdepgto[3] = "Outros Meios";
			$formasdepgto[4] = "Saldo de Conta PagSeguro";
			$formasdepgto[5] = "Outros Meios";
			$formasdepgto[7] = "Outros Meios";
			if($status==null)return false;
			try {
				$status = [
					"date" => strtotime($status->date),
					"status" => $this->PagSeguro->sys->getStatusText((int)$status->status),
					"codstatus" => (int)$status->status,
					"payment" => $formasdepgto[(int)$status->paymentMethod->type],
					"notification" => $status->code,
					"xml" =>  $status
				];
			} catch(Exception $e) {
				return false;
			}

			return $status;
		}

		function get_compras_atualizadas($query,$status = true){
			$query = $this->database()->query("transacoes", "{$query}");
			if(count($query)){
				foreach(array_keys($query) as $id){
					$pagseguro = $status === true ? $this->status_pagseguro($query[$id]["id"]) : ["status" => $status];
					$query[$id]["pagseguro"] = $pagseguro;

					foreach($query[$id]["itens"] as $k => $v){
						$p = $this->loja_produtos("id={$v[0]}");
						$query[$id]["itens"][$k] = $p[0];
						$query[$id]["itens"][$k]["qtdadq"] = $v[2];
						$query[$id]["itens"][$k]["valor"] = $v[1];
					}

					$c = $this->database()->query("contas-loja", "id={$query[$id]["cliente"]}");
					$query[$id]["cliente"] = $c[0];
				}
			}

			return $query;
		}
	}
?>
