<?php

namespace Tests;

use App\Exception\InvalidFileContentException;
use App\FileActions\JsonFileActions;
use PHPUnit\Framework\TestCase;

class JsonFileActionsTest extends TestCase
{
    public function testWriteReadAndDelete(): void
    {
        $json = new JsonFileActions();
        $path = sys_get_temp_dir() . '/vegetables_test.json';
        $data = [
            ['id' => 1, 'vegetable' => 'carrot', 'quantity' => 10],
            ['id' => 2, 'vegetable' => 'potato', 'quantity' => 7],
        ];

        $json->write($path, $data);
        $this->assertFileExists($path);
        $this->assertJsonStringEqualsJsonString(json_encode($data), file_get_contents($path));

        $this->assertSame($data, $json->read($path));

        $json->delete($path);
        $this->assertFileDoesNotExist($path);
    }

    public function testReadInvalidJson(): void
    {
        $json = new JsonFileActions();
        $path = sys_get_temp_dir() . '/broken_test.json';
        file_put_contents($path, '{broken');

        $this->expectException(InvalidFileContentException::class);
        $this->expectExceptionMessage('File "broken_test.json" contains invalid JSON.');

        $json->read($path);
    }
}
