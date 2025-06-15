<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use OpenApi\Attributes as OA;

#[
    OA\Info(version: '1.0.0', description: 'authentification api', title: 'FCT AUTHENTICATION BLOC'),
    OA\Server(url: L5_SWAGGER_CONST_HOST, description: 'server'),
    OA\SecurityScheme(securityScheme: 'JWT', type: 'apiKey', name: 'Authorization', in: 'header', scheme: 'bearer'),
]
//    OA\SecurityScheme(securityScheme: 'bearerAuth', type: 'http', name: 'Authorization', in: 'header', scheme: 'bearer', bearerFormat: 'JWT'),

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
