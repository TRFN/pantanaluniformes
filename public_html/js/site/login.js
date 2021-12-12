LWDKExec(function(){
	FormCreateAction("frmSignIn", function(data, instance){
		$.post("/login/", data, function(result){
			if(result === false){
				swal.fire("", "Email ou senha incorretos! Tente novamente.", "error");
			} else {
				window.location.href = "/";
			}
		});
	});

	FormCreateAction("frmRecuperar", (data) => {
		$.post("/recuperar_conta/", {"email": data.email}, function(errorlevel){
			switch(errorlevel){
				case 0:
					Swal.fire('', 'Um email de recupera&ccedil;&atilde;o foi enviado com os procedimentos para altera&ccedil;&atilde;o da senha atual.', 'success');
				break;
				case 1:
					Swal.fire('', 'O email para recupera&ccedil;&atilde;o da conta n&atilde;o p&ocirc;de ser localizado. Contate o administrador do sistema para obter suporte.', 'warning');
				break;
				default:
					Swal.fire('', 'Ocorreu um erro interno...<br>Tente novamente mais tarde.', 'question');
				break;
			}
		});
	});
});
