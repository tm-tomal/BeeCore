<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $code = session('locale', Auth::user()?->language ?? config('app.locale', 'en'));

        if (is_string($code) && preg_match('/^[a-z]{2}(-[A-Za-z]{2,4})?$/', $code)) {
            app()->setLocale($code);
            \Carbon\Carbon::setLocale($code);
        }

        return $next($request);
    }
}
