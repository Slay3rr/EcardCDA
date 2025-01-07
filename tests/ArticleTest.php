<?php
namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ArticleTest extends WebTestCase
{
    public function testArticlePageIsAccessible(): void
    {
        $client = static::createClient();
        // Remplacez l'ID par un article valide existant
        $client->request('GET', '/articles');

        // Vérifie que la réponse est réussie (code HTTP 200)
        $this->assertResponseIsSuccessful();

//         // Vérifie la présence du titre de l'article sur la page
//         $this->assertSelectorExists('h1'); // Vérifie qu'un titre de niveau 1 est présent
//         $this->assertSelectorTextContains('h1', 'Titre de l\'article'); // Remplacez par un titre attendu
//     }

//     public function testNonExistentArticle(): void
//     {
//         $client = static::createClient();

//         // Tente d'accéder à un article inexistant
//         $client->request('GET', '/article/30');

//         // Vérifie que la réponse est une erreur 404
//         $this->assertResponseStatusCodeSame(404);
//     }
// }
    }
}