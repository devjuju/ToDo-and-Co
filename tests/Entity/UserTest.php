<?php

namespace App\Tests\Entity;

use App\Entity\Task;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    /**
     * Vérifie que l'identifiant utilisé par Symfony Security
     * correspond au nom d'utilisateur.
     */
    public function testGetUserIdentifierReturnsUsername(): void
    {
        $user = new User();
        $user->setUsername('user1');

        self::assertSame('user1', $user->getUserIdentifier());
    }

    /**
     * Vérifie que le nom d'utilisateur peut être défini
     * puis récupéré correctement.
     */
    public function testGetUsernameAndSetUsername(): void
    {
        $user = new User();

        $user->setUsername('user1');

        self::assertSame('user1', $user->getUsername());
    }

    /**
     * Vérifie que le mot de passe peut être défini
     * puis récupéré correctement.
     *
     * Le hachage du mot de passe est géré par le système de sécurité
     * lors de la création ou de la modification de l'utilisateur.
     */
    public function testGetPasswordAndSetPassword(): void
    {
        $user = new User();

        $user->setPassword('password');

        self::assertSame('password', $user->getPassword());
    }

    /**
     * Vérifie que l'adresse e-mail peut être définie
     * puis récupérée correctement.
     */
    public function testGetEmailAndSetEmail(): void
    {
        $user = new User();

        $user->setEmail('user@example.com');

        self::assertSame('user@example.com', $user->getEmail());
    }

    /**
     * Vérifie que tout nouvel utilisateur possède
     * au minimum le rôle ROLE_USER.
     *
     * Ce comportement garantit qu'un utilisateur ne se retrouve
     * pas sans rôle de sécurité.
     */
    public function testGetRolesContainsRoleUserByDefault(): void
    {
        $user = new User();

        self::assertSame(
            ['ROLE_USER'],
            $user->getRoles()
        );
    }

    /**
     * Vérifie qu'un utilisateur peut être défini avec ROLE_USER.
     */
    public function testSetRolesAllowsRoleUser(): void
    {
        $user = new User();

        $user->setRoles(['ROLE_USER']);

        self::assertSame(
            ['ROLE_USER'],
            $user->getRoles()
        );
    }

    /**
     * Vérifie qu'un utilisateur administrateur possède
     * ROLE_ADMIN ainsi que ROLE_USER.
     *
     * ROLE_ADMIN donne également accès aux permissions
     * réservées aux utilisateurs standards.
     */
    public function testSetRolesAllowsRoleAdmin(): void
    {
        $user = new User();

        $user->setRoles(['ROLE_ADMIN']);

        self::assertSame(
            ['ROLE_ADMIN', 'ROLE_USER'],
            $user->getRoles()
        );
    }

    /**
     * Vérifie que le rôle retourné par défaut est ROLE_USER.
     */
    public function testGetRoleReturnsRoleUserByDefault(): void
    {
        $user = new User();

        self::assertSame(
            'ROLE_USER',
            $user->getRole()
        );
    }

    /**
     * Vérifie qu'un utilisateur possédant ROLE_ADMIN
     * est identifié comme administrateur.
     */
    public function testGetRoleReturnsRoleAdminForAdministrator(): void
    {
        $user = new User();

        $user->setRoles(['ROLE_ADMIN']);

        self::assertSame(
            'ROLE_ADMIN',
            $user->getRole()
        );
    }

    /**
     * Vérifie qu'un rôle utilisateur valide peut être défini
     * avec la méthode métier setRole().
     *
     * La méthode retourne également l'utilisateur lui-même
     * afin de permettre une utilisation fluide des setters.
     */
    public function testSetRoleAllowsRoleUser(): void
    {
        $user = new User();

        $result = $user->setRole('ROLE_USER');

        self::assertSame($user, $result);
        self::assertSame(['ROLE_USER'], $user->getRoles());
        self::assertSame('ROLE_USER', $user->getRole());
    }

    /**
     * Vérifie qu'un rôle administrateur valide peut être défini.
     *
     * Un administrateur conserve également ROLE_USER,
     * conformément à la hiérarchie des permissions de l'application.
     */
    public function testSetRoleAllowsRoleAdmin(): void
    {
        $user = new User();

        $result = $user->setRole('ROLE_ADMIN');

        self::assertSame($user, $result);
        self::assertSame(['ROLE_ADMIN', 'ROLE_USER'], $user->getRoles());
        self::assertSame('ROLE_ADMIN', $user->getRole());
    }

    /**
     * Vérifie qu'un rôle qui ne fait pas partie des rôles autorisés
     * est rejeté par l'entité.
     *
     * Cela protège l'application contre l'enregistrement
     * d'un rôle arbitraire.
     */
    public function testSetRoleRejectsInvalidRole(): void
    {
        $user = new User();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Rôle utilisateur invalide.');

        $user->setRole('ROLE_INVALID');
    }

    /**
     * Vérifie que eraseCredentials() peut être appelé
     * sans provoquer d'exception.
     *
     * Cette méthode est imposée par UserInterface et peut rester vide
     * lorsque l'application ne conserve pas de données sensibles
     * temporaires dans l'objet User.
     */
    public function testEraseCredentialsDoesNotThrowException(): void
    {
        $user = new User();

        $user->eraseCredentials();

        self::assertTrue(true);
    }

    /**
     * Vérifie que getSalt() retourne null.
     *
     * Avec le système moderne de hashage des mots de passe de Symfony,
     * un salt séparé n'est pas nécessaire.
     */
    public function testGetSaltReturnsNull(): void
    {
        $user = new User();

        self::assertNull($user->getSalt());
    }

    /**
     * Vérifie que la collection de tâches d'un nouvel utilisateur
     * est correctement initialisée et vide.
     */
    public function testTasksCollectionIsInitialized(): void
    {
        $user = new User();

        self::assertCount(0, $user->getTasks());
    }

    /**
     * Vérifie l'association entre un utilisateur et une tâche.
     *
     * L'ajout doit être cohérent des deux côtés de la relation :
     * la tâche doit apparaître dans la collection de l'utilisateur
     * et son propriétaire doit être défini.
     */
    public function testAddTaskAddsTaskToUserAndSetsOwner(): void
    {
        $user = new User();

        $task = new Task();

        $result = $user->addTask($task);

        self::assertSame($user, $result);
        self::assertTrue($user->getTasks()->contains($task));
        self::assertSame($user, $task->getUser());
    }

    /**
     * Vérifie qu'une même tâche ne peut pas être ajoutée
     * plusieurs fois à la collection de l'utilisateur.
     *
     * Cela évite les doublons dans la relation User -> Task.
     */
    public function testAddTaskDoesNotAddSameTaskTwice(): void
    {
        $user = new User();

        $task = new Task();

        $user->addTask($task);
        $user->addTask($task);

        self::assertCount(1, $user->getTasks());
    }

    /**
     * Vérifie que la suppression d'une tâche de la collection
     * supprime également son propriétaire.
     *
     * La relation User <-> Task reste ainsi cohérente
     * après la suppression de l'association.
     */
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
