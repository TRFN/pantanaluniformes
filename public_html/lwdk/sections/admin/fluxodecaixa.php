<?php
	trait fluxodecaixa {
		function page_fc_ajax(){
			$var = [];
		}

		function page_ajax_status_vendas(){
			$data = ($this->get_compras_atualizadas("cliente > -1"));
			$retorno = [];

			foreach($data as $transacao){
				$status = $transacao["pagseguro"] !== false ? $transacao["pagseguro"]["status"]:"Não Concluído";
				switch($status){
					case "Pago":
					case "Disponível":
						$status = "Pago";
						$corstatus = "#247844";
					break;

					case "Aguardando pagamento":
					case "Em análise":
					case "Em disputa":
						$corstatus = "#244478";
						$status = "Em análise";
					break;


					case "Não Concluído":
						$corstatus = "#244478";
					break;

					case "Cancelado":
					case "Cancelada":
						$corstatus = "#323232";
					break;

					default:
						$corstatus = "#df6500";
					break;
				}

				$retorno[$transacao["id"]] = [$status, $corstatus];
			}

			$this->json($retorno);
		}

		function page_vendas(UITemplate $content){
			$vars = ["filtro_aplicado" => "", "texto-exportar-excel" => "Exportar Excel dos Ultimos 30 dias"];

			$data = ($this->get_compras_atualizadas("cliente > -1", "<i class='fa-1x fas fa-sync fa-spin'></i>&nbsp;&nbsp;Aguarde..."));

			$filter = parent::control("util/dates");

			$hoje = date("Y-m-d");

			$vars["hoje"] = $hoje;

			$filter->set($hoje);

			$filter->sub("1 Month");

			$vars["30dias"] = $filter->get("Y-m-d");

			//
			// $this->dbg($filter->filter($hoje, "data", $data));
			//
			// $this->dbg($filter->get("d/m/Y"));

			if(isset($_REQUEST["filtro"])){
				$termo_filtro = explode("|", $_REQUEST["filtro"]);

				$textointervalo = [
					"Days" => "dias",
					"Months" => "meses",
					"Years" => "anos",
					"Day" => "dia",
					"Month" => "mês",
					"Year" => "ano"
				];

				switch ($termo_filtro[0]) {
					case 'retroativo':
						$filter->set($hoje);

						$filter->sub("{$termo_filtro[1]} {$termo_filtro[2]}");

						$data = ($filter->filter($hoje, "data", $data));

						$datatexto2 = date("d/m/Y");

						$datatexto1 = $filter->get("d/m/Y");

						if((int)$termo_filtro[1] > 1){
							$vars["filtro_aplicado"] = "<b>Intervalo:&nbsp;</b> de {$datatexto1} até {$datatexto2} (últimos {$termo_filtro[1]} {$textointervalo[$termo_filtro[2]]}). <a href='/admin/vendas/' class='btn btn-sm btn-outline-primary ml-4'><i class='  la-1x  la la-ban'></i>&nbsp;Remover filtro</a>";
						} elseif((int)$termo_filtro[1] > 0){
							$vars["filtro_aplicado"] = "<b>Intervalo:&nbsp;</b> de {$datatexto1} até {$datatexto2} (último {$textointervalo[$termo_filtro[2]]}). <a href='/admin/vendas/' class='btn btn-sm btn-outline-primary ml-4'><i class='  la-1x  la la-ban'></i>&nbsp;Remover filtro</a>";
						} else {
							$vars["filtro_aplicado"] = "<b>Intervalo:&nbsp;</b> Vendas de hoje ({$datatexto2}). <a href='/admin/vendas/' class='btn btn-sm btn-outline-primary ml-4'><i class='  la-1x  la la-ban'></i>&nbsp;Remover filtro</a>";
						}
						$vars["texto-exportar-excel"] = "Exportar Excel baseado no intervalo atual";
					break;

					case 'intervalo':
						$filter->set($termo_filtro[1]);

						$data = ($filter->filter($termo_filtro[2], "data", $data));

						$datatexto2 = date("d/m/Y", strtotime($termo_filtro[2]));

						$datatexto1 = date("d/m/Y", strtotime($termo_filtro[1]));

						$vars["hoje"] = $termo_filtro[2];

						$vars["30dias"] = $termo_filtro[1];

						$vars["filtro_aplicado"] = "<b>Intervalo:&nbsp;</b> de {$datatexto1} até {$datatexto2} (intervalo personalizado). <a href='/admin/vendas/' class='btn btn-sm btn-outline-primary ml-4'><i class='  la-1x  la la-ban'></i>&nbsp;Remover filtro</a>";
						$vars["texto-exportar-excel"] = "Exportar Excel baseado no intervalo atual";
					break;

					default:
						// code...
					break;
				}
			}

			$headers = [
				"Data",
				"Cliente",
				"Venda",
				"Status",
				"Valor Total"
			];

			$vars["thead"] = "";
			$vars["tbody"] = "";
			$vars["area"]  = "";
			$vars["TITLE"] = "Vendas da Loja";

			foreach($headers as $header){
				$vars["thead"] .= "<th>{$header}</th>";
			}
			foreach($data as $transacao){
				$status = $transacao["pagseguro"] !== false ? $transacao["pagseguro"]["status"]:"Não Concluído";
				switch($status){
					case "Pago":
					case "Disponível":
						$status = "Pago";
						$corstatus = "#247844";
					break;

					case "Aguardando pagamento":
					case "Em análise":
					case "Em disputa":
						$corstatus = "#244478";
						$status = "Em análise";
					break;


					case "Cancelado":
					case "Cancelada":
					case "Não Concluído":
						$corstatus = "#ab0000";
					break;

					default:
						$corstatus = "#df6500";
					break;
				}
				$data = date("d/m/Y", strtotime($transacao["data"]));
				$endereco = isset($transacao["cliente"]["enderecos"][(int)$transacao["endereco"]])?$transacao["cliente"]["enderecos"][(int)$transacao["endereco"]]:array("");
				$cep = array_shift($endereco);
				$endereco = implode(", ", $endereco);
				if(strlen($endereco) > 0){
					$endereco = "<div class='d-block text-center'><div class='d-inline-block text-center py-2' style='width: 500px;'><div class='p-0 m-0 row text-center'><hr class=col-12 style='color: #000'></hr><b class=col-3>Entrega:</b> <div class='col'>{$endereco} ({$cep})</div></div></div>";
				}
				$vars["tbody"] .= "
					<tr>
						<th><b>{$data}</b></th>
						<td>
							<div><b>{$transacao["cliente"]["name"]}</b></div>
							<div>{$transacao["cliente"]["email"]}</div>
							<div>{$transacao["cliente"]["doc"]}</div>
						</td><td><div class=row><div class=col-md-7 style='border-right: 1px solid'><h4>Produtos</h4><br>
				";
				$vt = 0;
				foreach($transacao["itens"] as $item){
					$item["nome"] = substr($item["nome"],0,30) . "...";
					$vt += (float)str_replace(",", ".", str_replace(".", "", preg_replace("/[^0-9\.\,]/", "", $item["valor"]))) * (int)$item["qtdadq"];
					$vars["tbody"] .= "<div>{$item["qtdadq"]}x {$item["nome"]} ({$item["valor"]} a unit)</div>";
				}
				$vt = "R$ " . number_format($vt, 2, ",", ".");
				$vars["tbody"] .= "</div><div class=col-md-5><h4>Detalhes</h4><br>";
				$transacao["frete"] = "R$ " . number_format($transacao["frete"], 2, ",", ".");
				$vars["tbody"] .= "<div><b>Frete:</b>&nbsp;{$transacao["frete"]}</div>";
				$vars["tbody"] .= "<div><b>Desconto:</b>&nbsp;{$transacao["desc"]}</div>";
				$vars["tbody"] .= "</div><div class=col-md-12>{$endereco}</div></td><td><span data-id='{$transacao["id"]}' class='status-pagseguro badge m-1 p-2' style='text-transform: uppercase; background-color: {$corstatus}; text-shadow: 0 0 1px #222222aa; font-weight: 400; font-size: 14px; color: #fff'>{$status}</span></td><td><h3>{$vt}</h3></td></tr>";

			}

			$layout = "admin/table_fluxodecaixa";

			$content = $this->simple_loader($content, $layout, $vars);

			echo $content->getCode();
		}
	}
?>
