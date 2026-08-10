<?php

namespace App\Tests\Entity;

use App\Entity\Task;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    public function testNewTaskHasCreationDate(): void
    {
        $task = new Task();

        self::assertInstanceOf(\DateTimeInterface::class, $task->getCreatedAt());
    }

    public function testNewTaskIsNotDone(): void
    {
        $task = new Task();

        self::assertFalse($task->isDone());
    }

    public function testSetTitleAndGetTitle(): void
    {
        $task = new Task();

        $task->setTitle('Ma tâche');

        self::assertSame('Ma tâche', $task->getTitle());
    }

    public function testSetContentAndGetContent(): void
    {
        $task = new Task();

        $task->setContent('Le contenu de ma tâche');

        self::assertSame(
            'Le contenu de ma tâche',
            $task->getContent()
        );
    }

    public function testToggleSetsTaskAsDone(): void
    {
        $task = new Task();

        $task->toggle(true);

        self::assertTrue($task->isDone());
    }

    public function testToggleSetsTaskAsNotDone(): void
    {
        $task = new Task();

        $task->toggle(true);
        $task->toggle(false);

        self::assertFalse($task->isDone());
    }

    public function testSetCreatedAtAndGetCreatedAt(): void
    {
        $task = new Task();
        $date = new \DateTime('2026-08-10 10:00:00');

        $task->setCreatedAt($date);

        self::assertSame($date, $task->getCreatedAt());
    }

    public function testSetUserAndGetUser(): void
    {
        $task = new Task();
        $user = new User();
        $user->setUsername('user1');

        $result = $task->setUser($user);

        self::assertSame($task, $result);
        self::assertSame($user, $task->getUser());
    }

    public function testNewTaskHasNoUser(): void
    {
        $task = new Task();

        self::assertNull($task->getUser());
    }
}
