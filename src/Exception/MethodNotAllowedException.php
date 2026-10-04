<?php

namespace App\Exception;

class MethodNotAllowedException extends FileServiceException
{
    public function __construct(string $action, string $expectedMethod, string $usedMethod)
    {
        parent::__construct('Action "' . $action . '" requires ' . $expectedMethod . ', got ' . $usedMethod . '.');
    }

    public function getStatusCode(): int
    {
        return 405;
    }
}
