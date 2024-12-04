<?php

namespace App\Scheduler;

use Symfony\Component\Scheduler\Attribute\AsCronTask;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

#[AsCronTask('@daily')]
class BuildStatisticsDay
{
    public function __invoke(): void
    {
        $process = new Process(['bin/console', 'statistics:build', '1d', '--no-debug'], timeout: 120);
        try {
            $process->mustRun();
            echo $process->getOutput();
        } catch (ProcessFailedException $exception) {
            echo $exception->getMessage();
        }
    }
}
