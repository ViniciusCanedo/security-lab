<?php

use App\Exceptions\InsufficientPermissionException;
use App\Exceptions\InvalidCredentialsException;
use App\Exceptions\InvalidResetTokenException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (InvalidCredentialsException $e, Request $request) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => [
                    'credentials' => [$e->getMessage()],
                ],
            ], 401);
        });

        $exceptions->render(function (InvalidResetTokenException $e, Request $request) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => [
                    'token' => [$e->getMessage()],
                ],
            ], 422);
        });

        $exceptions->render(function (InsufficientPermissionException $e, Request $request) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 403);
        });

        $exceptions->render(function (\App\Exceptions\SubscriberAlreadyExistsException $e, Request $request) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => ['email' => [$e->getMessage()]],
            ], 422);
        });

        $exceptions->render(function (\App\Exceptions\InvalidConfirmationTokenException $e, Request $request) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => ['token' => [$e->getMessage()]],
            ], 422);
        });

        $exceptions->render(function (\App\Exceptions\InvalidUnsubscribeTokenException $e, Request $request) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => ['token' => [$e->getMessage()]],
            ], 422);
        });

        $exceptions->render(function (\App\Exceptions\CampaignNotFoundException $e, Request $request) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 404);
        });

        $exceptions->render(function (\App\Exceptions\CampaignAlreadyDispatchedException $e, Request $request) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        });

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
