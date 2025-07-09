<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Response;

/**
 * IP制限をかけるためのミドルウェア
 */
class IpRestriction
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // リクエストのIPアドレスを取得
        // X-Forwarded-For ヘッダーが存在する場合はそれを使用し、存在しない場合はリクエストのIPアドレスを使用
        $ipAddress = explode(',', (string) $request->header('X-Forwarded-For', $request->ip()))[0];

        // 許可されたIPアドレスのリスト
        // ホワイトリストは手動で設定するか、ホワイトリストの保管場所から取得する
        /** @param string[] $allowedIps */
        $allowedIps = [];

        // ローカル環境または許可されたIPアドレスが未設定、許可されたIPアドレスにIPアドレスが含まれる場合にアクセスを許可
        if (app()->isLocal() || $allowedIps === [] || IpUtils::checkIp($ipAddress, $allowedIps)) { // @phpstan-ignore-line $allowedIpsが仮設定なので問題なし
            return $next($request);
        }

        // 許可されていないIPアドレスからのアクセスは拒否
        abort(403, sprintf('Access denied from %s', $ipAddress)); // @phpstan-ignore-line $allowedIpsが仮設定なので問題なし
    }
}
