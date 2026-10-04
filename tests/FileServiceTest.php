<?php

namespace Tests;

use App\Contract\FileActionsInterface;
use App\Exception\MethodNotAllowedException;
use App\Exception\UnsupportedFormatException;
use App\Http\Request;
use App\Service\FileNameResolver;
use App\Service\FileService;
use PHPUnit\Framework\TestCase;

class FileServiceTest extends TestCase
{
    private FileActionsInterface $csv;
    private FileService $service;

    protected function setUp(): void
    {
        $this->csv = $this->createMock(FileActionsInterface::class);
        $this->csv->method('getFormat')->willReturn('csv');
        $this->csv->method('supports')->willReturnCallback(fn ($extension) => $extension === 'csv');

        $this->service = new FileService('/storage', [$this->csv], new FileNameResolver());
    }

    public function testReadAction(): void
    {
        $this->csv->expects($this->once())
            ->method('read')
            ->with('/storage/fruits.csv')
            ->willReturn([['fruit' => 'apple'], ['fruit' => 'pear']]);

        $response = $this->service->handle(new Request('GET', ['action' => 'read', 'file' => 'fruits.csv']));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('File "fruits.csv" has 2 records.', $response->getData()['message']);
        $this->assertEquals([['fruit' => 'apple'], ['fruit' => 'pear']], $response->getData()['data']);
    }

    public function testUnsupportedFormat(): void
    {
        $this->expectException(UnsupportedFormatException::class);
        $this->expectExceptionMessage('Format "txt" is not supported. Allowed: csv.');

        $this->service->handle(new Request('GET', ['action' => 'read', 'file' => 'fruits.txt']));
    }

    public function testDeleteWithWrongMethod(): void
    {
        $this->csv->expects($this->never())->method('delete');

        $this->expectException(MethodNotAllowedException::class);
        $this->expectExceptionMessage('Action "delete" requires DELETE, got GET.');

        $this->service->handle(new Request('GET', ['action' => 'delete', 'file' => 'fruits.csv']));
    }
}
