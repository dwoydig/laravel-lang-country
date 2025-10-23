<?php

namespace Dwoydig\LaravelLangCountry\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

class LangCountrySwitchController extends Controller
{
    public function switch(string $language_code): RedirectResponse
    {
        if (! in_array($language_code, config('lang-country.allowed'))) {
            return redirect()->back();
        }

        \LangCountry::setAllSessions($language_code);

        if (\Auth::user() && array_key_exists('language_code', \Auth::user()->getAttributes())) {
            \Auth::user()->language_code = $language_code;
            \Auth::user()->save();
        }

        return redirect()->back();
    }
}
