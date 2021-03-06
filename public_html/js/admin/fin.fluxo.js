window.f_checksum = "";

window.apiDate = function(input){
    this.backup = input;
    this.input = new Date(typeof input !== "string" ? input : (input + "T12:00:00"));
    this.time = function(){ return this.input.getTime(); };
    this.get = function(){return this.input;};
    this._model = function(fn,val=1,op=1){
        return(this.input["set" + fn](this.input["get" + fn]() + (val*op)));
    };

    this.sumDay = function(q=1){
        return new Date(this._model("Date", q, 1));
    };

    this.sumMonth = function(q=1){
        return new Date(this._model("Month", q, 1));
    };

    this.sumYear = function(q=1){
        return new Date(this._model("Month", q*12, 1));
    };

    this.subDay = function(q=1){
        return new Date(this._model("Date", q, -1));
    };

    this.subMonth = function(q=1){
        return new Date(this._model("Month", q, -1));
    };

    this.subYear = function(q=1){
        return new Date(this._model("Month", q*12, -1));
    };
};

window.mudar_status = function(ctx){
	0&&console.log(ctx);
	$.post("/admin/change_status/", {data: ctx}, ()=>$(".m-tooltip").remove());
};

window.ano_atual = "{year}";

window.menor_data = [-1,-1];
window.maior_data = [-1,-1];

window.apagar_acao = (function(the){
	let myid = $(the).parent().find("a:first").data("my-id");
	One(the, myid).click(function(){
		0&&console.log(myid);
		confirm_msg("<h4>Deseja mesmo apagar recursivamente este registro?</h4><h6>Isto poderá implicar em outros lançamentos gerados a partir deste.</h6>", function(){
			$.post("/admin/financeiro_form_erase/",{id: myid}, function(){
				Swal.fire("","O dado foi apagado completamente.","success");
				finupdt();
			});
		});
		return false;
	});
});

window.finupdt = ((callback=-1, args=-1)=>{
	args === -1 && (args = {});

	function g_money(data){
		return parseFloat(data.split(/[^0-9\,]/).join('').split(",").join("."));
	}

	function s_money(data){
		return data.toLocaleString('pt-br',{style: 'currency', currency: 'BRL'});
	}

	function ksort(obj){
	  var keys = Object.keys(obj).sort(function(a, b){return b-a})
	    , sortedObj = {};

	  for(var i in keys) {
	    sortedObj[4000-parseInt(keys[i])] = obj[keys[i]];
	  }
	  // 0&&console.log(sortedObj);
	  return Object.values(sortedObj);
	}

	$(".nav-item.nav-link:not(.mes-atual)").css({"font-weight": "400"});
	$(".nav-item.nav-link.active:not(.mes-atual)").css({"font-weight": "bold"});

	function proccess_data(data){
		if($("#anos option").length > 0 && window.ano_atual == "-"){
			window.ano_atual = $("#anos option:first").attr("value");
			$("#anos").val(ano_atual);
		}

		function atualizarTabelas(){
			for(let i = 1; i < 13; i++){
				let varr = "DataTable__" + (table_html=$(".mes-"+String(i)).find("table")).attr("id").split(/[^a-z]/).join('_');
				// 0&&console.log(varr);
				table = window[varr];

				$(".receita-" + String(i)).text("R$ 0,00");
				$(".despesas-" + String(i)).text("R$ 0,00");

				table.rows().remove().draw(true);
			}
		}

		data.checksum += window.ano_atual.split(/[^0-9]/).join('');

		if(data.checksum !== f_checksum){
			// atualizarTabelas();
			f_checksum = data.checksum;
		} else {
			// atualizarTabelas();
			// data.valores.length == 0 && ($("#anos").html("<option disabled selected>dados de&nbsp;{year}</option>"),atualizarTabelas());
			return setTimeout(finupdt, 150);
		}

		saldo = 0;
		anos = {"{year}": `<option value={year}>dados de&nbsp;{year}</option>`};
		entrada = 0;
		saida = 0;

		grafico_entrada = [];
		grafico_saida = [];
		meses_txt = ["Jan","Fev","Mar","Abr","Mai","Jun","Jul","Ago","Set","Out","Nov","Dez"];

		// atualizarTabelas();

		for(ano in data.valores){
			let __ano = ano;
			let sl = ano_atual == ano ? 'selected="selected" ':'';
			anos[__ano] = `<option ${sl} value=${__ano}>dados de ${__ano}</option>`;

			let d_ano = data.valores[ano];
			ano_atual === ano && (atualizarTabelas(),grafico_entrada=[],grafico_saida=[]);

			if(ano_atual === ano){
				for(i of meses_txt){
					grafico_entrada.push({key: i, value: 0});
					grafico_saida.push({key: i, value: 0});
				}
			}

			for(mes in d_ano.itens){
				let d_mes = d_ano.itens[mes];
				let entrada_mes = 0;
				let saida_mes = 0;
				let row = 0;

				ano_atual === ano && (grafico_dia_entrada=[],grafico_dia_saida=[]);

				if(ano_atual === ano){
					for(i = 1; i <= (new Date(ano, mes, 0)).getDate(); i++){
						grafico_dia_entrada.push({key: i < 10 ? ("0" + String(i)) : i, value: 0});
						grafico_dia_saida.push({key: i < 10 ? ("0" + String(i)) : i, value: 0});
					}
				}

				// 0&&console.log();

				// ano_atual == ano && table
				// 	.rows()
				// 	.remove()
				// 	.draw(true);

				// ano_atual == "2020" && 0&&console.log([table, ano_atual, ano]);

				for(dia in d_mes.itens){
					let d_dia = d_mes.itens[dia];

					table = window["DataTable__" + (table_html=$(".mes-"+parseInt(mes)).find("table")).attr("id").split(/[^a-z]/).join('_')];


					let entrada_dia = 0;
					let saida_dia = 0;

					for(dado of d_dia.itens){
						if(dado.sect == "saida"){
							// 0&&console.log(dado);
						}

						(typeof dado.pago == "undefined" && (dado.pago = "not"));

						// console.log(dado);

						if((dado.pago != "not")){
							switch (dado.sect) {
								case "entrada":
									if(typeof dado.nomeText == "undefined" || (typeof dado.statusVenda !== "undefined" && dado.statusVenda == "ok") ){
										entrada_mes += g_money(dado.valor);
										entrada_dia += g_money(dado.valor);
										ano_atual == ano && (entrada += g_money(dado.valor));
										saldo += g_money(dado.valor);
									}
								break;
								case "saida":
									0&&console.log(saida_mes);
									saida_mes += g_money(dado.valor);
									saida_dia += g_money(dado.valor);
									ano_atual == ano && (saida += g_money(dado.valor));
									saldo -= g_money(dado.valor);
								break;
								default:
									0&&console.log(dado.sect);
								break;
							}
						}

						(menor_data[0] == -1 || menor_data[0] > dado.vars.date) ? (menor_data = [dado.vars.date, dado.vars.string_date]):((maior_data[0] == -1 || maior_data[0] < dado.vars.date) && (maior_data = [dado.vars.date, dado.vars.string_date]));

						// 0&&console.log(maior_data);

						let myid = dado.id;

						typeof dado.nomeText == "undefined" && (dado.cliente=dado.id,dado.vendedor="'"+dado.sect+"'",dado.imovel=-1,dado.kdta=dado.data);

						let sts = typeof dado.pago == "undefined"
							? ((dado.vars.date > dado.vars.now ? ('<div class="d-block text-center"><span class="text-uppercase badge badge-danger p-2" style="font-size: 12px; border-radius: 3px; text-shadow: 0px 0px 1px #0005;">'+(dado.sect=="entrada"?"a entrar":"a sair")+'</span></div>'):('<div class="d-block text-center"><span class="text-uppercase badge badge-success p-2" style="font-size: 12px; border-radius: 3px; text-shadow: 0px 0px 1px #0005;">ok</span></div>')))
							: typeof dado.nomeText !== "undefined" ? dado.pago : ((dado.pago == "not" ? ('<div class="d-block text-center"><span class="text-uppercase badge badge-danger p-2" style="font-size: 12px; border-radius: 3px; text-shadow: 0px 0px 1px #0005;">PENDENTE</span></div>'):('<div class="d-block text-center"><span class="text-uppercase badge badge-success p-2" style="font-size: 12px; border-radius: 3px; text-shadow: 0px 0px 1px #0005;">PAGO</span></div>')));
							0&&console.log("{sessao-nivel_acesso}");

						let actn = typeof dado.nomeText == "undefined" ? (dado.pago == "not" ? '<div class="d-inline-block text-center mb-3 mx-1" data-skin="white" data-toggle="m-tooltip" data-placement="top" title="" data-original-title="Mudar para Pago"><span onclick="mudar_status(['+dado.cliente+','+dado.vendedor+','+dado.imovel+',\''+dado.kdta+'\',1]); return false;" class="mt-3 text-uppercase badge badge-dark p-2" style="font-size: 12px; cursor: pointer; border-radius: 3px; text-shadow: 0px 0px 1px #0005;"><i style="font-size: 2rem!important;" class="la la-thumbs-up"></i></span></div>'
						: '<div class="d-inline-block text-center mb-3 mx-1" data-skin="white" data-toggle="m-tooltip" data-placement="top" title="" data-original-title="Mudar para Pendente"><span onclick="mudar_status(['+dado.cliente+','+dado.vendedor+','+dado.imovel+',\''+dado.kdta+'\',0]); return false;" class="mt-3 text-uppercase badge badge-danger p-2" style="font-size: 12px; cursor: pointer; border-radius: 3px; text-shadow: 0px 0px 1px #0005;"><i style="font-size: 2rem!important;" class="la la-thumbs-down"></i></span></div>'):sts;
						typeof dado.nomeText !== "undefined" && (sts = '<div class="d-block text-center">-></div>');
						typeof dado.nomeText == "undefined" && (actn += '<div class="d-inline-block mt-3 text-center"><span class="acoes mt-3 d-block"><a class="d-inline-block acoes text-center mb-3 mx-1 text-uppercase badge badge-info p-2" style="font-size: 12px; cursor: pointer; border-radius: 3px; text-shadow: 0px 0px 1px #0005;" data-skin="white" data-toggle="m-tooltip" data-placement="left" title="" data-original-title="Modificar ' + dado.sect + '" href="/financeiro_editar_'+dado.sect+'/'+dado.id+'/" data-my-id="'+dado.id+'" ajax=on><i class="la la-pencil-alt" style="font-size: 2rem!important;"></i></a><a href="#" class="d-inline-block acoes text-center mb-3 mx-1 text-uppercase badge badge-danger p-2" style="font-size: 12px; cursor: pointer; border-radius: 3px; text-shadow: 0px 0px 1px #0005;" data-skin="white" data-toggle="m-tooltip" data-placement="bottom" title="" data-original-title="Apagar ' + dado.sect + '" onclick="apagar_acao(this); return false;" class="apagar btn btn-sm m-btn btn-outline-danger text-uppercase mx-2"><i style="font-size: 2rem!important;" class="la la-trash"></i></a></span></div>');
						let frma = dado.sect == "entrada" ? (
							typeof dado.forma == "undefined" ? (
								 dado.tipo == "E-Commerce" ? "Crédito" : "Dinheiro"
							)
							: dado.forma
						):"";(frma.length > 0) && (frma = `<div class="d-block text-center"><span class="text-uppercase badge badge-dark p-2 my-1" style="font-size: 12px; border-radius: 3px; text-shadow: 0px 0px 1px #0005;">${frma}</span></div>`);
						'<div class="d-block text-center"><span class="text-uppercase badge badge-info p-2" style="font-size: 12px; border-radius: 3px; text-shadow: 0px 0px 1px #0005;">'+(dado.sect=="entrada"?(dado.tipo):dado.sect)+'</span></div>'
						let dt = [
							'<b>DIA '+String(dia)+'</b>',
							dado.nome,
							'<div class="d-block text-center" style="width: 120px; transform: scale(1.25)">' + dado.valor + '</div>',
							'<div class="d-block text-center"><span class="text-uppercase badge badge-info p-2" style="font-size: 12px; border-radius: 3px; text-shadow: 0px 0px 1px #0005;">'+(dado.sect=="entrada"?(dado.tipo):dado.sect)+'</span></div>' + frma,
							sts,
							'<div class="d-block text-center">' + actn + '</div>'
						];
						0&&console.log(dt);
						// 0&&console.log(ano_atual==ano,dt,table);
						ano_atual == ano && table.row.add(dt).draw( true );
						// row++;
					}

					ano_atual == ano && (grafico_dia_entrada[parseInt(dia)-1] = ({key: dia, value: entrada_dia}));
					ano_atual == ano && (grafico_dia_saida[parseInt(dia)-1] = ({key: dia, value: saida_dia}));
				}

				ano_atual == ano && $(".receita-" + String(parseInt(mes))).text(s_money(entrada_mes));
				ano_atual == ano && $(".despesas-" + String(parseInt(mes))).text(s_money(saida_mes));
				ano_atual == ano && $(".grafico1-" + String(parseInt(mes))).simpleBarGraph({
				  data: grafico_dia_entrada,
				  barsColor: '#36a3f7',
				  height:'170px'
				});
				// 0&&console.log(grafico_dia_saida);
				ano_atual == ano && $(".grafico2-" + String(parseInt(mes))).simpleBarGraph({
				  data: grafico_dia_saida,
				  barsColor: '#a01915',
				  height:'170px'
				});
				ano_atual == ano && (grafico_entrada[parseInt(mes)-1] = ({key: meses_txt[parseInt(mes)-1], value: entrada_mes}));
				ano_atual == ano && (grafico_saida[parseInt(mes)-1] = ({key: meses_txt[parseInt(mes)-1], value: saida_mes}));
			}
		}

		// ano = ano_atual;

		$(".ano").text(ano_atual);

		$(".saldo").text(s_money(saldo)).css({color: saldo < 0 ? "#a01915":"#36a3f7"});
		$(".receita").text(s_money(entrada));
		$(".despesas").text(s_money(saida));
		$('.grafico_entrada').simpleBarGraph({
		  data: grafico_entrada,
		  barsColor: '#36a3f7',
		  height:'250px'
		});
		$('.grafico_saida').simpleBarGraph({
		  data: grafico_saida,
		  barsColor: '#a01915',
		  height:'250px'

		});
		// 0&&console.log(ksort(anos));
		$("#anos").html(ksort(anos).join(""));
		// 0&&console.log([grafico_saida,grafico_entrada]);

		// 0&&console.log([maior_data,menor_data]);

		$("#exportacao .date").attr("max",maior_data[1]);
		$("#exportacao .date").attr("min",menor_data[1]);
		$("#exportacao .main").attr("value",menor_data[1]);
		$("#exportacao .ends").attr("value",maior_data[1]);

		LWDKInitFunction.exec();

		setTimeout(finupdt, 250);
	}

	$.post("/admin/financeiro_data/", args, typeof callback!=="function" ? proccess_data:callback);
});

LWDKExec(()=>setTimeout(()=>(finupdt(), setTimeout(()=>$("#anos").change(function(){
	if($(this).val() === false)return;

	ano_atual = $(this).val();
	f_checksum = "upt";
}),1000)), 600));

LWDKExec(()=>$('a[data-toggle="tab"]').on('shown.bs.tab', function(e){
   $($.fn.dataTable.tables(true)).DataTable()
      .columns.adjust();
}));

window.applyCli = [];
window.applyBank = [];

// LWDKExec(()=>$.post("/get_automatico/", (precad) => {
// 	applyCli = []; applyBank = [];
//
// 	for(dt of precad){
// 		applyCli.push(parseInt(dt.cliente));
// 		applyBank[dt.dados.id] = dt.cliente;
// 	}
//
// 	// 0&&console.log(applyCli);
// }));

LWDKExec(()=>($('.animated-btn > i').css({"transform":"scale(1.2)","transition":"all 600ms ease"}),$('.animated-btn').css({"border-radius":"2.5rem"}).animate({opacity:1}), setTimeout(()=>$('.animated-btn').each(function(i){
	$(this).find("> span").each(function(){
		$(this).data("orig-width", $(this).width());
	});
	let t = 150;
	$(this).find("> span").delay($(this).data("anim-delay")).animate({"width":"0px"}, t);
	$(this).find("> i").delay($(this).data("anim-delay")*2).css({"transform":"scale(1.6)"});
	$(this).delay($(this).data("anim-delay")).animate({"border-radius": "100%"}, t);
	// $(this).find("> i").css({"font-size": "18px"});
	$(this).mouseenter(function(){
		$(this).find("> span").css({"width":$(this).find("> span").data("orig-width")});
		$(this).css({"border-radius": "2.5rem"});
		$(this).find("> i").css({"transform":"scale(1.2)"});
	}).mouseleave(function(){
		$(this).find("> span").animate({"width":"0px"}, 300, ()=>($(this).animate({"border-radius": "100%"}, 100),$(this).find("> i").css({"transform":"scale(1.6)"})));
	});
}),1800)));

window.exportar_financeiro = function(fmt){
	let cfg = GetFormData("#exportacao"), query = [];

	for( i in cfg){
		query.push(i + "=" + encodeURIComponent(cfg[i]));
	}

	query = query.join("&");

	window.open("/admin/financeiro_gerar_relatorio?" + query);
}

LWDKExec(()=>{
	var historico = GetFormData("#exportacao");
	function verifyLoja(){
		let cfg = GetFormData("#exportacao");
		if(cfg["loja-virtual"] == 1 && (cfg["entrada"] == 1 || cfg["saida"] == 1) && historico["loja-virtual"] == 0){
			$("#exportacao [data-name=\"entrada\"]").prop("checked", false);
			$("#exportacao [data-name=\"saida\"]").prop("checked", false);
		} else if(cfg["loja-virtual"] == 1 && (cfg["entrada"] == 1 || cfg["saida"] == 1) && (historico["entrada"] == 0 || historico["saida"] == 0)){
			$("#exportacao [data-name=\"loja-virtual\"]").prop("checked", false);
		}
		historico = GetFormData("#exportacao");
		setTimeout(verifyLoja, 250);
	}
	verifyLoja();
});



var pgsupd = (() => {
	$(".status-pagseguro:not(.modified)").length > 0 ? $.post("/admin/ajax_status_vendas/", function(data){
		$(".status-pagseguro:not(.modified)").each(function(){
			$(this).addClass("modified");
			let id = $(this).data("id");
			if(typeof data[id] != 'undefined'){
				$(this).html(data[id][0]);
				$(this).css({backgroundColor: data[id][1]});
				$.post("/admin/bkpgs/", {"id": id, status: data[id][0] == "Pago" ? "ok":"not"});
			} else {
				$(this).html("N\u00e3o Conclu\u00eddo");
				$(this).css({backgroundColor: "#ab0000"});
			}
		});

		setTimeout(pgsupd, 300);
	}):setTimeout(pgsupd, 600);
});

LWDKExec(pgsupd);

/*cfg["fmt"] = fmt;

finupdt(function(data){
	let dados = [];

	for(let ano in data.valores){
		let d_ano = data.valores[ano];
		for(let mes in d_ano.itens){
			let d_mes = d_ano.itens[mes];
			for(let dia in d_mes.itens){
				let d_dia = d_mes.itens[dia];
				for(dado of d_dia.itens){
					dataDado = (new Date(dado.vars.string_date)).getTime();
					dataInicio = (new Date(cfg["data-inicio"])).getTime();
					dataFim = (new Date(cfg["data-final"])).getTime();

					if(dataDado >= dataInicio && dataDado <= dataFim){
						dados.push([dataDado, {
							titulo: dado.nome,
							valor: dado.valor,
							tipo: (dado.sect=="entrada"?dado.tipo:dado.sect),
							status: dado.vars.date > dado.vars.now ? (dado.sect=="entrada"?"a entrar":"a sair"):'OK',
							data: dado.vars.string_date
						}]);
					}
				}
			}
		}
	}

	cfg["classificacao"] = cfg["classificacao"].split(";");
	keys = [new apiDate(cfg["data-inicio"]), new apiDate(cfg["data-final"])];
	comm = ["sum"+cfg["classificacao"][0],parseInt(cfg["classificacao"][1])];
	keys[0]["sub"+cfg["classificacao"][0]](comm[1]);
	prev = [];
	// return console.log(keys[0].time(),keys[1].time());

	while(keys[0].time() < keys[1].time()){
		let prev_date = keys[0].time();
		keys[0][comm[0]](comm[1]);
		prev.push([prev_date, keys[0].time()]);
	}

	console.log(dados);

	let tratados = new Array();

	for(let dado of dados){
		let not = false;

		not = not || (cfg["entrada"] == "0" && dado.sect == "entrada");
		not = not || (cfg["saida"] == "0" && dado.sect == "saida");

		if(!not){
			for(let key = 0; key < prev.length; key++){
				let dt = (new apiDate(dado[1]["data"])).time();
				if(dt >= prev[key][0] && dt <= prev[key][1]){
					if(typeof tratados[key] == "undefined"){
						tratados[key] = new Object();
					}

					for(let c in dado[1]){
						if(cfg[c] == "1" || c == "data"){
							if(typeof tratados[key][c] == "undefined"){
								tratados[key][c] = [dado[1][c]];
							} else {
								tratados[key][c].push(dado[1][c]);
							}
						}
					}
					break;
				}
			}
		}
	}

	let meses_txt = [["Jan", "Janeiro"],["Fev", "Fevereiro"],["Mar", "Março"],["Abr", "Abril"],["Mai", "Maio"],["Jun", "Junho"],["Jul", "Julho"],["Ago", "Agosto"],["Set", "Setembro"],["Out", "Outubro"],["Nov", "Novembro"],["Dez", "Dezembro"]];

	let dias_txt = [
		["Dom","Domingo"],
		["Seg","Segunda"],
		["Ter","Terça-Feira"],
		["Qua","Quarta-Feira"],
		["Qui","Quinta-Feira"],
		["Sex","Sexta-Feira"],
		["Sáb","Sábado"]
	];

	for( let n = 0; n < tratados.length; n++ ){
		if(typeof tratados[n] !== "object" || tratados[n] == null || tratados[n].length < 1){
			tratados.slice(n, 1);
		} else {
			for(let dt of tratados[n].data){
				dia = (new Date(dt)).getDay();
				mes = (new Date(dt)).getMonth();
				ano = (new Date(dt)).getFullYear();

				if(typeof tratados[n].mes == 'undefined'){
					tratados[n].mes = [JSON.stringify(meses_txt[mes])];
				} else {
					tratados[n].mes.push(JSON.stringify(meses_txt[mes]));
				}
				if(typeof tratados[n].ano == 'undefined'){
					tratados[n].ano = [ano];
				} else {
					tratados[n].ano.push(ano);
				}

				if(typeof tratados[n].dia == 'undefined'){
					tratados[n].dia = [dia];
				} else {
					tratados[n].dia.push(dia);
				}
			}
			tratados[n].mes = Object.values(tratados[n].mes);

			// tratados[n].mes = tratados[n].mes.filter(function(item, pos) {
			//     return tratados[n].mes.indexOf(item) == pos;
			// });
		}
	}

	relatorio = [];

	for( let dado of tratados ){
		if(typeof dado == "object" && dado != null){
			dadoLinha = [];
			datas = [dado.data[0], dado.data[dado.data.length-1]];
			datas[0] = datas[0].split("-");
			datas[0] = datas[0][2] + "/" +  datas[0][1] + "/" +  datas[0][0];

			datas[1] = datas[1].split("-");
			datas[1] = datas[1][2] + "/" +  datas[1][1] + "/" +  datas[1][0];
			dado.dias = [];
			for(de = 0; de < dado.data.length; de++){
				dado.data[de] = dado.data[de].split("-");
				dado.dias[de] = function(d){return d}(dado.data[de][2]);
				dado.data[de] = dado.data[de][2] + "/" +  dado.data[de][1] + "/" + dado.data[de][0];
			}

			dadoLinha.push("Dados entre " + datas[0] + " e " + datas[1]);

			if(typeof relatorio[relatorio.length-1] !== "undefined" && relatorio[relatorio.length-1][0].length > 3){
				relatorio.push([""]);
			}

			relatorio.push(dadoLinha);
			dadoLinha = [];

			for(me = 0; me < dado.mes.length; me++){
				dado.mes[me] = JSON.parse(dado.mes[me]);
				dado.mes[me] = dado.mes[me][parseInt(cfg.abrev)==1?0:1];
			}

			dado2 = {
				data: []
			};

			for(de = 0; de < dado.dia.length; de++){
				dado.dia[de] = dias_txt[dado.dia[de]][parseInt(cfg.abrev)==1?0:1];
				dado2.data[de] = dado.dia[de] + ", " + dado.dias[de].padStart(2, '0') + " de " + dado.mes[de] + " de " + dado.ano[de] + " (" + dado.data[de] + ")";
			}

			delete dado.dia;delete dado.dias;delete dado.mes;delete dado.ano;

			for(iu in dado){
				if(typeof dado2[iu] == "undefined"){
					dado2[iu] = dado[iu];
				}
			}

			for(title of Object.keys(dado2)){
				dadoLinha.push(title.toUpperCase());
			}

			dadoLinha.push("TOTAL PERIODO");

			relatorio.push(dadoLinha);
			saida = [];

			// console.log(dado);

			for(d1 of Object.values(dado2)){
				di = 0;
				for(d2 of Object.values(d1)){
					if(typeof saida[di] == "undefined"){
						saida[di] = [];
					}
					saida[di].push(d2);
					di++;
				}
			}

			for(let dadoLinha of saida){
				relatorio.push(dadoLinha);
			}

			dadoLinha = [];
		}
	}

	$.post("/admin/financeiro_gerar_relatorio/", {config: cfg, data: relatorio}, function(link){
		// window.top.location.href = link;
		window.open("/" + link);
		setTimeout(()=>$.post("/admin/financeiro_apagar/", {arq: link}), 5000);
	});
});*/
