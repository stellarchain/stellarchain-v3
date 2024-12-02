<?php

namespace App\Message;

class ProcessInterval
{
    /**
     * @param array<int,mixed> $ledgerIds
     */
    public function __construct(
        private \DateTime $start,
        private \DateTime $end,
    ) {
    }

    public function getStart(): \DateTime
    {
        return $this->start;
    }

    public function getEnd(): \DateTime
    {
        return $this->end;
    }
}
