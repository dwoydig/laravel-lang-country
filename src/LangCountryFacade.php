<?php

namespace Dwoydig\LaravelLangCountry;

use Illuminate\Support\Facades\Facade;

class LangCountryFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return LangCountry::class;
    }
}
