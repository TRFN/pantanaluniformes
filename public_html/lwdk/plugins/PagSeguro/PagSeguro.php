<?php
class PagSeguro {
	public $email         = "";
	private $token_sandbox = "";
	public $token_oficial = "";
	public $url_retorno   = "";

	//URL OFICIAL
	//COMENTE AS 4 LINHAS ABAIXO E DESCOMENTE AS URLS DA SANDBOX PARA REALIZAR TESTES
	private $url              = "https://ws.pagseguro.uol.com.br/v2/checkout/";
	private $url_redirect     = "https://pagseguro.uol.com.br/v2/checkout/payment.html?code=";
	private $url_notificacao  = 'https://ws.pagseguro.uol.com.br/v2/transactions/notifications/';
	private $url_transactions = 'https://ws.pagseguro.uol.com.br/v2/transactions/';

	//URL SANDBOX
	//DESCOMENTAR PARA REALIZAR TESTES

	// private $url              = "https://ws.sandbox.pagseguro.uol.com.br/v2/checkout/";
	// private $url_redirect     = "https://sandbox.pagseguro.uol.com.br/v2/checkout/payment.html?code=";
	// private $url_notificacao  = 'https://ws.sandbox.pagseguro.uol.com.br/v2/transactions/notifications/';
	// private $url_transactions = 'https://ws.sandbox.pagseguro.uol.com.br/v2/transactions/';


	private $email_token = "";//NÃO MODIFICAR
	private $statusCode = array(0=>"Pendente",
								1=>"Aguardando pagamento",
								2=>"Em análise",
								3=>"Pago",
								4=>"Disponível",
								5=>"Em disputa",
								6=>"Devolvida",
								7=>"Cancelada");

	public function email_token(){
		return ($this->email_token = "?email=".$this->email."&token=".$this->token_oficial);
	}

	public function __construct(){
		$this->email_token();
		$this->url .= $this->email_token;
	}

	private function generateUrl($dados,$retorno){
		//Configurações
		$data['email'] = $this->email;
		$data['token'] = $this->token_oficial;
		$data['currency'] = 'BRL';

		//Itens
		$qtdtotal = 0;
		foreach($dados["itens"] as $item){
			$qtdtotal += (int)$item["qtd"];
		}
		foreach($dados["itens"] as $n=>$item){
			$k = $n + 1;
			$data['itemId'.$k] = $item["id"];
			$data['itemDescription'.$k] = mb_strimwidth("{$item["nome"]}", 0, 50, "...");
			$data['itemAmount'.$k] = str_replace(",", ".", (preg_replace("/[^0-9,]/", "", $item['preco'])));
			$data['itemQuantity'.$k] = $item["qtd"];
			$data['itemShippingCost'.$k] = number_format($dados["frete"] / $qtdtotal,2,".","");
			$data['itemWeight'.$k] = '0';
		}
		//Dados do pedido
		$data['reference'] = $dados['codigo'];

		//Dados do comprador

		//Tratar telefone
		$telefone = substr($dados['telefone'],2);
		$ddd = substr($dados['telefone'],0,2);

		// echo $ddd;

		if(empty($ddd)){
			$ddd = "31";
		}

		//Tratar CEP
		$cep = implode("",explode("-",isset($dados['cep'])?$dados['cep']:""));
		$cep = implode("",explode(".",$cep));
		$data['senderName'] = $dados['nome'];
		$data['senderAreaCode'] = $ddd;
		$data['senderPhone'] = $telefone;
		$data['senderEmail'] = $dados['email'];
		$data['senderCPF'] = (string)(preg_replace("/[^0-9]/", "", $dados['cpf']));
		$data['shippingType'] = '3';
		$data['extraAmount'] = isset($dados["desc"]) ? ("-" . str_replace(",", ".", (preg_replace("/[^0-9,]/", "", "{$dados["desc"]}")))) : "0.00";

		$data['shippingAddressStreet'] = isset($dados['rua'])?$dados['rua']:"";
		$data['shippingAddressNumber'] = isset($dados['numero'])?$dados['numero']:"";
		$data['shippingAddressComplement'] = isset($dados['complemento'])?$dados['complemento']:"";
		$data['shippingAddressDistrict'] = isset($dados['bairro'])?$dados['bairro']:"";
		$data['shippingAddressPostalCode'] = $cep;
		$data['shippingAddressCity'] = isset($dados['cidade'])?$dados['cidade']:"";
		$data['shippingAddressState'] = strtoupper(isset($dados['estado'])?$dados['estado']:"");
		$data['shippingAddressCountry'] = 'BRA';
		$data['redirectURL'] = $retorno;

		// header("Content-Type: text/plain");
		// var_dump($data);
		// exit;

		return http_build_query($data);
	}

	public function executeCheckout($dados,$retorno){

		if(isset($dados['codigo_pagseguro']) && $dados['codigo_pagseguro']!=null){
			header('Location: '.$this->url_redirect.$dados['codigo_pagseguro']);
		}

		$dados = $this->generateUrl($dados,$retorno);

		$curl = curl_init($this->url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded; charset=UTF-8'));
		curl_setopt($curl, CURLOPT_POSTFIELDS, $dados);
		$xml= curl_exec($curl);

		if($xml == 'Unauthorized'){
			//Insira seu código de prevenção a erros
			echo "Erro: Dados invalidos - Unauthorized";
			exit;//Mantenha essa linha
		}

		curl_close($curl);
		$xml_obj = simplexml_load_string($xml);
		if(count($xml_obj -> error) > 0){
			//Insira seu código de tratamento de erro, talvez seja útil enviar os códigos de erros.
			echo $xml."<br><br>";
			echo "Erro-> ".var_export($xml_obj->errors,true);
			exit;
		}
		header('Location: '.$this->url_redirect.$xml_obj->code);
	}

	//RECEBE UMA NOTIFICAÇÃO DO PAGSEGURO
	//RETORNA UM OBJETO CONTENDO OS DADOS DO PAGAMENTO

	private function XML2Array(SimpleXMLElement $parent) {
	    $array = array();

	    foreach ($parent as $name => $element) {
	        ($node = & $array[$name])
	            && (1 === count($node) ? $node = array($node) : 1)
	            && $node = & $node[];

	        $node = $element->count() ? $this->XML2Array($element) : trim($element);
	    }

	    return $array;
	}


	public function executeNotification($code){
		$url = $this->url_notificacao.$code.$this->email_token;

		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		$transaction= curl_exec($curl);
		if($transaction == 'Unauthorized'){
			return 'Unauthorized';
		}
		curl_close($curl);
		$date = new DateTime("{$transaction_obj->lastEventDate}");
		$transaction_obj = simplexml_load_string($transaction);

		$formasdepgto = array();
		$formasdepgto[1] = "Cartão de Crédito";
		$formasdepgto[2] = "Boleto Bancário";
		$formasdepgto[3] = "Outros Meios";
		$formasdepgto[4] = "Saldo de Conta PagSeguro";
		$formasdepgto[5] = "Outros Meios";
		$formasdepgto[7] = "Outros Meios";

		$meios = array();
		$meios[101] = "Cartão de crédito Visa";
		$meios[102] = "Cartão de crédito MasterCard";
		$meios[103] = "Cartão de crédito American Express";
		$meios[104] = "Cartão de crédito Diners";
		$meios[105] = "Cartão de crédito Hipercard";
		$meios[106] = "Cartão de crédito Aura";
		$meios[107] = "Cartão de crédito Elo";
		$meios[108] = "Cartão de crédito PLENOCard";
		$meios[109] = "Cartão de crédito PersonalCard";
		$meios[110] = "Cartão de crédito JCB";
		$meios[111] = "Cartão de crédito Discover";
		$meios[112] = "Cartão de crédito BrasilCard";
		$meios[113] = "Cartão de crédito FORTBRASIL";
		$meios[114] = "Cartão de crédito CARDBAN";
		$meios[115] = "Cartão de crédito VALECARD";
		$meios[116] = "Cartão de crédito Cabal";
		$meios[117] = "Cartão de crédito Mais!";
		$meios[118] = "Cartão de crédito Avista";
		$meios[119] = "Cartão de crédito GRANDCARD";
		$meios[120] = "Cartão de crédito Sorocred";
		$meios[122] = "Cartão de crédito Up Policard";
		$meios[123] = "Cartão de crédito Banese Card";
		$meios[201] = "Boleto Bradesco";
		$meios[202] = "Boleto Santander";
		$meios[301] = "Débito online Bradesco";
		$meios[302] = "Débito online Itaú";
		$meios[303] = "Débito online Unibanco";
		$meios[304] = "Débito online Banco do Brasil";
		$meios[305] = "Débito online Banco Real";
		$meios[306] = "Débito online Banrisul";
		$meios[307] = "Débito online HSBC";
		$meios[401] = "Saldo PagSeguro";
		$meios[501] = "Oi Paggo";
		$meios[701] = "Depósito em conta - Banco do Brasil";

		$produtos = array();

		foreach($transaction_obj->items->item as $item){
			$produtos[] = $this->XML2Array($item);
		}

		$status = (int)("{$transaction_obj->status}" === (string)($this->getStatusByCode("{$transaction_obj->code}")) ? "{$transaction_obj->status}":"0");
		return array(
			"codigo" => "{$transaction_obj->code}",
			"nome" => "{$transaction_obj->sender->name}",
			"email" => "{$transaction_obj->sender->email}",
			"status" => $this->getStatusText("{$status}"),
			"statusCode" => $status,
			"pagamento" => array(
				"forma" => array($formasdepgto["{$transaction_obj->paymentMethod->type}"],"{$transaction_obj->paymentMethod->type}"),
				"meio" => array($meios["{$transaction_obj->paymentMethod->code}"],"{$transaction_obj->paymentMethod->code}")
			),
			"financeiro" => array(
				"valor" => "{$transaction_obj->grossAmount}",
				"taxas" => "{$transaction_obj->feeAmount}",
				"recebimento" => isset($transaction_obj->escrowEndDate) && !empty("{$transaction_obj->escrowEndDate}") ? (new DateTime("{$transaction_obj->escrowEndDate}"))->format("d/m/Y") : "Não Informado"
			),
			"produtos" => $produtos
		);
	}

	//Obtém o status de um pagamento com base no código do PagSeguro
	//Se o pagamento existir, retorna um código de 1 a 7
	//Se o pagamento não exitir, retorna NULL
	public function getStatusByCode($code){
		$url = $this->url_transactions.$code.$this->email_token;
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		$transaction = curl_exec($curl);
		if($transaction == 'Unauthorized') {
			//Insira seu código avisando que o sistema está com problemas
			//sugiro enviar um e-mail avisando para alguém fazer a manutenção
			return ("Forbidden");//Mantenha essa linha para evitar que o código prossiga
		}

		$transaction_obj = simplexml_load_string($transaction);

		if(count($transaction_obj -> error) > 0) {
		   //Insira seu código avisando que o sistema está com problemas
		   var_dump($transaction_obj);
		}

		if(isset($transaction_obj->status))
			return $transaction_obj->status;
		else
			return NULL;
	}

	//Obtém o status de um pagamento com base na referência
	//Se o pagamento existir, retorna um código de 1 a 7
	//Se o pagamento não exitir, retorna NULL
	public function getStatusByReference($reference){

		$url = $this->url_transactions.$this->email_token()."&reference=".$reference;
		// echo $url;
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		$transaction = curl_exec($curl);
		if($transaction == 'Unauthorized') {
			//Insira seu código avisando que o sistema está com problemas
			return ($transaction);//Mantenha essa linha para evitar que o código prossiga
		}
		$transaction_obj = simplexml_load_string($transaction);
		if(count($transaction_obj->error) > 0) {
		   //Insira seu código avisando que o sistema está com problemas
		   return $this->XML2Array($transaction_obj);
		}
		//print_r($transaction_obj);
		if(isset($transaction_obj->transactions->transaction->status))
			return $transaction_obj->transactions->transaction;
		else
			return NULL;
	}

	public function getStatusText($code){
		if($code>=1 && $code<=7)
			return $this->statusCode[$code];
		else
			return $this->statusCode[0];
	}

}
?>
