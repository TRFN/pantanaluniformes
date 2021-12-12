// SCRIPTS PERSONALIZADOS

window.graphsattempts = 0;

Dropzone.autoDiscover = false;

const formatarCampo = function formatarCampo(campoTexto) {
    if (campoTexto.value.length <= 11) {
        campoTexto.value = mascaraCpf(campoTexto.value);
    }

    if(!/[^0-9]/.test(campoTexto.value) && campoTexto.value.length > 0){
        errorRequest(function(){
            campoTexto.focus();
        }, "Insira um CPF v&aacute;lido!");
    }
}
const retirarFormatacao = function retirarFormatacao(campoTexto) {
    campoTexto.value = campoTexto.value.replace(/(\.|\/|\-)/g,"");
}
const mascaraCpf = function mascaraCpf(valor) {
    return valor.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/g,"\$1.\$2.\$3\-\$4");
}
const mascaraCnpj = function mascaraCnpj(valor) {
    return valor.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/g,"\$1.\$2.\$3\/\$4\-\$5");
}

const Regex = ({
    Email: /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,
    NotOnlyNumbers: /[^0-9]/
});

LWDKInitFunction.addFN(function(){
    window.verifyLogged == 0 && CheckConnection();

    $("select option").each(function(){
        if(this.innerText.length == 0){
            $(this).remove();
        }
    });

    $(".sortable").each(function(){
        let e;
        return (e=One(this,"apply_sort")).length > 0 && new Sortable(e[0], {
        	animation: 150,
        	ghostClass: 'blue-background-class'
        });
    });

    $('.summernote:not(.__summernote__)').each(function(i){
        $(this).summernote("destroy");
        setTimeout(()=>One(this, "summernote").summernote({ height: 250, lang: "pt-BR", followingToolbar: false, toolbar: [
        // [groupName, [list of button]]
        ['style', ['bold', 'italic', 'underline']],
        ['fontsize', ['fontsize']],
        ['color', ['color']],
        ['para', ['ul', 'ol', 'paragraph']],
        ['height', ['height']]
    ] }),700);
    });
});

window.applyDinamicLink = function(fnlist){
    $("[data-original-href]").each(function(){
        for(let key in fnlist){
            $(this).attr("href", $(this).data("original-href").split(`{${key}}`).join(fnlist[key]()));
        }
    });
}

setInterval(function(){
    if($("base").length>1){
        window.top.location.reload();
    }
},250);

// Checar se esta logado.

window.CheckConnection = function(){
    if(window.verifyLogged == -1)return false;
    window.verifyLogged = 1;
    $.post("/admin/session/", function(data){
        let conectado = ((typeof data === "boolean" && data === true));

        if(!conectado){
            let timerInterval;
            Swal.fire({
              title: 'Sua sessão expirou!',
              html: '<div>Você será redirecionado para<br>a pagina de login em <b></b> segundo<span>s</span>.</div>',
              timer: 33000,
              timerProgressBar: true,
              didOpen: () => {
                Swal.showLoading()
                timerInterval = setInterval(() => {
                  const content = Swal.getContent()
                  if (content) {
                    const b = content.querySelector('b');
                    const c = content.querySelector('span');
                    const d = content.querySelector('div');
                    const t = ~~(Swal.getTimerLeft() / 1000)-1;
                    if (b && t > 0) {
                      t===1&&(c.textContent="");
                      b.textContent = t > 30 ? 30:t;
                    } else {
                      d.innerHTML = "Redirecionando para pagina<br>de login, aguarde...";
                    }
                  }
              }, 500)
              },
              willClose: () => {
                clearInterval(timerInterval)
              }
            }).then((result) => {
                window.top.location.href = "/admin/login/"
            });
        } else {
            setTimeout(CheckConnection, 5e3);
        }
    });
};

LWDKInitFunction.addFN(function(){
    $("#m_ver_menu li").click(function(){
        $("#m_ver_menu > li.m-menu__item--active").removeClass("m-menu__item--active");
        $(this).addClass("m-menu__item--active");
    });
});

window.uploadSingleImage = function(url, id, callback, quality=0.333333333333333333333, width=1024, height=768){
    if($("#" + id)[0].files.length==0)return callback(-1);
    function resize(id, callback, width, height){
        var filesToUpload = $("#" + id)[0].files;
        var file = filesToUpload[0];

        function df(dataurl, filename) {
           var arr = dataurl.split(','),
               mime = arr[0].match(/:(.*?);/)[1],
               bstr = atob(arr[1]),
               n = bstr.length,
               u8arr = new Uint8Array(n);

           while(n--){
               u8arr[n] = bstr.charCodeAt(n);
           }

           return new File([u8arr], filename, {type:mime});
       }

        var img = document.createElement("img");
        var reader = new FileReader();
        reader.onload = function(e) {
            img.src = e.target.result;

            img.onload = function(){
                var ctx = (canvas=document.createElement('canvas')).getContext("2d");
                ctx.drawImage(img, 0, 0);

                var MAX_WIDTH = width;
                var MAX_HEIGHT = height;
                var width = img.width;
                var height = img.height;

                if (width > height) {
                  if (width > MAX_WIDTH) {
                    height *= MAX_WIDTH / width;
                    width = MAX_WIDTH;
                  }
                } else {
                  if (height > MAX_HEIGHT) {
                    width *= MAX_HEIGHT / height;
                    height = MAX_HEIGHT;
                  }
                }
                canvas.width = width;
                canvas.height = height;

                var ctx = canvas.getContext("2d");
                ctx.drawImage(img, 0, 0, width, height);

                file = df(canvas.toDataURL("image/jpeg", quality),file.name.split(".")[0] + ".jpg");

                callback(file);
            }
        };
        reader.readAsDataURL(file);

        return reader;
    }

    return resize(id, function(file){
        let formData = new FormData();
        formData.append(id, file);
        $.ajax({
           type: 'POST',
           url: url,
           data: formData,
           processData: false,
           contentType: false
       }).done(callback);
   }, width, height, quality);
};

/* MOVE ELEMENT ON ARRAY SYSTEM */

window.verifyLogged = 0;

Array.prototype.moveElement = function moveElement(a, b, g = false){
	if(typeof a == "undefined" || typeof b == "undefined"){return false}
	let c = this, d = c.splice(a, 1), e = (g?c.splice(b+a, c.length-b-a):c.splice(b, c.length-b));
	c.push(d[0]);
	for(let f = 0; f < e.length; f++){
		c.push(e[f]);
    }
	return c;
}

/* OBJECT KEYS */


/*
USAGE: ([-Array- Object]).moveElement(position, new_position, ?relative);
-> {position}: Index of element to move
-> {new_position}: New position of element to move
-> {relative[?opt]}: If true, the new position will be equivalent to the initial position. (Default: false)
AUTHOR: Tulio Rodrigues de Freitas Nascimento
*/

window.loadingRequest = (function(text="Processando, aguarde...", icon=true){
    Swal.fire({
        position: 'top-end',
        html: text + (icon?' <i class="la la-refresh la-spin"></i>':''),
        showConfirmButton: false,
        allowOutsideClick: false
    });
});

window.successRequest = (function(action=null, text='Opera&ccedil;&atilde;o realizada com sucesso!'){
    Swal.fire({
        title: 'Concluido!',
        html: text,
        icon: 'success',
        confirmButtonText: 'Fechar'
    }).then(response => {
        if(action !== null){
            action();
        }
    });
});

window.errorRequest = (function(action=null, text='N&atilde;o foi poss&iacute;vel completar sua solicita&ccedil;&atilde;o...<br><strong>Tente novamente</strong>.'){
    Swal.fire({
        title: 'Erro',
        html: text,
        icon: 'error',
        confirmButtonText: 'Fechar'
    }).then(response => {
        if(action !== null){
            action();
        }
    });
});

(function($) {
  $.extend($.summernote.lang, {
    'pt-BR': {
      font: {
        bold: 'Negrito',
        italic: 'Itálico',
        underline: 'Sublinhado',
        clear: 'Remover estilo da fonte',
        height: 'Altura da linha',
        name: 'Fonte',
        strikethrough: 'Riscado',
        subscript: 'Subscrito',
        superscript: 'Sobrescrito',
        size: 'Tamanho da fonte',
      },
      image: {
        image: 'Imagem',
        insert: 'Inserir imagem',
        resizeFull: 'Redimensionar Completamente',
        resizeHalf: 'Redimensionar pela Metade',
        resizeQuarter: 'Redimensionar a um Quarto',
        floatLeft: 'Flutuar para Esquerda',
        floatRight: 'Flutuar para Direita',
        floatNone: 'Não Flutuar',
        shapeRounded: 'Forma: Arredondado',
        shapeCircle: 'Forma: Círculo',
        shapeThumbnail: 'Forma: Miniatura',
        shapeNone: 'Forma: Nenhum',
        dragImageHere: 'Arraste Imagem ou Texto para cá',
        dropImage: 'Solte Imagem ou Texto',
        selectFromFiles: 'Selecione a partir dos arquivos',
        maximumFileSize: 'Tamanho máximo do arquivo',
        maximumFileSizeError: 'Tamanho máximo do arquivo excedido.',
        url: 'URL da imagem',
        remove: 'Remover Imagem',
        original: 'Original',
      },
      video: {
        video: 'Vídeo',
        videoLink: 'Link para vídeo',
        insert: 'Inserir vídeo',
        url: 'URL do vídeo?',
        providers: '(YouTube, Google Drive, Vimeo, Vine, Instagram, DailyMotion or Youku)',
      },
      link: {
        link: 'Link',
        insert: 'Inserir link',
        unlink: 'Remover link',
        edit: 'Editar',
        textToDisplay: 'Texto para exibir',
        url: 'Para qual URL este link leva?',
        openInNewWindow: 'Abrir em uma nova janela',
      },
      table: {
        table: 'Tabela',
        addRowAbove: 'Adicionar linha acima',
        addRowBelow: 'Adicionar linha abaixo',
        addColLeft: 'Adicionar coluna à esquerda',
        addColRight: 'Adicionar coluna à direita',
        delRow: 'Excluir linha',
        delCol: 'Excluir coluna',
        delTable: 'Excluir tabela',
      },
      hr: {
        insert: 'Linha horizontal',
      },
      style: {
        style: 'Estilo',
        p: 'Normal',
        blockquote: 'Citação',
        pre: 'Código',
        h1: 'Título 1',
        h2: 'Título 2',
        h3: 'Título 3',
        h4: 'Título 4',
        h5: 'Título 5',
        h6: 'Título 6',
      },
      lists: {
        unordered: 'Lista com marcadores',
        ordered: 'Lista numerada',
      },
      options: {
        help: 'Ajuda',
        fullscreen: 'Tela cheia',
        codeview: 'Ver código-fonte',
      },
      paragraph: {
        paragraph: 'Parágrafo',
        outdent: 'Menor tabulação',
        indent: 'Maior tabulação',
        left: 'Alinhar à esquerda',
        center: 'Alinhar ao centro',
        right: 'Alinha à direita',
        justify: 'Justificado',
      },
      color: {
        recent: 'Cor recente',
        more: 'Mais cores',
        background: 'Fundo',
        foreground: 'Fonte',
        transparent: 'Transparente',
        setTransparent: 'Fundo transparente',
        reset: 'Restaurar',
        resetToDefault: 'Restaurar padrão',
        cpSelect: 'Selecionar',
      },
      shortcut: {
        shortcuts: 'Atalhos do teclado',
        close: 'Fechar',
        textFormatting: 'Formatação de texto',
        action: 'Ação',
        paragraphFormatting: 'Formatação de parágrafo',
        documentStyle: 'Estilo de documento',
        extraKeys: 'Extra keys',
      },
      help: {
        'insertParagraph': 'Inserir Parágrafo',
        'undo': 'Desfazer o último comando',
        'redo': 'Refazer o último comando',
        'tab': 'Tab',
        'untab': 'Desfazer tab',
        'bold': 'Colocar em negrito',
        'italic': 'Colocar em itálico',
        'underline': 'Sublinhado',
        'strikethrough': 'Tachado',
        'removeFormat': 'Remover estilo',
        'justifyLeft': 'Alinhar à esquerda',
        'justifyCenter': 'Centralizar',
        'justifyRight': 'Alinhar à esquerda',
        'justifyFull': 'Justificar',
        'insertUnorderedList': 'Lista não ordenada',
        'insertOrderedList': 'Lista ordenada',
        'outdent': 'Recuar parágrafo atual',
        'indent': 'Avançar parágrafo atual',
        'formatPara': 'Alterar formato do bloco para parágrafo(tag P)',
        'formatH1': 'Alterar formato do bloco para H1',
        'formatH2': 'Alterar formato do bloco para H2',
        'formatH3': 'Alterar formato do bloco para H3',
        'formatH4': 'Alterar formato do bloco para H4',
        'formatH5': 'Alterar formato do bloco para H5',
        'formatH6': 'Alterar formato do bloco para H6',
        'insertHorizontalRule': 'Inserir Régua horizontal',
        'linkDialog.show': 'Inserir um Hiperlink',
      },
      history: {
        undo: 'Desfazer',
        redo: 'Refazer',
      },
      specialChar: {
        specialChar: 'CARACTERES ESPECIAIS',
        select: 'Selecionar Caracteres Especiais',
      },
    },
  });
})(jQuery);
