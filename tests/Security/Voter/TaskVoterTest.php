<?php

namespace App\Tests\Security\Voter;

use App\Entity\Task;
use App\Entity\User;
use App\Security\Voter\TaskVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class TaskVoterTest extends TestCase
{
    /**
     * Vérifie que le propriétaire peut modifier sa tâche.
     *
     * Le Voter compare l'utilisateur présent dans le token
     * avec l'utilisateur propriétaire de la tâche.
     */
    public function testOwnerCanEditTask(): void
    {
        $owner = new User();
        $owner->setUsername('user1');

        $task = new Task();
        $task->setUser($owner);

        // Le token représente l'utilisateur actuellement connecté.
        $token = $this->createToken($owner);
        $voter = new TaskVoter();

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($token, $task, [TaskVoter::EDIT])
        );
    }

    /**
     * Vérifie qu'un utilisateur différent du propriétaire
     * ne peut pas modifier la tâche.
     */
    public function testOtherUserCannotEditTask(): void
    {
        $owner = new User();
        $owner->setUsername('user1');

        $otherUser = new User();
        $otherUser->setUsername('user2');

        $task = new Task();
        $task->setUser($owner);

        // Le token contient un autre utilisateur que le propriétaire.
        $token = $this->createToken($otherUser);
        $voter = new TaskVoter();

        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($token, $task, [TaskVoter::EDIT])
        );
    }

    /**
     * Vérifie que le propriétaire peut changer l'état
     * de sa propre tâche.
     */
    public function testOwnerCanToggleTask(): void
    {
        $owner = new User();
        $owner->setUsername('user1');

        $task = new Task();
        $task->setUser($owner);

        $token = $this->createToken($owner);
        $voter = new TaskVoter();

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($token, $task, [TaskVoter::TOGGLE])
        );
    }

    /**
     * Vérifie qu'un autre utilisateur ne peut pas changer
     * l'état d'une tâche qui ne lui appartient pas.
     */
    public function testOtherUserCannotToggleTask(): void
    {
        $owner = new User();
        $owner->setUsername('user1');

        $otherUser = new User();
        $otherUser->setUsername('user2');

        $task = new Task();
        $task->setUser($owner);

        $token = $this->createToken($otherUser);
        $voter = new TaskVoter();

        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($token, $task, [TaskVoter::TOGGLE])
        );
    }

    /**
     * Vérifie que le propriétaire peut supprimer sa propre tâche.
     */
    public function testOwnerCanDeleteTask(): void
    {
        $owner = new User();
        $owner->setUsername('user1');

        $task = new Task();
        $task->setUser($owner);

        $token = $this->createToken($owner);
        $voter = new TaskVoter();

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($token, $task, [TaskVoter::DELETE])
        );
    }

    /**
     * Vérifie qu'un utilisateur ne peut pas supprimer
     * la tâche d'un autre utilisateur.
     */
    public function testOtherUserCannotDeleteTask(): void
    {
        $owner = new User();
        $owner->setUsername('user1');

        $otherUser = new User();
        $otherUser->setUsername('user2');

        $task = new Task();
        $task->setUser($owner);

        $token = $this->createToken($otherUser);
        $voter = new TaskVoter();

        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($token, $task, [TaskVoter::DELETE])
        );
    }

    /**
     * Vérifie la règle métier spécifique aux anciennes tâches.
     *
     * Les tâches historiques sont rattachées à l'utilisateur
     * "anonymous". Un utilisateur standard ne peut pas les supprimer.
     */
    public function testRegularUserCannotDeleteAnonymousTask(): void
    {
        $anonymous = new User();
        $anonymous->setUsername('anonymous');
        $anonymous->setRoles(['ROLE_USER']);

        $user = new User();
        $user->setUsername('user1');
        $user->setRoles(['ROLE_USER']);

        $task = new Task();
        $task->setUser($anonymous);

        $token = $this->createToken($user);
        $voter = new TaskVoter();

        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($token, $task, [TaskVoter::DELETE])
        );
    }

    /**
     * Vérifie qu'un administrateur peut supprimer
     * une tâche rattachée à l'utilisateur "anonymous".
     *
     * Cette règle permet à l'administrateur de gérer
     * les anciennes tâches migrées depuis le MVP.
     */
    public function testAdminCanDeleteAnonymousTask(): void
    {
        $anonymous = new User();
        $anonymous->setUsername('anonymous');
        $anonymous->setRoles(['ROLE_USER']);

        $admin = new User();
        $admin->setUsername('admin');
        $admin->setRoles(['ROLE_ADMIN']);

        $task = new Task();
        $task->setUser($anonymous);

        // Le token représente ici un administrateur.
        $token = $this->createToken($admin);
        $voter = new TaskVoter();

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($token, $task, [TaskVoter::DELETE])
        );
    }

    /**
     * Vérifie qu'un utilisateur non authentifié est toujours refusé.
     *
     * Le Voter ne doit jamais accorder une permission
     * lorsque le token ne contient pas d'utilisateur.
     */
    public function testUnauthenticatedUserIsDenied(): void
    {
        $owner = new User();
        $owner->setUsername('user1');

        $task = new Task();
        $task->setUser($owner);

        // null représente l'absence d'utilisateur authentifié.
        $token = $this->createToken(null);
        $voter = new TaskVoter();

        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($token, $task, [TaskVoter::DELETE])
        );
    }

    /**
     * Vérifie que le Voter ne prend pas en charge
     * les objets qui ne sont pas des Task.
     *
     * ACCESS_ABSTAIN signifie que ce Voter laisse un autre mécanisme
     * de sécurité décider de l'autorisation.
     */
    public function testUnsupportedSubjectIsAbstained(): void
    {
        $user = new User();
        $user->setUsername('user1');

        $token = $this->createToken($user);
        $voter = new TaskVoter();

        self::assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $voter->vote($token, new \stdClass(), [TaskVoter::DELETE])
        );
    }

    /**
     * Crée un faux TokenInterface représentant l'utilisateur connecté.
     *
     * Le test unitaire ne démarre pas tout le système d'authentification
     * Symfony. On simule uniquement le token dont le Voter a besoin
     * pour déterminer l'utilisateur courant.
     */
    private function createToken(?User $user): TokenInterface
    {
        $token = $this->createMock(TokenInterface::class);

        $token
            ->method('getUser')
            ->willReturn($user);

        return $token;
    }
}
