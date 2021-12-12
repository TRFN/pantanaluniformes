LWDKExec(function(){
    FormCreateAction("categorias", function(){
        let categorias = [];

        $("input.m-input").each(function(i){
            if((v=$(this).val()).length==0 && i != 0){
                $(this).closest("[data-repeater-item]").find("[data-repeater-delete]")[0].click();
            } else {
                categorias.push(v);
            }
        });

        if(categorias.length>0&&categorias[0].length>0){
            $("[data-repeater-create]")[0].click();
        } else {
            return errorRequest(null, "Voce precisa cadastrar ao menos uma categoria.");
        }

        dados = {data: categorias};

        $.post("{myurl}", dados, function(success){
            if(success===true){
                successRequest(null, "As categorias foram atualizadas!");
            } else {
                errorRequest(refresh);
            }
        });
    });

    let i = 0;
    for(content of {valuesof}){
        content.length>0&&$("[data-repeater-create]")[0].click();

        $("#categorias input.m-input").eq(i).val(content);

        i++;
    }
});
