<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class JsonResponseException extends Exception
{
    protected $details;

    public function __construct(array $details, int $status = 400)
    {
        $this->details = $details;
        $this->code = $status;
        parent::__construct($details['message'] ?? 'Une erreur s\'est produite', $status);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->details['message'] ?? 'Erreur',
            'success' => $this->details['success'] ?? false,
            'data' => $this->details['data'] ?? null,
            'warning' => $this->details['warning'] ?? null,
        ], $this->code);
    }
}
