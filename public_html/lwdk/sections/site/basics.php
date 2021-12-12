<?php
	/**
	 * Paginas e funcoes basicas do sistema
	 */
	trait basics {
		function pmsg(UITemplate $content, String $title, String $text){
			return $this->simple_loader(
				$content, "site/textos-simples", array("TITLE" => "{$title}", "TEXT" => "{$text}")
			)->getCode();
		}

		function page_ajax_carrinho(){
			$UserData = parent::control("interactive/userdata");
			$act = $_REQUEST["-act-"];
	        if(isset($_REQUEST["-act-"])){
	            $UserData->Timeout("+3 Day");
	            switch($act){
	                case "s-cart":
	                    $UserData->Set("carrinho", isset($_POST["data"])?$_POST["data"]:"{}");
	                    exit;
	                break;
	                case "l-cart":
	                    $this->json($UserData->Get("carrinho"));
	                break;
	            }
	        }
		}

		function page_ajax_variacoes(){
			$UserData = parent::control("interactive/userdata");
			$act = $_REQUEST["-act-"];
	        if(isset($_REQUEST["-act-"])){
	            $UserData->Timeout("+3 Day");
	            switch($act){
	                case "s-cart":
	                    $UserData->Set("variacoes", isset($_POST["data"])?$_POST["data"]:"{}");
	                    exit;
	                break;
	                case "l-cart":
	                    $this->json($UserData->Get("variacoes"));
	                break;
	            }
	        }
		}

		function notfound(
			UITemplate $content,
			String $title="Página indisponível!",
			String $text="Esta pagina ou produto foi removido ou não está disponível no momento.<br>
						  Por favor, tente novamente mais tarde."
		){
			if(!$this->flypages($content)){
				exit($this->pmsg($content, $title, "<h2>{$text}</h2>"));
			}
		}

		function flypages(UITemplate $content){
			switch(parent::url(0)){
				case "aluguel":
					echo $this->fixed_pages($content, "Aluguel de Equipamentos", parent::url(0));
				break;
				case "politica-de-privacidade":
					echo $this->fixed_pages($content, "Politica de Privacidade", parent::url(0));
				break;
				case "trocas-e-devolucoes":
					echo $this->fixed_pages($content, "Trocas e Devoluções", parent::url(0));
				break;
				case "garantia-dos-produtos":
					echo $this->fixed_pages($content, "Garantia dos Produtos", parent::url(0));
				break;
				case "compra":
					echo $this->fixed_pages2($content, "Compra de Usados", parent::url(0));
				break;
				case "reforma":
					echo $this->fixed_pages2($content, "Reforma de Equipamentos", parent::url(0));
				break;
				case "a-empresa":
					echo $this->fixed_pages2($content, "A Empresa", parent::url(0));
				break;
				default:
					// $this->dbg($this->get_subcat($subcat = parent::url(2)));
					if(($cname = $this->get_cat($cat = parent::url(0))) !== false){
						$query = "categoria = {$cat}";
						$title = strtoupper($cname);
						if(is_array(($csname = $this->get_subcat($subcat = parent::url(2)))) && !(empty($subcat) && (string)$subcat !== "0")){
							$query .= " and subcategoria = {$subcat}";
							$title .= " / " . strtoupper($csname["txt"]);
						}

						// $this->dbg($query);

						$prods = $this->modelo_minhatura_produtos($content, $query, 500, "product mb-0", '<div class="col-12 col-sm-6 col-lg-3 mb-5">','</div>');

						$title = ucfirst(mb_strtolower($title));

						echo $this->simple_loader($content, "site/categoria", array(
							"TITLE" => "{$title}",
							"produtos" => empty($prods) ? '<section class="border-0 pt-4 m-4">
								<div class="container shop pb-4">
									<div class="row align-items-center">
										<div class="col-md-12">
											<div class="overflow-hidden mb-2">
											</div>
											<h3 class="appear-animation" data-appear-animation="fadeInUpShorter" data-appear-animation-delay="1400">
												Desculpe, mas ainda n&atilde;o h&aacute; produtos cadastrados nesta se&ccedil;&atilde;o.
												<br class="clear" />
												<p style="float: right; text-align: right;">Volte mais tarde.</p>
											</h3>
										</div>
									</div>
								</div>
							</section>':$prods
						))->getCode();
					} else {
						return false;
					}
				break;
			}
			return true;
		}

		function page_home(UITemplate $content){
			echo $this->simple_loader($content, "site/home", array(
				"TITLE" => "Pagina Inicial",
				"promocoes" => $this->modelo_minhatura_produtos($content, "promocao = true"),
				"lancamentos" => $this->modelo_minhatura_produtos($content, "lancamento = true")
			))->getCode();
		}

		private function fixed_pages(UITemplate $content, $title, $db){
			return $this->simple_loader($content, "site/textos-simples", array("TITLE" => $title, "TEXT" => !empty($page=parent::database()->get("paginas_fixas")) && isset($page[$db]) && !empty($page[$db]) ? $page[$db]["data"]:"<h1>Pagina em constru&ccedil;&atilde;o!</h1>"))->getCode();
		}

		private function fixed_pages2(UITemplate $content, $title, $db){
			if(!empty($page=parent::database()->get("social")) && isset($page[$db]) && !empty($page[$db])){
				return $this->simple_loader($content, "site/textos-imagem-do-lado", array("TITLE" => $title, "TEXT" => $page[$db]["content"], "IMG" => $page[$db]["img"]))->getCode();
			} else {
				return $this->simple_loader($content, "site/textos-simples", array("TITLE" => $title, "TEXT" => "<h1>Pagina em constru&ccedil;&atilde;o!</h1>"))->getCode();
			}
		}
	}

?>
