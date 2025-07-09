<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // グローバルミドルウェアを追加
        $middleware->append(\App\Http\Middleware\ResponseHeader::class);

        // IP制限ミドルウェアを追加
        $middleware->appendToGroup('web', \App\Http\Middleware\IpRestriction::class);
        $middleware->appendToGroup('api', \App\Http\Middleware\IpRestriction::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function (Response $response, Throwable $throwable, Request $request) {
            if ($response->getStatusCode() === 419) {
                // ログイン画面を表示させるためにログアウトを行う
                Auth::logout();

                // セッションの破棄
                if ($request->hasSession()) {
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                }

                // レスポンスのクラスの種類によって処理を切り分け
                return $response instanceof JsonResponse
                    // JsonResponse（application/json）の場合はログインに遷移させるためステータスコードを変更する
                    ? $response->setStatusCode(401)
                    // JsonResponseで無い場合はトップ画面にリダイレクトさせることでログイン画面を表示
                    : redirect('/');
            }

            return $response;
        });
    })->create();
