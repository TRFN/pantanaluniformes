const FormCreateAction = window.FormCreateAction = function FormCreateAction(id, action){
    One("#" + id + " .submit:first, #" + id + " [type=\"submit\"]:first").click(()=>action(GetFormData("#" + id),$("#" + id)));
};

const One = window.One = function One(selector, id="mod"){
    return $($(selector).not(".__" + id + "__").addClass("__" + id + "__")[0]);
};

const MapEl = window.MapEl = function MapEl(selector, fn = "error", removeEmpty = true, removeRepeated = true, types = /(string|number)/){
    let mode = typeof removeEmpty == "string" ? removeEmpty:"array";
        fn = typeof fn === "string" ? (function(f){
            switch(f){
                case "abstract-basic-form":
                    return function(){return [$(this).data("name"),$(this).val()]};
                break;

                case "error":
                    console.warn('A função requer um "callback" válido.');
                    return function(){}
                break;

                default:
                    return function(){ return this; }
                break;
            }
        })(fn):fn;
    let a = $(selector).map(fn), u;


    switch(mode){
        case "array": // Array Serie
            u = [];
            for(let i = 0; i < a.length; i++){ u.push(a[i]); }
            !!removeEmpty && (u=u.filter(function (el) {return el != null && el != ""}));
            !!removeRepeated && (u=function(a,b){$.each(a, function(c, d){$.inArray(d, b)===-1&&b.push(d);});return b;}(u,[]));
            return u.filter(function (e) {return types.test(typeof e)});
        break;

        case "object": // Object Element
            u = {};
            removeEmpty = removeRepeated;
            for(let i = 0; i < a.length; i+=2){
                if(types.test(typeof a[i+1]) && (!removeEmpty || (!!removeEmpty && String(a[i+1]).length > 0))){
                    if(typeof u[a[i]] == "string"){
                        u[a[i]] = [u[a[i]]];
                    }
                    if(typeof u[a[i]] == "object" && typeof u[a[i]].length !== "undefined"){
                        u[a[i]].push(a[i+1]);
                    } else {
                        u[a[i]] = a[i+1];
                    }
                }
            }

            return !!removeEmpty && Object.keys(u).length == 0 ? false : u;
        break;

        default:

            u = [];
            for(let i = 0; i < a.length; i++){ u.push(a[i]); }
            return JSON.stringify(u);

        break;
    }

    return false;
};

const MapTranslate = window.MapTranslate = function MapTranslate(map, k, sep="|"){
    for(let i = 0; i < map.length; i++){
        let oldmap = map[i].split(sep);
        map[i] = new Object();
        for(let j = 0; j < k.length; j++){
            map[i][k[j]] = oldmap[j];
        }
    }

    return map;
};

const MapKeyAssign = window.MapKeyAssign = function MapKeyAssign(map){
	if(!map.length % 2){return false;}
	newmap = new Object;
    for(let i = 0; i < map.length; i += 2){
    	newmap[map[i]] = map[i+1];
    }

    return newmap;
};

const GetFormData = window.GetFormData = function GetFormData(context="html"){
    let map = MapTranslate(
            MapEl(context + " [data-name]", function(){
                switch (this.type) {
                    case "checkbox":
                        ret = $(this).is(":checked") ? "1":"0";
                    break;
                    default:
                        ret = String($(this).val());
                    break;
                }
                return [
                    $(this).data("name"),
                    ret,
                    $(this).data("json")?"1":"0",
                    $(this).data("bool")?"1":"0"
                ].join("|");
            }),
            ["id","data","json", "bool"]
        ),
        result = {};

    for(let i = 0; i < map.length; i++){
        map[i]["json"] == "1" && (map[i]["data"] = function(d){try{return JSON.parse(d)}catch(e){return []}}(map[i]["data"]));
        map[i]["bool"] == "1" && (map[i]["data"] = function(d){try{return !!parseInt(d)}catch(e){return false}}(map[i]["data"]));
        delete map[i]["json"];
    }

    for(let i in map){
        typeof map[i] !== "function" && (result[map[i].id] = map[i].data);
    }

    return result;
};

const is_array = window.is_array = (function is_array(data=null, acceptempty=false){
    return data && typeof data == "object" && typeof data.length !== "undefined" && (data.length > 0 || acceptempty) && data.constructor.name=="Array";
});

LWDKExec(()=>One(".cpfcnpj").keydown(function(){
    try {
        $(this).unmask();
    } catch (e) {}

    var tamanho = $(this).val().length;
	try {
	    // ajustando foco
	    var elem = this;
	    setTimeout(function(){
	        // mudo a posição do seletor
		    if(tamanho < 11){
		        $(elem).mask("999.999.999-99");
		    } else {
		        $(elem).mask("99.999.999/9999-99");
		    }


	        elem.selectionStart = elem.selectionEnd = 10000;
	    }, 0);
	    // reaplico o valor para mudar o foco
	    var currentValue = $(this).val();
	    $(this).val('');
	    $(this).val(currentValue);
	} catch (e) {}
}));

LWDKExec(()=>{
	One(".tel").mask("(99) 9999-99999").focusout(function (event) {
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
})
});

const confirm_msg = window.confirm_msg = function(msg,act,ico='warning',c1='{cor-tema}',c2='#d33',ts='Sim',tn='Não'){
	Swal.fire({
	  title: '',
	  html: msg,
	  icon: ico,
	  showCancelButton: true,
	  confirmButtonColor: c1,
	  cancelButtonColor: c2,
	  confirmButtonText: ts,
	  cancelButtonText: tn
	}).then((result) => {
	  result.isConfirmed&&act();
  	});
};

const force_redirect = window.force_redirect = function force_redirect(){
	$("body").append("<form method=post id=__redirect_form__><input type=hidden name=redirect value=redirect /></form>");
	$("#__redirect_form__")[0].submit();
};

const favoritar_produto = window.favoritar_produto = (function favoritar_produto(the){
	$.post("/session/", (bool)=>(!bool ? (swal.fire("", "Crie uma conta ou fa&ccedil;a login para favoritar produtos.", "info").then(()=>(window.top.location.href="{mydomain}/entrar/"))):(function(e){
		if($(e).hasClass("processing")){
			return false;
		}

		$(e).addClass("processing");

		if($(e).find("i").hasClass("fas")){
			$("[data-prod-id=\"" + $(e).data("prod-id") + "\"]").find("i").removeClass("fas").addClass("far");
		} else {
			$("[data-prod-id=\"" + $(e).data("prod-id") + "\"]").find("i").removeClass("far").addClass("fas");
		}

		$.post("/action_fav_toggle/", {i: $(e).data("prod-id")}, (data)=>($(e).removeClass("processing"),data==0&&console.log("error in favorite toggle")));
	})(the)));
});

const CalcShipping = window.CalcShipping = ((valor, cep, passive=false) => {
    cep = cep.split(/[^0-9]/).join('');

    if(cep.length !== 8){
        return swal.fire("Insira um C.E.P. v&aacute;lido.");
    }

    let consulta = new Object();
    let vld = valor.split(/[^0-9,]/).join('').split(',').join('.');

    consulta.origem = "30.150-281";                // Cep da empresa / sCepOrigem
    consulta.destino = cep;                      // Cep Destino / sCepDestino
    /**************************************/
    consulta.valor_declarado = vld;              /* indicar 0 caso nao queira o valor declarado */
    consulta.peso = 2;            /* valor dado em Kg incluindo a embalagem. 0.1, 0.3, 1, 2 ,3 , 4 */
    consulta.altura = 40;            /* altura do produto em cm incluindo a embalagem */
    consulta.largura = 30;          /* altura do produto em cm incluindo a embalagem */
    consulta.comprimento = 60;  /* comprimento do produto incluindo embalagem em cm */

    !passive&&$("#calculo_frete").show();

    !passive&&$("#calculo_frete .table-responsive").hide();

    !passive&&$("#calculo_frete div.loading").show();

    $.post(LWDKLocal + "?consultaCEP=1", consulta, (data) => {
		// console.log(data);
        if(data[2] !== false){
            if(data[0] == data[1] && data[1] === false){
                $(".prazo_sedex").closest(".e-sedex").attr("style", "display: none!important;");
				$(".sedex-indisponivel").css("display", "flex");
                $(".prazo_pac").html(data[2].prazo);
                $(".preco_pac").html(pac=parseInt(data[2].valor[0]).toLocaleString('pt-br',{style: 'currency', currency: 'BRL'}));
				$(".input_pac").val(pac);
            } else {
	            $(".prazo_sedex").closest(".e-sedex").css("display", "flex");
                $(".sedex-indisponivel").attr("style", "display: none!important;");
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

		$("input[name=\"envio\"]").trigger("change");
    })
});
