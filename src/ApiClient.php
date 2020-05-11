<?php

declare(strict_types=1);

namespace Acme;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\RequestOptions;
use JsonException;
use Psr\Http\Message\ResponseInterface;

class ApiClient
{
    private GuzzleClient $httpClient;
    private CommentFactory $commentFactory;

    public function __construct(?GuzzleClient $httpClient = null)
    {
        if ($httpClient === null) {
            $httpClient = new GuzzleClient([
                'base_uri' => 'http://example.com',
                RequestOptions::HTTP_ERRORS => false,
            ]);
        }
        $this->httpClient = $httpClient;
        $this->commentFactory = new CommentFactory();
    }

    /**
     * Gets a list of Comments from the api
     *
     * @return Comment[]
     *
     * @throws ApiException
     */
    public function list(): array
    {
        $response = $this->httpClient->get('/comments');
        $list = $this->processJsonResponse($response);
        assert($list !== null);

        return $this->commentFactory->fromList($list);
    }

    /**
     * @return array<array{id: int, name: string, text: string}>|null
     */
    private function processJsonResponse(ResponseInterface $response): ?array
    {
        if ($response->getStatusCode() !== 200) {
            throw new ApiException('Api response code is not 200', $response);
        }
        try {
            $json = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new ApiException('Json parse error', $response);
        }

        if (
            !array_key_exists('status', $json)
            || !is_bool($json['status'])
            || !array_key_exists('data', $json)
            || (!is_null($json['data']) && !is_array($json['data']))
        ) {
            throw new ApiException('Api response schema is not recognized', $response);
        }

        if (!$json['status']) {
            throw new ApiException('Api response status is falsy', $response);
        }

        return $json['data'];
    }

    /**
     * Adds a Comment to the list
     *
     * @throws ApiException
     */
    public function add(string $name, string $text): void
    {
        $response = $this->httpClient->post('/comment', [
            RequestOptions::FORM_PARAMS => compact('name', 'text'),
        ]);
        $this->processJsonResponse($response);
    }

    /**
     * Updates a Comment in the list
     *
     * @throws ApiException
     */
    public function update(int $id, string $name, string $text): void
    {
        $response = $this->httpClient->put('/comment/' . $id, [
            RequestOptions::FORM_PARAMS => compact('name', 'text'),
        ]);
        $this->processJsonResponse($response);
    }
}
