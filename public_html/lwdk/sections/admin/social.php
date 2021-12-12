<?php
    trait admin_social {
        private function ajax_social($key="",$ext=true,$db="social"){
            if(empty($key)){
                exit("false");
            }
            try{
                header("Content-Type: application/json");
				if(is_string($key)){
                	parent::database()->set("$db",$key,$_POST);
				} elseif(is_array($key)) {
					foreach($key as $index=>$subkey){
						parent::database()->set("$db",$subkey,$_POST[$index]);
					}
				}
            } catch(Exception $e){
                exit("false");
            }
			// $this->json($_POST);
            if($ext){exit("true");}
        }

		function action_imgstatic($section = "global", String $folder="images", $mdl = 0,$use_form=true){
            $this->dropzoneUpload($folder, false, "not-resize");

			if(isset($_POST["imgs"])){
                $model = "";

                foreach($_POST["imgs"] as $img){
                    switch($mdl){
						case 0:
							$model .= "
		                    <div class='col-12 text-center'>
		                        <input type=hidden class=img data-img-url='${img}' value='{$img}' />
		                        <div class='col-12 img' data-img-url='${img}' style='background-image:url(/{$img});background-size: 90%;'>
		                            <br /><br /><br />
		                        </div>
		                        <div class='col-12 text-center'>
		                            <button  class='apagar m-btn text-center m-btn--pill btn-outline-danger btn'>
		                                <i class='la las la-trash'></i> Apagar
		                            </button>
		                        </div>
		                    </div>";
						break;

						case 1:
							$model .= "
							<div class='col-12 text-center'>
				                <input type=hidden data-img-url='${img}' class=img value='${img}' />
								<div class='col-12 img p-0 m-0' data-img-url='${img}' style='background-image:url(/${img});background-size: 100%;position: absolute;top: -170px;left: 34px;width: 408px;border: 0;box-shadow: 0px 0px 1px 4px inset;background-color: #aaa!important;'>
				                            <br><br><br>
				                        </div>
								<div class='col-12 text-center mt-4'>
				                    <button class='apagar m-btn text-center m-btn--pill btn-danger btn'>
				                        <i class='la las la-trash'></i> Apagar
				                    </button>
				                </div>
				            </div>";
						break;

						case 2:
							$model = $img;
						break;
					}
                }

                exit("{$model}");
            }

            if($this->post() && $use_form){
				$this->ajax_social($section);
			}
        }

		function model_imgstatic(UITemplate $content, $section="global", $layout="logo", $vars=[], $partial = false){
            $content->minify = true;
			$sec = [];
			if(is_array($section)){
				foreach($section as $_sec){
					$sec[] = $this->database()->get("social",$_sec);
				}
			} else {
				$sec = $this->database()->get("social",$section);
			}
            $content = $this->simple_loader($content, "admin/{$layout}", array_merge($vars, array(
                "valuesof" => json_encode($sec)
            )));

            return $content->getCode($partial);
        }

		function page_contatos($content){
            $content->minify = true;

            $section = "contatos";
            $title   = "Contatos";

            if($this->post())return $this->ajax_social($section);

            $content = $this->simple_loader($content, "admin/contatos", array(
                "TITLE"=>$title,
                "valuesof" => json_encode($this->database()->get("social",$section))
            ));

            echo $content->getCode();
        }

		function page_textos($content){
            $content->minify = true;

            $section = $this->url(1);
            $title   = "";

			switch($this->url(1)){
				case "aluguel":
					$title = "Aluguel de Equipamentos";
				break;
				case "politica-de-privacidade":
					$title = "Politica de Privacidade";
				break;
				case "trocas-e-devolucoes":
					$title = "Trocas e Devoluções";
				break;
				case "garantia-dos-produtos":
					$title = "Garantia dos Produtos";
				break;
			}

			if(empty($title)){
				exit($this->page_main($content));
			}

            if($this->post())return $this->ajax_social($section, true, "paginas_fixas");

			$value = $this->database()->get("paginas_fixas",$section);

            $content = $this->simple_loader($content, "admin/textos", array(
                "TITLE"=>$title,
                "valuesof" => json_encode($value)
            ));

            echo $content->getCode();
        }

		// function page_logotipo(UITemplate $content){
		// 	$folder = "uploads/logo";
		// 	$sec    = ["logotipo-topo","logotipo-rodape"];
		//
		// 	$this->action_imgstatic($sec, $folder); // Modelo Multiplo
		//
		// 	echo $this->model_imgstatic($content, $sec, "logomarca"); // Caso seja um modelo inteiro
		// }

		function page_logotipo($content){
            $content->minify = true;

            $section = "logotipo";

            $this->dropzoneUpload("images", false, "not-resize");

			if(isset($_POST["act"]) && $_POST["act"] == "erase"){
				parent::database()->set("social",$section,array("data"=>""));
			}

            if(isset($_POST["imgs"])){
                $model = "";

                foreach($_POST["imgs"] as $img){
					if(file_exists($fl=(__paths::get()->www . "/{$img}"))){
	                    $model .= "
	                    <div class='col-12 text-center'>
	                        <input type=hidden id=img value='{$img}' />
	                        <div class='col-12 img' style='background-image:url(/{$img})'>
	                            <br /><br /><br />
	                        </div>
	                        <div class='col-12 text-center'>
	                            <button  class='apagar m-btn text-center m-btn--pill btn-outline-danger btn'>
	                                <i class='la las la-trash'></i> Apagar
	                            </button>
	                        </div>
	                    </div>";
					} else {
						exit($fl);
					}
                }

                exit(empty($model) ? "not-found":"{$model}");
            }

            if($this->post())return $this->ajax_social($section, true);

            $content = $this->simple_loader($content, "admin/logo", ($arr=array(
                "valuesof" => json_encode($this->database()->get("social",$section))
            )));

            echo $content->getCode();
        }

		// function page_capa(UITemplate $content){
		// 	$pag = -1;
		//
		// 	switch(parent::url(1)){
		// 		case "home": 	      $pag = "Pagina Inicial"; break;
		// 		case "vela-virtual":  $pag = "Vela Virtual"; break;
		// 		case "doacoes":       $pag = "Doações"; break;
		// 		case "como-chegar":   $pag = "Como chegar"; break;
		// 		case "fale-conosco":  $pag = "Fale Conosco"; break;
		// 		case "oracoes":       $pag = "Doações"; break;
		// 		case "fotos":         $pag = "Fotos"; break;
		// 		case "videos":        $pag = "Videos"; break;
		// 		case "missas":        $pag = "Missas"; break;
		// 		case "novo-templo":   $pag = "Novo Templo"; break;
		// 	}
		//
		// 	if($pag===-1){
		// 		$this->page_main($content);
		// 		exit;
		// 	}
		//
		// 	$folder = "images";
		// 	$sec    = parent::url(1);
		//
		// 	$this->action_imgstatic($sec, $folder); // Modelo Simples
		//
		// 	echo $this->model_imgstatic($content, $sec, "capas", array("pagina"=>"&nbsp;<i class='fa fa-chevron-right fa-1x'></i> &nbsp;{$pag}"));
		// }

		function page_conteudo(UITemplate $content){
			$pag = -1;

			switch(parent::url(1)){
				case "compra":
					$pag = "Compra de Usados";
				break;
				case "reforma":
					$pag = "Reforma de Equipamentos";
				break;
				case "a-empresa":
					$pag = "A Empresa";
				break;
			}

			// exit($pag);

			if($pag===-1){
				$this->page_main($content);
				exit;
			}

			$folder = "uploads/paginas/estaticas";
			$sec    = parent::url(1);

			$this->action_imgstatic($sec, $folder, 1); // Modelo Simples

			echo $this->model_imgstatic($content, $sec, "conteudos-fixos", array("pagina"=>"&nbsp;<i class='fa fa-chevron-right fa-1x'></i> &nbsp;{$pag}"));
		}

		function page_conteudo2(UITemplate $content){
			$pag = -1;

			switch(parent::url(1)){
				case "compra":
					$pag = "Compra de Usados";
				break;
				case "reforma":
					$pag = "Reforma de Equipamentos";
				break;
				case "a-empresa":
					$pag = "A Empresa";
				break;
			}

			if($pag===-1){
				$this->page_main($content);
				exit;
			}

			$folder = "uploads/paginas/dinamicas/" . parent::url(1);
			$sec    = parent::url(2);

			$this->action_imgstatic($sec, $folder, (int)parent::url(3), false);

			if($this->post()){
	            header("Content-Type: application/json");

				try{
					parent::database()->set("paginas-dinamicas",parent::url(1),$_POST["data"]);
	            } catch(Exception $e){
	                exit("false");
	            }

	            exit("true");
			}

			$data = parent::database()->get("paginas-dinamicas");

			$data = array_values(isset($data[parent::url(1)]) ? $data[parent::url(1)] : [-1,-1,-1,"",""]);

			$vars["TITLE"] = $pag;

			$vars["pagina"] = " | " . $pag;

			$vars["txt1"] = $data[3];

			$vars["txt2"] = $data[4];

			$vars["txt3"] = $data[5];

			$vars["txt4"] = $data[6];

			$vars["txt5"] = $data[7];

			echo $this->simple_loader($content, "admin/conteudos-fixos-3img", array_merge($vars, array(
                "valuesof" => json_encode($data)
            )))->getCode();
		}
    }
