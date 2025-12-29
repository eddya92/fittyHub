<?php

namespace App\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class GymApiTest extends WebTestCase
{
    private $client;
    private $token;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->token = $this->getAuthToken();
    }

    private function getAuthToken(): ?string
    {
        // Register and login user
        $email = 'gym_owner_' . time() . rand(1000, 9999) . '@example.com';

        $this->client->request('POST', '/api/users', [], [], [
            'CONTENT_TYPE' => 'application/ld+json',
        ], json_encode([
            'email' => $email,
            'password' => 'GymOwner123!',
            'firstName' => 'Gym',
            'lastName' => 'Owner',
        ]));

        $this->client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => $email,
            'password' => 'GymOwner123!',
        ]));

        $data = json_decode($this->client->getResponse()->getContent(), true);
        return $data['token'] ?? null;
    }

    public function testListGymsPublicAccess(): void
    {
        // Public access - no authentication required
        $this->client->request('GET', '/api/gyms', [], [], [
            'CONTENT_TYPE' => 'application/ld+json',
        ]);

        $this->assertResponseIsSuccessful();

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('member', $responseData);
        $this->assertArrayHasKey('totalItems', $responseData);
    }

    public function testCreateGym(): void
    {
        $this->client->request('POST', '/api/gyms', [], [], [
            'HTTP_AUTHORIZATION' => "Bearer {$this->token}",
            'CONTENT_TYPE' => 'application/ld+json',
        ], json_encode([
            'name' => 'Test Gym',
            'description' => 'A test gym for unit testing',
            'address' => 'Via Roma 1',
            'city' => 'Milano',
            'postalCode' => '20100',
            'province' => 'MI',
            'phoneNumber' => '+39 02 123456',
            'email' => 'info@testgym.it',
            'website' => 'https://testgym.it',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals('Test Gym', $responseData['name']);
        $this->assertEquals('Milano', $responseData['city']);
        $this->assertEquals('Via Roma 1', $responseData['address']);
        $this->assertArrayHasKey('createdAt', $responseData);
    }

    public function testCreateGymRequiresAuthentication(): void
    {
        $this->client->request('POST', '/api/gyms', [], [], [
            'CONTENT_TYPE' => 'application/ld+json',
        ], json_encode([
            'name' => 'Unauthorized Gym',
            'address' => 'Via Test 1',
            'city' => 'Roma',
            'postalCode' => '00100',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testGetGymDetails(): void
    {
        // Create a gym first
        $this->client->request('POST', '/api/gyms', [], [], [
            'HTTP_AUTHORIZATION' => "Bearer {$this->token}",
            'CONTENT_TYPE' => 'application/ld+json',
        ], json_encode([
            'name' => 'Details Test Gym',
            'address' => 'Via Details 1',
            'city' => 'Torino',
            'postalCode' => '10100',
        ]));

        $gymData = json_decode($this->client->getResponse()->getContent(), true);
        $gymId = $gymData['id'];

        // Get gym details (authenticated)
        $this->client->request('GET', "/api/gyms/{$gymId}", [], [], [
            'HTTP_AUTHORIZATION' => "Bearer {$this->token}",
            'CONTENT_TYPE' => 'application/ld+json',
        ]);

        $this->assertResponseIsSuccessful();

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals($gymId, $responseData['id']);
        $this->assertEquals('Details Test Gym', $responseData['name']);
        $this->assertEquals('Torino', $responseData['city']);
    }

    public function testUpdateGym(): void
    {
        // Create a gym
        $this->client->request('POST', '/api/gyms', [], [], [
            'HTTP_AUTHORIZATION' => "Bearer {$this->token}",
            'CONTENT_TYPE' => 'application/ld+json',
        ], json_encode([
            'name' => 'Original Gym Name',
            'address' => 'Via Original 1',
            'city' => 'Napoli',
            'postalCode' => '80100',
        ]));

        $gymData = json_decode($this->client->getResponse()->getContent(), true);
        $gymId = $gymData['id'];

        // Update gym (requires GYM_ADMIN role - will fail with current user)
        $this->client->request('PATCH', "/api/gyms/{$gymId}", [], [], [
            'HTTP_AUTHORIZATION' => "Bearer {$this->token}",
            'CONTENT_TYPE' => 'application/merge-patch+json',
        ], json_encode([
            'name' => 'Updated Gym Name',
            'phoneNumber' => '+39 081 999999',
        ]));

        // This should return 403 Forbidden because user doesn't have GYM_ADMIN role
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}
