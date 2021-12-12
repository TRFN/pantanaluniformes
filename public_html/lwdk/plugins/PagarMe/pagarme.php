<?php
	class pagarme {
		/**
		*	@param object [private] $connection -> "Parametro responsavel
		*							por armazenar o objeto do pagarme".
		*/

		private $connection = null;

		/**
		* @method "Metodo de inicializacao (bootin) da aplicacao."
		*/

		function __construct(){

		}

		/**
		* @method "Metodo de conexao da aplicacao."
		* @return void
		*/

		function connect(String $token){
			/**
			* @param string $api -> "Parametro que contem a API-Token de acesso"
			* @return void
			*/
			if(empty($this->connection)){
				include_once('framework-library/autoload.php');
				$this->connection = new PagarMe\Client($token);
			}
		}

		/**
		* @method "Metodo de vefirificacao se a aplicacao ja esta inicializada."
		* @return boolean
		*/

		function started(){
			return!empty($this->connection);
		}

		/**
		* @method "Metodo de importacao de funcao interna da biblioteca"
		* @return object
		*/

		function transactions(){
			return $this->connection->transactions();
		}

		/**
		* @method "Metodo de importacao de funcao interna da biblioteca"
		* @return object
		*/

		function refunds(){
			return $this->connection->refunds();
		}

		/**
		* @method "Metodo de importacao de funcao interna da biblioteca"
		* @return object
		*/

		function cards(){
			return $this->connection->cards();
		}

		/**
		* @method "Metodo de importacao de funcao interna da biblioteca"
		* @return object
		*/

		function plans(){
			return $this->connection->plans();
		}

		/**
		* @method "Metodo de importacao de funcao interna da biblioteca"
		* @return object
		*/

		function subscriptions(){
			return $this->connection->subscriptions();
		}

		/**
		* @method "Metodo de importacao de funcao interna da biblioteca"
		* @return object
		*/

		function postbacks(){
			return $this->connection->postbacks();
		}

		/**
		* @method "Metodo de importacao de funcao interna da biblioteca"
		* @return object
		*/

		function balances(){
			return $this->connection->balances();
		}

		/**
		* @method "Metodo de importacao de funcao interna da biblioteca"
		* @return object
		*/

		function payables(){
			return $this->connection->payables();
		}

		/**
		* @method "Metodo de importacao de funcao interna da biblioteca"
		* @return object
		*/

		function transfers(){
			return $this->connection->transfers();
		}

		/**
		* @method "Metodo de importacao de funcao interna da biblioteca"
		* @return object
		*/

		function bulkAnticipations(){
			return $this->connection->bulkAnticipations();
		}

		/**
		* @method "Metodo de importacao de funcao interna da biblioteca"
		* @return object
		*/

		function bankAccounts(){
			return $this->connection->bankAccounts();
		}

		/**
		* @method "Metodo de importacao de funcao interna da biblioteca"
		* @return object
		*/

		function recipients(){
			return $this->connection->recipients();
		}

		/**
		* @method "Metodo de importacao de funcao interna da biblioteca"
		* @return object
		*/

		function customers(){
			return $this->connection->customers();
		}

		/**
		* @method "Metodo de importacao de funcao interna da biblioteca"
		* @return object
		*/

		function paymentLinks(){
			return $this->connection->paymentLinks();
		}

		/**
		* @method "Metodo de importacao de funcao interna da biblioteca"
		* @param array $arguments -> "Parametro para definir o que sera procurado"
		* @example => [
		*		    "type" => "transaction",
		*		    "query" => [
		*		        "query" => [
		*		            "terms" => [
		*		                "items.id" => [8, 9] // Busca transações com itens de ID 8 e 9
		*		            ]
		*		        ]
		*		    ]
		*		]
		* @return object
		*/

		function search(Array $arguments){
			return $this->connection->search()->get($arguments);
		}


	}
