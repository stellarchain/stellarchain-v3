<?php

namespace App\Config;

enum Metric: int
{
    case Ledgers = 0;
    case Tps = 1;
    case Ops = 2;
    case TxLedger= 3;
    case TxSuccess = 4;
    case TxFailed = 5;
    case OpsLedger = 6;
    case Transactions = 7;
    case Operations = 8;
    case AvgLedgerSec = 9;
    case Trades = 10;
    case OutputValue  = 11;
    case XmlTotalPay = 12;
    case DexVol = 13;

    public function label(): string
    {
        return match($this) {
            self::Ledgers => 'ledgers',
            self::Tps => 'tps',
            self::Ops => 'ops',
            self::TxLedger => 'tx-ledger',
            self::TxSuccess => 'tx-success',
            self::TxFailed => 'tx-failed',
            self::OpsLedger => 'ops-ledger',
            self::Transactions => 'transactions',
            self::Operations => 'operations',
            self::AvgLedgerSec => 'avg-ledger-sec',
            self::Trades => 'trades',
            self::OutputValue => 'output-value',
            self::XmlTotalPay => 'xml-total-pay',
            self::DexVol => 'dex-vol',
        };
    }

    public static function fromString(?string $label): ?self
    {
        return match($label) {
            'ledgers' => self::Ledgers,
            'tps' => self::Tps,
            'ops' => self::Ops,
            'tx-ledger' => self::TxLedger,
            'tx-success' => self::TxSuccess,
            'tx-failed' => self::TxFailed,
            'ops-ledger' => self::OpsLedger,
            'transactions' => self::Transactions,
            'operations' => self::Operations,
            'avg-ledger-sec' => self::AvgLedgerSec,
            'trades' => self::Trades,
            'output-value' => self::OutputValue,
            'xml-total-pay' => self::XmlTotalPay,
            'dex-vol' => self::DexVol,
            default => null,
        };
    }
}
