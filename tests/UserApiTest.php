<?php
namespace App\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserApiTest extends WebTestCase
{
    public function testGetUsersAsAdmin(): void
    {
        $client = static::createClient();

        // Simule l'envoi d'un jeton d'accès admin (remplace `your_admin_token_here` par un token valide)
        $client->request('GET', '/api/admin/users', [], [], [
            'HTTP_Authorization' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE3MzYyOTU4ODAsImV4cCI6MTczNjI5OTQ4MCwicm9sZXMiOlsiUk9MRV9BRE1JTiIsIlJPTEVfVVNFUiJdLCJ1c2VybmFtZSI6ImFkbWluQGFkbWluIn0.R1iYHH9xLQrSO7tpunMRHcwoKy2SJ260WbHZ3QtkhrD7KHwQlKqSoNyEhj56qtXydKmVdCS_f71c_HfojNWeCBQnqKaaJge_AttyHg5qUMIGsy_IInVWJRPojG52E5jkytwnBxWE7j7-zS3d8ZP0D4zdAOn1_9FP0xZoMGCDW2g0A7mK3OOLqUh_PBJqJteHxu7eqRJ3njYQuMoYW-8w_tLl-c6OkfLD5IbqNBkxDAAA3qCnw0x8DxiJ6ZaMC3unm22ESEic1f_oGce0VwwWRULcsTjWmcpFBW1VE8Isgt2kHF5-EkV0YbA5gSxWiiqm1HrHwuu3dvu6vy3mgOfOTSVa8IwGSu6cXNqFFIigA3WHgO4LlaXZRtx5zpl9ro_oO9PNUD10Agb4TIhbQ1OgfLTdfJ0psgAZsLDdmZ4AmKTNMXrWM-LCv9J2-cw1iUfdBST7c0hXaCOb1Nn-A70vpY2MqVXzZ8Rnc-oUpP6xhdUlQbUvzDYhTJTBYDhSQYLDU2u0AivRR7VlbQc_R90GRKxNXZ-eVkuPrUb-BHu0e5cR_YIwbwl-MzWtFhQjWi_9rrFT9oWFATcI8dy1cAOmjr1VfEtkn5Yyn5Kss7wFq2xH2vRnmIrxcR8pCLmcGNkRTYL_jUyRJOVCVkYHK2bgHlCji1q0wRI-3g0VyWvdfkI',
        ]);

        // Vérifie que la réponse HTTP est réussie
        $this->assertResponseIsSuccessful('Un administrateur doit pouvoir accéder à /api/admin/users.');

        // Vérifie que le Content-Type est JSON
        $this->assertResponseHeaderSame('Content-Type', 'application/json', 'Le Content-Type doit être application/json.');

        // Vérifie que le JSON retourné est valide
        $responseContent = $client->getResponse()->getContent();
        $this->assertJson($responseContent, 'La réponse doit être un JSON valide.');

        // Décoder le JSON pour d'autres assertions
        $data = json_decode($responseContent, true);

        // Vérifie que la réponse contient un tableau (liste des utilisateurs)
        $this->assertIsArray($data, 'La réponse JSON doit contenir un tableau.');

        // Vérifie qu'il y a au moins un utilisateur
        $this->assertNotEmpty($data, 'La liste des utilisateurs ne doit pas être vide.');

        // Vérifie que les utilisateurs ont des champs spécifiques
        foreach ($data as $user) {
            $this->assertArrayHasKey('id', $user, 'Chaque utilisateur doit avoir un champ id.');
            $this->assertArrayHasKey('email', $user, 'Chaque utilisateur doit avoir un champ email.');
            $this->assertArrayHasKey('roles', $user, 'Chaque utilisateur doit avoir un champ roles.');
        }
    }
}
