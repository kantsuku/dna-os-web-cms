<?php

namespace App\Http\Middleware;

use App\Models\Site;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSiteAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $site = $request->route('site');

        if ($site instanceof Site && !$request->user()->canAccessSite($site)) {
            abort(403, 'このサイトへのアクセス権限がありません');
        }

        return $next($request);
    }
}
