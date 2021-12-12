<?php
    class UITemplate {
        private $code = "";
        private $template = "";
        private $parent = null;
        private $object = null;
        public  $scriptAddOns = array();
        public  $minify = true;

        private function removerAcentos($input){
            $acento = array();
            $codigoHTML = array();

            $acento[] = "/Á/";
            $codigoHTML[] = "&Aacute;";
            $acento[] = "/É/";
            $codigoHTML[] = "&Eacute;";
            $acento[] = "/Í/";
            $codigoHTML[] = "&Iacute;";
            $acento[] = "/Ó/";
            $codigoHTML[] = "&Oacute;";
            $acento[] = "/Ú/";
            $codigoHTML[] = "&Uacute;";
            $acento[] = "/á/";
            $codigoHTML[] = "&aacute;";
            $acento[] = "/é/";
            $codigoHTML[] = "&eacute;";
            $acento[] = "/í/";
            $codigoHTML[] = "&iacute;";
            $acento[] = "/ó/";
            $codigoHTML[] = "&oacute;";
            $acento[] = "/ú/";
            $codigoHTML[] = "&uacute;";
            $acento[] = "/Â/";
            $codigoHTML[] = "&Acirc;";
            $acento[] = "/Ê/";
            $codigoHTML[] = "&Ecirc;";
            $acento[] = "/Ô/";
            $codigoHTML[] = "&Ocirc;";
            $acento[] = "/â/";
            $codigoHTML[] = "&acirc;";
            $acento[] = "/ê/";
            $codigoHTML[] = "&ecirc;";
            $acento[] = "/ô/";
            $codigoHTML[] = "&ocirc;";
            $acento[] = "/À/";
            $codigoHTML[] = "&Agrave;";
            $acento[] = "/à/";
            $codigoHTML[] = "&agrave;";
            $acento[] = "/Ü/";
            $codigoHTML[] = "&Uuml;";
            $acento[] = "/ü/";
            $codigoHTML[] = "&uuml;";
            $acento[] = "/Ç/";
            $codigoHTML[] = "&Ccedil;";
            $acento[] = "/ç/";
            $codigoHTML[] = "&ccedil;";
            $acento[] = "/Ã/";
            $codigoHTML[] = "&Atilde;";
            $acento[] = "/Õ/";
            $codigoHTML[] = "&Otilde;";
            $acento[] = "/ã/";
            $codigoHTML[] = "&atilde;";
            $acento[] = "/õ/";
            $codigoHTML[] = "&otilde;";
            $acento[] = "/Ñ/";
            $codigoHTML[] = "&Ntilde;";
            $acento[] = "/ñ/";
            $codigoHTML[] = "&ntilde;";

            return preg_replace($acento, $codigoHTML, $input);
       }

        function __construct(APPObject $parent){
            $this->parent = $parent;
        }

        function loadScripts(){
            $this->applyVars(array("LWDK::AJAX"=>__dinamicJS::ajaxCore()));
            $this->applyVars(array("LWDK::JSINIT"=>__dinamicJS::initScripts()));
        }

        function uiTemplate(String $set){
            $this->parent->uiTemplate($set);
            $this->reset();
        }

        function reset(){
            $this->setTemplate(is_bool($this->parent->uiTemplate) && !$this->parent->uiTemplate ? $this->parent->defaultUiTemplate : $this->parent->uiTemplate);
        }

        function minifyCode($code){
            if($this->minify === false){
                return $code;
            }

            //replace style elements
            $code = preg_replace_callback("/<style>([\s\S]*?)<\/style>/", function ($matches) {
                //minify the css
                $css = $matches[1];
                $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);

                $css = str_replace(["\r\n", "\r", "\n", "\t", '  ', '    ', '     '], '', $css);

                $css = preg_replace(['(( )+{)', '({( )+)'], '{', $css);
                $css = preg_replace(['(( )+})', '(}( )+)', '(;( )*})'], '}', $css);
                $css = preg_replace(['(;( )+)', '(( )+;)'], ';', $css);

                return '<style>'.$css.'</style>';
            }, $code);

            //replace script elements
            $code = preg_replace_callback("/<script>([\s\S]*?)<\/script>/", function ($matches) {
                $minifiedCode = JSMin::minify($matches[1]);
                // exit($minifiedCode);

                return '<script>'.$minifiedCode.'</script>';
            }, $code);


            $Search = array(
                '/(\n|^)(\x20+|\t)/',
                '/(\n|^)\/\/(.*?)(\n|$)/',
                '/\n/',
                '/\<\!--.*?-->/',
                '/(\x20+|\t)/', # Delete multispace (Without \n)
                '/\>\s+\</', # strip whitespaces between tags
                '/(\"|\')\s+\>/', # strip whitespaces between quotation ("') and end tags
                '/=\s+(\"|\')/'); # strip whitespaces between = "'

               $Replace = array(
                "\n",
                "\n",
                " ",
                "",
                " ",
                "><",
                "$1>",
                "=$1");

            return preg_replace($Search,$Replace,$code);

            return $code;
        }

        function applyVars(Array $vars){
			foreach($vars as $key => $value){
                if(!is_array($value)){
                    $this->template = explode("{{$key}}", $this->template);
                    $this->template = implode($value, $this->template);

                    $this->code = explode("{{$key}}", $this->code);
                    $this->code = implode($value, $this->code);


					$this->template = explode(urlencode("{{$key}}"), $this->template);
                    $this->template = implode($value, $this->template);

                    $this->code = explode(urlencode("{{$key}}"), $this->code);
                    $this->code = implode($value, $this->code);

					$this->template = explode(urlencode("{{$key}}"), $this->template);
                    $this->template = implode($value, $this->template);

                    $this->code = explode(urlencode("{{$key}}"), $this->code);
                    $this->code = implode($value, $this->code);

                    foreach($this->scriptAddOns as $order=>$script){
                        $this->scriptAddOns[$order] = explode("{{$key}}", $script);
                        $this->scriptAddOns[$order] = implode($value, $this->scriptAddOns[$order]);
                    }
                }
            }
        }

        function loadParentVars(){
            $this->applyVars($this->parent->defaultVars);
        }

        function applyModels(Array $load){
            foreach($load as $key => $value){
                if(!preg_match("/[^0-9]/",(string)$key)){
                    $key = $value;
                }
                $this->code = explode("{{$key}}", $this->code);
                $this->code = implode($l=file_get_contents($this->parent->parent()->path->models . "/{$value}.html"), $this->code);
                $this->template = explode("{{$key}}", $this->template);
                $this->template = implode($l, $this->template);

                foreach($this->scriptAddOns as $order=>$script){
                    $this->scriptAddOns[$order] = explode("{{$key}}", $script);
                    $this->scriptAddOns[$order] = implode($l, $this->scriptAddOns[$order]);
                }
            }
        }

		function getCode($codOnly=false){
            $this->template = $this->minifyCode($this->template);
            $this->code = $this->minifyCode($this->code);

            if(!($ajax=isset($_REQUEST["ajax"])||$codOnly)){
                foreach($this->scriptAddOns as $script=>$value){
                    $this->template = explode($script,$this->template);
                    $this->template = implode(JSMin::minify($value),$this->template);
                }
            }

            foreach($this->scriptAddOns as $script=>$value){
                $this->code = explode($script,$this->code);
                $this->code = implode(JSMin::minify($value),$this->code);
            }

            $this->parent->uiTemplateObject = $this->parent->simple_loader($this->parent->uiTemplateObject);

            $this->loadParentVars();

            return ($ajax?$this->code:implode($this->code,explode("{PAGE_CONTENT}", $this->template)));
        }

		function setCode($code){
            $this->code = ($this->removerAcentos(file_get_contents($this->parent->parent()->path->layouts . "/{$code}.html")));
			$this->loadParentVars();
			$this->code = $this->readScripts($this->code);

            $this->code .= "<script lwdk-addons>LWDKExec(()=>(document.title=`{TITLE} | {$this->parent->empresa}`));</script>";
        }

		function loadModel(String $file, Array $vars = []){
			$file = file_exists($f=$this->parent->parent()->path->models . "/{$file}.html")
				? file_get_contents($f)
				: (string)$file;

			foreach($vars as $k => $value){
                if(!is_array($value)){
                    $file = explode("{{$k}}", $file);
                    $file = implode($value, $file);
				}
			}

			return $file;
		}

		function setTemplate($code){
            $this->template = $this->readScripts($this->removerAcentos($code));
        }

        private function readScripts($code, $template=false){
            // return $code;
            /*** a new dom object ***/
            $dom = new DOMDocument();

            /*** load the html into the object ***/
            if($template){
                @$dom->loadHTML($code);
            } else {
                @$dom->loadHTML($code, 8196);
            }
            foreach($dom->getElementsByTagName('script') as $item=>$script){
                $file = $script->getAttribute("src");
                if(!preg_match("/(http:|https:|\/\/)/",$file) && file_exists($file=($this->parent->parent()->path->www . "/{$file}")) && $script->hasAttribute("lwdk-vars") && $script->getAttribute("lwdk-vars") == "on"){
                    $dom->getElementsByTagName('script')->item($item)->removeAttribute("src");
                    $this->scriptAddOns[md5($file)] = file_get_contents($file);
                    @$dom->getElementsByTagName('script')->item($item)->nodeValue = md5($file);
                }
            }
            // exit;
            return (String)$dom->saveHTML();
        }
    }
?>
