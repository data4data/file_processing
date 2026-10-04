<?php

namespace App\Exception;

class InvalidRequestException extends FileServiceException
{
    public function getStatusCode(): int
    {
        return 400;
    }
}
