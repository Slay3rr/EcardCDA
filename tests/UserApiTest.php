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
            'HTTP_Authorization' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE3NDIxNDY0NTQsImV4cCI6MTc0MjE1MDA1NCwicm9sZXMiOlsiUk9MRV9BRE1JTiIsIlJPTEVfVVNFUiJdLCJlbWFpbCI6ImFkbWluQGFkbWluIn0.g4YxCPvsdcJDUFg5FDVNhcHrf8IWRUEo3XC_AZhqEe7JyezOVGm0i5r8iMIneWVK176ReWRB2clYskOtfz5NpntuI5ojVbbEvE9xPL3AKe5jgEyQ7Jd24UooSWXsfI0wJUA62HC2eJ5fEOpmyiSYsTyrrcEJqfcbJIkfl_5XKDv1iY4EKeuW9QzvqYUgkJVhkdQWDQlpxEAsSu42nt4FWZQd-POqoVveH-jJG6HXBPj9IbImFd9JCcu0I1N0uVIHGEfi499NWLntNxzun3F_truH_PrlCwAZMDyg1-qkZhXTnN-E8ZoUS6qakmNaJSEQf6q_vpVNvWSYU5J0dh2lq7UBnSlSjysksNUa_o-pfhG-ZF1NE-Rckt30z9mrbBI44ZBaisfomVF9NOS0S8YzucRjwpFbyTwm2BElDqOdCwJS6VbIwYpIqKYj48wztjwHqWNjkKAQpOSh92oyz_aJj6eRmCU0ZiORKRT3bgrGShV6LDWFdF1wAYqXVLqVej6G6IcxIsS4E7XGgRL2x3aq2Za7MITVY0pgvVnhveKS7HyvFfq0Xg4v0B6qBSW0FqcuJGBN2ctHGlERLsIT8SY7lTQa9nYW8LoqQ37VpZ5g2ZvXvJYGHtGlaoAydPzYRlw221ZTy36mAm7O--8sUyswA5n-xQb8CSqACdLRCcQt10M',
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
