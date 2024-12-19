<?php

namespace App\Config;

enum Metric: int
{
    case TotalTransactions = 0;
    case TotalAccounts = 1;
    case TotalAssets = 2;
    case OutputValue = 3;
    case TotalTrades = 4;
    case SuccessTransactions = 5;
    case FailedTransactions = 6;
    case OperationsPerSecond = 7;
    case XmlTotalPayments = 8;
    case DexVolume = 9;

    public function label(): string
    {
        return match($this) {
            self::TotalTrades => 'total-trades',
            self::TotalAccounts => 'total-accounts',
            self::TotalAssets => 'total-assets',
            self::OutputValue => 'output-value',
            self::TotalTransactions => 'number-of-transactions',
            self::SuccessTransactions => 'successful-transactions',
            self::FailedTransactions => 'failed-transactions',
            self::OperationsPerSecond => 'operations-per-second',
            self::XmlTotalPayments => 'xml-total-payments',
            self::DexVolume => 'dex-volume',
        };
    }

    public static function fromString(?string $label): ?self
    {
        return match($label) {
            'total-trades' => self::TotalTrades,
            'total-accounts' => self::TotalAccounts,
            'total-assets' => self::TotalAssets,
            'output-value' => self::OutputValue,
            'total-transactions' => self::TotalTransactions,
            'successful-transactions' => self::SuccessTransactions,
            'failed-transactions' => self::FailedTransactions,
            'operations-per-second' => self::OperationsPerSecond,
            'xml-total-payments' => self::XmlTotalPayments,
            'dex-volume' => self::DexVolume,
            default => null,
        };
    }
}
