LWDKExec(()=>{
	function mask_inputs(){
		setTimeout(()=>(
			$("input[type=text].c_number").mask("9999 9999 9999 9999"),
			$("input[type=text].c_cvv").mask("999"),
			$("input[type=text].c_expires").mask("99/99")
		), 800);
	}

	$("#gerenciar-cartoes [data-repeater-create]").click(mask_inputs);

	window['buscar_cep']=()=>{let _0x4fa1b3=$('[data-name=\x22c_cep\x22]')['val']()['split'](/[^0-9]/)['join']('');_0x4fa1b3['length']==0x8&&(swal['fire']({'title':'','text':'Buscando\x20cep,\x20aguarde...','showCancelButton':![],'showConfirmButton':![]}),$('[data-name=\x22c_rua\x22]')['val']('...'),$('[data-name=\x22c_bairro\x22]')['val']('...'),$('[data-name=\x22c_cidade\x22]')['val']('...'),$('[data-name=\x22c_estado\x22]')['val']('...'),$('[data-name=\x22c_numero\x22]')['val']('')['prop']('disabled',!![]),$['get']('//viacep.com.br/ws/'+_0x4fa1b3+'/json/',function(_0x3aea2c){typeof _0x3aea2c['erro']=='boolean'&&_0x3aea2c['erro']?($('[data-name=\x22c_rua\x22]')['val'](''),$('[data-name=\x22c_bairro\x22]')['val'](''),$('[data-name=\x22c_cidade\x22]')['val'](''),$('[data-name=\x22c_estado\x22]')['val'](''),$('[data-name=\x22c_numero\x22]')['val']('')['prop']('disabled',![]),swal['fire']('','Cep\x20incorreto.','error')):($('[data-name=\x22c_rua\x22]')['val'](_0x3aea2c['logradouro']),$('[data-name=\x22c_bairro\x22]')['val'](_0x3aea2c['bairro']),$('[data-name=\x22c_cidade\x22]')['val'](_0x3aea2c['localidade']['toUpperCase']()),$('[data-name=\x22c_estado\x22]')['val'](_0x3aea2c['uf']['toUpperCase']()),$('[data-name=\x22c_numero\x22]')['prop']('disabled',![]),swal['close']());}));};

	// window.buscar_cep = () => {
	// 	let cep = $('[data-name="c_cep"]').val().split(/[^0-9]/).join('');
	// 	if(cep.length == 8){
	// 		swal.fire({
	// 			title: '',
	// 			text: 'Buscando cep, aguarde...',
	// 			showCancelButton: false,
	// 			showConfirmButton: false
	// 		});
	//
	// 		$('[data-name="c_rua"]').val('...');
	// 		$('[data-name="c_bairro"]').val('...');
	// 		$('[data-name="c_cidade"]').val('...');
	// 		$('[data-name="c_estado"]').val('...');
	// 		$('[data-name="c_numero"]').val('').prop("disabled",true);
	//
	// 		$.get(`//viacep.com.br/ws/${cep}/json/`, function(data){
	// 			if(typeof data.erro == "boolean" && data.erro){
	// 				$('[data-name="c_rua"]').val('');
	// 				$('[data-name="c_bairro"]').val('');
	// 				$('[data-name="c_cidade"]').val('');
	// 				$('[data-name="c_estado"]').val('');
	// 				$('[data-name="c_numero"]').val('').prop("disabled",false);
	// 				swal.fire("","Cep incorreto.","error");
	// 			} else {
	// 				$('[data-name="c_rua"]').val(data.logradouro);
	// 				$('[data-name="c_bairro"]').val(data.bairro);
	// 				$('[data-name="c_cidade"]').val(data.localidade.toUpperCase());
	// 				$('[data-name="c_estado"]').val(data.uf.toUpperCase());
	// 				$('[data-name="c_numero"]').prop("disabled",false);
	// 				swal.close();
	// 			}
	// 		});
	// 	}
	// };

	const getValues = function getValues(){
		values = MapEl("#gerenciar-cartoes [data-repeater-item] input[data-name]", function(){
			return [$(this).parent().parent().index()-2, $(this).data("name"),$(this).val()];
		}, !1, !1);b = []; for(i = 0; i < values.length; i += (values.length / $("[data-repeater-item]").length)){ b[values[i]] = {}; for(j = 0; j < 11; j+=3){ b[values[i+j]][values[i+j+1]] = values[i+j+2];}}

		extra = MapKeyAssign(MapEl(".fixed-data input[data-name]", function(){
					return [$(this).data("name"),$(this).val()];
				}, !1, !1));

		for(let i in extra){
			for(let j = 0; j < b.length; j++ ){
				b[j][i] = extra[i];
			}
		}

		return b;
	};

	const setValues = function setValues(values){
		for(let i = 0; i < values.length - 1; i++){
			$("#gerenciar-cartoes [data-repeater-create]").each(function(){
				this.click();
			});
		}

		for(let i = 0; i < values.length; i++){
			for( let j in values[i] ){
				$("#gerenciar-cartoes [data-repeater-item]").eq(i).find("[data-name=\"" + j + "\"]").val(values[i][j]);
				$(".fixed-data [data-name=\"" + j + "\"]").val(values[i][j]);
			}
		}
	};

	setTimeout(()=>setValues({cards}),500);

	$("#gerenciar-cartoes button.submit-form").click(()=>{
		$.post("/action_change_credit_cards/", {d: getValues()}, () => Swal.fire('', 'Seus cart&otilde;es foram atualizados com sucesso!', 'success').then(()=>force_redirect()));
	});

	mask_inputs();
});
