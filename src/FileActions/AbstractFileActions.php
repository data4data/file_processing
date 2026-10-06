<?php

declare(strict_types=1);

namespace App\FileActions;

use App\Contract\FileActionsInterface;
use App\Exception\FileNotFoundException;
use App\Exception\StorageException;

abstract class AbstractFileActions implements FileActionsInterface
{
    protected function checkFileExists(string $path): void
    {
        if (!is_file($path)) {
            throw new FileNotFoundException(basename($path));
        }
    }

    protected function readContent(string $path): string
    {
        $this->checkFileExists($path);

        $content = file_get_contents($path);

        if ($content === false) {
            throw new StorageException('Could not read file "' . basename($path) . '".');
        }

        return $content;
    }

    protected function saveContent(string $path, string $content): void
    {
        if (file_put_contents($path, $content, LOCK_EX) === false) {
            throw new StorageException('Could not save file "' . basename($path) . '".');
        }
    }
}
