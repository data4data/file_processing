<?php

namespace App\Http;

class Response
{
    private array $data;
    private int $statusCode;

    public function __construct(array $data, int $statusCode = 200)
    {
        $this->data = $data;
        $this->statusCode = $statusCode;
    }

    public static function success(string $message, array $data = [], int $statusCode = 200): self
    {
        return new self(['status' => 'success', 'message' => $message, 'data' => $data], $statusCode);
    }

    public static function error(string $message, int $statusCode): self
    {
        return new self(['status' => 'error', 'message' => $message], $statusCode);
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function send(): void
    {
        http_response_code($this->statusCode);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
