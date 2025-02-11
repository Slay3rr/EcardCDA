<?php
namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ArticleTest extends WebTestCase
{
    public function testArticle(): void
    {
        $client = static::createClient();
        
        // Effectue une requête sur une page d'article spécifique
        $crawler = $client->request('GET', '/article/3');

        // Vérifie que la réponse est réussie (code HTTP 200)
        $this->assertResponseIsSuccessful();


        // Vérifie que la page contient une section avec les offres disponibles
        $this->assertCount(
            1,
            $crawler->filter('h3:contains("Offres disponibles")'),
        );

        // Vérifie qu'il y a au moins une offre affichée (si applicable)
        $this->assertGreaterThanOrEqual(
            1,
            $crawler->filter('table tbody tr')->count(),
        );
    }
}
