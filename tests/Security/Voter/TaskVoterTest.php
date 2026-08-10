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
     */
    public function testOwnerCanEditTask(): void
    {
        $owner = new User();
        $owner->setUsername('user1');

        $task = new Task();
        $task->setUser($owner);

        $token = $this->createToken($owner);
        $voter = new TaskVoter();

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($token, $task, [TaskVoter::EDIT])
        );
    }

    /**
     * Vérifie qu'un autre utilisateur ne peut pas modifier la tâche.
     */
    public function testOtherUserCannotEditTask(): void
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
            $voter->vote($token, $task, [TaskVoter::EDIT])
        );
    }

    /**
     * Vérifie que le propriétaire peut changer l'état de sa tâche.
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
     * Vérifie qu'un autre utilisateur ne peut pas changer l'état de la tâche.
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
     * Vérifie que le propriétaire peut supprimer sa tâche.
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
     * Vérifie qu'un autre utilisateur ne peut pas supprimer la tâche.
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
     * Vérifie qu'un utilisateur standard ne peut pas supprimer
     * une tâche appartenant à l'utilisateur anonymous.
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
     * une tâche appartenant à l'utilisateur anonymous.
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

        $token = $this->createToken($admin);
        $voter = new TaskVoter();

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($token, $task, [TaskVoter::DELETE])
        );
    }

    /**
     * Vérifie qu'un utilisateur non authentifié est refusé.
     */
    public function testUnauthenticatedUserIsDenied(): void
    {
        $owner = new User();
        $owner->setUsername('user1');

        $task = new Task();
        $task->setUser($owner);

        $token = $this->createToken(null);
        $voter = new TaskVoter();

        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($token, $task, [TaskVoter::DELETE])
        );
    }

    /**
     * Vérifie que le Voter ne prend pas en charge un sujet
     * qui n'est pas une instance de Task.
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
     * Crée un TokenInterface représentant l'utilisateur connecté.
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
