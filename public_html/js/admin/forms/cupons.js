LWDKExec(function(){
    FormCreateAction("categorias", function(){
        let categorias = [];

		$("input.m-input").each(function(i){
            if((v=$(this).val()).length==0 && i != 0){
                $(this).closest("[data-repeater-item]").find("[data-repeater-delete]")[0].click();
            } else {
                categorias.push(v);
            }
        });

        if(categorias.length>0&&categorias[0].length>0){
            $("[data-repeater-create]")[0].click();
        }

        dados = {data: categorias};

        $.post("{myurl}", dados, function(success){
            if(success===true){
                successRequest();
            } else {
                errorRequest(refresh);
            }
        });
    });

    let i = 0;
    for(content of {valuesof}){
        content.length>0&&$("[data-repeater-create]")[0].click();

        $("#categorias input.m-input").eq(i).val(content);

        i++;
    }

	$("input.m-input").each(function(i){
		if((v=$(this).val()).length==0 && i != 0){
			let e = $(this).closest("[data-repeater-item]").find("[data-repeater-delete]");
			e.length && e[0].click();
		}
	});

	let e = $("[data-repeater-create]");
	e.length && e[0].click();
});
