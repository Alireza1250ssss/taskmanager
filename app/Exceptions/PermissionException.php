<?php

namespace App\Exceptions;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

class PermissionException extends Exception
{
    protected array $data;

    public function __construct($message = "", $code = 0, Throwable $previous = null,$data = [])
    {
        parent::__construct($message, $code, $previous);
        $this->data = $data;
    }

    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function render($request): JsonResponse
    {
        $response = (new Controller())->getForbidden($this->getMessage(),$this->getData());
        return response()->json($response,$response['statusCode']);
    }
}
