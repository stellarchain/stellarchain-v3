<?php

namespace App\Command;

use Soneso\StellarSDK\Soroban\Address;
use Soneso\StellarSDK\Soroban\Responses\LedgerEntry;
use Soneso\StellarSDK\Xdr\XdrBuffer;
use Soneso\StellarSDK\Xdr\XdrContractDataDurability;
use Soneso\StellarSDK\Xdr\XdrLedgerKey;
use Soneso\StellarSDK\Xdr\XdrLedgerKeyContractCode;
use Soneso\StellarSDK\Xdr\XdrLedgerEntryType;
use Soneso\StellarSDK\Xdr\XdrLedgerKeyContractData;
use Soneso\StellarSDK\Xdr\XdrSCSpecEntry;
use Soneso\StellarSDK\Xdr\XdrSCEnvMetaEntry;
use Soneso\StellarSDK\Xdr\XdrSCVal;
use Soneso\StellarSDK\Soroban\SorobanServer;
use Soneso\StellarSDK\Xdr\XdrSCValType;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'soroban-ct:get-contract-code',
    description: 'Add a short description for your command',
)]
class SorobanCtGetContractCodeCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('contractId', InputArgument::OPTIONAL, 'Contract Id');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $contractId = $input->getArgument('contractId');

        if ($contractId) {
            $io->note(sprintf('You passed an argument: %s', $contractId));
        }

        $server = new SorobanServer('http://192.168.3.200:8001');
        $ledgerKey = new XdrLedgerKey(XdrLedgerEntryType::CONTRACT_DATA());
        $ledgerKey->contractData = new XdrLedgerKeyContractData(
            Address::fromContractId($contractId)->toXdr(),
            XdrSCVal::forLedgerKeyContractInstance(),
            XdrContractDataDurability::PERSISTENT()
        );

        $ledgerEntries = $server->getLedgerEntries([$ledgerKey->toBase64Xdr()]);
        if ($ledgerEntries != null && $ledgerEntries->getEntries() != null && count($ledgerEntries->getEntries()) > 0) {
            $ledgerEntry = $ledgerEntries->getEntries()[0];
            $ledgerEntryData = $ledgerEntry->getLedgerEntryDataXdr();
            $lastModifiedLedger = $ledgerEntry->getLastModifiedLedgerSeq();

            if ($ledgerEntryData->getContractData() != null) {
                $contractData = $ledgerEntryData->getContractData();
                $contractInstance = $contractData->getVal()->getInstance();
                $contractExecutable = $contractInstance->getExecutable();
                $contractStorage = $contractInstance->getStorage();

                $contract = [
                    'contract_id' => $contractData->getContract()->getContractId(),
                    'contract_type' => $contractExecutable->getType()->getValue(),
                    'contract_data_xdr' => $ledgerEntry->getXdr(),
                    'last_modified_ledger' => $lastModifiedLedger,
                    'storage' => []
                ];

                if ($contractStorage != null) {
                    foreach ($contractStorage as $xdrScMapEntry) {
                        $contract['storage'][] = $this->parseValueTypes($xdrScMapEntry);
                    }
                }
                if ($contractExecutable->wasmIdHex != null) {
                    $wasmId = $contractExecutable->wasmIdHex;
                    $ledgerKey = new XdrLedgerKey(XdrLedgerEntryType::CONTRACT_CODE());
                    $ledgerKey->contractCode = new XdrLedgerKeyContractCode(hex2bin($wasmId));
                    $ledgerEntries = $server->getLedgerEntries([$ledgerKey->toBase64Xdr()]);
                    if ($ledgerEntries != null && $ledgerEntries->entries != null && count($ledgerEntries->entries) > 0) {
                        $ledgerEntry = $ledgerEntries->entries[0];
                        if ($ledgerEntry instanceof LedgerEntry) {
                            $codeLoaded  = $ledgerEntry->getLedgerEntryDataXdr()->contractCode;
                            $contract['c_hash'] = bin2hex($codeLoaded->getCHash());
                            $contract['wasm_id'] = $wasmId;
                            $contract['code_xdr'] = $ledgerEntry->getXdr();
                            $contract['env'] = $this->contractDataExtract($codeLoaded->getCode()->getValue());
                        }
                    }
                }

                $contractTransformed = [$contract, $codeLoaded->getCode()->getValue()];
            }
        }

        if (isset($contractTransformed)){
            dump($contractTransformed);
        }

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }

    function detectAndExtractTokenSymbol($input)
    {
        $pattern = '/initialize\s+\d+\s+([A-Za-z0-9]+)\s+([A-Za-z0-9]+)/';
        $matches = [];
        if (preg_match($pattern, $input, $matches)) {
            if (isset($matches[2])) {
                return $matches[2];
            }
            return $matches[2];
        } else {
            return 'Not initialized.';
        }
    }

    function parseValueTypes($xdrEntry)
    {
        $storage = [];
        foreach ($xdrEntry as $key => $xdrScMap) {
            $keyType = $xdrScMap->getType()->getValue();
            switch ($keyType) {
                case XdrSCValType::SCV_BOOL:
                    $storage[$key] = $xdrScMap->getB();
                    break;
                case XdrSCValType::SCV_SYMBOL:
                    $storage[$key] = $xdrScMap->getSym();
                    break;
                case XdrSCValType::SCV_ERROR:
                    $storage[$key] = $xdrScMap->getError();
                    break;
                case XdrSCValType::SCV_U32:
                    $storage[$key] = $xdrScMap->getU32();
                    break;
                case XdrSCValType::SCV_I32:
                    $storage[$key] = $xdrScMap->getI32();
                    break;
                case XdrSCValType::SCV_U64:
                    $storage[$key] = $xdrScMap->getU64();
                    break;
                case XdrSCValType::SCV_I64:
                    $storage[$key] = $xdrScMap->getI64();
                    break;
                case XdrSCValType::SCV_TIMEPOINT:
                    $storage[$key] = $xdrScMap->getTimepoint();
                    break;
                case XdrSCValType::SCV_DURATION:
                    $storage[$key] = $xdrScMap->getDuration();
                    break;
                case XdrSCValType::SCV_U128:
                    $storage[$key] = $xdrScMap->getU128();
                    break;
                case XdrSCValType::SCV_I128:
                    $storage[$key] = $xdrScMap->getI128();
                    break;
                case XdrSCValType::SCV_U256:
                    $storage[$key] = $xdrScMap->getU256();
                    break;
                case XdrSCValType::SCV_I256:
                    $storage[$key] = $xdrScMap->getI256();
                    break;
                case XdrSCValType::SCV_BYTES:
                    $storage[$key] = $xdrScMap->getBytes();
                    break;
                case XdrSCValType::SCV_STRING:
                    $storage[$key] = $xdrScMap->getStr();
                    break;
                case XdrSCValType::SCV_ADDRESS:
                    $storage[$key] = $xdrScMap->getAddress();
                    break;
                case XdrSCValType::SCV_LEDGER_KEY_NONCE:
                    $storage[$key] = $xdrScMap->getNonceKey();
                    break;
                case XdrSCValType::SCV_MAP:
                    $recursiveMaps = $xdrScMap->getMap();
                    foreach ($recursiveMaps as $recursive) {
                        $map = ['key' => $recursive->getKey(), 'symbol' => $recursive->getVal()];
                    }
                    break;
            }
        }

        return $storage;
    }

    function contractDataExtract($byteCode)
    {
        $contractData = [];
        $byteCodeSha256 = hash('sha256', $byteCode);
        $contractData['sha256'] = $byteCodeSha256;

        $entries = [];
        if (preg_match('/contractenvmetav0(.*?)contractmetav0/', $byteCode, $match) == 1) {
            $metaEntryBytes = $match[1];
            $metaEntry = XdrSCEnvMetaEntry::decode(new XdrBuffer($metaEntryBytes));
            $contractData['interface_version'] = $metaEntry->getInterfaceVersion();
        }

        if (preg_match('/contractmetav0(.*)/', $byteCode, $match) > 0) {
            $metaEntryBytes = $match[1];
            $metaEntry = XdrSCSpecEntry::decode(new XdrBuffer($metaEntryBytes));
            $entries[] = $this->appendSpecTypes($metaEntry);
        }

        $specBytes = null;
        if (preg_match('/contractspecv0(.*?)contractmetav0/', $byteCode, $match) == 1 && false) {
            $specBytes = $match[1];
        } else {
            $startPos = strpos($byteCode, 'contractspecv0');
            if ($startPos !== false) {
                $specBytes = substr($byteCode, $startPos + strlen('contractspecv0'));
                $metaEntry = XdrSCSpecEntry::decode(new XdrBuffer($specBytes));
                $entries[] = $this->appendSpecTypes($metaEntry);
                while (strlen($specBytes) > 0) {
                    $specBytes = substr($specBytes, strlen($metaEntry->encode()));
                    if (strlen($specBytes) > 0) {
                        try {
                            $metaEntry = XdrSCSpecEntry::decode(new XdrBuffer($specBytes));
                            $entry = $this->appendSpecTypes($metaEntry);
                            if ($entry) {
                                $entries[] = $entry;
                            }
                        } catch (\Exception $e) {
                        }
                    }
                }
                $contractData['specs'] = $entries;
            }
        }
        return $contractData;
    }

    function appendSpecTypes($metaEntry)
    {
        switch ($metaEntry->getType()->getValue()) {
            case 0:
                $entry = $metaEntry->getFunctionV0();
                return [
                    'type' => 'functionV0',
                    'name' => $entry->getName(),
                    'doc' => $entry->getDoc(),
                    'inputs' => json_encode($entry->getInputs()),
                    'outputs' => json_encode($entry->getOutputs())
                ];
                break;
            case 1:
                $entry = $metaEntry->getUdtStructV0();
                return [
                    'type' => 'udtStructV0',
                    'name' => $entry->getName(),
                    'doc' => $entry->getDoc(),
                    'lib' => $entry->getLib(),
                    'fields' => json_encode($entry->getFields()),
                ];
                break;
            case 2:
                $entry = $metaEntry->getUdtUnionV0();
                return [
                    'type' => 'udtUnionV0',
                    'name' => $entry->getName(),
                    'doc' => $entry->getDoc(),
                    'lib' => $entry->getLib(),
                    'cases' => json_encode($entry->getCases()),
                ];
                break;
                return "udtUnion";
            case 3:
                $entry = $metaEntry->getUdtEnumV0();
                return [
                    'type' => 'udtEnumV0',
                    'name' => $entry->getName(),
                    'doc' => $entry->getDoc(),
                    'lib' => $entry->getLib(),
                    'cases' => json_encode($entry->getCases()),
                ];
                break;
            case 4:
                $entry = $metaEntry->getUdtErrorEnumV0();
                return [
                    'type' => 'udtErrorEnumV0',
                    'name' => $entry->getName(),
                    'doc' => $entry->getDoc(),
                    'lib' => $entry->getLib(),
                    'cases' => json_encode($entry->getCases()),
                ];
                break;
        }
    }
}
