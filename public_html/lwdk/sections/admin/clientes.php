<?php
    trait admin_clientes {
        private function ajax_clientes(){
            try{
                header("Content-Type: application/json");
                $id = $_POST["id"];
                if(!empty($_POST["senha"])){
                    $_POST["senha"] = md5($_POST["senha"]);
                } else {
                    unset($_POST["senha"]);
                }
                $query = $this->database()->query("clientes", "id = {$id}");
                if(!count($query)){
                    $this->database()->push("clientes",array($_POST),"log_remove");
                } else {
                    $this->database()->setWhere("clientes","id = {$id}",$_POST);
                }
            } catch(Exception $e){
                exit("false");
            }
            exit("true");
        }

        function page_clientes($content,$me=false){

            $content->minify = true;

            if($this->post())return $this->ajax_clientes();

            if(
                parent::url(2) == "apagar" && (!empty(parent::url(1)) || (string)parent::url(1) == "0") &&
                count(parent::database()->query("clientes", "id = " . ($query = (string)parent::url(1)))) > 0
            ){
                exit(parent::database()->deleteWhere("clientes", "id = {$query}"));
            }

            $id = parent::database()->newID("clientes");

            $size_form = 4;

            $vars = array(
                "id"        => $id,
                "botao-txt" => "Criar novo cliente",
                "TITLE"     => "Adicionar Cliente",
                "nome"      => "",
                "email"     => "",
                "size_l"    => round((12-$size_form)/2)-1,
                "size_r"    => $size_form,
                "acao"      => "criar",
                "page"      => "clientes"
            );

            if(!empty(parent::url(1)) || (string)parent::url(1) == "0" || $me){
                $searchID = $me ? $this->admin_sessao()->id:(string)parent::url(1);
                if(count($query = parent::database()->query("clientes", "id = " . $searchID)) > 0){
                    $vars["TITLE"]      = ($me?"Alterar seus dados":"Modificar Cliente");
                    $vars["botao-txt"]  = "Salvar o que foi modificado";
                    $vars["acao"]       = "modificar";

                    foreach($query[0] as $id=>$val){
                        $vars[$id] = is_array($val) ? json_encode($val):$val;
                    }

                    unset($vars[0]);

                } elseif(parent::url(1) == "listar"){
					$btnTxt          = "Cliente";
                    $keyword         = "clientes";
                    $db              = "clientes";
                    $titulos         = "Nome,E-mail";
                    $dados           = "nome,email";
                    $keyid           = "id";
                    $titulo          = "Gerir Clientes da Loja virtual";

                    exit($this->_tablepage($content,$keyword,$titulos,$dados,$keyid,$titulo,$db,$btnTxt)->getCode());
                }
            }

            $content = $this->simple_loader($content, "admin/administrador", $vars);

            echo $content->getCode();
        }
    }
