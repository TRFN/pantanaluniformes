<?php
    class admin_panel extends APPObject {
        use
            function_group_dates,
            admin_users,
            admin_paginas,
            admin_clientes,
            admin_social,
			admin_pags;


        function __construct(){
            # CONFIGURATIONS #
            $this->rootDir("/admin/");
            $this->uiTemplateDefault("admin/application");
            header("Content-Type: text/html;charset=utf-8");
            $this->empresa = "Painel Administrativo";
        }

        function _tablepage($content,$keyword,$titulos,$__dados,$keyid,$titulo,$db,$txtBtn,$filtro="not",$acoes=true,$layout="admin/tables"){

                $dados = explode(",",$titulos);

                $thead = (("<th style='text-transform: uppercase;'>" . implode("</th><th style='text-transform: uppercase;'>", $dados) . "</th>") . ($acoes?"<th  style='text-transform: uppercase;' style='min-width: 100px;'>a&ccedil;&otilde;es</th>":""));

                $dados = explode(",",$__dados);

                $tbody = "";

                $botao_apagar = (function($id,$keyword,$txtBtn){
                    return '<a href="javascript:;" onclick="Swal.fire({
                                        title: ``,
                                        html: `Voc&ecirc; deseja mesmo apagar o(a) ' . $txtBtn . '?! <br>Essa a&ccedil;&atilde;o &eacute; irrevers&iacute;vel!`,
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
                                                ` ' . ucfirst($txtBtn) . ' apagado(a) com sucesso!`,
                                                `success`
                                            ).then((result) => {
                                                $.post(`{URLPrefix}/' . $keyword . '/' . $id . '/apagar/`, function(){setTimeout(refresh,500);});
                                            });
                                        }
                                    });" class="m-portlet__nav-link btn m-btn m-btn--hover-danger m-btn--icon m-btn--icon-only m-btn--pill" title="Apagar"><i class="la la-trash"></i></a>';
                });

                $botao_apagar_desabilitado = '<button class="m-portlet__nav-link btn m-btn m-btn--hover-light  m-btn--icon m-btn--icon-only m-btn--pill" onclick="swal.fire(``,`Desculpe, mas voc&ecirc; n&atilde;o possui privil&eacute;gios para apagar usuarios.`, `error`);" title="Voce nao pode deletar este usuario"><i class="la la-trash"></i></button>';

                $query = $filtro == -1 ? $dstyle="position: fixed; top: -100vw; left: -100vh; width: 0; height: 0; margin: 0; padding: 0; overflow: hidden; opacity: 0; display: none; visibility: hidden;" : ($filtro == "not" ? parent::database()->getAll($db):parent::database()->query($db,$filtro));

                foreach($query as $_dado){
                    $dado = array();
                    foreach($dados as $campo){
                        if(isset($_dado[$campo]) && !empty($_dado[$campo])){
                            $dado[] = ($_dado[$campo]);
                        } else {
                            $dado[] = "&ndash;";
                        }
                    }

                    if($acoes){
                        if(!isset($_dado[$keyid])){
                            $_dado[$keyid] = "";
                        }
                        $dado[] = '<a href="/'.$keyword.'/' . $_dado[$keyid] . '/" ajax=on class="m-portlet__nav-link btn m-btn m-btn--hover-accent m-btn--icon m-btn--icon-only m-btn--pill" title="Editar"><i class="la la-edit"></i></a>' . $botao_apagar($_dado[$keyid],$keyword,$txtBtn);
                    }

                    $tbody .= "<tr><td><span style='display: inline-block;overflow-wrap: break-word;word-wrap: break-word;hyphens: auto;max-width: 400px; text-align: center;'>" . implode("</span></td><td><span style='display: inline-block;overflow-wrap: break-word;word-wrap: break-word;hyphens: auto;max-width: 400px;text-align: center;'>", $dado) . "</span></td></tr>";
                }

                return $this->simple_loader($content, $layout, array(
                    "TITLE"=>$titulo,
                    "thead" => $thead,
                    "tbody" => $tbody,
                    "link-add" => "/{$keyword}/",
                    "text-add" => "Adicionar " . $txtBtn,
                    "extrascript" => ""
                ), array(
                "extrabody" => strlen($keyword)>0?"admin/botao_adicionar":"empty"));
        }

        function _admin_template_($content){
            $content->applyModels(array(
                "menu_lateral" => "admin/menu",
                "header" => "admin/header"
            ));

            $vars = (array(
                "logotipo" => "/images/logo-mini.png",
				"logotipo2" => "/images/logo-mini2.png",
                "TITLE" => "Painel Admin",
                "empresa" => $this->empresa,
				"hidden" => 'position: fixed; top: -100vw; left: -100vh; width: 0; height: 0; margin: 0; padding: 0; overflow: hidden; opacity: 0; display: none; visibility: hidden;'
            ));

            foreach($this->admin_sessao() as $chave=>$valor){
                $vars["sessao-{$chave}"] = $valor;
            }

            $content->applyVars($vars);

            return $content;
        }

        function page_main($content){
			$this->page_pagina($content, true);
        }
    }
?>
