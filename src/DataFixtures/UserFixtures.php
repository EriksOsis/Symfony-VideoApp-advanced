<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        foreach ($this->getUserData() as [$name, $last_name, $email, $password, $api_key, $roles]) {
            $user = new User();
            $user->setName($name);
            $user->setLastName($last_name);
            $user->setEmail($email);
            $user->setPassword($this->passwordHasher->hashPassword($user, $password));
            $user->setVimeoApiKey($api_key);
            $user->setRoles($roles);
            $manager->persist($user);
        }

        $manager->flush();
    }

    private function getUserData(): array
    {
        return [
            ['John', 'Wayne', 'jw@symf6.loc', 'password', 'e8112bfcd5db7174fd6385f618716701', ['ROLE_ADMIN']],
            ['Ēriks', 'Osis', 'eo@symf6.loc', 'password', null, ['ROLE_ADMIN']],
            ['Daniela', 'Putāne', 'dp@symf6.loc', 'password', null, ['ROLE_USER']],
        ];
    }
}
