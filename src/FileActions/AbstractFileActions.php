<?php

namespace App\FileActions;

use App\Contract\FileActionsInterface;
use App\Exception\FileNotFoundException;

abstract class AbstractFileActions implements FileActionsInterface
{
    public function supports(string $extension): bool
    {
        return strtolower($extension) === $this->getFormat();
    }

    public function delete(string $path): void
    {
        $this->checkFileExists($path);

        unlink($path);
    }

    protected function checkFileExists(string $path): void
    {
        if (!file_exists($path)) {
            throw new FileNotFoundException(basename($path));
        }
    }
}
