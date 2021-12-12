<?php
function ctrl_connect_pagseguro($args){
    $instance = new class extends APPControls {
        function __construct(){
            $this->sys = $this->loadPlugin("PagSeguro@PagSeguro");
        }

        function calcularFrete(
            $cep_origem,  /* cep de origem, apenas numeros */
            $cep_destino, /* cep de destino, apenas numeros */
            $valor_declarado='0', /* indicar 0 caso nao queira o valor declarado */
            $peso='1',        /* valor dado em Kg incluindo a embalagem. 0.1, 0.3, 1, 2 ,3 , 4 */
            $altura='15',      /* altura do produto em cm incluindo a embalagem */
            $largura='15',     /* altura do produto em cm incluindo a embalagem */
            $comprimento='15', /* comprimento do produto incluindo embalagem em cm */
            $cod_servico='pac' /* codigo do servico desejado */
            ){

            $cod_servico = strtoupper( $cod_servico );
            if( $cod_servico == 'SEDEX10' ) $cod_servico = 40215 ;
            if( $cod_servico == 'SEDEXACOBRAR' ) $cod_servico = 40045 ;
            if( $cod_servico == 'SEDEX' ) $cod_servico = 40010 ;
            if( $cod_servico == 'PAC' ) $cod_servico = 41106 ;

            # ###########################################
            # Código dos Principais Serviços dos Correios
            # 41106 PAC sem contrato
            # 40010 SEDEX sem contrato
            # 40045 SEDEX a Cobrar, sem contrato
            # 40215 SEDEX 10, sem contrato
            # ###########################################

            $correios = "http://ws.correios.com.br/calculador/CalcPrecoPrazo.aspx?nCdEmpresa=&sDsSenha=&sCepOrigem=".$cep_origem."&sCepDestino=".$cep_destino."&nVlPeso=".$peso."&nCdFormato=1&nVlComprimento=".$comprimento."&nVlAltura=".$altura."&nVlLargura=".$largura."&sCdMaoPropria=n&nVlValorDeclarado=".$valor_declarado."&sCdAvisoRecebimento=n&nCdServico=".$cod_servico."&nVlDiametro=0&StrRetorno=xml";

            // exit($correios);

            $xml = simplexml_load_file($correios);

            $_arr_ = array();
            if($xml->cServico->Erro == '0'):
                $_arr_['codigo'] = $xml -> cServico -> Codigo ;
                $_arr_['valor'] = $xml -> cServico -> Valor ;
                $_arr_['prazo'] = $xml -> cServico -> PrazoEntrega .' Dia(s)' ;
                // return $xml->cServico->Valor;
                return $_arr_ ;
            else:
                return false;
            endif;
        }

		function updNotif(String $id, Array $updt, String $db="nps"){
			$orig = $this->database()->query("{$db}-status", "id={$id}");
			if(count($orig) == 0){
				$orig = $updt;
				$orig["id"] = $id;
				$this->database()->push("{$db}-status", array($orig), "log_remove");
			} else {
				$orig = $orig[0];

				foreach($updt as $k => $v){
					$orig[$k] = $v;
				}

				$this->database()->setWhere("{$db}-status", "id={$id}", $orig);
			}
		}

		function lerNotif($query="cliente_id > 0", $db="nps"){
			$odb = $db;
			$translate_body = [
				"Referencia" => "cliente_id",
				"Extras" => "desconto_ou_adicional",
				"ValorFrete" => "frete",
				"TipoPagamento" => "forma_pagamento",
				"StatusTransacao" => "status",
				"NumItens" => "qtd_produtos",
				"Parcelas" => "parcelas_pgto",
				"CliEndereco" => "cliente_endereco",
				"CliNumero" => "cliente_numero",
				"CliCEP" => "cliente_cep",
				"CliBairro" => "cliente_bairro",
				"CliCidade" => "cliente_cidade",
				"CliEstado" => "cliente_estado"

			];
			$fdb = [];
			$db = $this->database()->query($db, "Referencia > 0");

			foreach($db as $g){
				$newdb = [];
				$qtdcerta = 0;
				foreach($g as $k => $v){
					if(isset($translate_body[$k])){
						$newdb[$translate_body[$k]] = $v;
					}
				}

				$newdb["qtd_produtos"] = isset($newdb["qtd_produtos"])?(int)$newdb["qtd_produtos"]:0;

				$n = $newdb["qtd_produtos"];

				$newdb["produtos"] = [];

				$newdb["valor_total"] = (float)preg_replace("/[,]/", ".", $newdb["desconto_ou_adicional"]) +
				 						(float)preg_replace("/[,]/", ".", $newdb["frete"]);


				while($n-- > 0){
					$_n = $n + 1;
					$newdb["produtos"][] = array(
						"id" => $g["ProdID_{$_n}"],
						"nome" => $g["ProdDescricao_{$_n}"],
						"valor" => $g["ProdValor_{$_n}"],
						"qtd" => $g["ProdQuantidade_{$_n}"]
					);
					$newdb["valor_total"] += (float)preg_replace("/[,]/", ".", $g["ProdValor_{$_n}"]) * (float)$g["ProdQuantidade_{$_n}"];
					$qtdcerta += (int)$g["ProdQuantidade_{$_n}"];
				}

				$newdb["qtd_produtos_total"] = (string)$qtdcerta;

				$newdb["data-hora"] = "{$g["@CREATED"][0][0]}/{$g["@CREATED"][0][1]}/{$g["@CREATED"][0][2]} às {$g["@CREATED"][1][0]}:{$g["@CREATED"][1][1]}:{$g["@CREATED"][1][2]}";


				if(preg_match("/cart(.?)o+\sde+\scr(.?)dito/i", $newdb["forma_pagamento"])){
					$newdb["forma_pagamento"] = array(
						"html" => "Cart&atilde;o de Cr&eacute;dito",
						"text" => "Cartão de Crédito",
						"data" => "cartao_de_credito"
					);
				} elseif(preg_match("/cart(.?)o+\sde+\sd(.?)bito/i", $newdb["forma_pagamento"])){
					$newdb["forma_pagamento"] = array(
						"html" => "Cart&atilde;o de D&eacute;bito",
						"text" => "Cartão de Débito",
						"data" => "cartao_de_debito"
					);
				} else {
					$newdb["forma_pagamento"] = array(
						"html" => htmlentities($newdb["forma_pagamento"]),
						"text" => $newdb["forma_pagamento"],
						"data" => $this->args["ux"]->slug($newdb["forma_pagamento"])
					);
				}

				$tid = sha1(md5(json_encode($newdb["produtos"]).json_encode($newdb["forma_pagamento"]).$newdb["cliente_id"]));

				$newdb["entregue"] = $this->getStatusVar($odb, $tid, "entregue", "false");

				$newdb["nf"] = $this->getStatusVar($odb, $tid, "nf", "false");

				$newdb["enviado"] = $this->getStatusVar($odb, $tid, "enviado", "false");

				$newdb["valor_total"] = number_format($newdb["valor_total"], 2, ",", ".");

				// exit($newdb["entregue"]);

				$fdb[$tid] = $newdb;
			}

			return $this->database()->query($fdb, $query);
		}



		private function getStatusVar($db, $tid, $key, $nullpointer=""){
			$entregue = $this->database()->query("{$db}-status", "id={$tid}", "{$key}");

			if(count($entregue) == 0){
				return $nullpointer;
			} else {
				return $entregue[0];
			}
		}
    };

    $instance->args = $args;

    return $instance;
}
