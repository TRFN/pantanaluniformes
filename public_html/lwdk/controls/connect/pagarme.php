<?php

function ctrl_connect_pagarme($args){
	/**
	* @return [anonymous] class extended to APPControls model
	*/
    return new class extends APPControls {

		/**
		* External Functions
		* @credits https://pt.coredump.biz/questions/174730/what-is-the-best-way-to-validate-a-credit-card-in-php
		*/

		protected function luhn($number){
		    // Force the value to be a string as this method uses string functions.
		    // Converting to an integer may pass PHP_INT_MAX and result in an error!
		    $number = (string)$number;

		    if (!ctype_digit($number)) {
		        // Luhn can only be used on numbers!
		        return FALSE;
		    }

		    // Check number length
		    $length = strlen($number);

		    // Checksum of the card number
		    $checksum = 0;

		    for ($i = $length - 1; $i >= 0; $i -= 2) {
		        // Add up every 2nd digit, starting from the right
		        $checksum += substr($number, $i, 1);
		    }

		    for ($i = $length - 2; $i >= 0; $i -= 2) {
		        // Add up every 2nd digit doubled, starting from the right
		        $double = substr($number, $i, 1) * 2;

		        // Subtract 9 from the double where value is greater than 10
		        $checksum += ($double >= 10) ? ($double - 9) : $double;
		    }

		    // If the checksum is a multiple of 10, the number is valid
		    return ($checksum % 10 === 0);
		}

		public function ValidCreditcard($number){
		    $card_array = array(
		        'default' => array(
		            'length' => '13,14,15,16,17,18,19',
		            'prefix' => '',
		            'luhn' => TRUE,
		        ),
		        'american express' => array(
		            'length' => '15',
		            'prefix' => '3[47]',
		            'luhn' => TRUE,
		        ),
		        'diners club' => array(
		            'length' => '14,16',
		            'prefix' => '36|55|30[0-5]',
		            'luhn' => TRUE,
		        ),
		        'discover' => array(
		            'length' => '16',
		            'prefix' => '6(?:5|011)',
		            'luhn' => TRUE,
		        ),
		        'jcb' => array(
		            'length' => '15,16',
		            'prefix' => '3|1800|2131',
		            'luhn' => TRUE,
		        ),
		        'maestro' => array(
		            'length' => '16,18',
		            'prefix' => '50(?:20|38)|6(?:304|759)',
		            'luhn' => TRUE,
		        ),
		        'mastercard' => array(
		            'length' => '16',
		            'prefix' => '5[1-5]',
		            'luhn' => TRUE,
		        ),
		        'visa' => array(
		            'length' => '13,16',
		            'prefix' => '4',
		            'luhn' => TRUE,
		        ),
		    );

		    // Remove all non-digit characters from the number
		    if (($number = preg_replace('/\D+/', '', $number)) === '')return FALSE;

		    // Use the default type
		    $type = 'default';

		    $cards = $card_array;

		    // Check card type
		    $type = strtolower($type);

		    if (!isset($cards[$type]))return FALSE;

		    // Check card number length
		    $length = strlen($number);

		    // Validate the card length by the card type
		    if (!in_array($length, preg_split('/\D+/', $cards[$type]['length'])))
		        return FALSE;

		    // Check card number prefix
		    if (!preg_match('/^' . $cards[$type]['prefix'] . '/', $number))
		        return FALSE;

		    // No Luhn check required
		    if ($cards[$type]['luhn'] == FALSE)return TRUE;

		    return $this->luhn($number);
		}

		/**
		* Private Vars
		* @var object $instance -> "Varivavel que contem o plugin PagarMe"
		* @var string $token    -> "Varivavel que contem o token da aplicacao"
		*/

		private $instance = null;
		private $token    = null;
		private $methods  = null;

		public $set = null;
		public $get = null;

		/**
		* Public Vars
		* @var string $firstname	 -> "Primeiro nome do cliente"
		* @var string $lastname      -> "Nome remanescente do cliente"
		* @var string $document      -> "Documento do cliente"
		* @var string $type_document -> "Tipo de documento (cpf ou cnpj)"
		* @var string $email         -> "Email do cliente"
		* @var string $phone         -> "Telefone do cliente (Formato: +1122344445555)"
		* @var int    $price         -> "Valor da transacao"
		* @var array  $products      -> "Vetor de itens/produtos da transacao"
		* @var int    $sendfee       -> "Valor do frete"
		* @var string $c_number      -> "Numero do cartao do cliente (Modelo: 1111222233334444 = 1111 2222 3333 4444)"
		* @var string $c_name        -> "Nome que aparece no cartao do comprador"
		* @var string $c_cvv         -> "Codigo verificador do cartao (Modelo: 123)"
		* @var string $c_expires     -> "Data de expiracao do cartao (Modelo: 1122 = 11/22)"
		* @var string $street        -> "Rua do cliente"
		* @var string $street_number -> "numero da rua"
		* @var string $state         -> "Estado do Cliente"
		* @var string $city          -> "cidade do Cliente"
		* @var string $country       -> "Pais do cliente"
		* @var string $neighborhood  -> "Bairro do cliente"
		* @var string $zipcode       -> "Cep do cliente"
		*/

		public $firstname     = null;
		public $lastname      = null;
		public $document      = null;
		public $type_document = "cpf";
		public $email         = null;
		public $phone         = null;
		public $price         = 0;
		public $products      = null;
		public $sendfee       = 0;
		public $c_number      = null;
		public $c_name        = null;
		public $c_cvv         = null;
		public $c_expires     = null;
		public $street        = null;
		public $street_number = null;
		public $state         = null;
		public $city          = null;
		public $country       = "br";
		public $neighborhood  = null;
		public $zipcode       = null;
		public $method        = "boleto";


		public $passive = false;


		/**
		* @method "Metodo de inicializacao do sistema"
		* @return self
		*/

		function __construct(){

			/**
			* @method "Metodo de definicao de parametros das transacoes"
			* @return self
			*/

			$this->methods  = array();
			$this->products = array();

			/* DOCUMENTAR */

			$this->methods["boleto"] = function(){
				$make = [
				    'amount' => $this->get->price(),
				    'payment_method' => 'boleto',
				    'customer' => [
				        'external_id' => md5($this->get->client->document()),
				        'name' => $this->get->client->name(),
				        'type' => 'individual',
				        'country' => $this->get->client->country(),
				        'documents' => [
				          [
				            'type' => $this->get->client->document_type(),
				            'number' => $this->get->client->document()
				          ]
				        ],
				        'phone_numbers' => [ $this->get->client->phone() ],
				        'email' => $this->get->client->email()
					],
					'items' => $this->get->products()
				];

				if($this->get->shippingFee() > 99){
					$make['amount'] += $this->get->shippingFee();
					$make['shipping'] = [
				        'name' => $this->get->client->name(),
				        'fee' => $this->get->shippingFee(),
				        'delivery_date' => date("Y-m-d"),
				        'expedited' => false,
				        'address' => [
				          'country' => $this->get->client->country(),
				          'street' => $this->get->client->street(),
				          'street_number' => $this->get->client->street_number(),
				          'state' => $this->get->client->state(),
				          'city' => $this->get->client->city(),
				          'neighborhood' => $this->get->client->neighborhood(),
				          'zipcode' => $this->get->client->zipcode()
				        ]
				    ];
				}

				return $make;
			};

			/* DOCUMENTAR */

			$this->methods['credit_card'] = function(){
				$make = [
				    'amount' => $this->get->price(),
					'payment_method' => 'credit_card',
				    'card_holder_name' => $this->get->card->name(),
				    'card_cvv' => $this->get->card->cvv(),
				    'card_number' => $this->get->card->number(),
				    'card_expiration_date' => $this->get->card->expires(),
				    'customer' => [
				        'external_id' => md5($this->get->client->document()),
				        'name' => $this->get->client->name(),
				        'type' => 'individual',
				        'country' => $this->get->client->country(),
				        'documents' => [
				          [
				            'type' => $this->get->client->document_type(),
				            'number' => $this->get->client->document()
				          ]
				        ],
				        'phone_numbers' => [ $this->get->client->phone() ],
				        'email' => $this->get->client->email()
					],
					'billing' => [
				        'name' => $this->get->client->name(),
				        'address' => [
							'country' => $this->get->client->country(),
							'street' => $this->get->client->street(),
							'street_number' => $this->get->client->street_number(),
							'state' => $this->get->client->state(),
							'city' => $this->get->client->city(),
							'neighborhood' => $this->get->client->neighborhood(),
							'zipcode' => $this->get->client->zipcode()
				        ]
				    ],
					'items' => $this->get->products()
				];

				if($this->get->shippingFee() > 99){
					$make['amount'] += $this->get->shippingFee();
					$make['shipping'] = [
				        'name' => $this->get->client->name(),
				        'fee' => $this->get->shippingFee(),
				        'delivery_date' => date("Y-m-d"),
				        'expedited' => false,
				        'address' => [
				          'country' => $this->get->client->country(),
				          'street' => $this->get->client->street(),
				          'street_number' => $this->get->client->street_number(),
				          'state' => $this->get->client->state(),
				          'city' => $this->get->client->city(),
				          'neighborhood' => $this->get->client->neighborhood(),
				          'zipcode' => $this->get->client->zipcode()
				        ]
				    ];
				}

				return $make;
			};

			/**
			* @method "Metodo de teste dos parametros"
			* @return boolean
			*/

			$this->isvalid = new class {
				public $parent  = null;
				public $card    = null;

				function __construct(){
					$this->card = new class {
						public $parent = null;

						function number(String $number = ""){
							if(empty($number) && empty($number = $this->parent->parent->c_number))
								 return false;
							else return $this->parent->parent->ValidCreditcard((string)$number);
						}

						function cvv(String $number = ""){
							if(empty($number) && empty($number = $this->parent->parent->c_cvv))
								 return false;
							else return count(preg_replace("/[^0-9]/","",$number)) !== 3;
						}

						function expires(String $number = ""){
							if(empty($number) && empty($number = $this->parent->parent->c_expires))
								 return false;
							else return count(preg_replace("/[^0-9]/","",$number)) !== 4;
						}
					};

					$this->card->parent = $this;
				}
			};

			/**
			* @method "Metodo de definicao de parametros das transacoes definidos"
			* @return self,object
			*/

			$this->set = new class {
				public $parent  = null;
				public $client  = null;
				public $card    = null;
				public $product = null;

				function __construct(){
					$this->client = new class {
						public $parent = null;
						function name(String $name){
							$name = explode(" ", $name);
							foreach(array_keys($name) as $k){
								$name[$k] = ucfirst($name[$k]);
							}
							$firstname = array_shift($name);
							$lastname = implode(" ", $name);
							$this->parent->parent->firstname = $firstname;
							$this->parent->parent->lastname = $lastname;
							return $this;
						}

						function document(String $doc){
							$this->parent->parent->document = $doc;
							return $this;
						}

						function document_type(String $type){
							$this->parent->parent->type_document = $type;
							return $this;
						}

						function email(String $email){
							$this->parent->parent->email = $email;
							return $this;
						}

						function phone(String $phone){
							$this->parent->parent->phone = $phone;
							return $this;
						}

						function street(String $street){
							$this->parent->parent->street = $street;
							return $this;
						}

						function street_number(String $street_number){
							$this->parent->parent->street_number = $street_number;
							return $this;
						}

						function city(String $city){
							$this->parent->parent->city = $city;
							return $this;
						}

						function state(String $state){
							$this->parent->parent->state = $state;
							return $this;
						}

						function country(String $country){
							$this->parent->parent->country = $country;
							return $this;
						}

						function neighborhood(String $neighborhood){
							$this->parent->parent->neighborhood = $neighborhood;
							return $this;
						}

						function zipcode(String $zipcode){
							$this->parent->parent->zipcode = $zipcode;
							return $this;
						}
					};

					$this->client->parent = $this;

					$this->card = new class {
						public $parent = null;

						function number(String $number){
							$this->parent->parent->c_number = $number;
							return $this;
						}

						function name(String $name){
							$this->parent->parent->c_name = $name;
							return $this;
						}

						function cvv(String $cvv){
							$this->parent->parent->c_cvv = $cvv;
							return $this;
						}

						function expires(String $expires){
							$this->parent->parent->c_expires = $expires;
							return $this;
						}
					};

					$this->card->parent = $this;

					$this->product = new class {
						public $parent = null;

						function add(Array $product){
							$this->parent->parent->products[] = array(
					          'id' => isset($product["id"])?$product["id"]:(string)count($this->parent->parent->products),
					          'title' => $product["name"],
					          'unit_price' => ($_price = (int)((float)$product["price"] * 100)),
					          'quantity' => ($_quantity = (int)$product["quantity"]),
					          'tangible' => isset($product["virtual"])?!$product["virtual"]:true
					        );

							$this->parent->parent->price += $_price * $_quantity;
							return $this;
						}

						function remove(int $id){
							if(isset($this->parent->parent->products[$id-1])){
								$_price = $this->parent->parent->products[$id-1]["unit_price"];
								$_quantity = $this->parent->parent->products[$id-1]["quantity"];

								unset($this->parent->parent->products[$id-1]);

								$this->parent->parent->products = array_values($this->parent->parent->products);

								$this->parent->parent->price -= $_price * $_quantity;
							}
							return $this;
						}

						function removeLast(){
							$this->remove(count($this->parent->parent->products));
							return $this;
						}

						function removeFirst(){
							$this->remove(1);
							return $this;
						}

						function changePrice(int $id, float $price){
							if(isset($this->parent->parent->products[$id-1])){
								$_price_old = $this->parent->parent->products[$id-1]["unit_price"];
								$this->parent->parent->products[$id-1]["unit_price"] = ($_price = (int)((float)$price * 100));

								$this->parent->parent->price -= $_price_old * $this->parent->parent->products[$id-1]["quantity"];

								$this->parent->parent->price += $_price * $this->parent->parent->products[$id-1]["quantity"];
							}
							return $this;
						}

						function changeQuantity(int $id, float $quantity){
							if(isset($this->parent->parent->products[$id-1])){
								$_quantity_old = $this->parent->parent->products[$id-1]["quantity"];
								$this->parent->parent->products[$id-1]["quantity"] = ($_price = (int)((float)$price * 100));

								$this->parent->parent->price -= $_quantity_old * $this->parent->parent->products[$id-1]["unit_price"];

								$this->parent->parent->price += $_quantity * $this->parent->parent->products[$id-1]["unit_price"];
							}
							return $this;
						}
					};

					$this->product->parent = $this;
				}

				function method(int $method){
					switch($method){
						case 0:
							$this->parent->method = "boleto";
						break;
						case 1:
							$this->parent->method = "credit_card";
						break;
					}
					return $this;
				}

				function shippingFee(Float $price){
					$this->parent->sendfee = (int)((float)$price * 100);
					return $this;
				}
			};

			/**
			* @method "Metodo de retorno de parametros das transacoes definidos anteriormente"
			* @return string,int,float
			*/

			$this->get = new class {
				public $parent  = null;
				public $client  = null;
				public $card    = null;

				function __construct(){
					$this->client = new class {
						public $parent = null;
						function name(){
							return "{$this->parent->parent->firstname} {$this->parent->parent->lastname}";
						}

						function document(){
							return $this->parent->parent->document;
						}

						function document_type(){
							return $this->parent->parent->type_document;
						}

						function email(){
							return $this->parent->parent->email;
						}

						function phone(){
							return $this->parent->parent->phone;
						}

						function street(){
							return $this->parent->parent->street;
						}

						function street_number(){
							return $this->parent->parent->street_number;
						}

						function city(){
							return $this->parent->parent->city;
						}

						function state(){
							return $this->parent->parent->state;
						}

						function country(){
							return $this->parent->parent->country;
						}

						function neighborhood(){
							return $this->parent->parent->neighborhood;
						}

						function zipcode(){
							return $this->parent->parent->zipcode;
						}
					};

					$this->client->parent = $this;

					$this->card = new class {
						public $parent = null;

						function number(){
							return $this->parent->parent->c_number;
						}

						function name(){
							return $this->parent->parent->c_name;
						}

						function cvv(){
							return $this->parent->parent->c_cvv;
						}

						function expires(){
							return $this->parent->parent->c_expires;
						}
					};

					$this->card->parent = $this;
				}

				function shippingFee(){
					return $this->parent->sendfee;
				}

				function products(){
					return $this->parent->products;
				}

				function method(){
					return $this->parent->method;
				}

				function price(){
					return $this->parent->price;
				}

				function transactions($filter=[]){
					return $this->parent->instance()->transactions()->getList($filter);
				}
			};

			$this->set->parent     = $this;
			$this->get->parent     = $this;
			$this->isvalid->parent = $this;

			return ($this->instance = $this->loadPlugin("PagarMe@pagarme"));
		}

		function instance(){
			return $this->instance;
		}

		/**
		* @method "Metodo de verificacao da validade da transacao PagarMe"
		* @return boolean
		*/

		function valid(){
			if($this->price < 100){
				return false;
			}

			foreach(["name","document","document_type","phone","email"] as $required){
				if(empty($this->get->client->{$required}()) && !$this->passive){
					return false;
				}
			}

			if(($this->get->shippingFee() > 99 || $this->get->method() === 1) && !$this->passive){
				foreach(['country', 'street', 'street_number', 'state', 'city', 'neighborhood', 'zipcode'] as $required){
					if(empty($this->get->client->{$required}())){
						return false;
					}
				}
			}

			if($this->get->method() === "credit_card"){
				foreach(['number', 'cvv', 'expires', 'name'] as $required){
					if(empty($this->get->card->{$required}())){
						return false;
					}
				}
			}

			if(count($this->products) < 1){
				return false;
			}

			return true;
		}

		/**
		* @method "Metodo de definicao do token de acesso PagarMe"
		* @return void
		*/

		function token(String $token){
			$this->token = $token;
		}

		/**
		* @method "Metodo de conexao inicial a plataforma"
		* @return void
		*/

		function connect(){
			if(empty($this->token) || $this->instance->started()){
				return false;
			}
			$this->instance->connect($this->token);
			return true;
		}

		function refund(String $id, int $value = 0){
			$refund = array( 'id' => '335858087' );

			if($value > 99){
				$refund["amount"] = $value;
			}

			$refunded = $this->instance->transactions()->refund($refund);

			return array(
				"id" => $refunded->tid,
				"success" => ($refunded->status === "refunded")
			);
		}

		function pay(){
			if($this->valid()){
				$transaction = $this->instance->transactions()->create($this->methods[$this->get->method()]());
				return $this->get->method() == "boleto" ? array(
					"id" => (string)$transaction->tid,
					"success" => ($transaction->status === "waiting_payment"),
					"type" => $this->get->method(),
					"ticket" => ($transaction->status === "waiting_payment"
						? array(
							"url" => $transaction->boleto_url,
							"code" => $transaction->boleto_barcode,
							"expires" => $transaction->boleto_expiration_date
						)
						: false
					),
					"object" => $transaction
				) : array(
					"id" => (string)$transaction->tid,
					"success" => ($transaction->status === "paid"),
					"type" => $this->get->method(),
					"object" => $transaction
				);
			} else {
				return false;
			}

		}
	};
}
