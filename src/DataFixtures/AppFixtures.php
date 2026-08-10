<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        $test = new User();
        $test->setUsername('test');
        $test->setEmail('test@todo.local');
        $test->setRole('ROLE_USER');
        $test->setPassword(
            $this->passwordHasher->hashPassword($test, 'test123')
        );

        $manager->persist($test);

        $admin = new User();
        $admin->setUsername('admin');
        $admin->setEmail('admin@todo.local');
        $admin->setRole('ROLE_ADMIN');
        $admin->setPassword(
            $this->passwordHasher->hashPassword($admin, 'admin123')
        );

        $manager->persist($admin);

        $anonymous = new User();
        $anonymous->setUsername('anonymous');
        $anonymous->setEmail('anonymous@todo.local');
        $anonymous->setRole('ROLE_USER');
        $anonymous->setPassword(
            $this->passwordHasher->hashPassword($anonymous, 'anonymous123')
        );

        $manager->persist($anonymous);

        $member = new User();
        $member->setUsername('member');
        $member->setEmail('member@todo.local');
        $member->setRole('ROLE_USER');
        $member->setPassword(
            $this->passwordHasher->hashPassword($member, 'member123')
        );

        $manager->persist($member);

        $manager->flush();
    }
}
