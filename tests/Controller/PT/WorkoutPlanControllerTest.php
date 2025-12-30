<?php

namespace App\Tests\Controller\PT;

use App\Domain\Workout\Entity\ClientAssessment;
use App\Domain\Workout\Entity\WorkoutPlan;
use App\Domain\User\Entity\User;
use App\Domain\PersonalTrainer\Entity\PersonalTrainer;
use App\Domain\PersonalTrainer\Entity\PTClientRelation;
use App\Domain\Gym\Entity\Gym;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class WorkoutPlanControllerTest extends WebTestCase
{
    private $client;
    private EntityManagerInterface $entityManager;
    private User $ptUser;
    private PersonalTrainer $pt;
    private User $clientUser;
    private Gym $gym;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        // Reset database before each test
        \App\Tests\DatabasePrimer::prime(static::$kernel);

        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();

        // Setup test data
        $this->setupTestData();
    }

    private function setupTestData(): void
    {
        // Create Gym
        $this->gym = new Gym();
        $this->gym->setName('Test Gym');
        $this->gym->setEmail('gym@test.com');
        $this->gym->setAddress('Test Address');
        $this->gym->setCity('Test City');
        $this->gym->setPostalCode('12345');
        $this->gym->setPhoneNumber('1234567890');
        $this->entityManager->persist($this->gym);

        // Create PT User
        $this->ptUser = new User();
        $this->ptUser->setEmail('pt@test.com');
        $this->ptUser->setPassword('$2y$13$hashedpassword');
        $this->ptUser->setFirstName('John');
        $this->ptUser->setLastName('Trainer');
        $this->ptUser->setRoles(['ROLE_PT']);
        $this->entityManager->persist($this->ptUser);

        // Create PT
        $this->pt = new PersonalTrainer();
        $this->pt->setUser($this->ptUser);
        $this->pt->setGym($this->gym);
        $this->entityManager->persist($this->pt);

        // Create Client User
        $this->clientUser = new User();
        $this->clientUser->setEmail('client@test.com');
        $this->clientUser->setPassword('$2y$13$hashedpassword');
        $this->clientUser->setFirstName('Jane');
        $this->clientUser->setLastName('Client');
        $this->clientUser->setRoles(['ROLE_USER']);
        $this->entityManager->persist($this->clientUser);

        // Create PT-Client Relation
        $relation = new PTClientRelation();
        $relation->setPersonalTrainer($this->pt);
        $relation->setClient($this->clientUser);
        $relation->setStartDate(new \DateTime());
        $relation->setStatus('active');
        $this->entityManager->persist($relation);

        $this->entityManager->flush();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }

    public function testIndexRequiresAuthentication(): void
    {
        $this->client->request('GET', '/pt/workout-plans');
        $this->assertResponseRedirects('/login');
    }

    public function testIndexShowsWorkoutPlans(): void
    {
        $this->client->loginUser($this->ptUser);

        $crawler = $this->client->request('GET', '/pt/workout-plans');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Piani di Allenamento');
    }

    public function testAssessmentsIndexRequiresAuthentication(): void
    {
        $this->client->request('GET', '/pt/workout-plans/assessments');
        $this->assertResponseRedirects('/login');
    }

    public function testAssessmentsIndexShowsList(): void
    {
        $this->client->loginUser($this->ptUser);

        $crawler = $this->client->request('GET', '/pt/workout-plans/assessments');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Interviste Clienti');
    }

    public function testCreateAssessmentRequiresPTRole(): void
    {
        // Login as regular user
        $regularUser = new User();
        $regularUser->setEmail('regular@test.com');
        $regularUser->setPassword('password');
        $regularUser->setFirstName('Regular');
        $regularUser->setLastName('User');
        $regularUser->setRoles(['ROLE_USER']);
        $this->entityManager->persist($regularUser);
        $this->entityManager->flush();

        $this->client->loginUser($regularUser);

        $this->client->request('GET', '/pt/workout-plans/assessments/create/' . $this->clientUser->getId());
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testCreateAssessmentShowsForm(): void
    {
        $this->client->loginUser($this->ptUser);

        $crawler = $this->client->request('GET', '/pt/workout-plans/assessments/create/' . $this->clientUser->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
        $this->assertSelectorTextContains('h3', $this->clientUser->getFirstName());
    }

    public function testCreateAssessmentSubmission(): void
    {
        $this->client->loginUser($this->ptUser);

        $crawler = $this->client->request('GET', '/pt/workout-plans/assessments/create/' . $this->clientUser->getId());

        $form = $crawler->selectButton('Salva Bozza')->form([
            'age' => '30',
            'height' => '180',
            'weight' => '75',
            'gender' => 'M',
            'fitnessLevel' => 'intermediate',
            'primaryGoal' => 'Aumento massa muscolare',
            'trainingExperience' => '5',
            'weeklyAvailability' => '4',
            'sessionDuration' => '60',
            'sleepHours' => '7',
            'stressLevel' => '5',
            'complete' => '0',
        ]);

        $this->client->submit($form);

        $this->assertResponseRedirects('/pt/workout-plans/assessments');

        // Verify assessment was created
        $assessment = $this->entityManager->getRepository(ClientAssessment::class)
            ->findOneBy(['client' => $this->clientUser]);

        $this->assertNotNull($assessment);
        $this->assertEquals(30, $assessment->getAge());
        $this->assertEquals('180.00', $assessment->getHeight());
        $this->assertEquals('Aumento massa muscolare', $assessment->getPrimaryGoal());
        $this->assertEquals('draft', $assessment->getStatus());
    }

    public function testCreateAssessmentAndCompletRedirectsToCreatePlan(): void
    {
        $this->client->loginUser($this->ptUser);

        $crawler = $this->client->request('GET', '/pt/workout-plans/assessments/create/' . $this->clientUser->getId());

        $form = $crawler->selectButton('Completa e Crea Piano →')->form([
            'age' => '25',
            'height' => '170',
            'weight' => '65',
            'gender' => 'F',
            'fitnessLevel' => 'beginner',
            'primaryGoal' => 'Perdita peso',
            'trainingExperience' => '2',
            'weeklyAvailability' => '3',
            'sessionDuration' => '45',
            'sleepHours' => '6',
            'stressLevel' => '7',
            'complete' => '1',
        ]);

        $this->client->submit($form);

        // Should redirect to create plan from assessment
        $this->assertResponseRedirects();
        $response = $this->client->getResponse();
        $this->assertStringContainsString('/pt/workout-plans/create-from-assessment/', $response->headers->get('Location'));

        // Verify assessment was created and completed
        $assessment = $this->entityManager->getRepository(ClientAssessment::class)
            ->findOneBy(['client' => $this->clientUser]);

        $this->assertNotNull($assessment);
        $this->assertEquals('completed', $assessment->getStatus());
        $this->assertNotNull($assessment->getCompletedAt());
    }

    public function testCreatePlanFromAssessment(): void
    {
        $this->client->loginUser($this->ptUser);

        // First create an assessment
        $assessment = new ClientAssessment();
        $assessment->setClient($this->clientUser);
        $assessment->setPersonalTrainer($this->pt);
        $assessment->setPrimaryGoal('Forza e massa');
        $assessment->setFitnessLevel('intermediate');
        $assessment->setWeeklyAvailability(4);
        $assessment->markAsCompleted();
        $this->entityManager->persist($assessment);
        $this->entityManager->flush();

        // Now create plan from assessment
        $crawler = $this->client->request('GET', '/pt/workout-plans/create-from-assessment/' . $assessment->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h3', 'Riepilogo Intervista');

        $form = $crawler->selectButton('Crea Piano →')->form([
            'name' => 'Piano Forza Fase 1',
            'planType' => 'strength',
            'startDate' => (new \DateTime())->format('Y-m-d'),
            'weeksCount' => 8,
            'description' => 'Piano focus forza massimale',
        ]);

        $this->client->submit($form);

        $this->assertResponseRedirects();

        // Verify plan was created
        $plan = $this->entityManager->getRepository(WorkoutPlan::class)
            ->findOneBy(['client' => $this->clientUser]);

        $this->assertNotNull($plan);
        $this->assertEquals('Piano Forza Fase 1', $plan->getName());
        $this->assertEquals('strength', $plan->getPlanType());
        $this->assertEquals(8, $plan->getWeeksCount());
        $this->assertEquals($this->pt->getId(), $plan->getPersonalTrainer()->getId());
        $this->assertEquals($this->clientUser->getId(), $plan->getClient()->getId());
    }

    public function testCannotAccessOtherPTsAssessment(): void
    {
        $this->client->loginUser($this->ptUser);

        // Create another PT
        $otherPTUser = new User();
        $otherPTUser->setEmail('otherpt@test.com');
        $otherPTUser->setPassword('password');
        $otherPTUser->setFirstName('Other');
        $otherPTUser->setLastName('PT');
        $otherPTUser->setRoles(['ROLE_PT']);
        $this->entityManager->persist($otherPTUser);

        $otherPT = new PersonalTrainer();
        $otherPT->setUser($otherPTUser);
        $otherPT->setGym($this->gym);
        $this->entityManager->persist($otherPT);

        // Create assessment for other PT
        $assessment = new ClientAssessment();
        $assessment->setClient($this->clientUser);
        $assessment->setPersonalTrainer($otherPT);
        $assessment->setPrimaryGoal('Test');
        $this->entityManager->persist($assessment);
        $this->entityManager->flush();

        // Try to access it
        $this->client->request('GET', '/pt/workout-plans/assessments/' . $assessment->getId());

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testCannotCreateAssessmentForNonAssignedClient(): void
    {
        $this->client->loginUser($this->ptUser);

        // Create a client not assigned to this PT
        $otherClient = new User();
        $otherClient->setEmail('otherclient@test.com');
        $otherClient->setPassword('password');
        $otherClient->setFirstName('Other');
        $otherClient->setLastName('Client');
        $otherClient->setRoles(['ROLE_USER']);
        $this->entityManager->persist($otherClient);
        $this->entityManager->flush();

        $this->client->request('GET', '/pt/workout-plans/assessments/create/' . $otherClient->getId());

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}