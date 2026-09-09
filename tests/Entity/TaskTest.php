<?php

namespace App\Tests\Entity;

use App\Entity\Task;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    /**
     * Vérifie qu'une nouvelle tâche reçoit automatiquement
     * une date de création.
     */
    public function testNewTaskHasCreationDate(): void
    {
        $task = new Task();

        self::assertInstanceOf(\DateTimeInterface::class, $task->getCreatedAt());
    }

    /**
     * Vérifie qu'une nouvelle tâche est créée
     * avec l'état "non terminée".
     */
    public function testNewTaskIsNotDone(): void
    {
        $task = new Task();

        self::assertFalse($task->isDone());
    }

    /**
     * Vérifie que le titre d'une tâche peut être défini
     * puis récupéré correctement.
     */
    public function testSetTitleAndGetTitle(): void
    {
        $task = new Task();

        $task->setTitle('Ma tâche');

        self::assertSame('Ma tâche', $task->getTitle());
    }

    /**
     * Vérifie que le contenu d'une tâche peut être défini
     * puis récupéré correctement.
     */
    public function testSetContentAndGetContent(): void
    {
        $task = new Task();

        $task->setContent('Le contenu de ma tâche');

        self::assertSame(
            'Le contenu de ma tâche',
            $task->getContent()
        );
    }

    /**
     * Vérifie que la méthode toggle(true)
     * marque correctement la tâche comme terminée.
     */
    public function testToggleSetsTaskAsDone(): void
    {
        $task = new Task();

        $task->toggle(true);

        self::assertTrue($task->isDone());
    }

    /**
     * Vérifie que la méthode toggle(false)
     * permet de remettre une tâche à l'état non terminée.
     */
    public function testToggleSetsTaskAsNotDone(): void
    {
        $task = new Task();

        $task->toggle(true);
        $task->toggle(false);

        self::assertFalse($task->isDone());
    }

    /**
     * Vérifie que la date de création peut être modifiée
     * puis récupérée correctement.
     */
    public function testSetCreatedAtAndGetCreatedAt(): void
    {
        $task = new Task();
        $date = new \DateTime('2026-08-10 10:00:00');

        $task->setCreatedAt($date);

        self::assertSame($date, $task->getCreatedAt());
    }

    /**
     * Vérifie qu'une tâche peut être associée à un utilisateur.
     *
     * Le test vérifie également que setUser() retourne l'objet Task,
     * ce qui permet d'utiliser un setter fluent.
     */
    public function testSetUserAndGetUser(): void
    {
        $task = new Task();
        $user = new User();
        $user->setUsername('user1');

        $result = $task->setUser($user);

        self::assertSame($task, $result);
        self::assertSame($user, $task->getUser());
    }

    /**
     * Vérifie qu'une nouvelle entité Task n'est pas automatiquement
     * associée à un utilisateur lors de son instanciation.
     *
     * Le rattachement à l'utilisateur authentifié est effectué
     * au niveau du processus de création de la tâche,
     * et non dans le constructeur de l'entité.
     */
    public function testNewTaskHasNoUser(): void
    {
        $task = new Task();

        self::assertNull($task->getUser());
    }
}
