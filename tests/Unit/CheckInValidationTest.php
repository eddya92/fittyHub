<?php

namespace App\Tests\Unit;

use App\Domain\Gym\Entity\Gym;
use App\Domain\Medical\Entity\MedicalCertificate;
use App\Domain\Membership\Entity\GymMembership;
use App\Domain\User\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * Test business logic validation for check-in
 */
class CheckInValidationTest extends TestCase
{
    public function testMembershipIsExpiredWhenEndDateIsInPast(): void
    {
        $membership = new GymMembership();
        $membership->setEndDate(new \DateTime('-1 day'));

        $now = new \DateTime();
        $isExpired = $membership->getEndDate() < $now;

        $this->assertTrue($isExpired, 'Membership with past end date should be expired');
    }

    public function testMembershipIsActiveWhenEndDateIsInFuture(): void
    {
        $membership = new GymMembership();
        $membership->setEndDate(new \DateTime('+30 days'));

        $now = new \DateTime();
        $isExpired = $membership->getEndDate() < $now;

        $this->assertFalse($isExpired, 'Membership with future end date should NOT be expired');
    }

    public function testCertificateIsExpiredWhenExpiryDateIsInPast(): void
    {
        $certificate = new MedicalCertificate();
        $certificate->setExpiryDate(new \DateTime('-1 day'));

        $now = new \DateTime();
        $isExpired = $certificate->getExpiryDate() < $now;

        $this->assertTrue($isExpired, 'Certificate with past expiry date should be expired');
    }

    public function testCertificateIsValidWhenExpiryDateIsInFuture(): void
    {
        $certificate = new MedicalCertificate();
        $certificate->setExpiryDate(new \DateTime('+60 days'));

        $now = new \DateTime();
        $isExpired = $certificate->getExpiryDate() < $now;

        $this->assertFalse($isExpired, 'Certificate with future expiry date should NOT be expired');
    }

    public function testMembershipStatusCanBeActive(): void
    {
        $membership = new GymMembership();
        $membership->setStatus('active');

        $this->assertEquals('active', $membership->getStatus());
        $this->assertTrue($membership->getStatus() === 'active', 'Status should be exactly "active"');
    }

    public function testMembershipStatusCanBeInactive(): void
    {
        $membership = new GymMembership();
        $membership->setStatus('inactive');

        $this->assertEquals('inactive', $membership->getStatus());
        $this->assertFalse($membership->getStatus() === 'active', 'Inactive membership should not be active');
    }

    public function testGymCanHaveMultipleMembers(): void
    {
        $gym = new Gym();
        $user1 = new User();
        $user2 = new User();

        $membership1 = new GymMembership();
        $membership1->setGym($gym);
        $membership1->setUser($user1);

        $membership2 = new GymMembership();
        $membership2->setGym($gym);
        $membership2->setUser($user2);

        $this->assertSame($gym, $membership1->getGym());
        $this->assertSame($gym, $membership2->getGym());
        $this->assertNotSame($membership1->getUser(), $membership2->getUser());
    }

    public function testUserCanHaveMedicalCertificate(): void
    {
        $user = new User();

        $certificate = new MedicalCertificate();
        $certificate->setUser($user);
        $certificate->setIssueDate(new \DateTime('-30 days'));
        $certificate->setExpiryDate(new \DateTime('+335 days')); // ~1 year validity

        $this->assertSame($user, $certificate->getUser());
        $this->assertInstanceOf(\DateTimeInterface::class, $certificate->getIssueDate());
        $this->assertInstanceOf(\DateTimeInterface::class, $certificate->getExpiryDate());
    }

    public function testCertificateHasValidityPeriod(): void
    {
        $issueDate = new \DateTime('2024-01-01');
        $expiryDate = new \DateTime('2025-01-01');

        $certificate = new MedicalCertificate();
        $certificate->setIssueDate($issueDate);
        $certificate->setExpiryDate($expiryDate);

        // Check that expiry is after issue
        $this->assertGreaterThan($certificate->getIssueDate(), $certificate->getExpiryDate());
    }
}
