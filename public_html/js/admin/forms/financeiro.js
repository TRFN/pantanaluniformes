LWDKExec(function(){
	window.listar_produto = function listar_produto(ref){
		let json = JSON.parse((ref=$(ref)).val()),
			parent = ref.closest(".produto-lanc-fin"),
			set = (key,val) => parent.find(`[data-name="${key}"]`).val(val),
			money = (float) => float.toLocaleString('pt-br',{style: 'currency', currency: 'BRL'}),
			now = (qtd=0,f="Y-m-d") => {
				let data = new Date(),
					dia  = String(data.getDate()).padStart(2, '0'),
					mes  = String(data.getMonth()).padStart(2, '0'),
					ano  = data.getFullYear();

				data = new Date((new Date()).setDate((new Date(`${ano}-${mes}-${dia}T00:00:00`)).getDate() + qtd));
				dia  = String(data.getDate()).padStart(2, '0');
				mes  = String(data.getMonth() + 1).padStart(2, '0');
				ano  = data.getFullYear();

				return f.split("Y").join(ano).split("m").join(mes).split("d").join(dia);
			};

		json.valorFloat = parseFloat(json.valor.split(/[^0-9]/).join('')) / 100;

		set("nome", json.nome);
		set("patrimonio", json.codigo);
		set("valor", json.valor);
		// set("dano", money(json.valorFloat * .3));
		// set("transporte", money(0));
		set("obs", "-");
		set("data", now(0));
	}

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

	// const setValues = window.setValues = function setValues(prods){
    //     for (let j = 0; j < prods.length - 1; j++) {
    //         $("[data-repeater-create]").each(function () {
    //             this.click();
    //         });
    //     }
    //     for (let j = 0; j < prods.length; j++) {
    //         for (let k in prods[j]) {
    //             $("[data-repeater-item]")
    //                 .eq(j)
    //                 .find('[data-name="' + k + '"]')
    //                 .val(prods[j][k]);
    //         }
    //     }
	// };

	const getValues = window.getValues = function getValues(){
		let contas = repeaterGetData(".conta-fixa","data");
		let gastos = repeaterGetData(".gasto-var","data");
		let prod = repeaterGetData(".prod","data");

		return {"contas": contas, "gastos": gastos, "prod": prod};
	};

	// setTimeout(()=>setValues({produtos}),1000);

    FormCreateAction("dados_contas_geral", function(){
        $.post("{myurl}", {cadfin: getValues()}, function(success){
            if(success===true){
                successRequest(("{acao}" === "criado" ? function(){window.top.location.href="/admin/financeiro/listar/";}:null), "O lançamento foi {acao} com sucesso!");
            } else {
                errorRequest(refresh);
            }
			// console.log(success);
        });
    });

	// console.log({data-edit});
});
