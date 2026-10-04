<?php

namespace App\Exception;

class UnsupportedActionException extends FileServiceException
{
    public function __construct(string $action, array $actions)
    {
        parent::__construct('Action "' . $action . '" is not supported. Allowed: ' . implode(', ', $actions) . '.');
    }

    public function getStatusCode(): int
    {
        return 400;
    }
}
