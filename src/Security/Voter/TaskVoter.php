<?php

namespace App\Security\Voter;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TaskVoter extends Voter
{
    public const EDIT = 'TASK_EDIT';
    public const DELETE = 'TASK_DELETE';
    public const TOGGLE = 'TASK_TOGGLE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [
            self::EDIT,
            self::DELETE,
            self::TOGGLE,
        ], true) && $subject instanceof Task;
    }

    protected function voteOnAttribute(
        string $attribute,
        mixed $subject,
        TokenInterface $token
    ): bool {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var Task $task */
        $task = $subject;

        switch ($attribute) {
            case self::EDIT:
            case self::TOGGLE:
                return $task->getUser() === $user;

            case self::DELETE:
                if ($task->getUser()->getUsername() === 'anonymous') {
                    return in_array('ROLE_ADMIN', $user->getRoles(), true);
                }

                return $task->getUser() === $user;
        }

        return false;
    }
}
