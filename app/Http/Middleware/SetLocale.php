<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class SetLocale
{
  public function handle(Request $request, Closure $next)
  {
    if (Auth::check()) {
      $language = Auth::user()->language ?? 'en';
      App::setLocale(in_array($language, ['en', 'id']) ? $language : 'en');
    }

    return $next($request);
  }
}
