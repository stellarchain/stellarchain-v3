<?php

namespace App\Command;

use App\Integrations\StellarHorizon\HorizonConnector;
use App\Integrations\StellarHorizon\ListHorizonAssets;
use App\Message\UpdateAsset;
use Soneso\StellarSDK\Responses\Asset\AssetResponse;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'horizon:read-assets',
    description: 'Import assets from Stellar Horizon Api',
)]
class HorizonReadAssetsCommand extends Command
{
    public function __construct(private MessageBusInterface $bus)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $cursor = '';
        $assetsResponse = $this->importAssets($cursor);
        $totalAssets = 0;

        while (true) {
            $assetsResponse = $this->importAssets($cursor);
            $assets = $assetsResponse['_embedded']['records'];
            $totalRecords = count($assets);
            $totalAssets += $totalRecords;

            $next = $assetsResponse['_links']['next']['href'];
            $query_string = parse_url($next, PHP_URL_QUERY);
            $query_params = [];
            parse_str($query_string, $query_params);
            $cursor = $query_params['cursor'];

            foreach($assets as $asset){
                $this->saveAsset($asset);
            }

            $io->note(sprintf('Saved: %s', $totalAssets));

            if ($totalRecords < 200){
                break;
            }
        }

        $io->success(sprintf('Successfully imported total of %s assets', $totalAssets));

        return Command::SUCCESS;
    }

    /**
     * @return array
     */
    public function importAssets(string $cursor): array
    {
        $connector = new HorizonConnector();
        $listAssetsRequest = new ListHorizonAssets($cursor);

        return $connector->send($listAssetsRequest)->json();
    }

    /**
     * @param array<int,mixed> $assetData
     */
    public function saveAsset(array $assetData): void
    {
        $assetResponse = AssetResponse::fromJson($assetData);
        if ($assetResponse instanceof AssetResponse) {
            $this->bus->dispatch(new UpdateAsset($assetResponse));
        }
    }
}