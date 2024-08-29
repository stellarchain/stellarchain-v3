<?php

namespace App\Scheduler;

use Symfony\Component\Scheduler\Attribute\AsCronTask;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

#[AsCronTask('*/60 * * * *')]
class BuildAssetMetrics
{
    public function __invoke(): void
    {
        $process = new Process(['bin/console', 'market:build-asset-metrics', '--no-debug']);
        try {
            $process->mustRun();

            echo $process->getOutput();
        } catch (ProcessFailedException $exception) {
            echo $exception->getMessage();
        }
    }
}
