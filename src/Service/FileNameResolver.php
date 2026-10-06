<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\StorageException;

class FileNameResolver
{
    public function reserveFreeName(string $folder, string $fileName): string
    {
        $name = pathinfo($fileName, PATHINFO_FILENAME);
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $freeName = $fileName;
        $number = 1;

        while (!$this->createEmptyFile($folder . '/' . $freeName)) {
            $freeName = $name . '_' . $number . '.' . $extension;
            $number++;
        }

        return $freeName;
    }

    private function createEmptyFile(string $path): bool
    {
        if (file_exists($path)) {
            return false;
        }

        $file = @fopen($path, 'x');

        if ($file !== false) {
            fclose($file);

            return true;
        }

        if (file_exists($path)) {
            return false;
        }

        throw new StorageException('Could not save file "' . basename($path) . '".');
    }
}
