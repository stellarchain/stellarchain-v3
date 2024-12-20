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
    case PriceUsd = 14;
    case Rank = 15;
    case MarketCap = 16;
    case Volume24h = 17;
    case CirculatingSupply = 18;
    case MarketCapDominance = 19;
    case Accounts = 20;
    case TopAccounts = 21;
    case ActiveAddresses = 22;
    case InactiveAddresses = 23;
    case Assets = 24;
    case Invocations = 25;
    case Contracts = 26;
    case FeeCharged = 27;
    case MaxFee = 28;

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
            self::PriceUsd => 'price-usd',
            self::Rank => 'rank',
            self::MarketCap => 'market-cap',
            self::Volume24h => 'volume-24h',
            self::CirculatingSupply => 'circulating-supply',
            self::MarketCapDominance => 'market-cap-dominance',
            self::Accounts => 'accounts',
            self::TopAccounts => 'top-accounts',
            self::ActiveAddresses => 'active-addresses',
            self::InactiveAddresses => 'inactive-addresses',
            self::Assets => 'assets',
            self::Invocations => 'invocations',
            self::Contracts => 'contracts',
            self::FeeCharged => 'fee-charged',
            self::MaxFee => 'max-fee',
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
            'price-usd' => self::PriceUsd,
            'rank' => self::Rank,
            'market-cap' => self::MarketCap,
            'volume-24h' => self::Volume24h,
            'circulating-supply' => self::CirculatingSupply,
            'market-cap-dominance' => self::MarketCapDominance,
            'accounts' => self::Accounts,
            'top-accounts' => self::TopAccounts,
            'active-addresses' => self::ActiveAddresses,
            'inactive-addresses' => self::InactiveAddresses,
            'assets' => self::Assets,
            'invocations' => self::Invocations,
            'contracts' => self::Contracts,
            'fee-charged' => self::FeeCharged,
            'max-fee' => self::MaxFee,
            default => null,
        };
    }
}
