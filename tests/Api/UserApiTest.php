<?php

namespace App\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserApiTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testRegisterUser(): void
    {
        $email = 'test_' . time() . rand(1000, 9999) . '@example.com';

        $this->client->request('POST', '/api/users', [], [], [
            'CONTENT_TYPE' => 'application/ld+json',
        ], json_encode([
            'email' => $email,
            'password' => 'SecurePass123!',
            'firstName' => 'Test',
            'lastName' => 'User',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('email', $responseData);
        $this->assertEquals($email, $responseData['email']);
        $this->assertEquals('Test', $responseData['firstName']);
        $this->assertEquals('User', $responseData['lastName']);
        $this->assertArrayNotHasKey('password', $responseData); // Password should not be exposed
    }

    public function testLoginUser(): void
    {
        $email = 'login_' . time() . rand(1000, 9999) . '@example.com';

        // First, register a user
        $this->client->request('POST', '/api/users', [], [], [
            'CONTENT_TYPE' => 'application/ld+json',
        ], json_encode([
            'email' => $email,
            'password' => 'LoginPass123!',
            'firstName' => 'Login',
            'lastName' => 'Test',
        ]));

        // Now login
        $this->client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => $email,
            'password' => 'LoginPass123!',
        ]));

        $this->assertResponseIsSuccessful();

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('token', $responseData);
        $this->assertNotEmpty($responseData['token']);

        // Verify JWT token format (should have 3 parts separated by dots)
        $parts = explode('.', $responseData['token']);
        $this->assertCount(3, $parts);
    }

    public function testLoginWithInvalidCredentials(): void
    {
        $this->client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'nonexistent@example.com',
            'password' => 'WrongPassword',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testGetUserProfileRequiresAuthentication(): void
    {
        $this->client->request('GET', '/api/users/1', [], [], [
            'CONTENT_TYPE' => 'application/ld+json',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testGetUserProfileWithAuthentication(): void
    {
        $email = 'profile_' . time() . rand(1000, 9999) . '@example.com';

        // Register and login
        $this->client->request('POST', '/api/users', [], [], [
            'CONTENT_TYPE' => 'application/ld+json',
        ], json_encode([
            'email' => $email,
            'password' => 'ProfilePass123!',
            'firstName' => 'Profile',
            'lastName' => 'User',
        ]));

        $userData = json_decode($this->client->getResponse()->getContent(), true);
        $userId = $userData['id'];

        // Login to get token
        $this->client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => $email,
            'password' => 'ProfilePass123!',
        ]));

        $loginData = json_decode($this->client->getResponse()->getContent(), true);
        $token = $loginData['token'];

        // Get user profile with token
        $this->client->request('GET', "/api/users/{$userId}", [], [], [
            'HTTP_AUTHORIZATION' => "Bearer {$token}",
            'CONTENT_TYPE' => 'application/ld+json',
        ]);

        $this->assertResponseIsSuccessful();

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals($userId, $responseData['id']);
        $this->assertEquals($email, $responseData['email']);
        $this->assertArrayHasKey('roles', $responseData);
        $this->assertContains('ROLE_USER', $responseData['roles']);
    }

    public function testUpdateUserProfile(): void
    {
        $email = 'update_' . time() . rand(1000, 9999) . '@example.com';

        // Register user
        $this->client->request('POST', '/api/users', [], [], [
            'CONTENT_TYPE' => 'application/ld+json',
        ], json_encode([
            'email' => $email,
            'password' => 'UpdatePass123!',
            'firstName' => 'Original',
            'lastName' => 'Name',
        ]));

        $userData = json_decode($this->client->getResponse()->getContent(), true);
        $userId = $userData['id'];

        // Login
        $this->client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => $email,
            'password' => 'UpdatePass123!',
        ]));

        $loginData = json_decode($this->client->getResponse()->getContent(), true);
        $token = $loginData['token'];

        // Update profile
        $this->client->request('PATCH', "/api/users/{$userId}", [], [], [
            'HTTP_AUTHORIZATION' => "Bearer {$token}",
            'CONTENT_TYPE' => 'application/merge-patch+json',
        ], json_encode([
            'firstName' => 'Updated',
            'lastName' => 'User',
            'phoneNumber' => '+39 123456789',
        ]));

        $this->assertResponseIsSuccessful();

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals('Updated', $responseData['firstName']);
        $this->assertEquals('User', $responseData['lastName']);
        $this->assertEquals('+39 123456789', $responseData['phoneNumber']);
    }
}
