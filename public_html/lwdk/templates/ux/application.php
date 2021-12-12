<?php
    class application extends APPObject {
		use
            function_group_dates,
			function_geo_info,
            basics,
			site_contas,
			helper_produtos,
			site_produtos,
			moduloPagSeguro;

		public $PagSeguro;

        function __construct(){
            # CONFIGURATIONS #
            $this->rootDir("/");
            $this->uiTemplateDefault("site/index");
            header("Content-Type: text/html;charset=utf-8");
            $this->empresa = "Amparar equipamentos médicos-ortopédicos";
			$this->mydomain = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER["HTTP_HOST"]}";
        }

		function apply__menu(UITemplate $content){
            $query = parent::database()->query("produtos", "name = menu",array("content"));

            if(count($query) < 1){
                $query = [];
            } else {
                $query = $query[0]["content"];
            }

            $menu = "";

            $submenu = function(Array $submenu){
				$submenu["texto"] = ucfirst(mb_strtolower($submenu["texto"]));
                return "<div class=\"col-lg-3\">
						    <span class=\"dropdown-mega-sub-title\"></span>
						    <ul class=\"dropdown-mega-sub-nav\">
						        <li><a class=\"dropdown-item text-center\" href=\"{$submenu["link"]}\">{$submenu["texto"]}</a></li>
						    </ul>
						</div>";
            };

            foreach($query as $opcao){
				// $opcao["texto"] = mb_strtolower($opcao["texto"]);
                $menu .= ($sub_en=(isset($opcao["submenu"]) && count($opcao["submenu"]) > 0)) ? "<li class=\"dropdown dropdown-mega\">":"<li>";

                $menu .= $sub_en ? "<a class=\"dropdown-item dropdown-toggle\" href=\"{$opcao["link"]}\">{$opcao["texto"]}</a>":"<a class=\"dropdown-item\" href=\"{$opcao["link"]}\">{$opcao["texto"]}</a>";

                if($sub_en){
                    $menu .= "<ul class=\"dropdown-menu\"><li><div class=\"dropdown-mega-content\"><div class=\"row\">";

                    foreach($opcao["submenu"] as $sub){
                        $menu .= $submenu($sub);
                    }

                    $menu .= "</div></div></li></ul>";
                }

				$menu .= "</li>";
            }

            // exit(print_r($menu,true));

            return $content->applyVars(array("menu_loja"=>$menu));
        }

		function _template_($content){
			$this->calcular_frete();

			$this->apply__menu($content);

			$content->applyVars(array("empresa" => $this->empresa, "termo" => ""));

            $social = $this->database()->get("social");

			// $this->dbg($social);

			if(isset($social["contatos"])){
				$content->applyVars($social["contatos"]);
				$this->PagSeguro = parent::control("connect/pagseguro");
	            $this->PagSeguro->sys->token_oficial = $social["contatos"]["token-pag-seguro"];
	            $this->PagSeguro->sys->email = $social["contatos"]["email-pag-seguro"];
				unset($social["contatos"]);
			}


			$keyapply = parent::url(0);

			if(!empty($keyapply) && $keyapply !== null && $keyapply !== false && isset($social[$keyapply])){
				foreach($social[$keyapply] as $kapply=>$apply){
					$content->applyVars(array("{$kapply}" => $apply));
				}
				unset($social[$keyapply]);
			}

			foreach($social as $k=>$apply_social){
				if(is_array($apply_social) and isset($apply_social["data"])){
					$content->applyVars(array("{$k}" => $apply_social["data"]));
				} elseif(is_string($apply_social)) {
					$content->applyVars(array("{$k}" => $apply_social));
				}
			}

			$content->applyModels(array("perfil-ou-login" => $this->sessao() !== false ? "site/perfil-botao":"site/login-botao"));

			if($this->sessao()!==false){
				foreach((array)$this->sessao() as $key=>$val){
					$content->applyVars(array("cli_{$key}" => is_array($val)?json_encode($val):$val));
				}

			}

            return $content;
        }

		function formatar_cpf_cnpj($doc) {

            $doc = preg_replace("/[^0-9]/", "", $doc);
            $qtd = strlen($doc);

            if($qtd >= 11) {

                if($qtd === 11 ) {

                    $docFormatado = substr($doc, 0, 3) . '.' .
                                    substr($doc, 3, 3) . '.' .
                                    substr($doc, 6, 3) . '-' .
                                    substr($doc, 9, 2);
                } else {
                    $docFormatado = substr($doc, 0, 2) . '.' .
                                    substr($doc, 2, 3) . '.' .
                                    substr($doc, 5, 3) . '/' .
                                    substr($doc, 8, 4) . '-' .
                                    substr($doc, -2);
                }

                return $docFormatado;

            } else {
                return false;
            }
        }

		function request() {
			return $_REQUEST;
		}

        function page_main(UITemplate $content){
            if(count($this->url()) < 2){
				$this->page_home($content);
			} else {
				$this->notfound($content, "Conteúdo não localizado!", "<b style='float: left;'>A página que você está procurando não existe...</b><a  style='float: right;' href='/'>Ir para página inicial</a>");
			}
        }
	}
?>
