<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\DatabaseTester;
use App\Tests\SessionTester;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    use DatabaseTester;

    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = self::createClient();
        $this->entityManager = $this->client->getContainer()->get(EntityManagerInterface::class);
        $this->refreshDatabase();
    }

    #[Test]
    public function render_user_index(): void
    {
        $user = new User();
        $user->setName('Admin');
        $user->setEmail('admin@test.com');
        $user->setPassword('123456');

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->client->loginUser($user);
        $this->client->request('GET', '/user');

        self::assertResponseIsSuccessful();
    }

    #[Test]
    public function create_new_user_action(): void
    {
        $user = new User();
        $user->setName('Admin');
        $user->setEmail('admin@test.com');
        $user->setPassword('123456');

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->client->loginUser($user);
        $this->client->request('GET', '/user/new');

        self::assertResponseIsSuccessful();

        $this->client->submitForm('Save', [
            'user' => [
                'name' => 'Test',
                'email' => 'test@test.com',
                'password' => '12345x',
            ],
        ]);

        self::assertResponseRedirects('/user');

        $repo = self::getContainer()->get(UserRepository::class);
        self::assertCount(2, $repo->findAll());
    }
}