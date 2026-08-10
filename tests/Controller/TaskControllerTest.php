<?php

namespace Tests\Controller;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskControllerTest extends WebTestCase
{
    private function getEntityManager(): EntityManagerInterface
    {
        return static::getContainer()
            ->get(EntityManagerInterface::class);
    }


    public function testAnonymousUserIsRedirectedToLogin(): void
    {
        $client = static::createClient();

        $client->request('GET', '/tasks');

        $this->assertResponseRedirects('/login');
    }

    public function testUserCanAccessTaskList(): void
    {
        $client = static::createClient();

        $user = $this->getUserByUsername('test');

        $client->loginUser($user);

        $client->request('GET', '/tasks');

        $this->assertResponseIsSuccessful();
    }

    public function testUserCanEditOwnTask(): void
    {
        $client = static::createClient();

        $user = $this->getUserByUsername('test');
        $task = $this->createTask($user);

        $client->loginUser($user);

        $client->request('GET', '/tasks/' . $task->getId() . '/edit');

        $this->assertResponseIsSuccessful();

        $this->removeTask($task);
    }

    public function testUserCannotEditTaskOfAnotherUser(): void
    {
        $client = static::createClient();

        $user = $this->getUserByUsername('test');
        $owner = $this->getUserByUsername('admin');

        $task = $this->createTask($owner);

        $client->loginUser($user);

        $client->request('GET', '/tasks/' . $task->getId() . '/edit');

        $this->assertResponseStatusCodeSame(403);

        $this->removeTask($task);
    }

    public function testUserCanToggleOwnTask(): void
    {
        $client = static::createClient();

        $user = $this->getUserByUsername('test');
        $task = $this->createTask($user);

        $client->loginUser($user);

        $client->request(
            'GET',
            '/tasks/' . $task->getId() . '/toggle'
        );

        $this->assertResponseRedirects('/tasks');

        $this->getEntityManager()->refresh($task);

        $this->assertTrue($task->isDone());

        $this->removeTask($task);
    }

    public function testUserCannotToggleTaskOfAnotherUser(): void
    {
        $client = static::createClient();

        $user = $this->getUserByUsername('test');
        $owner = $this->getUserByUsername('admin');

        $task = $this->createTask($owner);

        $client->loginUser($user);

        $client->request(
            'GET',
            '/tasks/' . $task->getId() . '/toggle'
        );

        $this->assertResponseStatusCodeSame(403);

        $this->removeTask($task);
    }

    public function testUserCanDeleteOwnTask(): void
    {
        $client = static::createClient();

        $user = $this->getUserByUsername('test');
        $task = $this->createTask($user);
        $taskId = $task->getId();

        $client->loginUser($user);

        $client->request(
            'GET',
            '/tasks/' . $taskId . '/delete'
        );

        $this->assertResponseRedirects('/tasks');

        $this->getEntityManager()->clear();

        $deletedTask = $this->getEntityManager()
            ->getRepository(Task::class)
            ->find($taskId);

        $this->assertNull($deletedTask);
    }

    public function testUserCannotDeleteTaskOfAnotherUser(): void
    {
        $client = static::createClient();

        $user = $this->getUserByUsername('test');
        $owner = $this->getUserByUsername('admin');

        $task = $this->createTask($owner);
        $taskId = $task->getId();

        $client->loginUser($user);

        $client->request(
            'GET',
            '/tasks/' . $taskId . '/delete'
        );

        $this->assertResponseStatusCodeSame(403);

        $this->getEntityManager()->clear();

        $existingTask = $this->getEntityManager()
            ->getRepository(Task::class)
            ->find($taskId);

        $this->assertNotNull($existingTask);

        $this->removeTask($existingTask);
    }

    public function testUserCannotDeleteAnonymousTask(): void
    {
        $client = static::createClient();

        $user = $this->getUserByUsername('test');
        $anonymous = $this->getUserByUsername('anonymous');

        $task = $this->createTask($anonymous);
        $taskId = $task->getId();

        $client->loginUser($user);

        $client->request(
            'GET',
            '/tasks/' . $taskId . '/delete'
        );

        $this->assertResponseStatusCodeSame(403);

        $this->getEntityManager()->clear();

        $existingTask = $this->getEntityManager()
            ->getRepository(Task::class)
            ->find($taskId);

        $this->assertNotNull($existingTask);

        $this->removeTask($existingTask);
    }

    public function testAdminCanDeleteAnonymousTask(): void
    {
        $client = static::createClient();

        $admin = $this->getUserByUsername('admin');
        $anonymous = $this->getUserByUsername('anonymous');

        $task = $this->createTask($anonymous);
        $taskId = $task->getId();

        $client->loginUser($admin);

        $client->request(
            'GET',
            '/tasks/' . $taskId . '/delete'
        );

        $this->assertResponseRedirects('/tasks');

        $this->getEntityManager()->clear();

        $deletedTask = $this->getEntityManager()
            ->getRepository(Task::class)
            ->find($taskId);

        $this->assertNull($deletedTask);
    }

    private function getUserByUsername(string $username): User
    {
        $user = $this->getEntityManager()
            ->getRepository(User::class)
            ->findOneBy(['username' => $username]);

        $this->assertNotNull($user);

        return $user;
    }

    private function createTask(User $user): Task
    {
        $task = new Task();

        $task->setTitle('Test task ' . uniqid());
        $task->setContent('Contenu de test');
        $task->setUser($user);

        $this->getEntityManager()->persist($task);
        $this->getEntityManager()->flush();

        return $task;
    }

    private function removeTask(Task $task): void
    {
        $this->getEntityManager()->remove($task);
        $this->getEntityManager()->flush();
    }
}
