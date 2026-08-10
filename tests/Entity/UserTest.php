<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Entity\Task;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testGetUserIdentifierReturnsUsername(): void
    {
        $user = new User();
        $user->setUsername('user1');

        self::assertSame('user1', $user->getUserIdentifier());
    }

    public function testGetUsernameAndSetUsername(): void
    {
        $user = new User();

        $user->setUsername('user1');

        self::assertSame('user1', $user->getUsername());
    }

    public function testGetPasswordAndSetPassword(): void
    {
        $user = new User();

        $user->setPassword('password');

        self::assertSame('password', $user->getPassword());
    }

    public function testGetEmailAndSetEmail(): void
    {
        $user = new User();

        $user->setEmail('user@example.com');

        self::assertSame('user@example.com', $user->getEmail());
    }

    public function testGetRolesContainsRoleUserByDefault(): void
    {
        $user = new User();

        self::assertSame(
            ['ROLE_USER'],
            $user->getRoles()
        );
    }

    public function testSetRolesAllowsRoleUser(): void
    {
        $user = new User();

        $user->setRoles(['ROLE_USER']);

        self::assertSame(
            ['ROLE_USER'],
            $user->getRoles()
        );
    }

    public function testSetRolesAllowsRoleAdmin(): void
    {
        $user = new User();

        $user->setRoles(['ROLE_ADMIN']);

        self::assertSame(
            ['ROLE_ADMIN', 'ROLE_USER'],
            $user->getRoles()
        );
    }

    public function testGetRoleReturnsRoleUserByDefault(): void
    {
        $user = new User();

        self::assertSame(
            'ROLE_USER',
            $user->getRole()
        );
    }

    public function testGetRoleReturnsRoleAdminForAdministrator(): void
    {
        $user = new User();

        $user->setRoles(['ROLE_ADMIN']);

        self::assertSame(
            'ROLE_ADMIN',
            $user->getRole()
        );
    }

    public function testSetRoleAllowsRoleUser(): void
    {
        $user = new User();

        $result = $user->setRole('ROLE_USER');

        self::assertSame($user, $result);
        self::assertSame(['ROLE_USER'], $user->getRoles());
        self::assertSame('ROLE_USER', $user->getRole());
    }

    public function testSetRoleAllowsRoleAdmin(): void
    {
        $user = new User();

        $result = $user->setRole('ROLE_ADMIN');

        self::assertSame($user, $result);
        self::assertSame(['ROLE_ADMIN', 'ROLE_USER'], $user->getRoles());
        self::assertSame('ROLE_ADMIN', $user->getRole());
    }

    public function testSetRoleRejectsInvalidRole(): void
    {
        $user = new User();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Rôle utilisateur invalide.');

        $user->setRole('ROLE_INVALID');
    }

    public function testEraseCredentialsDoesNotThrowException(): void
    {
        $user = new User();

        $user->eraseCredentials();

        self::assertTrue(true);
    }

    public function testGetSaltReturnsNull(): void
    {
        $user = new User();

        self::assertNull($user->getSalt());
    }

    public function testTasksCollectionIsInitialized(): void
    {
        $user = new User();

        self::assertCount(0, $user->getTasks());
    }

    public function testAddTaskAddsTaskToUserAndSetsOwner(): void
    {
        $user = new User();

        $task = new Task();

        $result = $user->addTask($task);

        self::assertSame($user, $result);
        self::assertTrue($user->getTasks()->contains($task));
        self::assertSame($user, $task->getUser());
    }

    public function testAddTaskDoesNotAddSameTaskTwice(): void
    {
        $user = new User();

        $task = new Task();

        $user->addTask($task);
        $user->addTask($task);

        self::assertCount(1, $user->getTasks());
    }

    public function testRemoveTaskRemovesTaskAndClearsOwner(): void
    {
        $user = new User();

        $task = new Task();

        $user->addTask($task);
        $user->removeTask($task);

        self::assertFalse($user->getTasks()->contains($task));
        self::assertNull($task->getUser());
    }
}
