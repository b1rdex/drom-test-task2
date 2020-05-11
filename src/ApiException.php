<?php

declare(strict_types=1);

namespace Acme;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class ApiException extends RuntimeException
{
    private ResponseInterface $response;

    public function __construct(string $message, ResponseInterface $response)
    {
        parent::__construct($message, 0, null);
        $this->response = $response;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
