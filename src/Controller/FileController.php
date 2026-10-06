<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\FileNotFoundException;
use App\Exception\FileServiceException;
use App\Exception\InvalidFileContentException;
use App\Exception\InvalidRequestException;
use App\Exception\MethodNotAllowedException;
use App\Exception\StorageException;
use App\Exception\UnsupportedActionException;
use App\Service\FileService;
use App\Traits\JsonDecodeTrait;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class FileController
{
    use JsonDecodeTrait;

    private const ACTION_METHODS = [
        'list' => 'GET',
        'read' => 'GET',
        'write' => 'POST',
        'delete' => 'DELETE',
        'upload' => 'POST',
    ];

    private const MAX_UPLOAD_SIZE = 1024 * 1024;

    private FileService $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function handle(Request $request): JsonResponse
    {
        try {
            $action = strtolower($this->getRequired($request, 'action'));
            $this->checkAction($action, $request->getMethod());

            return match ($action) {
                'list' => $this->success('Files loaded.', $this->fileService->listFiles()),
                'read' => $this->read($request),
                'write' => $this->write($request),
                'delete' => $this->delete($request),
                'upload' => $this->upload($request),
            };
        } catch (FileServiceException $e) {
            return $this->error($e->getMessage(), $this->getStatusCode($e));
        }
    }

    private function read(Request $request): JsonResponse
    {
        $fileName = $this->getFileName($request);
        $data = $this->fileService->read($fileName);

        return $this->success('File "' . $fileName . '" has ' . count($data) . ' records.', $data);
    }

    private function write(Request $request): JsonResponse
    {
        $fileName = $this->getFileName($request);
        $isNewFile = $this->fileService->write($fileName, $this->getJsonBody($request));

        if ($isNewFile) {
            return $this->success('File "' . $fileName . '" created.', [], 201);
        }

        return $this->success('File "' . $fileName . '" saved.');
    }

    private function delete(Request $request): JsonResponse
    {
        $fileName = $this->getFileName($request);
        $this->fileService->delete($fileName);

        return $this->success('File "' . $fileName . '" deleted.');
    }

    private function upload(Request $request): JsonResponse
    {
        $file = $request->files->get('file');

        if (!$file instanceof UploadedFile) {
            throw new InvalidRequestException('No file uploaded.');
        }

        if (!$file->isValid()) {
            throw new InvalidRequestException('File upload failed.');
        }

        $fileName = basename($file->getClientOriginalName());

        if ($file->getSize() > self::MAX_UPLOAD_SIZE) {
            throw new InvalidRequestException('File "' . $fileName . '" is too big. Maximum size is 1 MB.');
        }

        $savedName = $this->fileService->upload($fileName, $file->getPathname());
        $message = 'File "' . $savedName . '" uploaded.';

        if ($savedName !== $fileName) {
            $message = 'File "' . $fileName . '" already exists, saved as "' . $savedName . '".';
        }

        return $this->success($message, ['file' => $savedName], 201);
    }

    private function checkAction(string $action, string $method): void
    {
        if (!isset(self::ACTION_METHODS[$action])) {
            throw new UnsupportedActionException($action, array_keys(self::ACTION_METHODS));
        }

        if (self::ACTION_METHODS[$action] !== $method) {
            throw new MethodNotAllowedException($action, self::ACTION_METHODS[$action], $method);
        }
    }

    private function getRequired(Request $request, string $key): string
    {
        $value = trim($request->query->getString($key));

        if ($value === '') {
            throw new InvalidRequestException('Parameter "' . $key . '" is required.');
        }

        return $value;
    }

    private function getFileName(Request $request): string
    {
        return basename($this->getRequired($request, 'file'));
    }

    private function getJsonBody(Request $request): array
    {
        $body = $request->getContent();

        if (trim($body) === '') {
            throw new InvalidRequestException('Request body is empty.');
        }

        $data = $this->decodeJson($body);

        if ($data === null) {
            throw new InvalidRequestException('Request body is not valid JSON.');
        }

        return $data;
    }

    private function getStatusCode(FileServiceException $e): int
    {
        return match (true) {
            $e instanceof FileNotFoundException => 404,
            $e instanceof MethodNotAllowedException => 405,
            $e instanceof InvalidFileContentException => 422,
            $e instanceof StorageException => 500,
            default => 400,
        };
    }

    private function success(string $message, array $data = [], int $statusCode = 200): JsonResponse
    {
        return new JsonResponse(['status' => 'success', 'message' => $message, 'data' => $data], $statusCode);
    }

    private function error(string $message, int $statusCode): JsonResponse
    {
        return new JsonResponse(['status' => 'error', 'message' => $message], $statusCode);
    }
}
