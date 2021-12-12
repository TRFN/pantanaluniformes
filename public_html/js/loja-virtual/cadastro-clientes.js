LWDKExec(()=>One(".cpfcnpj").keydown(function(){
    try {
        $(this).unmask();
    } catch (e) {}

    var tamanho = $(this).val().length;

    if(tamanho < 11){
        $(this).mask("999.999.999-99");
    } else {
        $(this).mask("99.999.999/9999-99");
    }

    // ajustando foco
    var elem = this;
    setTimeout(function(){
        // mudo a posição do seletor
        elem.selectionStart = elem.selectionEnd = 10000;
    }, 0);
    // reaplico o valor para mudar o foco
    var currentValue = $(this).val();
    $(this).val('');
    $(this).val(currentValue);
}));

LWDKExec(()=>One(".tel").mask("(99) 9999-99999")
    .focusout(function (event) {
        var target, phone, element;
        target = (event.currentTarget) ? event.currentTarget : event.srcElement;
        phone = target.value.replace(/\D/g, '');
        element = $(target);
        element.unmask();
        if(phone.length > 10) {
            element.mask("(99) 99999-9999");
        } else {
            element.mask("(99) 9999-99999");
        }
    }));

LWDKExec(()=>FormCreateAction("cadastro-cliente", function(){
    data = MapEl("#cadastro-cliente input", "abstract-basic-form", "object");
    if(data!==false){
        $.post(LWDKLocal, data, function(erros){
            let campos;
            if(typeof erros.length == "undefined" && (campos=Object.keys(erros)).length > 0){
                Swal.fire("", "Preencha os dados corretamente: <br><br><p style='font-size: 12px; color: #851117;'>* " + Object.values(erros).join("</p><p style='font-size: 12px; color: #851117;'>* ") + "</p>", "error").then((result) => {
                    for( campo of campos ){
                        $("input[data-name=" + campo + "]").addClass("is-invalid");
                    }

                    setTimeout("$('input[data-name=" + campos[0] + "]').focus()[0].click();", 800);
                    setTimeout('$("input").removeClass("is-invalid");', 2000);
                });
                return false;
            } else {
                data.acao == "0" && Swal.fire("", "Cadastro efetuado com sucesso!<br />Bem vindo " + $('input[data-name=nome]').val(), "success").then((result) => setTimeout(()=>(!/finalizar_compra/.test(LWDKLocal) ? Go("home"):refresh()),600));
                data.acao == "1" && Swal.fire("", "Seu perfil foi modificado com sucesso.", "info").then((result) => refresh());
            }
        });
    } else {
        refresh();
    }
}));
