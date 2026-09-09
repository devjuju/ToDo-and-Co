<?php

namespace Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomepageControllerTest extends WebTestCase
{
    /**
     * Vérifie qu'un utilisateur non authentifié est redirigé
     * vers la page de connexion lorsqu'il tente d'accéder à l'accueil.
     */
    public function testHomepageRedirectsAnonymousUser(): void
    {
        $client = static::createClient();

        // Aucun utilisateur n'est connecté : on simule une requête anonyme.
        $client->request('GET', '/');

        $this->assertResponseRedirects('/login');
    }
}
