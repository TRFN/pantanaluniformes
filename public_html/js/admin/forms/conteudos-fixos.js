LWDKExec(function(){
	One("#img_upload").addClass("dropzone").dropzone({
        autoProcessQueue: true,
        uploadMultiple: true,
        parallelUploads: 1,
        maxFiles: 1,
        acceptedFiles: "image/*",
        init: function() {
            var myDropzone = this;

            setInterval(function(){
				$(".apagar").each(function(){
					One(this).click(function(){
						ctx = $(this).parent().parent();
						Swal.fire({
						  title: '',
						  icon: 'question',
						  html: '<br>Deseja mesmo remover essa imagem?<br>Esta imagem sera deletada em definitivo.<br><br>',
						  showCloseButton: true,
						  showCancelButton: true,
						  focusConfirm: true,
						  confirmButtonText:
						    '<i class="la la-check"></i> Pode apagar',
						  confirmButtonAriaLabel: 'Sim, pode apagar',
						  cancelButtonText:
						    '<i class="la la-times"></i> Cancelar',
						  cancelButtonAriaLabel: 'Cancelar'
						}).then((result) => {
						  if (result.isConfirmed) {
							  ctx.fadeOut('fast', function(){
	  							$.post(LWDKLocal, {act: "erase", file: (f=$(this).find(".img:first").data("img-url"))});
	  							$("#img_upload")[0].dropzone.enable();
	  							$(this).remove();
	  						});
						  }
					  });
					});
				});
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

    const getLogoData = window.getLogoData = ((i=1) => {
		i--;
        return $("input.img").length?$("input.img").eq(i).val():null;
    });

    const setLogoData = window.setLogoData = ((data) => {
        if(data === null || typeof data !== "string" || typeof data.length !== "number" || data.length === 0)return;

        $("#img_upload")[0].dropzone.disable();

        $("#gallery.start").removeClass("start").html("");
        $("#gallery").html(
            `<div class='col-12 text-center'>
                <input type=hidden data-img-url='${data}' class=img value='${data}' />
				<div class="col-12 img p-0 m-0" data-img-url="${data}" style="background-image:url(/${data});background-size: 100%;position: absolute;top: -150px;left: 34px;width: 408px;border: 0;box-shadow: 0px 0px 1px 4px inset;background-color: #aaa!important;">
                            <br><br><br>
                        </div>
				<div class="col-12 text-center pt-4">
                    <button class="apagar m-btn text-center m-btn--pill btn-danger btn">
                        <i class="la las la-trash"></i> Apagar
                    </button>
                </div>
            </div>`
        );
    });

    One(".submit").click(function(){
        $.post(LWDKLocal, {img: getLogoData(), content: $(".summernote").summernote("code")}, function(success){
            return success ? successRequest(refresh):errorRequest(refresh);
        });
    });

    ({valuesof}) !== null && (setLogoData(({valuesof}.img)),$(".summernote").summernote("code",({valuesof}.content)));
});
