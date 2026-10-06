<?php

declare(strict_types=1);

namespace App\Contract;

interface FileActionsInterface
{
    public function getFormat(): string;

    public function read(string $path): array;

    public function write(string $path, array $data): void;
}
