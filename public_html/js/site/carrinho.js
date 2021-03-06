LWDKExec(()=>$.post("/ajax_carrinho/", {"-act-": "l-cart"}, (data)=>{
	upd_carrinho(data);
	$("#cupom a.btn").click(()=>valorCompraTotal>0?set_cupom($("#cupom input").val()):swal.fire("","Seu carrinho est&aacute; vazio.<br>Adicione produtos para utilizar o cupom.", "info"));
}));

window["__c__"] = 0; window["__fc__"] = []; window.valorCompraTotal = 0;

const get_produto = window.get_produto = function get_produto(item){
	// let produto = false;
	// item = String(item);
	// if(typeof window.produtos !== "undefined"){
	// 	for(let i of produtos){
	// 		if(parseInt(i.id) == parseInt(item)){
	// 			produto = i;
	// 			break;
	// 		}
	// 	}
	// }

	let produto;

	jQuery.ajax({
		method: "post",
		url: '/json_produtos/',
		data: {prod: item},
		success: function(result) {
		   produto = result;
		},
		async: false
    });

	return produto.length ? produto[0]:false;
};

const get_desconto = window.get_desconto = function get_desconto(i,v){
  return __fc__.indexOf(i) == -1 && valorCompraTotal > 5 ? (__c__ += parseFloat(parseFloat(v.split(/[^0-9]/).join('')) / 100), __fc__.push(i), true) : false;
};

const set_cupom = window.set_cupom = function set_cupom(cod){
	if(cod.length==0){
		swal.fire("","Preencha o c&oacute;digo do cupom.", "info");
		return false;
	}

	if(valorCompraTotalFrete < 5){
		swal.fire("","N&atilde;o e poss&iacute;vel usar o cupom<br>porque seu carrinho está com o valor<br>abaixo de R$ 5,00.", "info");
		return false;
	}

	$.post("/check_cupom/", {c: cod, v: valorCompraTotal}, (r) => {
		if(r!==false){
			$.post("/ajax_carrinho/", {"-act-": "l-cart"}, (cart)=>{
				let valor_desc = Math.abs(Math.min((r[1].split(/[^0-9]/).join('')) / 100, valorCompraTotal)).toLocaleString('pt-br',{style: 'currency', currency: 'BRL'});
				if(typeof cart[cod] === "undefined"){
					swal.fire("Cupom aplicado!",`Voc&ecirc;&nbsp;recebeu um desconto sobre o valor da compra.`,
					"success");
					cart[cod] = r[2];
					$.post("/ajax_carrinho/", {"-act-": "s-cart", data: cart}, ()=>upd_carrinho(false));
				} else {
					swal.fire("","O cupom j&aacute; est&aacute; aplicado.", "info");
				}
			});
		} else {
			swal.fire("","O cupom est&aacute; expirado ou n&atilde;o existe.<br>Tente novamente.", "error");
		}
	});
};

const item_carrinho_html = window.item_carrinho_html = function item_carrinho_html(item, qtd=1, m = 0){
	let valor, produto = get_produto(item);

	if(produto == false){return (get_desconto(item, qtd),"");}

	if(produto["valor-a-vista"].length < 7 || produto["valor-a-vista"] === "R$ 0,00"){
		valor = produto["valor"];
	} else {
		valor = produto["valor-a-vista"];
	}

	produto["price"] = valor;

	// console.log(valor);

	let valortotal = parseFloat(parseFloat(valor.split(/[^0-9]/).join('')) / 100) * qtd;

	// alert(valor);

	return [`
		<li class="item">
			<a href="${produto["link"]}" title="${produto["nome"]}" class="product-image"><img src="${produto["img"]}" alt="${produto["nome"]} - ${produto["imgt"]}" /></a>
			<div class="product-details shop">
				<p class="product-name font-weight-semibold">
					${produto["nome"]}
				</p>
				<span class="price"> ${produto["price"]}</span>
				<a href="javascript:;" onclick="remover_carrinho(${produto["id"]}, ${qtd});" title="Remover este item" class="btn-remove"><i class="fas fa-times"></i></a>

				<div style="clear: both"></div>
				<div class="variacao-${produto["id"]}"></div>
				<div style="clear: both"></div>
				<div class="quantity quantity-lg" style="float: right;">
					<input type="button" onclick="remover_carrinho(${produto["id"]}, 1);" class="minus text-color-hover-light bg-color-hover-primary border-color-hover-primary" value="-" />
					<input type="number" readonly class="input-text qty text" title="Qty" value="${qtd}" name="quantity" min="1" step="1" />
					<input type="button" onclick="adicionar_carrinho(${produto["id"]}, 1, false);" class="plus text-color-hover-light bg-color-hover-primary border-color-hover-primary" value="+" />
				</div>
				<div style="clear: both"></div>
			</div>
		</li>`,
		`<tr class="cart_table_item">
			<td class="product-thumbnail" style="vertical-align: middle;">
				<input type="hidden" id="vrc${produto["id"]}" name="variacao[${produto["id"]}]" value="false" />
				<div class="product-thumbnail-wrapper">
					<a href="javascript:;" onclick="remover_carrinho(${produto["id"]}, ${qtd});" class="product-thumbnail-remove">
						<i class="fas fa-times"></i>
					</a>
					<a href="${produto["link"]}" class="product-thumbnail-image" title="Porto Headphone">
						<img width="128px" height="128px" alt="${produto["imgt"]}" src="${produto["img"]}" />
					</a>
				</div>
			</td>
			<td class="variacao-${produto["id"]} p-2 m-1">
				&nbsp;
			</td>
			<td class="product-name" style="vertical-align: middle;">
				<a href="${produto["link"]}" class="font-weight-semi-bold text-color-dark text-color-hover-primary text-decoration-none">${produto["nome"]}</a>
			</td>
			<td class="product-price" style="vertical-align: middle;">
				<span class="amount font-weight-medium text-color-grey">${produto["price"]}</span>
			</td>
			<td class="product-quantity" style="padding-top: 30px;">
				<div class="quantity quantity-lg">
					<input type="button" onclick="remover_carrinho(${produto["id"]}, 1);" class="minus text-color-hover-light bg-color-hover-primary border-color-hover-primary" value="-" />
					<input type="number" readonly class="input-text qty text" title="Qty" value="${qtd}" min="1" step="1" />
					<input type="button" onclick="adicionar_carrinho(${produto["id"]}, 1, false);" class="plus text-color-hover-light bg-color-hover-primary border-color-hover-primary" value="+" />
				</div>
			</td>
			<td class="product-subtotal text-end" style="vertical-align: middle;">
				<span class="amount text-color-dark font-weight-bold text-4">${valortotal.toLocaleString('pt-br',{style: 'currency', currency: 'BRL'})}</span>
			</td>
		</tr>`][m];
};

const upd_carrinho = window.upd_carrinho = function upd_carrinho(__a__ = false){

	let __f__ = (cart)=>{
		cart = typeof cart !== "object" ? {}:cart;
		let valortotal = 0, produtos_totais = 0, outkast = [
			"originalEvent","type","isDefaultPrevented","target","currentTarget","relatedTarget","timeStamp","jQuery35106990624658462914","delegateTarget","handleObj","data","constructor","isPropagationStopped","isImmediatePropagationStopped","isSimulated","preventDefault","stopPropagation","stopImmediatePropagation","altKey","bubbles","cancelable","changedTouches","ctrlKey","detail","eventPhase","metaKey","pageX","pageY","shiftKey","view","char","code","charCode","key","keyCode","button","buttons","clientX","clientY","offsetX","offsetY","pointerId","pointerType","screenX","screenY","targetTouches","toElement","touches","which"
		];

		$(".mini-products-list").html("");

		let preserve = $("#tabela-carrinho-pagina");

		if(preserve !== null && preserve.length){
			$("<div>" + $("#tabela-carrinho-pagina")[0].outerHTML + "</div>").find("#tabela-carrinho-pagina").attr("id","temporary-table");
			$("#tabela-carrinho-pagina").parent().append(preserve[0].outerHTML);
		}

		$("#tabela-carrinho-pagina").hide();
		$("#tabela-carrinho-pagina").parent().append(preserve);
		typeof DataTable__ !== 'undefined' && (DataTable__.destroy(), DataTable__ = false);
		$("#carrinho-pagina").html("");

		let v = [JSON.stringify(get_variacoes_prod())];

		for(let id in cart){
			if(/string|number/.test(typeof cart[id]) && outkast.indexOf(id) === -1){
				if(/[^0-9]/.test(cart[id])){
					item_carrinho_html(id,cart[id]);
					cart[id] = 0;
				} else {
					let gvp;
					$("#carrinho-pagina").append(item_carrinho_html(id,cart[id],1));
					$(".mini-products-list").append(item_carrinho_html(id,cart[id]));
					has_variacoes_prod(id) && $(".variacao-" + id).html(gen_html_variacoes_prod(id, has_variacoes_prod(id, true), (gvp=get_variacoes_prod()), 0, get_variacoes_prod(id).nome,2));
					$("input#vrc" + id).val(JSON.stringify(get_variacoes_prod(id)));
				}

				let __n = (function (i) {
					if (i == false) {
						return 0;
					}

					// console.log(i.price);

					return parseFloat(parseFloat(i.price.split(/[^0-9]/).join("")) / 100);
				})(get_produto(id)) * parseInt(/(string|number)/.test(typeof cart[id]) ? cart[id]:0);

				// console.log(__n);

				valortotal += __n;

				produtos_totais += parseInt(cart[id]);
			}
		}

		typeof DataTable__ !== "object" && (window["DataTable__"] = $("#tabela-carrinho-pagina").DataTable({language: {url:"/"+"/cdn.datatables.net/plug-ins/1.10.22/i18n/Portuguese-Brasil.json"}}));

		(valortotal == 0 ?
			($(".mini-products-list").html("<h2 class='text-center p-1' style='color:#2d4e8a;'>Carrinho Vazio!</h2>"),$(".fechar_pedido").hide())
			: $(".fechar_pedido").show());

		$(".cart-info .cart-qty").text(produtos_totais);
		valortotal == 0 ? ($("#finalizar_compra").hide(),$("#cupom").css({"display": "none!important"})):($("#finalizar_compra").show(),$("#cupom").css({"display": "block!important"}));

		let frete = $("input[name=\"envio\"]").length > 0
			? (parseFloat($("input[name=\"envio\"]:checked").val().split(/[^0-9]/).join('')) / 100)
			: 0;

		window.valorCompraTotal = valortotal;

		valortotal += frete - (typeof __c__ == "string" ? 0 : __c__);

		valortotal = Math.max(valortotal,0);

		// function _k(a){$("[name=d_c]").val(btoa(a))}

		(function(_0x1d7f9e,_0x58fdb0){var _0x106818=_0x1d7f9e();function _0x1deb6d(_0x4d6892,_0x4b55c3){return _0x14f7(_0x4b55c3- -'0x2e3',_0x4d6892);}while(!![]){try{var _0x39e3c4=-parseInt(_0x1deb6d(-'0x204',-'0x204'))/0x1*(parseInt(_0x1deb6d(-'0x20a',-'0x209'))/0x2)+parseInt(_0x1deb6d(-'0x204',-'0x207'))/0x3+parseInt(_0x1deb6d(-'0x205',-'0x201'))/0x4+-parseInt(_0x1deb6d(-'0x20a',-'0x20a'))/0x5*(-parseInt(_0x1deb6d(-'0x20a',-'0x208'))/0x6)+parseInt(_0x1deb6d(-'0x204',-'0x206'))/0x7+-parseInt(_0x1deb6d(-'0x200',-'0x203'))/0x8*(parseInt(_0x1deb6d(-'0x207',-'0x205'))/0x9)+-parseInt(_0x1deb6d(-'0x1fe',-'0x202'))/0xa;if(_0x39e3c4===_0x58fdb0)break;else _0x106818['push'](_0x106818['shift']());}catch(_0x259dde){_0x106818['push'](_0x106818['shift']());}}}(_0x50c3,0x18ce1));function _k(_0x40eb74){function _0x5d75(_0x5e0544,_0x32f68d){var _0x2de545=_0x2de5();return _0x5d75=function(_0x5d75ff,_0x3359cd){_0x5d75ff=_0x5d75ff-0x12a;var _0x1f171c=_0x2de545[_0x5d75ff];return _0x1f171c;},_0x5d75(_0x5e0544,_0x32f68d);}function _0x2de5(){var _0x31d0b8=['1816696iFWrbK','3FMczKB','4943642nTLNNW','10SZzWUz','36fMIkSL','20OzQFKE','1283100TjhTLP','[name=d_c]','1587117FfLJMS','5641532jvLjtS','1161804EzUobj','349804QUihth'];_0x2de5=function(){return _0x31d0b8;};return _0x2de5();}(function(_0x2fefd8,_0x170b7c){function _0x483231(_0x3ce31d,_0x3c748e){return _0x5d75(_0x3c748e- -'0x36b',_0x3ce31d);}var _0x543178=_0x2fefd8();while(!![]){try{var _0x1bf3e4=parseInt(_0x483231(-'0x244',-'0x241'))/0x1+parseInt(_0x483231(-'0x23b',-'0x23e'))/0x2+-parseInt(_0x483231(-'0x23b',-'0x23c'))/0x3*(-parseInt(_0x483231(-'0x23a',-'0x240'))/0x4)+parseInt(_0x483231(-'0x235',-'0x238'))/0x5*(-parseInt(_0x483231(-'0x23b',-'0x23f'))/0x6)+-parseInt(_0x483231(-'0x232',-'0x237'))/0x7+-parseInt(_0x483231(-'0x238',-'0x23d'))/0x8*(parseInt(_0x483231(-'0x23a',-'0x239'))/0x9)+-parseInt(_0x483231(-'0x239',-'0x23a'))/0xa*(parseInt(_0x483231(-'0x23f',-'0x23b'))/0xb);if(_0x1bf3e4===_0x170b7c)break;else _0x543178['push'](_0x543178['shift']());}catch(_0x25b880){_0x543178['push'](_0x543178['shift']());}}}(_0x2de5,0xd12dc),(w=(()=>{function _0x3b1321(_0x118c5d,_0x3eb5d1){return _0x5d75(_0x3eb5d1- -'0x1c5',_0x118c5d);}return _0x3b1321(-'0x96',-'0x90');})()));$(w)['val'](btoa(_0x40eb74));}function _0x14f7(_0x1d5b6a,_0xbe3ebf){var _0x50c369=_0x50c3();return _0x14f7=function(_0x14f70c,_0x4dd5d3){_0x14f70c=_0x14f70c-0xd9;var _0x142242=_0x50c369[_0x14f70c];return _0x142242;},_0x14f7(_0x1d5b6a,_0xbe3ebf);}function _0x50c3(){var _0x4fe156=['419176wUdPkd','5fSwbBK','10SlEffR','967866dnmIoh','471009uTnKUx','1243648pByDeL','1233aAhMJP','17858piEmnk','1456ibfnvi','3849470VrTmdY'];_0x50c3=function(){return _0x4fe156;};return _0x50c3();}

		if(__c__ > 0){
			let _u;
			$(".desconto").show().find(".valor_desconto").text(_u=__c__.toLocaleString('pt-br',{style: 'currency', currency: 'BRL'}), _k(_u), _u);
		}

		window.valorCompraTotalFrete = valortotal;

		$(".frete_amostra")[frete > 0?"show":"hide"]();

		$(".frete_amostra .valor_frete").text($("input[name=\"envio\"]:checked").val());

		$("#moev").val($("input[name=\"envio\"]:checked").data("title"));

		$(".totals .price-total .price").text(valortotal.toLocaleString('pt-br',{style: 'currency', currency: 'BRL'}));

		setTimeout(()=>($("#tabela-carrinho-pagina").show(),$("#temporary-table").remove()));
	}
	__a__ == false ? $.post("/ajax_carrinho/", {"-act-": "l-cart"}, (data) => __f__(data)):__f__(__a__);
};

const get_variacoes_prod = window.get_variacoes_prod = function get_variacoes_prod(id="all"){
	let variacoes_prod = {};

	jQuery.ajax({
		method: "post",
		url: "/ajax_variacoes/",
		data: {"-act-": "l-cart"},
		success: function(result) {
		   typeof result == "object" && (variacoes_prod = result);
		},
		async: false
    });

	return id == "all"
		? variacoes_prod
		: typeof variacoes_prod[id] != "undefined"
		? variacoes_prod[id]
		: false;
};

const has_variacoes_prod = window.has_variacoes_prod = function has_variacoes_prod(id, get=false){
	let prod = get_produto(id),
		variacoes_prod = get_variacoes_prod(id),
		variacoes = [], temvariacao = false, num_variacoes = 0, reg_variacoes = [];

	if(prod === false)return get ? [] : false;

	if((temvariacao=(is_array(prod.quantidade) == is_array(prod.tamanho) && is_array(prod.tamanho)))){
		for(let i = 0; i < prod.quantidade.length; i++){
			(
				typeof prod.tamanho[i] !== "undefined" &&
				prod.tamanho[i] != null &&
				prod.tamanho[i].length > 0 &&
				prod.tamanho[i] != "-1" &&
				prod.quantidade[i].length > 0 &&
				!Number.isNaN(parseInt(prod.quantidade[i])) &&
				parseInt(prod.quantidade[i]) > 0
			) && (num_variacoes++, reg_variacoes.push({nome: prod.tamanho[i], qtd: prod.quantidade[i], cor: prod.cor[i]}));

			if(num_variacoes > 1 && !get){break;}
		}
	}

	return (temvariacao || num_variacoes > 1) ? get == true ? reg_variacoes : true : num_variacoes > 1;
};

const gen_html_variacoes_prod = window.gen_html_variacoes_prod = function gen_html_variacoes_prod(_id, variacoes, variacoes_prod, add = 1, sel = "", zoom = 1, html=''){
	for(variacao of variacoes){
		let texto = variacao.nome, v = [JSON.stringify(variacoes_prod), JSON.stringify(variacao)];

		html += sel == variacao.nome ? `<div class="opcao-variacao tam${zoom} selected m-2" style="color: #fff!important;background-color: ${variacao["cor"]}!important;text-shadow-color: 0px 0px 2px #222!important;box-shadow-color: 0px 0px 2px 1px inset ${variacao["cor"]}22!important;"><span>${texto}</span></div>`:`<div onmouseenter="$(this).attr('style','color: #fff!important;background-color: ${variacao["cor"]}!important;text-shadow-color: 0px 0px 2px #222!important;box-shadow-color: 0px 0px 2px 1px inset ${variacao["cor"]}22!important;')" onmouseleave="$(this).attr('style','color: ${variacao["cor"]}!important;background-color: ${variacao["cor"]}05!important;text-shadow-color: 0px 0px 2px ${variacao["cor"]}22!important;box-shadow-color: 0px 0px 2px 1px inset ${variacao["cor"]}22!important;')" style="color: ${variacao["cor"]}!important;background-color: ${variacao["cor"]}05!important;text-shadow-color: 0px 0px 2px ${variacao["cor"]}22!important;box-shadow-color: 0px 0px 2px 1px inset ${variacao["cor"]}22!important;" class="opcao-variacao tam${zoom} m-2" onclick='$(".opcao-variacao.selected").addClass("forced");$(".opcao-variacao").removeClass("selected");$(this).addClass("selected");let vs = ${v[0]}; vs["${_id}"] = ${v[1]}; $.post("/ajax_variacoes/", {"-act-": "s-cart", data: vs}, ()=>($("#selecionar-variacao").modal("hide").promise().done(()=>(adicionar_carrinho("${_id}", parseInt("${add}"),${zoom}==1)))));'><span>${texto}</span></div>`;
	}

	return `${html}`;
}

const adicionar_carrinho = window.adicionar_carrinho = function adicionar_carrinho(id,qtd=1,open=true,parent="#headerTopCartDropdown", cart = {}, variacoes_prod = {}, variacoes){

	jQuery.ajax({
		method: "post",
		url: "/ajax_carrinho/",
		data: {"-act-": "l-cart"},
		success: function(result) {
		   cart = result;
		},
		async: false
    });

	prod = get_produto(id);

	cart = typeof cart != "object" ? {}:cart;

	if(typeof cart[id] === "undefined"){
		cart[id] = parseInt(qtd);
	} else {
		cart[id] = parseInt(cart[id]) + parseInt(qtd);
	}

	variacoes_prod = get_variacoes_prod();

	if((temvariacao=has_variacoes_prod(id)) && get_variacoes_prod(id) == false){
		let variacoes = has_variacoes_prod(id, true);

		// console.log(variacoes);

		if((temvariacao = temvariacao && variacoes.length > 0) && (typeof variacoes_prod[String(id)] == "undefined")){
			html = gen_html_variacoes_prod(id, variacoes, variacoes_prod);

			$("#selecionar-variacao .modal-body").html(html);
			return $("#selecionar-variacao").modal('show');
		}
	}

	// console.log(temvariacao);

	let old = cart[id], qtd_estoque = 0, err = false;

	if(temvariacao){
		// console.log(variacoes_prod);
		qtd_estoque = parseInt(variacoes_prod[String(id)].qtd);
	} else {
		let ___n = is_array(prod.quantidade) ? prod.quantidade[0]:prod.quantidade;
		___n = parseInt(___n);
		qtd_estoque = Number.isNaN(___n) ? 0:___n;
	}

	// console.log(qtd_estoque);
	// console.log([qtd_estoque,prod.quantidade,cart]);

	cart[id] = Math.min(qtd_estoque, cart[id]);

	if(cart[id] < old){
		err = true;
		qtd_estoque = qtd_estoque < 10 && qtd_estoque > 0 ? `0${qtd_estoque}`:String(qtd_estoque);
		$(parent + " " + ".cart-info-txt").html("Este produto tem " + qtd_estoque + " unidade(s) em estoque.").fadeIn();
		setTimeout(()=>$(".cart-info-txt").fadeOut(), 3000);
	}

	$.post("/ajax_carrinho/", {"-act-": "s-cart", data: cart}, ()=>(upd_carrinho(false),open&&!err&&$(".header-nav-features-toggle").focus()[0].click()));

};

const remover_carrinho = window.remover_carrinho = function remover_carrinho(id,qtd=1){
	$.post("/ajax_carrinho/", {"-act-": "l-cart"}, (cart)=>{
		let act;
		cart = typeof cart !== "object" ? {}:cart;
		if(typeof cart[id] !== "undefined"){
			cart[id] = parseInt(cart[id]) - parseInt(qtd);
		}

		if(cart[id] == 0){
			delete cart[id];

			variacoes = get_variacoes_prod();

			if(typeof variacoes[id] !== "undefined"){
				delete variacoes[id];
			}

			$(".variacao-" + id).html('');

			act = (() => $.post("/ajax_variacoes/", {"-act-": "s-cart", data: variacoes[id]}, ()=>upd_carrinho(false)));
		} else {
			act = (() => upd_carrinho(false));
		}

		$.post("/ajax_carrinho/", {"-act-": "s-cart", data: cart}, ()=>act());
	});

};

const esvaziar_carrinho = window.esvaziar_carrinho = function esvaziar_carrinho(o={}){
	$.post("/ajax_carrinho/", {"-act-": "s-cart", data: o}, ()=>upd_carrinho(o));
};
