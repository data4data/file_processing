<?php

namespace App\Contract;

interface FileActionsInterface
{
    public function getFormat(): string;

    public function supports(string $extension): bool;

    public function read(string $path): array;

    public function write(string $path, array $data): void;

    public function delete(string $path): void;
}
