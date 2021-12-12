<?php

	$basicAuthUserName = 'acc_mA17e5QUQQHxW7BO'; // The username to use with basic authentication
	$basicAuthPassword = 'pk_m0pMeL1cQfK4xr2R'; // The password to use with basic authentication

	require "pagarme-core-api-php-main/vendor/autoload.php";

	$client = new PagarmeCoreApiLib\PagarmeCoreApiClient($basicAuthUserName, $basicAuthPassword);

	$orderId = 'orderId';
	$request = new PagarmeCoreApiLib\CreateOrderItemRequest();
	$idempotencyKey = 'idempotency-key';

	$result = $orders->createOrderItem($orderId, $request, $idempotencyKey);

	echo "<pre>";

	var_dump($result);
?>
