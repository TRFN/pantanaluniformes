LWDKExec(()=>$.post("/json_produtos/", (data)=>{
	window.produtos = data;
	upd_carrinho();
	$("#cupom a.btn").click(()=>valorCompraTotal>0?set_cupom($("#cupom input").val()):swal.fire("","Seu carrinho est&aacute; vazio.<br>Adicione produtos para utilizar o cupom.", "info"));
}));

const get_produto = window.get_produto = function get_produto(item){
	let produto = false;
	item = String(item);
	if(typeof window.produtos !== "undefined"){
		for(let i of produtos){
			if(parseInt(i.id) == parseInt(item)){
				produto = i;
				break;
			}
		}
	}

	return produto;
};

const _0x347c=['239211mQvjcg','2ZBYEyu','1588283BncEeP','split','190BtpvdE','5TMGHQc','216991gkOiqb','61GADfbM','22386LjnDIC','get_desconto','1360867ZzbXsr','1281768ZtjjVB','1UnYTNk','join','2206dxjGVc'];const _0x8a4753=_0x27a2;(function(_0x172e98,_0x3fedad){const _0x453cf7=_0x27a2;while(!![]){try{const _0x179cbd=parseInt(_0x453cf7(0xb6))*parseInt(_0x453cf7(0xb4))+-parseInt(_0x453cf7(0xb5))+parseInt(_0x453cf7(0xb9))*-parseInt(_0x453cf7(0xaf))+-parseInt(_0x453cf7(0xb2))*parseInt(_0x453cf7(0xb1))+-parseInt(_0x453cf7(0xb0))+parseInt(_0x453cf7(0xbd))*parseInt(_0x453cf7(0xb8))+-parseInt(_0x453cf7(0xbb))*-parseInt(_0x453cf7(0xba));if(_0x179cbd===_0x3fedad)break;else _0x172e98['push'](_0x172e98['shift']());}catch(_0x19e9d5){_0x172e98['push'](_0x172e98['shift']());}}}(_0x347c,0xdacd5));function _0x27a2(_0x29b229,_0x273ed0){return _0x27a2=function(_0x347c3b,_0x27a201){_0x347c3b=_0x347c3b-0xaf;let _0x5a6b62=_0x347c[_0x347c3b];return _0x5a6b62;},_0x27a2(_0x29b229,_0x273ed0);}const get_desconto=window[_0x8a4753(0xb3)]=function get_desconto(_0x5bfd74){const _0x1dc380=_0x8a4753;window['__c__']+=parseFloat(_0x5bfd74[0x1][_0x1dc380(0xbc)](/[^0-9]/)[_0x1dc380(0xb7)](''))/0x64;};

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
					swal.fire("Cupom aplicado!",`Voc&ecirc;&nbsp;recebeu um desconto de ${valor_desc}<br>sobre o valor da compra.`,
					"success");
					cart[cod] = r[2];
					$.post("/ajax_carrinho/", {"-act-": "s-cart", data: cart}, upd_carrinho);
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
	if((produto = get_produto(item)) === false){get_desconto([item,qtd]); return "";}

	let valor;

	if(produto["valor-a-vista"].length < 7 || produto["valor-a-vista"] === "R$ 0,00"){
		valor = produto["valor"];
	} else {
		valor = produto["valor-a-vista"];
	}

	produto["price"] = valor;

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
				<div class="quantity quantity-lg" style="float: right;">
					<input type="button" onclick="remover_carrinho(${produto["id"]}, 1);" class="minus text-color-hover-light bg-color-hover-primary border-color-hover-primary" value="-" />
					<input type="number" readonly class="input-text qty text" title="Qty" value="${qtd}" name="quantity" min="1" step="1" />
					<input type="button" onclick="adicionar_carrinho(${produto["id"]}, 1);" class="plus text-color-hover-light bg-color-hover-primary border-color-hover-primary" value="+" />
				</div>
				<div style="clear: both"></div>
			</div>
		</li>`,
		`<tr class="cart_table_item">
			<td class="product-thumbnail" style="vertical-align: middle;">
				<div class="product-thumbnail-wrapper">
					<a href="javascript:;" onclick="remover_carrinho(${produto["id"]}, ${qtd});" class="product-thumbnail-remove">
						<i class="fas fa-times"></i>
					</a>
					<a href="${produto["link"]}" class="product-thumbnail-image" title="Porto Headphone">
						<img width="128" height="76" alt="${produto["imgt"]}" src="${produto["img"]}" />
					</a>
				</div>
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
					<input type="number" readonly class="input-text qty text" title="Qty" value="${qtd}" name="quantity" min="1" step="1" />
					<input type="button" onclick="adicionar_carrinho(${produto["id"]}, 1, false);" class="plus text-color-hover-light bg-color-hover-primary border-color-hover-primary" value="+" />
				</div>
			</td>
			<td class="product-subtotal text-end" style="vertical-align: middle;">
				<span class="amount text-color-dark font-weight-bold text-4">${valortotal.toLocaleString('pt-br',{style: 'currency', currency: 'BRL'})}</span>
			</td>
		</tr>`][m];
}

const upd_carrinho = window.upd_carrinho = function upd_carrinho(){
	__c__ = 0;
	$.post("/ajax_carrinho/", {"-act-": "l-cart"}, (cart)=>{
		cart = typeof cart !== "object" ? {}:cart;
		let valortotal = 0, produtos_totais = 0;

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

		for(let id in cart){
			if(/[^0-9]/.test(cart[id])){
				item_carrinho_html(id,cart[id]);
				cart[id] = 0;
			} else {
				$("#carrinho-pagina").append(item_carrinho_html(id,cart[id],1));
				$(".mini-products-list").append(item_carrinho_html(id,cart[id]));
			}
			valortotal += ((function(i){
				if(i == false){
					return 0;
				}

				return parseFloat(parseFloat(i.price.split(/[^0-9]/).join('')) / 100);
			})(get_produto(id))) * parseInt(cart[id]);
			produtos_totais += parseInt(cart[id]);
		}

		typeof DataTable__ !== "object" && (window["DataTable__"] = $("#tabela-carrinho-pagina").DataTable({language: {url:"/"+"/cdn.datatables.net/plug-ins/1.10.22/i18n/Portuguese-Brasil.json"}}));

		(valortotal == 0 ?
		 	($(".mini-products-list").html("Carrinho Vazio!"),$(".fechar_pedido").hide())
			: $(".fechar_pedido").show());

		$(".cart-info .cart-qty").text(produtos_totais);
		valortotal == 0 ? ($("#finalizar_compra").hide(),$("#cupom").css({"display": "none!important"})):($("#finalizar_compra").show(),$("#cupom").css({"display": "block!important"}));

		let frete = $("input[name=\"envio\"]").length > 0
			? (parseFloat($("input[name=\"envio\"]:checked").val().split(/[^0-9]/).join('')) / 100)
			: 0;

		window.valorCompraTotal = valortotal;

		valortotal += frete - __c__;

		valortotal = Math.max(valortotal,0);

		if(__c__ > 0){
			$(".desconto").show().find(".valor_desconto").text(__c__.toLocaleString('pt-br',{style: 'currency', currency: 'BRL'}));
		}

		window.valorCompraTotalFrete = valortotal;

		$(".frete_amostra")[frete > 0?"show":"hide"]();

		$(".frete_amostra .valor_frete").text($("input[name=\"envio\"]:checked").val());

		$(".totals .price-total .price").text(valortotal.toLocaleString('pt-br',{style: 'currency', currency: 'BRL'}));

		setTimeout(()=>($("#tabela-carrinho-pagina").show(),$("#temporary-table").remove()));
	});
}

const adicionar_carrinho = window.adicionar_carrinho = function adicionar_carrinho(id,qtd=1,open=true,parent="#headerTopCartDropdown"){
	$.post("/json_produtos/", (data)=>{
		window.produtos = data;

		$.post("/ajax_carrinho/", {"-act-": "l-cart"}, (cart)=>{
			cart = typeof cart != "object" ? {}:cart;
			if(typeof cart[id] === "undefined"){
				cart[id] = parseInt(qtd);
			} else {
				cart[id] = parseInt(cart[id]) + parseInt(qtd);
			}

			let old = cart[id], qtd_estoque = (parseInt(get_produto(id).quantidade_estoque_barreiro) + parseInt(get_produto(id).quantidade_estoque_funcionarios)), err = false;

			cart[id] = Math.min(qtd_estoque, cart[id]);

			if(cart[id] < old){
				err = true;
				qtd_estoque = qtd_estoque < 10 ? `0${qtd_estoque}`:String(qtd_estoque);
				$(parent + " " + ".cart-info-txt").html("Este produto tem " + qtd_estoque + " unidade(s) em estoque.").fadeIn();
				setTimeout(()=>$(".cart-info-txt").fadeOut(), 3000);
			}

			$.post("/ajax_carrinho/", {"-act-": "s-cart", data: cart}, ()=>(upd_carrinho(),open&&!err&&$(".header-nav-features-toggle").focus()[0].click()));
		});
	});
};

const remover_carrinho = window.remover_carrinho = function remover_carrinho(id,qtd=1){
	$.post("/ajax_carrinho/", {"-act-": "l-cart"}, (cart)=>{
		cart = typeof cart !== "object" ? {}:cart;
		if(typeof cart[id] !== "undefined"){
			cart[id] = parseInt(cart[id]) - parseInt(qtd);
		}

		if(cart[id] == 0){
			delete cart[id];
		}

		$.post("/ajax_carrinho/", {"-act-": "s-cart", data: cart}, upd_carrinho);
	});

};
