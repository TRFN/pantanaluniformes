LWDKExec(function(){
    FormCreateAction("login", function(){
        let dados = MapEl("#login input", "abstract-basic-form", "object");
        if(dados !== false){
            $.post("/cli_ajax/", dados, function(sessao){
                if(sessao){
                    refresh();
                } else {
                    $("#login .error").fadeIn();
                    for(campo of Object.keys(dados)){
                        $("input[data-name=" + campo + "]").addClass("is-invalid");
                    }
                    setTimeout('$("input").removeClass("is-invalid"); $("#login .error").fadeOut();', 3000);
                    setTimeout('$("#login input").eq(0).focus()[0].click();',800);
                }
            });
        }
    });
});
