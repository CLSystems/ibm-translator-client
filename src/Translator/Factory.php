<?php

namespace CLSystems\IBMWatson\Translator\Translator;

use GuzzleHttp\Client as HttpClient;
use CLSystems\IBMWatson\Translator\Api\Transport as ApiTransport;
use CLSystems\IBMWatson\Translator\Translator\Client as TranslatorClient;

class Factory
{
	/**
	 * @param string $apiKey
	 * @param string $host
	 * @return Client
	 */
	public static function getTranslator(string $apiKey, string $host): Client
	{
		$httpClient = new HttpClient([
			'base_uri' => $host,
		]);
		$apiTransport = new ApiTransport($apiKey, $httpClient);
		return new TranslatorClient($apiTransport);
	}
}
