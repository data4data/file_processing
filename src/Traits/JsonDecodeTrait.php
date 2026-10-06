<?php

declare(strict_types=1);

namespace App\Traits;

trait JsonDecodeTrait
{
    protected function decodeJson(string $json): ?array
    {
        $data = json_decode($json, true);

        return is_array($data) ? $data : null;
    }
}
