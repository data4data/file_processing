<?php

namespace App\Http;

use App\Exception\InvalidRequestException;
use App\Traits\JsonDecodeTrait;

class Request
{
    use JsonDecodeTrait;

    private string $method;
    private array $query;
    private string $body;
    private array $files;

    public function __construct(string $method, array $query, string $body = '', array $files = [])
    {
        $this->method = strtoupper($method);
        $this->query = $query;
        $this->body = $body;
        $this->files = $files;
    }

    public static function fromGlobals(): self
    {
        return new self($_SERVER['REQUEST_METHOD'] ?? 'GET', $_GET, file_get_contents('php://input'), $_FILES);
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function get(string $key, ?string $default = null): ?string
    {
        if (!isset($this->query[$key]) || !is_string($this->query[$key])) {
            return $default;
        }

        $value = trim($this->query[$key]);

        return $value === '' ? $default : $value;
    }

    public function getRequired(string $key): string
    {
        $value = $this->get($key);

        if ($value === null) {
            throw new InvalidRequestException('Parameter "' . $key . '" is required.');
        }

        return $value;
    }

    public function getJsonBody(): array
    {
        if (trim($this->body) === '') {
            throw new InvalidRequestException('Request body is empty.');
        }

        $data = $this->decodeJson($this->body);

        if ($data === null) {
            throw new InvalidRequestException('Request body is not valid JSON.');
        }

        return $data;
    }

    public function getUploadedFile(string $key): array
    {
        if (!isset($this->files[$key])) {
            throw new InvalidRequestException('No file uploaded.');
        }

        if ($this->files[$key]['error'] !== UPLOAD_ERR_OK) {
            throw new InvalidRequestException('File upload failed.');
        }

        return $this->files[$key];
    }
}
