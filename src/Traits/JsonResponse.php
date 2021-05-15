<?php

declare(strict_types=1);

namespace Idunis\Context\Traits;

trait JsonResponse
{
    /**
     * @param string $message
     * @param int $httpStatusCode
     * @return Illuminate\Http\JsonResponse
     */
    public function responseError(string $message = '', int $httpStatusCode = 500)
    {
        return response()->json([
            'status' => $httpStatusCode,
            'error' => [
                'type' => 'Exception',
                'message' => $message
            ],
        ], 500);
    }

    /**
     * @param array|object $data
     * @param int $httpStatus
     * @return Illuminate\Http\JsonResponse
     */
    protected function responseSuccess($data, int $httpStatus = 200)
    {
        return response()->json([
            'status' => $httpStatus,
            'data' => $data ?? []
        ], $httpStatus);
    }

    /**
     * @param array $data
     * @return Illuminate\Http\JsonResponse
     */
    protected function responseValidationFailed($data)
    {
        return response()->json([
            'status' => 422,
            'message' => 'Validation Failed',
            'errors' => $data
        ], 422);
    }

    /**
     * @param string $msg
     * @param int $httpStatus
     * @return Illuminate\Http\JsonResponse
     */
    protected function responseMsg($msg, int $httpStatus = 200)
    {
        return response()->json([
            'status' => $httpStatus,
            'message' => $msg
        ], $httpStatus);
    }
}