<?php
/**
 * Currency Helper Functions
 */

function formatCurrency($amount, $showSymbol = true) {
    $amount = floatval($amount);
    $formatted = number_format(
        $amount, 
        DECIMAL_PLACES, 
        DECIMAL_SEPARATOR, 
        THOUSANDS_SEPARATOR
    );
    
    if (!$showSymbol) {
        return $formatted;
    }
    
    if (CURRENCY_POSITION === 'before') {
        return CURRENCY_SYMBOL . ' ' . $formatted;
    } else {
        return $formatted . ' ' . CURRENCY_SYMBOL;
    }
}

function getCurrencySettings() {
    return [
        'currency' => DEFAULT_CURRENCY,
        'symbol' => CURRENCY_SYMBOL,
        'position' => CURRENCY_POSITION,
        'decimal_places' => DECIMAL_PLACES,
        'thousands_separator' => THOUSANDS_SEPARATOR,
        'decimal_separator' => DECIMAL_SEPARATOR
    ];
}

function getCurrencySymbol() {
    return CURRENCY_SYMBOL;
}

function getCurrencyCode() {
    return DEFAULT_CURRENCY;
}
