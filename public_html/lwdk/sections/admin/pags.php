<?php
	trait admin_pags {
		private function ajax_pags($id = "", $db = "paginas_fixas"){
			if(!isset($_POST["data"])){
				return null;
			}
            try{
                $id = empty($id)?parent::url(0):$id;
                $query = $this->database()->query($db, "name = {$id}");
                if(!count($query)){
                    $this->database()->push($db, array(array("data"=>$_POST["data"], "name" => "{$id}")));
                } else {
                    $this->database()->setWhere($db, "name = {$id}",array("data"=>$_POST["data"], "name" => "{$id}"));
                }
            } catch(Exception $e){
                $this->json("false");
            }
            $this->json("true");
        }

		private function ajax_imgs($template="", $folder="imgpagsfixas"){
			$this->dropzoneUpload($folder, false, "not-resize");

			if(isset($_POST["imgs"]) && !empty($template)){
                $model = "";

                foreach($_POST["imgs"] as $img){
                    $model .= $this->{"m_{$template}"}($img);
                }

                exit("{$model}");
            }
        }

		private function m_missas($img){
			return "<div class='col-6 text-center slide'>
				<label class='col-8'>Titulo:
					<input placeholder='Missa' class='form-control form-control-sm' type=text />
				</label>

				<label class='col-8'>Dia(s):
					<input placeholder='Sexta-feira' class='form-control form-control-sm' type=text />
				</label>

				<label class='col-8'>Horario(s):
					<input placeholder='08:00, 16:00, 21:00' class='form-control form-control-sm' type=text />
				</label>

				<div class='col-10 offset-2 img' data-img-url='${img}' style='background-image:url(/${img});background-size: 100%;'>
					<br /><br /><br />
					<input type=hidden value='${img}' />
				</div>
				<div class='col-12 text-center mb-4'>
					<button  class='apagar m-btn text-center m-btn--pill btn-outline-danger btn'>
						<i class='la las la-trash'></i> Apagar
					</button>
				</div>
			</div>";
		}

		function model_imgstatic_pages2($img,$two=false){
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
				<input type=\"hidden\" data-name=\"imagem{$id}\" data-img-url=\"{$img}\" class=\"img m-input\" value=\"{$img}\">
				<div class=\"col-12\" style=\"{$css[1]}\">
					<br><br><br>
				</div>
				<div class=\"col-12 text-center\">
					{$css[2]}
				</div>
			</div>";
		}

		private function m_fotos($img){
			return "<div class='col-4 text-center slide'>
				<label class='col-8'>Categoria:
					<input placeholder='Dia especial' class='form-control form-control-sm' type=text />
				</label>

				<label class='col-8'>Titulo:
					<input placeholder='Ordenação da Freira Ana' class='form-control form-control-sm' type=text />
				</label>

				<label class='col-8'>Data:
					<input style='text-transform: uppercase;' placeholder='DIA 13 DE MAIO 2021' class='form-control form-control-sm' type=text />
				</label>

				<div class='col-10 offset-2 img' data-img-url='${img}' style='background-image:url(/${img});background-size: 100%;'>
					<br /><br /><br />
					<input type=hidden value='${img}' />
				</div>
				<div class='col-12 text-center mb-4'>
					<button  class='apagar m-btn text-center m-btn--pill btn-outline-danger btn'>
						<i class='la las la-trash'></i> Apagar
					</button>
				</div>
			</div>";
		}

		private function m_oracoes($img){
			return $this->model_imgstatic_pages2($img);
		}

		function page_ajax_oracoes_get_img(){
			exit($this->m_oracoes($_POST["img"]));
		}

		function page_pag_missas(UITemplate $content){

			$pag = "missas";

			/* Form Submit */
			$this->ajax_imgs($pag); $this->post() && $this->ajax_pags($pag);

			$vars = array("TITLE" => "Pagina Missas");

			$query = $this->database()->query("paginas_fixas", "name = {$pag}");

			$vars["valuesof"] = count($query) < 1 ? "null":json_encode($query[0]);

			echo $this->simple_loader($content, "admin/pag_missas", $vars)->getCode();
		}

		function page_pag_fotos(UITemplate $content){
			$pag = "fotos";

			/* Form Submit */
			$this->ajax_imgs($pag); $this->post() && $this->ajax_pags($pag);

			$vars = array("TITLE" => "Pagina de Fotos");

			$query = $this->database()->query("paginas_fixas", "name = {$pag}");

			$vars["valuesof"] = count($query) < 1 ? "null":json_encode($query[0]);

			echo $this->simple_loader($content, "admin/pag_fotos", $vars)->getCode();
		}

		function page_pag_videos(UITemplate $content){
			$pag = "videos";

			/* Form Submit */
			$this->post() && $this->ajax_pags($pag);

			$vars = array("TITLE" => "Pagina de Videos");

			$query = $this->database()->query("paginas_fixas", "name = {$pag}");

			$vars["valuesof"] = count($query) < 1 ? "[]":json_encode($query[0]["data"]);

			echo $this->simple_loader($content, "admin/pag_videos", $vars)->getCode();
		}

		function page_pag_oracoes(UITemplate $content){
			$pag = "oracoes";

			/* Form Submit */
			$this->ajax_imgs($pag); $this->post() && $this->ajax_pags($pag);

			$vars = array("TITLE" => "Pagina de Orações");

			$query = $this->database()->query("paginas_fixas", "name = {$pag}");

			$vars["valuesof"] = count($query) < 1 ? "[]":json_encode($query[0]["data"]);

			echo $this->simple_loader($content, "admin/pag_oracoes", $vars)->getCode();
		}

		function page_pag_vela_virtual(UITemplate $content){
			if(
                parent::url(2) == "apagar" && (!empty(parent::url(1)) || (string)parent::url(1) == "0") &&
                count(parent::database()->query("velasacesas", "@ID = " . ($query = (string)parent::url(1)))) > 0
            ){
                exit(parent::database()->deleteWhere("velasacesas", "@ID = {$query}"));
            } elseif(parent::url(2) == "apagar"){
                echo parent::url(1);
                exit;
            }

			$velas = parent::database()->getAll("velasacesas");

			foreach(array_keys($velas) as $k){
				$velas[$k]["dias"] = $this->diff_dates("{$velas[$k]["@CREATED"][0][2]}-{$velas[$k]["@CREATED"][0][1]}-{$velas[$k]["@CREATED"][0][0]}", date("Y-m-d"));

				$velas[$k]["apagar"] = "<a onclick='Swal.fire({
									title: ``,
									html: `Voc&ecirc; deseja mesmo apagar essa vela?! <br>Essa a&ccedil;&atilde;o &eacute; irrevers&iacute;vel!`,
									icon: `warning`,
									showCancelButton: true,
									confirmButtonColor: `#3085d6`,
									cancelButtonColor: `#d33`,
									confirmButtonText: `Sim, apagar`,
									cancelButtonText: `Cancelar`,
								}).then((result) => {
									if (result.isConfirmed) {
										Swal.fire(
											``,
											`Vela apagada com sucesso!`,
											`success`
										).then((result) => {
											$.post(`/admin/pag_vela_virtual/{$velas[$k]["@ID"]}/apagar/`, function(){setTimeout(refresh,500);});
										});
									}
								});' href='javascript:;' class='btn btn-outline-danger m-btn'><i class='la la-trash'></i>&nbsp;DELETAR</a>";
				$velas[$k]["acendida"] = $velas[$k]["dias"] == 0
					? "Acesa Hoje ({$velas[$k]["@CREATED"][0][0]}/{$velas[$k]["@CREATED"][0][1]}/{$velas[$k]["@CREATED"][0][2]})"
					: ($velas[$k]["dias"] == 1
						? "Acesa Ontem ({$velas[$k]["@CREATED"][0][0]}/{$velas[$k]["@CREATED"][0][1]}/{$velas[$k]["@CREATED"][0][2]})"
						: "Acesa a {$velas[$k]["dias"]} dias ({$velas[$k]["@CREATED"][0][0]}/{$velas[$k]["@CREATED"][0][1]}/{$velas[$k]["@CREATED"][0][2]})"
					);
			}

			$btnTxt          = "";
			$keyword         = "";
			$db              = $velas;
			$titulos         = "Autor,Pedido,Quando foi acesa,ação";
			$dados           = "nome,texto,acendida,apagar";
			$keyid           = "@ID";
			$titulo          = "Velas Acendidas";

			$content = $this->_tablepage($content,$keyword,$titulos,$dados,$keyid,$titulo,$db,$btnTxt,"not",false);

			echo $content->getCode();
		}
	}
?>
