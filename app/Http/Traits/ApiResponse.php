<?php

namespace App\Http\Traits;

trait ApiResponse
{
    // Successful responses
    public static int $HTTP_SUCCESS = 200;
    public static int $HTTP_CREATED = 201;
    // Client error responses
    public static int $HTTP_BAD_REQUEST = 400;
    public static int $HTTP_UNAUTHORIZED = 401;
    public static int $HTTP_FORBIDDEN = 403;
    public static int $HTTP_NOT_FOUND = 404;
    // Server error responses
    public static int $HTTP_SERVER_ERROR = 500;

    public function getResponse(string $message, array $data = [], int $statusCode = 200): array
    {
        return [
            'status' => true,
            'message' => $message,
            'data' => $data,
            'statusCode' => $statusCode
        ];
    }

    public function getError(string $message, array $data = [], int $statusCode = 400): array
    {
        return [
            'status' => false,
            'message' => $message,
            'data' => $data,
            'statusCode' => $statusCode
        ];
    }

    public function getForbidden(string $message = null,array $data = [])
    {
        return [
            'status' => false,
            'message' => $message ?? __('apiResponse.forbidden'),
            'data' => $data,
            'statusCode' => self::$HTTP_FORBIDDEN
        ];
    }

    public function getValidationError($data = [])
    {
        return [
            'status' => false,
            'message' => __('apiResponse.not-validated'),
            'data' => [],
            'errors' => $data,
            'statusCode' => self::$HTTP_BAD_REQUEST
        ];
    }

    public function getNotFound(string $message = "not found")
    {
        return [
            'status' => false,
            'message' => $message,
            'statusCode' => self::$HTTP_NOT_FOUND
        ];
    }

}
