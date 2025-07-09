<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response as IlluminateResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * レスポンスヘッダの設定
 *
 * bootstrap/app.php でグローバルミドルウェアとして登録する
 */
class ResponseHeader
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var IlluminateResponse $response */
        $response = $next($request);

        // セキュリティヘッダ
        // https://www.templarbit.com/blog/jp/2018/07/24/top-http-security-headers-and-how-to-deploy-them/
        // WEBサーバー側で設定する場合は、以下の設定は不要
        $response->header('X-Frame-Options', 'SAMEORIGIN ');
        $response->header('X-XSS-Protection', '1; mode=block');
        $response->header('X-Content-Type-Options', 'nosniff');

        // キャッシュコントロール
        // https://turningp.jp/network_and_security/http_header-cache
        // プロトコルがHTTP/1.0の場合は下記のno_cacheの設定により、Pragma: no-cacheヘッダとExpires: -1ヘッダが追加される
        $response->setCache([
            'must_revalidate' => true,
            'no_cache'        => true,
            'no_store'        => true,
            'private'         => true,
        ]);

        return $response;
    }
}
