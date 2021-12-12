<?php

function ctrl_connect_picpay($args){
    return new class extends APPControls {
    	public $set = null;

		public $picpaytoken = null;
		public $firstname   = null;
		public $lastname    = null;
		public $document    = null;
		public $email       = null;
		public $title       = null;
		public $phone       = null;
		public $referenceId = null;
		public $expiresAt   = null;
		public $callbackUrl = null;
		public $returnUrl   = null;

		function __construct(){
			$this->set = new class {
				public $parent = null;
				public $client = null;

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
						}

						function doc(String $doc){
							$this->parent->parent->document = $doc;
						}

						function email(String $email){
							$this->parent->parent->email = $email;
						}

						function phone(String $phone){
							$this->parent->parent->phone = $phone;
						}

						function returnUrl(String $url){
							$this->parent->parent->returnurl = $url;
						}
					};

					$this->client->parent = $this;
				}

				function token(String $keypass){
					$this->parent->picpaytoken = $keypass;
				}

				function price(Float $price){
					$this->parent->price = (float)$price;
				}

				function callbackUrl(String $url){
					$this->parent->callbackurl = $url;
				}

				function referenceID(String $ref){
					$this->parent->referenceId = $ref;
				}

				function title(String $title){
					$this->parent->title = $title;
				}

				function expiresAt(String $expires){
					$this->parent->expiresAt = $expires;
				}
			};
			$this->get = new class {
				public $parent = null;
				public $client = null;

				function __construct(){
					$this->client = new class {
						public $parent = null;
						function name(){
							return "{$this->parent->parent->firstname} {$this->parent->parent->lastname}";
						}

						function doc(){
							return $this->parent->parent->document;
						}

						function email(){
							return $this->parent->parent->email;
						}

						function phone(){
							return $this->parent->parent->phone;
						}

						function returnUrl(){
							return $this->parent->parent->returnurl;
						}
					};

					$this->client->parent = $this;
				}

				function price(){
					return $this->parent->price;
				}

				function title(){
					return $this->parent->title;
				}

				function callbackUrl(){
					return $this->parent->callbackurl;
				}

				function referenceID(){
					return $this->parent->referenceId;
				}

				function expiresAt(){
					return $this->parent->expiresAt;
				}
			};

			$this->set->parent = $this;
			$this->get->parent = $this;

			$this->referenceId = mt_rand(100000, 999999);
			$this->expiresAt   = (string)((int)date("Y") + 1) . "-05-01T16:00:00-03:00";
		}

		function pay($object=false){
			$dados = [
			    "referenceId" => !empty($this->title)
					? "{$this->title} | {$this->referenceId}"
					: "{$this->referenceId}",
			    "callbackUrl"=> $this->callbackurl,
			    "returnUrl"=> $this->returnurl . $this->referenceId . "/",
			    "value"=> $this->price,
			    "expiresAt"=> $this->expiresAt,
			    "buyer"=> [
			      "firstName"=> $this->firstname,
			      "lastName"=> $this->lastname,
			      "document"=> $this->document,
			      "email"=> $this->email,
			      "phone"=> $this->phone
			    ]
			];


			$ch = curl_init('https://appws.picpay.com/ecommerce/public/payments');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($dados));
			curl_setopt($ch, CURLOPT_HTTPHEADER, ['x-picpay-token: ' . $this->picpaytoken]);

			$res = curl_exec($ch);
			curl_close($ch);

			$retorno = json_decode($res);

			$result = (array(
				"qrcode" => isset($retorno->qrcode->base64) && !empty($retorno->qrcode->base64)
					? $retorno->qrcode->base64
					: "",
				"id" => isset($retorno->referenceId) && !empty($retorno->referenceId)
					? $this->referenceId
					: "",
				"link" => isset($retorno->paymentUrl) && !empty($retorno->paymentUrl)
					? $retorno->paymentUrl
					: ""
			));

			return isset($retorno->message) ? false : ($object?(object)$result:$result);
		}
	};
}
