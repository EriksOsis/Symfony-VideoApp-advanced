<?php

namespace App\Entity;

use App\Repository\SubscriptionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SubscriptionRepository::class)]
#[ORM\Table(name: 'subscriptions')]
class Subscription
{
    public const ProPlan = 'https://ww.sandbox.paypal.com/cgi-bin/webscr?cmd=_sxclick&hosted_button_id=L4TAVL5GJLNK6';

    public const EnterprisePlan = 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_sxclick&hosted_button_id=BJGEYNG63G3F6';

    private static $planDataNames = ['free', 'pro', 'enterprise'];

    private static $planDataPrices = [
        'free' => 0,
        'pro' => 15,
        'enterprise' => 29
    ];

    public static function getPlanDataNameByIndex(int $index): string
    {
        return self::$planDataNames[$index];
    }

    public static function getPlanDataPriceByName(string $name): string
    {
        return self::$planDataPrices[$name];
    }

    public static function getPlanDataNames(): array
    {
        return self::$planDataNames;
    }

    public static function getPlanDataPrices(): array
    {
        return self::$planDataPrices;
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $plan = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $valid_to = null;

    #[ORM\Column(length: 45, nullable: true)]
    private ?string $payment_status = null;

    #[ORM\Column]
    private ?bool $free_plan_used = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlan(): ?string
    {
        return $this->plan;
    }

    public function setPlan(string $plan): self
    {
        $this->plan = $plan;

        return $this;
    }

    public function getValidTo(): ?\DateTimeInterface
    {
        return $this->valid_to;
    }

    public function setValidTo(\DateTimeInterface $valid_to): self
    {
        $this->valid_to = $valid_to;

        return $this;
    }

    public function getPaymentStatus(): ?string
    {
        return $this->payment_status;
    }

    public function setPaymentStatus(?string $payment_status): self
    {
        $this->payment_status = $payment_status;

        return $this;
    }

    public function isFreePlanUsed(): ?bool
    {
        return $this->free_plan_used;
    }

    public function setFreePlanUsed(bool $free_plan_used): self
    {
        $this->free_plan_used = $free_plan_used;

        return $this;
    }
}
