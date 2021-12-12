<?php
    trait admin_paginas {
		function model_imgstatic_pages($img,$two=false){
			$id = isset($_GET["capa"]) || $two ? "2":"";
			$css = $id === "2"
				? ["position: absolute; background-image:url({$img}); background-size: 100% auto; background-repeat: no-repeat; background-position: center center; height: 240px; top: 5px;width: 96%;left: 1.5%;","height: 95%;",
				"<button class=\"apagar m-btn btn-danger text-center btn px-2 py-2 \" style='position: absolute; bottom: 0; right: 0;'>
					<i class=\"las la-trash\"></i> Deletar
				</button>"]
				: ["position: absolute;
					top: 0;
					left: 0;
					margin: 0;
					padding: 0;
					height: 100%;",
					"background-image:url({$img}); background-size: 100% auto; background-repeat: no-repeat; background-position: center center; height: 100%;",
				"<button class=\"apagar btn-danger m-btn text-center btn px-2 py-2 \" style='position: absolute; bottom: 8px; right: 8px;'>
					<i class=\"las la-trash\"></i> Deletar
				</button>"];

			return "<div class=\"col-12 text-center img-group\" style=\"
							$css[0]
						\">
				<input type=\"hidden\" data-name=\"imagem{$id}\" data-img-url=\"{$img}\" class=\"img\" value=\"{$img}\">
				<div class=\"col-12\" style=\"{$css[1]}\">
					<br><br><br>
				</div>
				<div class=\"col-12 text-center\">
					{$css[2]}
				</div>
			</div>";
		}

		function action_imgstatic_pages($section = "global", String $folder="images"){
			$this->dropzoneUpload($folder, false, "not-resize");

			if(isset($_POST["imgs"])){
                $model = "";

                foreach($_POST["imgs"] as $img){
                    $model .= $this->model_imgstatic_pages($img);
                }

                exit("{$model}");
            }
        }

        private function ajax_paginas($id=""){
            try{
				if(isset($_POST["cadprod"])){

                } else {
                    $query = $this->database()->query("config", "name = {$id}");
                    if(!count($query)){
                        $this->database()->push("config",array(array("content"=>$_POST["data"], "name" => "{$id}")));
                    } else {
                        $this->database()->setWhere("config","name = {$id}",array("content"=>$_POST["data"], "name" => "{$id}"));
                    }
                }
            } catch(Exception $e){
                $this->json(false);
            }
            $this->json(true);
        }

		function page_ajax_paginas(){
			$id = $_POST["id"];
			$_POST["tp"] = "prod";
			$query = $this->database()->query("paginas", "id = {$id}");

			if(!count($query)){
				$this->json($this->database()->push("paginas",array($_POST)) !== false);
			} else {
				$this->json($this->database()->setWhere("paginas","id = {$id}",$_POST) !== false);
			}

            if($this->post()){
				$this->ajax_social($section);
			}
			exit("false");
		}

		function urlPages($page){
			$slug = $this->slug(strip_tags($page["sub-titulo"]) . " " . strip_tags($page["titulo"]));
			$slug = "/{$page["id"]}/{$slug}/";
			return $slug;
		}

        function page_pagina($content,$listar=false){
            $content->minify = true;
            $db = "paginas";

            if(
                parent::url(2) == "apagar" && (!empty(parent::url(1)) || (string)parent::url(1) == "0") &&
                count(parent::database()->query($db, "tp=prod and id = " . ($query = (string)parent::url(1)))) > 0
            ){
                exit(parent::database()->deleteWhere($db, "tp=prod and id = {$query}"));
            } elseif(parent::url(2) == "apagar"){
                echo parent::url(1);
                exit;
            }

			$folder = "imgpags";
			$sec    = parent::url(1);

			$this->action_imgstatic_pages($sec, $folder); // Modelo Simples

            if($this->post())return $this->ajax_paginas();

            $id = parent::database()->newID($db,"tp = prod");

            $vars = array(
                "acao" => "cadastrada",
                "id" => $id,
                "botao-txt" => "Adicionar página",
                "TITLE" => "Criar uma nova página",
                "mdl" => "sk1",
				"nome" => "",
                "ativo" => false,
                "imagem" => "",
                "imagem2" => "",
				"titulo" => "",
				"sub-titulo" => "",
				"conteudo" => ""
            );

            if(!empty(parent::url(1)) || (string)parent::url(1) == "0" || $listar){
                if(count($query = parent::database()->query($db, "tp = prod and id = " . (string)parent::url(1))) > 0){
                    $vars["TITLE"] = "Modificar página";
                    $vars["botao-txt"] = "Salvar modificações";

                    $vars["acao"] = "modificada";

                    foreach($query[0] as $id=>$val){
                        $vars[$id] = is_array($val) ? json_encode($val):$val;
                    }

					$vars["imagem"] = $this->model_imgstatic_pages($vars["imagem"]);

					$vars["imagem2"] = $this->model_imgstatic_pages($vars["imagem2"], true);

                    unset($vars[0]);

                } elseif(parent::url(1) == "listar" || $listar){
					$paginas = parent::database()->query($db, "id > -1");

					foreach(array_keys($paginas) as $k){
						$c = $paginas[$k]["@CREATED"];
						$m = isset($paginas[$k]["@MODIFIED"])?$paginas[$k]["@MODIFIED"]:false;

						$paginas[$k]["criada"] = "{$c[0][0]}/{$c[0][1]}/{$c[0][2]} às {$c[1][0]}:{$c[1][1]}";
						$paginas[$k]["modificada"] = $m === false ? "Nunca foi alterada":"{$m[0][0]}/{$m[0][1]}/{$m[0][2]} às {$m[1][0]}:{$m[1][1]}";
						$url = $this->urlPages($paginas[$k]);
						$paginas[$k]["url"] = "<a class='btn m-btn btn-info' href='{mydomain}{$url}' target=_blank><i class='la la-external-link'></i> ABRIR</a>";
					}

                    $btnTxt          = "Página";
                    $keyword         = "pagina";
                    $db              = $paginas;
                    $titulos         = "Título,Criada Em,Ultima Modificação,Visualizar P&aacute;gina";
                    $dados           = "nome,criada,modificada,url";
                    $keyid           = "id";
                    $titulo          = "Gerir páginas cadastradas";

                    exit($this->_tablepage($content,$keyword,$titulos,$dados,$keyid,$titulo,$db,$btnTxt)->getCode());
                }
            }

            $content = $this->simple_loader($content, "admin/pagina", $vars);

            echo $content->getCode();
        }

        function page_config_menu($content){
            if($this->post())return $this->ajax_paginas("menu");

            $ordens = "";

            $opcoes = array(
				"Home" => "/",
				"Como Chegar" => "/como_chegar/",
                "Fale Conosco" => "/fale_conosco/",
				"Doações" => "/doacoes/",
				"Missas" => "/missas/",
				"Fotos" => "/fotos/",
				"Videos" => "/videos/",
				"Rádio" => "https://radiosaomiguel.com/",
				"Novo Templo" => "/novo_templo/",
				"Orações" => "/oracoes/",
				"Vela Virtual" => "/vela_virtual/"
            );

			$paginas = parent::database()->query("paginas", "tp=prod and ativo=true");

			foreach(array_keys($paginas) as $k){
				$url = $this->urlPages($paginas[$k]);
				$opcoes[$paginas[$k]["nome"]] = $url;
			}

            $opcoes_html = "";

			ksort($opcoes);

            foreach($opcoes as $titulo=>$url){
                $opcoes_html .= "<option value='{$url}'>{$titulo}</option>";
            }

            for($i = 1; $i < 50; $i++){
                $i = $i < 10 ? "0{$i}":(string)$i;
                $ordens .= "<option value='{$i}'>{$i}</option>";
            }

            $query = parent::database()->query("config", "name = menu",array("content"));

            if(count($query) < 1){
                $query = [];
            } else {
                $query = $query[0]["content"];
            }

            echo $this->simple_loader($content, "admin/config-menu", array(
                "TITLE" => "Configurar menu do site",
                "ordens" => $ordens,
                "opcoes_link" => $opcoes_html,
                "menu_data" => json_encode($query)
            ), array("t_opcao"=>"admin/opcao_menuconf"))->getCode();
        }
    }
