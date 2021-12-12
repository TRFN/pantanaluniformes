LWDKExec(function(){
	window.listar_produto = function listar_produto(ref){
		let json = JSON.parse((ref=$(ref)).val()),
			parent = ref.closest(".add-form"),
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
	};

	$(".act-opt").change(f=function(){
		$(".act-in." + $(this).data("name") + ", .act-in.r-" + $(this).data("name")).addClass("d-none");
		$(".act-in." + $(this).data("name") + "." + $(this).val()).removeClass("d-none");
	});

	$("select").each(function(){
		typeof $(this).data("value") !== "undefined" && $(this).val($(this).data("value"));
		if($(this).hasClass("act-opt")){
			$(".act-in." + $(this).data("name") + ", .act-in.r-" + $(this).data("name")).addClass("d-none");
			$(".act-in." + $(this).data("name") + "." + $(this).val()).removeClass("d-none");
		}
	});

	$(".act-opt").click(f);

	$(".panel .submit").click(function(){
		let form = $(this).closest(".panel"),
			data = (MapKeyAssign(MapEl(form.find("*[data-name]"), function(){
			return [$(this).data("name"),$(this).val()];
		}, !1, !1)));

		let go = function(to){
            to = /(#)/.test(to)?$(to)[0]:$("[data-name=\"" + to + "\"]")[0];

            setTimeout(()=>$(to).focus()[0].click(), 1100);

            $([document.documentElement, document.body]).animate({
                scrollTop: $(to).offset().top - 300
            }, 900);
        };

		if(data.nome.length < 3){
	        return errorRequest(()=>go("nome"), "Preenchimento incompleto!")
		}

		if(data.data.length < 10){
	        return errorRequest(()=>go("data"), "Preenchimento incompleto!")
		}

		if(data.valor.length < 7){
	        return errorRequest(()=>go("valor"), "Preenchimento incompleto!")
		}

		$.post("/admin/financeiro_form_{act}/", data, function(success){
            if(success===true){
                successRequest(()=>(window.scrollTo(0,0),("{act}" == "edit" ? null:(top.location.href="/admin/financeiro_home/"))), "O lançamento foi " + ("{act}" == "edit"?"atualizado":"criado") + " com sucesso!");
            } else {
				// console.log(success);
                errorRequest();
            }
		});
	});
	window.updatepg = function(){
		$.post("/admin/financeiro_data/", function(success){
			for(i in success){
				$("."+i).find(" > span").html(success[i]);
			}
		});

		setTimeout(updatepg, 4000);
	};

	updatepg();
});
