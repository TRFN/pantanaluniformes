<?php
/*
    VER: 1.0
    LAST-UPDATE: 13/04/2021
*/
    function ctrl_connect_tinyERP($args){
        $instance = new class extends APPControls {

            private $token = 'adb64116820905c066384ea1a4f539c28feff1d2';

            private function enviarREST($url, $data, $optional_headers = null){
            	$params = array('http' => array(
            		'method' => 'POST',
            	    'content' => $data
            	));

            	if ($optional_headers !== null) {
            		$params['http']['header'] = $optional_headers;
            	}

            	$ctx = stream_context_create($params);
            	$fp = @fopen($url, 'rb', false, $ctx);

            	if (!$fp) {
            		throw new Exception("Problema com $url, $php_errormsg");
            	}
            	$response = @stream_get_contents($fp);
            	if ($response === false) {
            		throw new Exception("Problema obtendo retorno de $url, $php_errormsg");
            	}

            	return $response;
            }

            public function produto($id=-1,$pagina=1){
                return is_array($id)
                    ? $this->enviarREST('https://api.tiny.com.br/api2/produto.incluir.php', "token={$this->token}&produto=" . (json_encode($id)) . "&formato=JSON")
                    : $id===-1
                    ? $this->enviarREST('https://api.tiny.com.br//api2/produtos.pesquisa.php', "token={$this->token}&pesquisa=&pagina={$pagina}&formato=JSON")
                    : $this->enviarREST('https://api.tiny.com.br/api2/produto.obter.php', "token={$this->token}&id={$id}&formato=JSON");
            }

            public function estoque($id){
                return is_array($id)
                    ? $this->enviarREST('https://api.tiny.com.br/api2/produto.atualizar.estoque.php', "token={$this->token}&estoque=" . (json_encode($id)) . "&formato=JSON")
                    : $this->enviarREST('https://api.tiny.com.br/api2/produto.obter.estoque.php', "token={$this->token}&id={$id}&formato=JSON");
            }

            public function categorias(){
                return $this->enviarREST('https://api.tiny.com.br/api2/produtos.categorias.arvore.php', "token={$this->token}&formato=JSON");
            }
        };

        $instance->args = $args;

        return $instance;
    }
?>
