<?php

namespace App\Service;

use WP_Http;

class LicenceService {

	private $key;

	private $wp_http;

	private $logger;

	const AUTH_TOKEN = '0syIV29MV1qljY4W9J6j';

	const VERIFY_URL = 'https://is.dotykacka.cz/api.php';

	const ACCESS_TOKEN_CACHE_KEY = 'dotypos_licence_verified';


	public function __construct( ) {
		// $this->verifyUrl = strpos($_SERVER['HTTP_HOST'], 'glazar.cz') !== false ? 'https://dev.is.dotykacka.cz/api.php' : 'https://is.dotykacka.cz/api.php';
		$this->wp_http = new WP_Http();
		$this->logger  = wc_get_logger();
	}

	public function register($key) {
		$body = json_encode(
			[
				"id"      => null,
				"jsonrpc" => "2.0",
				"method"  => "moje.plugin.woo.register",
				"params"  => [
					"key"    => $key,
					"domain" => $_SERVER['HTTP_HOST'],
				],
			] );

		return $this->wp_http->post( self::VERIFY_URL, [
			'headers' => [
				'Authorization' => 'Bearer ' . self::AUTH_TOKEN,
				'Content-Type'  => 'application/json'
			],
			'body'    => $body
		] );
	}

	public function verify($key) {
		$body = json_encode(
			[
				"id"      => null,
				"jsonrpc" => "2.0",
				"method"  => "moje.plugin.woo.verify",
				"params"  => [
					"key"    => $key,
					"domain" => $_SERVER['HTTP_HOST'],
				],
			] );

		return $this->wp_http->post( self::VERIFY_URL, [
			'headers' => [
				'Authorization' => 'Bearer ' . self::AUTH_TOKEN,
				'Content-Type'  => 'application/json'
			],
			'body'    => $body
		] );
	}

}
