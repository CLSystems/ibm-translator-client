<?php

namespace CLSystems\IBMWatson\Translator\Api;

use Exception;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException as HttpClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\RequestOptions as HTTPRequestOptions;
use CLSystems\IBMWatson\Translator\Exception\Api as ApiException;
use CLSystems\IBMWatson\Translator\Exception\Client as ClientException;
use CLSystems\IBMWatson\Translator\Exception\Transport as TransportException;
use CLSystems\IBMWatson\Translator\Exception\Unauthorized as UnauthorizedException;

/**
 * This client deals with the low level aspects of interacting with Watson's API.
 * It serves as an encapsulation layer when interacting with the actual HTTP transport.
 */
class Transport
{
	const MIME_TYPE_PLAIN_TEXT = 'text/plain';
	const MIME_TYPE_JSON       = 'application/json';

	/**
	 * @var HttpClient
	 */
	protected $httpClient;

	/**
	 * @var string
	 */
	protected $apiKey;

	/**
	 * @param string $apiKey
	 * @param HttpClient|null $httpClient
	 */
	public function __construct(string $apiKey, HttpClient $httpClient)
	{
		$this->apiKey = $apiKey;
		$this->httpClient = $httpClient;
	}

	/**
	 * @param string $httpMethod
	 * @param string $apiUri
	 * @param string $requestBody
	 * @return mixed
	 * @throws GuzzleException
	 * @throws TransportException
	 */
	public function sendSynchronousPlainTextApiRequest(string $httpMethod, string $apiUri, string $requestBody): array
	{
		return $this->sendSynchronousApiRequest($httpMethod, $apiUri, $requestBody, static::MIME_TYPE_PLAIN_TEXT);
	}

	/**
	 * @param string $httpMethod
	 * @param string $apiUri
	 * @param string $requestBody
	 * @return mixed
	 * @throws GuzzleException
	 * @throws TransportException
	 */
	public function sendSynchronousJsonApiRequest(string $httpMethod, string $apiUri, string $requestBody): array
	{
		return $this->sendSynchronousApiRequest($httpMethod, $apiUri, $requestBody, static::MIME_TYPE_JSON);
	}

	/**
	 * @param string $httpMethod
	 * @param string $apiUri
	 * @param string $requestBody
	 * @param string $bodyContentType
	 * @return mixed
	 * @throws TransportException|GuzzleException
	 */
	public function sendSynchronousApiRequest(
		string $httpMethod,
		string $apiUri,
		string $requestBody,
		string $bodyContentType
	): array
	{
		try
		{
			$response = $this->httpClient->request(
				$httpMethod,
				$apiUri,
				[
					HTTPRequestOptions::BODY    => $requestBody,
					HTTPRequestOptions::HEADERS => [
						'apikey'       => $this->apiKey,
						'Accept'       => 'application/json',
						'Content-Type' => $bodyContentType,
					],
				]
			);
			return json_decode($response->getBody()->getContents(), true);
		}
		catch (HttpClientException $e)
		{
			$this->handleClientException($e);
		}
		catch (ServerException $e)
		{
			throw new ApiException($e->getMessage(), 0, $e);
		}
		catch (Exception $e)
		{
			throw new TransportException("Unexpected transport error.", 0, $e);
		}

		return [];
	}

	/**
	 * @param HttpClientException $exception
	 * @throws TransportException
	 */
	protected function handleClientException(HttpClientException $exception)
	{
		switch ($exception->getResponse()->getStatusCode())
		{
			case 401:
				throw new UnauthorizedException("Unauthorized API request.");
			default:
				throw new ClientException($exception->getMessage());
		}
	}
}
