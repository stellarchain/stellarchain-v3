<?php

namespace App\Entity\Horizon;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\Horizon\AccountsRepository;

#[ORM\Entity(repositoryClass: AccountsRepository::class)]
class Accounts
{
    #[ORM\Id]
    #[ORM\Column(type: 'string')]
    private string $account_id;

    #[ORM\Column(type: 'integer')]
    private int $balance;

    #[ORM\Column(type: 'integer')]
    private int $selling_liabilities;

    #[ORM\Column(type: 'integer')]
    private int $buying_liabilities;

    #[ORM\Column(type: 'string')]
    private int $home_domain;

    public function getAccountId(): int
    {
        return $this->account_id;
    }

    public function getBuyingLiabilities(): int
    {
        return $this->buying_liabilities;
    }

    public function getSellingLiabilities(): int
    {
        return $this->selling_liabilities;
    }

    public function getBalance(): int
    {
        return $this->balance;
    }

    public function getHomeDomain(): string
    {
        return $this->home_domain;
    }
}
