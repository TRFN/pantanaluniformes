LWDKExec(function(){
    if(`{categorias}`.length == 0){
        return errorRequest(()=>Go("categorias"), "Antes de cadastrar um produto, voc&ecirc; precisa criar uma categoria.");
    }

    if(`{subcathtml}`.length == 0){
        return errorRequest(()=>Go("sub_categorias"), "Antes de cadastrar um produto, voc&ecirc; precisa criar uma sub-categoria.");
    }

    One("#img_upload").addClass("dropzone").dropzone({ // The camelized version of the ID of the form element

        // The configuration we've talked about above
        autoProcessQueue: true,
        uploadMultiple: true,
        parallelUploads: 25,
        maxFiles: 25,
        acceptedFiles: "image/*",

        // The setting up of the dropzone
        init: function() {
            var myDropzone = this;

            setInterval(function(){
                $("[data-name=\"imagens\"]").val(JSON.stringify(MapTranslate(MapEl("#gallery .img", function(){
                    return $(this).css("background-image").split('"')[1] + "|" + $(this).parent().find("input:not([type=\"hidden\"])").first().val();
                }), ["url","legend"])));

                $(".apagar").each(function(){
                    One(this).click(function(){
                        $(this).parent().parent().slideUp('slow', function(){
                            $(this).remove();
                        })
                    });
                });

                // One("#gallery input", "AutoComplete").change(function(){
                    map = MapEl("#gallery input:not([type=\"hidden\"])", function(){return $(this).val();});
                    $("#gallery input").each(function(){
                        AutoComplete(this, map);
                    });
                // });
            }, 500);

            myDropzone.on("successmultiple", function(file, response) {
                $.post("{myurl}", {imgs: response}, function(data){
                    $("#gallery.start").removeClass("start").html("");
                    $("#gallery").append(data);
                });
            });
        },

        complete: function(file){
            this.removeFile(file);
        }
    });
    One("select[data-name=\"categoria\"]", "changed__event").on("changed.bs.select", function(){
        let $html = {subcategorias}[parseInt(this.value)];
        // console.log({subcategorias});
        $("select[data-name=\"subcategoria\"]").prop("disabled", false).html($html).selectpicker("refresh");
    });
    FormCreateAction("dados_produto", function(data){
        let go = function(to){
            to = /(#)/.test(to)?$(to)[0]:$("[data-name=\"" + to + "\"]")[0];

            setTimeout(()=>$(to).focus()[0].click(), 1100);

            $([document.documentElement, document.body]).animate({
                scrollTop: $(to).offset().top - 150
            }, 900);
        };

        if(data.nome.length < 3){
            return errorRequest(()=>go("nome"), "Insira um nome valido para produto...");
        }

        if(data.categoria == "null"){
            return errorRequest(()=>go("categoria"), "Selecione uma categoria");
        }

        if(data.subcategoria == "null"){
            return errorRequest(()=>go("subcategoria"), "Selecione uma sub-categoria");
        }

        for(let n of ["largura","altura","comprimento","parcelas-sem-juros"]){
            if(parseInt(data[n]) < 1){
                return errorRequest(()=>go(n), "Defina um valor maior do que zero.");
            }
        }

        if(data.imagens.length < 1){
            return errorRequest(()=>go("#img_upload"), "Carregue uma imagem para o produto.");
        }

        $.post("{myurl}", {cadprod: data}, function(success){
            if(success===true){
                successRequest(("{acao}" === "cadastrado" ? function(){window.top.location.href="/admin/produto/listar/";}:function(){history.go(0)}), "O produto foi {acao} com sucesso!");
            } else {
                errorRequest(refresh);
            }
        });

    });

    let imgs={imagens}, limg = MapEl(imgs,function(){return this.url}), ltxt = MapEl(imgs,function(){return this.legend},false,false);
    limg.length > 0 && $.post("{myurl}", {imgs: limg}, function(data){
        $("#gallery.start").removeClass("start").html("");
        $("#gallery").append(data);
        for(let i = 0; i < ltxt.length; i++){
            $("#gallery input").eq(i).val(ltxt[i]);
        }
    });
});
