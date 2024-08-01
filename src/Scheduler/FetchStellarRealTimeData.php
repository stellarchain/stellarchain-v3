<?php

namespace App\Scheduler;

use Symfony\Component\Scheduler\Attribute\AsCronTask;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

#[AsCronTask('*/10 * * * *')]
class FetchStellarRealTimeData
{
    public function __invoke(): void
    {
        $process = new Process(['bin/console', 'market:fetch-stellar-real-time-data']);
        try {
            $process->mustRun();

            echo $process->getOutput();
        } catch (ProcessFailedException $exception) {
            echo $exception->getMessage();
        }
    }
}
