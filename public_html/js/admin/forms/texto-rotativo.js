LWDKExec(function(){
    FormCreateAction("texto-rotativo", function(){
        let textos = [];

        $(".m-input:not(.dropdown.bootstrap-select)").each(function(i){
			let smtctx = Math.sign($(this).closest("[data-repeater-item]").find(".summernote").length),
				smt = $(this).hasClass("summernote"),
				v = (!!smtctx && smt ? $(this).summernote("code"):$(this).val()),
				d = $(this).closest("[data-repeater-item]").find("[data-repeater-delete]");

            textos.push(v);
        });

		if(textos[0].length == 0){
			return errorRequest(null, "Insira uma imagem de capa.");
		}



        dados = ({data: textos});

		for( j = 2; j < textos.length; j += 3){
			if(textos[j].length == 0 && textos[j+1] !== null && textos[j+1] !== "add"){
				return errorRequest(()=>setTimeout(()=>$(".m-input:not(.dropdown.bootstrap-select)").eq(j).focus()[0].click(),1000), "Preencha os campos completamente.1");
			}

			if(textos[j].length == 0 && textos[j+2] !== "<p><br></p>"){
				return errorRequest(()=>setTimeout(()=>$(".m-input:not(.dropdown.bootstrap-select)").eq(j).focus()[0].click(),1000), "Preencha os campos completamente.2");
			}

			if(textos[j].length > 0 && textos[j+2] == "<p><br></p>"){
				return errorRequest(()=>setTimeout(()=>$(".m-input:not(.dropdown.bootstrap-select)").eq(j+2).summernote('focus'),1000), "Preencha os campos completamente.3");
			}

			if(textos[j].length > 0 && (textos[j+1] == null || textos[j+1] == "add")){
				return errorRequest(()=>setTimeout(()=>$(".m-input:not(.dropdown.bootstrap-select)").eq(j+1).focus()[0].click(),1000), "Preencha os campos completamente.4");
			}

			if(textos[j+2] !== "<p><br></p>" && (textos[j+1] == null || textos[j+1] == "add")){
				return errorRequest(()=>setTimeout(()=>$(".m-input:not(.dropdown.bootstrap-select)").eq(j+1).focus()[0].click(),1000), "Preencha os campos completamente.5");
			}
		}

        $.post("{myurl}", dados, function(success){
			typeof success == "string" && (success=(success==="true"));
            if(success===true){
                successRequest(null, "A central de ajuda foi atualizada!");
            } else {
                errorRequest(refresh);
            }
        });
    });

	let i = 0, d = ({valuesof});

	for(c of d){
		$(".m-input:not(.dropdown.bootstrap-select)").eq(i).length == 0 && $("[data-repeater-create]")[0].click();
		i++;
	}

	setTimeout(()=>{

	    let i = 0, c = {valuesof}, content, img = c[0], e;

		if(!/uploads/.test(img)){
			img.length && c.unshift("");
			img = false;
			while(/uploads/.test(c[1])){
				c.shift();
				img = c[0];
			}
		}

		if(img !== false){
			$.post("/admin/ajax_ajuda_get_img/", {img: img}, function(data){
				$("#gallery input.m-input").replaceWith(data);
			});
		}


		for(content of c){
	        $(".m-input:not(.dropdown.bootstrap-select)").eq(i).length == 0 && $("[data-repeater-create]")[0].click();

			(e = $(".m-input:not(.dropdown.bootstrap-select)").eq(i))
			.hasClass("summernote")
				? e.removeClass("__summernote__").summernote("code", content)
				: ( e.hasClass("m_selectpicker")
					? e.val(content).selectpicker("refresh")
					: e.val(content)
				);

	        i++;
	    }

		LWDKInitFunction.exec();
	}, 1500);
});
