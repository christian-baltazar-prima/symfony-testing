<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class CreateUserAction
{
    public function __construct(
        private UserPasswordService $userPassword,
        private EntityManagerInterface $entityManager,
    ) {}

    public function __invoke(User $user): void
    {
        $user->setPassword(($this->userPassword)($user, $user->getPassword()));

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}