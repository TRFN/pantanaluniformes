<?php
    trait function_group_sessions {

        public $usuario = array();

        function page_session(){
            header("Content-Type: application/json");
            exit(json_encode($this->usuario() !== false));
        }

        function session_protect(){
            if($this->url(0) == "responder_pesquisa"){
                return true;
            }
            $session = parent::control("users/session")->session();
            if($this->url(0) !== "login"){
                if($this->usuario($session) === false){
                    header("Location: /login/?ref={$_SERVER["REQUEST_URI"]}");
                    exit;
                } else {
                    $data = array();

                    $data["usuario_nome"] = $this->usuario()->nome;
                    $data["usuario_email"] = $this->usuario()->email;
                    $data["nivelacesso"] = $this->usuario()->nivelacesso;

                    $this->applyVars($data);
                }
            } else {
                if($this->usuario($session) !== false && !isset($_POST["email"])){
                    header("Location: /");
                    exit;
                }
            }
        }

        function session_auth(Array $enabled=array()){
            if(!in_array($this->usuario()->nivelacesso,$enabled)){
                header("Location: /");
                exit;
            }
        }

        function session_error(String $error, String $redirect = "/"){
            exit("<script lwdk-addons>setTimeout(function(){Swal.fire({
                title: 'Ocorreu um erro.',
                html: '{$error}',
                icon: 'error',
                confirmButtonText: 'Entendido',
                allowOutsideClick: false
            }).then(result => {window.top.location.href='{$redirect}';});},1000);</script>");
        }

        function usuario($set = false){
            return (count($this->usuario)>0 || $set !== false)?((object)($set===false?$this->usuario:($this->usuario=$set))):false;
        }
    }
?>
