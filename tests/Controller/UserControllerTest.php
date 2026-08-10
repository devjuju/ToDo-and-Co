<?php

namespace Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    private function getEntityManager(): EntityManagerInterface
    {
        return static::getContainer()
            ->get(EntityManagerInterface::class);
    }

    private function getUserByUsername(string $username): User
    {
        $user = $this->getEntityManager()
            ->getRepository(User::class)
            ->findOneBy(['username' => $username]);

        $this->assertNotNull($user);

        return $user;
    }

    public function testAnonymousUserIsRedirectedToLogin(): void
    {
        $client = static::createClient();

        $client->request('GET', '/users');

        $this->assertResponseRedirects('/login');
    }

    public function testUserCannotAccessUserManagement(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Se connecter')->form();

        $form['_username'] = 'test';
        $form['_password'] = 'test123';

        $client->submit($form);

        $client->request('GET', '/users');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testAdminCanAccessUserManagement(): void
    {
        $client = static::createClient();

        $admin = $this->getUserByUsername('admin');

        $client->loginUser($admin);

        $client->request('GET', '/users');

        $this->assertResponseIsSuccessful();
    }

    public function testAdminCanDisplayUserCreationForm(): void
    {
        $client = static::createClient();

        $admin = $this->getUserByUsername('admin');

        $client->loginUser($admin);

        $crawler = $client->request('GET', '/users/create');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input[name="user[username]"]');
        $this->assertSelectorExists('input[name="user[email]"]');
        $this->assertSelectorExists('input[name="user[password][first]"]');
        $this->assertSelectorExists('input[name="user[password][second]"]');
        $this->assertSelectorExists('select[name="user[role]"]');
    }

    public function testAdminCanCreateUser(): void
    {
        $client = static::createClient();

        $admin = $this->getUserByUsername('admin');

        $client->loginUser($admin);

        $crawler = $client->request('GET', '/users/create');

        $form = $crawler->selectButton('Ajouter')->form();

        $form['user[username]'] = 'new_user';
        $form['user[email]'] = 'new_user@todo.local';
        $form['user[password][first]'] = 'password123';
        $form['user[password][second]'] = 'password123';
        $form['user[role]'] = 'ROLE_USER';

        $client->submit($form);

        $this->assertResponseRedirects('/users');

        $user = $this->getEntityManager()
            ->getRepository(User::class)
            ->findOneBy(['username' => 'new_user']);

        $this->assertNotNull($user);
        $this->assertSame('new_user@todo.local', $user->getEmail());
        $this->assertSame('ROLE_USER', $user->getRole());

        $this->getEntityManager()->remove($user);
        $this->getEntityManager()->flush();
    }

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
        $form['user[role]'] = 'ROLE_ADMIN';

        $client->submit($form);

        $this->assertResponseRedirects('/users');

        $this->getEntityManager()->clear();

        $updatedUser = $this->getEntityManager()
            ->getRepository(User::class)
            ->findOneBy(['username' => 'member_updated']);

        $this->assertNotNull($updatedUser);
        $this->assertSame('member_updated@todo.local', $updatedUser->getEmail());
        $this->assertSame('ROLE_ADMIN', $updatedUser->getRole());

        // Restore the fixture user for the other tests.
        $updatedUser->setUsername('member');
        $updatedUser->setEmail('member@todo.local');
        $updatedUser->setRole('ROLE_USER');

        $this->getEntityManager()->flush();
    }
}
