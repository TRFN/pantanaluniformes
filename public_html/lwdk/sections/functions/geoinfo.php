<?php
	trait function_geo_info {
		function getIpInfo($data="*",bool $object = false){
			return parent::control("interactive/ip")->get($data, !$object);
		}
	}
?>
