<?php
	trait site_produtos {
		function page_adicionar_tamanho(){
			if(!in_array(strtoupper($_REQUEST["nome"]), $this->database()->get("tamanhos"))){
				$this->database()->push("tamanhos", array(strtoupper($_REQUEST["nome"])), "log_remove");
			}

			return $this->page_carregar_tamanho();
		}

		function page_mo_tamanho(){
			$this->database()->rewrite("tamanhos", $_REQUEST["tams"]);

			return $this->page_carregar_tamanho();
		}

		function page_deletar_tamanho(){
			if(in_array(strtoupper($_REQUEST["nome"]), ($tamanhos=$this->database()->get("tamanhos")))){
				$tamanhos = array_values(array_diff($tamanhos, [strtoupper($_REQUEST["nome"])]));
				$this->database()->rewrite("tamanhos", $tamanhos);
			}

			return $this->page_carregar_tamanho();
		}

		function page_editar_tamanho(){
			if(in_array(strtoupper($_REQUEST["nome"]), ($tamanhos=$this->database()->get("tamanhos")))){
				$index = array_search(strtoupper($_REQUEST["nome"]), $tamanhos);
				array_splice($tamanhos, $index, 1, strtoupper($_REQUEST["para"]));
				// $this->dbg([$index, $tamanhos]);
				$this->database()->rewrite("tamanhos", array_values(array_unique($tamanhos)));
			}

			return $this->page_carregar_tamanho();
		}

		function page_carregar_tamanho(){
			return $this->json($this->database()->get("tamanhos"));
		}

		function page_checksum_tamanho(){
			header("Content-Type: text/plain");
			exit(sha1(serialize($this->database()->get("tamanhos"))));
		}

		function page_json_produtos(){
			$retorno = [];
			foreach($this->loja_produtos(isset($_POST["prod"])?"id={$_POST["prod"]}":"@ID > -1") as $p){
				$fav_state = isset($this->sessao()->favs) ? $this->sessao()->favs:array();
				if(in_array($p["id"], $fav_state)){
					$key = array_search($p["id"], $fav_state);
					if($key !== false){
					    $fav_state = true;
					}
				}

				$fav_state = $fav_state === true ? "fas":"far";

				$retorno[] = array_merge($p, array(
					"id" => $p["id"],
					"nome" => $p["nome"],
					"cat" => (($cat=$this->get_cat($p["categoria"])) !== false ? (
						($subcat=$this->get_subcat($p["subcategoria"])) !== false
							? "{$cat} / {$subcat["txt"]}"
							: "{$cat}"
					) : ""),
					"img" => ($img=(isset($p["imagens"]) && isset($p["imagens"][0]))) ? $p["imagens"][0]["url"] : "img/products/product-grey-1.jpg",
					"imgt" => $img ? $p["imagens"][0]["legend"] : "",
					"link" => "/produtos/{$p["id"]}-" . $this->slug("{$cat} {$subcat["txt"]} {$p["nome"]}") . "/",
					"old_price" => ($promo=("{$p["valor-a-vista"]}"=="R$ 0,00")) ? "" : $p["valor"],
					"price" => $promo ? $p["valor"] : $p["valor-a-vista"],
					"fav" => $fav_state,
					"disponivel" => (int)$p["quantidade"] > 0
				));
			}
			$this->json($retorno);
		}

		function get_cat($id=-1){
			$cats = parent::database()->query("produtos", "name = categorias",array("content"));
			$id = (int)$id;
			return (count($cats) > 0 ? ($id === -1 ? $cats[0]["content"] : (isset($cats[0]["content"][$id]) ? $cats[0]["content"][$id] : false)) : false);
		}

		function get_subcat($id=-1){
			$cats = parent::database()->query("produtos", "name = subcategorias",array("content"));
			$id = (int)$id;
			return (count($cats) > 0 ? ($id === -1 ? $cats[0]["content"] : (isset($cats[0]["content"][$id]) ? $cats[0]["content"][$id] : false)) : false);
		}

		function modelo_minhatura_produtos(UITemplate $content, $queryby="@ID > -1", $limit = 10, $style1 = "product mb-0", $init="", $end=""){
			$retorno = "";
			$obj = (is_array($queryby) ? $queryby : $this->loja_produtos($queryby));
			// $this->dbg($obj);
			foreach($obj as $p){
				$fav_state = isset($this->sessao()->favs) ? $this->sessao()->favs:array();
				if(in_array($p["id"], $fav_state)){
					$key = array_search($p["id"], $fav_state);
					if($key !== false){
						$fav_state = true;
					}
				}

				$fav_state = $fav_state === true ? "fas":"far";
				if($limit < 1){break;}
				// $this->dbg($p);
				$retorno .= $content->loadModel("site/produto-minhatura", array(
					"id" => $p["id"],
					"nome" => $submenu["texto"] = mb_ucfirst(mb_strtolower($p["nome"])),
					"cat" => (($cat=$this->get_cat($p["categoria"])) !== false ? (
						($subcat=$this->get_subcat($p["subcategoria"])) !== false
							? "{$cat} / {$subcat["txt"]}"
							: "{$cat}"
					) : ""),
					"queryCat" => "{$p["categoria"]}/{$p["subcategoria"]}",
					"img" => ($img=(isset($p["imagens"]) && isset($p["imagens"][0]))) ? $p["imagens"][0]["url"] : "img/products/product-grey-1.jpg",
					"imgt" => $img ? $p["imagens"][0]["legend"] : "",
					"link" => "/produtos/{$p["id"]}-" . $this->slug("{$cat} {$subcat["txt"]} {$p["nome"]}") . "/",
					"old_price" => ($promo=("{$p["valor-a-vista"]}"=="R$ 0,00"||empty("{$p["valor-a-vista"]}"))) ? "" : $p["valor"],
					"price" => $promo ? $p["valor"] : $p["valor-a-vista"],
					"fav" => $fav_state,
					"style1" => $style1,
					"init" => $init,
					"end" => $end,
					"disponivel" => (int)$p["quantidade"] > 0 ? "addtocart-btn-wrapper":"d-none",
					"indisponivel" => (int)$p["quantidade"] < 1 ? "addtocart-btn-wrapper":"d-none"
				));
				$limit--;
			}
			return $retorno;
		}

		function page_facebook_list(){
			$fba = parent::control("connect/facebookApi");
			$produtos = [];
			$prods = $this->loja_produtos("naloja = true and venda = true and aluguel = false and vaiparaofacebook=true");

			foreach($prods as $p){
				$p["descricao-curta"] = ($ns=substr(($p["descricao-curta"]=strip_tags($p["descricao-curta"])),0,100)) != $p["descricao-curta"]
					? $ns . "..."
					: $p["descricao-curta"];

				$produtos[] = array_merge($p, array(
					"id" => $p["id"],
					"nome" => $p["nome"],
					"cat" => (($cat=$this->get_cat($p["categoria"])) !== false ? (
						($subcat=$this->get_subcat($p["subcategoria"])) !== false
							? "{$cat} / {$subcat["txt"]}"
							: "{$cat}"
					) : ""),
					"img" => ($img=(isset($p["imagens"]) && isset($p["imagens"][0]))) ? $p["imagens"][0]["url"] : "img/products/product-grey-1.jpg",
					"link" => "{$this->mydomain}/produtos/{$p["id"]}-" . $this->slug("{$cat} {$subcat["txt"]} {$p["nome"]}") . "/",
					"price" => str_replace(",",".",(preg_replace("/[^0-9\,]/","",("{$p["valor-a-vista"]}"=="R$ 0,00") ? $p["valor"] : $p["valor-a-vista"]))) . " BRL",
					"gpc" => array($cat,$p["nome"],$subcat["txt"]),
					"stock" => $p["quantidade"] > 0
						? "in stock"
						: "out of stock",
					"new" => "new"
				));
			}

			$fba->setData($produtos, array(
				"id" => "id",
				"title" => "nome",
				"description" => "descricao-curta",
				"availability" => "stock",
				"condition" => "new",
				"price" => "price",
				"link" => "link",
				"image_link" => "img",
				"brand" => "cat",
				"google_product_category" => "gpc"
			));

			$fba->render();
		}

		function page_produtos(UITemplate $content){
			$link = explode("-",$this->url(1));

			$error = !isset($link[0]) || preg_match("/[^0-9]/", $link[0]);

			if(!($error = $error || (count($query = $this->loja_produtos("id = {$link[0]}")) == 0))){
				$produto = $query[0];

				$p = $produto;

				$imgs = "";
				$imgs_thumbs = "";

				$fav_state = isset($this->sessao()->favs) ? $this->sessao()->favs:array();
				if(in_array($p["id"], $fav_state)){
					$key = array_search($p["id"], $fav_state);
					if($key !== false){
					    $fav_state = true;
					}
				}
				$fav_state = $fav_state === true ? "fas":"far";

				foreach($produto["imagens"] as $img){
					$imgs .=
						"<div>
							<img alt=\"{$img["legend"]}\" class=\"img-fluid\" src=\"{$img["url"]}\" />
						</div>";

					$imgs_thumbs .=
						"<div class=\"cur-pointer\">
							<img alt=\"{$img["legend"]}\" class=\"img-fluid\" src=\"{$img["url"]}\" />
						</div>";
				}

				$tags = implode("% or nome = %", explode(" ", $p["nome"]));

				$indicacoes = $this->modelo_minhatura_produtos($content, "categoria = {$p["categoria"]} or subcategoria = {$p["subcategoria"]} and nome = %{$tags}%", 30);

				if($indicacoes === ""){
					$indicacoes = $this->modelo_minhatura_produtos($content);
				}

				$dados = array_merge($p, array(
					"titulo" => $p["nome"],
					"cat" => (($cat=$this->get_cat($p["categoria"])) !== false ? (
						($subcat=$this->get_subcat($p["subcategoria"])) !== false
							? "{$cat} / {$subcat["txt"]}"
							: "{$cat}"
					) : ""),
					"imgs" => $imgs,
					"imgs_thumb" => $imgs_thumbs,
					"link" => "/produtos/{$p["id"]}-" . $this->slug("{$cat} {$subcat["txt"]} {$p["nome"]}") . "/",
					"old_price" => ($promo=("{$p["valor-a-vista"]}"=="R$ 0,00" || empty($p["valor-a-vista"])))
						? ""
						: $p["valor"],
					"price" => $promo ? $p["valor"]:$p["valor-a-vista"],
					"fav" => $fav_state,
					"indicacoes" => $indicacoes,
					"disponivel" => (int)$p["quantidade"] > 0 ? "":"d-none",
					"indisponivel" => (int)$p["quantidade"] < 1 ? "":"d-none",
					"virtualh" => (!isset($p["tipo"]) || $p["tipo"] == "fisico") ? "":"d-none",
					"virtuald" => (!isset($p["tipo"]) || $p["tipo"] == "fisico") ? "d-none":""

				));

				$dados["TITLE"] = "{$p["nome"]} - {$dados["cat"]}";

				exit($this->simple_loader($content, "site/detalhes", $dados)->getCode());
			}

			if($error){
				$this->notfound($content);
			}

		}

		function page_fechar_pedido(UITemplate $content){
			if($this->sessao()==false){
				header("Location: /entrar/?retorno=fechar_pedido");
			}

			$enderecos = isset($this->sessao()->enderecos) ? $this->sessao()->enderecos:[];

			// $this->dbg($this->status_pagseguro("061120213657971910986523030"));

			if($this->post()){
				if(count($enderecos) < 1){
					header("Location: /fechar_pedido/?houveumerro=2&cod=" . $post["codigo"]);
					exit;
				}

				$post = $_POST;

				$debug = false;

				$post["codigo"] = $this->get_transaction_now_id();
				$desc = utf8_encode(base64_decode($post["d_c"]));
				$desc = (float)str_replace(",", ".", str_replace(".", "", preg_replace("/[^0-9\.\,]/", "", $desc)));
				$post["desc"] = "R$ " . number_format($desc, 2, ",", ".");
				$post["itens"] = $this->get_produtos_pagseguro();
				$post["frete"] = (float)preg_replace("/[^0-9\.]/","",str_replace(",", ".", str_replace(".", "", trim(str_replace("R$", "", $post["envio"])))));
				$post["telefone"] = preg_replace("/[^0-9]/","",$this->sessao()->tel);

				$getdata = [
					"id" => $post["codigo"],
					"desc" => $post["desc"],
					"frete" => $post["frete"],
					"itens" => [],
					"variacao" => [],
					"cliente" => $this->sessao()->id,
					"data" => date("Y-m-d"),
					"endereco" => $post["endereco"],
					"envio" => $post["modo_envio"]
				];

				if($debug){ $post["desc"] = "R$ 0,00"; $post["frete"] = (float)0; }

				foreach(array_keys($post["itens"]) as $i){
					if($debug){
						$post["itens"][$i]["preco"] = "R$ " . number_format(1.1/count($post["itens"])/(float)$post["itens"][$i]["qtd"], 2, ",", ".");
					}
					$getdata["itens"][$i] = [$post["itens"][$i]["id"],$post["itens"][$i]["preco"],$post["itens"][$i]["qtd"]];
					if(isset($post["variacao"][$post["itens"][$i]["id"]])){
						$getdata["variacao"][$i] = json_decode($post["variacao"][$post["itens"][$i]["id"]], true);
						$post["itens"][$i]["nome"] = substr($post["itens"][$i]["nome"], 0, 35) . " | {$getdata["variacao"][$i]["nome"]}";
					}
				}

				if(isset($post["endereco"]) && isset($enderecos[$post["endereco"]])){
					$post = array_merge($post,$enderecos[$post["endereco"]]);
				}

				unset($post["d_c"]);
				unset($post["tabela-carrinho-pagina_length"]);
				unset($post["envio"]);
				unset($post["variacao"]);

				// $this->dbg($getdata);


				$qt = $this->database()->query("transacoes", "id = {$getdata["id"]}"); // (Q)uery (T)ransaction

				if(count($qt)){
					$this->database()->setWhere("transacoes", "id = {$getdata["id"]}", $getdata);
				} else {
					$this->database()->push("transacoes", [$getdata], "log_remove");
				}

				// $this->dbg($post);

				// exit("OK");

			    $this->PagSeguro->sys->executeCheckout($post, "http://ampararbh.com.br/minhas_compras/");
			}

			$vars = ["enderecos" => "","erro" => "", "TITLE" => "Fechar Pedido"];

			$vars["erro"] = isset($_GET["houveumerro"]) ? "{$_GET["houveumerro"]}":"0";

			if(count($enderecos) > 0){
				foreach($enderecos as $k=>$l){
					$vars["enderecos"] .= "<option value=\"{$k}\" data-apply='" . json_encode($l) . "'>Receber em {$l["rua"]}, {$l["numero"]} - {$l["bairro"]}</option>";
				}
			}



			exit($this->simple_loader($content, "site/fechar-pedido", $vars)->getCode());
		}

		function page_pesquisa(UITemplate $content){
			// $produtos = $this->loja_produtos();
			//
			// for($i = 0; $i < count($produtos); $i++){
			// 	$produtos[$i]["query_for_search"] = (($cat=$this->get_cat($p["categoria"])) !== false ? (
			// 		($subcat=$this->get_subcat($p["subcategoria"])) !== false
			// 			? "{$cat} {$subcat["txt"]}"
			// 			: "{$cat}"
			// 	) : "") . " {$produtos[$i]["nome"]} {$produtos[$i]["descricao-curta"]}";
			// }

			$q = $_GET["q"];

			while(!in_array(strtolower(substr($q, strlen($q) - 1, strlen($q))), ["a","e","i","o","u"]) && strlen($q) > 4){
				$q = substr($q, 0, strlen($q) - 1);
			}

			$produtos = $this->loja_produtos("nome = %{$q}%");

			// $this->dbg($produtos);

			$produtos = $this->modelo_minhatura_produtos($content, $produtos, 20, "product mb-0", '<div class="col-12 col-sm-6 col-lg-3 mb-5">','</div>');

			exit($this->simple_loader($content, "site/pesquisa", ["TITLE" => "Pesquisa", "termo" => $_GET["q"], "resultado" => $produtos])->getCode() . "<script>LWDKExec(()=>$('.search-form-wrapper input[type=search]').val('{$_GET["q"]}'))</script>");
		}

		function _calcular_frete(
            $cep_origem,  /* cep de origem, apenas numeros */
            $cep_destino, /* cep de destino, apenas numeros */
            $valor_declarado='0', /* indicar 0 caso não queira o valor declarado */
            $peso='1',        /* valor dado em Kg incluindo a embalagem. 0.1, 0.3, 1, 2 ,3 , 4 */
            $altura='15',      /* altura do produto em cm incluindo a embalagem */
            $largura='15',     /* altura do produto em cm incluindo a embalagem */
            $comprimento='15', /* comprimento do produto incluindo embalagem em cm */
            $cod_servico='pac' /* codigo do servico desejado */
            ){

            $cod_servico = strtoupper( $cod_servico );
            if( $cod_servico == 'SEDEX10' ) $cod_servico = 40215;
            if( $cod_servico == 'SEDEXACOBRAR' ) $cod_servico = 40045;
            // if( $cod_servico == 'SEDEX' ) $cod_servico = 40010;
            // if( $cod_servico == 'PAC' ) $cod_servico = 41106;
            if( $cod_servico == 'SEDEX' ) $cod_servico = "03220";
            if( $cod_servico == 'PAC' ) $cod_servico = "03298";

			// $login = "";
			// $senha = "";
			$login = "21404208";
			$senha = "35949447";

            ###############################################
            # Código dos Principais Serviços dos Correios #
            # 41106 PAC sem contrato                      #
            # 40010 SEDEX sem contrato                    #
            # 40045 SEDEX a Cobrar, sem contrato          #
            # 40215 SEDEX 10, sem contrato                #
            ###############################################

            $correios = "http://ws.correios.com.br/calculador/CalcPrecoPrazo.aspx?nCdEmpresa={$login}&sDsSenha={$senha}&sCepOrigem=".$cep_origem."&sCepDestino=".$cep_destino."&nVlPeso=".$peso."&nCdFormato=1&nVlComprimento=".$comprimento."&nVlAltura=".$altura."&nVlLargura=".$largura."&sCdMaoPropria=n&nVlValorDeclarado=".$valor_declarado."&sCdAvisoRecebimento=n&nCdServico=".$cod_servico."&nVlDiametro=0&StrRetorno=xml";

            // return($correios);

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

		function calcular_frete(){
            if(isset($_GET["consultaCEP"]) && $_GET["consultaCEP"] === "1"){
                foreach(array("largura","altura","comprimento") as $medida){
                    if((float)$_POST[$medida] < 15){
                        $_POST[$medida] = "15";
                    }
                }

                if((float)$_POST["peso"] < .3){
                    $_POST["peso"] = "0.3";
                }
// $this->json(false);
                $this->json([$this->_calcular_frete(
                    $_POST["origem"],
                    $_POST["destino"],
                    $_POST["valor_declarado"],
                    $_POST["peso"],
                    $_POST["altura"],
                    $_POST["largura"],
                    $_POST["comprimento"],
                    "pac"
                ),$this->_calcular_frete(
                    $_POST["origem"],
                    $_POST["destino"],
                    $_POST["valor_declarado"],
                    $_POST["peso"],
                    $_POST["altura"],
                    $_POST["largura"],
                    $_POST["comprimento"],
                    "sedex"
                ),$this->_calcular_frete(
                    $_POST["origem"],
                    $_POST["destino"]
                )]);
            }
        }
	}
?>
