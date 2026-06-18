<?php
declare(strict_types=1);

namespace RyTM\Core;

class App
{
    public static function run()
    {
        $router = new Router();

        $router->add('GET', '/', [\RyTM\Controllers\AuthController::class, 'showHome']);
        $router->add('GET', '/login', [\RyTM\Controllers\AuthController::class, 'showLogin']);
        $router->add('POST', '/login', [\RyTM\Controllers\AuthController::class, 'processLogin']);
        $router->add('GET', '/logout', [\RyTM\Controllers\AuthController::class, 'logout']);

        $router->add('GET', '/register/step1', [\RyTM\Controllers\AuthController::class, 'showRegisterStep1']);
        $router->add('POST', '/register/step1', [\RyTM\Controllers\AuthController::class, 'processRegisterStep1']);
        $router->add('GET', '/register/step2', [\RyTM\Controllers\AuthController::class, 'showRegisterStep2']);
        $router->add('POST', '/register/step2', [\RyTM\Controllers\AuthController::class, 'processRegisterStep2']);
        $router->add('GET', '/register/step3', [\RyTM\Controllers\AuthController::class, 'showRegisterStep3']);
        $router->add('POST', '/register/resend', [\RyTM\Controllers\AuthController::class, 'resendCode']);
        $router->add('POST', '/register/avatar', [\RyTM\Controllers\AuthController::class, 'uploadAvatar']);

        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];
        $router->dispatch($method, $uri);
    }
}