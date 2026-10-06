<?php

declare(strict_types=1);

namespace Tests;

use App\Contract\FileActionsInterface;
use App\Exception\UnsupportedFormatException;
use App\Service\FileNameResolver;
use App\Service\FileService;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class FileServiceTest extends TestCase
{
    private FileActionsInterface&MockObject $csv;
    private FileService $service;

    protected function setUp(): void
    {
        $this->csv = $this->createMock(FileActionsInterface::class);
        $this->csv->method('getFormat')->willReturn('csv');

        $this->service = new FileService('/storage', [$this->csv], new FileNameResolver());
    }

    public function testRead(): void
    {
        $this->csv->expects($this->once())
            ->method('read')
            ->with('/storage/fruits.csv')
            ->willReturn([['fruit' => 'apple'], ['fruit' => 'pear']]);

        $data = $this->service->read('fruits.csv');

        $this->assertEquals([['fruit' => 'apple'], ['fruit' => 'pear']], $data);
    }

    public function testUnsupportedFormat(): void
    {
        $this->expectException(UnsupportedFormatException::class);
        $this->expectExceptionMessageIs('Format "txt" is not supported. Allowed: csv.');

        $this->service->read('fruits.txt');
    }

    public function testDelete(): void
    {
        $folder = sys_get_temp_dir();
        $path = $folder . '/fruits_delete_test.csv';
        file_put_contents($path, "id,fruit\n1,apple\n");
        $service = new FileService($folder, [$this->csv], new FileNameResolver());

        $service->delete('fruits_delete_test.csv');

        $this->assertFileDoesNotExist($path);
    }
}
