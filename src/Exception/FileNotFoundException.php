<?php

namespace App\Exception;

class FileNotFoundException extends FileServiceException
{
    public function __construct(string $fileName)
    {
        parent::__construct('File "' . $fileName . '" not found.');
    }

    public function getStatusCode(): int
    {
        return 404;
    }
}
