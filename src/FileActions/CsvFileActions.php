<?php

declare(strict_types=1);

namespace App\FileActions;

use App\Exception\InvalidFileContentException;
use SplFileObject;

class CsvFileActions extends AbstractFileActions
{
    public function getFormat(): string
    {
        return 'csv';
    }

    public function read(string $path): array
    {
        $this->checkFileExists($path);

        $file = new SplFileObject($path);
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::READ_AHEAD | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);
        $file->setCsvControl(',', '"', '\\');

        $headers = [];
        $rows = [];

        foreach ($file as $values) {
            if ($headers === []) {
                $headers = $values;

                if (count($headers) !== count(array_unique($headers))) {
                    throw new InvalidFileContentException('Column names must be unique.');
                }

                continue;
            }

            if (count($values) !== count($headers)) {
                throw new InvalidFileContentException(
                    'Row ' . (count($rows) + 1) . ' has ' . count($values) . ' columns, expected ' . count($headers) . '.'
                );
            }

            $rows[] = array_combine($headers, $values);
        }

        return $rows;
    }

    public function write(string $path, array $data): void
    {
        $headers = [];
        $lines = [];

        foreach (array_values($data) as $index => $row) {
            if (!is_array($row)) {
                throw new InvalidFileContentException(
                    'CSV data must be a list of rows, as [{"id": 1, "fruit": "apple", "quantity": 5}, {"id": 2, "fruit": "pear", "quantity": 3}].'
                );
            }

            if ($headers === []) {
                $headers = array_keys($row);
            }

            $lines[] = $this->getRowValues($row, $headers, $index + 1);
        }

        $file = fopen('php://temp', 'r+');

        if ($headers !== []) {
            fputcsv($file, $headers, ',', '"', '\\');
        }

        foreach ($lines as $values) {
            fputcsv($file, $values, ',', '"', '\\');
        }

        rewind($file);
        $content = stream_get_contents($file);
        fclose($file);

        $this->saveContent($path, $content);
    }

    private function getRowValues(array $row, array $headers, int $rowNumber): array
    {
        if (count($row) !== count($headers)) {
            throw new InvalidFileContentException('Row ' . $rowNumber . ' has different keys than row 1.');
        }

        $values = [];

        foreach ($headers as $header) {
            if (!array_key_exists($header, $row)) {
                throw new InvalidFileContentException('Row ' . $rowNumber . ' has different keys than row 1.');
            }

            if (!is_scalar($row[$header]) && $row[$header] !== null) {
                throw new InvalidFileContentException(
                    'Row ' . $rowNumber . ': value of "' . $header . '" must be text or a number.'
                );
            }

            $values[] = $row[$header];
        }

        return $values;
    }
}
