<?php

declare(strict_types=1);

namespace Tests;

use App\Controller\FileController;
use App\Service\FileService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class FileControllerTest extends TestCase
{
    public function testDeleteWithWrongMethod(): void
    {
        $fileService = $this->createMock(FileService::class);
        $fileService->expects($this->never())->method('delete');
        $controller = new FileController($fileService);

        $response = $controller->handle(Request::create('/index.php?action=delete&file=fruits.csv', 'GET'));

        $this->assertSame(405, $response->getStatusCode());
        $this->assertSame('Action "delete" requires DELETE, got GET.', json_decode($response->getContent(), true)['message']);
    }
}
