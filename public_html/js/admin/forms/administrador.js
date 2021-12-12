LWDKExec(function(){

    FormCreateAction("form_submit", function(data){
        let go = function(to){
            to = /(#)/.test(to)?$(to)[0]:$("[data-name=\"" + to + "\"]")[0];

            setTimeout(()=>$(to).focus()[0].click(), 1100);

            $([document.documentElement, document.body]).animate({
                scrollTop: $(to).offset().top - 150
            }, 900);
        };

        if(data.nome.length < 3 || data.nome.split(" ").length < 2){
            return errorRequest(()=>go("nome"), "Insira um nome v&aacute;lido contendo nome e sobrenome.");
        }

        if(!Regex.Email.test(data.email)){
            return errorRequest(()=>go("email"), "Insira um email v&aacute;lido!");
        }

        if((data.senha.length > 0 && data.senha.length < 4 && "{acao}" == "modificar")||(data.senha.length < 4 && "{acao}" == "criar")){
            return errorRequest(()=>go("senha"), "Insira uma senha com ao menos 4 caracteres" + ("{acao}"=="modificar"?", <br>caso contr&aacute;rio, deixe em branco para n&atilde;o alterar.":"."));
        }

        $.post("{myurl}", data, function(success){
            if(success===true){
                successRequest(()=>Go("{page}/listar"));
            } else {
                errorRequest(refresh);
            }
        });
    });
});
