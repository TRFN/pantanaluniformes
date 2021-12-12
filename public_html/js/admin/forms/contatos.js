LWDKExec(function(){
    FormCreateAction("contatos", function(){
        let campos = {};

        $("[data-name]").each(function(){
            campos[$(this).data("name")] = $(this).val();
        });

        // console.log(dados);

        // return;

        $.post("{myurl}", campos, function(success){
            if(success===true){
                successRequest(null, "Informa&ccedil;&otilde;es atualizadas com sucesso!");
            } else {
                errorRequest(refresh);
            }
        });
    });

    for(i in {valuesof}){
        $("[data-name=\"" + i + "\"]").val({valuesof}[i]);
    }
});
