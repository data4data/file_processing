<?php

namespace App\Service;

use App\Contract\FileActionsInterface;
use App\Exception\InvalidFileContentException;
use App\Exception\InvalidRequestException;
use App\Exception\MethodNotAllowedException;
use App\Exception\UnsupportedActionException;
use App\Exception\UnsupportedFormatException;
use App\Http\Request;
use App\Http\Response;

class FileService
{
    private const ACTION_METHODS = [
        'list' => 'GET',
        'read' => 'GET',
        'write' => 'POST',
        'delete' => 'DELETE',
        'upload' => 'POST',
    ];

    private string $storagePath;
    private array $fileActionsList;
    private FileNameResolver $fileNameResolver;

    public function __construct(string $storagePath, array $fileActionsList, FileNameResolver $fileNameResolver)
    {
        $this->storagePath = rtrim($storagePath, '/');
        $this->fileActionsList = $fileActionsList;
        $this->fileNameResolver = $fileNameResolver;
    }

    public function handle(Request $request): Response
    {
        $action = strtolower($request->getRequired('action'));
        $this->checkAction($action, $request->getMethod());

        if ($action === 'list') {
            return Response::success('Files loaded.', $this->getFileNames());
        }

        if ($action === 'upload') {
            return $this->upload($request->getUploadedFile('file'));
        }

        $fileName = basename($request->getRequired('file'));
        $path = $this->storagePath . '/' . $fileName;
        $fileActions = $this->findFileActions($fileName);

        switch ($action) {
            case 'read':
                $data = $fileActions->read($path);

                return Response::success('File "' . $fileName . '" has ' . count($data) . ' records.', $data);
            case 'write':
                $fileActions->write($path, $request->getJsonBody());

                return Response::success('File "' . $fileName . '" saved.', [], 201);
            default:
                $fileActions->delete($path);

                return Response::success('File "' . $fileName . '" deleted.');
        }
    }

    private function upload(array $uploadedFile): Response
    {
        $fileName = basename($uploadedFile['name']);
        $fileActions = $this->findFileActions($fileName);

        try {
            $fileActions->read($uploadedFile['tmp_name']);
        } catch (InvalidFileContentException $e) {
            throw new InvalidFileContentException('File "' . $fileName . '" has invalid content.');
        }

        $savedName = $this->fileNameResolver->getFreeName($this->storagePath, $fileName);

        if (!move_uploaded_file($uploadedFile['tmp_name'], $this->storagePath . '/' . $savedName)) {
            throw new InvalidRequestException('File upload failed.');
        }

        $message = 'File "' . $savedName . '" uploaded.';

        if ($savedName !== $fileName) {
            $message = 'File "' . $fileName . '" already exists, saved as "' . $savedName . '".';
        }

        return Response::success($message, ['file' => $savedName], 201);
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

    private function getFileNames(): array
    {
        $fileNames = [];

        foreach (scandir($this->storagePath) as $fileName) {
            if (is_file($this->storagePath . '/' . $fileName) && $this->getFileActions($fileName) !== null) {
                $fileNames[] = $fileName;
            }
        }

        return $fileNames;
    }

    private function findFileActions(string $fileName): FileActionsInterface
    {
        $fileActions = $this->getFileActions($fileName);

        if ($fileActions === null) {
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);

            throw new UnsupportedFormatException($extension, $this->getSupportedFormats());
        }

        return $fileActions;
    }

    private function getFileActions(string $fileName): ?FileActionsInterface
    {
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);

        foreach ($this->fileActionsList as $fileActions) {
            if ($fileActions->supports($extension)) {
                return $fileActions;
            }
        }

        return null;
    }

    private function getSupportedFormats(): array
    {
        $formats = [];

        foreach ($this->fileActionsList as $fileActions) {
            $formats[] = $fileActions->getFormat();
        }

        return $formats;
    }
}
