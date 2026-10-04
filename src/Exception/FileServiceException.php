<?php

namespace App\Exception;

abstract class FileServiceException extends \RuntimeException
{
    abstract public function getStatusCode(): int;
}
