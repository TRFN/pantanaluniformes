<?php

	trait financeiro {
		private $sizename = 20;

		function page_financeiro_form_add(){
			$insert = $_POST;
			$insert["id"] = $this->database()->newID("findb");
			/* LOG DE ACESSO */
			$namelog = substr($insert["nome"],0,$this->sizename);
			if(strlen($namelog) != strlen($insert["nome"])){ $namelog .= "..."; }
			$this->log->post("Lançamento de entrada financeira:&nbsp;<a target=_blank data-skin='white' data-toggle='m-tooltip' data-placement='top' title='' data-original-title='{$insert["nome"]}' href='/admin/financeiro_editar_{$insert["sect"]}/{$insert["id"]}/' class='d-inline-block py-1 my-1'>{$namelog}</a>");
			$this->json($this->database()->push("findb", [$insert]));
		}

		function page_financeiro_form_edit(){
			$insert = $_POST;
			// $this->dbg($insert);
			// $insert["id"] = $this->database()->newID("findb");
			$namelog = substr($insert["nome"],0,$this->sizename);
			if(strlen($namelog) != strlen($insert["nome"])){ $namelog .= "..."; }
			$this->log->post("Edição de lançamento financeiro:&nbsp;<a target=_blank data-skin='white' data-toggle='m-tooltip' data-placement='top' title='' data-original-title='{$insert["nome"]}' href='/admin/financeiro_editar_{$insert["sect"]}/{$insert["id"]}/' class='d-inline-block py-1 my-1'>{$namelog}</a>");
			$this->json($this->database()->setWhere("findb", "id = {$insert["id"]}", $insert));
		}

		function page_financeiro_form_erase(){
			$insert = $_POST;
			// $this->dbg($insert);
			// $insert["id"] = $this->database()->newID("findb");
			$insert = $this->database()->query("findb", "id = {$insert["id"]}");
			if(count($insert) > 0){
				$insert = $insert[0];
				$namelog = substr($insert["nome"],0,$this->sizename);
				if(strlen($namelog) != strlen($insert["nome"])){ $namelog .= "..."; }
				$this->log->post("O lançamento financeiro foi apagado:&nbsp;<span data-skin='white' data-toggle='m-tooltip' data-placement='top' title='' data-original-title='{$insert["nome"]}' class='d-inline-block py-1 my-1'>{$namelog}</span>");
				$this->json($this->database()->deleteWhere("findb", "id = {$insert["id"]}"));
			} else {
				$this->json(false);
			}
		}

		function page_change_status(){
			if(count($this->database()->query("pgtos", "cliente = {$_POST["data"][0]} and imovel = {$_POST["data"][2]} and data = {$_POST["data"][3]}")) > 0){
				$this->database()->setWhere("pgtos", "cliente = {$_POST["data"][0]} and imovel = {$_POST["data"][2]} and data = {$_POST["data"][3]}", ["status" => ($_POST["data"][4] == 1?"pg":'not')]);
			} else {
				$this->database()->push("pgtos", array(
					array(
						"cliente" => $_POST["data"][0],
						"imovel" => $_POST["data"][2],
						"vendedor" => $_POST["data"][1],
						"data" => $_POST["data"][3],
						"status" => ($_POST["data"][4] == 1?"pg":'not')
					)
				));
			}
			$status = $_POST["data"][4] == 1 ? ["Pago","success"]:["Pendente","danger"];
			$insert = $this->database()->query("findb", "id = {$_POST["data"][0]}");
			if(count($insert) > 0){
				$insert = $insert[0];
				$namelog = substr($insert["nome"],0,$this->sizename);
				if(strlen($namelog) != strlen($insert["nome"])){ $namelog .= "..."; }
				$this->log->post("O lançamento financeiro <a target='_blank' href='/admin/financeiro_editar_{$insert["sect"]}/{$insert["id"]}/' data-skin='white' data-toggle='m-tooltip' data-placement='top' title='' data-original-title='{$insert["nome"]}' class='d-inline-block py-1 my-1'>{$namelog}</a> teve seu <b>Status</b> alterado para <b class='badge badge-{$status[1]}'>{$status[0]}</b>");
			}
		}

		function tratar_dado_lancamento($out){
			unset($out["@CREATED"]);
			unset($out["@CREATED"]);
			unset($out["@ID"]);
			return $out;
		}

		function page_bkpgs(){
			$req = $_REQUEST;
			$this->database()->setWhere("transacoes", "id={$req["id"]}", array("bkpgs" => $req["status"]));
		}

		function page_financeiro_data(){
			$valor = 0;
			$valores = [];

			$entrada   = $this->database()->query("findb", "sect = entrada");
			$vendas = ($this->get_compras_atualizadas("cliente > -1", "<i class='fa-1x fas fa-sync fa-spin'></i>&nbsp;&nbsp;Aguarde..."));
			// $this->dbg($vendas);
			foreach($vendas as $index => $transacao){
				$index += 1;
				$status = $transacao["pagseguro"] !== false ? $transacao["pagseguro"]["status"]:"Não Concluído";

				$dado = [
					date("d/m/Y", strtotime($transacao["data"])),
					$transacao["cliente"]["name"],
					$transacao["cliente"]["email"],
					$transacao["cliente"]["doc"]
				];

				$vt = 0;
				$itens = "";
				foreach($transacao["itens"] as $item){
					$item["nome"] = substr($item["nome"],0,30) . "...";
					$vt += (float)str_replace(",", ".", str_replace(".", "", preg_replace("/[^0-9\.\,]/", "", $item["valor"]))) * (int)$item["qtdadq"];
					$itens .= "<div>{$item["qtdadq"]}x {$item["nome"]} ({$item["valor"]} a unit)</div>";
				}

				$dado[] = $itens;

				if($status == "Disponível" || $status == "Pago"){
					$tg += $vt;
				}

				$vt = "R$ " . number_format($vt, 2, ",", ".");

				$dado[] = "R$ " . number_format($transacao["frete"], 2, ",", ".");

				$dado[] = $transacao["desc"];

				$dado[] = $status;

				$dado[] = $vt;

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

				// ["Data", "Cliente", "Email", "Documento", "Produtos", "Frete", "Desconto", "Status", "Total"]

				$entrada[] = array(
					"sect" => "entrada",
					"nome" => "
						<div style='width: 600px; font-size:11px; transform: scale(1.2)' class='row p-0 m-0 ml-4 my-2'>
							<div class=col-md-1>&nbsp;</div>
							<div class='col-md-4 text-left text-start'>
								<div><b>Cliente:</b>&nbsp;{$dado[1]}</div>
								<div><b>Email:</b>&nbsp;{$dado[2]}</div>
								<div><b>Documento:</b>&nbsp;{$dado[3]}</div>
							</div>
							<div class='col-md-7 text-left text-start' style='border-left: 1px solid;'><b>Produtos:</b>&nbsp;{$dado[4]}</div>
						</div>
					",
					"valor" => "$vt",
					"tipo" => "E-Commerce",
					"data" => date("Y-m-d", strtotime($transacao["data"])),
					"pago" => "<div style='text-align: center;'><span data-id='{$transacao["id"]}' class='status-pagseguro badge m-1 p-2' style='text-transform: uppercase; border-radius: 3px;background-color: {$corstatus}; text-shadow: 0 0 1px #222222aa; font-weight: 400; font-size: 12px; color: #fff; font-weight: bold'>{$status}</span></div>",
					"nomeText" => "",
					"kdta" => $transacao["data"],
					"vars" => ["date" => strtotime($transacao["data"]), "now" => strtotime(date("Y-m-d")), "string_date" => date("Y-m-d", strtotime($transacao["data"]))],
					"fixo" => "fn",
					"statusVenda" => isset($transacao["bkpgs"])?$transacao["bkpgs"]:"not"
				);
			}
			// $this->json($entrada);
			$saida     = $this->database()->query("findb", "sect = saida");

			foreach($entrada as $dt){
				if(!isset($dt["apagada"]) || strtotime(date("Y-m-d")) > strtotime(date($dt["apagada"]))){
					// $valor += g_money($dt["valor"]);

					$d = [$dt["data"], explode("-", $dt["data"])];

					if(!isset($valores[$d[1][0]])){
						$valores[$d[1][0]] = [
							"valor" => 0,
							"itens" => []
						];
					}

					if(!isset($valores[$d[1][0]]["itens"][$d[1][1]])){
						$valores[$d[1][0]]["itens"][$d[1][1]] = [
							"valor" => 0,
							"itens" => []
						];
					}

					if(!isset($valores[$d[1][0]]["itens"][$d[1][1]]["itens"][$d[1][2]])){
						$valores[$d[1][0]]["itens"][$d[1][1]]["itens"][$d[1][2]] = [
							"valor" => 0,
							"itens" => []
						];
					}

					$valores[$d[1][0]]["itens"][$d[1][1]]["itens"][$d[1][2]]["valor"] += g_money($dt["valor"]);
					$valores[$d[1][0]]["itens"][$d[1][1]]["valor"] += g_money($dt["valor"]);
					$valores[$d[1][0]]["valor"] += g_money($dt["valor"]);

					$out = $dt;
					$out["data"] = $d[0];

					if(!isset($out["nomeText"])){

						$query = $this->database()->query("pgtos", "cliente = {$out["id"]} and imovel = -1 and data = {$out['data']}");
						$pg = isset($query[0]) && $query[0]["status"] == "pg";


						$out["pago"] = $pg ? "pg":"not";

					}

					$out["vars"] = ["date"=>strtotime($d[0]),"now"=>strtotime(date("Y-m-d"))];

					$valores
						[$d[1][0]]["itens"]
						[$d[1][1]]["itens"]
						[$d[1][2]]["itens"][] = $this->tratar_dado_lancamento($out);

					// $valores[$d[1][0]]["itens"][$d[1][1]]["itens"][$d[1][2]]["itens"][] = ($dt);

					if($dt["tipo"] == "Aluguel" && 1){
						$compare_date = abs(floor((($dt_apply=strtotime($dt["data-aluguel-ate"]))-(strtotime($dt["data"]))) / 60 / 60 / 24 / 30));

						$mdt = 0;

						// $this->dbg($compare_date);

						// $dt_apply -= 60 * 60 * 24 * 30;


						$dt_apply = strtotime($dt2=date("Y-m-d",strtotime("-${mdt} months",strtotime($dt["data-aluguel-ate"]))));

						// $this->dbg($dt_apply);

						while($dt_apply >= strtotime($dt["data"])){

							$dt_apply = strtotime($dt2=date("Y-m-d",strtotime("-${mdt} months",strtotime($dt["data-aluguel-ate"]))));

							$dt["vars"] = ["date"=>$dt_apply,"string_date"=>date("Y-m-d",$dt_apply) ,"now"=>strtotime(date("Y-m-d"))];

							$d = date("Y-m-d", $dt_apply);

							$d = [$d, explode("-", $d)];

							if(!isset($valores[$d[1][0]])){
								$valores[$d[1][0]] = [
									"valor" => 0,
									"itens" => []
								];
							}

							if(!isset($valores[$d[1][0]]["itens"][$d[1][1]])){
								$valores[$d[1][0]]["itens"][$d[1][1]] = [
									"valor" => 0,
									"itens" => []
								];
							}

							if(!isset($valores[$d[1][0]]["itens"][$d[1][1]]["itens"][$d[1][2]])){
								$valores[$d[1][0]]["itens"][$d[1][1]]["itens"][$d[1][2]] = [
									"valor" => 0,
									"itens" => []
								];
							}

							$valores
								[$d[1][0]]["itens"]
								[$d[1][1]]["itens"]
								[$d[1][2]]["valor"] += g_money($dt["valor"]);

							$valores
								[$d[1][0]]["itens"]
								[$d[1][1]]["valor"] += g_money($dt["valor"]);

							$valores
								[$d[1][0]]
								["valor"] += g_money($dt["valor"])*$compare_date;

							$valores
								[$d[1][0]]["itens"]
								[$d[1][1]]["itens"]
								[$d[1][2]]["itens"][] = $this->tratar_dado_lancamento($dt);

							$mdt++;
						}

						while($compare_date--){
							$valor += g_money($dt["valor"]);
						}
					}

					elseif($dt["fixo"] != "fn" && 1){
						// $ano = date("Y", strtotime($dt["data"]));
						// $mes = date("m", strtotime($dt["data"]));

						$compare_date = abs(floor((($dt_apply=strtotime($dt2=date("Y-m-", strtotime($dt["data"])) . $dt["data-receb-dia"]))-($now=strtotime(($dt["fixo"] == "fs"?$dt["data-receb-ate"]:date("Y-m-d"))))) / 60 / 60 / 24 / 30));

						// $this->dbg([$compare_date,$dt_apply,$dt2,$dt["data"],$now]);
						$mdt = 1;

						if($dt_apply > strtotime($dt["data"])){
							// $dt_apply -= 60 * 60 * 24 * 30;
							while(($dt_apply >= strtotime($dt["data-receb-ate"]) || ($dt["fixo"] == "fsv" && $dt_apply >= strtotime(date("Y-m-d")))) and $compare_date > 0){
								// echo "<pre>";
								// var_dump($dt2);

								if(empty($dt["data-receb-dia"])){
									break;
								}

								$dt_apply = strtotime($dt2=date("Y-m-",strtotime("-${mdt} months",strtotime($dt["data"]))) . $dt["data-receb-dia"]);

								$d = date("Y-m-d", $dt_apply);

								$d = [$d, explode("-", $d)];

								if(!isset($valores[$d[1][0]])){
									$valores[$d[1][0]] = [
										"valor" => 0,
										"itens" => []
									];
								}

								if(!isset($valores[$d[1][0]]["itens"][$d[1][1]])){
									$valores[$d[1][0]]["itens"][$d[1][1]] = [
										"valor" => 0,
										"itens" => []
									];
								}

								if(!isset($valores[$d[1][0]]["itens"][$d[1][1]]["itens"][$d[1][2]])){
									$valores[$d[1][0]]["itens"][$d[1][1]]["itens"][$d[1][2]] = [
										"valor" => 0,
										"itens" => []
									];
								}

								$valores
									[$d[1][0]]["itens"]
									[$d[1][1]]["itens"]
									[$d[1][2]]["valor"] += g_money($dt["valor"]);

								$valores
									[$d[1][0]]["itens"]
									[$d[1][1]]["valor"] += g_money($dt["valor"]);

									$valores
										[$d[1][0]]
										["valor"] += g_money($dt["valor"]);
								// echo "<pre>{$valores[$d[1][0]]["valor"]}";
								$valor += g_money($dt["valor"]);

								$dt["vars"] = ["date"=>$dt_apply,"string_date"=>date("Y-m-d",$dt_apply) ,"now"=>strtotime(date("Y-m-d"))];



								$query = $this->database()->query("pgtos", "cliente = {$dt["id"]} and imovel = -1 and data = {$dt_apply}");
								$pg = isset($query[0]) && $query[0]["status"] == "pg";

								$out["pago"] = $pg ? "pg":"not";

								$valores
									[$d[1][0]]["itens"]
									[$d[1][1]]["itens"]
									[$d[1][2]]["itens"][] = $this->tratar_dado_lancamento($dt);

								$mdt++;
							}
						} else {
							// $dt_apply += 60 * 60 * 24 * 30;

							while(($dt_apply <= strtotime($dt["data-receb-ate"]) || ($dt["fixo"] == "fsv" && $dt_apply <= strtotime(date("Y-m-d")))) and $compare_date > 0){

								if(empty($dt["data-receb-dia"])){
									break;
								}

								$dt_apply = strtotime($dt2=date("Y-m-",strtotime("+${mdt} months",strtotime($dt["data"]))) . $dt["data-receb-dia"]);

								$d = date("Y-m-", $dt_apply) . $dt["data-receb-dia"];

								$d = [$d, explode("-", $d)];

								// echo "<pre>";
								//
								// if($d[0] == "1969-12-"): $this->dbg([$dt2,$dt["data"]]); endif;

								if(!isset($valores[$d[1][0]])){
									$valores[$d[1][0]] = [
										"valor" => 0,
										"itens" => []
									];
								}

								if(!isset($valores[$d[1][0]]["itens"][$d[1][1]])){
									$valores[$d[1][0]]["itens"][$d[1][1]] = [
										"valor" => 0,
										"itens" => []
									];
								}

								if(!isset($valores[$d[1][0]]["itens"][$d[1][1]]["itens"][$d[1][2]])){
									$valores[$d[1][0]]["itens"][$d[1][1]]["itens"][$d[1][2]] = [
										"valor" => 0,
										"itens" => []
									];
								}

								$valores
									[$d[1][0]]["itens"]
									[$d[1][1]]["itens"]
									[$d[1][2]]["valor"] += g_money($dt["valor"]);

								$valores
									[$d[1][0]]["itens"]
									[$d[1][1]]["valor"] += g_money($dt["valor"]);

								$valores
									[$d[1][0]]
									["valor"] += g_money($dt["valor"]);

								$valor += g_money($dt["valor"]);

								// echo "<pre>{$valores[$d[1][0]]["valor"]}";

								$out = $dt;

								$out["vars"] = ["date"=>$dt_apply,"string_date"=>date("Y-m-d",$dt_apply) ,"now"=>strtotime(date("Y-m-d"))];

								$out["data"] = $d[0];



								$query = $this->database()->query("pgtos", "cliente = {$out["id"]} and imovel = -1 and data = {$out['data']}");
								$pg = isset($query[0]) && $query[0]["status"] == "pg";

								$out["pago"] = $pg ? "pg":"not";

								$valores
									[$d[1][0]]["itens"]
									[$d[1][1]]["itens"]
									[$d[1][2]]["itens"][] = $this->tratar_dado_lancamento($out);
								// echo "<pre>";
								// var_dump($d[1]);
								$mdt++;
							}
							// exit;
						}

						while($compare_date--){
							$valor += g_money($dt["valor"]);
						}
					} else {
						$valor += g_money($dt["valor"]);
					}
				}
			}


			foreach($saida as $dt){

				$d = [$dt["data"], explode("-", $dt["data"])];

				if(!isset($valores[$d[1][0]])){
					$valores[$d[1][0]] = [
						"valor" => 0,
						"itens" => []
					];
				}

				if(!isset($valores[$d[1][0]]["itens"][$d[1][1]])){
					$valores[$d[1][0]]["itens"][$d[1][1]] = [
						"valor" => 0,
						"itens" => []
					];
				}

				if(!isset($valores[$d[1][0]]["itens"][$d[1][1]]["itens"][$d[1][2]])){
					$valores[$d[1][0]]["itens"][$d[1][1]]["itens"][$d[1][2]] = [
						"valor" => 0,
						"itens" => []
					];
				}

				$valores[$d[1][0]]["itens"][$d[1][1]]["itens"][$d[1][2]]["valor"] -= g_money($dt["valor"]);
				$valores[$d[1][0]]["itens"][$d[1][1]]["valor"] -= g_money($dt["valor"]);
				$valores[$d[1][0]]["valor"] -= g_money($dt["valor"]);

				$dt["vars"] = ["date"=>strtotime($dt["data"]),"now"=>strtotime(date("Y-m-d"))];

				if($dt["fator"] == "n"){
					$query = $this->database()->query("pgtos", "cliente = {$dt["id"]} and imovel = -1 and data = {$dt['data']}");
					$pg = isset($query[0]) && $query[0]["status"] == "pg";

					$dt["pago"] = $pg ? "pg":"not";

					$valores[$d[1][0]]["itens"][$d[1][1]]["itens"][$d[1][2]]["itens"][] = ($dt);
				}

				if(isset($dt["fator"]) && $dt["fator"] != "n" && 1){
					// $compare_date = floor((strtotime(date("Y-m-d"))-(($dt_apply=strtotime($dt["data"])))) / 60 / 60 / 24 / (int)$dt["fator"]);

					$dt_apply = strtotime($dt["data"]);

					// $this->dbg([$compare_date,$dt_apply]);

					while($dt_apply <= strtotime(date("Y-m-d"))){
						$d = date("Y-m-d", $dt_apply);

						$d = [$d, explode("-", $d)];

						if(!isset($valores[$d[1][0]])){
							$valores[$d[1][0]] = [
								"valor" => 0,
								"itens" => []
							];
						}

						if(!isset($valores[$d[1][0]]["itens"][$d[1][1]])){
							$valores[$d[1][0]]["itens"][$d[1][1]] = [
								"valor" => 0,
								"itens" => []
							];
						}

						if(!isset($valores[$d[1][0]]["itens"][$d[1][1]]["itens"][$d[1][2]])){
							$valores[$d[1][0]]["itens"][$d[1][1]]["itens"][$d[1][2]] = [
								"valor" => 0,
								"itens" => []
							];
						}

						$valores
							[$d[1][0]]["itens"]
							[$d[1][1]]["itens"]
							[$d[1][2]]["valor"] -= g_money($dt["valor"]);

						$valores
							[$d[1][0]]["itens"]
							[$d[1][1]]["valor"] -= g_money($dt["valor"]);

						$valores
							[$d[1][0]]
							["valor"] -= g_money($dt["valor"]);

						$valor -= g_money($dt["valor"]);

						$out = $dt;

						$out["data"] = $d[0];

						$query = $this->database()->query("pgtos", "cliente = {$out["id"]} and imovel = -1 and data = {$out['data']}");
						$pg = isset($query[0]) && $query[0]["status"] == "pg";

						$out["pago"] = $pg ? "pg":"not";

						$out["vars"] = ["date"=>$dt_apply,"string_date"=>date("Y-m-d",$dt_apply) ,"now"=>strtotime(date("Y-m-d"))];

						$valores
							[$d[1][0]]["itens"]
							[$d[1][1]]["itens"]
							[$d[1][2]]["itens"][] = $this->tratar_dado_lancamento($out);

						$dt_apply = strtotime($dt2=date("Y-m-d",strtotime($dt["fator"],$dt_apply)));
					}

					// while($compare_date--){
					// 	$valor -= g_money($dt["valor"]);
					// }
				} else {
					$valor -= g_money($dt["valor"]);
				}
			}
			// $this->json($pagamentos);
			$this->json(["valores" => $valores, "checksum"=>sha1(json_encode([$valor,$valores]))]);
		}

		function fin_get_produtos(){
			$produtos_lanc_fin = "";

			foreach(parent::database()->query("produtos","ativo = true") as $prod){
				$jprod = json_encode($prod);
				$produtos_lanc_fin .=  "<option value='{$jprod}'>{$prod["codigo"]}&nbsp;&nbsp;{$prod["nome"]}</option>";
			}

			foreach(parent::database()->query("produtos","ativo = false") as $prod){
				$jprod = json_encode($prod);
				$produtos_lanc_fin .=  "<option value='{$jprod}'>(*)&nbsp;{$prod["codigo"]}&nbsp;&nbsp;{$prod["nome"]}</option>";
			}

			return $produtos_lanc_fin;
		}

		function page_financeiro_adicionar_entrada(UITemplate $content){
            $content->minify = true;

			$vars = ["produtos-lanc-fin" => $this->fin_get_produtos(),"act"=>"add"];

			$vars["data"] = date("Y-m-d");

            $content = $this->simple_loader($content, "admin/financeiro/adicionar-entrada", $vars);

            echo $content->getCode();
        }

		function page_financeiro_editar_entrada(UITemplate $content){
            $content->minify = true;

			$vars = ["produtos-lanc-fin" => $this->fin_get_produtos(),"act"=>"edit"];

			// $vars["data"] = date("Y-m-d");

			$vars["id"] = $this->url(1);

			$busca = $this->database()->query("findb", "sect = entrada and id = {$vars["id"]}");

			if(count($busca) > 0){
				$vars = array_merge($busca[0], $vars);
			}

            $content = $this->simple_loader($content, "admin/financeiro/editar-entrada", $vars);

            echo $content->getCode();
        }

		function page_financeiro_adicionar_saida(UITemplate $content){
            $content->minify = true;

			$vars = ["act"=>"add"];

			$vars["data"] = date("Y-m-d");

            $content = $this->simple_loader($content, "admin/financeiro/adicionar-saida", $vars);

            echo $content->getCode();
        }

		function page_financeiro_editar_saida(UITemplate $content){
			$content->minify = true;

			$vars = ["act"=>"edit"];

			$vars["id"] = $this->url(1);

			$busca = $this->database()->query("findb", "sect = saida and id = {$vars["id"]}");

			if(count($busca) > 0){
				$vars = array_merge($busca[0], $vars);
			}

			$content = $this->simple_loader($content, "admin/financeiro/editar-saida", $vars);

			echo $content->getCode();
		}

		function page_financeiro_home(UITemplate $content){
            $content->minify = true;

			$vars = ["nav-meses"=>"", "content-meses"=>"", "ano" => date("Y"), "mes" => date("m"), "dia" => date("d")];

			$filter = parent::control("util/dates");

			$hoje = date("Y-m-d");

			$filter->set($hoje);

			$filter->sub("1 Month");

			$vars["data1mesantes"] = $filter->get("Y-m-d");

			$meses = [
				["jan","Janeiro",0],
				["fev","Fevereiro",0],
				["mar","Março",0],
				["abr","Abril",0],
				["mai","Maio",0],
				["jun","Junho",0],
				["jul","Julho",0],
				["ago","Agosto",0],
				["set","Setembro",0],
				["out","Outubro",0],
				["nov","Novembro",0],
				["dez","Dezembro",0]
			];

			foreach($meses as $imes=>$mes){
				$imes = $imes + 1;

				$act = (($mes[2] == 1 || (int)$vars["mes"] == (int)$imes) ? [" active", ' aria-selected="true"'," show active"]:["","",""]);

				$vars["nav-meses"] .= '
				  <a class="nav-item nav-link'.$act[0].'" id="link-'.$mes[0].'" data-toggle="tab" href="#'.$mes[0].'" role="tab" aria-controls="'.$mes[0].'" '.$act[1].'>'.$mes[1].'</a>
				';

				$vars["content-meses"] .= '<div class="tab-pane mes-'.$imes.' fade'.$act[2].'" id="'.$mes[0].'" role="tabpanel" aria-labelledby="'.$mes[0].'">
					<h2>Mês de '.$mes[1].' de <span class="ano">'.$vars["ano"].'</span></h2>
					<br><br>
					<div class="table-responsive">
						<h4>Transações deste mês</h4>
							<table id="table-'.$mes[0].'" class="table table-bordered table-hover table-striped">
					        <thead>
					            <tr>
					                <th class="text-center" style="width: 30px!important;">Dia</th>
					                <th class="text-center" style="width: 220px!important;">Titulo</th>
					                <th class="text-center" style="width: 30px!important;">Valor</th>
					                <th class="text-center" style="width: 30px!important;">Transação</th>
					                <th class="text-center" style="width: 30px!important;">Status</th>
					                <th class="text-center" style="width: 120px!important;">Opções</th>
					            </tr>
					        </thead>
		        			<tbody>
		        			</tbody>
							<tfooter><tr><td colspan=6 class="text-right"><button class="m-0 mt-2 btn btn-dark m-btn" data-toggle="collapse" data-target="#indices-' . $imes . '">Exibir/Esconder Gráficos</button></td></tr><tr><td class="bg-secondary py-4 collapse show" id="indices-' . $imes . '" colspan=6>
								<div class="indices row p-0 m-0">
									<div class="col-lg col-md-4 col-12">
										<h4>Receita do Mês</h4>
										<h6 class="receita-'.$imes.'">&nbsp;R$ 0,00&nbsp;</h6>
										<div style="font-family: Arial Black;" class="grafico1-'.$imes.'"></div>
									</div>

									<div class="col-lg col-md-4 col-12">
										<h4>Despesas do Mês</h4>
										<h6 class="despesas-'.$imes.'">&nbsp;R$ 0,00&nbsp;</h6>
										<div style="font-family: Arial Black;" class="grafico2-'.$imes.'"></div>
									</div>
								</div>
							</td></tr></tfooter>
	    				</table>
					</div>
					<br><br>
				</div>';
			}

			$vars["data"] = date("Y-m-d");

            $content = $this->simple_loader($content, "admin/financeiro/fluxo", $vars);

            echo $content->getCode();
        }

		function page_financeiro_gerar_relatorio(){
			$req = $_REQUEST;

			$filter = parent::control("util/dates");

			$excel = parent::control("util/excel");
			$excel->SetDocumentTitle("relatorio-" . substr(sha1(date("dmYHis")),0,10));

			$styleTitle = array(
				'font'  => array(
					'bold'  => true,
					'color' => array('rgb' => 'FFFFFF'),
					'size'  => 14
				),

				'fill' => array(
					'type' => 'solid',
					'color' => array('rgb' => "42AB66")
				)
			);

			if((int)$req["loja-virtual"] == 1){
				$data = [
					["Data", "Cliente", "Email", "Documento", "Produtos", "Forma de Pagamento", "Frete", "Desconto", "Status", "Total"]
				];

				$excel->applyStyle("A1:J1", $styleTitle);

				$tg = 0;

				$vendas = array_reverse($this->get_compras_atualizadas("cliente > -1"));

				$filter->set($req["data-inicio"]);

				$vendas = ($filter->filter($req["data-final"], "data", $vendas));

				foreach($vendas as $index => $transacao){
					$index += 1;
					$excel->Instance()->getActiveSheet()->getRowDimension('1')->setRowHeight(-1);
					$status = $transacao["pagseguro"] !== false ? $transacao["pagseguro"]["status"]:"Não Concluído";

					$dado = [
						date("d/m/Y", strtotime($transacao["data"])),
						$transacao["cliente"]["name"],
						$transacao["cliente"]["email"],
						$transacao["cliente"]["doc"]
					];

					$vt = 0;
					$itens = "";
					foreach($transacao["itens"] as $item){
						$item["nome"] = substr($item["nome"],0,30) . "...";
						$vt += (float)str_replace(",", ".", str_replace(".", "", preg_replace("/[^0-9\.\,]/", "", $item["valor"]))) * (int)$item["qtdadq"];
						$itens .= "{$item["qtdadq"]}x {$item["nome"]} ({$item["valor"]} a unit)\n";
					}

					$dado[] = $itens;

					if($status == "Disponível" || $status == "Pago"){
						$tg += $vt;
					}

					$vt = "R$ " . number_format($vt, 2, ",", ".");

					$dado[] = "PagSeguro";

					$dado[] = "R$ " . number_format($transacao["frete"], 2, ",", ".");

					$dado[] = $transacao["desc"];

					$dado[] = $status;

					$dado[] = $vt;

					$data[] = $dado;
				}

				$excel->Instance()->getActiveSheet()->getColumnDimension('A')->setWidth(30);
				$excel->Instance()->getActiveSheet()->getColumnDimension('B')->setWidth(70);
				$excel->Instance()->getActiveSheet()->getColumnDimension('C')->setWidth(30);
				$excel->Instance()->getActiveSheet()->getColumnDimension('D')->setWidth(30);
				$excel->Instance()->getActiveSheet()->getColumnDimension('E')->setWidth(80);
				$excel->Instance()->getActiveSheet()->getStyle('E')->getAlignment()->setWrapText(true);
				$excel->Instance()->getActiveSheet()->getColumnDimension('F')->setWidth(25);
				$excel->Instance()->getActiveSheet()->getColumnDimension('G')->setWidth(25);
				$excel->Instance()->getActiveSheet()->getColumnDimension('H')->setWidth(30);
				$excel->Instance()->getActiveSheet()->getColumnDimension('I')->setWidth(25);

				$data[] = ["", "", "", "","","","","",""];
				$data[] = ["", "Total das vendas:", "R$ " . number_format($tg, 2, ",", "."), "","","","","",""];
				$data[] = ["", "", "", "","","","","",""];

				// $this->dbg($data);
			} else {
				$query = [];

				$excel->applyStyle("A1:F1", $styleTitle);

				if((int)$req["entrada"] == 1){
					$query[] = "sect = entrada";
				}

				if((int)$req["saida"] == 1){
					$query[] = "sect = saida";
				}

				$query = implode(" or ", $query);

				$vendas = $this->database()->query("findb", "$query");

				$data = [
					["Data", "Titulo", "Valor", "Tipo", "Forma de Pagamento", "Status"]
				];

				$tge = 0;$tgs = 0;

				$filter->set($req["data-inicio"]);

				$vendas_novo = ($filter->filter($req["data-final"], "data", $vendas));

				$vendas = [];

				foreach($vendas_novo as $dado){
					$key = ((strtotime(date("Y-m-d")) - strtotime($dado["data"])))/1000;
					while(isset($vendas[$key])){$key++;}
					$vendas[$key] = $dado;
				}

				$vendas = array_reverse($vendas);

				foreach($vendas as $index => $transacao){
					$index += 1;
					$excel->Instance()->getActiveSheet()->getRowDimension('1')->setRowHeight(-1);

					$dado = [
						date("d/m/Y", strtotime($transacao["data"])),
						$transacao["nome"],
						$transacao["valor"],
						$transacao["sect"] === "saida" ? "Saída" : $transacao["tipo"],
						isset($transacao["forma"]) ? $transacao["forma"]:"Dinheiro"
					];

					$status = $this->database()->query("pgtos", "cliente = {$transacao["id"]}");

					// $this->dbg($status);

					if(count($status) > 0){
						$status = $status[0]["status"];
					} else {
						$status = "not";
					}

					switch ($status) {
						case 'pg':
							$status = "OK";
						break;

						default:
							$status = "PENDENTE";
						break;
					}

					if($status == "OK"){
						if($transacao["sect"] == "entrada"){
							$tge += (float)str_replace(",", ".", str_replace(".", "", preg_replace("/[^0-9\.\,]/", "", $transacao["valor"])));
						}

						if($transacao["sect"] == "saida"){
							$tgs += (float)str_replace(",", ".", str_replace(".", "", preg_replace("/[^0-9\.\,]/", "", $transacao["valor"])));
						}
					}

					$dado[] = $status;

					// $vt = 0;
					// $itens = "";
					// foreach($transacao["itens"] as $item){
					// 	$item["nome"] = substr($item["nome"],0,30) . "...";
					// 	$vt += (float)str_replace(",", ".", str_replace(".", "", preg_replace("/[^0-9\.\,]/", "", $item["valor"]))) * (int)$item["qtdadq"];
					// 	$itens .= "{$item["qtdadq"]}x {$item["nome"]} ({$item["valor"]} a unit)\n";
					// }
					//
					// $dado[] = $itens;
					//
					// if($status == "Disponível" || $status == "Pago"){
					// 	$tg += $vt;
					// }
					//
					// $vt = "R$ " . number_format($vt, 2, ",", ".");
					//
					// $dado[] = "R$ " . number_format($transacao["frete"], 2, ",", ".");
					//
					// $dado[] = $transacao["desc"];
					//
					// $dado[] = $status;
					//
					// $dado[] = $vt;

					$data[] = $dado;
				}

				$excel->Instance()->getActiveSheet()->getColumnDimension('A')->setWidth(30);
				$excel->Instance()->getActiveSheet()->getColumnDimension('B')->setWidth(70);
				$excel->Instance()->getActiveSheet()->getColumnDimension('C')->setWidth(30);
				$excel->Instance()->getActiveSheet()->getColumnDimension('D')->setWidth(30);
				$excel->Instance()->getActiveSheet()->getColumnDimension('E')->setWidth(80);
				$excel->Instance()->getActiveSheet()->getStyle('E')->getAlignment()->setWrapText(true);
				$excel->Instance()->getActiveSheet()->getColumnDimension('F')->setWidth(25);
				$excel->Instance()->getActiveSheet()->getColumnDimension('G')->setWidth(25);
				$excel->Instance()->getActiveSheet()->getColumnDimension('H')->setWidth(30);
				$excel->Instance()->getActiveSheet()->getColumnDimension('I')->setWidth(25);

				$data[] = ["", "", "", "","","","","",""];
				$data[] = ["_______", "_______", "_______", "_______","_______","_______","_______","_______","_______"];
				$makeTotals = [];
				if((int)$req["entrada"] == 1){
					$makeTotals[] = "Total da Entrada:";
					$makeTotals[] = "R$ " . number_format($tge, 2, ",", ".");
				}
				if((int)$req["saida"] == 1){
					$makeTotals[] = "Total da Saida:";
					$makeTotals[] = "R$ " . number_format($tgs, 2, ",", ".");
				}
				while(count($makeTotals) < 10){
					$makeTotals[] = "";
				}
				$data[] = $makeTotals;
				$data[] = ["", "", "", "","","","","",""];

				// $this->dbg($data);
			}

			$excel->Instance()->getActiveSheet()->fromArray($data);

			$lastrow = $excel->Instance()->getActiveSheet()->getHighestRow();

			$styleArray = array(
				'font'  => array(
					'bold'  => true,
					'color' => (int)$req["entrada"] == 1 ? array('rgb' => '168511') : array('rgb' => '851611'),
					'size'  => 14
				));

			$excel->Instance()->getActiveSheet()->getStyle("B{$lastrow}")->applyFromArray($styleArray);

			$styleArray = array(
				'font'  => array(
					'bold'  => true,
					'color' => array('rgb' => '851611'),
					'size'  => 14
				));

			$excel->Instance()->getActiveSheet()->getStyle("D{$lastrow}")->applyFromArray($styleArray);

			$excel->Instance()->getActiveSheet()->getStyle('A1:I'.$lastrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

			$excel->Instance()->getActiveSheet()->getStyle('A1:I'.$lastrow)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

			$this->log->post("Gerou um relaório financeiro");

			exit($excel->Download("xls"));
		}

		function page_financeiro_apagar(){
			unlink($_POST["arq"]);
		}
	}

?>
