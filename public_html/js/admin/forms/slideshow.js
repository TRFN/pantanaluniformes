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
        parallelUploads: 2,
        maxFiles: 8,
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
                        if(confirm("Deseja mesmo remover este slide?")){the.slideUp('slow', function(){
                            $(this).remove();
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
        slides = MapTranslate(slides, ["titulo","desc","url","btn-txt","img"]);
        return slides.length ? slides:null;
    });

    const setSlideData = window.setSlideData = ((data) => {
        if(data === null || typeof data !== "object" || typeof data.length !== "number" || data.length === 0)return;

        for(i of data){
            $("#gallery.start").removeClass("start").html("");
            (!(typeof i != "object" || typeof i.img != "string" || i.img.length < 4)) && $("#gallery").append(
                `<div class='col-3 text-center slide'>
                    <label class='col-12'>Titulo:
                        <input class='form-control form-control-sm' type=text value="${i.titulo}" />
                    </label>

                    <label class='col-12'>Descrição:
                        <input class='form-control form-control-sm' type=text value="${i.desc}" />
                    </label>

                    <label class='col-12'>URL/Link:
                        <input class='form-control form-control-sm' type=text value="${i.url}" />
                    </label>

                    <label class='col-12'>Texto Bot&atilde;o
                        <input class='form-control form-control-sm' type=text value="${i["btn-txt"]}" />
                        <input type=hidden value='${i.img}' />
                    </label>

                    <div class='col-12 img' style='background-image:url(/${i.img})'>
                        <br /><br /><br />
                    </div>
                    <div class='col-12 text-center'>
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

    ({valuesof}) !== null && setSlideData(Object.values({valuesof}.data));
});
