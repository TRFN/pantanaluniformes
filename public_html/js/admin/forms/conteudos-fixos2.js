LWDKExec(function(){
	$("#img_upload").dropzone({
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
	                $.post("{myurl}img1/2/", {imgs: response}, function(data){
	                    setLogoData(data);
	                });
	            });
	        },

	        complete: function(file){
	            this.removeFile(file);
	        }
		});
	$("#img_upload2").dropzone({
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
		  							$("#img_upload2")[0].dropzone.enable();
		  							$(this).remove();
		  						});
							  }
						  });
						});
					});
	            }, 500);

	            myDropzone.on("successmultiple", function(file, response) {
	                $.post("{myurl}img2/2/", {imgs: response}, function(data){
	                    setLogoData(data, 2);
	                });
	            });
	        },

	        complete: function(file){
	            this.removeFile(file);
	        }
		});
	$("#img_upload3").dropzone({
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
		  							$("#img_upload3")[0].dropzone.enable();
		  							$(this).remove();
		  						});
							  }
						  });
						});
					});
	            }, 500);

	            myDropzone.on("successmultiple", function(file, response) {
	                // $.post("{myurl}/img3/2/", {imgs: response}, function(data){
					// 	console.log(data);
					// 	false&&;
	                // });
					setLogoData(response[0],3);
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

    const setLogoData = window.setLogoData = ((data, to="") => {
        if(data === null || typeof data !== "string" || data == "-1" || typeof data.length !== "number" || data.length === 0)return;

		to = String(to);

		$("#img_upload"+to)[0].dropzone.disable();

        $("#gallery"+to+".start").removeClass("start").html("");
        $("#gallery"+to).html(
            `<div class='col-12 text-center'>
                <input type=hidden data-img-url='${data}' class=img value='${data}' />
				<div class="col-12 img p-0 m-0" data-img-url="${data}" style="background-image:url(/${data});background-size: 100%;position: absolute;top: -150px;right: 5%;left: 2.35%;border: 0;box-shadow: 0px 0px 1px 4px inset;background-color: #aaa!important; width: calc(100% - 2vw);">
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

	const getFormData = window.getFormData = () => {
		let $valores = [getLogoData(),getLogoData(2),getLogoData(3)].concat(MapEl("div.summernote", function(){
			return $(this).summernote("code");
		}, false ,false));

		for( f = 0; f < $valores.length; f++ ){
			$valores[f] = typeof $valores[f] != "string" ? -1:$valores[f];
		}

		return $valores;
	};

	const setFormData = window.setFormData = (data) => {
		if(data === null || typeof data !== "object" || typeof data.length !== "number" || data.length === 0)return;
		setLogoData(data[0]);
		setLogoData(data[1],2);
		setLogoData(data[2],3);
	};

	const saveFormData = window.saveFormData = () => {
		$.post("{myurl}", {data:getFormData()}, function(success){
			if(success===true){
                successRequest(refresh, "P&aacute;gina atualizada!");
            } else {
                errorRequest(refresh);
            }
        });
	};

	setFormData({valuesof});
});
