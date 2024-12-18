<?php

namespace App\Scheduler;

use Symfony\Component\Scheduler\Attribute\AsCronTask;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

#[AsCronTask('1 * * * *')]
class AssetAssignRanks
{
    public function __invoke(): void
    {
        $process = new Process(['bin/console', 'market:assets-assign-ranks', '--no-debug']);
        try {
            $process->mustRun();
            echo $process->getOutput();
        } catch (ProcessFailedException $exception) {
            echo $exception->getMessage();
        }
    }
}
