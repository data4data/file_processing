<?php

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
        $this->checkFileExists($path);

        $data = $this->decodeJson(file_get_contents($path));

        if ($data === null) {
            throw new InvalidFileContentException('File "' . basename($path) . '" contains invalid JSON.');
        }

        return $data;
    }

    public function write(string $path, array $data): void
    {
        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
    }
}
