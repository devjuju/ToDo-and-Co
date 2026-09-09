<?php

namespace App\DataFixtures;

use App\Entity\Task;
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
        // Utilisateur standard utilisé pour les tests fonctionnels.
        $test = new User();
        $test->setUsername('test');
        $test->setEmail('test@todo.local');
        $test->setRole('ROLE_USER');
        $test->setPassword(
            $this->passwordHasher->hashPassword($test, 'test123')
        );

        $manager->persist($test);

        // Administrateur utilisé pour tester les droits ROLE_ADMIN.
        $admin = new User();
        $admin->setUsername('admin');
        $admin->setEmail('admin@todo.local');
        $admin->setRole('ROLE_ADMIN');
        $admin->setPassword(
            $this->passwordHasher->hashPassword($admin, 'admin123')
        );

        $manager->persist($admin);

        // Utilisateur technique auquel sont rattachées les anciennes tâches.
        $anonymous = new User();
        $anonymous->setUsername('anonymous');
        $anonymous->setEmail('anonymous@todo.local');
        $anonymous->setRole('ROLE_USER');
        $anonymous->setPassword(
            $this->passwordHasher->hashPassword($anonymous, 'anonymous123')
        );

        $manager->persist($anonymous);

        // Deuxième utilisateur standard utilisé pour tester les règles
        // de propriété des tâches.
        $member = new User();
        $member->setUsername('member');
        $member->setEmail('member@todo.local');
        $member->setRole('ROLE_USER');
        $member->setPassword(
            $this->passwordHasher->hashPassword($member, 'member123')
        );

        $manager->persist($member);

        // Tâche appartenant à l'utilisateur test.
        $testTask = new Task();
        $testTask->setTitle('Tâche de test');
        $testTask->setContent('Tâche appartenant à test.');
        $testTask->setUser($test);

        $manager->persist($testTask);

        // Tâche appartenant à un autre utilisateur.
        $memberTask = new Task();
        $memberTask->setTitle('Tâche de member');
        $memberTask->setContent('Tâche appartenant à member.');
        $memberTask->setUser($member);

        $manager->persist($memberTask);

        // Tâche historique rattachée à anonymous.
        $anonymousTask = new Task();
        $anonymousTask->setTitle('Ancienne tâche');
        $anonymousTask->setContent('Tâche historique rattachée à anonymous.');
        $anonymousTask->setUser($anonymous);

        $manager->persist($anonymousTask);

        // Tâche appartenant à l'administrateur.
        $adminTask = new Task();
        $adminTask->setTitle('Tâche administrateur');
        $adminTask->setContent('Tâche appartenant à admin.');
        $adminTask->setUser($admin);

        $manager->persist($adminTask);

        $manager->flush();
    }
}
