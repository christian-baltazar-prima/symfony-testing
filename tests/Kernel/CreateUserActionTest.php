<?php

declare(strict_types=1);

namespace App\Tests\Kernel;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\User\CreateUserAction;
use App\Tests\DatabaseTester;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CreateUserActionTest extends KernelTestCase
{
    use DatabaseTester;

    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->refreshDatabase();
    }

    #[Test]
    public function user_created_successfully(): void
    {
        $user = new User();
        $user->setName('test');
        $user->setEmail('test@test.com');
        $user->setPassword('12345x');

        $test = self::getContainer()->get(CreateUserAction::class);

        $test($user);

        $repo = self::getContainer()->get(UserRepository::class);
        $this->assertCount(1, $repo->findAll());
    }
}