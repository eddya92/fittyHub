<?php

namespace App\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class GymMembershipApiTest extends WebTestCase
{
    private $client;
    private $token;
    private $userId;
    private $gymId;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->authenticateAndSetup();
    }

    private function authenticateAndSetup(): void
    {
        $email = 'member_' . time() . rand(1000, 9999) . '@example.com';

        // Register user
        $this->client->request('POST', '/api/users', [], [], [
            'CONTENT_TYPE' => 'application/ld+json',
        ], json_encode([
            'email' => $email,
            'password' => 'Member123!',
            'firstName' => 'Member',
            'lastName' => 'User',
        ]));

        $userData = json_decode($this->client->getResponse()->getContent(), true);
        $this->userId = $userData['id'];

        // Login
        $this->client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => $email,
            'password' => 'Member123!',
        ]));

        $loginData = json_decode($this->client->getResponse()->getContent(), true);
        $this->token = $loginData['token'];

        // Create a gym
        $this->client->request('POST', '/api/gyms', [], [], [
            'HTTP_AUTHORIZATION' => "Bearer {$this->token}",
            'CONTENT_TYPE' => 'application/ld+json',
        ], json_encode([
            'name' => 'Test Gym for Membership',
            'address' => 'Via Test 123',
            'city' => 'Milano',
            'postalCode' => '20100',
        ]));

        $gymData = json_decode($this->client->getResponse()->getContent(), true);
        $this->gymId = $gymData['id'];
    }

    public function testSubscribeToGym(): void
    {
        $this->client->request('POST', '/api/gym_memberships', [], [], [
            'HTTP_AUTHORIZATION' => "Bearer {$this->token}",
            'CONTENT_TYPE' => 'application/ld+json',
        ], json_encode([
            'gym' => "/api/gyms/{$this->gymId}",
            'startDate' => '2025-01-01',
            'endDate' => '2025-12-31',
            'notes' => 'Annual membership',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('id', $responseData);
        $this->assertStringContainsString("/api/gyms/{$this->gymId}", $responseData['gym']);
        $this->assertStringContainsString("/api/users/{$this->userId}", $responseData['user']);
        $this->assertEquals('pending', $responseData['status']);
        $this->assertStringContainsString('2025-01-01', $responseData['startDate']);
        $this->assertStringContainsString('2025-12-31', $responseData['endDate']);
        $this->assertEquals('Annual membership', $responseData['notes']);
    }

    public function testListMemberships(): void
    {
        // Subscribe to gym first
        $this->client->request('POST', '/api/gym_memberships', [], [], [
            'HTTP_AUTHORIZATION' => "Bearer {$this->token}",
            'CONTENT_TYPE' => 'application/ld+json',
        ], json_encode([
            'gym' => "/api/gyms/{$this->gymId}",
            'startDate' => '2025-01-01',
            'endDate' => '2025-03-31',
        ]));

        // List memberships
        $this->client->request('GET', '/api/gym_memberships', [], [], [
            'HTTP_AUTHORIZATION' => "Bearer {$this->token}",
            'CONTENT_TYPE' => 'application/ld+json',
        ]);

        $this->assertResponseIsSuccessful();

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('member', $responseData);
        $this->assertArrayHasKey('totalItems', $responseData);
        $this->assertGreaterThan(0, $responseData['totalItems']);
    }

    public function testGetMembershipDetails(): void
    {
        // Create membership
        $this->client->request('POST', '/api/gym_memberships', [], [], [
            'HTTP_AUTHORIZATION' => "Bearer {$this->token}",
            'CONTENT_TYPE' => 'application/ld+json',
        ], json_encode([
            'gym' => "/api/gyms/{$this->gymId}",
            'startDate' => '2025-02-01',
            'endDate' => '2025-07-31',
            'notes' => 'Half year membership',
        ]));

        $membershipData = json_decode($this->client->getResponse()->getContent(), true);
        $membershipId = $membershipData['id'];

        // Get membership details
        $this->client->request('GET', "/api/gym_memberships/{$membershipId}", [], [], [
            'HTTP_AUTHORIZATION' => "Bearer {$this->token}",
            'CONTENT_TYPE' => 'application/ld+json',
        ]);

        $this->assertResponseIsSuccessful();

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals($membershipId, $responseData['id']);
        $this->assertEquals('Half year membership', $responseData['notes']);
        $this->assertEquals('pending', $responseData['status']);
    }

    public function testMembershipRequiresAuthentication(): void
    {
        $this->client->request('POST', '/api/gym_memberships', [], [], [
            'CONTENT_TYPE' => 'application/ld+json',
        ], json_encode([
            'gym' => "/api/gyms/{$this->gymId}",
            'startDate' => '2025-01-01',
            'endDate' => '2025-12-31',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testUserCannotSubscribeToNonExistentGym(): void
    {
        $this->client->request('POST', '/api/gym_memberships', [], [], [
            'HTTP_AUTHORIZATION' => "Bearer {$this->token}",
            'CONTENT_TYPE' => 'application/ld+json',
        ], json_encode([
            'gym' => '/api/gyms/999999', // Non-existent gym
            'startDate' => '2025-01-01',
            'endDate' => '2025-12-31',
        ]));

        // API Platform returns 400 Bad Request for invalid IRI references
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }
}
