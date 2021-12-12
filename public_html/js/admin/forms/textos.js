LWDKExec(function(){
    FormCreateAction("textos", function(){
        let texto = $(".summernote").first().summernote('code');

        dados = {data: texto};

        $.post("{myurl}", dados, function(success){
            if(success===true){
                successRequest(null, "{TITLE} atualizado(a)!");
            } else {
                errorRequest(refresh);
            }
        });
    });
	({valuesof}) !== null && $(".summernote").first().summernote('code', {valuesof}.data);
});
