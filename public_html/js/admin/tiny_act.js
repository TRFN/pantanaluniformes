const IDs = window.IDs = function IDs(){
    $ids = [];
    $(DataTable__tabela_principal.rows().data()).each(function(){$ids.push($(this[3]).find(".botao-importar-produto-tinyerp").data("id"));});
    return $ids;
}

const converter = (minutos) => {
  const horas = Math.floor(minutos/ 60);
  const min = minutos % 60;
  const textoHoras = (`00${horas}`).slice(-2);
  const textoMinutos = (`00${min}`).slice(-2);

  return `${textoHoras }:${textoMinutos}`;
};

const atualizar_progresso = window.atualizar_progresso = function atualizar_progresso(n=-1){
    (n==-1)&&(n=parseInt($(".numprod").first().text())+1);

    LU("#progress_prod",(r=n/IDs().length));
    $(".numprod").text(n);

    return r;
};

const importar_todos = window.importar_todos = (function importar_todos(){
    if(IDs().length < 1)return swal.fire("","N&atilde;o h&aacute; dados a serem importados no momento.","info")&&false;

    tempo = converter(~~(IDs().length/24)).split(":");

    tempo[0] = parseInt(tempo[0]) < 1 ? "":`${tempo[0]}&nbsp;hora(s)`;
    tempo[1] = parseInt(tempo[1]) < 1 ? "":`${tempo[1]}&nbsp;minuto(s)`;

    tempo = tempo.filter(function(e){return e.length>0;});

    if(tempo.length==0){
        tempo = "menos de um minuto";
    } else {
        if(tempo.length == 1){
            tempo = tempo[0];
        } else {
            tempo = tempo.join(" e ");
        }
    }

    Swal.fire({
        title: ``,
        html: `Voc&ecirc;&nbsp;deseja mesmo importar&nbsp;<b>TODOS</b>&nbsp;estes produtos de uma s&oacute;&nbsp;vez?&nbsp;Isso pode demorar varios minutos,&nbsp;e para que a a&ccedil;&atilde;o seja concluida,&nbsp;esta p&aacute;gina dever&aacute;&nbsp;ficar aberta.<br><br><b><small>Tempo estimado:&nbsp;${tempo}.</small></b>`,
        icon: `question`,
        showCancelButton: true,
        confirmButtonColor: `#3085d6`,
        cancelButtonColor: `#d33`,
        confirmButtonText: `Sim,&nbsp;prosseguir`,
        cancelButtonText: `Mais tarde`,
    }).then((result) => {
        if (result.isConfirmed) {
            window.onbeforeunload = function(){
                return true;
            };
            loadingRequest("<h3>Importando produtos da plataforma TinyERP...</h3><br>N&atilde;o saia desta p&aacute;gina at&eacute;<br>que esta a&ccedil;&atilde;o esteja conclu&iacute;da!&nbsp;&nbsp;<small style='text-transform: uppercase;position: absolute;bottom: 12px;right: 16px;'>Produtos importados: <span class='numprod'>0</span> de " + String(IDs().length) + "<br><span class='with-error-import' style='color: #c22;'>erros: <span id='erros_importacao'>0</span> de <span class='numprod'>0</span> importados</span></small>&nbsp;<br><br>&nbsp;<div class=progress><div id=progress_prod class=progress-bar style='width: 0%;'>25%</div></div>&nbsp;<br><br>&nbsp;", false);

            window.current_id_import = 0;
            window.errors_import = 0;

            function _import(){
                errors_import > 0 && ($("#erros_importacao").text(errors_import).show());
                window._importTimeOut = setTimeout(()=>{setTimeout(_import,500);window.errors_import++;current_id_import++;setTimeout(atualizar_progresso,1000);},5000);
                $.post("{myurl}", {getProd: (IDs()[current_id_import])}, function(data){
                    $.post("{myurl}", {cadprod: data}, function(success){
                        if(success===true){
                            current_id_import++;
                            clearTimeout(_importTimeOut);
                            if(current_id_import == IDs().length){
                                atualizar_progresso();
                                window.onbeforeunload = null;
                                return setTimeout(()=>successRequest(refresh,errors_import<1?"A importa&ccedil;&atilde;o foi conclu&iacute;da com sucesso.":`A importa&ccedil;&atilde;o foi conclu&iacute;da,&nbsp;por&eacute;m houve ${errors_import}&nbsp;falhas por erro interno da plataforma TinyERP.`),1000);
                            }
                            clearTimeout(_importTimeOut);
                            atualizar_progresso();
                            setTimeout(_import,2000);
                        } else {
                            clearTimeout(_importTimeOut);
                            errorRequest(refresh,"Ocorreu um erro durante o processo. Tente novamente.");
                        }
                    });
                });
            }

            setTimeout(_import,1000);
        }
    });
});
