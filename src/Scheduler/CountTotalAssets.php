<?php

namespace App\Scheduler;

use Symfony\Component\Scheduler\Attribute\AsCronTask;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

#[AsCronTask('0 0 * * *')]
class CountTotalAssets
{
    public function __invoke(): void
    {
        $process = new Process(['bin/console', 'horizon:read-assets', '--no-debug'], timeout: null);
        try {
            $process->mustRun();
            echo $process->getOutput();
        } catch (ProcessFailedException $exception) {
            echo $exception->getMessage();
        }
    }
}
