<?php

namespace App\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class WorkoutPlanApiTest extends WebTestCase
{
    private $client;
    private $token;
    private $userId;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->authenticateUser();
    }

    private function authenticateUser(): void
    {
        $email = 'workout_user_' . time() . rand(1000, 9999) . '@example.com';

        // Register user
        $this->client->request('POST', '/api/users', [], [], [
            'CONTENT_TYPE' => 'application/ld+json',
        ], json_encode([
            'email' => $email,
            'password' => 'Workout123!',
            'firstName' => 'Workout',
            'lastName' => 'User',
        ]));

        $userData = json_decode($this->client->getResponse()->getContent(), true);
        $this->userId = $userData['id'];

        // Login
        $this->client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => $email,
            'password' => 'Workout123!',
        ]));

        $loginData = json_decode($this->client->getResponse()->getContent(), true);
        $this->token = $loginData['token'];
    }

    public function testCreateWorkoutPlanAsAutonomousUser(): void
    {
        $this->client->request('POST', '/api/workout_plans', [], [], [
            'HTTP_AUTHORIZATION' => "Bearer {$this->token}",
            'CONTENT_TYPE' => 'application/ld+json',
        ], json_encode([
            'name' => 'Full Body 3x Week',
            'description' => 'Complete full body workout plan',
            'planType' => 'user_created',
            'goal' => 'Build muscle and strength',
            'startDate' => '2025-01-01',
            'endDate' => '2025-03-31',
            'weeksCount' => 12,
            'notes' => '3 workouts per week',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals('Full Body 3x Week', $responseData['name']);
        $this->assertEquals('user_created', $responseData['planType']);
        $this->assertEquals(12, $responseData['weeksCount']);
        $this->assertStringContainsString("/api/users/{$this->userId}", $responseData['client']);
        $this->assertArrayHasKey('createdAt', $responseData);
    }

    public function testListWorkoutPlans(): void
    {
        // Create a plan first
        $this->client->request('POST', '/api/workout_plans', [], [], [
            'HTTP_AUTHORIZATION' => "Bearer {$this->token}",
            'CONTENT_TYPE' => 'application/ld+json',
        ], json_encode([
            'name' => 'Test Plan',
            'planType' => 'user_created',
            'startDate' => '2025-01-01',
            'weeksCount' => 8,
        ]));

        // List all plans
        $this->client->request('GET', '/api/workout_plans', [], [], [
            'HTTP_AUTHORIZATION' => "Bearer {$this->token}",
            'CONTENT_TYPE' => 'application/ld+json',
        ]);

        $this->assertResponseIsSuccessful();

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('member', $responseData);
        $this->assertArrayHasKey('totalItems', $responseData);
        $this->assertGreaterThan(0, $responseData['totalItems']);
    }

    public function testGetWorkoutPlanDetails(): void
    {
        // Create a plan
        $this->client->request('POST', '/api/workout_plans', [], [], [
            'HTTP_AUTHORIZATION' => "Bearer {$this->token}",
            'CONTENT_TYPE' => 'application/ld+json',
        ], json_encode([
            'name' => 'Details Test Plan',
            'planType' => 'user_created',
            'goal' => 'Test goal',
            'startDate' => '2025-01-15',
            'weeksCount' => 6,
        ]));

        $planData = json_decode($this->client->getResponse()->getContent(), true);
        $planId = $planData['id'];

        // Get plan details
        $this->client->request('GET', "/api/workout_plans/{$planId}", [], [], [
            'HTTP_AUTHORIZATION' => "Bearer {$this->token}",
            'CONTENT_TYPE' => 'application/ld+json',
        ]);

        $this->assertResponseIsSuccessful();

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals($planId, $responseData['id']);
        $this->assertEquals('Details Test Plan', $responseData['name']);
        $this->assertEquals('Test goal', $responseData['goal']);
    }

    public function testUpdateWorkoutPlan(): void
    {
        // Create a plan
        $this->client->request('POST', '/api/workout_plans', [], [], [
            'HTTP_AUTHORIZATION' => "Bearer {$this->token}",
            'CONTENT_TYPE' => 'application/ld+json',
        ], json_encode([
            'name' => 'Original Plan',
            'planType' => 'user_created',
            'startDate' => '2025-01-01',
            'weeksCount' => 4,
        ]));

        $planData = json_decode($this->client->getResponse()->getContent(), true);
        $planId = $planData['id'];

        // Update plan
        $this->client->request('PATCH', "/api/workout_plans/{$planId}", [], [], [
            'HTTP_AUTHORIZATION' => "Bearer {$this->token}",
            'CONTENT_TYPE' => 'application/merge-patch+json',
        ], json_encode([
            'name' => 'Updated Plan Name',
            'goal' => 'New updated goal',
            'weeksCount' => 8,
        ]));

        $this->assertResponseIsSuccessful();

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals('Updated Plan Name', $responseData['name']);
        $this->assertEquals('New updated goal', $responseData['goal']);
        $this->assertEquals(8, $responseData['weeksCount']);
    }

    public function testDeleteWorkoutPlan(): void
    {
        // Create a plan
        $this->client->request('POST', '/api/workout_plans', [], [], [
            'HTTP_AUTHORIZATION' => "Bearer {$this->token}",
            'CONTENT_TYPE' => 'application/ld+json',
        ], json_encode([
            'name' => 'Plan to Delete',
            'planType' => 'user_created',
            'startDate' => '2025-01-01',
            'weeksCount' => 2,
        ]));

        $planData = json_decode($this->client->getResponse()->getContent(), true);
        $planId = $planData['id'];

        // Delete plan
        $this->client->request('DELETE', "/api/workout_plans/{$planId}", [], [], [
            'HTTP_AUTHORIZATION' => "Bearer {$this->token}",
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        // Verify deleted - should return 404
        $this->client->request('GET', "/api/workout_plans/{$planId}", [], [], [
            'HTTP_AUTHORIZATION' => "Bearer {$this->token}",
            'CONTENT_TYPE' => 'application/ld+json',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testWorkoutPlanRequiresAuthentication(): void
    {
        $this->client->request('POST', '/api/workout_plans', [], [], [
            'CONTENT_TYPE' => 'application/ld+json',
        ], json_encode([
            'name' => 'Unauthorized Plan',
            'planType' => 'user_created',
            'startDate' => '2025-01-01',
            'weeksCount' => 4,
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
}
