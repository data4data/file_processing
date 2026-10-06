<?php

declare(strict_types=1);

namespace App\Service;

use App\Contract\FileActionsInterface;
use App\Exception\FileNotFoundException;
use App\Exception\InvalidFileContentException;
use App\Exception\StorageException;
use App\Exception\UnsupportedFormatException;

class FileService
{
    private string $storagePath;
    private array $fileActionsByFormat = [];
    private FileNameResolver $fileNameResolver;

    public function __construct(string $storagePath, array $fileActionsList, FileNameResolver $fileNameResolver)
    {
        $this->storagePath = rtrim($storagePath, '/');
        $this->fileNameResolver = $fileNameResolver;

        foreach ($fileActionsList as $fileActions) {
            $this->fileActionsByFormat[$fileActions->getFormat()] = $fileActions;
        }
    }

    public function listFiles(): array
    {
        $fileNames = [];

        foreach (scandir($this->storagePath) as $fileName) {
            if (is_file($this->getPath($fileName)) && $this->isSupported($fileName)) {
                $fileNames[] = $fileName;
            }
        }

        return $fileNames;
    }

    public function read(string $fileName): array
    {
        return $this->readFile($this->findFileActions($fileName), $this->getPath($fileName), $fileName);
    }

    public function write(string $fileName, array $data): bool
    {
        $fileActions = $this->findFileActions($fileName);
        $path = $this->getPath($fileName);
        $isNewFile = !is_file($path);

        $fileActions->write($path, $data);

        return $isNewFile;
    }

    public function delete(string $fileName): void
    {
        $this->findFileActions($fileName);
        $path = $this->getPath($fileName);

        if (!is_file($path)) {
            throw new FileNotFoundException(basename($fileName));
        }

        if (!unlink($path)) {
            throw new StorageException('Could not delete file "' . basename($fileName) . '".');
        }
    }

    public function upload(string $fileName, string $tmpPath): string
    {
        $fileName = basename($fileName);
        $fileActions = $this->findFileActions($fileName);

        $this->readFile($fileActions, $tmpPath, $fileName);

        $savedName = $this->fileNameResolver->reserveFreeName($this->storagePath, $fileName);

        if (!move_uploaded_file($tmpPath, $this->getPath($savedName))) {
            unlink($this->getPath($savedName));

            throw new StorageException('Could not save file "' . $fileName . '".');
        }

        return $savedName;
    }

    private function readFile(FileActionsInterface $fileActions, string $path, string $fileName): array
    {
        try {
            return $fileActions->read($path);
        } catch (InvalidFileContentException $e) {
            throw new InvalidFileContentException(
                'File "' . basename($fileName) . '" has invalid content. ' . $e->getMessage(),
                0,
                $e
            );
        }
    }

    private function getPath(string $fileName): string
    {
        return $this->storagePath . '/' . basename($fileName);
    }

    private function isSupported(string $fileName): bool
    {
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        return isset($this->fileActionsByFormat[$extension]);
    }

    private function findFileActions(string $fileName): FileActionsInterface
    {
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!isset($this->fileActionsByFormat[$extension])) {
            throw new UnsupportedFormatException($extension, array_keys($this->fileActionsByFormat));
        }

        return $this->fileActionsByFormat[$extension];
    }
}
