<?php

namespace Dwoydig\LaravelLangCountry;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Dwoydig\LaravelLangCountry\Services\PreferredLanguage;

class LangCountry
{
    protected ?string $language_code = null;

    protected array $data;

    protected bool $withTime = false;

    public function __construct()
    {
        if (config('lang-country.fallback_based_on_current_locale', false) && ! session()->has(config('lang-country.language_code_field', 'language_code'))) {
            $lang = new PreferredLanguage(app()->getLocale());
            $this->language_code = $lang->language_code;
        } else {
            $this->language_code = session('language_code', config('lang-country.fallback'));
        }
        $this->data = $this->getDataFromFile($this->language_code);
    }

    /**
     * @throws Exception
     */
    public function overrideSession(string $language_code): void
    {
        $lang = new PreferredLanguage($language_code);

        $this->language_code = $lang->language_code;
        $this->data = $this->getDataFromFile($this->language_code);
    }

    /**
     * Will return the current LangCountry value
     **/
    public function currentLangCountry(): string
    {
        return $this->language_code;
    }

    /**
     * It will return the right language. This can be a two char representation (ex. "nl", Dutch)
     * or a four char representation (ex. es_CO; Spanish-colombian).
     */
    public function lang(string|bool $override = false): string
    {
        if ($override) {
            $this->overrideSession($override);
        }

        return $this->data['lang'];
    }

    public function withTime(): self
    {
        $this->withTime = true;

        return $this;
    }

    protected function createFormatString(string $format): string
    {
        if ($this->withTime) {
            $format .= ' ' . $this->timeFormat();
        }

        $this->withTime = false; // Reset the time flag, so it doesn't affect the next call.

        return $format;
    }

    /**
     * It will return the two character code representation of the country.
     */
    public function country(): string
    {
        return $this->data['country'];
    }

    /**
     * It will return the name of the country represented by the country code.
     */
    public function countryName(): string
    {
        return $this->data['country_name'];
    }

    /**
     * It will return the two character code representation of the country.
     */
    public function countryNameLocal(): string
    {
        return $this->data['country_name_local'];
    }

    /**
     * It will return the name of the language TRANSLATED IN THE LANGUAGE IN QUESTION.
     * You can use this for nice country-selectors in your app.
     */
    public function name(): string
    {
        return $this->data['name'];
    }

    /**
     * String representation of the dateformat with only numbers.
     * Ex: "Y-m-d".
     */
    public function dateNumbersFormat(): string
    {
        return $this->createFormatString($this->data['date_numbers']);
    }

    /**
     * String representation of the date with only numbers from the Carbon instance provided.
     * Ex: "2018-04-24".
     */
    public function dateNumbers(Carbon $carbon, string|bool $override = false): string
    {
        if ($override) {
            $this->overrideSession($override);
        }

        return $carbon->locale($this->data['lang'])->translatedFormat($this->dateNumbersFormat());
    }

    /**
     * String representation of the dateformat with only capitals, some javascript date selectors use this.
     * Ex: "DD-MM-YYYY".
     */
    public function dateNumbersFullCapitalsFormat(): string
    {
        return $this->createFormatString($this->data['date_numbers_full_capitals']);
    }

    /**
     * String representation of the dateformat with words but without the day.
     * Ex: "F jS Y".
     */
    public function dateWordsWithoutDayFormat(): string
    {
        return $this->createFormatString($this->data['date_words_without_day']);
    }

    /**
     * String representation of the date in words but without the day.
     * Ex: "April 24th 2018".
     */
    public function dateWordsWithoutDay(Carbon $carbon, string|bool $override = false): string
    {
        if ($override) {
            $this->overrideSession($override);
        }

        return $carbon->locale($this->data['lang'])->translatedFormat($this->dateWordsWithoutDayFormat());
    }

    /**
     * String representation of the dateformat with words but without the day.
     * Ex: "l F jS Y".
     */
    public function dateWordsWithDayFormat(): string
    {
        return $this->createFormatString($this->data['date_words_with_day']);
    }

    /**
     * String representation of the date with words but without the day.
     * Ex: "Tuesday April 24th 2018".
     */
    public function dateWordsWithDay(Carbon $carbon, string|bool $override = false): string
    {
        if ($override) {
            $this->overrideSession($override);
        }

        return $carbon->locale($this->data['lang'])->translatedFormat($this->dateWordsWithDayFormat());
    }

    /**
     * String representation of the dateformat for a birthday.
     * Ex: "F jS".
     */
    public function dateBirthdayFormat(): string
    {
        return $this->data['date_birthday'];
    }

    /**
     * String representation of a birthday date.
     * Ex: "April 24th".
     */
    public function dateBirthday(Carbon $carbon, string|bool $override = false): string
    {
        if ($override) {
            $this->overrideSession($override);
        }

        return $carbon->locale($this->data['lang'])->translatedFormat($this->dateBirthdayFormat());
    }

    /**
     * String representation of the time-format.
     * Ex: "h:i a".
     */
    public function timeFormat(): string
    {
        return $this->data['time_format'];
    }

    /**
     * String representation of time.
     * Ex: "12:00 pm".
     */
    public function time(Carbon $carbon, string|bool $override = false): string
    {
        if ($override) {
            $this->overrideSession($override);
        }

        return $carbon->locale($this->data['lang'])->translatedFormat($this->timeFormat());
    }

    /**
     * Emoji representation of language country flag.
     * Ex: "🇱🇹".
     */
    public function emojiFlag(): string
    {
        return $this->data['emoji_flag'];
    }

    public function allLanguages(): Collection
    {
        return collect(config('lang-country.allowed'))
            ->map(function (string $item) {
                $file = $this->getDataFromFile($item);

                return [
                    'country' => $file['country'],
                    'country_name' => $file['country_name'],
                    'country_name_local' => $file['country_name_local'],
                    'lang' => $file['lang'],
                    'name' => $file['name'],
                    'language_code' => $item,
                    'emoji_flag' => $file['emoji_flag'],
                    'currency_code' => $file['currency_code'],
                    'currency_symbol' => $file['currency_symbol'],
                    'currency_symbol_local' => $file['currency_symbol_local'],
                    'currency_name' => $file['currency_name'],
                    'currency_name_local' => $file['currency_name_local'],
                ];
            });
    }

    /**
     * It will return the iso code of the country's currency.
     */
    public function currencyCode(): string
    {
        return $this->data['currency_code'];
    }

    /**
     * It will return the iso symbol of the country's currency.
     */
    public function currencySymbol(): string
    {
        return $this->data['currency_symbol'];
    }

    /**
     * It will return the iso symbol of the country's currency, prefixed with localization.
     */
    public function currencySymbolLocal(): string
    {
        return $this->data['currency_symbol_local'];
    }

    /**
     * It will return the name of the country's currency.
     */
    public function currencyName(): string
    {
        return $this->data['currency_name'];
    }

    /**
     * It will return the name of the country's currency as spoken locally by language code.
     */
    public function currencyNameLocal(): string
    {
        return $this->data['currency_name_local'];
    }

    /**
     * It will return a collection with the current language, country and name
     * and also the other available language, country and name.
     */
    public function langSelectorHelper(): array
    {
        return $this->allLanguages()->reduce(function (?array $carry, array $item) {
            if ($item['language_code'] != session('language_code')) {
                $carry['available'][] = $item;
            } else {
                $carry['current'] = $item;
            }

            return $carry;
        });
    }

    public function setAllSessions(?string $preferred_lang): void
    {
        $lang = new PreferredLanguage($preferred_lang);
        session(['language_code' => $lang->language_code]);
        session(['locale' => $lang->locale]);
    }

    /**
     * @throws Exception
     */
    private function getDataFromFile(?string $language_code): array
    {
        if ($language_code === null) {
            throw new Exception('The language_code session is not set');
        }

        $filename = $language_code . '.json';
        if (file_exists(lang_path('lang-country-overrides/' . $filename))) {
            $resource = lang_path('lang-country-overrides/' . $filename);
        } else {
            $resource = __DIR__ . '/LangCountryData/' . $filename;
        }

        return json_decode(file_get_contents($resource), true);
    }
}
