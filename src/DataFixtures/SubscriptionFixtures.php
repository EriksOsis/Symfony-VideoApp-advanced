<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Subscription;
use App\Entity\User;

class SubscriptionFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        foreach ($this->getSubscriptionData() as [$user_id, $plan, $valid_to, $payment_status, $free_plan_used]) {
            $subscription = new Subscription();
            $subscription->setPlan($plan);
            $subscription->setValidTo($valid_to);
            $subscription->setPaymentStatus($payment_status);
            $subscription->setFreePlanUsed($free_plan_used);

            $user = $manager->getRepository(User::class)->find($user_id);

            $user->setSubscription($subscription);
            $manager->persist($user);
        }

        $manager->flush();
    }

    private function getSubscriptionData(): array
    {
        return [
            [1, Subscription::getPlanDataNameByIndex(2), (new \DateTime())->modify('+100 years'), 'paid', false], //super admin
            [2, Subscription::getPlanDataNameByIndex(0), (new \DateTime())->modify('+1 month'), 'paid', true],
            [3, Subscription::getPlanDataNameByIndex(1), (new \DateTime())->modify('+1 minute'), 'paid', false],

        ];
    }
}
