LWDKExec(function(){
    One("#img_upload").addClass("dropzone").dropzone({ // The camelized version of the ID of the form element

        // The configuration we've talked about above
        autoProcessQueue: true,
        uploadMultiple: true,
        parallelUploads: 2,
        maxFiles: 8,
        acceptedFiles: "image/*",

        // The setting up of the dropzone
        init: function() {
            var myDropzone = this;

            setInterval(function(){
                $(".apagar").each(function(){
                    One(this).click(function(){
                        let the = $(this).parent().parent();
						confirm("Deseja mesmo remover essa imagem?") && the.slideUp('slow', function(){
							$.post(LWDKLocal, {act: "erase", file: (f=$(this).find(".img:first").data("img-url"))});
							$(this).remove();
						})
                    });
                });
            }, 500);

            myDropzone.on("successmultiple", function(file, response) {
                $.post("{myurl}", {imgs: response}, function(data){
                    $("#gallery.start").removeClass("start").html("");
                    $("#gallery").append(data);
                });
            });
        },

        complete: function(file){
            this.removeFile(file);
        }
    });

    const getSlideData = window.getSlideData = (() => {
        let slides = [];
        $(".slide").each(function(){slides.push(MapEl($(this).find("input"), function(){return this.value;},false, false).join("|"))});
        slides = MapTranslate(slides, ["titulo","dia","horario","img"]);
        return slides.length ? slides:null;
    });

    const setSlideData = window.setSlideData = ((data) => {
        if(data === null || typeof data !== "object" || typeof data.length !== "number" || data.length === 0)return;

        for(i of data){
            $("#gallery.start").removeClass("start").html("");
            (!(typeof i != "object" || typeof i.img != "string" || i.img.length < 4)) && $("#gallery").append(
                `<div class='col-6 text-center slide'>
                    <label class='col-8'>Titulo:
                        <input class='form-control form-control-sm' type=text value="${i.titulo}" />
                    </label>

                    <label class='col-8'>Dia(s):
                        <input class='form-control form-control-sm' type=text value="${i.dia}" />
                    </label>

                    <label class='col-8'>Horario(s):
                        <input class='form-control form-control-sm' type=text value="${i.horario}" />
                    </label>

                    <div class='col-10 offset-2 img' data-img-url='${i.img}' style='background-image:url(/${i.img});background-size: 100%;'>
                        <br /><br /><br />
						<input type=hidden value="${i.img}" />
                    </div>
                    <div class='col-12 text-center mb-4'>
                        <button  class='apagar m-btn text-center m-btn--pill btn-outline-danger btn'>
                            <i class='la las la-trash'></i> Apagar
                        </button>
                    </div>
                </div>`
            );
        }
    });

    One(".submit").click(function(){
        $.post(LWDKLocal, {data: getSlideData()}, function(success){
            return success ? successRequest(refresh):errorRequest(refresh);
        });
    });

	/* bkp: [{"titulo":"Missas","dia":"Quartas","horario":"Às 9h, 15h e 19h","img":"imgpagsfixas/14c83d567c40b09e9ab716496a1ed563.jpg"},{"titulo":"Missas","dia":"Sextas","horario":"Às 9h, 15h e 19h","img":"imgpagsfixas/d70513f0c172a1a8d198924d52c572fa.jpg"},{"titulo":"Missas","dia":"Domingos","horario":"Às 8h","img":"imgpagsfixas/1943167ab98e6f2e9b9d06b73037b38e.jpg"},{"titulo":"Adoração Ao Santíssimo Sacramento de Jesus","dia":"Dia 29 de cada Mês","horario":"De 7h ás 8h","img":"imgpagsfixas/13318ba278206e1784260e4d577061df.jpg"},{"titulo":"Adoração","dia":"Dia 29 de cada Mês","horario":"Às 9h, 15h e 19h","img":"imgpagsfixas/ba434e275a638d377474d764bdf53340.jpg"},{"titulo":"Adoração Ao Santíssimo Sacramento Missa Especial de Cura e Libertação","dia":"Quintas","horario":"Às 9h","img":"imgpagsfixas/8914d05d07e6c4a919cd27304f95f02d.jpg"}] */

    ({valuesof}) !== null && setSlideData(Object.values({valuesof}.data));
});
