<?php
    trait admin_social {
        private function ajax_social($key="",$ext=true){
            if(empty($key)){
                exit("false");
            }
            try{
                header("Content-Type: application/json");
				if(is_string($key)){
                	parent::database()->set("social",$key,$_POST);
				} elseif(is_array($key)) {
					foreach($key as $index=>$subkey){
						parent::database()->set("social",$subkey,$_POST[$index]);
					}
				}
            } catch(Exception $e){
                exit("false");
            }
			// $this->json($_POST);
            if($ext){exit("true");}
        }

		function action_imgstatic($section = "global", String $folder="images"){
            $this->dropzoneUpload($folder, false, "not-resize");

			if(isset($_POST["imgs"])){
                $model = "";

                foreach($_POST["imgs"] as $img){
                    $model .= "
                    <div class='col-12 text-center'>
                        <input type=hidden class=img value='{$img}' />
                        <div class='col-12 img' style='background-image:url(/{$img});background-size: cover;'>
                            <br /><br /><br />
                        </div>
                        <div class='col-12 text-center'>
                            <button  class='apagar m-btn text-center m-btn--pill btn-outline-danger btn'>
                                <i class='la las la-trash'></i> Apagar
                            </button>
                        </div>
                    </div>";
                }

                exit("{$model}");
            }

            if($this->post()){
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

		function page_logotipo(UITemplate $content){
			$folder = "images";
			$sec    = ["logotipo-white","logotipo-dark"];

			$this->action_imgstatic($sec, $folder); // Modelo Multiplo

			echo $this->model_imgstatic($content, $sec, "logomarca"); // Caso seja um modelo inteiro
		}

		function page_capa(UITemplate $content){
			$pag = -1;

			switch(parent::url(1)){
				case "home": 	      $pag = "Pagina Inicial"; break;
				case "vela-virtual":  $pag = "Vela Virtual"; break;
				case "doacoes":       $pag = "Doações"; break;
				case "como-chegar":   $pag = "Como chegar"; break;
				case "fale-conosco":  $pag = "Fale Conosco"; break;
				case "oracoes":       $pag = "Doações"; break;
				case "fotos":         $pag = "Fotos"; break;
				case "videos":        $pag = "Videos"; break;
				case "missas":        $pag = "Missas"; break;
				case "novo-templo":   $pag = "Novo Templo"; break;
			}

			if($pag===-1){
				$this->page_main($content);
				exit;
			}

			$folder = "images";
			$sec    = parent::url(1);

			$this->action_imgstatic($sec, $folder); // Modelo Simples

			echo $this->model_imgstatic($content, $sec, "capas", array("pagina"=>"&nbsp;<i class='fa fa-chevron-right fa-1x'></i> &nbsp;{$pag}"));
		}

    }
