
const CartControl = window.CartControl = new Object();

CartControl.init = (() => {
    CartControl.cookieGet((data)=>{
        if(is_array(data)){
            for( let prod of data ){
                CartControl.addProduct(prod, true);
            }
        }
    });
    CartControl.mainTag.data("source-bkp", JSON.stringify(CartControl.getProducts()));
    CartControl.oneCall(CartControl.step, 400);
});

CartControl.mainTag = $("#dropdnMinicart");

CartControl.temporary = CartControl.temp = CartControl.tmp = new Object();

CartControl.oneCall = ((fn, wait=1000) => {
    id = "_" + fn.name + "_call_fn_";
    if(typeof CartControl.tmp[id] !== "undefined"){
        clearTimeout(CartControl.tmp[id]);
    }
    CartControl.tmp[id] = setTimeout(()=>{delete CartControl.tmp[id]; fn();}, wait);
});

CartControl.watchModify = (() => {
    let prods = JSON.stringify(CartControl.getProducts());
    if(!CartControl.mainTag.data("source-bkp") || CartControl.mainTag.data("source-bkp") != prods){
		CartControl.cookieSet();
        CartControl.mainTag.data("source-bkp", prods);
    }
});

CartControl.enableState = CartControl.mainTag[0].innerHTML;

CartControl.emptyAll = (() => {return(THEME.productAction.removeAnimationAll(), setTimeout(()=>THEME.productAction.clearAllAnimation(), 600))});

CartControl.reload = () => {CartControl.mainTag.html(CartControl.enableState); $(".minicart-link").removeClass("only-icon");};

CartControl.step = () => setInterval(()=>{try{
    CartControl.watchModify();
    CartControl.enableSold();


    if($(".main-content-cart.row").length < 1 && !CartControl.mainTag.hasClass("empty")){
        CartControl.mainTag.addClass("empty");
        CartControl.emptyAll();
    }

    let preco = 0.00, totalitens = 0;

    $(".main-content-cart.row").each(function(){
        preco += (parseFloat($(this).data("prod").preco.split(/[^0-9,]/).join('').split(',').join('.')) * parseInt($(this).data("qtd")));
        $(this).find(".minicart-prd-qty-value").html($(this).data("qtd"));
        String($(this).data("qtd")) == "0" ? ($(this).remove()) : (totalitens+=parseInt($(this).data("qtd")));
    });

    preco2 = parseFloat($(".minicart-drop-total-price").text().split(/[^0-9,]/).join('').split(',').join('.'));
    if(preco != preco2){
        if(Math.abs(preco - preco2) > 10){
            preco2 += (preco - preco2) * .0198;
        } else {
            preco2 = preco;
        }
        $(".minicart-drop-total-price[data-preco]").text(preco2=preco2.toLocaleString('pt-br',{style: 'currency', currency: 'BRL'}));
        $(".minicart-total").html('<span style="color:#fff">-</span>' + preco2);

		CartControl.updateShipping();
    }

    if(totalitens > 0){
        $(".minicart-total").parent().find(".minicart-qty").text(totalitens).removeClass("d-none");
    } else {
        $(".minicart-total").parent().find(".minicart-qty").text("").addClass("d-none");
    }
}catch(e){}});

CartControl.updateShipping = (()=>{
	CartControl.oneCall(()=>{
		window._prod_largura 	 = 0;
		window._prod_altura      = 0;
		window._prod_comprimento = 0;
		window._vld              = 0;

		for(let prod of CartControl.getProducts()){

			$.post(prod.url, {getJSON: true}, function(prod2){
				_prod_largura      += parseFloat(prod2.largura) 	* parseFloat(prod.qtd);
				_prod_altura  	   += parseFloat(prod2.altura)  	* parseFloat(prod.qtd);
				_prod_comprimento  += parseFloat(prod2.comprimento) * parseFloat(prod.qtd);
				_vld               += parseFloat(prod2.valor.split(/[^0-9,]/).join('').split(',').join('.'));
			});

		}

		// console.log(prod={largura: _prod_largura, altura: _prod_altura, comprimento: _prod_comprimento, valor: _vld.toLocaleString('pt-br',{style: 'currency', currency: 'BRL'})});


		$.post("/cli_ajax/", {}, function(data){
			if(data){
				prod = {largura: _prod_largura, altura: _prod_altura, comprimento: _prod_comprimento, valor: _vld.toLocaleString('pt-br',{style: 'currency', currency: 'BRL'})};
				CalcShipping(prod, data.endereco[typeof endereco_selecionado !== "number" ? 0 : endereco_selecionado].cep, true);
			}
		});
	});
});

CartControl.__addProduct = ((product, start=false) => {
    if(CartControl.mainTag.hasClass("empty")){
        CartControl.mainTag.removeClass("empty");
        CartControl.reload();
    }

    let qtd = start ? product.qtd  : 1;

    if((prod=$(`#prod-${product.id}`)).length == 0){
        $("#dropdnMinicart .minicart-drop-content").append(`<div class="minicart-prd row main-content-cart" data-qtd='${qtd}' data-prod='` + JSON.stringify(product) + `' id="prod-${product.id}">
          <div class="minicart-prd-image image-hover-scale-circle col">
             <a href="produtos.html"><img src="${product.img}" data-src="${product.img}" alt="${product.nome}"></a>
          </div>
          <div class="minicart-prd-info col">
            <div class="minicart-prd-tag">${product.categoria}</div>
            <h2 class="minicart-prd-name"><a href="${product.url}">${product.nome}</a></h2>
            <div class="minicart-prd-qty"><span class="minicart-prd-qty-label">Quantidade:</span><span onclick="Swal.fire({text: 'Defina uma quantidade:',input: 'number',inputValue: $(this).closest('.main-content-cart').data('qtd'),inputAttributes: {},showCancelButton: true,confirmButtonText: 'Alterar',cancelButtonText: 'Cancelar'}).then((result) => {if(result.isConfirmed){$('#prod-${product.id}').data('qtd',result.value);$('.dropdn-link.js-dropdn-link.minicart-link').each(function(){this.click();})}});" class="minicart-prd-qty-value">${qtd}</span>&nbsp;&nbsp;
            <span style="z-index: 1000000; cursor: pointer;position: absolute;top: 40px;right: 13px;font-size: 30px;" onclick="(prod=$(this).closest('.main-content-cart')).data('qtd',parseInt(prod.data('qtd')) + 1);">+</span>&nbsp;<span style="z-index: 1000000; cursor: pointer;position: absolute;top: 78px;right: 14px;font-size: 40px;" onclick="(prod=$(this).closest('.main-content-cart')).data('qtd',parseInt(prod.data('qtd')) - 1);">-</span></div>
            <div class="minicart-prd-price prd-price">
              <div class="price-new">${product.preco}</div>
            </div>
          </div>
          <div class="minicart-prd-action">
            <a href="#" class="js-product-remove" data-line-number="1"><i class="icon-recycle"></i></a>
          </div>
        </div>`);

    } else {
        prod.data('qtd',parseInt(prod.data('qtd')) + 1);
    }
});

CartControl.getProducts = (() => {
    let $__map = MapEl(CartControl.mainTag.find(".main-content-cart.row"), function(){return [$(this).data("prod"),$(this).data("qtd")];}, true, true, /(object|number)/),
        saida  = [];

    for(i = 0; i < $__map.length; i += 2){
        saida.push($__map[i]);
        saida[saida.length-1]["qtd"] = $__map[i+1];
    }

    return saida;
});

CartControl.cookieSet = (() => {
    $.post(LWDKLocal, {"-act-": "s-cart", data: CartControl.getProducts()});
});

CartControl.cookieGet = ((fn) => {
    $.post(LWDKLocal, {"-act-": "l-cart"}, fn);
});

CartControl.enableSold = (() => {
    $("[data-carrinho]").each(function(){
        One(this, "sold").click(function(){
            CartControl.addProduct({
                id: $(this).data("carrinho").id,
                nome: $(this).data("carrinho").nome,
                img: $(this).data("carrinho").imagens[0].url,
                categoria: $(this).closest(".produto").find(".prd-tag:first").text(),
                url: $(this).data("product").url,
                preco: $(this).data("carrinho").valor,
				qtd: typeof $(this).data("carrinho").qtd !== "undefined" ? $(this).data("carrinho").qtd:1
            });
        });
    })
});

window.addEventListener("load", ()=>CartControl.oneCall(CartControl.init, 100), true);

CartControl.addProduct = ((prod) => {
	prod.qtd = parseInt(prod.qtd);
	while(!!prod.qtd--){
		CartControl.__addProduct(prod);
	}
});

CartControl.openCartPage = () => {
	$("#dropdnMinicart").addClass("page");
	$(".minicart-link")[0].click();
}

const CalcShipping = window.CalcShipping = ((product, cep, passive=false) => {
    cep = cep.split(/[^0-9]/).join('');

    if(cep.length !== 8){
        return swal.fire("Insira um C.E.P. v&aacute;lido.");
    }

    let consulta = new Object();
    let vld = product.valor.split(/[^0-9,]/).join('').split(',').join('.');

    consulta.origem = "31535094";                // Cep da empresa / sCepOrigem
    consulta.destino = cep;                      // Cep Destino / sCepDestino
    /**************************************/
    consulta.valor_declarado = vld;              /* indicar 0 caso nao queira o valor declarado */
    consulta.peso = product.peso_liq;            /* valor dado em Kg incluindo a embalagem. 0.1, 0.3, 1, 2 ,3 , 4 */
    consulta.altura = product.altura;            /* altura do produto em cm incluindo a embalagem */
    consulta.largura = product.largura;          /* altura do produto em cm incluindo a embalagem */
    consulta.comprimento = product.comprimento;  /* comprimento do produto incluindo embalagem em cm */

    !passive&&$("#calculo_frete").show();

    !passive&&$("#calculo_frete .table-responsive").hide();

    !passive&&$("#calculo_frete div.loading").show();

	console.log(passive?"not-hide":"hide");

    $.post(LWDKLocal + "?consultaCEP=1", consulta, (data) => {
        if(data[2] !== false){
            if(data[0] == data[1] && data[1] === false){
                $(".prazo_sedex").html("&ndash;");
                $(".preco_sedex").html("&ndash;");
                $(".prazo_pac").html(data[2].prazo);
                $(".preco_pac").html(pac=parseInt(data[2].valor[0]).toLocaleString('pt-br',{style: 'currency', currency: 'BRL'}));
				$(".input_pac").val(pac);
            } else {
                $(".prazo_pac").html(data[0].prazo);
                $(".prazo_sedex").html(data[1].prazo);
                $(".preco_pac").html(pac=parseFloat(data[0].valor[0]).toLocaleString('pt-br',{style: 'currency', currency: 'BRL'}));
                $(".preco_sedex").html(sedex=parseFloat(data[1].valor[0]).toLocaleString('pt-br',{style: 'currency', currency: 'BRL'}));
				$(".input_pac").val(pac);
				$(".input_sedex").val(sedex);
            }

            !passive&&$("#calculo_frete .table-responsive").show();

            !passive&&$("#calculo_frete div.loading").hide();
        } else {
            !passive&&$("#calculo_frete").hide();
            swal.fire("","Seu CEP est&aacute; incorreto ou o servi&ccedil;o dos Correios est&aacute; fora do ar...<br>Tente novamente mais tarde!","warning");
        }
    })
});
