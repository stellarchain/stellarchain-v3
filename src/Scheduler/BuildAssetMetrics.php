<?php

namespace App\Scheduler;

use Symfony\Component\Scheduler\Attribute\AsCronTask;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

#[AsCronTask('0 * * * *')]
class BuildAssetMetrics
{
    public function __invoke(): void
    {
        $process = new Process(['bin/console', 'market:build-statistics', '--no-debug'], timeout: 900);
        try {
            $process->mustRun();
            echo $process->getOutput();
        } catch (ProcessFailedException $exception) {
            echo $exception->getMessage();
        }
    }
}
