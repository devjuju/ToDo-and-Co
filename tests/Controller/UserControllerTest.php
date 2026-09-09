<?php

namespace Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    /**
     * Récupère l'EntityManager depuis le conteneur Symfony.
     *
     * Il permet aux tests fonctionnels de vérifier directement
     * les données enregistrées en base de données.
     */
    private function getEntityManager(): EntityManagerInterface
    {
        return static::getContainer()
            ->get(EntityManagerInterface::class);
    }

    /**
     * Recherche un utilisateur de la fixture par son nom d'utilisateur.
     *
     * Le test échoue immédiatement si l'utilisateur attendu
     * n'existe pas dans la base de test.
     */
    private function getUserByUsername(string $username): User
    {
        $user = $this->getEntityManager()
            ->getRepository(User::class)
            ->findOneBy(['username' => $username]);

        $this->assertNotNull($user);

        return $user;
    }

    /**
     * Vérifie qu'un utilisateur non authentifié ne peut pas accéder
     * à la gestion des utilisateurs et est redirigé vers la connexion.
     */
    public function testAnonymousUserIsRedirectedToLogin(): void
    {
        $client = static::createClient();

        $client->request('GET', '/users');

        $this->assertResponseRedirects('/login');
    }

    /**
     * Vérifie qu'un utilisateur authentifié avec ROLE_USER
     * ne peut pas accéder à la gestion des utilisateurs.
     */
    public function testUserCannotAccessUserManagement(): void
    {
        $client = static::createClient();

        // Connexion via le formulaire afin de tester le parcours
        // réel de connexion d'un utilisateur standard.
        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Se connecter')->form();

        $form['_username'] = 'test';
        $form['_password'] = 'test123';

        $client->submit($form);

        // ROLE_USER n'autorise pas l'accès aux routes /users.
        $client->request('GET', '/users');

        $this->assertResponseStatusCodeSame(403);
    }

    /**
     * Vérifie qu'un administrateur peut accéder à la gestion
     * des utilisateurs.
     */
    public function testAdminCanAccessUserManagement(): void
    {
        $client = static::createClient();

        $admin = $this->getUserByUsername('admin');

        // loginUser() simule la connexion de l'utilisateur
        // sans avoir à reproduire le formulaire de connexion.
        $client->loginUser($admin);

        $client->request('GET', '/users');

        $this->assertResponseIsSuccessful();
    }

    /**
     * Vérifie qu'un administrateur peut afficher le formulaire
     * de création d'un utilisateur et que le champ de rôle est présent.
     */
    public function testAdminCanDisplayUserCreationForm(): void
    {
        $client = static::createClient();

        $admin = $this->getUserByUsername('admin');

        $client->loginUser($admin);

        $crawler = $client->request('GET', '/users/create');

        $this->assertResponseIsSuccessful();

        // Vérifie la présence des champs nécessaires au formulaire,
        // notamment le choix du rôle utilisateur/administrateur.
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input[name="user[username]"]');
        $this->assertSelectorExists('input[name="user[email]"]');
        $this->assertSelectorExists('input[name="user[password][first]"]');
        $this->assertSelectorExists('input[name="user[password][second]"]');
        $this->assertSelectorExists('select[name="user[role]"]');
    }

    /**
     * Vérifie qu'un administrateur peut créer un utilisateur
     * avec le rôle ROLE_USER.
     */
    public function testAdminCanCreateUser(): void
    {
        $client = static::createClient();

        $admin = $this->getUserByUsername('admin');

        $client->loginUser($admin);

        $crawler = $client->request('GET', '/users/create');

        $form = $crawler->selectButton('Ajouter')->form();

        // Remplit le formulaire comme le ferait un administrateur.
        $form['user[username]'] = 'new_user';
        $form['user[email]'] = 'new_user@todo.local';
        $form['user[password][first]'] = 'password123';
        $form['user[password][second]'] = 'password123';
        $form['user[role]'] = 'ROLE_USER';

        $client->submit($form);

        $this->assertResponseRedirects('/users');

        // Vérifie que l'utilisateur a réellement été enregistré en base.
        $user = $this->getEntityManager()
            ->getRepository(User::class)
            ->findOneBy(['username' => 'new_user']);

        $this->assertNotNull($user);
        $this->assertSame('new_user@todo.local', $user->getEmail());
        $this->assertSame('ROLE_USER', $user->getRole());

        // Nettoie la donnée créée pour éviter qu'elle influence
        // les autres tests.
        $this->getEntityManager()->remove($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Vérifie qu'un administrateur peut afficher le formulaire
     * de modification d'un utilisateur, avec la possibilité de modifier son rôle.
     */
    public function testAdminCanDisplayUserEditForm(): void
    {
        $client = static::createClient();

        $admin = $this->getUserByUsername('admin');
        $user = $this->getUserByUsername('member');

        $client->loginUser($admin);

        $crawler = $client->request(
            'GET',
            '/users/' . $user->getId() . '/edit'
        );

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input[name="user[username]"]');
        $this->assertSelectorExists('input[name="user[email]"]');
        $this->assertSelectorExists('input[name="user[password][first]"]');
        $this->assertSelectorExists('input[name="user[password][second]"]');
        $this->assertSelectorExists('select[name="user[role]"]');
    }

    /**
     * Vérifie qu'un administrateur peut modifier les informations
     * et le rôle d'un utilisateur existant.
     */
    public function testAdminCanEditUser(): void
    {
        $client = static::createClient();

        $admin = $this->getUserByUsername('admin');
        $user = $this->getUserByUsername('member');

        $client->loginUser($admin);

        $crawler = $client->request(
            'GET',
            '/users/' . $user->getId() . '/edit'
        );

        $form = $crawler->selectButton('Modifier')->form();

        $form['user[username]'] = 'member_updated';
        $form['user[email]'] = 'member_updated@todo.local';
        $form['user[password][first]'] = 'newpassword123';
        $form['user[password][second]'] = 'newpassword123';

        // Le test vérifie notamment qu'un ROLE_USER peut être
        // transformé en ROLE_ADMIN par un administrateur.
        $form['user[role]'] = 'ROLE_ADMIN';

        $client->submit($form);

        $this->assertResponseRedirects('/users');

        // Recharge l'utilisateur depuis la base afin de vérifier
        // l'état réellement persisté et non seulement l'objet en mémoire.
        $this->getEntityManager()->clear();

        $updatedUser = $this->getEntityManager()
            ->getRepository(User::class)
            ->findOneBy(['username' => 'member_updated']);

        $this->assertNotNull($updatedUser);
        $this->assertSame('member_updated@todo.local', $updatedUser->getEmail());
        $this->assertSame('ROLE_ADMIN', $updatedUser->getRole());

        // Restaure l'utilisateur de la fixture afin que les autres tests
        // retrouvent l'état initial attendu.
        $updatedUser->setUsername('member');
        $updatedUser->setEmail('member@todo.local');
        $updatedUser->setRole('ROLE_USER');

        $this->getEntityManager()->flush();
    }
}
