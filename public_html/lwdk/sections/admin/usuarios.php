<?php
trait admin_users {
    use administradores;
    function _admin_protect_(){
        if(!$this->page_session(true) && $this->url(0) != "login"){
            header("Location: " . $this->rootDir() . "login/");
        } elseif($this->page_session(true) && $this->url(0) == "login"){
            header("Location: " . $this->rootDir());
        }
    }

    function admin_sessao_object(){
        $sessao = $this->control("users/session");
        /* CONFIG */
        $sessao->keyid    = "administrador";
        $sessao->database = "administradores";
        $sessao->mainkey  = "id";

        return $sessao;
    }

    function admin_sessao(String $email="",String $senha=""){
        # INITIAL USER OF ADMIN
        # $this->database()->push($this->admin_sessao_object()->database, array(array("email"=>"tulio.nasc95@gmail.com","senha"=>md5("12345"))));

        if(empty($email) && $email===$senha){
            $sessid = $this->admin_sessao_object()->session();
            unset($sessid->senha);
            return $sessid;
        } else {
            return $this->admin_sessao_object()->connect($email, $senha);
        }
    }

    function page_session($get=false){
        $cond = !(is_bool($this->admin_sessao()) && $this->admin_sessao() === false);
        if($get!==true){
            header("Content-Type: application/json");
            exit($cond ? "true":"false");
        } else {
            return $cond;
        }
    }

    function page_logout($content){
        $this->admin_sessao_object()->logout();
        if($content !== "no-redirect"){
            header("Location: " . $this->rootDir() . "login/");
        }
    }

    function page_login($content){
        $content->uiTemplate("admin/login");

        $content->applyVars(array("url_retorno" => isset($_GET["ref"]) ? $_GET["ref"]:$this->rootDir()));

        if($this->post()){
            header("Content-Type: text/plain");

            if($this->admin_sessao()!==false){
                exit("on");
            }

            if(($id=$this->admin_sessao($_POST["email"],$_POST["password"])) !== false){
                exit("on");
            }

            exit("off");
        }

        echo $content->getCode();
    }
}
?>
