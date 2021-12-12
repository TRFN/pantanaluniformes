<?php
	function g_money($the){
		return (float)str_replace(",", ".", (preg_replace("/[^0-9,]/", "", $the)));
	}
	function s_money($the){
		return "R$ " . number_format($the, 2, ",", ".");
	}

    trait admin_produtos {
		private $sizename = 20;
		private function ajax_produtos($id=""){
            try{
                header("Content-Type: application/json");
				if(isset($_POST["cadprod"])){
                    $id = $_POST["cadprod"]["id"];
                    $_POST["cadprod"]["tp"] = "prod";
                    $query = $this->database()->query("produtos", "id = {$id}");

					if($_POST["cadprod"]["aluguel"] == "true" && $_POST["cadprod"]["naloja"] == "true"){
						$_POST["cadprod"]["naloja"] = "false";
					} elseif($_POST["cadprod"]["naloja"] == "true"){
						$_POST["cadprod"]["venda"] = "true";
						$_POST["cadprod"]["aluguel"] = "false";
					}

                    if(!count($query)){
                        $this->database()->push("produtos",array($_POST["cadprod"]));
						$txt = "Criou";
                    } else {
                        $this->database()->setWhere("produtos","id = {$id}",$_POST["cadprod"]);
						$txt = "Modificou";
                    }

					$insert = $_POST["cadprod"];
					$namelog = substr($insert["nome"],0,$this->sizename);
					if(strlen($namelog) != strlen($insert["nome"])){ $namelog .= "..."; }
					$this->log->post("{$txt} o produto:&nbsp;<a target=_blank data-skin='white' data-toggle='m-tooltip' data-placement='top' title='' data-original-title='{$insert["nome"]}' href='/admin/produto/{$insert["id"]}/' class='d-inline-block py-1 my-1'>{$namelog}</a>");
                } elseif(isset($_POST["cadfin"])){
                    // $id = $_POST["cadfin"]["id"];
                    // $_POST["cadfin"]["tp"] = "prod";
                    // $query = $this->database()->query("produtos", "id = {$id}");
					//
                    // if(!count($query)){
                    //     $this->database()->push("produtos",array($_POST["cadfin"]));
                    // } else {
                    //     $this->database()->setWhere("produtos","id = {$id}",$_POST["cadfin"]);
                    // }
					$cad = $_POST["cadfin"];
					if(!isset($cad["id"])){
						// foreach(array_keys($cad) as $nom){
						// 	foreach($cad[$nom] as $k1=>$dt){
						// 		$vazio = 0;
						// 		foreach($dt as $k2=>$dt2){
						// 			if(empty($dt2) || $dt2 == "R$ 0,00"){$vazio++;}
						// 		}
						//
						// 		// echo((string)$vazio);
						//
						// 		if($vazio > 1){
						// 			unset($cad[$nom][$k1]);
						// 		}
						// 	}
						// 	if(count($cad[$nom])>0 && $nom != "prod"){
						// 		$cad[$nom]["id"] = sha1(uniqid());
						// 		$this->database()->push("fin-{$nom}",[$cad[$nom]]);
						// 	}
						// }
						// var_dump($cad);
							// var_dump($cad["prod"]);
						foreach (array_keys($cad) as $key) {
							if(count($cad[$key]) > 0){
								$cad[$key]["id"] = sha1(uniqid());
								$this->database()->push("fin-{$key}",[$cad[$key]]);
							}
						}
					} else {
						if(isset($cad["prod"])){
							$cad["prod"]["id"] = $cad["id"];
							$this->database()->deleteWhere("fin-prod","id = {$cad["id"]}");
							$this->database()->push("fin-prod",[$cad["prod"]]);
						}
						if(isset($cad["contas"])){
							$cad["contas"]["id"] = $cad["id"];
							$this->database()->deleteWhere("fin-contas","id = {$cad["id"]}");
							$this->database()->push("fin-contas",[$cad["contas"]]);
						}
						if(isset($cad["gastos"])){
							$cad["gastos"]["id"] = $cad["id"];
							$this->database()->deleteWhere("fin-gastos","id = {$cad["id"]}");
							$this->database()->push("fin-gastos",[$cad["gastos"]]);
						}
					}
                } elseif(isset($_POST["cadaluguel"])){
                    $id = $_POST["cadaluguel"]["id"];

                    $query = $this->database()->query("alugueis", "id = {$id}");

                    if(!count($query)){
						$txt = "Criou";
                        $this->database()->push("alugueis",array($_POST["cadaluguel"]));
                    } else {
						$txt = "Modificou";
                        $this->database()->setWhere("alugueis","id = {$id}",$_POST["cadaluguel"]);
                    }
					$insert = $_POST["cadaluguel"];
					$namelog = substr($insert["nome"],0,$this->sizename);
					if(strlen($namelog) != strlen($insert["nome"])){ $namelog .= "..."; }
					$this->log->post("{$txt} o contrato de aluguel do &nbsp;<a target=_blank data-skin='white' data-toggle='m-tooltip' data-placement='top' title='' data-original-title='{$insert["nome"]}' href='/admin/alugueis/{$insert["id"]}/' class='d-inline-block py-1 my-1'>{$namelog}</a>");
                } else {
                    $query = $this->database()->query("produtos", "name = {$id}");
                    if(!count($query)){
                        $this->database()->push("produtos",array(array("content"=>$_POST["data"], "name" => "{$id}")));
                    } else {
                        $this->database()->setWhere("produtos","name = {$id}",array("content"=>$_POST["data"], "name" => "{$id}"));
                    }
                }
            } catch(Exception $e){
                exit("false");
            }
        	exit("true");
        }

		function page_cupons($content,$get=false){
            if($this->post())return $this->ajax_produtos("cupons_desc");

            $vars = array("TITLE" => "Cupons de Desconto da Loja");

            $query = parent::database()->query("produtos", "name = cupons_desc",array("content"));

            if(count($query) < 1){
                $cats = array();
            } else {
                $cats = $query[0]["content"];
            }

            if($get){
                return $cats;
            }

            $vars["valuesof"] = json_encode($cats);

            $content = $this->simple_loader($content, "admin/cupons", $vars);
            echo $content->getCode();
        }

		function page_categorias($content,$get=false){
            if($this->post())return $this->ajax_produtos("categorias");

            $vars = array("TITLE" => "Categorias");

            $query = parent::database()->query("produtos", "name = categorias",array("content"));

            if(count($query) < 1){
                $cats = array();
            } else {
                $cats = $query[0]["content"];
            }

            if($get){
                return $cats;
            }

            $vars["valuesof"] = json_encode($cats);

            $content = $this->simple_loader($content, "admin/categorias", $vars);
            echo $content->getCode();
        }

        function page_sub_categorias($content,$get=false){
            if($this->post())return $this->ajax_produtos("subcategorias");

            $vars = array("TITLE" => "Sub-Categorias");

            $query = parent::database()->query("produtos", "name = subcategorias",array("content"));

            if($get)return $query[0]["content"];

            if(count($query)>0)$vars["valuesof"] = json_encode($query[0]["content"]);
            else $vars["valuesof"] = "[]";

            $query = parent::database()->query("produtos", "name = categorias",array("content"));

            $vars["opcoes"] = "";

            if(count($query)>0){

                foreach($query[0]["content"] as $id=>$cat){
                    $vars["opcoes"] .= "<option value='{$id}'>{$cat}</option>";
                }

            }

            $content = $this->simple_loader($content, "admin/subcategorias", $vars);
            echo $content->getCode();
        }

		function page_produto($content){
            $content->minify = true;
            $db = "produtos";

            if(
                parent::url(2) == "apagar" && (!empty(parent::url(1)) || (string)parent::url(1) == "0") &&
                count(parent::database()->query($db, "tp=prod and id = " . ($query = (string)parent::url(1)))) > 0
            ){
                exit(parent::database()->deleteWhere($db, "tp=prod and id = {$query}"));
            } elseif(parent::url(2) == "apagar"){
                echo parent::url(1);
                exit;
            }

            $this->dropzoneUpload("imgprod");

            if(isset($_POST["imgs"])){
                $model = "";

                foreach($_POST["imgs"] as $img){
                    $model .= "<div style='margin: 48px 0px' class='col-lg-4 col-sm-6 col-xs-12'><div class='col-12 row text-center'><label class='col-12'>Legenda:</label><div class='col-6 offset-3'><input class=form-control type=text /></div></div><div class='col-12 img' style='background-image:url({$img})'><br /><br /><br /></div><div class='col-12 text-center'><button  class='apagar m-btn text-center m-btn--pill btn-outline-danger btn'><i class='la las la-trash'></i> Apagar</button></div></div>";
                }

                exit("{$model}");
            }

            if($this->post())return $this->ajax_produtos();

            $id = parent::database()->newID($db,"tp = prod");
            $vars = array(
                "acao" => "cadastrado",
                "id" => $id,
                "botao-txt" => "Add Produto",
                "TITLE" => "Adicionar Produto",
                "nome" => "",
                "categoria" => "0",
                "subcategoria" => "0",
                "ativo" => true,
                "lancamento" => false,
                "naloja" => false,
                "venda" => false,
                "vaiparaofacebook" => false,
                "aluguel" => false,
                "promocao" => false,
                "imagens" => "[]",
                "valor" => "R$ 0,00",
                "valor-a-vista" => "R$ 0,00",
                "descricao-longa" => "",
                "descricao-curta" => "",
                "quantidade" => "0",
                "codigo" => ""
            );

            if(!empty(parent::url(1)) || (string)parent::url(1) == "0"){
                if(count($query = $this->loja_produtos("id = " . (string)parent::url(1), "@ID > -1", "@ID > -1")) > 0){
                    $vars["TITLE"] = "Modificar Produto";
                    $vars["botao-txt"] = "Salvar Altera&ccedil;&otilde;es";
                    $vars["acao"] = "modificado";

                    foreach($query[0] as $id=>$val){
                        $vars[$id] = is_array($val) ? json_encode($val):$val;
                    }

					// $this->dbg($vars);

                    unset($vars[0]);

                } elseif(parent::url(1) == "listar"){
                    $produtos = $this->loja_produtos("id > -1", "@ID > -1", "@ID > -1");

					if(count($query=parent::database()->query("produtos","name=categorias")) > 0){
                    	foreach($produtos as $key=>$val){
							if(is_array($val["quantidade"])){
								$qtd = 0;
								foreach($val["quantidade"] as $n){
									$qtd += (int)$n;
								}
								$val["quantidade"] = $qtd;
							}
							$produtos[$key]["quant"] = "{$val["quantidade"]} Unit(s)";
							if(!isset($produtos[$key]["categoria"])){
								$produtos[$key]["categoria"] = 0;
							}

							if(!isset($produtos[$key]["subcategoria"])){
								$produtos[$key]["subcategoria"] = 0;
							}
							$produtos[$key]["catsub"] = isset($query[0]["content"][$produtos[$key]["categoria"]]) ? $query[0]["content"][$produtos[$key]["categoria"]]:"";

							if(count($query2=parent::database()->query("produtos","name=subcategorias")) > 0){
								$produtos[$key]["catsub"] .= isset($query2[0]["content"][$produtos[$key]["subcategoria"]]) ? " / {$query2[0]["content"][$produtos[$key]["subcategoria"]]["txt"]}":"";
							}
						}
					}

                    $btnTxt          = "Produto";
                    $keyword         = "produto";
                    $db              = $produtos;
                    $titulos         = "Codigo,Nome,Categoria,Estoque";
                    $dados           = "codigo,nome,catsub,quant";
                    $keyid           = "id";
                    $titulo          = "Gerir Produtos da Loja Virtual";

                    exit($this->_tablepage($content,$keyword,$titulos,$dados,$keyid,$titulo,$db,$btnTxt)->getCode());
                }
            }

			$query = parent::database()->query($db, "name = categorias",array("content"));

            $query2 = parent::database()->query($db, "name = subcategorias",array("content"));

            $vars["categorias"] = "";

            $vars["subcategorias"] = array();

            if(count($query) > 0){
                foreach($query[0]["content"] as $id=>$cat){
                    $vars["categorias"] .= "<option" . ((string)$id==$vars["categoria"]?" selected":"") . " value='{$id}'>{$cat}</option>";
                    if(count($query2) > 0){
                        $query3 = parent::database()->query($query2[0]["content"], "vinculo = {$id}", array("txt"));
                        $vars["subcategorias"][$id] = "";
                        // print_r($query3);
                        foreach($query3 as $idsub => $subcat){
                            $vars["subcategorias"][$id] .= "<option" . ((string)$idsub==$vars["subcategoria"]?" selected":"") . " value='{$idsub}'>{$subcat["txt"]}</option>";
                        }
                    }
                }
            }

            $vars["subcathtml"] = isset($vars["subcategorias"][(int)$vars["categoria"]])?$vars["subcategorias"][(int)$vars["categoria"]]:"";

            $vars["subcategorias"] = preg_replace("/(selected)/","",json_encode($vars["subcategorias"]));

            $content = $this->simple_loader($content, "admin/produto", $vars);

            echo $content->getCode();
        }

		function page_alugueis($content){
            $content->minify = true;
            $db = "alugueis";

            if(
                parent::url(2) == "apagar" && (!empty(parent::url(1)) || (string)parent::url(1) == "0") &&
                count(parent::database()->query($db, "id = " . ($query = (string)parent::url(1)))) > 0
            ){
                exit(parent::database()->deleteWhere($db, "id = {$query}"));
            } elseif(parent::url(2) == "apagar"){
                echo parent::url(1);
                exit;
            }

            if($this->post())return $this->ajax_produtos();

            $id = parent::database()->newID($db);
            $vars = array(
                "acao" => "criado",
                "id" => $id,
                "botao-txt" => "Criar Contrato",
                "TITLE" => "Gerar Contrato de Aluguel",
				"nome" => "",
				"ref_nome" => "",
				"doc" => "",
				"rg" => "",
				"rua" => "",
				"numero" => "",
				"complemento" => "",
				"bairro" => "",
				"cidade" => "",
				"estado" => "",
				"tel" => "",
				"cel" => "",
				"ref_tel" => "",
				"ref_cel" => "",
				"email" => "",
				"produtos" => "[]",
				"produtos-aluguel" => ""
            );

			foreach(parent::database()->query("produtos","aluguel = true and ativo = true") as $prod){
				$jprod = json_encode($prod);
				$vars["produtos-aluguel"] .=  "<option value='{$jprod}'>{$prod["codigo"]}&nbsp;&nbsp;{$prod["nome"]}</option>";
			}

            if(!empty(parent::url(1)) || (string)parent::url(1) == "0"){
                if(count($query = parent::database()->query($db, "id = " . (string)parent::url(1))) > 0){
                    $vars["TITLE"] = "Modificar Contrato";
                    $vars["botao-txt"] = "Salvar Altera&ccedil;&otilde;es";
                    $vars["acao"] = "modificado";

                    foreach($query[0] as $id=>$val){
                        $vars[$id] = is_array($val) ? json_encode($val):$val;
                    }

                    unset($vars[0]);

                } elseif(parent::url(1) == "listar"){
                    $produtos = parent::database()->query($db, "id > -1");

					foreach($produtos as $index=>$p){
						$produtos[$index]["data"] = "{$p["@CREATED"][0][0]}/{$p["@CREATED"][0][1]}/{$p["@CREATED"][0][2]}";
						$produtos[$index]["contrato"] = '<a href="javascript:;" onclick="imprimir_contrato(' . $p["id"] . ')" class="m-portlet__nav-link btn m-btn m-btn--hover-info m-btn--icon m-btn--icon-only m-btn--pill" title="Imprimir"><i class="la la-print"></i></a>';
						$expirados = 0;
						foreach($produtos[$index]["produtos"] as $prod){
							if(strtotime($prod["devolucao"]) - strtotime(date("Y-m-d")) < 1){
								$expirados++;
							}
						}
						$produtos[$index]["alugueis_vencidos"] = (string)$expirados . " produto(s)";
					}

					// $this->dbg($produtos);

                    $btnTxt          = "Contrato";
                    $keyword         = "alugueis";
                    $db              = $produtos;
                    $titulos         = "Nome,Documento,Itens Vencidos,Data,Status,Contrato";
                    $dados           = "nome,doc,alugueis_vencidos,data,status,contrato";
                    $keyid           = "id";
                    $titulo          = "Gerir Alugueis";

                    exit($this->_tablepage($content,$keyword,$titulos,$dados,$keyid,$titulo,$db,$btnTxt)->getCode());
                }
            }

            $content = $this->simple_loader($content, "admin/alugueis", $vars);

            echo $content->getCode();
        }

        function page_config_menu($content){
            if($this->post())return $this->ajax_produtos("menu");

            $ordens = "";

            $opcoes = array(
                "Home" => "/"
            );

            // $tiny = json_decode(parent::control("connect/tinyERP")->categorias(),true);
            $sep = "/";

            $categorias_ecommerce = $this->page_categorias("",true);
            $subcategorias_ecommerce = $this->page_sub_categorias("",true);

            foreach($categorias_ecommerce as $ind=>$opt){
                $opcoes[ucfirst($opt)] = "/"  . (string)$ind . $sep . $this->slug($opt) . "/";
                foreach(parent::database()->query($subcategorias_ecommerce,"vinculo={$ind}") as $ind2=>$opt2){
                    $opcoes[ucfirst($opt) . " / " . ucfirst($opt2["txt"])] = "/"  . (string)$ind . $sep . $this->slug($opt) . "/"  . (string)$ind2 . $sep . $this->slug($opt2["txt"]) . "/";
                }
            }

            foreach($this->page_categorias(false,true) as $ind=>$opt){
                $opcoes[$opt] = "/"  . (string)$ind . $sep . $this->slug($opt) . "/";
            }

            // foreach($tiny["retorno"] as $cat){
            //     $opcoes[ucfirst($cat["descricao"])] = "/"  . $cat["id"] . $sep . $this->slug($cat["descricao"]) . "/";
            //     foreach($cat["nodes"] as $subcat){
            //         $opcoes[ucfirst($cat["descricao"]) . " / " . ucfirst($subcat["descricao"])] = "/"  . $cat["id"] . $sep . $this->slug($cat["descricao"]) . "/"  . $subcat["id"] . $sep . $this->slug($subcat["descricao"]) . "/";
            //         foreach($subcat["nodes"] as $subcat2){
            //             $opcoes[ucfirst($cat["descricao"]) . " / " . ucfirst($subcat2["descricao"])] = "/"  . $cat["id"] . $sep . $this->slug($cat["descricao"]) . "/"  . $subcat2["id"] . $sep . $this->slug($subcat2["descricao"]) . "/";
            //         }
            //     }
            // }

            $opcoes_html = "";

            foreach($opcoes as $titulo=>$url){
                $opcoes_html .= "<option value='{$url}'>{$titulo}</option>";
            }

            for($i = 1; $i < 50; $i++){
                $i = $i < 10 ? "0{$i}":(string)$i;
                $ordens .= "<option value='{$i}'>{$i}</option>";
            }

            $query = parent::database()->query("produtos", "name = menu",array("content"));

            if(count($query) < 1){
                $query = [];
            } else {
                $query = $query[0]["content"];
            }

            echo $this->simple_loader($content, "admin/config-menu", array(
                "TITLE" => "Configurar menu da loja virtual",
                "ordens" => $ordens,
                "opcoes_link" => $opcoes_html,
                "menu_data" => json_encode($query)
            ), array("t_opcao"=>"admin/opcao_menuconf"))->getCode();
        }

		function page_email_marketing($content){
			$filtros = "not";
			if(parent::url(1) === "filtrar"){

				$filtros = [];

				$i = 2;

				if(!empty(parent::url($i)) && parent::url($i) !== "?ajax=1"){
					$filtros[] = "name = %" . parent::url($i) . "%";
				}

				$i++;

				if(!empty(parent::url($i)) && parent::url($i) !== "?ajax=1"){
					$filtros[] = "email = %" . parent::url($i) . "%";
				}

				$i++;

				if(!empty(parent::url($i)) && parent::url($i) !== "?ajax=1"){
					$data = explode("-", parent::url($i));
					$data = "{$data[2]}/{$data[1]}/{$data[0]}";
					$filtros[] = "data-cadastro = %" . $data . "%";
				}

				$filtros = implode(" and ", $filtros);

				$filtros = empty($filtros) ? "not":$filtros;

				// $this->dbg($filtros);
			}

			$vars = array(
                "id"        => "",
                "botao-txt" => "Contas Cadastradas no Site",
                "TITLE"     => "Email Marketing",
                "nome"      => "",
                "email"     => "",
                "size_l"    => 12,
                "size_r"    => 12,
                "acao"      => "",
                "page"      => ""
            );

			$db = parent::database()->query("contas-loja","@ID > -1");

			foreach(array_keys($db) as $k){
				$db[$k]["data-cadastro"] = "{$db[$k]["@CREATED"][0][0]}/{$db[$k]["@CREATED"][0][1]}/{$db[$k]["@CREATED"][0][2]}";
			}

			if(isset($_POST["data"])){
				$excel = parent::control("util/excel");
	            $excel->SetDocumentTitle("email-marketing");
				$excel->Instance()->getActiveSheet()->fromArray($_POST["data"]);
				$excel->Instance()->getActiveSheet()->getColumnDimension('A')->setWidth(40);
				$excel->Instance()->getActiveSheet()->getColumnDimension('B')->setWidth(40);
				$excel->Instance()->getActiveSheet()->getColumnDimension('C')->setWidth(40);
				$lastrow = $excel->Instance()->getActiveSheet()->getHighestRow();

				$excel->Instance()->getActiveSheet()->getStyle('A1:A'.$lastrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$excel->Instance()->getActiveSheet()->getStyle('B1:B'.$lastrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$excel->Instance()->getActiveSheet()->getStyle('C1:C'.$lastrow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

				exit($excel->Save("xls"));
			}

			$btnTxt          = "Administrador";
			$keyword         = "administradores";
			$db              = $db;
			$titulos         = "Nome,E-mail,Data de Cadastro";
			$dados           = "name,email,data-cadastro";
			$keyid           = "id";
			$titulo          = "Listagem de clientes cadastrados";

			exit($this->_tablepage($content,$keyword,$titulos,$dados,$keyid,$titulo,$db,$btnTxt,$filtros,false,"admin/emailmarketing")->getCode());
        }

		function page_contrato(UITemplate $content){
			$content->minify = false;
			$id_contrato = parent::url(1);
			$content->uiTemplate("admin/contrato");
			setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
			date_default_timezone_set('America/Sao_Paulo');

			$data = parent::database()->query("alugueis", "id = {$id_contrato}");

			$defaults = array("nome" => "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;","doc" => "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;","rg" => "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;","rua" => "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;","numero" => "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;","complemento" => "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;","bairro" => "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;","cidade" => "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;","estado" => "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;","tel" => "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;","cel" => "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;","produtos" => []);

			$data = array_merge(array("dia" => strftime('%d', strtotime('today')),
			"mes" => ucfirst(strftime('%B', strtotime('today'))),
			"ano" => date("Y")), isset($data[0]) ? array_merge($defaults, $data[0]):$defaults);

			foreach($data as $dk=>$dv){
				if(is_string($data[$dk]) && empty($data[$dk])){
					$data[$dk] = $defaults[$dk];
				}
			}

			$produtos = $data["produtos"];

			$data["produtos"] = "<table style='text-align: left; display: table;'><tbody><tr>";
			$data["prazos"] = "<table style='text-align: left; display: table;'><tbody><tr>";
			$data["precos"] = "<table style='text-align: left; display: table;'><tbody><tr>";
			$data["valores-caso-dano"] = "<table style='text-align: left; display: table;'><tbody><tr>";
			$data["transporte-valores"] = "<table style='text-align: left; display: table;'><tbody><tr>";
			$data["devolucoes"] = "<table style='text-align: left; display: table;'><tbody><tr>";
			$index = 0;
			$counter = 0;

			foreach($produtos as $produto){
				$index++;

				if($counter > 9){
					$counter = 1;
					$data["prazos"] .= "</tr><tr>";
					$data["precos"] .= "</tr><tr>";
					$data["valores-caso-dano"] .= "</tr><tr>";
					$data["transporte-valores"] .= "</tr><tr>";
					$data["devolucoes"] .= "</tr><tr>";
				} else {
					$counter++;
				}

				if(empty($produto["devolucao"])){
					$produto["devolucao"] = date("Y-m-d");
				}

				$data["produtos"] .= "<tr><td>({$index}) - </td><td style='text-decoration: underline;'>{$produto["nome"]}</td><td>&nbsp;&nbsp;&nbsp;&nbsp;</td><td>Nº Patrimônio:</td><td style='text-decoration: underline;'>{$produto["patrimonio"]}</td></tr>";

				$data["precos"] .= "<td>({$index}) - </td><td style='text-decoration: underline;'>{$produto["valor"]}</td><td>&nbsp;&nbsp;</td>";

				$data["prazos"] .= "<td>({$index}) - </td><td style='text-decoration: underline;'>" . 1 . " Dias</td><td>&nbsp;&nbsp;</td>";

				$data["valores-caso-dano"] .= "<td>({$index}) - </td><td style='text-decoration: underline;'>{$produto["dano"]}</td><td>&nbsp;&nbsp;</td>";

				$data["transporte-valores"] .= "<td>({$index}) - </td><td style='text-decoration: underline;'>{$produto["transporte"]}</td><td>&nbsp;&nbsp;</td>";

				$produto["devolucao"] = date("d/m/Y", strtotime($produto["devolucao"]));

				$data["devolucoes"] .= "<td>({$index}) - </td><td style='text-decoration: underline;'>{$produto["devolucao"]}</td><td>&nbsp;&nbsp;</td>";
			}

			$data["produtos"] .= "</tbody></table>";
			$data["prazos"] .= "</tr></tbody></table>";
			$data["precos"] .= "</tr></tbody></table>";
			$data["valores-caso-dano"] .= "</tr></tbody></table>";
			$data["transporte-valores"] .= "</tr></tbody></table>";
			$data["devolucoes"] .= "</tr></tbody></table>";

			$social = $this->database()->get("social");

			if(isset($social["contatos"])){
				$content->applyVars($social["contatos"]);
				unset($social["contatos"]);
			}

			$data["observacoes"] = strip_tags($data["observacoes"]);

			$content->applyVars($data);
			echo $content->getCode();
		}

		function page_nfs_upload(UITemplate $content){
			if(isset($_FILES['arquivo']['tmp_name'])){
				if($_FILES['arquivo']["type"] !== "text/xml"){
					header("Location: /admin/nfs_upload/");
				}
				$xml = parent::control("util/nfe")->read(file_get_contents($_FILES['arquivo']['tmp_name']));
				$produtos = is_array($xml) && isset($xml["itens"]) && is_array($xml["itens"]) ? $xml["itens"]:[];
				$addprod = [];
				$upd = false;

				foreach($produtos as $produto){
					$existe = count($prode=parent::database()->query("produtos", "cnf = {$produto["codigo"]}")) > 0;

					if($existe){
						$qtd = [(int)$prode[0]["quantidade_estoque_funcionarios"], (int)$prode[0]["quantidade_estoque_barreiro"]];
					} else {
						$qtd = [0,0];
					}

					$qtdp = (int)$produto["quantidade"];
					$estado = 0;

					while($qtdp--){
						$qtd[$estado] += 1;
						$estado = $estado == 0 ? 1:0;
					}

					$dadosprod = array(
						"cnf" => $produto["codigo"],
						"codigo" => $produto["codigo"],
						"nome" => $produto["nome"],
						"quantidade_estoque_funcionarios" => "{$qtd[0]}",
						"quantidade_estoque_barreiro" => "{$qtd[1]}",
						"tp" => "prod"
					);

					if($existe){
						parent::database()->setWhere("produtos", "cnf = {$produto["codigo"]}", $dadosprod);
						$upd = true;
					} else {
						$dadosprod["id"] = parent::database()->newID($db,"tp = prod");
						$addprod[] = $dadosprod;
					}
				}

				if(count($addprod) > 0){
					parent::database()->push("produtos", $addprod);
					$upd = true;
				}

				if($upd){
					header("Location: /admin/nfs_upload/?op=1");
				} else {
					header("Location: /admin/nfs_upload/");
				}
			} else {
				echo $this->simple_loader($content, "admin/nfxml", ["TITLE" => "Importar Produtos Nota Fiscal"])->getCode();
			}
		}
	}
