<?php
    class APPObject {
        private $dir = null;
        private $parent = null;
        public  $uiTemplateObject;
        public  $defaultPage = "main";
        public  $defaultUiTemplate = null;
        public  $uiTemplate = false;
        public  $defaultVars = array();

        function type($set="text/plain"){
            header("Content-Type: {$set}");
        }

        function json($data){
            $this->type("application/json");
            exit(json_encode($data));
        }

		function dbg($v){
			echo "<pre>";
			var_dump($v);
			exit;
		}

		function pday(){ // 1 = Manha | 2 = Tarde | 3 = Noite
			$hora = date('H');if( $hora >= 6 && $hora <= 12 )return 0;else if ( $hora > 12 && $hora <= 18  )return 1;else return 2;
		}

		function distance_coord($lat1, $lon1, $lat2, $lon2) {
			$lat1 = deg2rad($lat1);
			$lat2 = deg2rad($lat2);
			$lon1 = deg2rad($lon1);
			$lon2 = deg2rad($lon2);

			$dist = (6371 * acos( cos( $lat1 ) * cos( $lat2 ) * cos( $lon2 - $lon1 ) + sin( $lat1 ) * sin($lat2) ) );
			$dist = number_format($dist, 2, '.', '');
			return $dist;
		}

		function ufcpf($cpf){
			$tabela = [];
			$tabela[0] = ["RS"]; // Rio Grande do Sul
			$tabela[1] = ["DF","GO","MT","MS","TO"]; // Distrito Federal, Goiás, Mato Grosso, Mato Grosso do Sul e Tocantins
			$tabela[2] = ["AM", "PA", "RR", "AP", "AC", "RO"]; // Amazonas, Pará, Roraima, Amapá, Acre e Rondônia
			$tabela[3] = ["CE", "MA", "PI"]; // Ceará, Maranhão e Piauí
			$tabela[4] = ["PB", "PE", "AL", "RN"]; // Paraíba, Pernambuco, Alagoas e Rio Grande do Norte
			$tabela[5] = ["BA", "SE"]; // Bahia e Sergipe
			$tabela[6] = ["MG"]; // Minas Gerais
			$tabela[7] = ["RJ", "ES"]; // Rio de Janeiro e Espírito Santo
			$tabela[8] = ["SP"]; // São Paulo
			$tabela[9] = ["PR", "SC"]; // Paraná e Santa Catarina

			$cpf = preg_replace("/[^0-9]/", "", $cpf);



		}

		function compare_strings($a, $b){
			$str = array(
				preg_replace(array("/(á|à|ã|â|ä)/","/(Á|À|Ã|Â|Ä)/","/(é|è|ê|ë)/","/(É|È|Ê|Ë)/","/(í|ì|î|ï)/","/(Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö)/","/(Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü)/","/(Ú|Ù|Û|Ü)/","/(ñ)/","/(Ñ)/","/(ç)/","/(Ç)/"),explode(" ","a A e E i I o O u U n N c C"),$a),
				preg_replace(array("/(á|à|ã|â|ä)/","/(Á|À|Ã|Â|Ä)/","/(é|è|ê|ë)/","/(É|È|Ê|Ë)/","/(í|ì|î|ï)/","/(Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö)/","/(Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü)/","/(Ú|Ù|Û|Ü)/","/(ñ)/","/(Ñ)/","/(ç)/","/(Ç)/"),explode(" ","a A e E i I o O u U n N c C"),$b)
			);

        	return ~~((similar_text(preg_replace("/[^0-9a-z]/", "", strtolower($str[0])),preg_replace("/[^0-9a-z]/", "", strtolower($str[1]))) / max(strlen($str[0]),strlen($str[1]),1)) * 100);
		}

        function dropzoneUpload(String $storeFolder = 'uploads',$notimg=false, $w=1024, $h=1024, $__op__='maxwidth', $qlt=40){
            $ds = DIRECTORY_SEPARATOR;
            $targetPath = (new __paths)->get()->www . $ds. $storeFolder . $ds;  //4
            if (!file_exists($targetPath)) {
        		mkdir($targetPath, 0777, true);
        	}

            if (!empty($_FILES)) {
                header("Content-Type: application/json");
                $files = array();
				if(!is_array($_FILES['file']['tmp_name'])){
					foreach(array_keys($_FILES['file']) as $i){
						$_FILES['file'][$i] = array($_FILES['file'][$i]);
					}
				}
				// $this->json($_FILES);
                foreach(array_keys($_FILES['file']['tmp_name']) as $i){
                    $ext = explode(".", $_FILES['file']['name'][$i]);
                    $ext = end($ext);

                    if(!$notimg){
                        do {
                            $name_img = md5(uniqid());
                        } while (file_exists($storeFolder . $ds . $name_img . "." . $ext));
                    } else {
                        $name_img = $_FILES['file']['name'][$i];
                    }

                    if(!$notimg){
                        if($w === "not-resize"){
                            $targetFile =  $targetPath . $name_img . "." . $ext;  //5
                            $tempFile = $_FILES['file']['tmp_name'][$i];
                            move_uploaded_file($tempFile,$targetFile);

                            $files[] = $storeFolder . $ds . $name_img . "." . $ext;
                        } else {
                            $targetFile =  $targetPath . $name_img . "." . $ext;  //5
                            $tempFile = $_FILES['file']['tmp_name'][$i];
                            $targetFileResize =  $targetPath . $name_img . "r." . $ext;  //5
                            if(move_uploaded_file($tempFile,$targetFileResize)){
                                $files[] = $storeFolder . $ds . $name_img . "." . $ext;

                                $resize = new ResizeImage($targetFileResize);
                                $resize->resizeTo($w, $h, $__op__);
                                $resize->saveImage($targetFile, 35);

                                unlink($targetFileResize);
                            }
                        }
                    } else {
                        $tempFile = $_FILES['file']['tmp_name'][$i];
                        $targetFile =  $targetPath . $name_img;
                        move_uploaded_file($tempFile,$targetFile);
                        $files[] = $targetFile;
                    }
                }
                exit(json_encode($files));
            }


			if(isset($_POST["act"]) && $_POST["act"] == "erase"){
				unlink($f=((new __paths)->get()->www . $ds . $_POST["file"]));
				exit("File: {$f}");
			}
		}

        function slug($url){
            $p = str_split(preg_replace("/[^0-9a-z\-]/", "-", strtolower(preg_replace(array("/(á|à|ã|â|ä)/","/(Á|À|Ã|Â|Ä)/","/(é|è|ê|ë)/","/(É|È|Ê|Ë)/","/(í|ì|î|ï)/","/(Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö)/","/(Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü)/","/(Ú|Ù|Û|Ü)/","/(ñ)/","/(Ñ)/","/(ç)/","/(Ç)/"),explode(" ","a A e E i I o O u U n N c C"),$url))));

            return implode(array_map(function ($c) use ($p) {
                return ($c > 0 && $p[$c] == $p[$c - 1] ? '': $p[$c]);
            }, array_keys($p)));
        }

        function parseData($type, $data){
            $fns = array(
                function($cnpj) {
                    // Deixa o CNPJ com apenas números
                    $cnpj = preg_replace('/[^0-9]/', '', $cnpj);

                    // Garante que o CNPJ é uma string
                    $cnpj = (string) $cnpj;

                    // O valor original
                    $cnpj_original = $cnpj;

                    // Captura os primeiros 12 números do CNPJ
                    $primeiros_numeros_cnpj = substr($cnpj, 0, 12);

                    /**
                     * Multiplicação do CNPJ
                     *
                     * @param string $cnpj Os digitos do CNPJ
                     * @param int $posicoes A posição que vai iniciar a regressão
                     * @return int O
                     *
                     */
                    if (!function_exists('multiplica_cnpj')) {

                        function multiplica_cnpj($cnpj, $posicao = 5) {
                            // Variável para o cálculo
                            $calculo = 0;

                            // Laço para percorrer os item do cnpj
                            for ($i = 0; $i < strlen($cnpj); $i++) {
                                // Cálculo mais posição do CNPJ * a posição
                                $calculo = $calculo + ( $cnpj[$i] * $posicao );

                                // Decrementa a posição a cada volta do laço
                                $posicao--;

                                // Se a posição for menor que 2, ela se torna 9
                                if ($posicao < 2) {
                                    $posicao = 9;
                                }
                            }
                            // Retorna o cálculo
                            return $calculo;
                        }

                    }

                    // Faz o primeiro cálculo
                    $primeiro_calculo = multiplica_cnpj($primeiros_numeros_cnpj);

                    // Se o resto da divisão entre o primeiro cálculo e 11 for menor que 2, o primeiro
                    // Dígito é zero (0), caso contrário é 11 - o resto da divisão entre o cálculo e 11
                    $primeiro_digito = ( $primeiro_calculo % 11 ) < 2 ? 0 : 11 - ( $primeiro_calculo % 11 );

                    // Concatena o primeiro dígito nos 12 primeiros números do CNPJ
                    // Agora temos 13 números aqui
                    $primeiros_numeros_cnpj .= $primeiro_digito;

                    // O segundo cálculo é a mesma coisa do primeiro, porém, começa na posição 6
                    $segundo_calculo = multiplica_cnpj($primeiros_numeros_cnpj, 6);
                    $segundo_digito = ( $segundo_calculo % 11 ) < 2 ? 0 : 11 - ( $segundo_calculo % 11 );

                    // Concatena o segundo dígito ao CNPJ
                    $cnpj = $primeiros_numeros_cnpj . $segundo_digito;

                    // Verifica se o CNPJ gerado é idêntico ao enviado
                    if ($cnpj === $cnpj_original) {
                        return true;
                    }
                },
                function($cpf = false) {
                    // Exemplo de CPF: 025.462.884-23

                    /**
                     * Multiplica dígitos vezes posições
                     *
                     * @param string $digitos Os digitos desejados
                     * @param int $posicoes A posição que vai iniciar a regressão
                     * @param int $soma_digitos A soma das multiplicações entre posições e dígitos
                     * @return int Os dígitos enviados concatenados com o último dígito
                     *
                     */
                    if ( ! function_exists('calc_digitos_posicoes') ) {
                        function calc_digitos_posicoes( $digitos, $posicoes = 10, $soma_digitos = 0 ) {
                            // Faz a soma dos dígitos com a posição
                            // Ex. para 10 posições:
                            //   0    2    5    4    6    2    8    8   4
                            // x10   x9   x8   x7   x6   x5   x4   x3  x2
                            //   0 + 18 + 40 + 28 + 36 + 10 + 32 + 24 + 8 = 196
                            for ( $i = 0; $i < strlen( $digitos ); $i++  ) {
                                $soma_digitos = $soma_digitos + ( $digitos[$i] * $posicoes );
                                $posicoes--;
                            }

                            // Captura o resto da divisão entre $soma_digitos dividido por 11
                            // Ex.: 196 % 11 = 9
                            $soma_digitos = $soma_digitos % 11;

                            // Verifica se $soma_digitos é menor que 2
                            if ( $soma_digitos < 2 ) {
                                // $soma_digitos agora será zero
                                $soma_digitos = 0;
                            } else {
                                // Se for maior que 2, o resultado é 11 menos $soma_digitos
                                // Ex.: 11 - 9 = 2
                                // Nosso dígito procurado é 2
                                $soma_digitos = 11 - $soma_digitos;
                            }

                            // Concatena mais um dígito aos primeiro nove dígitos
                            // Ex.: 025462884 + 2 = 0254628842
                            $cpf = $digitos . $soma_digitos;

                            // Retorna
                            return $cpf;
                        }
                    }

                    // Verifica se o CPF foi enviado
                    if ( ! $cpf ) {
                        return false;
                    }

                    // Remove tudo que não é número do CPF
                    // Ex.: 025.462.884-23 = 02546288423
                    $cpf = preg_replace( '/[^0-9]/is', '', $cpf );

                    // Verifica se o CPF tem 11 caracteres
                    // Ex.: 02546288423 = 11 números
                    if ( strlen( $cpf ) != 11 ) {
                        return false;
                    }

                    // Captura os 9 primeiros dígitos do CPF
                    // Ex.: 02546288423 = 025462884
                    $digitos = substr($cpf, 0, 9);

                    // Faz o cálculo dos 9 primeiros dígitos do CPF para obter o primeiro dígito
                    $novo_cpf = calc_digitos_posicoes( $digitos );

                    // Faz o cálculo dos 10 dígitos do CPF para obter o último dígito
                    $novo_cpf = calc_digitos_posicoes( $novo_cpf, 11 );

                    // Verifica se o novo CPF gerado é idêntico ao CPF enviado
                    if ( $novo_cpf === $cpf ) {
                        // CPF válido
                        return true;
                    } else {
                        // CPF inválido
                        return false;
                    }
                }
            );

            switch($type){
                case "nome":
                    return preg_match( "/^[a-zA-ZàáâäãåąčćęèéêëėįìíîïłńòóôöõøùúûüųūÿýżźñçčšžÀÁÂÄÃÅĄĆČĖĘÈÉÊËÌÍÎÏĮŁŃÒÓÔÖÕØÙÚÛÜŲŪŸÝŻŹÑßÇŒÆČŠŽ∂ð ,.'-]+ [a-zA-ZàáâäãåąčćęèéêëėįìíîïłńòóôöõøùúûüųūÿýżźñçčšžÀÁÂÄÃÅĄĆČĖĘÈÉÊËÌÍÎÏĮŁŃÒÓÔÖÕØÙÚÛÜŲŪŸÝŻŹÑßÇŒÆČŠŽ∂ð ,.'-]+$/u", $data);
                break;
                case "cpfcnpj":
                    return $fns[1]($data) || $fns[0]($data);
                break;
                case "cnpj":
                    return $fns[0]($data);
                break;
                case "cpf":
                    return $fns[1]($data);
                break;
                case "email":
                    return preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i", $data);
                break;
            }
        }

        function simple_loader(UITemplate $content, String $layout="", Array $vars=array(), Array $models=array()){
            if(!empty($layout)){
                $content->loadScripts();

                $content->setCode($layout);

                $content->loadParentVars();

                $content->applyVars($vars);

                $content->applyModels($models);

                $content->applyVars($vars);
            } else {
                # Default Vars

                $prefix = explode("/",$this->rootDir());

                $prefix = array_filter($prefix);

                $prefix = "/".implode("/", $prefix);

                if($prefix === "/"){
                    $prefix = "";
                }

                $content->applyVars($tvars=array(
                    "mydomain" => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER["HTTP_HOST"]}",
                    "myurl" => preg_replace("/(\?ajax.*)/", "", ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER["HTTP_HOST"]}{$_SERVER["REQUEST_URI"]}")),
                    "mypath" => $_SERVER["REQUEST_URI"],
                    "year" => date("Y"),
                    "URLPrefix" => "{$prefix}"
                ));

				foreach($tvars as $vr=>$tk){
					$this->{$vr} = $tk;
				}

                $content->applyVars($_REQUEST);

                $content->applyVars($_SESSION);

                if(method_exists($this,($met=implode("_", explode("/", $this->rootDir())) . "template_"))){
                    $content = $this->{$met}($content);
                }

            }



			// $this->dbg($_SESSION);

			// $this->dbg("isset({$_SESSION["magic__returnback__on__post"]}) && (\"/\" . ".implode("/",$this->url())." == {$_SESSION["magic__returnback__on__page"]}");

			if($this->post() && !isset($_SESSION["magic__returnback__break"]) && isset($_SESSION["magic__returnback__on__post"]) && ("/".implode("/",$this->url())) == $_SESSION["magic__returnback__on__page"]){
				// $this->dbg("!isset({$_SESSION["magic__returnback__break"]}) && isset({$_SESSION["magic__returnback__on__post"]}) && (\"/\" . ".implode("/",$this->url())." == {$_SESSION["magic__returnback__on__page"]}");
				$action = ("Location: {$_SESSION["magic__returnback__on__post"]}");

				unset($_SESSION["magic__returnback__on__post"]);
				unset($_SESSION["magic__returnback__on__page"]);

				header($action);
			} elseif(isset($_SESSION["magic__returnback__break"])){
				if($_SESSION["magic__returnback__break"] > 0)unset($_SESSION["magic__returnback__break"]);
				else $_SESSION["magic__returnback__break"]++;
			} elseif($this->post() && isset($_POST["magic__returnback__on__post"]) && isset($_POST["magic__returnback__on__page"])){
				$_SESSION["magic__returnback__on__post"] = $_POST["magic__returnback__on__post"];
				$_SESSION["magic__returnback__on__page"] = $_POST["magic__returnback__on__page"];
				$_SESSION["magic__returnback__break"] = 0;
			}

			// $this->dbg($_SESSION);

            return $content;
        }

        function post(){
            return $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST) && count(array_keys($_POST)) > 0;
        }

        function rootDir(String $set="empty"){
            if($set == "empty"){
                return $this->dir;
            } else {
                return $this->dir = $set;
            }
        }

        function applyVars(Array $vars){
            $this->defaultVars = array_merge($this->defaultVars, $vars);
        }

        function generateID($base=-1){
            if($base == -1){
                $base = bae64_encode(uniqid());
            }

            return md5(strtolower(preg_replace("/[^A-z0-9]/","",preg_replace("/(&[\s\S]+?;)/","",$base))));
        }

        function uiTemplateDefault($template){
            $path = (new __paths)->get();
            $this->defaultUiTemplate = file_get_contents("{$path->templates}/ui/{$template}.html");
        }

        function uiTemplate($template){
            $path = (new __paths)->get();
            $this->uiTemplate = file_get_contents("{$path->templates}/ui/{$template}.html");
            if(method_exists($this,($met=implode("_", explode("/", $this->rootDir())) . "template_"))){
                $this->uiTemplateObject = $this->{$met}($this->uiTemplateObject);
            }

        }

        function control(String $control, Array $args = array()){
            $args["ux"] = $this;
            $args["lwdk"] = $this->parent;
            include_once (new __paths)->get()->controls . "/{$control}.php";
            $control = preg_replace("/\//", "_", $control);
            $control = "ctrl_{$control}";
            return ${"control"}($args);
        }

        function url(int $index=-1, String $url = "empty"){
            return $this->parent()->url($index, $url);
        }

        function inUrl(String $needle){
            return in_array($needle, $this->url());
        }

        function getPage(){
            $this->uiTemplateObject = new UITemplate($this);
            // exit($f = dirname(dirname(dirname(__DIR__))) . $_SERVER["REQUEST_URI"] . ".html");
            if(method_exists($this,($met=implode("_", explode("/", $this->rootDir())) . "protect_"))){
                $this->{$met}();
            }

            if(method_exists($this,($met=implode("_", explode("/", $this->rootDir())) . "create_"))){
                $this->{$met}();
            }

            $exec = "page_" . $this->url(0);
            // exit($exec);
            if(!method_exists($this,$exec)){
                $file = false;

                if(file_exists($f = dirname(dirname(dirname(__DIR__))) . $_SERVER["REQUEST_URI"] . ".html")){
                    $file = $f;
                } elseif(file_exists($f = dirname(dirname(dirname(__DIR__))) . $_SERVER["REQUEST_URI"] . ".htm")){
                    $file = $f;
                } elseif(file_exists($f = dirname(dirname(dirname(__DIR__))) . $_SERVER["REQUEST_URI"] . "index.html")){
                    $file = $f;
                } elseif(file_exists($f = dirname(dirname(dirname(__DIR__))) . $_SERVER["REQUEST_URI"] . "index.htm")){
                    $file = $f;
                } elseif(file_exists($f = dirname(dirname(dirname(__DIR__))) . $_SERVER["REQUEST_URI"] . "/index.html")){
                    $file = $f;
                } elseif(file_exists($f = dirname(dirname(dirname(__DIR__))) . $_SERVER["REQUEST_URI"] . "/index.htm")){
                    $file = $f;
                }

                if($file !== false){
                    readfile($file);
                    exit;
                } else {
                    $exec = "page_{$this->defaultPage}";
                }
            }

            if(method_exists($this,$exec)){
                $this->uiTemplateObject->setTemplate(is_bool($this->uiTemplate) && !$this->uiTemplate ? $this->defaultUiTemplate : $this->uiTemplate);

                if(method_exists($this,($met=implode("_", explode("/", $this->rootDir())) . "template_"))){
                    $this->uiTemplateObject = $this->{$met}($this->uiTemplateObject);
                }

                $this->{$exec}($this->uiTemplateObject);
            }

            if(method_exists($this,($met=implode("_", explode("/", $this->rootDir())) . "end_"))){
                $this->{$met}();
            }
        }

        function setParent(lwdk $parent){
            $this->parent = $parent;
        }

        function parent(){
            return $this->parent;
        }

        /** FUNCIONALIDADES ADICIONAIS **/

        function entity ($string) {
            $string = str_split($string);

            for($i = 0; $i < count($string); $i++){
                $code = (int)ord($string[$i]);
                if($code > 123)$string[$i] = "&#{$code};";
            }

            return implode("", $string);
        }

        function database(){
            return new __database();
        }
    }
?>
