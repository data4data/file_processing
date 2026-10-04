<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Exception\FileServiceException;
use App\Http\Request;
use App\Http\Response;
use App\FileActions\CsvFileActions;
use App\FileActions\JsonFileActions;
use App\Service\FileNameResolver;
use App\Service\FileService;

$request = Request::fromGlobals();

if ($request->get('action') === null) {
    readfile(__DIR__ . '/home.html');
    exit;
}

$service = new FileService(__DIR__ . '/../storage', [
    new CsvFileActions(),
    new JsonFileActions(),
], new FileNameResolver());

try {
    $response = $service->handle($request);
} catch (FileServiceException $e) {
    $response = Response::error($e->getMessage(), $e->getStatusCode());
} catch (Throwable $e) {
    error_log($e->getMessage());
    $response = Response::error('Internal server error.', 500);
}

$response->send();
