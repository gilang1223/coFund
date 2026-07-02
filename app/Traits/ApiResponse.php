<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Send a success response.
     */
    protected function sendResponse(string $message, mixed $data = null, int $code = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if (!is_null($data)) {
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }

    /**
     * Send a paginated success response.
     */
    protected function sendPaginatedResponse(string $message, mixed $data, mixed $meta): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => $meta,
        ]);
    }

    /**
     * Send an error response.
     */
    protected function sendError(string $message, int $code = 400, mixed $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!is_null($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * Send a created response (HTTP 201).
     */
    protected function sendCreated(string $message, mixed $data = null): JsonResponse
    {
        return $this->sendResponse($message, $data, 201);
    }

    /**
     * Send a validation error response (HTTP 422).
     */
    protected function sendValidationError(string $message, mixed $errors = null): JsonResponse
    {
        return $this->sendError($message, 422, $errors);
    }

    /**
     * Send a not found response (HTTP 404).
     */
    protected function sendNotFound(string $message = 'Resource not found'): JsonResponse
    {
        return $this->sendError($message, 404);
    }

    /**
     * Send an unauthorized response (HTTP 401).
     */
    protected function sendUnauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->sendError($message, 401);
    }

    /**
     * Send a forbidden response (HTTP 403).
     */
    protected function sendForbidden(string $message = 'Forbidden'): JsonResponse
    {
        return $this->sendError($message, 403);
    }
}
