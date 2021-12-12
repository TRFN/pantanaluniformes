LWDKExec(function(){
	One("#img_upload1").addClass("dropzone").dropzone({ // The camelized version of the ID of the form element

        // The configuration we've talked about above
        autoProcessQueue: true,
        uploadMultiple: true,
        parallelUploads: 1,
        maxFiles: 1,
        acceptedFiles: "image/*",

        // The setting up of the dropzone
        init: function() {
            var myDropzone = this;

            myDropzone.on("successmultiple", function(file, response) {
                $.post("{myurl}", {imgs: response}, function(data){
                    $("#gallery1.start").removeClass("start").html("");
                    $("#gallery1").append(data);
                    $("#img_upload1")[0].dropzone.disable();
                });
            });
        },

        complete: function(file){
            this.removeFile(file);
        }
    });

	One("#img_upload2").addClass("dropzone").dropzone({ // The camelized version of the ID of the form element

        // The configuration we've talked about above
        autoProcessQueue: true,
        uploadMultiple: true,
        parallelUploads: 1,
        maxFiles: 1,
        acceptedFiles: "image/*",

        // The setting up of the dropzone
        init: function() {
            var myDropzone = this;

            myDropzone.on("successmultiple", function(file, response) {
                $.post("{myurl}", {imgs: response}, function(data){
                    $("#gallery2.start").removeClass("start").html("");
                    $("#gallery2").append(data);
                    $("#img_upload2")[0].dropzone.disable();
                });
            });
        },

        complete: function(file){
            this.removeFile(file);
        }
    });

    const getLogoData = window.getLogoData = ((id) => {
		id--;
        return $("input.img").eq(id).length?$("input.img").eq(id).val():null;
    });

    const setLogoData = window.setLogoData = ((data,id=1) => {
        if(data === null || typeof data !== "string" || typeof data.length !== "number" || data.length === 0)return;

        $("#img_upload" + String(id))[0].dropzone.disable();

        $("#gallery" + String(id) + ".start").removeClass("start").html("");
        $("#gallery" + String(id)).append(
            `<div class='col-12 text-center'>
                <input type=hidden data-img-url='${data}' class=img value='${data}' />
                <div class='col-12 img' data-img-url='${data}' style='background-image:url(/${data}); background-size: 90%;'>
                    <br /><br /><br />
                </div>
                <div class='col-12 text-center'>
                    <button  class='apagar m-btn text-center m-btn--pill btn-outline-danger btn'>
                        <i class='la las la-trash'></i> Apagar
                    </button>
                </div>
            </div>`
        );
    });

    One(".submit").click(function(){
        $.post(LWDKLocal, {0:getLogoData(1),1:getLogoData(2)}, function(success){
            return success ? successRequest(refresh):errorRequest(refresh);
        });
    });

    typeof {valuesof} == "object" && typeof {valuesof} !== "null" && typeof {valuesof}[0] == "string" && setLogoData({valuesof}[0] , 1);
    typeof {valuesof} == "object" && typeof {valuesof} !== "null" && typeof {valuesof}[1] == "string" && setLogoData({valuesof}[1] , 2);

	setTimeout(()=>{
		$("#img1 .apagar").each(function(){
			One(this, "erase1").click(function(){
				confirm("Deseja mesmo remover essa imagem?") && $(this).parent().parent().slideUp('fast', function(){
					$.post(LWDKLocal, {act: "erase", file: $(this).find(".img:first").data("img-url")});
					$("#img_upload1")[0].dropzone.enable();
					$(this).remove();
				})
			});
		});

		$("#img2 .apagar").each(function(){
			One(this, "erase2").click(function(){
				confirm("Deseja mesmo remover essa imagem?") && $(this).parent().parent().slideUp('fast', function(){
					$.post(LWDKLocal, {act: "erase", file: $(this).find(".img:first").data("img-url")});
					$("#img_upload2")[0].dropzone.enable();
					$(this).remove();
				})
			});
		});
	}, 100);
});
