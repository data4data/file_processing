<?php

declare(strict_types=1);

namespace App\Exception;

class UnsupportedFormatException extends FileServiceException
{
    public function __construct(string $extension, array $supportedFormats)
    {
        $formats = implode(', ', $supportedFormats);

        if ($extension === '') {
            parent::__construct('File name has no extension. Allowed: ' . $formats . '.');
        } else {
            parent::__construct('Format "' . $extension . '" is not supported. Allowed: ' . $formats . '.');
        }
    }
}
