<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="Todo API",
 *     version="1.0.0",
 *     description="A comprehensive Todo application API with authentication, task management, and CSV import functionality.",
 *     @OA\Contact(
 *         email="support@todo-api.com",
 *         name="API Support"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 *
 * @OA\Server(
 *     url="/api",
 *     description="Todo API Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Enter token in format (Bearer <token>)"
 * )
 */
abstract class Controller
{
    //
}
