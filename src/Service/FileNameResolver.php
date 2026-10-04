<?php

namespace App\Service;

class FileNameResolver
{
    public function getFreeName(string $folder, string $fileName): string
    {
        $name = pathinfo($fileName, PATHINFO_FILENAME);
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $freeName = $fileName;
        $number = 1;

        while (file_exists($folder . '/' . $freeName)) {
            $freeName = $name . '_' . $number . '.' . $extension;
            $number++;
        }

        return $freeName;
    }
}
