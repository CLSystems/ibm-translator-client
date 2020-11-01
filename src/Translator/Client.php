<?php

namespace CLSystems\IBMWatson\Translator\Translator;

use CLSystems\IBMWatson\Translator\Api\Transport as ApiTransport;
use CLSystems\IBMWatson\Translator\Exception\UnexpectedAPIResponse;

/**
 * This client encapsulates the translation domain (business logic).
 */
class Client implements ServiceInterface
{
	/**
	 * @see https://cloud.ibm.com/apidocs/language-translator#versioning
	 *
	 * @const string
	 */
	const VERSION = '2018-05-01';

	/**
	 * @var ApiTransport
	 */
	protected $apiTransport;

	/**
	 * Client constructor.
	 *
	 * @param ApiTransport $apiTransport
	 */
	public function __construct(ApiTransport $apiTransport)
	{
		$this->apiTransport = $apiTransport;
	}

	/**
	 * Returns the 2 char language code.
	 *
	 * @param string $text
	 * @return string
	 */
	public function identifyLanguage(string $text)
	{
		$response = $this->sendApiRequest('POST', '/v3/identify?version=' . self::VERSION, $text);
		if (true === empty($response->languages))
		{
			$this->handleUnexpectedApiResponse('language identification');
		}

		return $response->languages[0]->language;
	}

	/**
	 * Interact with our bare API client to send a request.
	 *
	 * @param string $httpMethod
	 * @param string $apiUri
	 * @param string $requestBody
	 * @param bool $jsonRequestBodyFlag
	 * @return mixed
	 */
	protected function sendApiRequest(
		string $httpMethod,
		string $apiUri,
		string $requestBody,
		$jsonRequestBodyFlag = false
	)
	{
		if ($jsonRequestBodyFlag)
		{
			return $this->apiTransport->sendSynchronousJsonApiRequest(
				$httpMethod,
				$apiUri,
				$requestBody
			);
		}

		return $this->apiTransport->sendSynchronousPlainTextApiRequest(
			$httpMethod,
			$apiUri,
			$requestBody
		);
	}

	/**
	 * Simple translation, specify text and target language.
	 * This makes first a call to identify the source language.
	 * Simple interface, but expensive since it requires 2 API calls
	 *
	 * @param string $text
	 * @param string $targetLanguageCode 2 character language code.
	 * @return string
	 */
	public function simpleTranslate(string $text, string $targetLanguageCode)
	{
		$translateRequest = new \stdClass();
		$translateRequest->text = [$text];
		$translateRequest->model_id = $this->identifyLanguage($text) . '-' . $targetLanguageCode;

		$response = $this->sendApiRequest(
			'POST',
			'/v3/translate?version=' . self::VERSION,
			json_encode($translateRequest),
			true
		);

		if (empty($response->translations))
		{
			$this->handleUnexpectedApiResponse('translation');
		}

		return $response->translations[0]->translation;
	}

	/**
	 * This gets called when the API response was unexpected.
	 *
	 * @param string $failedActivity
	 * @throws UnexpectedAPIResponse
	 */
	protected function handleUnexpectedApiResponse(string $failedActivity)
	{
		throw new UnexpectedAPIResponse("Unexpected API response for {$failedActivity}.");
	}
}
