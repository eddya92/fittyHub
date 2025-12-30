<?php

namespace App\Tests\Domain\Workout\Repository;

use App\Domain\Workout\Entity\ClientAssessment;
use App\Domain\Workout\Repository\ClientAssessmentRepository;
use App\Domain\User\Entity\User;
use App\Domain\PersonalTrainer\Entity\PersonalTrainer;
use App\Domain\Gym\Entity\Gym;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;

class ClientAssessmentRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private ClientAssessmentRepository $repository;
    private PersonalTrainer $pt;
    private User $client1;
    private User $client2;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        // Reset database before each test
        \App\Tests\DatabasePrimer::prime($kernel);

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->repository = $this->entityManager->getRepository(ClientAssessment::class);

        $this->setupTestData();
    }

    private function setupTestData(): void
    {
        // Create Gym
        $gym = new Gym();
        $gym->setName('Test Gym Repository');
        $gym->setEmail('gymrepo@test.com');
        $gym->setAddress('Test');
        $gym->setCity('Test');
        $gym->setPostalCode('12345');
        $gym->setPhoneNumber('123');
        $this->entityManager->persist($gym);

        // Create PT
        $ptUser = new User();
        $ptUser->setEmail('ptrepo@test.com');
        $ptUser->setPassword('pass');
        $ptUser->setFirstName('PT');
        $ptUser->setLastName('Test');
        $ptUser->setRoles(['ROLE_PT']);
        $this->entityManager->persist($ptUser);

        $this->pt = new PersonalTrainer();
        $this->pt->setUser($ptUser);
        $this->pt->setGym($gym);
        $this->entityManager->persist($this->pt);

        // Create Clients
        $this->client1 = new User();
        $this->client1->setEmail('client1repo@test.com');
        $this->client1->setPassword('pass');
        $this->client1->setFirstName('Client');
        $this->client1->setLastName('One');
        $this->entityManager->persist($this->client1);

        $this->client2 = new User();
        $this->client2->setEmail('client2repo@test.com');
        $this->client2->setPassword('pass');
        $this->client2->setFirstName('Client');
        $this->client2->setLastName('Two');
        $this->entityManager->persist($this->client2);

        $this->entityManager->flush();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }

    public function testFindByPersonalTrainer(): void
    {
        // Create assessments
        $assessment1 = new ClientAssessment();
        $assessment1->setClient($this->client1);
        $assessment1->setPersonalTrainer($this->pt);
        $assessment1->setPrimaryGoal('Goal 1');
        $this->entityManager->persist($assessment1);

        $assessment2 = new ClientAssessment();
        $assessment2->setClient($this->client2);
        $assessment2->setPersonalTrainer($this->pt);
        $assessment2->setPrimaryGoal('Goal 2');
        $this->entityManager->persist($assessment2);

        $this->entityManager->flush();

        $results = $this->repository->findByPersonalTrainer($this->pt);

        $this->assertCount(2, $results);
        $this->assertContains($assessment1, $results);
        $this->assertContains($assessment2, $results);
    }

    public function testFindByPersonalTrainerOrdersByCreatedAtDesc(): void
    {
        // Create assessments with different timestamps
        $assessment1 = new ClientAssessment();
        $assessment1->setClient($this->client1);
        $assessment1->setPersonalTrainer($this->pt);
        $assessment1->setPrimaryGoal('First');
        $this->entityManager->persist($assessment1);
        $this->entityManager->flush();

        // Delay to ensure different timestamps
        sleep(1);

        $assessment2 = new ClientAssessment();
        $assessment2->setClient($this->client2);
        $assessment2->setPersonalTrainer($this->pt);
        $assessment2->setPrimaryGoal('Second');
        $this->entityManager->persist($assessment2);
        $this->entityManager->flush();

        $results = $this->repository->findByPersonalTrainer($this->pt);

        $this->assertCount(2, $results);
        // Most recent should be first
        $this->assertEquals('Second', $results[0]->getPrimaryGoal());
        $this->assertEquals('First', $results[1]->getPrimaryGoal());
    }

    public function testFindByClient(): void
    {
        $assessment1 = new ClientAssessment();
        $assessment1->setClient($this->client1);
        $assessment1->setPersonalTrainer($this->pt);
        $assessment1->setPrimaryGoal('Goal 1');
        $this->entityManager->persist($assessment1);

        $assessment2 = new ClientAssessment();
        $assessment2->setClient($this->client1);
        $assessment2->setPersonalTrainer($this->pt);
        $assessment2->setPrimaryGoal('Goal 2');
        $this->entityManager->persist($assessment2);

        // Assessment for different client
        $assessment3 = new ClientAssessment();
        $assessment3->setClient($this->client2);
        $assessment3->setPersonalTrainer($this->pt);
        $assessment3->setPrimaryGoal('Goal 3');
        $this->entityManager->persist($assessment3);

        $this->entityManager->flush();

        $results = $this->repository->findByClient($this->client1);

        $this->assertCount(2, $results);
        $this->assertContains($assessment1, $results);
        $this->assertContains($assessment2, $results);
        $this->assertNotContains($assessment3, $results);
    }

    public function testFindLatestByClient(): void
    {
        // Create draft assessment
        $draftAssessment = new ClientAssessment();
        $draftAssessment->setClient($this->client1);
        $draftAssessment->setPersonalTrainer($this->pt);
        $draftAssessment->setPrimaryGoal('Draft');
        $draftAssessment->setStatus('draft');
        $this->entityManager->persist($draftAssessment);
        $this->entityManager->flush();

        sleep(1);

        // Create first completed assessment
        $assessment1 = new ClientAssessment();
        $assessment1->setClient($this->client1);
        $assessment1->setPersonalTrainer($this->pt);
        $assessment1->setPrimaryGoal('First Completed');
        $assessment1->markAsCompleted();
        $this->entityManager->persist($assessment1);
        $this->entityManager->flush();

        sleep(1);

        // Create second completed assessment (most recent)
        $assessment2 = new ClientAssessment();
        $assessment2->setClient($this->client1);
        $assessment2->setPersonalTrainer($this->pt);
        $assessment2->setPrimaryGoal('Latest Completed');
        $assessment2->markAsCompleted();
        $this->entityManager->persist($assessment2);
        $this->entityManager->flush();

        $latest = $this->repository->findLatestByClient($this->client1);

        $this->assertNotNull($latest);
        $this->assertEquals('Latest Completed', $latest->getPrimaryGoal());
        $this->assertEquals('completed', $latest->getStatus());
    }

    public function testFindLatestByClientReturnsNullWhenNoCompletedAssessments(): void
    {
        // Create only draft assessment
        $draftAssessment = new ClientAssessment();
        $draftAssessment->setClient($this->client1);
        $draftAssessment->setPersonalTrainer($this->pt);
        $draftAssessment->setPrimaryGoal('Draft Only');
        $draftAssessment->setStatus('draft');
        $this->entityManager->persist($draftAssessment);
        $this->entityManager->flush();

        $latest = $this->repository->findLatestByClient($this->client1);

        $this->assertNull($latest);
    }

    public function testFindLatestByClientReturnsNullWhenNoAssessments(): void
    {
        $latest = $this->repository->findLatestByClient($this->client1);

        $this->assertNull($latest);
    }

    public function testFindByPersonalTrainerReturnsEmptyArrayWhenNoAssessments(): void
    {
        $results = $this->repository->findByPersonalTrainer($this->pt);

        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }

    public function testFindByClientReturnsEmptyArrayWhenNoAssessments(): void
    {
        $results = $this->repository->findByClient($this->client1);

        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }
}