LWDKExec(function(){
	FormCreateAction("frmSignUp", function(data, instance){
		$.post("/registrar_conta/", data, function(result){
			if(result.error.length > 0){
				swal.fire("", result.error, "error");
			} else {
				window.top.location.href = "/";
			}
		});
	});
});
