<?php

use App\Models\Currencies;

if (!function_exists('allCurrencies')) {
    function allCurrencies()
    {
        return Currencies::where('currency_status', "1")->get();
    }
}

if (!function_exists('activeCurrency')) {
    function activeCurrency() {
        $name = session('currency');
        if($name){
            return Currencies::where('currency_name', $name)->first();
        }
        return Currencies::where('currency_default', "1")->first();

    }
}

if (!function_exists('convertCurrency')) {
    function convertCurrency($amount, $fromCurrency, $toCurrency)
    {
        $base_currency = 'USD';

        $fromRate = getCurrencyRate($fromCurrency, $base_currency);
        $toRate = getCurrencyRate($toCurrency, $base_currency);

        $amountInBase = $amount / $fromRate;
        $convertedAmount = $amountInBase * $toRate;

        return round($convertedAmount, 2);
    }
}

if (!function_exists('getCurrencyRate')) {
    function getCurrencyRate($currency)
    {
        $rate = Currencies::where('currency_name', $currency)->first();
        return $rate->currency_rate;
    }
}

if (!function_exists('getFlagClass')) {
    function getFlagClass($countryCode)
    {
        $countryCode = strtoupper($countryCode);

        // Map currency codes to country codes
        $currencyToCountry = [
            'USD' => 'us',
            'EUR' => 'eu',
            'GBP' => 'gb',
            'PKR' => 'pk',
            'INR' => 'in',
            'CAD' => 'ca',
            'AUD' => 'au',
            'JPY' => 'jp',
            'CNY' => 'cn',
            'CHF' => 'ch',
            'SAR' => 'sa',
            'AED' => 'ae',
            'SGD' => 'sg',
            'NZD' => 'nz',
            'THB' => 'th',
            'MYR' => 'my',
            'KRW' => 'kr',
            'BRL' => 'br',
            'MXN' => 'mx',
            'ZAR' => 'za',
            'TRY' => 'tr',
            'RUB' => 'ru',
        ];

        // Map language codes to country codes for flags
        $languageToCountry = [
            'EN' => 'gb',
            'NL' => 'nl',
            'AR' => 'sa',
            'FR' => 'fr',
            'DE' => 'de',
            'ES' => 'es',
            'IT' => 'it',
            'PT' => 'pt',
            'RU' => 'ru',
            'ZH' => 'cn',
            'JA' => 'jp',
            'KO' => 'kr',
            'TR' => 'tr',
            'HI' => 'in',
            'UR' => 'pk',
            'BN' => 'bd',
            'ID' => 'id',
            'MS' => 'my',
            'TH' => 'th',
            'VI' => 'vn',
            'SV' => 'se',
            'NO' => 'no',
            'DA' => 'dk',
            'FI' => 'fi',
            'PL' => 'pl',
            'CS' => 'cz',
            'HU' => 'hu',
            'RO' => 'ro',
            'UK' => 'ua',
            'EL' => 'gr',
            'HE' => 'il',
            'FA' => 'ir',
        ];

        // Check if it's a currency code first
        if (isset($currencyToCountry[$countryCode])) {
            return 'fi fi-' . $currencyToCountry[$countryCode];
        }

        // Check if it's a language code
        if (isset($languageToCountry[$countryCode])) {
            return 'fi fi-' . $languageToCountry[$countryCode];
        }

        // Convert to lowercase for flag-icons
        $countryCode = strtolower($countryCode);

        // Handle special cases
        if ($countryCode === 'uae') {
            $countryCode = 'ae';
        }

        return 'fi fi-' . $countryCode;
    }
}
