<?php

namespace App\Exceptions\SatuSehat;

use Exception;

class SatuSehatException extends Exception
{
    protected ?array $context;

    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null, ?array $context = null)
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    public function getContext(): ?array
    {
        return $this->context;
    }

    public function render(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'context' => $this->getContext(),
        ], $this->getCode() ?: 500);
    }
}
