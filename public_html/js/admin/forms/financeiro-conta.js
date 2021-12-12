LWDKExec(function(){
$(".pesquisa").keyup(function(){
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

	const setValues = window.setValues = function setValues(prods){
        for (let j = 0; j < prods.length - 1; j++) {
            $("[data-repeater-create]:first").each(function () {
                this.click();
            });
        }
        for (let j = 0; j < prods.length; j++) {
            for (let k in prods[j]) {
                $("[data-repeater-item]")
                    .eq(j)
                    .find('[data-name="' + k + '"]')
                    .val(prods[j][k]);
            }
        }
	};

	const getValues = window.getValues = function getValues(){

			let contas = repeaterGetData(".conta-fixa","data");

		return {"contas": contas, "id": "{myid}"};
	};

	setTimeout(()=>setValues({data-edit}),1000);

    FormCreateAction("dados_contas_geral", function(){
        $.post("{myurl}", {cadfin: getValues()}, function(success){
            if(success===true){
                successRequest((function(){window.top.location.href="/admin/financeiro/listar/";}), "O lançamento foi {acao} com sucesso!");
            } else {
                errorRequest(refresh);
            }
			// console.log(success);
        });
    });

	// console.log({data-edit});
});
