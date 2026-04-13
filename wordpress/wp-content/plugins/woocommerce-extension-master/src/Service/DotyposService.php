<?php

namespace App\Service;

	use WP_Http;

	class DotyposService
	{

		private $token;

		private $cloudId;

		private $wp_http;

		private $logger;

		const AUTH_URL = 'https://api.dotykacka.cz/v2/signin/token';

		const ACCESS_TOKEN_CACHE_KEY = 'dtk_access_token';

		private $cloudUrl = 'https://api.dotykacka.cz/v2/clouds/';

		public function __construct($token, $cloudId)
		{
			$this->token = $token;
			$this->cloudId = $cloudId;
			$this->wp_http = new WP_Http();
			$this->cloudUrl .= $cloudId;
			$this->logger = wc_get_logger();
		}

		private function getAccessToken()
		{
			$accessToken = get_transient(self::ACCESS_TOKEN_CACHE_KEY . '2');
			if (!$accessToken) {
				$auth = 'User ' . $this->token;
				$body = isset($this->cloudId) ? json_encode(['_cloudId' => $this->cloudId]) : '';
				$response = $this->wp_http->post(self::AUTH_URL, [
					'headers' => [
						'Authorization' => $auth,
						'Content-Type' => 'application/json'
					],
					'body' => $body
				]);
				$data = json_decode($response['body'], true);
				$accessToken = $data['accessToken'];
				if (empty($accessToken)) {
					//$this->logger->debug('Error: Empty Dotypos access token.', array('source' => 'dotypos_integration'));
				} else {
					set_transient(self::ACCESS_TOKEN_CACHE_KEY, $accessToken, 60 * 59);
				}
				return $accessToken;
			} else {
				return $accessToken;
			}
		}

		public function getProduct(string $productId)
		{

				$decodedResponse = $this->doAuthorizedGetRequest(
					$this->cloudUrl . '/products/'.$productId,
					true
				);
				return $decodedResponse;
		}

		public function getProducts(): iterable
		{

			$currentPage = 1;
			$lastPage = PHP_INT_MAX;

			while ($currentPage <= $lastPage) {
				$decodedResponse = $this->doAuthorizedGetRequest(
					$this->cloudUrl . '/products?page=' . $currentPage . '&limit=100',
					false
				);
                if(isset($decodedResponse['totalItemsCount']) && $decodedResponse['totalItemsCount'] === "0") {
                    break;
                }
				$currentPage++;
				//$lastPage = 100;
				$lastPage = (int)$decodedResponse['lastPage'];
				//$this->logger->debug('Data: '.json_encode($decodedResponse), array('source' => 'dotypos_integration'));
				foreach ($decodedResponse['data'] as $product) {
					yield $product;
				}
			}
		}

		public function getProductETag(string $productId)
		{
			return $this->doAuthorizedGetRequestHeaders( $this->cloudUrl . '/products/' . $productId)['ETag'];
		}

		public function patchProduct(string $productId, array $payload)
		{
			$productETag = $this->getProductETag($productId);
			//$oldProduct = $this->getProduct($productId);
			//$this->logger->debug('Before merge: '.json_encode($oldProduct), array('source' => 'dotypos_integration'));
			//$payload = array_merge($oldProduct, $payload);
			//$this->logger->debug('After merge: '.json_encode($payload), array('source' => 'dotypos_integration'));
			$status_code = $this->doAuthorizedPatchRequest($this->cloudUrl . '/products/' . $productId, json_encode($payload), $productETag);
		}

		public function postProduct(array $payload)
		{
			return $this->doAuthorizedPostRequest( $this->cloudUrl . '/products', json_encode($payload));
		}

		public function postWebhook(array $payload)
		{
			return $this->doAuthorizedPostRequest( $this->cloudUrl . '/webhooks', json_encode($payload));
		}

		public function deleteWebhook(int $id)
		{
			return $this->doAuthorizedDeleteRequest( $this->cloudUrl . '/webhooks/'.$id);
		}

		public function getProductOnWarehouse(string $warehouseId, string $productId): array
		{
				$decodedResponse = $this->doAuthorizedGetRequest(
					$this->cloudUrl . '/warehouses/'.$warehouseId.'/products/'.$productId
				);
				//$this->logger->debug($this->cloudUrl . '/warehouses/'.$warehouseId.'/products/'.$productId, array('source' => 'dotypos_integration'));
				if($decodedResponse === null) {
					return [];
				}
				else {
					return $decodedResponse;
				}
		}

		public function getProductsOnWarehouse(string $warehouseId): iterable
		{

			$currentPage = 1;
			$lastPage = PHP_INT_MAX;

			while ($currentPage <= $lastPage) {
				$decodedResponse = $this->doAuthorizedGetRequest(
					$this->cloudUrl .  '/warehouses/'.$warehouseId.'/products?page=' . $currentPage . '&limit=100'
				);

                if(isset($decodedResponse['totalItemsCount']) && $decodedResponse['totalItemsCount'] === "0") {
                    break;
                }
				$currentPage++;
				$lastPage = (int)$decodedResponse['lastPage'];
				//$lastPage = 1;

				foreach ($decodedResponse['data'] as $product) {
					yield $product;
				}
			}
		}

		public function getWarehouses(): iterable
		{

			$currentPage = 1;
			$lastPage = PHP_INT_MAX;

			while ($currentPage <= $lastPage) {
				$decodedResponse = $this->doAuthorizedGetRequest(
					$this->cloudUrl . '/warehouses?page=' . $currentPage . '&limit=100'
				);
                if(isset($decodedResponse['totalItemsCount']) && $decodedResponse['totalItemsCount'] === "0") {
                    break;
                }
				$currentPage++;
				$lastPage = (int)$decodedResponse['lastPage'];

				foreach ($decodedResponse['data'] as $warehouse) {
					yield $warehouse;
				}
			}
		}

		public function getCategories(): iterable
		{

			$currentPage = 1;
			$lastPage = PHP_INT_MAX;

			while ($currentPage <= $lastPage) {
				$decodedResponse = $this->doAuthorizedGetRequest(
					$this->cloudUrl . '/categories?page=' . $currentPage . '&limit=100'
				);
                if(isset($decodedResponse['totalItemsCount']) && $decodedResponse['totalItemsCount'] === "0") {
                    break;
                }
				$currentPage++;
				$lastPage = (int)$decodedResponse['lastPage'];

				foreach ($decodedResponse['data'] as $category) {
					yield $category;
				}
			}
		}

		public function getCategoryETag(string $categoryId)
		{
			return $this->doAuthorizedGetRequestHeaders( $this->cloudUrl . '/categories/' . $categoryId)['ETag'];
		}

		public function patchCategory(string $categoryId, array $payload)
		{
			$categoryETag = $this->getCategoryETag($categoryId);
			$status_code = $this->doAuthorizedPatchRequest($this->cloudUrl . '/categories/' . $categoryId, json_encode($payload), $categoryETag);
		}

		public function postCategory(array $payload)
		{
			return $this->doAuthorizedPostRequest( $this->cloudUrl . '/categories', json_encode($payload));
		}

		public function updateProductStock($warehouse_id, $product_id, $change, $invoiceNumber = 'WC')
		{
			$payload = [
				'updatePurchasePrice' => false,
				'invoiceNumber' => $invoiceNumber,
				'items' => [[
					'_productId' => (int)$product_id,
					'quantity' => $change,
				]]
			];

			$status_code = $this->doAuthorizedPostRequestWithoutResponse($this->cloudUrl . '/warehouses/' . $warehouse_id . '/stockups', $payload);
			//$this->logger->debug($this->cloudUrl . '/warehouses/' . $warehouse_id . '/stockups '.$status_code, array('source' => 'dotypos_integration'));

		}

		private function doAuthorizedGetRequest($url, bool $debug = false)
		{
			if($debug) {
				//$this->logger->debug('Token: '.$accessToken, array('source' => 'dotypos_integration'));
				$this->logger->debug('URL: '.$url, array('source' => 'dotypos_integration'));
			}
			$accessToken = $this->getAccessToken();
			$response = $this->wp_http->request($url, [
				'method' => 'GET',
				'headers' => [
					'Authorization' => 'Bearer ' . $accessToken,
					'Content-Type' => 'application/json'
				]
			]);
			if($debug) {
				$this->logger->debug('Response: '.json_encode($response), array('source' => 'dotypos_integration'));
			}
			return json_decode($response['body'], true);
		}


		private function doAuthorizedGetRequestHeaders($url)
		{
			$accessToken = $this->getAccessToken();
			$response = $this->wp_http->request($url, [
				'method' => 'GET',
				'headers' => [
					'Authorization' => 'Bearer ' . $accessToken,
					'Content-Type' => 'application/json'
				]
			]);
			return $response['headers'];
		}

		private function getResponseStatusCode($response): int
		{
			return (int)($response['response']['code'] ?? 0);
		}

		private function doAuthorizedPatchRequest($url, $body, $etag)
		{
			$accessToken = $this->getAccessToken();
			$response = $this->wp_http->request($url, [
				'method' => 'PATCH',
				'headers' => [
					'Authorization' => 'Bearer ' . $accessToken,
					'Content-Type' => 'application/json',
					'If-Match' => $etag,
				],
				'body' => $body,
			]);
            if($this->getResponseStatusCode($response) !== 200) {
                wc_get_logger()->debug('Dotypos API [Post] Error: '. json_encode($response, JSON_THROW_ON_ERROR), array('source' => 'dotypos_integration'));
            }
			//$this->logger->debug(json_encode($response), array('source' => 'dotypos_integration'));
			return json_decode($response['body'], true);
		}

		private function doAuthorizedPostRequest($url, $body)
		{
			$accessToken = $this->getAccessToken();
			$response = $this->wp_http->request($url, [
				'method' => 'POST',
				'headers' => [
					'Authorization' => 'Bearer ' . $accessToken,
					'Content-Type' => 'application/json',
				],
				'body' => $body,
			]);
            if($this->getResponseStatusCode($response) !== 200) {
                wc_get_logger()->debug('Dotypos API [Post] Error: '. json_encode($response, JSON_THROW_ON_ERROR), array('source' => 'dotypos_integration'));
            }
			//$this->logger->debug($accessToken, array('source' => 'dotypos_integration'));
			//$this->logger->debug('Post: '. json_encode($response), array('source' => 'dotypos_integration'));
			return json_decode($response['body'], true);
		}
		private function doAuthorizedDeleteRequest($url)
		{
			$accessToken = $this->getAccessToken();
			$response = $this->wp_http->request($url, [
				'method' => 'DELETE',
				'headers' => [
					'Authorization' => 'Bearer ' . $accessToken,
					'Content-Type' => 'application/json',
				]
			]);
			return $this->getResponseStatusCode($response);
		}

		private function doAuthorizedPostRequestWithoutResponse($url, $body)
		{
			$accessToken = $this->getAccessToken();
			$body = json_encode($body);
			$response = $this->wp_http->post($url, [
				'headers' => [
					'Authorization' => 'Bearer ' . $accessToken,
					'Content-Type' => 'application/json'
				],
				'body' => $body
			]);
            if($this->getResponseStatusCode($response) !== 200) {
                wc_get_logger()->debug('Dotypos API [Post] Error: '. json_encode($response, JSON_THROW_ON_ERROR), array('source' => 'dotypos_integration'));
            }
			return $this->getResponseStatusCode($response);
		}
	}
