<?php

namespace Tests\Controller;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskControllerTest extends WebTestCase
{
    /**
     * Récupère l'EntityManager depuis le conteneur Symfony.
     *
     * Il permet notamment de préparer des données de test
     * et de vérifier que les modifications ont bien été persistées
     * en base de données.
     */
    private function getEntityManager(): EntityManagerInterface
    {
        return static::getContainer()
            ->get(EntityManagerInterface::class);
    }

    /**
     * Vérifie qu'un utilisateur non authentifié ne peut pas accéder
     * à la liste des tâches et est redirigé vers la connexion.
     */
    public function testAnonymousUserIsRedirectedToLogin(): void
    {
        $client = static::createClient();

        // Aucun utilisateur n'est connecté.
        $client->request('GET', '/tasks');

        $this->assertResponseRedirects('/login');
    }

    /**
     * Vérifie qu'un utilisateur authentifié peut accéder
     * à la liste des tâches.
     */
    public function testUserCanAccessTaskList(): void
    {
        $client = static::createClient();

        $user = $this->getUserByUsername('test');

        // Simule la connexion de l'utilisateur de test.
        $client->loginUser($user);

        $client->request('GET', '/tasks');

        $this->assertResponseIsSuccessful();
    }

    /**
     * Vérifie qu'un utilisateur authentifié peut créer une tâche
     * et que celle-ci est automatiquement rattachée
     * à l'utilisateur connecté.
     */
    public function testUserCanCreateTask(): void
    {
        $client = static::createClient();

        $user = $this->getUserByUsername('test');

        // L'utilisateur doit être authentifié avant d'accéder
        // au formulaire de création.
        $client->loginUser($user);

        // Récupère le formulaire réel de l'application
        // afin de tester le parcours utilisateur complet.
        $crawler = $client->request('GET', '/tasks/create');

        $this->assertResponseIsSuccessful();

        // Remplit le formulaire avec les données de test.
        $form = $crawler->selectButton('Ajouter')->form([
            'task[title]' => 'Nouvelle tâche de test',
            'task[content]' => 'Contenu de la nouvelle tâche.',
        ]);

        $client->submit($form);

        // Après une création réussie, l'application redirige
        // l'utilisateur vers la liste des tâches.
        $this->assertResponseRedirects('/tasks');

        // Recherche la tâche créée directement en base de données.
        // Cela permet de vérifier la persistance réelle des données.
        $task = $this->getEntityManager()
            ->getRepository(Task::class)
            ->findOneBy([
                'title' => 'Nouvelle tâche de test',
            ]);

        $this->assertNotNull($task);

        // Vérifie la règle métier principale :
        // une tâche créée doit automatiquement être rattachée
        // à l'utilisateur actuellement authentifié.
        $this->assertNotNull($task->getUser());
        $this->assertSame($user->getId(), $task->getUser()->getId());

        // Supprime la donnée créée afin de ne pas influencer
        // les autres tests.
        $this->removeTask($task);
    }

    /**
     * Vérifie qu'un utilisateur peut accéder à la modification
     * de sa propre tâche.
     */
    public function testUserCanEditOwnTask(): void
    {
        $client = static::createClient();

        $user = $this->getUserByUsername('test');

        // Crée une tâche appartenant à l'utilisateur connecté.
        $task = $this->createTask($user);

        $client->loginUser($user);

        $client->request('GET', '/tasks/' . $task->getId() . '/edit');

        $this->assertResponseIsSuccessful();

        // Nettoie la donnée temporaire créée pour le test.
        $this->removeTask($task);
    }

    /**
     * Vérifie qu'un utilisateur ne peut pas modifier
     * une tâche appartenant à un autre utilisateur.
     *
     * Le TaskVoter doit refuser l'accès et retourner HTTP 403.
     */
    public function testUserCannotEditTaskOfAnotherUser(): void
    {
        $client = static::createClient();

        $user = $this->getUserByUsername('test');
        $owner = $this->getUserByUsername('admin');

        // La tâche appartient à l'administrateur,
        // et non à l'utilisateur connecté.
        $task = $this->createTask($owner);

        $client->loginUser($user);

        $client->request('GET', '/tasks/' . $task->getId() . '/edit');

        // Le Voter doit refuser l'action.
        $this->assertResponseStatusCodeSame(403);

        $this->removeTask($task);
    }

    /**
     * Vérifie que le propriétaire peut modifier l'état
     * de sa propre tâche.
     */
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

        // Recharge l'entité depuis la base pour vérifier
        // que la modification a réellement été persistée.
        $this->getEntityManager()->refresh($task);

        $this->assertTrue($task->isDone());

        $this->removeTask($task);
    }

    /**
     * Vérifie qu'un utilisateur ne peut pas modifier l'état
     * d'une tâche appartenant à quelqu'un d'autre.
     */
    public function testUserCannotToggleTaskOfAnotherUser(): void
    {
        $client = static::createClient();

        $user = $this->getUserByUsername('test');
        $owner = $this->getUserByUsername('admin');

        // La tâche appartient à un autre utilisateur.
        $task = $this->createTask($owner);

        $client->loginUser($user);

        $client->request(
            'GET',
            '/tasks/' . $task->getId() . '/toggle'
        );

        // Le TaskVoter refuse l'action.
        $this->assertResponseStatusCodeSame(403);

        $this->removeTask($task);
    }

    /**
     * Vérifie que le propriétaire peut supprimer sa propre tâche.
     */
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

        // Vide le contexte Doctrine afin de forcer
        // une nouvelle lecture depuis la base de données.
        $this->getEntityManager()->clear();

        $deletedTask = $this->getEntityManager()
            ->getRepository(Task::class)
            ->find($taskId);

        // L'absence de résultat confirme que la suppression
        // a bien été persistée en base.
        $this->assertNull($deletedTask);
    }

    /**
     * Vérifie qu'un utilisateur ne peut pas supprimer
     * une tâche appartenant à un autre utilisateur.
     *
     * La tâche doit rester présente en base après le refus.
     */
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

        // L'utilisateur n'est pas propriétaire :
        // le TaskVoter doit donc retourner HTTP 403.
        $this->assertResponseStatusCodeSame(403);

        // Vérifie que la tâche n'a pas été supprimée malgré le refus.
        $this->getEntityManager()->clear();

        $existingTask = $this->getEntityManager()
            ->getRepository(Task::class)
            ->find($taskId);

        $this->assertNotNull($existingTask);

        $this->removeTask($existingTask);
    }

    /**
     * Vérifie qu'un utilisateur standard ne peut pas supprimer
     * une tâche rattachée à l'utilisateur "anonymous".
     *
     * Les anciennes tâches du MVP sont associées à cet utilisateur
     * et leur suppression est réservée aux administrateurs.
     */
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

        // Un utilisateur standard ne possède pas les droits
        // nécessaires pour supprimer une tâche anonyme.
        $this->assertResponseStatusCodeSame(403);

        // Vérifie que la tâche est toujours présente en base.
        $this->getEntityManager()->clear();

        $existingTask = $this->getEntityManager()
            ->getRepository(Task::class)
            ->find($taskId);

        $this->assertNotNull($existingTask);

        $this->removeTask($existingTask);
    }

    /**
     * Vérifie qu'un administrateur peut supprimer
     * une tâche rattachée à l'utilisateur "anonymous".
     *
     * Cette règle permet notamment de gérer les tâches historiques
     * récupérées lors de la migration de l'ancien MVP.
     */
    public function testAdminCanDeleteAnonymousTask(): void
    {
        $client = static::createClient();

        $admin = $this->getUserByUsername('admin');
        $anonymous = $this->getUserByUsername('anonymous');

        $task = $this->createTask($anonymous);
        $taskId = $task->getId();

        // L'administrateur est autorisé à gérer les tâches anonymes.
        $client->loginUser($admin);

        $client->request(
            'GET',
            '/tasks/' . $taskId . '/delete'
        );

        $this->assertResponseRedirects('/tasks');

        // Vérifie directement en base que la tâche a été supprimée.
        $this->getEntityManager()->clear();

        $deletedTask = $this->getEntityManager()
            ->getRepository(Task::class)
            ->find($taskId);

        $this->assertNull($deletedTask);
    }

    /**
     * Recherche un utilisateur de test dans la base de données.
     *
     * Les utilisateurs utilisés ici proviennent des fixtures.
     */
    private function getUserByUsername(string $username): User
    {
        $user = $this->getEntityManager()
            ->getRepository(User::class)
            ->findOneBy(['username' => $username]);

        // Le test doit échouer si la fixture attendue n'existe pas.
        $this->assertNotNull($user);

        return $user;
    }

    /**
     * Crée et persiste une tâche temporaire appartenant
     * à l'utilisateur fourni.
     *
     * Cette méthode évite de répéter le même code dans
     * les différents tests nécessitant une tâche existante.
     */
    private function createTask(User $user): Task
    {
        $task = new Task();

        // uniqid() permet d'utiliser un titre différent
        // pour chaque tâche générée pendant les tests.
        $task->setTitle('Test task ' . uniqid());
        $task->setContent('Contenu de test');
        $task->setUser($user);

        $this->getEntityManager()->persist($task);
        $this->getEntityManager()->flush();

        return $task;
    }

    /**
     * Supprime une tâche temporaire créée par un test.
     *
     * Cela permet de conserver une base de test propre
     * et d'éviter que les données d'un test influencent les suivants.
     */
    private function removeTask(Task $task): void
    {
        $this->getEntityManager()->remove($task);
        $this->getEntityManager()->flush();
    }
}
