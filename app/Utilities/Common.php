<?php

namespace App\Utilities;

use Illuminate\Http\JsonResponse;

final class Common
{
    public static function success($message, $data, $status = true, $warning = null): JsonResponse
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data,
            'warning' => $warning,
            'error_list' => null,
        ], 200);

    }

    public static function successCreate($message, $data, $status = true, $warning = null): JsonResponse
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data,
            'warning' => $warning,
            'error_list' => null,
        ], 201);

    }

    public static function successDelete($message, $data, $status = true, $warning = null): JsonResponse
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data,
            'warning' => $warning,
            'error_list' => null,
        ], 200);

    }

    public static function error($message, $errorList): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => null,
            'error_list' => $errorList,
            'warning' => null,
        ], 500);

    }

    public static function notFound(): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => 'Elément non trouvée',
            'data' => null,
            'error_list' => null,
            'warning' => null,
        ], 404);

    }

    public static function badRequest(): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => 'Mauvais paramètre(s) fourni(s)',
            'data' => null,
            'error_list' => null,
            'warning' => null,
        ], 400);

    }

    public static function expired(): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => 'Session expirée',
            'data' => null,
            'error_list' => null,
            'warning' => null,
        ], 419);

    }

    public static function notAuthorized(): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => 'Email ou mot de passe incorrect',
            'data' => null,
            'error_list' => null,
            'warning' => null,
        ], 401);

    }

    public static function failedValidation($message): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => null,
            'error_list' => null,
            'warning' => null,
        ], 422);

    }

    public static function currentUse(): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => 'Un code est déjà envoyé à ce destinaire par numéro et/ou email',
            'data' => null,
            'error_list' => null,
            'warning' => null,
        ], 409);

    }
}
