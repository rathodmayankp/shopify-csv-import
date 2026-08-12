<?php

namespace App\Exceptions;

use Exception;

class ShopifyApiException extends Exception
{
    protected array $responseBody;

    public function __construct(string $message, array $responseBody = [], int $code = 0)
    {
        parent::__construct($message, $code);

        $this->responseBody = $responseBody;
    }

    public function responseBody(): array
    {
        return $this->responseBody;
    }
}
