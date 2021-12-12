LWDKExec(function(){
	FormCreateAction("mudar-perfil", function(data, instance){
		$.post("/action_change_profile/", data, function(result){
			if(result.length > 0){
				swal.fire("", result, "error");
			} else {
				swal.fire("", "Seu perfil foi modificado com sucesso!", "success").then(()=>window.location.reload());
			}
		});
	});
});
