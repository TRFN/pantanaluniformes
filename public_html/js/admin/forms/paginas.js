window.getAllData = function getAllData(){
	function is_cki(e){let name = $(e).data("name"); return typeof window[name] === "object"?[name,window[name].getData()]:-1;}

	return MapKeyAssign(((new Array).concat(MapEl("[data-name]", function(){
		return((r=is_cki(this)) === -1)
			? [$(this).data("name"),$(this).data("name") == "ativo" ? $(this).bootstrapSwitch("state"):$(this).val()]
			: [r[0],r[1]];
		}, 0, 0, /string|boolean/))));
};

window.saveData = function saveData() {
    Swal.fire({
        title: "Continuar ?",
        html: "Se voc&ecirc; alterar este plano, todos os clientes que aderiram a este plano ser&atilde;o afetados. Prosseguir?<br><br>",
        showCancelButton: true,
        confirmButtonText: `Continuar`,
        cancelButtonText: `N&atilde;o salvar`,
    }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
            data = getAllData();
            $.post("/admin/ajax_paginas/", data, function (success) {
                if (success === true) {
                    successRequest(null, "O Plano foi {acao} com sucesso!");
                } else {
                    errorRequest();
                }
            });
        } else {
            Swal.fire("As altera&ccedil;&otilde;es n&atilde;o ser&atilde;o salvas.", "", "info");
        }
    });
};
