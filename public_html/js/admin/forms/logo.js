LWDKExec(function(){
    // if(`{categorias}`.length == 0){
    //     return errorRequest(()=>Go("categorias"), "Antes de cadastrar um produto, voc&ecirc; precisa criar uma categoria.");
    // }
    // if(`{subcathtml}`.length == 0){
    //     return errorRequest(()=>Go("sub_categorias"), "Antes de cadastrar um produto, voc&ecirc; precisa criar uma sub-categoria.");
    // }

    One("#img_upload").addClass("dropzone").dropzone({ // The camelized version of the ID of the form element

        // The configuration we've talked about above
        autoProcessQueue: true,
        uploadMultiple: true,
        parallelUploads: 1,
        maxFiles: 1,
        acceptedFiles: "image/*",

        // The setting up of the dropzone
        init: function() {
            var myDropzone = this;

            setInterval(function(){
            //     $("[data-name=\"imagens\"]").val(JSON.stringify(MapTranslate(MapEl("#gallery .img", function(){
            //         return $(this).css("background-image").split('"')[1] + "|" + $(this).parent().find("input:not([type=\"hidden\"])").first().val();
            //     }), ["url","legend"])));
            //
                $(".apagar").each(function(){
                    One(this).click(function(){
                        let the = $(this).parent().parent();
                        if(confirm("Deseja mesmo remover esta logo?")){the.slideUp('slow', function(){
                            $("#img_upload")[0].dropzone.enable();
							$.post(LWDKLocal, {act: "erase", file: (f=$(this).parent().parent().find("#img:first").val())}, ()=>$(this).remove());
							console.log(f);
                        })}
                    });
                });
            //
            //     // One("#gallery input", "AutoComplete").change(function(){
            //         map = MapEl("#gallery input:not([type=\"hidden\"])", function(){return $(this).val();});
            //         $("#gallery input").each(function(){
            //             AutoComplete(this, map);
            //         });
            //     // });
            }, 500);

            myDropzone.on("successmultiple", function(file, response) {
				$.post("{myurl}", {imgs: response}, function(data){
                    $("#gallery.start").removeClass("start").html("");
                    $("#gallery").append(data);
                    $("#img_upload")[0].dropzone.disable();
                });
            });
        },

        complete: function(file){
            this.removeFile(file);
        }
    });

    const getLogoData = window.getLogoData = (() => {
        return $("input#img").length?$("input#img").val():null;
    });

    const setLogoData = window.setLogoData = ((data) => {
        if(data === null || typeof data !== "string" || typeof data.length !== "number" || data.length === 0)return;

		$.post("{myurl}", {imgs: [data]}, function(data){
			if(data === "not-found")return false;

			$("#img_upload")[0].dropzone.disable();
			$("#gallery.start").removeClass("start").html("");
			$("#gallery").append(data);
		});
    });

    One(".submit").click(function(){
        $.post(LWDKLocal, {data: getLogoData()}, function(success){
            return success ? successRequest(refresh):errorRequest(refresh);
        });
    });

    ({valuesof}) !== null && setLogoData(({valuesof}.data));
});
