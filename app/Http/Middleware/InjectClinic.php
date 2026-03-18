<?php

namespace App\Http\Middleware;

use App\Models\Clinic;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class InjectClinic
{
    /**
     * ルートパラメータ{clinic}からClinicを解決し、全ビューに自動注入する
     */
    public function handle(Request $request, Closure $next)
    {
        $clinic = $request->route('clinic');

        if ($clinic) {
            // ルートモデルバインディングでClinicインスタンスが来る場合
            if ($clinic instanceof Clinic) {
                $clinic->load(['sites', 'design']);
            } else {
                // IDが来た場合
                $clinic = Clinic::with(['sites', 'design'])->findOrFail($clinic);
                $request->route()->setParameter('clinic', $clinic);
            }

            View::share('clinic', $clinic);

            // サイトパラメータもあれば共有
            $site = $request->route('site');
            if ($site && !($site instanceof \App\Models\Site)) {
                $site = \App\Models\Site::find($site);
                if ($site) {
                    $request->route()->setParameter('site', $site);
                }
            }
            if ($site) {
                View::share('site', $site);
            }
        }

        return $next($request);
    }
}
