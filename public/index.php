<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Controller\FileController;
use App\FileActions\CsvFileActions;
use App\FileActions\JsonFileActions;
use App\Service\FileNameResolver;
use App\Service\FileService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$request = Request::createFromGlobals();

if (!$request->query->has('action')) {
    (new Response(file_get_contents(__DIR__ . '/home.html')))->send();
    exit;
}

$fileService = new FileService(__DIR__ . '/../storage', [
    new CsvFileActions(),
    new JsonFileActions(),
], new FileNameResolver());

$controller = new FileController($fileService);

try {
    $response = $controller->handle($request);
} catch (Throwable $e) {
    error_log((string) $e);
    $response = new JsonResponse(['status' => 'error', 'message' => 'Internal server error.'], 500);
}

$response->send();
