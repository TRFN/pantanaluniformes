LWDKExec(function(){
    if(`{opcoes}`.length == 0){
        return errorRequest(()=>Go("categorias"), "Antes de cadastrar uma sub-categoria, voc&ecirc; precisa criar uma categoria.");
    }
    FormCreateAction("subcategorias", function(){
        let subcategorias = [];

        $("#subcategorias input.m-input").each(function(i){
            if((v=$(this).val()).length==0 && i != 0){
                $(this).closest("[data-repeater-item]").find("[data-repeater-delete]")[0].click();
            } else {
                subcategorias.push({txt:v,vinculo:$(this).parent().find("select:first").val()});
            }
        });

        if(subcategorias.length>0&&typeof subcategorias[0].txt !== "undefined" && (subcategorias[0].txt.length>0 && subcategorias[0].vinculo !== null)){
            $("[data-repeater-create]")[0].click();
        } else {
            return errorRequest(null, "Voce precisa cadastrar ao menos uma subcategoria.");
        }

        dados = {data: subcategorias};

        $.post("{myurl}", dados, function(success){
            if(success===true){
                successRequest(null, "As subcategorias foram atualizadas!");
            } else {
                errorRequest(refresh);
            }
        });
    });

    One("[data-repeater-create]:first").click(()=>$("select.m_selectpicker").selectpicker());

    let i = 0;
    for(content of {valuesof}){
        content.txt.length>0&&(
            $("[data-repeater-create]")[0].click(),

            $("#subcategorias input.m-input").eq(i).val(content.txt),

            $("#subcategorias input.m-input").eq(i).parent().find("select").val(content.vinculo).selectpicker('refresh')
        );

        i++;
    }

    One(".pesquisa").keyup(function(){
        $conteudo = this.value.toLowerCase().split(/[^a-z 0-9]/).join("");
        if($conteudo == ""){
            $("[data-repeater-item]").show();
        } else {
            $conteudo = $conteudo.split(" ");
            $("[data-repeater-item]").each(function(){
                for( termo of $conteudo ){
                    if($(this).find("input").val()=="" || ($(this).find("input").val().toLowerCase().split(termo).length > 1 || $(this).find("select option:selected").text().toLowerCase().split(termo).length > 1)){
                        $(this).show();
                    } else {
                        $(this).hide();
                        break;
                    }
                }
            });
        }
    });
});
