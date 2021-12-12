LWDKExec(function(){
	window['buscar_cep']=function buscar_cep(_0x257523){
		cep=_0x257523['value']['split'](/[^0-9]/)['join']('');if(cep['length']>0x8)$(_0x257523)['addClass']('is-invalid'),$(_0x257523)['closest']('#dados_aluguel')['find']('[data-name=\x22rua\x22]')['val'](''),$(_0x257523)['closest']('#dados_aluguel')['find']('[data-name=\x22bairro\x22]')['val'](''),$(_0x257523)['closest']('#dados_aluguel')['find']('[data-name=\x22complemento\x22]')['val']('')['prop']('disabled',!![]),$(_0x257523)['closest']('#dados_aluguel')['find']('[data-name=\x22cidade\x22]')['val'](''),$(_0x257523)['closest']('#dados_aluguel')['find']('[data-name=\x22estado\x22]')['val'](''),$(_0x257523)['closest']('#dados_aluguel')['find']('[data-name=\x22numero\x22]')['val']('')['prop']('disabled',!![]);else cep['length']==0x8?(swal['fire']({'title':'','text':'Buscando\x20cep,\x20aguarde...','showCancelButton':![],'showConfirmButton':![]}),$['get']('//viacep.com.br/ws/'+cep+'/json/',function(_0x685447){typeof _0x685447['erro']=='boolean'&&_0x685447['erro']?($(_0x257523)['addClass']('is-invalid'),$(_0x257523)['closest']('#dados_aluguel')['find']('[data-name=\x22rua\x22]')['val'](''),$(_0x257523)['closest']('#dados_aluguel')['find']('[data-name=\x22bairro\x22]')['val'](''),$(_0x257523)['closest']('#dados_aluguel')['find']('[data-name=\x22complemento\x22]')['val']('')['prop']('disabled',!![]),$(_0x257523)['closest']('#dados_aluguel')['find']('[data-name=\x22cidade\x22]')['val'](''),$(_0x257523)['closest']('#dados_aluguel')['find']('[data-name=\x22estado\x22]')['val'](''),$(_0x257523)['closest']('#dados_aluguel')['find']('[data-name=\x22numero\x22]')['val']('')['prop']('disabled',!![]),swal['fire']('','Cep\x20incorreto.','error')):($(_0x257523)['removeClass']('is-invalid'),$(_0x257523)['val'](_0x685447['cep']),$(_0x257523)['closest']('#dados_aluguel')['find']('[data-name=\x22rua\x22]')['val'](_0x685447['logradouro']),$(_0x257523)['closest']('#dados_aluguel')['find']('[data-name=\x22bairro\x22]')['val'](_0x685447['bairro']),$(_0x257523)['closest']('#dados_aluguel')['find']('[data-name=\x22complemento\x22]')['val'](_0x685447['complemento'])['prop']('disabled',![]),$(_0x257523)['closest']('#dados_aluguel')['find']('[data-name=\x22cidade\x22]')['val'](_0x685447['localidade']['toUpperCase']()),$(_0x257523)['closest']('#dados_aluguel')['find']('[data-name=\x22estado\x22]')['val'](_0x685447['uf']['toUpperCase']()),$(_0x257523)['closest']('#dados_aluguel')['find']('[data-name=\x22numero\x22]')['prop']('disabled',![]),swal['close']());})):$(_0x257523)['removeClass']('is-invalid');return!![];};

	window.listar_produto = function listar_produto(ref){
		let json = JSON.parse((ref=$(ref)).val()),
			parent = ref.closest(".produto-aluguel"),
			set = (key,val) => parent.find(`[data-name="${key}"]`).val(val),
			money = (float) => float.toLocaleString('pt-br',{style: 'currency', currency: 'BRL'}),
			now = (qtd=0,f="Y-m-d") => {
				let data = new Date(),
					dia  = String(data.getDate()).padStart(2, '0'),
					mes  = String(data.getMonth() + 1).padStart(2, '0'),
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
		set("dano", money(json.valorFloat * .3));
		set("transporte", money(0));
		set("devolucao", now(30));
	}

	const setValues = window.setValues = function setValues(prods){
        for (let j = 0; j < prods.length - 1; j++) {
            $("[data-repeater-create]").each(function () {
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
		let contrato = MapKeyAssign(MapEl("[data-name]:not(.prod)", function(data){return [$(this).data("name"),this.value];}, false, false));
		contrato.produtos = MapEl("[data-name].prod", function(data){return [$(this).data("name"),this.value];}, false, false).chunk(12);
		for(let i = 0; i < contrato.produtos.length; i++){
			contrato.produtos[i] = MapKeyAssign(contrato.produtos[i]);
		}
		return contrato;
	};

	setTimeout(()=>setValues({produtos}),1000);

    FormCreateAction("dados_aluguel", function(){
        $.post("{myurl}", {cadaluguel: getValues()}, function(success){
            if(success===true){
                successRequest(("{acao}" === "criado" ? function(){window.top.location.href="/admin/alugueis/listar/";}:null, "O contrato do aluguel foi {acao} com sucesso!"));
            } else {
                errorRequest(refresh);
            }
        });
    });
});
