<?php

namespace App\FileActions;

use App\Exception\InvalidFileContentException;

class CsvFileActions extends AbstractFileActions
{
    public function getFormat(): string
    {
        return 'csv';
    }

    public function read(string $path): array
    {
        $this->checkFileExists($path);

        $content = trim(file_get_contents($path));

        if ($content === '') {
            return [];
        }

        $lines = explode("\n", $content);
        $headers = str_getcsv(trim($lines[0]), ',', '"', '\\');
        $rows = [];

        for ($i = 1; $i < count($lines); $i++) {
            $line = trim($lines[$i]);

            if ($line === '') {
                continue;
            }

            $values = str_getcsv($line, ',', '"', '\\');

            if (count($values) !== count($headers)) {
                throw new InvalidFileContentException(
                    'File "' . basename($path) . '": row ' . ($i + 1) . ' has ' . count($values) . ' columns, expected ' . count($headers) . '.'
                );
            }

            $rows[] = array_combine($headers, $values);
        }

        return $rows;
    }

    public function write(string $path, array $data): void
    {
        foreach ($data as $row) {
            if (!is_array($row)) {
                throw new InvalidFileContentException('CSV data must be a list of rows, as [{"id": 1, "fruit": "apple", "quantity": 5}, {"id": 2, "fruit": "pear", "quantity": 3}].');
            }
        }

        $file = fopen($path, 'w');

        if (!empty($data)) {
            fputcsv($file, array_keys(reset($data)), ',', '"', '\\');
        }

        foreach ($data as $row) {
            fputcsv($file, $row, ',', '"', '\\');
        }

        fclose($file);
    }
}
