function callback_prod(produtos, fornecedor, xml){
    if(produtos.length > 1){
        swal({
            title: "",
            text: "Esta mesma nota fiscal contem mais de um produto. Deseja cadastrar todos os produtos assim mesmo?",
            type: "info",
            showCancelButton: true,
            cancelButtonText: "Não",
            confirmButtonClass: "btn-success",
            cancelButtonClass: "btn-primary",
            confirmButtonText: "Sim",
            closeOnConfirm: false
        }, function(e){
            var tamanho = produtos.length;
            !e && ($("#input-xml-nfe")[0].value="");
            swal({
                title: "",
                text: "Cadastrando " + tamanho + " produtos em massa, aguarde...",
                type: null,
                showCancelButton: false,
                showConfirmButton: false,
                confirmButtonText: "",
                closeOnConfirm: false
            });
            $.post(location.href,{"cadM":produtos,"xml":xml, "emit": fornecedor},function(ok){
                if(ok=="ok"){
                    swal({
                        title: "",
                        text: "Os produtos foram cadastrados com sucesso!",
                        type: "success",
                        showCancelButton: false,
                        confirmButtonClass: "btn-success",
                        confirmButtonText: "OK",
                        closeOnConfirm: false
                    }, function(){
                        window.top.location.href = "/painel/produtos/gerir";
                    });
                } else {
                    swal({
                        title: "",
                        text: "Ocorreu um erro ao cadastrar os " + tamanho + " produtos... Tente novamente!",
                        type: "error",
                        showCancelButton: false,
                        confirmButtonClass: "btn-primary",
                        confirmButtonText: "OK",
                        closeOnConfirm: false
                    }, function(){
                        window.history.go(0);
                    });
                }
            })
        });
    } else {
        swal({
            title: "",
            text: "Deseja cadastrar o fornecedor deste produto (vinculado a nota fiscal eletronica)?",
            type: "info",
            showCancelButton: true,
            cancelButtonText: "Não",
            confirmButtonClass: "btn-success",
            cancelButtonClass: "btn-primary",
            confirmButtonText: "Sim",
            closeOnConfirm: false
        }, function(e){
            swal({
                title: "",
                text: "Cadastrando fornecedor...",
                type: null,
                showCancelButton: false,
                showConfirmButton: false,
                confirmButtonText: "",
                closeOnConfirm: false
            });
            $.post(location.href,{"emit": fornecedor,"xml":xml, prodUni: "sim", "cadM":produtos},function(ok){
                ok = JSON.parse(ok);
                window.myprodid = ok.id;
                switch(ok.stFor){
                    case "cad": text = "O fornecedor foi cadastrado com sucesso!"; type="success"; break;
                    case "upd": text = "O fornecedor foi atualizado com sucesso!"; type="info"; break;
                    default: case "ok": text = "O fornecedor ja esta cadastrado e atualizado, logo não foi necessario alteração."; type="error"; break;
                }
                swal({
                    title: "",
                    "text": text,
                    "type": type,
                    showCancelButton: false,
                    confirmButtonClass: "btn-" + (type=="error"?"danger":type),
                    confirmButtonText: "Fechar essa mensagem",
                    closeOnConfirm: false
                }, function(e){
                    url = "/painel/produtos/gerir/id/" + myprodid;
                    window.top.location.href = url;
                });
            });
        });
    }
}

$(function(){
    return !(function(ixn,axn,sep,f,r,fn){
        ixn.prependTo(f);

        $(".panel-heading:first").append(axn);

        $("#action-xml-nfe").click(function(){
            $("#input-xml-nfe").click();
        });

        r($("#input-xml-nfe")[0],fn);
    })(
        $("<input type='file' accept='text/xml' style='display:none' id='input-xml-nfe'>"),
        $("<div style='width: 150px; float: right; display: inline-block; margin-top: -8px;'><div class='btn btn-primary btn-block' id='action-xml-nfe'>Carregar NFe (XML)</div></div>"),
        $("<div class='col-md-8' style='clear:both'>&nbsp;</div>"),
        $("form:first"),
        (function(fileSelected,fn){
            if (window.File && window.FileReader && window.FileList && window.Blob) {
                fileSelected.addEventListener('change', function (e) {
                    //Set the extension for the file
                    var fileExtension = /text\/xml/;
                    //Get the file object
                    var fileTobeRead = fileSelected.files[0];
                    //Check of the extension match
                    if (fileTobeRead.type.match(fileExtension)) {
                        //Initialize the FileReader object to read the 2file
                        var fileReader = new FileReader();
                        fileReader.onload = function (e) {
                            fn(fileReader.result);
                        };
                        fileReader.readAsText(fileTobeRead);
                    }
                    else {
                        swal("","Por favor selecione um arquivo XML");
                    }

                }, false);
            }
            else {
                return false;
            }
        }),
        (function(xml){
            originalXML = xml;
            xml = $(xml);
            tipo = xml.find("prod").length > 0
                ? "produto"
                : xml.find("servico").length > 0
                    ? "servico"
                    : -1;

            switch (tipo) {
                case "produto":
                    produtos = []; fornecedor = {};
                    xml.find("prod").each(function(i){
                        produtos.push({});
                        prod = $(this).find("*").map(function(){
                            ob = {};
                            ob[this.tagName] = this.innerText;
                            return ob;
                        }).get();
                        for( i = 0; i < prod.length; i++ ){
                            for(j in prod[i]){
                                produtos[produtos.length-1][j] = prod[i][j];
                            }
                        }
                    });

                    emit = xml.find("emit *").map(function(){
                        ob = {};
                        ob[this.tagName] = this.innerText;
                        return ob;
                    }).get();
                    for( i = 0; i < emit.length; i++ ){
                        for(j in emit[i]){
                            fornecedor[j] = emit[i][j];
                        }
                    }
                    typeof callback_prod !== 'undefined' && callback_prod(produtos, fornecedor, originalXML);
                break;
                default:
                    /* - */
                break;
            }
        })
    );
});
