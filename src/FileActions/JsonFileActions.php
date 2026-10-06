<?php

declare(strict_types=1);

namespace App\FileActions;

use App\Exception\InvalidFileContentException;
use App\Traits\JsonDecodeTrait;

class JsonFileActions extends AbstractFileActions
{
    use JsonDecodeTrait;

    public function getFormat(): string
    {
        return 'json';
    }

    public function read(string $path): array
    {
        $data = $this->decodeJson($this->readContent($path));

        if ($data === null) {
            throw new InvalidFileContentException('Invalid JSON.');
        }

        return $data;
    }

    public function write(string $path, array $data): void
    {
        $this->saveContent($path, json_encode($data, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
    }
}
