window.loadedApp = (()=>{return typeof window["initApp"] == "boolean" && window["initApp"];});

LWDKExec(() => {
	$(".edit-area").css({
		border: "1px dashed #555",
	});

	$("main#app-page-maker").hide();

	console.log("{mdl}");

	window.page_set_model = ((selected = (parseInt("{mdl}".split("sk")[1]) - 1)) => {
		function fn(selected){
			let modelos = MapEl(
					"main#app-page-maker header#models section",
					function () {
						return this.innerHTML;
					},
					false,
					false
				),
				secoes = MapEl(
					"main#app-page-maker header#sections section content",
					function () {
						return this.innerHTML;
					},
					false,
					false
				),
				conteudos = MapEl(
					"main#app-page-maker header#sections section data",
					function () {
						return this.innerHTML;
					},
					false,
					false
				),
				ids = MapEl(
					"main#app-page-maker header#models section",
					function () {
						return this.getAttribute("name");
					},
					false,
					false
				),
				applyEditor = () => {
					addEditPart("#app [data-name=sub-titulo]", ["|", "bold", "italic", "|", "heading", "|", "undo", "redo"], "Sub-Titulo da pagina", (the) => the.execute("heading", { value: "heading4" }));

					addEditPart("#app [data-name=titulo]", ["|", "bold", "italic", "|", "heading", "|", "undo", "redo"], "Titulo da pagina", (the) => the.execute("heading", { value: "heading1" }));

					addEditPart(
						"#app [data-name=conteudo]",
						["selectAll", "undo", "redo", "bold", "italic", "blockQuote", "link", "imageUpload", "mediaEmbed", "indent", "outdent", "numberedList", "bulletedList", "insertTable", "tableColumn", "tableRow", "mergeTableCells"],
						"Conteudo da pagina"
					);
				}, modelo, replacer;

			modelo = modelos[selected];
			modelo = modelo.replaceAll(/\%([0-9]){1}/g, "{$1-1}");
			replacer = {};

			modelo.split("{").forEach(function (e) {
				e = e.split("}");

				if (e.length > 1) {
					e = e[0];
					n = e.split("-");
					n[0] = parseInt(n[0]);
					n[1] = parseInt(n[1]);
					replacer[`{${e}}`] = secoes[n[0] - n[1]].split("%1").join(conteudos[n[0] - n[1]]);
				}
			});

			for (i in replacer) {
				modelo = modelo.split(i).join(replacer[i]);
			}

			$("#app").html(modelo);

			$("[data-name=mdl]").val(ids[selected]);

			$(".mdl-master .mdl").removeClass("btn-primary").addClass("btn-secondary");
			$(".mdl-master").find("." + ids[selected]).removeClass("btn-secondary").addClass("btn-primary");

			applyEditor();

			One("#app form.dzn:not(.dropzone)")
				.addClass("dropzone")
				.dropzone({
					autoProcessQueue: true,
					uploadMultiple: true,
					parallelUploads: 1,
					maxFiles: 1,
					acceptedFiles: "image/*",

					init: function () {
						var myDropzone = this;

						setInterval(function () {
							$("#app .apagar").each(function () {
								One(this,"actd").click(function () {
									confirm("Deseja mesmo remover essa imagem?") &&
										$(this)
											.parent()
											.parent()
											.slideUp("slow", function () {
												$.post(LWDKLocal, { act: "erase", file: (f = $(this).find(".img:first").data("img-url")) });
												$(this).remove();
											});
								});
							});
						}, 500);

						myDropzone.on("success", function (file, response) {
							$.post("{myurl}", { imgs: response }, function (data) {
								$("#app #gallery .img-group").remove();
								$("#app #gallery").append(data);
							});
						});
					},

					complete: function (file) {
						this.removeFile(file);
					},
				});

				One("#app form.dzn2:not(.dropzone)")
					.addClass("dropzone")
					.dropzone({
						autoProcessQueue: true,
						uploadMultiple: true,
						parallelUploads: 1,
						maxFiles: 1,
						acceptedFiles: "image/*",

						init: function () {
							var myDropzone = this;

							setInterval(function () {
								$("#app .apagar").each(function () {
									One(this,"actd").click(function () {
										confirm("Deseja mesmo remover essa imagem?") &&
											$(this)
												.parent()
												.parent()
												.slideUp("slow", function () {
													$.post(LWDKLocal, { act: "erase", file: (f = $(this).find(".img:first").data("img-url")) });
													$(this).remove();
												});
									});
								});
							}, 500);

							myDropzone.on("success", function (file, response) {
								$.post("{myurl}?capa=1", { imgs: response }, function (data) {
									$("#app #gallery2 .img-group").remove();
									$("#app #gallery2").append(data);
								});
							});
						},

						complete: function (file) {
							this.removeFile(file);
						},
					});
		}

		refresh(
			()=>(
				LWDKLinks(),
				fn(selected)
			)
		);
	});

	!loadedApp() && (setTimeout(()=>page_set_model(),2000), window["initApp"]=true);
});
