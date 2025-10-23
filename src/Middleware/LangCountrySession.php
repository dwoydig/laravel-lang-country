<?php

namespace Dwoydig\LaravelLangCountry\Middleware;

use App;
use Closure;
use Illuminate\Http\Request;

class LangCountrySession
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (! session()->has('language_code') || ! session()->has('locale')) {

            if (\Auth::user() && \Auth::user()?->language_code !== null) {
                $preferred_lang = \Auth::user()->language_code;
            } else {
                $preferred_lang = $request->server('HTTP_ACCEPT_LANGUAGE');
            }

            \LangCountry::setAllSessions($preferred_lang);

            if (\Auth::user() && array_key_exists('language_code', \Auth::user()->getAttributes()) && \Auth::user()->language_code === null) {
                \Auth::user()->language_code = session('language_code');
                \Auth::user()->save();
            }
        }

        App::setLocale(session('locale'));


        return $next($request);
    }
}
