<?php

namespace Tests;

use App\Exception\FileNotFoundException;
use App\FileActions\CsvFileActions;
use PHPUnit\Framework\TestCase;

class CsvFileActionsTest extends TestCase
{
    public function testWriteReadAndDelete(): void
    {
        $csv = new CsvFileActions();
        $path = sys_get_temp_dir() . '/fruits_test.csv';
        $data = [
            ['id' => '1', 'fruit' => 'apple', 'quantity' => '5'],
            ['id' => '2', 'fruit' => 'pear', 'quantity' => '3'],
        ];

        $csv->write($path, $data);
        $this->assertFileExists($path);
        $this->assertSame("id,fruit,quantity\n1,apple,5\n2,pear,3\n", file_get_contents($path));

        $this->assertSame($data, $csv->read($path));

        $csv->delete($path);
        $this->assertFileDoesNotExist($path);
    }

    public function testReadMissingFile(): void
    {
        $csv = new CsvFileActions();

        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage('File "missing.csv" not found.');

        $csv->read(sys_get_temp_dir() . '/missing.csv');
    }
}
