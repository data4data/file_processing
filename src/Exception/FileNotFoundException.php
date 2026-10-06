<?php

declare(strict_types=1);

namespace App\Exception;

class FileNotFoundException extends FileServiceException
{
    public function __construct(string $fileName)
    {
        parent::__construct('File "' . $fileName . '" not found.');
    }
}
