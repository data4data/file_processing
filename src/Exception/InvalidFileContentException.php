<?php

namespace App\Exception;

class InvalidFileContentException extends FileServiceException
{
    public function getStatusCode(): int
    {
        return 422;
    }
}
