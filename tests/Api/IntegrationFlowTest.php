<?php

namespace App\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Integration test for the complete user journey:
 * 1. User registers
 * 2. User creates autonomous workout plan
 * 3. User subscribes to a gym
 * 4. User can see both autonomous and gym-related resources
 */
class IntegrationFlowTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testCompleteUserJourney(): void
    {
        // ========================================
        // STEP 1: User Registration
        // ========================================
        $email = 'integration_' . time() . rand(1000, 9999) . '@example.com';

        $this->client->request('POST', '/api/users', [], [], [
            'CONTENT_TYPE' => 'application/ld+json',
        ], json_encode([
            'email' => $email,
            'password' => 'Integration123!',
            'firstName' => 'Integration',
            'lastName' => 'Test',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $userData = json_decode($this->client->getResponse()->getContent(), true);
        $userId = $userData['id'];

        $this->assertNotNull($userId);
        $this->assertEquals($email, $userData['email']);

        // ========================================
        // STEP 2: User Login (get JWT token)
        // ========================================
        $this->client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => $email,
            'password' => 'Integration123!',
        ]));

        $this->assertResponseIsSuccessful();
        $loginData = json_decode($this->client->getResponse()->getContent(), true);
        $token = $loginData['token'];

        $this->assertNotEmpty($token);

        // ========================================
        // STEP 3: User creates autonomous workout plan
        // ========================================
        $this->client->request('POST', '/api/workout_plans', [], [], [
            'HTTP_AUTHORIZATION' => "Bearer {$token}",
            'CONTENT_TYPE' => 'application/ld+json',
        ], json_encode([
            'name' => 'My First Workout Plan',
            'description' => 'Self-created full body workout',
            'planType' => 'user_created',
            'goal' => 'Build strength and muscle mass',
            'startDate' => '2025-01-01',
            'endDate' => '2025-03-31',
            'weeksCount' => 12,
            'notes' => 'Training 3 times per week',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $workoutPlanData = json_decode($this->client->getResponse()->getContent(), true);
        $workoutPlanId = $workoutPlanData['id'];

        $this->assertNotNull($workoutPlanId);
        $this->assertEquals('My First Workout Plan', $workoutPlanData['name']);
        $this->assertEquals('user_created', $workoutPlanData['planType']);
        $this->assertStringContainsString("/api/users/{$userId}", $workoutPlanData['client']);

        // ========================================
        // STEP 4: User lists their workout plans
        // ========================================
        $this->client->request('GET', '/api/workout_plans', [], [], [
            'HTTP_AUTHORIZATION' => "Bearer {$token}",
            'CONTENT_TYPE' => 'application/ld+json',
        ]);

        $this->assertResponseIsSuccessful();
        $plansData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThan(0, $plansData['totalItems']);
        $this->assertGreaterThanOrEqual(1, count($plansData['member']));

        // ========================================
        // STEP 5: Create a gym (simulating gym owner)
        // ========================================
        $this->client->request('POST', '/api/gyms', [], [], [
            'HTTP_AUTHORIZATION' => "Bearer {$token}",
            'CONTENT_TYPE' => 'application/ld+json',
        ], json_encode([
            'name' => 'FitGym Integration Test',
            'description' => 'Gym for integration testing',
            'address' => 'Via Integration 123',
            'city' => 'Roma',
            'postalCode' => '00100',
            'province' => 'RM',
            'phoneNumber' => '+39 06 123456',
            'email' => 'info@fitgym-integration.it',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $gymData = json_decode($this->client->getResponse()->getContent(), true);
        $gymId = $gymData['id'];

        $this->assertNotNull($gymId);
        $this->assertEquals('FitGym Integration Test', $gymData['name']);
        $this->assertEquals('Roma', $gymData['city']);

        // ========================================
        // STEP 6: User subscribes to the gym
        // ========================================
        $this->client->request('POST', '/api/gym_memberships', [], [], [
            'HTTP_AUTHORIZATION' => "Bearer {$token}",
            'CONTENT_TYPE' => 'application/ld+json',
        ], json_encode([
            'gym' => "/api/gyms/{$gymId}",
            'startDate' => '2025-01-01',
            'endDate' => '2025-12-31',
            'notes' => 'Full year membership',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $membershipData = json_decode($this->client->getResponse()->getContent(), true);
        $membershipId = $membershipData['id'];

        $this->assertNotNull($membershipId);
        $this->assertStringContainsString("/api/gyms/{$gymId}", $membershipData['gym']);
        $this->assertStringContainsString("/api/users/{$userId}", $membershipData['user']);
        $this->assertEquals('pending', $membershipData['status']);
        $this->assertStringContainsString('2025-12-31', $membershipData['endDate']);

        // ========================================
        // STEP 7: User views their membership
        // ========================================
        $this->client->request('GET', "/api/gym_memberships/{$membershipId}", [], [], [
            'HTTP_AUTHORIZATION' => "Bearer {$token}",
            'CONTENT_TYPE' => 'application/ld+json',
        ]);

        $this->assertResponseIsSuccessful();
        $membershipDetails = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals($membershipId, $membershipDetails['id']);
        $this->assertEquals('Full year membership', $membershipDetails['notes']);

        // ========================================
        // STEP 8: User lists all memberships
        // ========================================
        $this->client->request('GET', '/api/gym_memberships', [], [], [
            'HTTP_AUTHORIZATION' => "Bearer {$token}",
            'CONTENT_TYPE' => 'application/ld+json',
        ]);

        $this->assertResponseIsSuccessful();
        $membershipsData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThan(0, $membershipsData['totalItems']);

        // ========================================
        // STEP 9: User updates their profile
        // ========================================
        $this->client->request('PATCH', "/api/users/{$userId}", [], [], [
            'HTTP_AUTHORIZATION' => "Bearer {$token}",
            'CONTENT_TYPE' => 'application/merge-patch+json',
        ], json_encode([
            'phoneNumber' => '+39 333 1234567',
            'city' => 'Roma',
        ]));

        $this->assertResponseIsSuccessful();
        $updatedUser = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals('+39 333 1234567', $updatedUser['phoneNumber']);
        $this->assertEquals('Roma', $updatedUser['city']);

        // ========================================
        // STEP 10: Verify all user resources are accessible
        // ========================================

        // Check user profile
        $this->client->request('GET', "/api/users/{$userId}", [], [], [
            'HTTP_AUTHORIZATION' => "Bearer {$token}",
            'CONTENT_TYPE' => 'application/ld+json',
        ]);

        $this->assertResponseIsSuccessful();

        // Check workout plans
        $this->client->request('GET', '/api/workout_plans', [], [], [
            'HTTP_AUTHORIZATION' => "Bearer {$token}",
            'CONTENT_TYPE' => 'application/ld+json',
        ]);

        $this->assertResponseIsSuccessful();
        $finalPlans = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertGreaterThanOrEqual(1, $finalPlans['totalItems']);

        // Check gym memberships
        $this->client->request('GET', '/api/gym_memberships', [], [], [
            'HTTP_AUTHORIZATION' => "Bearer {$token}",
            'CONTENT_TYPE' => 'application/ld+json',
        ]);

        $this->assertResponseIsSuccessful();
        $finalMemberships = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertGreaterThanOrEqual(1, $finalMemberships['totalItems']);
    }

    public function testPublicGymListingWithoutAuthentication(): void
    {
        $email = 'public_test_' . time() . rand(1000, 9999) . '@example.com';

        // Create a gym first (with auth)
        $this->client->request('POST', '/api/users', [], [], [
            'CONTENT_TYPE' => 'application/ld+json',
        ], json_encode([
            'email' => $email,
            'password' => 'Public123!',
            'firstName' => 'Public',
            'lastName' => 'Test',
        ]));

        $this->client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => $email,
            'password' => 'Public123!',
        ]));

        $loginData = json_decode($this->client->getResponse()->getContent(), true);
        $token = $loginData['token'];

        $this->client->request('POST', '/api/gyms', [], [], [
            'HTTP_AUTHORIZATION' => "Bearer {$token}",
            'CONTENT_TYPE' => 'application/ld+json',
        ], json_encode([
            'name' => 'Public Gym',
            'address' => 'Via Public 1',
            'city' => 'Milano',
            'postalCode' => '20100',
        ]));

        // Now try to access gyms list WITHOUT authentication (should work - public)
        $this->client->request('GET', '/api/gyms', [], [], [
            'CONTENT_TYPE' => 'application/ld+json',
        ]);

        $this->assertResponseIsSuccessful();

        $gymsData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('member', $gymsData);
        $this->assertGreaterThan(0, $gymsData['totalItems']);
    }
}
